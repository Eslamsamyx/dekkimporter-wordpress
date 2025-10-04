<?php
/**
 * Template Name: About Page
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
			<h1><?php esc_html_e( 'About Us', 'velocity' ); ?></h1>
			<p><?php esc_html_e( 'Passionate team of innovators dedicated to digital excellence', 'velocity' ); ?></p>
		</div>
	</section>

	<!-- Story Section -->
	<section class="section">
		<div class="container">
			<div class="grid grid-2" style="align-items: center; gap: 4rem;">
				<div>
					<h2><?php esc_html_e( 'Our Story', 'velocity' ); ?></h2>
					<p><?php esc_html_e( 'Founded in 2008, our agency has grown from a small startup to a leading digital solutions provider. We\'ve helped hundreds of businesses transform their digital presence and achieve remarkable growth.', 'velocity' ); ?></p>
					<p><?php esc_html_e( 'Our mission is to empower businesses through innovative technology and creative design. We believe in building long-term partnerships based on trust, transparency, and measurable results.', 'velocity' ); ?></p>
					<p><?php esc_html_e( 'Every project we undertake is driven by a commitment to excellence and a passion for pushing boundaries. We don\'t just build websites and apps—we create digital experiences that matter.', 'velocity' ); ?></p>
				</div>
				<div>
					<img src="https://source.unsplash.com/1200x800/?team,office" alt="<?php esc_attr_e( 'Our Team', 'velocity' ); ?>" style="border-radius: var(--border-radius); box-shadow: 0 20px 60px rgba(0,0,0,0.15);" loading="lazy">
				</div>
			</div>
		</div>
	</section>

	<!-- Values Section -->
	<section class="section bg-light">
		<div class="container">
			<div class="text-center mb-4">
				<h2><?php esc_html_e( 'Our Values', 'velocity' ); ?></h2>
				<p><?php esc_html_e( 'The principles that guide everything we do', 'velocity' ); ?></p>
			</div>

			<div class="grid grid-3">
				<div class="card">
					<div class="card-content">
						<div style="font-size: 3rem; color: var(--color-primary); margin-bottom: 1rem;">🎯</div>
						<h3 class="card-title"><?php esc_html_e( 'Excellence', 'velocity' ); ?></h3>
						<p class="card-text"><?php esc_html_e( 'We set the highest standards for quality in everything we deliver, from design to development and support.', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<div class="card-content">
						<div style="font-size: 3rem; color: var(--color-primary); margin-bottom: 1rem;">🚀</div>
						<h3 class="card-title"><?php esc_html_e( 'Innovation', 'velocity' ); ?></h3>
						<p class="card-text"><?php esc_html_e( 'We embrace emerging technologies and creative approaches to solve complex challenges in new ways.', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<div class="card-content">
						<div style="font-size: 3rem; color: var(--color-primary); margin-bottom: 1rem;">🤝</div>
						<h3 class="card-title"><?php esc_html_e( 'Collaboration', 'velocity' ); ?></h3>
						<p class="card-text"><?php esc_html_e( 'We work closely with our clients as partners, ensuring their vision becomes reality through teamwork.', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<div class="card-content">
						<div style="font-size: 3rem; color: var(--color-primary); margin-bottom: 1rem;">💡</div>
						<h3 class="card-title"><?php esc_html_e( 'Transparency', 'velocity' ); ?></h3>
						<p class="card-text"><?php esc_html_e( 'We believe in open communication, honest feedback, and clear reporting throughout every project.', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<div class="card-content">
						<div style="font-size: 3rem; color: var(--color-primary); margin-bottom: 1rem;">⚡</div>
						<h3 class="card-title"><?php esc_html_e( 'Speed', 'velocity' ); ?></h3>
						<p class="card-text"><?php esc_html_e( 'We deliver projects efficiently without compromising quality, respecting deadlines and budgets.', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<div class="card-content">
						<div style="font-size: 3rem; color: var(--color-primary); margin-bottom: 1rem;">🌱</div>
						<h3 class="card-title"><?php esc_html_e( 'Growth', 'velocity' ); ?></h3>
						<p class="card-text"><?php esc_html_e( 'We invest in continuous learning and development to stay ahead of industry trends and best practices.', 'velocity' ); ?></p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Team Section -->
	<section class="section">
		<div class="container">
			<div class="text-center mb-4">
				<h2><?php esc_html_e( 'Meet Our Team', 'velocity' ); ?></h2>
				<p><?php esc_html_e( 'The talented people behind our success', 'velocity' ); ?></p>
			</div>

			<div class="grid grid-4">
				<?php
				$team_members = array(
					array( 'name' => 'Sarah Johnson', 'role' => 'CEO & Founder', 'img' => 'portrait,woman,professional' ),
					array( 'name' => 'Michael Chen', 'role' => 'CTO', 'img' => 'portrait,man,professional' ),
					array( 'name' => 'Emily Rodriguez', 'role' => 'Creative Director', 'img' => 'portrait,woman,creative' ),
					array( 'name' => 'David Kim', 'role' => 'Lead Developer', 'img' => 'portrait,man,developer' ),
					array( 'name' => 'Jessica Martinez', 'role' => 'UX Designer', 'img' => 'portrait,woman,designer' ),
					array( 'name' => 'Ryan Thompson', 'role' => 'Marketing Manager', 'img' => 'portrait,man,marketing' ),
					array( 'name' => 'Amanda Lee', 'role' => 'Project Manager', 'img' => 'portrait,woman,manager' ),
					array( 'name' => 'James Wilson', 'role' => 'Senior Developer', 'img' => 'portrait,man,tech' ),
				);

				foreach ( $team_members as $member ) :
				?>
					<div class="card">
						<img src="https://source.unsplash.com/400x400/?<?php echo esc_attr( $member['img'] ); ?>"
						     alt="<?php echo esc_attr( $member['name'] ); ?>"
						     class="card-image"
						     style="height: 300px; object-fit: cover;"
						     loading="lazy">
						<div class="card-content text-center">
							<h4 class="card-title mb-0"><?php echo esc_html( $member['name'] ); ?></h4>
							<p class="card-text" style="color: var(--color-primary); font-weight: 600;"><?php echo esc_html( $member['role'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- Process Section -->
	<section class="section bg-light">
		<div class="container">
			<div class="text-center mb-4">
				<h2><?php esc_html_e( 'Our Process', 'velocity' ); ?></h2>
				<p><?php esc_html_e( 'How we turn ideas into reality', 'velocity' ); ?></p>
			</div>

			<div class="grid grid-2">
				<div class="card">
					<div class="card-content">
						<div style="background: var(--color-primary); color: white; width: 60px; height: 60px; border-radius: 50%; display: grid; place-items: center; font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem;">1</div>
						<h3 class="card-title"><?php esc_html_e( 'Discovery', 'velocity' ); ?></h3>
						<p class="card-text"><?php esc_html_e( 'We start by understanding your business goals, target audience, and project requirements through detailed consultations.', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<div class="card-content">
						<div style="background: var(--color-primary); color: white; width: 60px; height: 60px; border-radius: 50%; display: grid; place-items: center; font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem;">2</div>
						<h3 class="card-title"><?php esc_html_e( 'Strategy', 'velocity' ); ?></h3>
						<p class="card-text"><?php esc_html_e( 'We develop a comprehensive strategy including wireframes, user flows, and technical specifications aligned with your objectives.', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<div class="card-content">
						<div style="background: var(--color-primary); color: white; width: 60px; height: 60px; border-radius: 50%; display: grid; place-items: center; font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem;">3</div>
						<h3 class="card-title"><?php esc_html_e( 'Design', 'velocity' ); ?></h3>
						<p class="card-text"><?php esc_html_e( 'Our creative team crafts beautiful, user-centric designs that reflect your brand identity and engage your audience.', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<div class="card-content">
						<div style="background: var(--color-primary); color: white; width: 60px; height: 60px; border-radius: 50%; display: grid; place-items: center; font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem;">4</div>
						<h3 class="card-title"><?php esc_html_e( 'Development', 'velocity' ); ?></h3>
						<p class="card-text"><?php esc_html_e( 'We build robust, scalable solutions using modern technologies, following best practices and coding standards.', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<div class="card-content">
						<div style="background: var(--color-primary); color: white; width: 60px; height: 60px; border-radius: 50%; display: grid; place-items: center; font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem;">5</div>
						<h3 class="card-title"><?php esc_html_e( 'Testing', 'velocity' ); ?></h3>
						<p class="card-text"><?php esc_html_e( 'Rigorous quality assurance testing ensures everything works perfectly across devices, browsers, and scenarios.', 'velocity' ); ?></p>
					</div>
				</div>

				<div class="card">
					<div class="card-content">
						<div style="background: var(--color-primary); color: white; width: 60px; height: 60px; border-radius: 50%; display: grid; place-items: center; font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem;">6</div>
						<h3 class="card-title"><?php esc_html_e( 'Launch', 'velocity' ); ?></h3>
						<p class="card-text"><?php esc_html_e( 'We deploy your project with care and provide ongoing support to ensure continued success and growth.', 'velocity' ); ?></p>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- CTA Section -->
	<section class="section text-center">
		<div class="container">
			<h2><?php esc_html_e( 'Want to Work With Us?', 'velocity' ); ?></h2>
			<p><?php esc_html_e( 'We\'d love to hear about your project', 'velocity' ); ?></p>
			<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="btn btn-primary">
				<?php esc_html_e( 'Start a Conversation', 'velocity' ); ?>
			</a>
		</div>
	</section>

</main>

<?php get_footer(); ?>
