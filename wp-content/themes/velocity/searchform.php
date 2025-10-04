<?php
/**
 * Template for displaying search forms
 *
 * @package Velocity
 * @since 1.0.0
 */
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label>
		<span class="screen-reader-text"><?php esc_html_e( 'Search for:', 'velocity' ); ?></span>
		<input type="search"
		       class="search-field"
		       placeholder="<?php esc_attr_e( 'Search...', 'velocity' ); ?>"
		       value="<?php echo get_search_query(); ?>"
		       name="s"
		       style="padding: 0.75rem 1rem; border: 2px solid var(--color-light); border-radius: var(--border-radius); font-size: 1rem; width: 100%; max-width: 400px;" />
	</label>
	<button type="submit"
	        class="search-submit btn btn-primary"
	        style="margin-top: 1rem;">
		<?php esc_html_e( 'Search', 'velocity' ); ?>
	</button>
</form>
