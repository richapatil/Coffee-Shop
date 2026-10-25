<?php
/**
 * Plugin Name: Coffee Shop
 * Description: A small coffee shop manager: products, a public menu and an order form.
 * Version:     1.0.0
 * Author:      Richa Patil
 * License:     GPL-2.0-or-later
 */

// Stop if someone opens this file directly instead of through WordPress.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/products.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/orders.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/shortcodes.php';

// Load the plugin stylesheet on the public pages.
function coffee_shop_enqueue_styles() {
	wp_enqueue_style( 'coffee-shop', plugin_dir_url( __FILE__ ) . 'assets/style.css', array(), '1.0.0' );
}
add_action( 'wp_enqueue_scripts', 'coffee_shop_enqueue_styles' );

// When the plugin is activated, add a few sample products so the menu is not empty.
function coffee_shop_activate() {
	coffee_shop_register_product_type();
	coffee_shop_add_sample_products();
}
register_activation_hook( __FILE__, 'coffee_shop_activate' );
