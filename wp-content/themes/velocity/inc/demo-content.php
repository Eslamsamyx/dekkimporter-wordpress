<?php
/**
 * Demo Content Seeder
 *
 * Creates sample content to showcase theme capabilities
 *
 * @package Velocity
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if demo content has already been installed
 */
function velocity_is_demo_installed() {
	return get_option( 'velocity_demo_installed', false );
}

/**
 * Main function to install demo content
 */
function velocity_install_demo_content() {
	// Check if already installed
	if ( velocity_is_demo_installed() ) {
		return;
	}

	// Install demo pages
	velocity_create_demo_pages();

	// Install demo posts
	velocity_create_demo_posts();

	// Create navigation menus
	velocity_create_demo_menus();

	// Set up widgets
	velocity_create_demo_widgets();

	// Set homepage
	velocity_set_demo_homepage();

	// Mark as installed
	update_option( 'velocity_demo_installed', true );
	update_option( 'velocity_demo_installed_date', current_time( 'mysql' ) );
}

/**
 * Create Demo Pages
 */
function velocity_create_demo_pages() {
	$pages = array(
		array(
			'title'    => 'Home',
			'template' => 'template-home.php',
			'content'  => velocity_get_home_page_content(),
		),
		array(
			'title'    => 'About Us',
			'template' => 'template-about.php',
			'content'  => velocity_get_about_page_content(),
		),
		array(
			'title'    => 'Our Services',
			'template' => 'template-services.php',
			'content'  => velocity_get_services_page_content(),
		),
		array(
			'title'    => 'Portfolio',
			'template' => 'template-portfolio.php',
			'content'  => velocity_get_portfolio_page_content(),
		),
		array(
			'title'    => 'Contact Us',
			'template' => 'template-contact.php',
			'content'  => velocity_get_contact_page_content(),
		),
		array(
			'title'    => 'Blog',
			'template' => '',
			'content'  => '<p>Welcome to our blog! Here you\'ll find the latest news, insights, and updates from our team of experts. We cover topics ranging from web design and development to digital marketing and branding strategies.</p><p>Stay tuned for regular updates and feel free to engage with our content!</p>',
		),
	);

	foreach ( $pages as $page_data ) {
		// Check if page already exists
		$existing_page = get_page_by_title( $page_data['title'] );

		if ( $existing_page ) {
			continue;
		}

		// Create page
		$page_id = wp_insert_post( array(
			'post_title'   => $page_data['title'],
			'post_content' => $page_data['content'],
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => 1,
		) );

		// Set page template
		if ( $page_id && ! empty( $page_data['template'] ) ) {
			update_post_meta( $page_id, '_wp_page_template', $page_data['template'] );
		}

		// Store page ID for later use
		if ( $page_id ) {
			update_option( 'velocity_demo_page_' . sanitize_title( $page_data['title'] ), $page_id );
		}
	}
}

/**
 * Create Demo Blog Posts
 */
function velocity_create_demo_posts() {
	$posts = array(
		array(
			'title'      => 'The Future of Web Design: Trends to Watch in 2024',
			'content'    => '<p>Web design is constantly evolving, and staying ahead of the curve is crucial for creating engaging digital experiences. As we move through 2024, several exciting trends are shaping the future of web design.</p>

<h2>1. Minimalist Design with Bold Typography</h2>
<p>Less is more continues to be a dominant philosophy. Clean layouts paired with striking typography create powerful visual hierarchies that guide users effortlessly through content. This approach not only looks modern but also improves user experience significantly.</p>

<h2>2. Dark Mode Everything</h2>
<p>Dark mode has moved from a nice-to-have feature to an expected standard. Users appreciate the reduced eye strain and battery savings, while designers love the opportunity to create dramatic, high-contrast interfaces.</p>

<h2>3. Micro-Interactions and Animations</h2>
<p>Subtle animations and micro-interactions add personality to websites without overwhelming users. From hover effects to loading animations, these small details make interfaces feel alive and responsive.</p>

<h2>4. AI-Powered Personalization</h2>
<p>Artificial intelligence is enabling websites to adapt to individual user preferences in real-time, creating unique experiences for each visitor.</p>

<p>The future of web design is exciting, combining aesthetic innovation with technological advancement to create better user experiences.</p>',
			'category'   => 'Web Design',
			'tags'       => array( 'Design', 'Trends', 'UX' ),
			'image_keyword' => 'web,design,modern',
		),
		array(
			'title'      => '10 Essential Tips for Building High-Performance Websites',
			'content'    => '<p>Website performance directly impacts user experience, SEO rankings, and conversion rates. Here are ten essential tips to ensure your website loads quickly and runs smoothly.</p>

<h2>1. Optimize Your Images</h2>
<p>Images often account for the majority of a page\'s file size. Use modern formats like WebP, compress images before upload, and implement lazy loading to improve load times significantly.</p>

<h2>2. Minimize HTTP Requests</h2>
<p>Every file your page loads requires an HTTP request. Combine CSS files, reduce scripts, and use CSS sprites to minimize requests and speed up your site.</p>

<h2>3. Enable Browser Caching</h2>
<p>Browser caching stores static files locally, reducing load times for returning visitors. Configure your server to leverage browser caching effectively.</p>

<h2>4. Use a Content Delivery Network (CDN)</h2>
<p>CDNs distribute your content across multiple servers worldwide, ensuring users download files from the closest location.</p>

<h2>5. Minify CSS, JavaScript, and HTML</h2>
<p>Remove unnecessary characters from your code to reduce file sizes without affecting functionality.</p>

<p>Implementing these strategies will dramatically improve your website\'s performance and user satisfaction.</p>',
			'category'   => 'Development',
			'tags'       => array( 'Performance', 'Optimization', 'Speed' ),
			'image_keyword' => 'coding,development,fast',
		),
		array(
			'title'      => 'How to Create a Winning Digital Marketing Strategy',
			'content'    => '<p>In today\'s digital landscape, having a comprehensive marketing strategy is essential for business success. Here\'s how to create a strategy that delivers results.</p>

<h2>Define Your Goals</h2>
<p>Start by establishing clear, measurable objectives. Whether it\'s increasing brand awareness, generating leads, or driving sales, your goals will guide your entire strategy.</p>

<h2>Know Your Audience</h2>
<p>Deep understanding of your target audience is crucial. Create detailed buyer personas including demographics, behaviors, pain points, and preferences.</p>

<h2>Choose Your Channels</h2>
<p>Select marketing channels based on where your audience spends time. This might include social media, email marketing, content marketing, SEO, or paid advertising.</p>

<h2>Create Compelling Content</h2>
<p>Content is king. Develop valuable, engaging content that addresses your audience\'s needs and positions your brand as a trusted authority.</p>

<h2>Measure and Optimize</h2>
<p>Regularly analyze your results using analytics tools. Use data-driven insights to refine your strategy and improve performance over time.</p>

<p>A well-executed digital marketing strategy can transform your business and drive sustainable growth.</p>',
			'category'   => 'Marketing',
			'tags'       => array( 'Marketing', 'Strategy', 'Digital' ),
			'image_keyword' => 'marketing,strategy,business',
		),
		array(
			'title'      => 'The Rise of Mobile-First Design: Why It Matters',
			'content'    => '<p>Mobile devices now account for over 60% of web traffic worldwide. Mobile-first design isn\'t just a trend—it\'s a necessity for modern web development.</p>

<h2>What is Mobile-First Design?</h2>
<p>Mobile-first design means creating your website for mobile devices first, then scaling up to larger screens. This approach ensures the best possible experience for the majority of your users.</p>

<h2>Benefits of Mobile-First Approach</h2>
<p>Starting with mobile forces you to prioritize essential content and features. This results in cleaner, more focused designs that work well across all devices.</p>

<h2>Key Principles</h2>
<ul>
<li>Touch-friendly interface elements</li>
<li>Simplified navigation</li>
<li>Fast loading times</li>
<li>Readable typography</li>
<li>Optimized images</li>
</ul>

<h2>Implementation Tips</h2>
<p>Use responsive design frameworks, test on real devices, and prioritize performance. Remember that mobile users often have slower connections and less patience for slow-loading sites.</p>

<p>Embracing mobile-first design ensures your website meets the needs of modern users and stays competitive in an increasingly mobile world.</p>',
			'category'   => 'Web Design',
			'tags'       => array( 'Mobile', 'Responsive', 'UX' ),
			'image_keyword' => 'mobile,phone,responsive',
		),
		array(
			'title'      => 'Understanding SEO: A Beginner\'s Guide to Search Optimization',
			'content'    => '<p>Search Engine Optimization (SEO) is crucial for driving organic traffic to your website. This guide covers the fundamentals every website owner should know.</p>

<h2>What is SEO?</h2>
<p>SEO is the practice of optimizing your website to rank higher in search engine results pages (SERPs). Higher rankings lead to more visibility and organic traffic.</p>

<h2>On-Page SEO</h2>
<p>On-page SEO involves optimizing individual web pages through:</p>
<ul>
<li>Keyword-rich, quality content</li>
<li>Optimized title tags and meta descriptions</li>
<li>Header tag hierarchy (H1, H2, H3)</li>
<li>Internal linking structure</li>
<li>Image alt text</li>
</ul>

<h2>Technical SEO</h2>
<p>Technical aspects include site speed, mobile-friendliness, secure connections (HTTPS), XML sitemaps, and proper URL structure.</p>

<h2>Off-Page SEO</h2>
<p>Building authority through quality backlinks, social signals, and brand mentions helps improve your site\'s credibility and rankings.</p>

<h2>Content is King</h2>
<p>Creating valuable, original content that answers user questions is the most important SEO strategy. Focus on providing genuine value to your audience.</p>

<p>SEO is a long-term investment, but the organic traffic and visibility it generates make it worthwhile.</p>',
			'category'   => 'SEO',
			'tags'       => array( 'SEO', 'Search', 'Optimization' ),
			'image_keyword' => 'seo,search,google',
		),
		array(
			'title'      => 'Building a Strong Brand Identity in the Digital Age',
			'content'    => '<p>Your brand identity is more than just a logo—it\'s the complete visual and emotional experience your business provides. Here\'s how to build a strong brand in today\'s digital world.</p>

<h2>Define Your Brand Strategy</h2>
<p>Start with your mission, vision, and values. What does your brand stand for? What makes you unique? Clear brand positioning guides all your branding decisions.</p>

<h2>Visual Identity Elements</h2>
<p>Develop consistent visual elements including:</p>
<ul>
<li>Logo and logo variations</li>
<li>Color palette</li>
<li>Typography</li>
<li>Photography style</li>
<li>Graphic elements</li>
</ul>

<h2>Brand Voice and Messaging</h2>
<p>Define how your brand communicates. Are you professional, friendly, innovative, or authoritative? Consistency in tone across all channels builds recognition.</p>

<h2>Digital Brand Presence</h2>
<p>Your website, social media, and digital marketing materials should all reflect your brand identity cohesively. Every touchpoint is an opportunity to reinforce your brand.</p>

<h2>Brand Guidelines</h2>
<p>Create comprehensive brand guidelines documenting proper logo usage, color codes, typography rules, and voice guidelines to ensure consistency.</p>

<p>A strong brand identity differentiates you from competitors and builds trust with your audience.</p>',
			'category'   => 'Branding',
			'tags'       => array( 'Branding', 'Identity', 'Marketing' ),
			'image_keyword' => 'branding,logo,identity',
		),
		array(
			'title'      => 'The Power of User Experience (UX) Design',
			'content'    => '<p>Great user experience design can make or break a digital product. Understanding UX principles is essential for creating products people love to use.</p>

<h2>What is UX Design?</h2>
<p>UX design focuses on creating meaningful, relevant experiences for users. It encompasses all aspects of user interaction with a product or service.</p>

<h2>Core UX Principles</h2>
<p>Effective UX design follows key principles:</p>
<ul>
<li>User-centered design</li>
<li>Consistency across interfaces</li>
<li>Clear navigation and information architecture</li>
<li>Accessibility for all users</li>
<li>Feedback and response to user actions</li>
</ul>

<h2>The UX Design Process</h2>
<p>Start with user research to understand needs and pain points. Create wireframes and prototypes, test with real users, and iterate based on feedback.</p>

<h2>Measuring UX Success</h2>
<p>Track metrics like task completion rates, time on task, error rates, and user satisfaction scores to evaluate and improve your UX.</p>

<h2>UX and Business Impact</h2>
<p>Good UX design directly impacts business metrics including conversion rates, customer retention, and brand loyalty.</p>

<p>Investing in UX design creates products that users love and businesses that thrive.</p>',
			'category'   => 'UX Design',
			'tags'       => array( 'UX', 'Design', 'User Experience' ),
			'image_keyword' => 'ux,design,interface',
		),
		array(
			'title'      => 'Choosing the Right CMS for Your Website',
			'content'    => '<p>Selecting the right Content Management System (CMS) is a crucial decision that affects your website\'s functionality, scalability, and ease of use.</p>

<h2>Popular CMS Options</h2>
<p>The CMS landscape includes several major players, each with unique strengths:</p>

<h3>WordPress</h3>
<p>Powers over 40% of the web. Highly flexible with thousands of themes and plugins. Great for blogs, business sites, and e-commerce.</p>

<h3>Drupal</h3>
<p>Robust and secure, ideal for complex, enterprise-level websites requiring advanced customization.</p>

<h3>Shopify</h3>
<p>Specialized for e-commerce with built-in payment processing and inventory management.</p>

<h3>Webflow</h3>
<p>Visual-first CMS combining design tools with content management, perfect for designers.</p>

<h2>Key Considerations</h2>
<ul>
<li>Ease of use for content editors</li>
<li>Scalability for future growth</li>
<li>Security features and updates</li>
<li>Available themes and plugins</li>
<li>Developer community and support</li>
<li>Cost (hosting, themes, plugins)</li>
</ul>

<h2>Making Your Choice</h2>
<p>Consider your technical expertise, budget, specific needs, and long-term goals. The best CMS is the one that fits your unique requirements.</p>

<p>Take time to evaluate options through demos and trials before committing to a platform.</p>',
			'category'   => 'Development',
			'tags'       => array( 'CMS', 'WordPress', 'Development' ),
			'image_keyword' => 'cms,wordpress,website',
		),
	);

	foreach ( $posts as $index => $post_data ) {
		// Check if post already exists
		$existing_post = get_page_by_title( $post_data['title'], OBJECT, 'post' );

		if ( $existing_post ) {
			continue;
		}

		// Create post
		$post_id = wp_insert_post( array(
			'post_title'   => $post_data['title'],
			'post_content' => $post_data['content'],
			'post_status'  => 'publish',
			'post_type'    => 'post',
			'post_author'  => 1,
			'post_date'    => date( 'Y-m-d H:i:s', strtotime( '-' . ( $index * 3 ) . ' days' ) ),
		) );

		if ( ! $post_id ) {
			continue;
		}

		// Set category
		if ( ! empty( $post_data['category'] ) ) {
			$category = get_term_by( 'name', $post_data['category'], 'category' );
			if ( ! $category ) {
				$category = wp_insert_term( $post_data['category'], 'category' );
				if ( ! is_wp_error( $category ) ) {
					$category_id = $category['term_id'];
				}
			} else {
				$category_id = $category->term_id;
			}

			if ( isset( $category_id ) ) {
				wp_set_post_categories( $post_id, array( $category_id ) );
			}
		}

		// Set tags
		if ( ! empty( $post_data['tags'] ) ) {
			wp_set_post_tags( $post_id, $post_data['tags'] );
		}

		// Note: Featured images use placeholders from Unsplash via the templates
	}
}

/**
 * Create Demo Navigation Menus
 */
function velocity_create_demo_menus() {
	// Check if menu already exists
	$menu_exists = wp_get_nav_menu_object( 'Primary Menu' );

	if ( $menu_exists ) {
		return;
	}

	// Create Primary Menu
	$menu_id = wp_create_nav_menu( 'Primary Menu' );

	if ( is_wp_error( $menu_id ) ) {
		return;
	}

	// Get page IDs
	$home_id      = get_option( 'velocity_demo_page_home' );
	$about_id     = get_option( 'velocity_demo_page_about-us' );
	$services_id  = get_option( 'velocity_demo_page_our-services' );
	$portfolio_id = get_option( 'velocity_demo_page_portfolio' );
	$blog_id      = get_option( 'velocity_demo_page_blog' );
	$contact_id   = get_option( 'velocity_demo_page_contact-us' );

	// Add menu items
	$menu_items = array(
		array( 'title' => 'Home', 'page_id' => $home_id ),
		array( 'title' => 'About', 'page_id' => $about_id ),
		array( 'title' => 'Services', 'page_id' => $services_id ),
		array( 'title' => 'Portfolio', 'page_id' => $portfolio_id ),
		array( 'title' => 'Blog', 'page_id' => $blog_id ),
		array( 'title' => 'Contact', 'page_id' => $contact_id ),
	);

	$menu_order = 0;
	foreach ( $menu_items as $item ) {
		if ( $item['page_id'] ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => $item['title'],
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $item['page_id'],
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => ++$menu_order,
			) );
		}
	}

	// Assign to theme location
	$locations = get_theme_mod( 'nav_menu_locations' );
	$locations['primary'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	// Create Footer Menu
	$footer_menu_id = wp_create_nav_menu( 'Footer Menu' );

	if ( ! is_wp_error( $footer_menu_id ) ) {
		// Add footer menu items
		$footer_items = array(
			array( 'title' => 'Privacy Policy', 'url' => '#' ),
			array( 'title' => 'Terms of Service', 'url' => '#' ),
			array( 'title' => 'Sitemap', 'url' => '#' ),
		);

		$footer_order = 0;
		foreach ( $footer_items as $item ) {
			wp_update_nav_menu_item( $footer_menu_id, 0, array(
				'menu-item-title'    => $item['title'],
				'menu-item-url'      => $item['url'],
				'menu-item-type'     => 'custom',
				'menu-item-status'   => 'publish',
				'menu-item-position' => ++$footer_order,
			) );
		}

		// Assign footer menu
		$locations['footer'] = $footer_menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}
}

/**
 * Create Demo Widgets
 */
function velocity_create_demo_widgets() {
	// Get existing widgets
	$sidebar_widgets = get_option( 'sidebars_widgets', array() );

	// Sidebar widgets setup
	$sidebar_widgets['sidebar-1'] = array( 'search-2', 'recent-posts-2', 'categories-2', 'tag_cloud-2' );

	// Footer widgets
	$sidebar_widgets['footer-1'] = array( 'text-2' );
	$sidebar_widgets['footer-2'] = array( 'text-3' );
	$sidebar_widgets['footer-3'] = array( 'text-4' );
	$sidebar_widgets['footer-4'] = array( 'text-5' );

	// Update sidebar widgets
	update_option( 'sidebars_widgets', $sidebar_widgets );

	// Search widget
	update_option( 'widget_search', array(
		2 => array( 'title' => '' ),
	) );

	// Recent posts widget
	update_option( 'widget_recent-posts', array(
		2 => array(
			'title'  => 'Recent Posts',
			'number' => 5,
		),
	) );

	// Categories widget
	update_option( 'widget_categories', array(
		2 => array( 'title' => 'Categories' ),
	) );

	// Tag cloud widget
	update_option( 'widget_tag_cloud', array(
		2 => array( 'title' => 'Tags' ),
	) );

	// Text widgets for footer
	$text_widgets = array(
		2 => array(
			'title'  => 'About Velocity',
			'text'   => 'We are a digital agency specializing in web design, development, and digital marketing. Our mission is to help businesses succeed online through innovative solutions.',
			'filter' => false,
		),
		3 => array(
			'title'  => 'Quick Links',
			'text'   => '<ul style="list-style: none; padding: 0;">
<li style="padding: 0.25rem 0;"><a href="#">About Us</a></li>
<li style="padding: 0.25rem 0;"><a href="#">Services</a></li>
<li style="padding: 0.25rem 0;"><a href="#">Portfolio</a></li>
<li style="padding: 0.25rem 0;"><a href="#">Contact</a></li>
</ul>',
			'filter' => false,
		),
		4 => array(
			'title'  => 'Our Services',
			'text'   => '<ul style="list-style: none; padding: 0;">
<li style="padding: 0.25rem 0;">Web Design</li>
<li style="padding: 0.25rem 0;">Web Development</li>
<li style="padding: 0.25rem 0;">Digital Marketing</li>
<li style="padding: 0.25rem 0;">Branding</li>
</ul>',
			'filter' => false,
		),
		5 => array(
			'title'  => 'Get in Touch',
			'text'   => '<p style="margin-bottom: 0.5rem;"><strong>Email:</strong> hello@velocity.com</p>
<p style="margin-bottom: 0.5rem;"><strong>Phone:</strong> +1 (555) 123-4567</p>
<p style="margin-bottom: 0;"><strong>Address:</strong> 123 Innovation St<br>San Francisco, CA 94102</p>',
			'filter' => false,
		),
	);

	update_option( 'widget_text', $text_widgets );
}

/**
 * Set Demo Homepage
 */
function velocity_set_demo_homepage() {
	$home_id = get_option( 'velocity_demo_page_home' );
	$blog_id = get_option( 'velocity_demo_page_blog' );

	if ( $home_id ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home_id );

		if ( $blog_id ) {
			update_option( 'page_for_posts', $blog_id );
		}
	}
}

/**
 * Add admin notice to install demo content
 */
function velocity_demo_content_admin_notice() {
	// Don't show if already installed
	if ( velocity_is_demo_installed() ) {
		return;
	}

	// Don't show if user dismissed
	if ( get_user_meta( get_current_user_id(), 'velocity_demo_dismissed', true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( $screen->id !== 'themes' && $screen->id !== 'dashboard' ) {
		return;
	}

	?>
	<div class="notice notice-info is-dismissible velocity-demo-notice">
		<p>
			<strong><?php esc_html_e( '🎉 Welcome to Velocity Theme!', 'velocity' ); ?></strong>
		</p>
		<p>
			<?php esc_html_e( 'To see the theme\'s full capabilities, install the demo content with sample pages, posts, and menus.', 'velocity' ); ?>
		</p>
		<p>
			<a href="<?php echo esc_url( admin_url( 'themes.php?page=velocity-demo-content' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Install Demo Content', 'velocity' ); ?>
			</a>
			<a href="#" class="button velocity-dismiss-demo">
				<?php esc_html_e( 'Maybe Later', 'velocity' ); ?>
			</a>
		</p>
	</div>
	<script>
	jQuery(document).ready(function($) {
		$('.velocity-dismiss-demo').on('click', function(e) {
			e.preventDefault();
			$('.velocity-demo-notice').fadeOut();
			$.post(ajaxurl, {
				action: 'velocity_dismiss_demo_notice',
				nonce: '<?php echo wp_create_nonce( 'velocity_demo_dismiss' ); ?>'
			});
		});
	});
	</script>
	<?php
}
add_action( 'admin_notices', 'velocity_demo_content_admin_notice' );

/**
 * Handle dismiss notice AJAX
 */
function velocity_dismiss_demo_notice() {
	check_ajax_referer( 'velocity_demo_dismiss', 'nonce' );
	update_user_meta( get_current_user_id(), 'velocity_demo_dismissed', true );
	wp_die();
}
add_action( 'wp_ajax_velocity_dismiss_demo_notice', 'velocity_dismiss_demo_notice' );

/**
 * Add demo content page to admin menu
 */
function velocity_demo_content_menu() {
	add_theme_page(
		__( 'Demo Content', 'velocity' ),
		__( 'Demo Content', 'velocity' ),
		'manage_options',
		'velocity-demo-content',
		'velocity_demo_content_page'
	);
}
add_action( 'admin_menu', 'velocity_demo_content_menu' );

/**
 * Demo content admin page
 */
function velocity_demo_content_page() {
	// Handle form submission
	if ( isset( $_POST['velocity_install_demo'] ) && check_admin_referer( 'velocity_demo_content' ) ) {
		velocity_install_demo_content();
		echo '<div class="notice notice-success"><p>' . esc_html__( '✅ Demo content installed successfully! Visit your site to see it in action.', 'velocity' ) . '</p></div>';
	}

	$is_installed = velocity_is_demo_installed();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Velocity Demo Content', 'velocity' ); ?></h1>

		<div class="card" style="max-width: 800px; margin-top: 20px;">
			<?php if ( ! $is_installed ) : ?>
				<h2><?php esc_html_e( '📦 Install Demo Content', 'velocity' ); ?></h2>
				<p><?php esc_html_e( 'Click the button below to install demo content including:', 'velocity' ); ?></p>
				<ul style="list-style: disc; margin-left: 2rem; line-height: 1.8;">
					<li><strong><?php esc_html_e( '6 Sample Pages:', 'velocity' ); ?></strong> <?php esc_html_e( 'Home, About, Services, Portfolio, Contact, Blog', 'velocity' ); ?></li>
					<li><strong><?php esc_html_e( '8 Sample Blog Posts:', 'velocity' ); ?></strong> <?php esc_html_e( 'With categories, tags, and rich content', 'velocity' ); ?></li>
					<li><strong><?php esc_html_e( 'Navigation Menus:', 'velocity' ); ?></strong> <?php esc_html_e( 'Primary and Footer menus configured', 'velocity' ); ?></li>
					<li><strong><?php esc_html_e( 'Widget Areas:', 'velocity' ); ?></strong> <?php esc_html_e( 'Sidebar and footer widgets with sample content', 'velocity' ); ?></li>
					<li><strong><?php esc_html_e( 'Homepage Setup:', 'velocity' ); ?></strong> <?php esc_html_e( 'Static front page configured', 'velocity' ); ?></li>
				</ul>

				<div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin: 20px 0;">
					<p style="margin: 0;"><strong><?php esc_html_e( '📌 Note:', 'velocity' ); ?></strong> <?php esc_html_e( 'This will not delete any existing content. It only adds new demo content to showcase the theme features.', 'velocity' ); ?></p>
				</div>

				<form method="post">
					<?php wp_nonce_field( 'velocity_demo_content' ); ?>
					<p>
						<button type="submit" name="velocity_install_demo" class="button button-primary button-hero">
							<?php esc_html_e( '🚀 Install Demo Content Now', 'velocity' ); ?>
						</button>
					</p>
				</form>
			<?php else : ?>
				<h2><?php esc_html_e( '✅ Demo Content Installed', 'velocity' ); ?></h2>
				<p>
					<?php esc_html_e( 'Demo content was successfully installed on:', 'velocity' ); ?>
					<strong><?php echo esc_html( get_option( 'velocity_demo_installed_date' ) ); ?></strong>
				</p>

				<div style="background: #d4edda; border-left: 4px solid #28a745; padding: 12px; margin: 20px 0;">
					<p style="margin: 0;"><strong><?php esc_html_e( '🎉 Success!', 'velocity' ); ?></strong> <?php esc_html_e( 'Your demo content is ready. Visit your site to see all the features in action.', 'velocity' ); ?></p>
				</div>

				<h3><?php esc_html_e( 'What\'s Included:', 'velocity' ); ?></h3>
				<ul style="list-style: none; padding: 0;">
					<li style="padding: 8px 0; border-bottom: 1px solid #eee;">✅ Home page with hero, services, portfolio showcase</li>
					<li style="padding: 8px 0; border-bottom: 1px solid #eee;">✅ About page with team members and company info</li>
					<li style="padding: 8px 0; border-bottom: 1px solid #eee;">✅ Services page with pricing packages</li>
					<li style="padding: 8px 0; border-bottom: 1px solid #eee;">✅ Portfolio page with projects and testimonials</li>
					<li style="padding: 8px 0; border-bottom: 1px solid #eee;">✅ Contact page with form and map</li>
					<li style="padding: 8px 0; border-bottom: 1px solid #eee;">✅ Blog with 8 sample posts</li>
					<li style="padding: 8px 0; border-bottom: 1px solid #eee;">✅ Navigation menus configured</li>
					<li style="padding: 8px 0;">✅ Widgets populated</li>
				</ul>

				<p style="margin-top: 30px;">
					<a href="<?php echo esc_url( home_url() ); ?>" class="button button-primary button-hero" target="_blank">
						<?php esc_html_e( '👁️ View Your Site', 'velocity' ); ?>
					</a>
				</p>

				<hr style="margin: 40px 0;">

				<h3><?php esc_html_e( 'Next Steps', 'velocity' ); ?></h3>
				<ol style="line-height: 1.8;">
					<li><?php esc_html_e( 'Customize the demo content with your own text and images', 'velocity' ); ?></li>
					<li><?php esc_html_e( 'Install page builders (Elementor or WPBakery) for advanced customization', 'velocity' ); ?></li>
					<li><?php esc_html_e( 'Replace placeholder images with your own brand assets', 'velocity' ); ?></li>
					<li><?php esc_html_e( 'Configure contact forms and other functionality', 'velocity' ); ?></li>
					<li><?php esc_html_e( 'Install SEO and performance plugins as recommended', 'velocity' ); ?></li>
				</ol>

			<?php endif; ?>
		</div>

		<?php if ( $is_installed ) : ?>
		<div class="card" style="max-width: 800px; margin-top: 20px;">
			<h2><?php esc_html_e( '📚 Resources', 'velocity' ); ?></h2>
			<p><?php esc_html_e( 'Check out these helpful resources:', 'velocity' ); ?></p>
			<ul style="list-style: disc; margin-left: 2rem; line-height: 1.8;">
				<li><a href="<?php echo esc_url( get_template_directory_uri() . '/README.md' ); ?>" target="_blank"><?php esc_html_e( 'Complete Documentation (README.md)', 'velocity' ); ?></a></li>
				<li><a href="<?php echo esc_url( get_template_directory_uri() . '/INSTALLATION.md' ); ?>" target="_blank"><?php esc_html_e( 'Quick Installation Guide', 'velocity' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Customize Your Theme', 'velocity' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><?php esc_html_e( 'Manage Menus', 'velocity' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>"><?php esc_html_e( 'Manage Widgets', 'velocity' ); ?></a></li>
			</ul>
		</div>
		<?php endif; ?>
	</div>

	<style>
		.velocity-demo-notice { padding: 15px; }
		.velocity-demo-notice strong { font-size: 16px; }
		.card h2 { margin-top: 0; }
		.card h3 { margin-top: 25px; }
	</style>
	<?php
}

/**
 * Page Content Generators
 * These functions generate full HTML content for pages
 * This content is editable with page builders and provides a fallback design
 */

/**
 * Home Page Content
 */
function velocity_get_home_page_content() {
	return '
<!-- Hero Section -->
<div style="background: linear-gradient(135deg, #6C5CE7 0%, #00B894 100%); color: white; text-align: center; padding: 100px 20px; margin-bottom: 60px;">
	<h1 style="font-size: 3rem; margin-bottom: 20px; color: white;">Transform Your Digital Presence</h1>
	<p style="font-size: 1.25rem; margin-bottom: 30px; opacity: 0.95;">Cutting-edge design and development solutions that drive results</p>
	<a href="#services" style="display: inline-block; padding: 15px 40px; background: white; color: #6C5CE7; border-radius: 8px; text-decoration: none; font-weight: 600; margin: 0 10px;">Our Services</a>
	<a href="#portfolio" style="display: inline-block; padding: 15px 40px; background: transparent; color: white; border: 2px solid white; border-radius: 8px; text-decoration: none; font-weight: 600; margin: 0 10px;">View Work</a>
</div>

<!-- Services Section -->
<div id="services" style="padding: 60px 20px; background: #F8F9FA;">
	<div style="max-width: 1200px; margin: 0 auto;">
		<div style="text-align: center; margin-bottom: 50px;">
			<h2 style="font-size: 2.5rem; margin-bottom: 15px;">Our Services</h2>
			<p style="color: #636E72;">Comprehensive digital solutions tailored to your business needs</p>
		</div>

		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
			<div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
				<img src="https://source.unsplash.com/800x600/?web,design" alt="Web Design" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 20px;" />
				<h3 style="margin-bottom: 15px;">Web Design</h3>
				<p style="color: #636E72;">Beautiful, responsive websites that captivate your audience and deliver exceptional user experiences.</p>
			</div>

			<div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
				<img src="https://source.unsplash.com/800x600/?development,code" alt="Development" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 20px;" />
				<h3 style="margin-bottom: 15px;">Development</h3>
				<p style="color: #636E72;">Robust, scalable applications built with modern technologies and best practices for optimal performance.</p>
			</div>

			<div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
				<img src="https://source.unsplash.com/800x600/?branding,strategy" alt="Branding" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 20px;" />
				<h3 style="margin-bottom: 15px;">Branding</h3>
				<p style="color: #636E72;">Strategic brand identity development that resonates with your target audience and sets you apart.</p>
			</div>
		</div>
	</div>
</div>

<!-- Portfolio Section -->
<div id="portfolio" style="padding: 60px 20px;">
	<div style="max-width: 1200px; margin: 0 auto;">
		<div style="text-center; margin-bottom: 50px;">
			<h2 style="font-size: 2.5rem; margin-bottom: 15px;">Featured Work</h2>
			<p style="color: #636E72;">Explore our latest projects and success stories</p>
		</div>

		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px;">
			<div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
				<img src="https://source.unsplash.com/800x600/?ecommerce,shop" alt="E-Commerce Platform" style="width: 100%; height: 250px; object-fit: cover;" />
				<div style="padding: 30px;">
					<h3>E-Commerce Platform</h3>
					<p style="color: #636E72;">A modern e-commerce solution with seamless checkout experience.</p>
				</div>
			</div>

			<div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
				<img src="https://source.unsplash.com/800x600/?dashboard,analytics" alt="SaaS Dashboard" style="width: 100%; height: 250px; object-fit: cover;" />
				<div style="padding: 30px;">
					<h3>SaaS Dashboard</h3>
					<p style="color: #636E72;">Intuitive analytics dashboard with real-time data visualization.</p>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Stats Section -->
<div style="background: #6C5CE7; color: white; padding: 60px 20px; text-align: center;">
	<div style="max-width: 1200px; margin: 0 auto;">
		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 30px;">
			<div>
				<h3 style="color: #FDCB6E; font-size: 3rem; margin-bottom: 10px;">500+</h3>
				<p>Projects Completed</p>
			</div>
			<div>
				<h3 style="color: #FDCB6E; font-size: 3rem; margin-bottom: 10px;">200+</h3>
				<p>Happy Clients</p>
			</div>
			<div>
				<h3 style="color: #FDCB6E; font-size: 3rem; margin-bottom: 10px;">15+</h3>
				<p>Years Experience</p>
			</div>
			<div>
				<h3 style="color: #FDCB6E; font-size: 3rem; margin-bottom: 10px;">50+</h3>
				<p>Team Members</p>
			</div>
		</div>
	</div>
</div>

<!-- CTA Section -->
<div style="padding: 80px 20px; text-align: center;">
	<h2 style="font-size: 2.5rem; margin-bottom: 20px;">Ready to Start Your Project?</h2>
	<p style="font-size: 1.25rem; color: #636E72; margin-bottom: 30px;">Let\'s build something amazing together</p>
	<a href="/contact-us" style="display: inline-block; padding: 15px 40px; background: #6C5CE7; color: white; border-radius: 8px; text-decoration: none; font-weight: 600;">Get in Touch</a>
</div>
';
}

/**
 * About Page Content
 */
function velocity_get_about_page_content() {
	return '
<!-- Hero -->
<div style="background: linear-gradient(135deg, #6C5CE7 0%, #00B894 100%); color: white; text-align: center; padding: 80px 20px; margin-bottom: 60px;">
	<h1 style="font-size: 3rem; color: white; margin-bottom: 15px;">About Us</h1>
	<p style="font-size: 1.25rem; opacity: 0.95;">Passionate team of innovators dedicated to digital excellence</p>
</div>

<!-- Story Section -->
<div style="padding: 60px 20px;">
	<div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 60px; align-items: center;">
		<div>
			<h2 style="font-size: 2.5rem; margin-bottom: 20px;">Our Story</h2>
			<p style="margin-bottom: 15px;">Founded in 2008, our agency has grown from a small startup to a leading digital solutions provider. We\'ve helped hundreds of businesses transform their digital presence and achieve remarkable growth.</p>
			<p style="margin-bottom: 15px;">Our mission is to empower businesses through innovative technology and creative design. We believe in building long-term partnerships based on trust, transparency, and measurable results.</p>
			<p>Every project we undertake is driven by a commitment to excellence and a passion for pushing boundaries. We don\'t just build websites and apps—we create digital experiences that matter.</p>
		</div>
		<div>
			<img src="https://source.unsplash.com/1200x800/?team,office" alt="Our Team" style="width: 100%; border-radius: 8px; box-shadow: 0 20px 60px rgba(0,0,0,0.15);" />
		</div>
	</div>
</div>

<!-- Values Section -->
<div style="padding: 60px 20px; background: #F8F9FA;">
	<div style="max-width: 1200px; margin: 0 auto; text-align: center;">
		<h2 style="font-size: 2.5rem; margin-bottom: 15px;">Our Values</h2>
		<p style="color: #636E72; margin-bottom: 50px;">The principles that guide everything we do</p>

		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; text-align: left;">
			<div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
				<div style="font-size: 3rem; margin-bottom: 15px;">🎯</div>
				<h3 style="margin-bottom: 15px;">Excellence</h3>
				<p style="color: #636E72;">We set the highest standards for quality in everything we deliver, from design to development and support.</p>
			</div>

			<div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
				<div style="font-size: 3rem; margin-bottom: 15px;">🚀</div>
				<h3 style="margin-bottom: 15px;">Innovation</h3>
				<p style="color: #636E72;">We embrace emerging technologies and creative approaches to solve complex challenges in new ways.</p>
			</div>

			<div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
				<div style="font-size: 3rem; margin-bottom: 15px;">🤝</div>
				<h3 style="margin-bottom: 15px;">Collaboration</h3>
				<p style="color: #636E72;">We work closely with our clients as partners, ensuring their vision becomes reality through teamwork.</p>
			</div>
		</div>
	</div>
</div>

<!-- Team Section -->
<div style="padding: 60px 20px;">
	<div style="max-width: 1200px; margin: 0 auto; text-align: center;">
		<h2 style="font-size: 2.5rem; margin-bottom: 15px;">Meet Our Team</h2>
		<p style="color: #636E72; margin-bottom: 50px;">The talented people behind our success</p>

		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
			<div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
				<img src="https://source.unsplash.com/400x400/?portrait,woman,professional" alt="Sarah Johnson" style="width: 100%; height: 300px; object-fit: cover;" />
				<div style="padding: 20px; text-align: center;">
					<h4 style="margin-bottom: 5px;">Sarah Johnson</h4>
					<p style="color: #6C5CE7; font-weight: 600;">CEO & Founder</p>
				</div>
			</div>

			<div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
				<img src="https://source.unsplash.com/400x400/?portrait,man,professional" alt="Michael Chen" style="width: 100%; height: 300px; object-fit: cover;" />
				<div style="padding: 20px; text-align: center;">
					<h4 style="margin-bottom: 5px;">Michael Chen</h4>
					<p style="color: #6C5CE7; font-weight: 600;">CTO</p>
				</div>
			</div>

			<div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
				<img src="https://source.unsplash.com/400x400/?portrait,woman,creative" alt="Emily Rodriguez" style="width: 100%; height: 300px; object-fit: cover;" />
				<div style="padding: 20px; text-align: center;">
					<h4 style="margin-bottom: 5px;">Emily Rodriguez</h4>
					<p style="color: #6C5CE7; font-weight: 600;">Creative Director</p>
				</div>
			</div>

			<div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
				<img src="https://source.unsplash.com/400x400/?portrait,man,developer" alt="David Kim" style="width: 100%; height: 300px; object-fit: cover;" />
				<div style="padding: 20px; text-align: center;">
					<h4 style="margin-bottom: 5px;">David Kim</h4>
					<p style="color: #6C5CE7; font-weight: 600;">Lead Developer</p>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- CTA -->
<div style="padding: 80px 20px; text-align: center; background: #F8F9FA;">
	<h2 style="font-size: 2.5rem; margin-bottom: 20px;">Want to Work With Us?</h2>
	<p style="font-size: 1.25rem; color: #636E72; margin-bottom: 30px;">We\'d love to hear about your project</p>
	<a href="/contact-us" style="display: inline-block; padding: 15px 40px; background: #6C5CE7; color: white; border-radius: 8px; text-decoration: none; font-weight: 600;">Start a Conversation</a>
</div>
';
}
