<?php
/**
 * The header template file
 *
 * @package Velocity
 * @since 1.0.0
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#main">
		<?php esc_html_e( 'Skip to content', 'velocity' ); ?>
	</a>

	<header id="masthead" class="site-header">
		<div class="container">
			<div class="header-inner">

				<div class="site-logo">
					<?php velocity_site_logo(); ?>
				</div>

				<nav id="site-navigation" class="main-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'velocity' ); ?>">
					<?php
					wp_nav_menu( array(
						'theme_location' => 'primary',
						'menu_id'        => 'primary-menu',
						'container'      => false,
						'fallback_cb'    => false,
					) );
					?>
				</nav>

				<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle navigation', 'velocity' ); ?>">
					<span></span>
					<span></span>
					<span></span>
				</button>

			</div>
		</div>
	</header>
