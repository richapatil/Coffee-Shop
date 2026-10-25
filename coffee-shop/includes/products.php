<?php
/**
 * Products.
 *
 * Each product is saved as a WordPress post of type "coffee_product".
 * The post title is the product name. Price and category are saved as post meta.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function coffee_shop_categories() {
	return array( 'Hot Coffee', 'Cold Coffee', 'Tea', 'Bakery' );
}

function coffee_shop_money( $amount ) {
	return '$' . number_format( (float) $amount, 2 );
}

// Adds the "Products" menu to wp-admin.
function coffee_shop_register_product_type() {
	register_post_type(
		'coffee_product',
		array(
			'labels'    => array(
				'name'          => 'Products',
				'singular_name' => 'Product',
				'add_new_item'  => 'Add New Product',
				'edit_item'     => 'Edit Product',
				'search_items'  => 'Search Products',
			),
			'public'    => false,
			'show_ui'   => true,
			'menu_icon' => 'dashicons-coffee',
			'supports'  => array( 'title' ),
		)
	);
}
add_action( 'init', 'coffee_shop_register_product_type' );

// All published products, sorted by name.
function coffee_shop_get_products() {
	return get_posts(
		array(
			'post_type'   => 'coffee_product',
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby'     => 'title',
			'order'       => 'ASC',
		)
	);
}

// How many of a product are left. A product with no stock saved counts as 0 (sold out).
function coffee_shop_get_stock( $product_id ) {
	return (int) get_post_meta( $product_id, '_coffee_stock', true );
}

// Takes the ordered amount out of stock.
function coffee_shop_reduce_stock( $product_id, $quantity ) {
	update_post_meta( $product_id, '_coffee_stock', max( 0, coffee_shop_get_stock( $product_id ) - $quantity ) );
}

// Price, Category and Quantity available fields on the Add/Edit Product screen.
function coffee_shop_add_product_box() {
	add_meta_box( 'coffee_product_details', 'Product Details', 'coffee_shop_render_product_box', 'coffee_product' );
}
add_action( 'add_meta_boxes', 'coffee_shop_add_product_box' );

function coffee_shop_render_product_box( $post ) {
	$price    = get_post_meta( $post->ID, '_coffee_price', true );
	$category = get_post_meta( $post->ID, '_coffee_category', true );
	$stock    = get_post_meta( $post->ID, '_coffee_stock', true );

	wp_nonce_field( 'coffee_save_product', 'coffee_product_nonce' );
	?>
	<p>
		<label for="coffee_price">Price ($)</label><br>
		<input type="number" id="coffee_price" name="coffee_price" step="0.01" min="0" required value="<?php echo esc_attr( $price ); ?>">
	</p>
	<p>
		<label for="coffee_category">Category</label><br>
		<select id="coffee_category" name="coffee_category">
			<?php foreach ( coffee_shop_categories() as $option ) : ?>
				<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $category, $option ); ?>><?php echo esc_html( $option ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="coffee_stock">Quantity available</label><br>
		<input type="number" id="coffee_stock" name="coffee_stock" step="1" min="0" required value="<?php echo esc_attr( $stock ); ?>">
	</p>
	<?php
}

// Runs when a product is saved.
function coffee_shop_save_product( $post_id ) {
	// Only save if our form was sent, the nonce is valid and this user is allowed to edit the product.
	if ( ! isset( $_POST['coffee_product_nonce'] ) || ! wp_verify_nonce( $_POST['coffee_product_nonce'], 'coffee_save_product' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$price = isset( $_POST['coffee_price'] ) ? (float) $_POST['coffee_price'] : 0;
	update_post_meta( $post_id, '_coffee_price', max( 0, $price ) );

	$category = isset( $_POST['coffee_category'] ) ? sanitize_text_field( wp_unslash( $_POST['coffee_category'] ) ) : '';
	if ( in_array( $category, coffee_shop_categories(), true ) ) {
		update_post_meta( $post_id, '_coffee_category', $category );
	}

	$stock = isset( $_POST['coffee_stock'] ) ? absint( $_POST['coffee_stock'] ) : 0;
	update_post_meta( $post_id, '_coffee_stock', $stock );
}
add_action( 'save_post_coffee_product', 'coffee_shop_save_product' );

// Columns on the Products list screen.
function coffee_shop_product_columns( $columns ) {
	return array(
		'cb'              => $columns['cb'],
		'title'           => 'Product',
		'coffee_category' => 'Category',
		'coffee_price'    => 'Price',
		'coffee_stock'    => 'In Stock',
	);
}
add_filter( 'manage_coffee_product_posts_columns', 'coffee_shop_product_columns' );

function coffee_shop_product_column_value( $column, $post_id ) {
	if ( 'coffee_category' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_coffee_category', true ) );
	}
	if ( 'coffee_price' === $column ) {
		echo esc_html( coffee_shop_money( get_post_meta( $post_id, '_coffee_price', true ) ) );
	}
	if ( 'coffee_stock' === $column ) {
		$stock = coffee_shop_get_stock( $post_id );
		echo esc_html( $stock > 0 ? $stock : 'Sold out' );
	}
}
add_action( 'manage_coffee_product_posts_custom_column', 'coffee_shop_product_column_value', 10, 2 );

// Sample products, added once when the plugin is activated.
function coffee_shop_add_sample_products() {
	if ( coffee_shop_get_products() ) {
		return;
	}

	// Name, category, price, quantity available.
	$samples = array(
		array( 'Espresso', 'Hot Coffee', 3.00, 30 ),
		array( 'Cappuccino', 'Hot Coffee', 4.50, 25 ),
		array( 'Iced Latte', 'Cold Coffee', 5.00, 20 ),
		array( 'Chai Tea', 'Tea', 3.75, 15 ),
		array( 'Blueberry Muffin', 'Bakery', 2.95, 12 ),
	);

	foreach ( $samples as $sample ) {
		wp_insert_post(
			array(
				'post_type'   => 'coffee_product',
				'post_status' => 'publish',
				'post_title'  => $sample[0],
				'meta_input'  => array(
					'_coffee_category' => $sample[1],
					'_coffee_price'    => $sample[2],
					'_coffee_stock'    => $sample[3],
				),
			)
		);
	}
}
