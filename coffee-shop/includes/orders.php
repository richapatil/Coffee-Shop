<?php
/**
 * Orders.
 *
 * Each order is saved as a post of type "coffee_order". The title is
 * "Customer name - Product", so the search box on the Orders screen
 * finds orders by customer name.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function coffee_shop_order_types() {
	return array( 'Dine-in', 'Takeaway' );
}

function coffee_shop_payment_methods() {
	return array( 'Cash', 'Card' );
}

function coffee_shop_order_statuses() {
	return array( 'Pending', 'Ready', 'Completed' );
}

// Reads one saved order field, e.g. coffee_shop_order_field( $id, 'total' ).
function coffee_shop_order_field( $order_id, $key ) {
	return get_post_meta( $order_id, '_coffee_' . $key, true );
}

// Adds the "Orders" menu to wp-admin.
function coffee_shop_register_order_type() {
	register_post_type(
		'coffee_order',
		array(
			'labels'       => array(
				'name'          => 'Orders',
				'singular_name' => 'Order',
				'edit_item'     => 'View Order',
				'search_items'  => 'Search Orders',
			),
			'public'       => false,
			'show_ui'      => true,
			'menu_icon'    => 'dashicons-cart',
			'supports'     => array( 'title' ),
			// No "Add New" button: orders only come from the order form.
			'capabilities' => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap' => true,
		)
	);
}
add_action( 'init', 'coffee_shop_register_order_type' );

// Saves a new order, takes the quantity out of stock and returns the order ID.
function coffee_shop_create_order( $order ) {
	$price = (float) get_post_meta( $order['product_id'], '_coffee_price', true );

	$order_id = wp_insert_post(
		array(
			'post_type'   => 'coffee_order',
			'post_status' => 'publish',
			'post_title'  => $order['customer'] . ' - ' . get_the_title( $order['product_id'] ),
			'meta_input'  => array(
				'_coffee_customer'   => $order['customer'],
				'_coffee_phone'      => $order['phone'],
				'_coffee_product_id' => $order['product_id'],
				'_coffee_quantity'   => $order['quantity'],
				'_coffee_order_type' => $order['order_type'],
				'_coffee_payment'    => $order['payment'],
				// Total is saved now, so a later price change does not change old orders.
				'_coffee_total'      => $price * $order['quantity'],
				'_coffee_status'     => 'Pending',
			),
		)
	);

	if ( $order_id ) {
		coffee_shop_reduce_stock( $order['product_id'], $order['quantity'] );
	}
	return $order_id;
}

// Changes an order's status. Used by the View Order screen and the staff buttons on the website.
function coffee_shop_update_order_status( $order_id, $status ) {
	if ( 'coffee_order' !== get_post_type( $order_id ) || ! in_array( $status, coffee_shop_order_statuses(), true ) ) {
		return false;
	}
	update_post_meta( $order_id, '_coffee_status', $status );
	return true;
}

// The 10 newest orders with one status, e.g. all "Ready" orders.
function coffee_shop_get_orders_by_status( $status ) {
	return get_posts(
		array(
			'post_type'   => 'coffee_order',
			'post_status' => 'publish',
			'numberposts' => 10,
			'meta_key'    => '_coffee_status',
			'meta_value'  => $status,
		)
	);
}

// Columns on the Orders list screen.
function coffee_shop_order_columns( $columns ) {
	return array(
		'cb'              => $columns['cb'],
		'title'           => 'Order',
		'coffee_quantity' => 'Qty',
		'coffee_total'    => 'Total',
		'coffee_status'   => 'Status',
		'date'            => 'Date',
	);
}
add_filter( 'manage_coffee_order_posts_columns', 'coffee_shop_order_columns' );

function coffee_shop_order_column_value( $column, $post_id ) {
	if ( 'coffee_quantity' === $column ) {
		echo esc_html( coffee_shop_order_field( $post_id, 'quantity' ) );
	}
	if ( 'coffee_total' === $column ) {
		echo esc_html( coffee_shop_money( coffee_shop_order_field( $post_id, 'total' ) ) );
	}
	if ( 'coffee_status' === $column ) {
		echo esc_html( coffee_shop_order_field( $post_id, 'status' ) );
	}
}
add_action( 'manage_coffee_order_posts_custom_column', 'coffee_shop_order_column_value', 10, 2 );

// Order details on the View Order screen.
function coffee_shop_add_order_box() {
	add_meta_box( 'coffee_order_details', 'Order Details', 'coffee_shop_render_order_box', 'coffee_order' );
}
add_action( 'add_meta_boxes', 'coffee_shop_add_order_box' );

function coffee_shop_render_order_box( $post ) {
	$details = array(
		'Order ID'       => $post->ID,
		'Customer'       => coffee_shop_order_field( $post->ID, 'customer' ),
		'Phone'          => coffee_shop_order_field( $post->ID, 'phone' ),
		'Product'        => get_the_title( coffee_shop_order_field( $post->ID, 'product_id' ) ),
		'Quantity'       => coffee_shop_order_field( $post->ID, 'quantity' ),
		'Order Type'     => coffee_shop_order_field( $post->ID, 'order_type' ),
		'Payment Method' => coffee_shop_order_field( $post->ID, 'payment' ),
		'Total'          => coffee_shop_money( coffee_shop_order_field( $post->ID, 'total' ) ),
	);
	$status = coffee_shop_order_field( $post->ID, 'status' );

	wp_nonce_field( 'coffee_save_order', 'coffee_order_admin_nonce' );
	?>
	<table class="form-table">
		<?php foreach ( $details as $label => $value ) : ?>
			<tr>
				<th><?php echo esc_html( $label ); ?></th>
				<td><?php echo esc_html( $value ); ?></td>
			</tr>
		<?php endforeach; ?>
		<tr>
			<th><label for="coffee_status">Status</label></th>
			<td>
				<select id="coffee_status" name="coffee_status">
					<?php foreach ( coffee_shop_order_statuses() as $option ) : ?>
						<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $status, $option ); ?>><?php echo esc_html( $option ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
	</table>
	<?php
}

// The manager can only change the status. Everything else stays as the customer entered it.
function coffee_shop_save_order( $post_id ) {
	if ( ! isset( $_POST['coffee_order_admin_nonce'] ) || ! wp_verify_nonce( $_POST['coffee_order_admin_nonce'], 'coffee_save_order' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$status = isset( $_POST['coffee_status'] ) ? sanitize_text_field( wp_unslash( $_POST['coffee_status'] ) ) : '';
	coffee_shop_update_order_status( $post_id, $status );
}
add_action( 'save_post_coffee_order', 'coffee_shop_save_order' );
