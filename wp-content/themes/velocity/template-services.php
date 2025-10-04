<?php
/**
 * Template Name: Services Page
 * Template Post Type: page
 *
 * @package Velocity
 * @since 1.0.0
 */

get_header(); ?>

<main id="main" class="site-main">

	<!-- Hero Section -->
	<section class="hero" style="min-height: 400px;">
		<div class="hero-content">
			<h1><?php esc_html_e( 'Our Services', 'velocity' ); ?></h1>
			<p><?php esc_html_e( 'Comprehensive digital solutions to grow your business', 'velocity' ); ?></p>
		</div>
	</section>

	<!-- Services Overview -->
	<section class="section">
		<div class="container">
			<div class="text-center mb-4">
				<h2><?php esc_html_e( 'What We Do', 'velocity' ); ?></h2>
				<p><?php esc_html_e( 'From concept to launch, we handle every aspect of your digital presence', 'velocity' ); ?></p>
			</div>

			<div class="grid grid-2" style="gap: 4rem;">

				<!-- Web Design -->
				<div style="display: flex; gap: 2rem; align-items: flex-start;">
					<div>
						<img src="https://source.unsplash.com/600x600/?web,design,ui" alt="<?php esc_attr_e( 'Web Design', 'velocity' ); ?>" style="border-radius: var(--border-radius); box-shadow: 0 10px 30px rgba(0,0,0,0.1);" loading="lazy">
					</div>
					<div>
						<h3><?php esc_html_e( 'Web Design', 'velocity' ); ?></h3>
						<p><?php esc_html_e( 'Create stunning, user-friendly websites that convert visitors into customers. Our design process focuses on aesthetics, usability, and brand consistency.', 'velocity' ); ?></p>
						<ul style="list-style: none; padding: 0;">
							<li style="padding: 0.5rem 0; border-bottom: 1px solid var(--color-light);">✓ <?php esc_html_e( 'Responsive Design', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0; border-bottom: 1px solid var(--color-light);">✓ <?php esc_html_e( 'UI/UX Design', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0; border-bottom: 1px solid var(--color-light);">✓ <?php esc_html_e( 'Wireframing & Prototyping', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0; border-bottom: 1px solid var(--color-light);">✓ <?php esc_html_e( 'Design Systems', 'velocity' ); ?></li>
						</ul>
					</div>
				</div>

				<!-- Web Development -->
				<div style="display: flex; gap: 2rem; align-items: flex-start;">
					<div>
						<img src="https://source.unsplash.com/600x600/?coding,development,programming" alt="<?php esc_attr_e( 'Web Development', 'velocity' ); ?>" style="border-radius: var(--border-radius); box-shadow: 0 10px 30px rgba(0,0,0,0.1);" loading="lazy">
					</div>
					<div>
						<h3><?php esc_html_e( 'Web Development', 'velocity' ); ?></h3>
						<p><?php esc_html_e( 'Build powerful, scalable web applications with cutting-edge technologies. We specialize in custom development that meets your exact requirements.', 'velocity' ); ?></p>
						<ul style="list-style: none; padding: 0;">
							<li style="padding: 0.5rem 0; border-bottom: 1px solid var(--color-light);">✓ <?php esc_html_e( 'Custom WordPress Development', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0; border-bottom: 1px solid var(--color-light);">✓ <?php esc_html_e( 'E-Commerce Solutions', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0; border-bottom: 1px solid var(--color-light);">✓ <?php esc_html_e( 'Web Applications', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0; border-bottom: 1px solid var(--color-light);">✓ <?php esc_html_e( 'API Development', 'velocity' ); ?></li>
						</ul>
					</div>
				</div>

				<!-- Branding -->
				<div style="display: flex; gap: 2rem; align-items: flex-start;">
					<div>
						<img src="https://source.unsplash.com/600x600/?branding,identity,logo" alt="<?php esc_attr_e( 'Branding', 'velocity' ); ?>" style="border-radius: var(--border-radius); box-shadow: 0 10px 30px rgba(0,0,0,0.1);" loading="lazy">
					</div>
					<div>
						<h3><?php esc_html_e( 'Brand Identity', 'velocity' ); ?></h3>
						<p><?php esc_html_e( 'Develop a memorable brand that resonates with your audience. From logo design to comprehensive brand guidelines, we create identities that stand out.', 'velocity' ); ?></p>
						<ul style="list-style: none; padding: 0;">
							<li style="padding: 0.5rem 0; border-bottom: 1px solid var(--color-light);">✓ <?php esc_html_e( 'Logo Design', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0; border-bottom: 1px solid var(--color-light);">✓ <?php esc_html_e( 'Brand Guidelines', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0; border-bottom: 1px solid var(--color-light);">✓ <?php esc_html_e( 'Visual Identity', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0; border-bottom: 1px solid var(--color-light);">✓ <?php esc_html_e( 'Brand Strategy', 'velocity' ); ?></li>
						</ul>
					</div>
				</div>

				<!-- Digital Marketing -->
				<div style="display: flex; gap: 2rem; align-items: flex-start;">
					<div>
						<img src="https://source.unsplash.com/600x600/?marketing,social,media" alt="<?php esc_attr_e( 'Digital Marketing', 'velocity' ); ?>" style="border-radius: var(--border-radius); box-shadow: 0 10px 30px rgba(0,0,0,0.1);" loading="lazy">
					</div>
					<div>
						<h3><?php esc_html_e( 'Digital Marketing', 'velocity' ); ?></h3>
						<p><?php esc_html_e( 'Drive traffic, generate leads, and increase conversions with data-driven marketing strategies tailored to your business goals.', 'velocity' ); ?></p>
						<ul style="list-style: none; padding: 0;">
							<li style="padding: 0.5rem 0; border-bottom: 1px solid var(--color-light);">✓ <?php esc_html_e( 'SEO Optimization', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0; border-bottom: 1px solid var(--color-light);">✓ <?php esc_html_e( 'Content Marketing', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0; border-bottom: 1px solid var(--color-light);">✓ <?php esc_html_e( 'Social Media Marketing', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0; border-bottom: 1px solid var(--color-light);">✓ <?php esc_html_e( 'Email Campaigns', 'velocity' ); ?></li>
						</ul>
					</div>
				</div>

			</div>
		</div>
	</section>

	<!-- Service Packages -->
	<section class="section bg-light">
		<div class="container">
			<div class="text-center mb-4">
				<h2><?php esc_html_e( 'Service Packages', 'velocity' ); ?></h2>
				<p><?php esc_html_e( 'Choose the package that fits your needs', 'velocity' ); ?></p>
			</div>

			<div class="grid grid-3">
				<!-- Starter -->
				<div class="card">
					<div class="card-content text-center">
						<h3 class="card-title"><?php esc_html_e( 'Starter', 'velocity' ); ?></h3>
						<div style="font-size: 3rem; font-weight: 700; color: var(--color-primary); margin: 1rem 0;">
							$2,999
						</div>
						<p class="card-text"><?php esc_html_e( 'Perfect for small businesses and startups', 'velocity' ); ?></p>
						<ul style="list-style: none; padding: 0; text-align: left; margin: 2rem 0;">
							<li style="padding: 0.5rem 0;">✓ <?php esc_html_e( '5-Page Website', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0;">✓ <?php esc_html_e( 'Responsive Design', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0;">✓ <?php esc_html_e( 'Basic SEO', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0;">✓ <?php esc_html_e( 'Contact Form', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0;">✓ <?php esc_html_e( '30 Days Support', 'velocity' ); ?></li>
						</ul>
						<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="btn btn-primary">
							<?php esc_html_e( 'Get Started', 'velocity' ); ?>
						</a>
					</div>
				</div>

				<!-- Professional -->
				<div class="card" style="border: 3px solid var(--color-primary);">
					<div class="card-content text-center">
						<div style="background: var(--color-primary); color: white; padding: 0.5rem; margin: -2rem -2rem 1rem; border-radius: var(--border-radius) var(--border-radius) 0 0;">
							<?php esc_html_e( 'MOST POPULAR', 'velocity' ); ?>
						</div>
						<h3 class="card-title"><?php esc_html_e( 'Professional', 'velocity' ); ?></h3>
						<div style="font-size: 3rem; font-weight: 700; color: var(--color-primary); margin: 1rem 0;">
							$5,999
						</div>
						<p class="card-text"><?php esc_html_e( 'Ideal for growing businesses', 'velocity' ); ?></p>
						<ul style="list-style: none; padding: 0; text-align: left; margin: 2rem 0;">
							<li style="padding: 0.5rem 0;">✓ <?php esc_html_e( '10-Page Website', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0;">✓ <?php esc_html_e( 'Custom Design', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0;">✓ <?php esc_html_e( 'Advanced SEO', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0;">✓ <?php esc_html_e( 'CMS Integration', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0;">✓ <?php esc_html_e( 'E-Commerce Ready', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0;">✓ <?php esc_html_e( '90 Days Support', 'velocity' ); ?></li>
						</ul>
						<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="btn btn-primary">
							<?php esc_html_e( 'Get Started', 'velocity' ); ?>
						</a>
					</div>
				</div>

				<!-- Enterprise -->
				<div class="card">
					<div class="card-content text-center">
						<h3 class="card-title"><?php esc_html_e( 'Enterprise', 'velocity' ); ?></h3>
						<div style="font-size: 3rem; font-weight: 700; color: var(--color-primary); margin: 1rem 0;">
							$15,999+
						</div>
						<p class="card-text"><?php esc_html_e( 'For large-scale projects', 'velocity' ); ?></p>
						<ul style="list-style: none; padding: 0; text-align: left; margin: 2rem 0;">
							<li style="padding: 0.5rem 0;">✓ <?php esc_html_e( 'Unlimited Pages', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0;">✓ <?php esc_html_e( 'Custom Development', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0;">✓ <?php esc_html_e( 'Premium SEO', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0;">✓ <?php esc_html_e( 'API Integration', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0;">✓ <?php esc_html_e( 'Advanced Features', 'velocity' ); ?></li>
							<li style="padding: 0.5rem 0;">✓ <?php esc_html_e( '1 Year Support', 'velocity' ); ?></li>
						</ul>
						<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="btn btn-primary">
							<?php esc_html_e( 'Contact Us', 'velocity' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Additional Services -->
	<section class="section">
		<div class="container">
			<div class="text-center mb-4">
				<h2><?php esc_html_e( 'Additional Services', 'velocity' ); ?></h2>
				<p><?php esc_html_e( 'Enhance your package with these add-ons', 'velocity' ); ?></p>
			</div>

			<div class="grid grid-4">
				<div class="card">
					<div class="card-content text-center">
						<div style="font-size: 2.5rem; margin-bottom: 1rem;">📱</div>
						<h4 class="card-title"><?php esc_html_e( 'Mobile App', 'velocity' ); ?></h4>
						<p class="card-text"><?php esc_html_e( 'Native iOS & Android applications', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<div class="card-content text-center">
						<div style="font-size: 2.5rem; margin-bottom: 1rem;">🛒</div>
						<h4 class="card-title"><?php esc_html_e( 'E-Commerce', 'velocity' ); ?></h4>
						<p class="card-text"><?php esc_html_e( 'Full online store setup', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<div class="card-content text-center">
						<div style="font-size: 2.5rem; margin-bottom: 1rem;">🔒</div>
						<h4 class="card-title"><?php esc_html_e( 'Security', 'velocity' ); ?></h4>
						<p class="card-text"><?php esc_html_e( 'Advanced security & SSL', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<div class="card-content text-center">
						<div style="font-size: 2.5rem; margin-bottom: 1rem;">📊</div>
						<h4 class="card-title"><?php esc_html_e( 'Analytics', 'velocity' ); ?></h4>
						<p class="card-text"><?php esc_html_e( 'Custom tracking & reporting', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<div class="card-content text-center">
						<div style="font-size: 2.5rem; margin-bottom: 1rem;">⚡</div>
						<h4 class="card-title"><?php esc_html_e( 'Performance', 'velocity' ); ?></h4>
						<p class="card-text"><?php esc_html_e( 'Speed optimization & CDN', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<div class="card-content text-center">
						<div style="font-size: 2.5rem; margin-bottom: 1rem;">🔧</div>
						<h4 class="card-title"><?php esc_html_e( 'Maintenance', 'velocity' ); ?></h4>
						<p class="card-text"><?php esc_html_e( 'Monthly updates & backups', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<div class="card-content text-center">
						<div style="font-size: 2.5rem; margin-bottom: 1rem;">🎓</div>
						<h4 class="card-title"><?php esc_html_e( 'Training', 'velocity' ); ?></h4>
						<p class="card-text"><?php esc_html_e( 'Team training sessions', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<div class="card-content text-center">
						<div style="font-size: 2.5rem; margin-bottom: 1rem;">🌐</div>
						<h4 class="card-title"><?php esc_html_e( 'Migration', 'velocity' ); ?></h4>
						<p class="card-text"><?php esc_html_e( 'Platform migration service', 'velocity' ); ?></p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- CTA Section -->
	<section class="section bg-primary text-white text-center">
		<div class="container">
			<h2 class="text-white"><?php esc_html_e( 'Ready to Get Started?', 'velocity' ); ?></h2>
			<p><?php esc_html_e( 'Let\'s discuss your project requirements', 'velocity' ); ?></p>
			<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="btn btn-secondary">
				<?php esc_html_e( 'Request a Quote', 'velocity' ); ?>
			</a>
		</div>
	</section>

</main>

<?php get_footer(); ?>
