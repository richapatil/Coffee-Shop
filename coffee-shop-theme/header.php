<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
	<div class="container header-inner">
		<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">☕ <?php bloginfo( 'name' ); ?></a>
		<nav class="site-nav">
			<a href="<?php echo esc_url( home_url( '/#menu' ) ); ?>">Menu</a>
			<a href="<?php echo esc_url( home_url( '/#order' ) ); ?>">Order</a>
			<a href="<?php echo esc_url( home_url( '/#status' ) ); ?>">Status</a>
			<a href="<?php echo esc_url( home_url( '/#about' ) ); ?>">About</a>
			<a href="<?php echo esc_url( home_url( '/#visit' ) ); ?>">Visit</a>
		</nav>
	</div>
</header>
