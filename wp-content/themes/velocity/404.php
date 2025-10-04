<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package Velocity
 * @since 1.0.0
 */

get_header(); ?>

<main id="main" class="site-main">
	<div class="container">
		<div class="section text-center" style="min-height: 60vh; display: flex; flex-direction: column; justify-content: center;">

			<div style="font-size: 10rem; font-weight: 700; color: var(--color-primary); line-height: 1; margin-bottom: 1rem;">
				404
			</div>

			<h1><?php esc_html_e( 'Oops! Page Not Found', 'velocity' ); ?></h1>

			<p style="font-size: 1.25rem; max-width: 600px; margin: 0 auto 2rem;">
				<?php esc_html_e( 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.', 'velocity' ); ?>
			</p>

			<div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-bottom: 3rem;">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary">
					<?php esc_html_e( 'Go to Homepage', 'velocity' ); ?>
				</a>
				<a href="javascript:history.back()" class="btn btn-secondary">
					<?php esc_html_e( 'Go Back', 'velocity' ); ?>
				</a>
			</div>

			<div style="max-width: 600px; margin: 0 auto;">
				<h3><?php esc_html_e( 'Search our site:', 'velocity' ); ?></h3>
				<?php get_search_form(); ?>
			</div>

		</div>

		<!-- Helpful Links -->
		<div class="section bg-light" style="margin-top: 4rem; padding: 3rem; border-radius: var(--border-radius);">
			<h3 class="text-center mb-3"><?php esc_html_e( 'Popular Pages', 'velocity' ); ?></h3>

			<div class="grid grid-4">
				<div class="text-center">
					<div style="font-size: 2rem; margin-bottom: 0.5rem;">🏠</div>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="font-weight: 600;">
						<?php esc_html_e( 'Home', 'velocity' ); ?>
					</a>
				</div>

				<div class="text-center">
					<div style="font-size: 2rem; margin-bottom: 0.5rem;">💼</div>
					<a href="<?php echo esc_url( home_url( '/services' ) ); ?>" style="font-weight: 600;">
						<?php esc_html_e( 'Services', 'velocity' ); ?>
					</a>
				</div>

				<div class="text-center">
					<div style="font-size: 2rem; margin-bottom: 0.5rem;">📁</div>
					<a href="<?php echo esc_url( home_url( '/portfolio' ) ); ?>" style="font-weight: 600;">
						<?php esc_html_e( 'Portfolio', 'velocity' ); ?>
					</a>
				</div>

				<div class="text-center">
					<div style="font-size: 2rem; margin-bottom: 0.5rem;">📧</div>
					<a href="<?php echo esc_url( home_url( '/contact' ) ); ?>" style="font-weight: 600;">
						<?php esc_html_e( 'Contact', 'velocity' ); ?>
					</a>
				</div>
			</div>
		</div>

	</div>
</main>

<?php get_footer(); ?>
