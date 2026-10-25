<?php
/**
 * The home page: one long page with Hero, Menu, Order, Status, About and Visit sections.
 * WordPress uses this file for the site's front page automatically.
 */

get_header();
?>

<main>
	<section class="hero">
		<div class="container">
			<p class="eyebrow">Freshly roasted &middot; Made to order</p>
			<h1>Good coffee,<br>made with care.</h1>
			<p class="hero-text">Espresso, iced lattes, tea and fresh bakes. Order ahead and we will have it ready when you walk in.</p>
			<div class="hero-buttons">
				<a class="button" href="#order">Order Now</a>
				<a class="button button-outline" href="#menu">See the Menu</a>
			</div>
		</div>
	</section>

	<section id="menu" class="section section-cream">
		<div class="container">
			<h2 class="section-title">Our Menu</h2>
			<p class="section-text">Everything is made fresh when you order.</p>
			<?php echo coffee_theme_shortcode( 'coffee_menu' ); ?>
		</div>
	</section>

	<section id="order" class="section">
		<div class="container container-narrow">
			<h2 class="section-title">Order Ahead</h2>
			<p class="section-text">Pick your drink, choose dine-in or takeaway, and skip the line.</p>
			<div class="card">
				<?php echo coffee_theme_shortcode( 'coffee_order_form' ); ?>
			</div>
		</div>
	</section>

	<section id="status" class="section section-cream">
		<div class="container">
			<h2 class="section-title">Order Status</h2>
			<p class="section-text">Find your order number. When it moves to Ready for Pickup, come and get it. Refresh the page to see updates.</p>
			<?php echo coffee_theme_shortcode( 'coffee_order_status' ); ?>
		</div>
	</section>

	<section id="about" class="section">
		<div class="container">
			<h2 class="section-title">Why People Love Us</h2>
			<div class="features">
				<div class="feature">
					<div class="feature-icon">🌱</div>
					<h3>Fresh Beans</h3>
					<p>Roasted every week in small batches for the best flavor.</p>
				</div>
				<div class="feature">
					<div class="feature-icon">🥐</div>
					<h3>Baked Daily</h3>
					<p>Muffins and pastries come out of the oven every morning.</p>
				</div>
				<div class="feature">
					<div class="feature-icon">⚡</div>
					<h3>Quick Pickup</h3>
					<p>Order online and your drink is ready when you arrive.</p>
				</div>
			</div>
		</div>
	</section>

	<section id="visit" class="section section-dark">
		<div class="container visit">
			<div>
				<h2>Visit Us</h2>
				<p>123 Main Street<br>Boston, MA 02115</p>
				<p>(111) 111-1111</p>
			</div>
			<div>
				<h3>Opening Hours</h3>
				<ul class="hours">
					<li><span>Monday &ndash; Friday</span><span>7:00 am &ndash; 7:00 pm</span></li>
					<li><span>Saturday</span><span>8:00 am &ndash; 6:00 pm</span></li>
					<li><span>Sunday</span><span>9:00 am &ndash; 4:00 pm</span></li>
				</ul>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
