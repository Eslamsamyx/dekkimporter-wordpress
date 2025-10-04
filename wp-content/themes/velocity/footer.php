<?php
/**
 * The footer template file
 *
 * @package Velocity
 * @since 1.0.0
 */
?>

	<footer id="colophon" class="site-footer">
		<div class="container">

			<?php if ( is_active_sidebar( 'footer-1' ) || is_active_sidebar( 'footer-2' ) || is_active_sidebar( 'footer-3' ) || is_active_sidebar( 'footer-4' ) ) : ?>
				<div class="footer-widgets">
					<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
						<?php if ( is_active_sidebar( 'footer-' . $i ) ) : ?>
							<div class="footer-widget-area">
								<?php dynamic_sidebar( 'footer-' . $i ); ?>
							</div>
						<?php endif; ?>
					<?php endfor; ?>
				</div>
			<?php endif; ?>

			<div class="footer-bottom">
				<p>&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'velocity' ); ?></p>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'footer',
					'menu_id'        => 'footer-menu',
					'container'      => false,
					'depth'          => 1,
					'fallback_cb'    => false,
				) );
				?>
			</div>

		</div>
	</footer>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
