<?php
/**
 * Velocity Theme Functions
 *
 * @package Velocity
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Define Constants
 */
define( 'VELOCITY_VERSION', '1.0.0' );
define( 'VELOCITY_THEME_DIR', get_template_directory() );
define( 'VELOCITY_THEME_URI', get_template_directory_uri() );

/**
 * Theme Setup
 */
function velocity_theme_setup() {
	// Add default posts and comments RSS feed links to head
	add_theme_support( 'automatic-feed-links' );

	// Let WordPress manage the document title
	add_theme_support( 'title-tag' );

	// Enable support for Post Thumbnails
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 1200, 630, true );

	// Add custom image sizes
	add_image_size( 'velocity-hero', 1920, 1080, true );
	add_image_size( 'velocity-portfolio', 800, 600, true );
	add_image_size( 'velocity-thumbnail', 400, 300, true );

	// Register navigation menus
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary Menu', 'velocity' ),
		'footer'  => esc_html__( 'Footer Menu', 'velocity' ),
	) );

	// Switch default core markup to output valid HTML5
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	) );

	// Add theme support for selective refresh for widgets
	add_theme_support( 'customize-selective-refresh-widgets' );

	// Add support for custom logo
	add_theme_support( 'custom-logo', array(
		'height'      => 100,
		'width'       => 400,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	// Add support for responsive embeds
	add_theme_support( 'responsive-embeds' );

	// Add support for editor styles
	add_theme_support( 'editor-styles' );

	// Add support for wide and full alignment
	add_theme_support( 'align-wide' );

	// Add support for custom background
	add_theme_support( 'custom-background', array(
		'default-color' => 'ffffff',
	) );

	// Content width
	if ( ! isset( $content_width ) ) {
		$content_width = 1200;
	}
}
add_action( 'after_setup_theme', 'velocity_theme_setup' );

/**
 * Enqueue Scripts and Styles
 */
function velocity_enqueue_scripts() {
	// Main stylesheet
	wp_enqueue_style( 'velocity-style', get_stylesheet_uri(), array(), VELOCITY_VERSION );

	// Main JavaScript
	wp_enqueue_script( 'velocity-scripts', VELOCITY_THEME_URI . '/assets/js/main.js', array(), VELOCITY_VERSION, true );

	// Comment reply script
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'velocity_enqueue_scripts' );

/**
 * Register Widget Areas
 */
function velocity_widgets_init() {
	// Sidebar
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'velocity' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here to appear in your sidebar.', 'velocity' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	// Footer widgets
	$footer_widget_areas = 4;
	for ( $i = 1; $i <= $footer_widget_areas; $i++ ) {
		register_sidebar( array(
			'name'          => sprintf( esc_html__( 'Footer Widget Area %d', 'velocity' ), $i ),
			'id'            => 'footer-' . $i,
			'description'   => sprintf( esc_html__( 'Footer widget area %d', 'velocity' ), $i ),
			'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		) );
	}
}
add_action( 'widgets_init', 'velocity_widgets_init' );

/**
 * Page Builder Compatibility
 */

// Elementor Support
function velocity_add_elementor_support() {
	add_theme_support( 'elementor' );
	add_theme_support( 'elementor-header-footer' );
}
add_action( 'after_setup_theme', 'velocity_add_elementor_support' );

// WPBakery (Visual Composer) Support
function velocity_vc_set_as_theme() {
	if ( function_exists( 'vc_set_as_theme' ) ) {
		vc_set_as_theme( true );
	}
}
add_action( 'vc_before_init', 'velocity_vc_set_as_theme' );

/**
 * Performance Optimizations
 */

// Remove jQuery Migrate
function velocity_remove_jquery_migrate( $scripts ) {
	if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
		$script = $scripts->registered['jquery'];
		if ( $script->deps ) {
			$script->deps = array_diff( $script->deps, array( 'jquery-migrate' ) );
		}
	}
}
add_action( 'wp_default_scripts', 'velocity_remove_jquery_migrate' );

// Remove WordPress emoji scripts
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

// Disable embed scripts
function velocity_disable_embeds() {
	wp_deregister_script( 'wp-embed' );
}
add_action( 'wp_footer', 'velocity_disable_embeds' );

// Clean up wp_head
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );

// Add async/defer to scripts
function velocity_async_scripts( $tag, $handle ) {
	$scripts_to_defer = array( 'velocity-scripts' );

	foreach ( $scripts_to_defer as $defer_script ) {
		if ( $defer_script === $handle ) {
			return str_replace( ' src', ' defer src', $tag );
		}
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'velocity_async_scripts', 10, 2 );

/**
 * Custom Excerpt Length
 */
function velocity_excerpt_length( $length ) {
	return 30;
}
add_filter( 'excerpt_length', 'velocity_excerpt_length' );

/**
 * Custom Excerpt More
 */
function velocity_excerpt_more( $more ) {
	return '...';
}
add_filter( 'excerpt_more', 'velocity_excerpt_more' );

/**
 * Custom Body Classes
 */
function velocity_body_classes( $classes ) {
	// Add page slug to body class
	if ( is_singular() ) {
		global $post;
		$classes[] = 'page-' . $post->post_name;
	}

	// Add class for page builders
	if ( is_singular() ) {
		if ( get_post_meta( get_the_ID(), '_elementor_edit_mode', true ) ) {
			$classes[] = 'elementor-page';
		}
		if ( get_post_meta( get_the_ID(), '_wpb_vc_js_status', true ) ) {
			$classes[] = 'vc-page';
		}
	}

	return $classes;
}
add_filter( 'body_class', 'velocity_body_classes' );

/**
 * Template Tags
 */

// Custom Logo or Site Title
function velocity_site_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
	} else {
		echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="site-title">';
		bloginfo( 'name' );
		echo '</a>';
	}
}

// Posted On
function velocity_posted_on() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';

	$time_string = sprintf( $time_string,
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);

	echo '<span class="posted-on">' . $time_string . '</span>';
}

// Posted By
function velocity_posted_by() {
	echo '<span class="posted-by">' .
	     esc_html__( 'By ', 'velocity' ) .
	     '<a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' .
	     esc_html( get_the_author() ) .
	     '</a></span>';
}

/**
 * Pagination
 */
function velocity_pagination() {
	global $wp_query;

	if ( $wp_query->max_num_pages <= 1 ) {
		return;
	}

	$paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
	$max   = intval( $wp_query->max_num_pages );

	if ( $paged >= 1 ) {
		$links[] = $paged;
	}

	if ( $paged >= 3 ) {
		$links[] = $paged - 1;
		$links[] = $paged - 2;
	}

	if ( ( $paged + 2 ) <= $max ) {
		$links[] = $paged + 2;
		$links[] = $paged + 1;
	}

	echo '<div class="pagination"><ul>' . "\n";

	if ( get_previous_posts_link() ) {
		printf( '<li>%s</li>' . "\n", get_previous_posts_link( '&laquo;' ) );
	}

	if ( ! in_array( 1, $links ) ) {
		$class = 1 == $paged ? ' class="active"' : '';
		printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( 1 ) ), '1' );

		if ( ! in_array( 2, $links ) ) {
			echo '<li>…</li>';
		}
	}

	sort( $links );
	foreach ( (array) $links as $link ) {
		$class = $paged == $link ? ' class="active"' : '';
		printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $link ) ), $link );
	}

	if ( ! in_array( $max, $links ) ) {
		if ( ! in_array( $max - 1, $links ) ) {
			echo '<li>…</li>' . "\n";
		}

		$class = $paged == $max ? ' class="active"' : '';
		printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $max ) ), $max );
	}

	if ( get_next_posts_link() ) {
		printf( '<li>%s</li>' . "\n", get_next_posts_link( '&raquo;' ) );
	}

	echo '</ul></div>' . "\n";
}

/**
 * Comments Template
 */
function velocity_comment( $comment, $args, $depth ) {
	?>
	<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
		<article class="comment-body">
			<div class="comment-author vcard">
				<?php echo get_avatar( $comment, 60 ); ?>
				<?php printf( '<cite class="fn">%s</cite>', get_comment_author_link() ); ?>
			</div>
			<div class="comment-meta">
				<time datetime="<?php comment_time( 'c' ); ?>">
					<?php printf( '%1$s at %2$s', get_comment_date(), get_comment_time() ); ?>
				</time>
			</div>
			<div class="comment-content">
				<?php comment_text(); ?>
			</div>
			<div class="comment-reply">
				<?php
				comment_reply_link( array_merge( $args, array(
					'depth'     => $depth,
					'max_depth' => $args['max_depth'],
				) ) );
				?>
			</div>
		</article>
	<?php
}

/**
 * Include Theme Files
 */
// require_once VELOCITY_THEME_DIR . '/inc/customizer.php';

// Include demo content functionality
require_once VELOCITY_THEME_DIR . '/inc/demo-content.php';
require_once VELOCITY_THEME_DIR . '/inc/demo-content-pages.php';

/**
 * Security Headers
 */
function velocity_security_headers() {
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'X-XSS-Protection: 1; mode=block' );
}
add_action( 'send_headers', 'velocity_security_headers' );
