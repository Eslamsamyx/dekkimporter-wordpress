<?php
/**
 * The main template file
 *
 * @package Velocity
 * @since 1.0.0
 */

get_header(); ?>

<main id="main" class="site-main">
	<div class="container">
		<div class="content-area">

			<?php if ( have_posts() ) : ?>

				<div class="posts-grid grid grid-2">

					<?php while ( have_posts() ) : the_post(); ?>

						<article id="post-<?php the_ID(); ?>" <?php post_class( 'card' ); ?>>

							<?php if ( has_post_thumbnail() ) : ?>
								<a href="<?php the_permalink(); ?>">
									<?php the_post_thumbnail( 'velocity-portfolio', array( 'class' => 'card-image', 'loading' => 'lazy' ) ); ?>
								</a>
							<?php else : ?>
								<a href="<?php the_permalink(); ?>">
									<img src="https://source.unsplash.com/800x600/?business,office,<?php echo esc_attr( get_the_ID() ); ?>"
									     alt="<?php the_title_attribute(); ?>"
									     class="card-image"
									     loading="lazy">
								</a>
							<?php endif; ?>

							<div class="card-content">
								<h2 class="card-title">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h2>

								<div class="entry-meta">
									<?php velocity_posted_on(); ?>
									<?php velocity_posted_by(); ?>
								</div>

								<div class="card-text">
									<?php the_excerpt(); ?>
								</div>

								<a href="<?php the_permalink(); ?>" class="btn btn-primary">
									<?php esc_html_e( 'Read More', 'velocity' ); ?>
								</a>
							</div>

						</article>

					<?php endwhile; ?>

				</div>

				<?php velocity_pagination(); ?>

			<?php else : ?>

				<div class="no-results">
					<h1><?php esc_html_e( 'Nothing Found', 'velocity' ); ?></h1>
					<p><?php esc_html_e( 'Sorry, no posts matched your criteria.', 'velocity' ); ?></p>
					<?php get_search_form(); ?>
				</div>

			<?php endif; ?>

		</div>
	</div>
</main>

<?php get_footer(); ?>
