<?php
/**
 * Template Name: Home Page
 * Template Post Type: page
 *
 * @package Velocity
 * @since 1.0.0
 */

get_header(); ?>

<main id="main" class="site-main">

	<!-- Hero Section -->
	<section class="hero">
		<div class="hero-content">
			<h1 class="fade-in-up"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
			<p class="fade-in-up">Transform your digital presence with cutting-edge design and development solutions that drive results.</p>
			<div class="fade-in-up">
				<a href="#services" class="btn btn-primary"><?php esc_html_e( 'Our Services', 'velocity' ); ?></a>
				<a href="#portfolio" class="btn btn-outline"><?php esc_html_e( 'View Work', 'velocity' ); ?></a>
			</div>
		</div>
	</section>

	<!-- Services Section -->
	<section id="services" class="section bg-light">
		<div class="container">
			<div class="text-center mb-4">
				<h2><?php esc_html_e( 'Our Services', 'velocity' ); ?></h2>
				<p><?php esc_html_e( 'Comprehensive digital solutions tailored to your business needs', 'velocity' ); ?></p>
			</div>

			<div class="grid grid-3">
				<div class="card">
					<img src="https://source.unsplash.com/800x600/?web,design" alt="<?php esc_attr_e( 'Web Design', 'velocity' ); ?>" class="card-image" loading="lazy">
					<div class="card-content">
						<h3 class="card-title"><?php esc_html_e( 'Web Design', 'velocity' ); ?></h3>
						<p class="card-text"><?php esc_html_e( 'Beautiful, responsive websites that captivate your audience and deliver exceptional user experiences across all devices.', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<img src="https://source.unsplash.com/800x600/?development,code" alt="<?php esc_attr_e( 'Development', 'velocity' ); ?>" class="card-image" loading="lazy">
					<div class="card-content">
						<h3 class="card-title"><?php esc_html_e( 'Development', 'velocity' ); ?></h3>
						<p class="card-text"><?php esc_html_e( 'Robust, scalable applications built with modern technologies and best practices for optimal performance.', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<img src="https://source.unsplash.com/800x600/?branding,strategy" alt="<?php esc_attr_e( 'Branding', 'velocity' ); ?>" class="card-image" loading="lazy">
					<div class="card-content">
						<h3 class="card-title"><?php esc_html_e( 'Branding', 'velocity' ); ?></h3>
						<p class="card-text"><?php esc_html_e( 'Strategic brand identity development that resonates with your target audience and sets you apart.', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<img src="https://source.unsplash.com/800x600/?marketing,digital" alt="<?php esc_attr_e( 'Digital Marketing', 'velocity' ); ?>" class="card-image" loading="lazy">
					<div class="card-content">
						<h3 class="card-title"><?php esc_html_e( 'Digital Marketing', 'velocity' ); ?></h3>
						<p class="card-text"><?php esc_html_e( 'Data-driven marketing campaigns that boost visibility, engagement, and conversions for your business.', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<img src="https://source.unsplash.com/800x600/?mobile,app" alt="<?php esc_attr_e( 'Mobile Apps', 'velocity' ); ?>" class="card-image" loading="lazy">
					<div class="card-content">
						<h3 class="card-title"><?php esc_html_e( 'Mobile Apps', 'velocity' ); ?></h3>
						<p class="card-text"><?php esc_html_e( 'Native and cross-platform mobile applications that provide seamless experiences for iOS and Android users.', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<img src="https://source.unsplash.com/800x600/?consulting,strategy" alt="<?php esc_attr_e( 'Consulting', 'velocity' ); ?>" class="card-image" loading="lazy">
					<div class="card-content">
						<h3 class="card-title"><?php esc_html_e( 'Consulting', 'velocity' ); ?></h3>
						<p class="card-text"><?php esc_html_e( 'Expert guidance on technology strategy, digital transformation, and process optimization for growth.', 'velocity' ); ?></p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Portfolio Section -->
	<section id="portfolio" class="section">
		<div class="container">
			<div class="text-center mb-4">
				<h2><?php esc_html_e( 'Featured Work', 'velocity' ); ?></h2>
				<p><?php esc_html_e( 'Explore our latest projects and success stories', 'velocity' ); ?></p>
			</div>

			<div class="grid grid-2">
				<?php
				$portfolio_items = array(
					array(
						'title' => 'E-Commerce Platform',
						'desc'  => 'A modern e-commerce solution with seamless checkout experience and inventory management.',
						'img'   => 'ecommerce,shop'
					),
					array(
						'title' => 'SaaS Dashboard',
						'desc'  => 'Intuitive analytics dashboard with real-time data visualization and reporting tools.',
						'img'   => 'dashboard,analytics'
					),
					array(
						'title' => 'Corporate Website',
						'desc'  => 'Professional corporate website with CMS integration and multilingual support.',
						'img'   => 'business,corporate'
					),
					array(
						'title' => 'Mobile Banking App',
						'desc'  => 'Secure mobile banking application with biometric authentication and instant transfers.',
						'img'   => 'finance,banking'
					),
				);

				foreach ( $portfolio_items as $index => $item ) :
				?>
					<div class="card">
						<img src="https://source.unsplash.com/800x600/?<?php echo esc_attr( $item['img'] ); ?>"
						     alt="<?php echo esc_attr( $item['title'] ); ?>"
						     class="card-image"
						     loading="lazy">
						<div class="card-content">
							<h3 class="card-title"><?php echo esc_html( $item['title'] ); ?></h3>
							<p class="card-text"><?php echo esc_html( $item['desc'] ); ?></p>
							<a href="#" class="btn btn-primary"><?php esc_html_e( 'View Project', 'velocity' ); ?></a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- Stats Section -->
	<section class="section bg-primary text-white text-center">
		<div class="container">
			<div class="grid grid-4">
				<div>
					<h3 style="color: var(--color-accent); font-size: 3rem; margin-bottom: 0.5rem;">500+</h3>
					<p><?php esc_html_e( 'Projects Completed', 'velocity' ); ?></p>
				</div>
				<div>
					<h3 style="color: var(--color-accent); font-size: 3rem; margin-bottom: 0.5rem;">200+</h3>
					<p><?php esc_html_e( 'Happy Clients', 'velocity' ); ?></p>
				</div>
				<div>
					<h3 style="color: var(--color-accent); font-size: 3rem; margin-bottom: 0.5rem;">15+</h3>
					<p><?php esc_html_e( 'Years Experience', 'velocity' ); ?></p>
				</div>
				<div>
					<h3 style="color: var(--color-accent); font-size: 3rem; margin-bottom: 0.5rem;">50+</h3>
					<p><?php esc_html_e( 'Team Members', 'velocity' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<!-- CTA Section -->
	<section class="section text-center">
		<div class="container">
			<h2><?php esc_html_e( 'Ready to Start Your Project?', 'velocity' ); ?></h2>
			<p><?php esc_html_e( 'Let\'s build something amazing together', 'velocity' ); ?></p>
			<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="btn btn-primary">
				<?php esc_html_e( 'Get in Touch', 'velocity' ); ?>
			</a>
		</div>
	</section>

</main>

<?php get_footer(); ?>
