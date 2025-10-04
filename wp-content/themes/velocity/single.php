<?php
/**
 * The template for displaying all single posts
 *
 * @package Velocity
 * @since 1.0.0
 */

get_header(); ?>

<main id="main" class="site-main">
	<div class="container">
		<?php while ( have_posts() ) : the_post(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<header class="entry-header section-sm">
					<h1 class="entry-title"><?php the_title(); ?></h1>
					<div class="entry-meta">
						<?php velocity_posted_on(); ?>
						<?php velocity_posted_by(); ?>
						<?php if ( has_category() ) : ?>
							<span class="posted-in">
								<?php esc_html_e( 'in', 'velocity' ); ?>
								<?php the_category( ', ' ); ?>
							</span>
						<?php endif; ?>
					</div>
				</header>

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="entry-thumbnail">
						<?php the_post_thumbnail( 'velocity-hero', array( 'loading' => 'eager' ) ); ?>
					</div>
				<?php endif; ?>

				<div class="entry-content section">
					<?php the_content(); ?>
					<?php
					wp_link_pages( array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'velocity' ),
						'after'  => '</div>',
					) );
					?>
				</div>

				<?php if ( has_tag() ) : ?>
					<footer class="entry-footer">
						<div class="entry-tags">
							<?php the_tags( '<span class="tags-label">' . esc_html__( 'Tags: ', 'velocity' ) . '</span>', ', ' ); ?>
						</div>
					</footer>
				<?php endif; ?>

			</article>

			<?php
			// Previous/Next post navigation
			the_post_navigation( array(
				'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous:', 'velocity' ) . '</span> <span class="nav-title">%title</span>',
				'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next:', 'velocity' ) . '</span> <span class="nav-title">%title</span>',
			) );
			?>

			<?php
			// Comments
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;
			?>

		<?php endwhile; ?>
	</div>
</main>

<?php get_footer(); ?>
