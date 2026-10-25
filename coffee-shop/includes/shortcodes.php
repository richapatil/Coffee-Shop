<?php
/**
 * Website parts: [coffee_menu], [coffee_order_form] and [coffee_order_status],
 * plus the staff buttons on the status board.
 *
 * Put a shortcode in any page and WordPress replaces it with the HTML returned here.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function coffee_shop_notice( $text, $type ) {
	return '<p class="coffee-notice coffee-notice-' . esc_attr( $type ) . '">' . esc_html( $text ) . '</p>';
}

// [coffee_menu]: all products grouped by category, like a printed cafe menu.
function coffee_shop_menu_shortcode() {
	$products = coffee_shop_get_products();
	if ( ! $products ) {
		return coffee_shop_notice( 'The menu is empty. Add products in wp-admin under Products.', 'error' );
	}

	// Put each product into the list for its category.
	$groups = array();
	foreach ( $products as $product ) {
		$category              = get_post_meta( $product->ID, '_coffee_category', true );
		$groups[ $category ][] = $product;
	}

	ob_start();
	?>
	<div class="coffee-menu">
		<?php foreach ( coffee_shop_categories() as $category ) : ?>
			<?php if ( empty( $groups[ $category ] ) ) { continue; } ?>
			<div class="coffee-menu-category">
				<h3><?php echo esc_html( $category ); ?></h3>
				<ul>
					<?php foreach ( $groups[ $category ] as $product ) : ?>
						<?php $sold_out = 0 === coffee_shop_get_stock( $product->ID ); ?>
						<li<?php echo $sold_out ? ' class="coffee-menu-sold-out"' : ''; ?>>
							<span class="coffee-menu-item"><?php echo esc_html( $product->post_title ); ?></span>
							<span class="coffee-menu-price"><?php echo esc_html( $sold_out ? 'Sold out' : coffee_shop_money( get_post_meta( $product->ID, '_coffee_price', true ) ) ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'coffee_menu', 'coffee_shop_menu_shortcode' );

// [coffee_order_form]: the customer order form.
function coffee_shop_order_form_shortcode() {
	$message = '';
	if ( isset( $_POST['coffee_order_nonce'] ) ) {
		$message = coffee_shop_handle_order_form();
	}

	ob_start();
	?>
	<div id="coffee-order">
	<?php
	echo $message; // Already escaped in coffee_shop_notice().
	// action="#coffee-order" scrolls back to this form after it is sent, so the customer sees the message.
	?>
	<form method="post" action="#coffee-order" class="coffee-order-form">
		<?php wp_nonce_field( 'coffee_place_order', 'coffee_order_nonce' ); ?>
		<p>
			<label for="coffee_customer">Your name</label>
			<input type="text" id="coffee_customer" name="coffee_customer" required>
		</p>
		<p>
			<label for="coffee_phone">Phone (10 digits)</label>
			<input type="tel" id="coffee_phone" name="coffee_phone" pattern="\d{10}" required>
		</p>
		<p>
			<label for="coffee_product">Product</label>
			<select id="coffee_product" name="coffee_product" required>
				<?php foreach ( coffee_shop_get_products() as $product ) : ?>
					<?php $sold_out = 0 === coffee_shop_get_stock( $product->ID ); ?>
					<option value="<?php echo esc_attr( $product->ID ); ?>" <?php disabled( $sold_out ); ?>>
						<?php echo esc_html( $product->post_title . ' (' . ( $sold_out ? 'Sold out' : coffee_shop_money( get_post_meta( $product->ID, '_coffee_price', true ) ) ) . ')' ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="coffee_quantity">Quantity</label>
			<input type="number" id="coffee_quantity" name="coffee_quantity" min="1" max="20" value="1" required>
		</p>
		<p>
			<label for="coffee_order_type">Order type</label>
			<select id="coffee_order_type" name="coffee_order_type">
				<?php foreach ( coffee_shop_order_types() as $option ) : ?>
					<option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="coffee_payment">Payment method</label>
			<select id="coffee_payment" name="coffee_payment">
				<?php foreach ( coffee_shop_payment_methods() as $option ) : ?>
					<option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p><button type="submit">Place Order</button></p>
	</form>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'coffee_order_form', 'coffee_shop_order_form_shortcode' );

// [coffee_order_status]: order numbers that are being made or are ready, like the screen at a cafe counter.
// Visitors only see order numbers, never names or phone numbers, because the website is public.
// Logged-in staff also see the customer and product, and a button that moves the order to its next status.
// Completed orders are not shown.
function coffee_shop_order_status_shortcode() {
	$columns = array(
		'Pending' => 'Preparing',
		'Ready'   => 'Ready for Pickup',
	);
	$next_status = array(
		'Pending' => 'Ready',
		'Ready'   => 'Completed',
	);
	$is_staff = current_user_can( 'edit_posts' );

	ob_start();
	?>
	<div id="coffee-status" class="coffee-status<?php echo $is_staff ? ' coffee-status-staff' : ''; ?>">
		<?php foreach ( $columns as $status => $heading ) : ?>
			<?php $orders = coffee_shop_get_orders_by_status( $status ); ?>
			<div class="coffee-status-column coffee-status-<?php echo esc_attr( strtolower( $status ) ); ?>">
				<h3><?php echo esc_html( $heading ); ?></h3>
				<?php if ( $orders ) : ?>
					<ul>
						<?php foreach ( $orders as $order ) : ?>
							<li>
								<span class="coffee-status-number">#<?php echo esc_html( $order->ID ); ?></span>
								<?php if ( $is_staff ) : ?>
									<span class="coffee-status-details"><?php echo esc_html( $order->post_title . ' x ' . coffee_shop_order_field( $order->ID, 'quantity' ) ); ?></span>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
										<input type="hidden" name="action" value="coffee_update_status">
										<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'coffee_update_status' ) ); ?>">
										<input type="hidden" name="order_id" value="<?php echo esc_attr( $order->ID ); ?>">
										<input type="hidden" name="status" value="<?php echo esc_attr( $next_status[ $status ] ); ?>">
										<button type="submit">Mark <?php echo esc_html( $next_status[ $status ] ); ?></button>
									</form>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p>No orders right now.</p>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'coffee_order_status', 'coffee_shop_order_status_shortcode' );

// Runs when staff click Mark Ready or Mark Completed on the website.
// WordPress only runs "admin_post_" hooks for logged-in users, so visitors cannot change orders.
function coffee_shop_handle_status_button() {
	check_admin_referer( 'coffee_update_status' );

	$order_id = absint( $_POST['order_id'] ?? 0 );
	$status   = sanitize_text_field( wp_unslash( $_POST['status'] ?? '' ) );
	if ( ! current_user_can( 'edit_post', $order_id ) ) {
		wp_die( 'You are not allowed to change this order.' );
	}

	coffee_shop_update_order_status( $order_id, $status );

	// Go back to the page the button was on.
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	wp_safe_redirect( $back . '#coffee-status' );
	exit;
}
add_action( 'admin_post_coffee_update_status', 'coffee_shop_handle_status_button' );

// Checks the submitted form and saves the order. Returns a success or error message.
function coffee_shop_handle_order_form() {
	if ( ! wp_verify_nonce( $_POST['coffee_order_nonce'], 'coffee_place_order' ) ) {
		return coffee_shop_notice( 'The form expired. Please try again.', 'error' );
	}

	// sanitize_text_field() trims spaces and removes HTML tags.
	$customer   = sanitize_text_field( wp_unslash( $_POST['coffee_customer'] ?? '' ) );
	$phone      = sanitize_text_field( wp_unslash( $_POST['coffee_phone'] ?? '' ) );
	$product_id = absint( $_POST['coffee_product'] ?? 0 );
	$quantity   = sanitize_text_field( wp_unslash( $_POST['coffee_quantity'] ?? '' ) );
	$order_type = sanitize_text_field( wp_unslash( $_POST['coffee_order_type'] ?? '' ) );
	$payment    = sanitize_text_field( wp_unslash( $_POST['coffee_payment'] ?? '' ) );

	// Check blank fields first, then format, then range.
	// The browser also checks the form, but the server must check again because the browser can be bypassed.
	if ( '' === $customer || '' === $phone || '' === $quantity ) {
		return coffee_shop_notice( 'All fields are mandatory.', 'error' );
	}
	if ( ! preg_match( '/^[a-zA-Z ]+$/', $customer ) ) {
		return coffee_shop_notice( 'Name must be letters only.', 'error' );
	}
	if ( 'coffee_product' !== get_post_type( $product_id ) || 'publish' !== get_post_status( $product_id ) ) {
		return coffee_shop_notice( 'Please select a product from the menu.', 'error' );
	}
	$stock = coffee_shop_get_stock( $product_id );
	if ( 0 === $stock ) {
		return coffee_shop_notice( get_the_title( $product_id ) . ' is sold out. Please choose another product.', 'error' );
	}
	if ( ! preg_match( '/^\d{10}$/', $phone ) || ! ctype_digit( $quantity ) ) {
		return coffee_shop_notice( 'Phone must be 10 digits and Quantity must be a whole number.', 'error' );
	}
	if ( (int) $quantity < 1 || (int) $quantity > 20 ) {
		return coffee_shop_notice( 'Quantity must be between 1 and 20.', 'error' );
	}
	if ( (int) $quantity > $stock ) {
		return coffee_shop_notice( 'Only ' . $stock . ' ' . get_the_title( $product_id ) . ' left. Please order fewer.', 'error' );
	}
	if ( ! in_array( $order_type, coffee_shop_order_types(), true ) || ! in_array( $payment, coffee_shop_payment_methods(), true ) ) {
		return coffee_shop_notice( 'Please choose an order type and payment method.', 'error' );
	}

	$order_id = coffee_shop_create_order(
		array(
			'customer'   => $customer,
			'phone'      => $phone,
			'product_id' => $product_id,
			'quantity'   => (int) $quantity,
			'order_type' => $order_type,
			'payment'    => $payment,
		)
	);
	if ( ! $order_id ) {
		return coffee_shop_notice( 'Sorry, the order could not be saved. Please try again.', 'error' );
	}

	$total = coffee_shop_money( coffee_shop_order_field( $order_id, 'total' ) );
	return coffee_shop_notice( "Thank you, $customer! Order #$order_id is placed. Total: $total", 'success' );
}
