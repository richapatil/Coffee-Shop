<?php
/**
 * Coffee Shop Theme setup.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Let WordPress print the page <title> for us.
function coffee_theme_setup() {
	add_theme_support( 'title-tag' );
}
add_action( 'after_setup_theme', 'coffee_theme_setup' );

function coffee_theme_fonts_url() {
	return 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap';
}

// Load the Google Fonts and this theme's style.css.
function coffee_theme_enqueue_styles() {
	wp_enqueue_style( 'coffee-theme-fonts', coffee_theme_fonts_url(), array(), null );
	wp_enqueue_style( 'coffee-theme', get_stylesheet_uri(), array( 'coffee-theme-fonts' ), wp_get_theme()->get( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'coffee_theme_enqueue_styles' );

// Hide the black WordPress admin bar on the website, so it looks like a normal site even when logged in.
add_filter( 'show_admin_bar', '__return_false' );

// Shows a shortcode from the Coffee Shop plugin, or a hint if the plugin is not active.
function coffee_theme_shortcode( $tag ) {
	if ( shortcode_exists( $tag ) ) {
		return do_shortcode( '[' . $tag . ']' );
	}
	return '<p class="plugin-missing">Activate the Coffee Shop plugin to show this section.</p>';
}

// Make the staff login page match the website: shop colors and fonts from login.css.
function coffee_theme_login_styles() {
	wp_enqueue_style( 'coffee-theme-fonts', coffee_theme_fonts_url(), array(), null );
	wp_enqueue_style( 'coffee-theme-login', get_theme_file_uri( 'login.css' ), array( 'coffee-theme-fonts' ), wp_get_theme()->get( 'Version' ) );
}
add_action( 'login_enqueue_scripts', 'coffee_theme_login_styles' );

// The title above the login form shows the shop name instead of the WordPress logo, and links to the website.
function coffee_theme_login_title() {
	return '☕ ' . esc_html( get_bloginfo( 'name' ) );
}
add_filter( 'login_headertext', 'coffee_theme_login_title' );

function coffee_theme_login_title_link() {
	return home_url( '/' );
}
add_filter( 'login_headerurl', 'coffee_theme_login_title_link' );
