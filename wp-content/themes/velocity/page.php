<?php
/**
 * The template for displaying all pages
 *
 * @package Velocity
 * @since 1.0.0
 */

get_header(); ?>

<main id="main" class="site-main">
	<?php while ( have_posts() ) : the_post(); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<?php if ( has_post_thumbnail() && ! get_post_meta( get_the_ID(), '_elementor_edit_mode', true ) && ! get_post_meta( get_the_ID(), '_wpb_vc_js_status', true ) ) : ?>
				<div class="entry-header" style="background-image: url('<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'velocity-hero' ) ); ?>');">
					<div class="container">
						<h1 class="entry-title text-white"><?php the_title(); ?></h1>
					</div>
				</div>
			<?php endif; ?>

			<div class="entry-content">
				<?php if ( get_post_meta( get_the_ID(), '_elementor_edit_mode', true ) || get_post_meta( get_the_ID(), '_wpb_vc_js_status', true ) ) : ?>
					<?php the_content(); ?>
				<?php else : ?>
					<div class="container">
						<div class="section">
							<?php if ( ! has_post_thumbnail() ) : ?>
								<h1 class="entry-title"><?php the_title(); ?></h1>
							<?php endif; ?>
							<?php the_content(); ?>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( comments_open() || get_comments_number() ) : ?>
				<div class="container">
					<div class="section">
						<?php comments_template(); ?>
					</div>
				</div>
			<?php endif; ?>

		</article>

	<?php endwhile; ?>
</main>

<?php get_footer(); ?>
