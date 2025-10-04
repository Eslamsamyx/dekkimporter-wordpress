<?php
/**
 * Template Name: Portfolio Page
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
			<h1><?php esc_html_e( 'Our Portfolio', 'velocity' ); ?></h1>
			<p><?php esc_html_e( 'Explore our work and see how we help businesses succeed', 'velocity' ); ?></p>
		</div>
	</section>

	<!-- Portfolio Grid -->
	<section class="section">
		<div class="container">
			<div class="text-center mb-4">
				<h2><?php esc_html_e( 'Featured Projects', 'velocity' ); ?></h2>
				<p><?php esc_html_e( 'A showcase of our recent work across various industries', 'velocity' ); ?></p>
			</div>

			<div class="grid grid-3">
				<?php
				$portfolio_projects = array(
					array(
						'title'    => 'TechFlow SaaS Platform',
						'category' => 'Web Development',
						'desc'     => 'Enterprise SaaS platform with real-time analytics and team collaboration features.',
						'img'      => 'saas,dashboard,software'
					),
					array(
						'title'    => 'Urban Eats Restaurant',
						'category' => 'Web Design',
						'desc'     => 'Modern restaurant website with online ordering and reservation system.',
						'img'      => 'restaurant,food,dining'
					),
					array(
						'title'    => 'FitLife Wellness App',
						'category' => 'Mobile App',
						'desc'     => 'Fitness tracking app with workout plans and nutrition guidance.',
						'img'      => 'fitness,gym,workout'
					),
					array(
						'title'    => 'Luxe Fashion Store',
						'category' => 'E-Commerce',
						'desc'     => 'Premium fashion e-commerce with advanced filtering and wishlist features.',
						'img'      => 'fashion,shopping,style'
					),
					array(
						'title'    => 'Summit Real Estate',
						'category' => 'Web Development',
						'desc'     => 'Property listing platform with virtual tours and mortgage calculator.',
						'img'      => 'realestate,property,house'
					),
					array(
						'title'    => 'GreenLeaf Organics',
						'category' => 'Branding',
						'desc'     => 'Complete brand identity for organic food company including logo and packaging.',
						'img'      => 'organic,nature,green'
					),
					array(
						'title'    => 'CloudSync Enterprise',
						'category' => 'Web Application',
						'desc'     => 'Cloud storage solution with file sharing and team collaboration.',
						'img'      => 'cloud,technology,data'
					),
					array(
						'title'    => 'Travel Wanderlust',
						'category' => 'Web Design',
						'desc'     => 'Travel booking platform with destination guides and itinerary planning.',
						'img'      => 'travel,vacation,adventure'
					),
					array(
						'title'    => 'HealthCare Plus',
						'category' => 'Web Development',
						'desc'     => 'Medical appointment booking system with telemedicine capabilities.',
						'img'      => 'healthcare,medical,hospital'
					),
					array(
						'title'    => 'EduLearn Online',
						'category' => 'E-Learning',
						'desc'     => 'Online learning platform with courses, quizzes, and certification.',
						'img'      => 'education,learning,school'
					),
					array(
						'title'    => 'AutoPro Garage',
						'category' => 'Web Design',
						'desc'     => 'Auto repair shop website with service booking and vehicle diagnostics.',
						'img'      => 'automotive,car,mechanic'
					),
					array(
						'title'    => 'FinanceHub Portal',
						'category' => 'Web Application',
						'desc'     => 'Financial management platform with budgeting and investment tracking.',
						'img'      => 'finance,money,banking'
					),
				);

				foreach ( $portfolio_projects as $project ) :
				?>
					<div class="card">
						<img src="https://source.unsplash.com/800x600/?<?php echo esc_attr( $project['img'] ); ?>"
						     alt="<?php echo esc_attr( $project['title'] ); ?>"
						     class="card-image"
						     loading="lazy">
						<div class="card-content">
							<div style="display: inline-block; background: var(--color-primary); color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem;">
								<?php echo esc_html( $project['category'] ); ?>
							</div>
							<h3 class="card-title"><?php echo esc_html( $project['title'] ); ?></h3>
							<p class="card-text"><?php echo esc_html( $project['desc'] ); ?></p>
							<a href="#" class="btn btn-primary"><?php esc_html_e( 'View Case Study', 'velocity' ); ?></a>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- Case Study Highlight -->
	<section class="section bg-light">
		<div class="container">
			<div class="grid grid-2" style="gap: 4rem; align-items: center;">
				<div>
					<img src="https://source.unsplash.com/1200x800/?computer,workspace,design"
					     alt="<?php esc_attr_e( 'Case Study', 'velocity' ); ?>"
					     style="border-radius: var(--border-radius); box-shadow: 0 20px 60px rgba(0,0,0,0.15);"
					     loading="lazy">
				</div>
				<div>
					<div style="display: inline-block; background: var(--color-accent); color: var(--color-dark); padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.875rem; font-weight: 700; margin-bottom: 1rem;">
						<?php esc_html_e( 'FEATURED CASE STUDY', 'velocity' ); ?>
					</div>
					<h2><?php esc_html_e( 'TechFlow: Revolutionizing Team Collaboration', 'velocity' ); ?></h2>
					<p><?php esc_html_e( 'We partnered with TechFlow to build a comprehensive SaaS platform that transformed how remote teams collaborate. The platform now serves over 50,000 active users across 120 countries.', 'velocity' ); ?></p>

					<div class="grid grid-3" style="margin: 2rem 0;">
						<div>
							<h4 style="color: var(--color-primary); font-size: 2rem; margin-bottom: 0.25rem;">300%</h4>
							<p style="font-size: 0.875rem; margin: 0;"><?php esc_html_e( 'User Growth', 'velocity' ); ?></p>
						</div>
						<div>
							<h4 style="color: var(--color-primary); font-size: 2rem; margin-bottom: 0.25rem;">50K+</h4>
							<p style="font-size: 0.875rem; margin: 0;"><?php esc_html_e( 'Active Users', 'velocity' ); ?></p>
						</div>
						<div>
							<h4 style="color: var(--color-primary); font-size: 2rem; margin-bottom: 0.25rem;">4.9/5</h4>
							<p style="font-size: 0.875rem; margin: 0;"><?php esc_html_e( 'User Rating', 'velocity' ); ?></p>
						</div>
					</div>

					<h4><?php esc_html_e( 'Key Features:', 'velocity' ); ?></h4>
					<ul style="list-style: none; padding: 0;">
						<li style="padding: 0.5rem 0;">✓ <?php esc_html_e( 'Real-time collaboration tools', 'velocity' ); ?></li>
						<li style="padding: 0.5rem 0;">✓ <?php esc_html_e( 'Advanced analytics dashboard', 'velocity' ); ?></li>
						<li style="padding: 0.5rem 0;">✓ <?php esc_html_e( 'Integrated video conferencing', 'velocity' ); ?></li>
						<li style="padding: 0.5rem 0;">✓ <?php esc_html_e( 'Project management suite', 'velocity' ); ?></li>
					</ul>

					<a href="#" class="btn btn-primary"><?php esc_html_e( 'Read Full Case Study', 'velocity' ); ?></a>
				</div>
			</div>
		</div>
	</section>

	<!-- Client Testimonials -->
	<section class="section">
		<div class="container">
			<div class="text-center mb-4">
				<h2><?php esc_html_e( 'What Clients Say', 'velocity' ); ?></h2>
				<p><?php esc_html_e( 'Real feedback from real clients', 'velocity' ); ?></p>
			</div>

			<div class="grid grid-3">
				<?php
				$testimonials = array(
					array(
						'name'    => 'Sarah Mitchell',
						'company' => 'TechFlow CEO',
						'text'    => 'Working with this team was an absolute pleasure. They delivered beyond our expectations and the platform they built has transformed our business.',
						'img'     => 'portrait,woman,ceo'
					),
					array(
						'name'    => 'John Anderson',
						'company' => 'Urban Eats Owner',
						'text'    => 'The website they designed for us is stunning and has significantly increased our online orders. Customer support is exceptional.',
						'img'     => 'portrait,man,business'
					),
					array(
						'name'    => 'Emily Chen',
						'company' => 'FitLife Founder',
						'text'    => 'From concept to launch, they were professional and attentive. Our app has received incredible feedback from users.',
						'img'     => 'portrait,woman,founder'
					),
					array(
						'name'    => 'Michael Torres',
						'company' => 'Luxe Fashion Director',
						'text'    => 'They understood our brand vision perfectly. The e-commerce platform is fast, beautiful, and conversion-optimized.',
						'img'     => 'portrait,man,director'
					),
					array(
						'name'    => 'Lisa Johnson',
						'company' => 'Summit Real Estate',
						'text'    => 'The property listing platform exceeded all our requirements. Virtual tours and the mortgage calculator are game-changers.',
						'img'     => 'portrait,woman,professional'
					),
					array(
						'name'    => 'David Kim',
						'company' => 'GreenLeaf Marketing',
						'text'    => 'Our brand identity is now cohesive and memorable. The logo and packaging designs are absolutely perfect.',
						'img'     => 'portrait,man,marketing'
					),
				);

				foreach ( $testimonials as $testimonial ) :
				?>
					<div class="card">
						<div class="card-content">
							<div style="font-size: 3rem; color: var(--color-primary); line-height: 1; margin-bottom: 1rem;">"</div>
							<p class="card-text" style="font-style: italic;"><?php echo esc_html( $testimonial['text'] ); ?></p>
							<div style="display: flex; align-items: center; gap: 1rem; margin-top: 1.5rem;">
								<img src="https://source.unsplash.com/100x100/?<?php echo esc_attr( $testimonial['img'] ); ?>"
								     alt="<?php echo esc_attr( $testimonial['name'] ); ?>"
								     style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;"
								     loading="lazy">
								<div>
									<h5 style="margin: 0; color: var(--color-dark);"><?php echo esc_html( $testimonial['name'] ); ?></h5>
									<p style="margin: 0; font-size: 0.875rem; color: var(--color-text-light);"><?php echo esc_html( $testimonial['company'] ); ?></p>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- Results Section -->
	<section class="section bg-primary text-white">
		<div class="container">
			<div class="text-center mb-4">
				<h2 class="text-white"><?php esc_html_e( 'Proven Results', 'velocity' ); ?></h2>
				<p><?php esc_html_e( 'Numbers that speak for themselves', 'velocity' ); ?></p>
			</div>

			<div class="grid grid-4">
				<div class="text-center">
					<h3 style="color: var(--color-accent); font-size: 3.5rem; margin-bottom: 0.5rem;">500+</h3>
					<p><?php esc_html_e( 'Projects Delivered', 'velocity' ); ?></p>
				</div>
				<div class="text-center">
					<h3 style="color: var(--color-accent); font-size: 3.5rem; margin-bottom: 0.5rem;">98%</h3>
					<p><?php esc_html_e( 'Client Satisfaction', 'velocity' ); ?></p>
				</div>
				<div class="text-center">
					<h3 style="color: var(--color-accent); font-size: 3.5rem; margin-bottom: 0.5rem;">250%</h3>
					<p><?php esc_html_e( 'Avg. ROI Increase', 'velocity' ); ?></p>
				</div>
				<div class="text-center">
					<h3 style="color: var(--color-accent); font-size: 3.5rem; margin-bottom: 0.5rem;">24/7</h3>
					<p><?php esc_html_e( 'Support Available', 'velocity' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<!-- CTA Section -->
	<section class="section text-center">
		<div class="container">
			<h2><?php esc_html_e( 'Ready to Start Your Success Story?', 'velocity' ); ?></h2>
			<p><?php esc_html_e( 'Let\'s create something amazing together', 'velocity' ); ?></p>
			<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" class="btn btn-primary">
				<?php esc_html_e( 'Start Your Project', 'velocity' ); ?>
			</a>
		</div>
	</section>

</main>

<?php get_footer(); ?>
