<?php
/**
 * The template for displaying search results
 *
 * @package Velocity
 * @since 1.0.0
 */

get_header(); ?>

<main id="main" class="site-main">
	<div class="container">

		<header class="page-header section-sm">
			<h1 class="page-title">
				<?php
				printf(
					esc_html__( 'Search Results for: %s', 'velocity' ),
					'<span>' . get_search_query() . '</span>'
				);
				?>
			</h1>
		</header>

		<?php if ( have_posts() ) : ?>

			<div class="posts-grid grid grid-2">

				<?php while ( have_posts() ) : the_post(); ?>

					<article id="post-<?php the_ID(); ?>" <?php post_class( 'card' ); ?>>

						<?php if ( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail( 'velocity-portfolio', array( 'class' => 'card-image', 'loading' => 'lazy' ) ); ?>
							</a>
						<?php endif; ?>

						<div class="card-content">
							<h2 class="card-title">
								<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h2>

							<div class="entry-meta">
								<?php velocity_posted_on(); ?>
								<?php if ( 'post' === get_post_type() ) : ?>
									<?php velocity_posted_by(); ?>
								<?php endif; ?>
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

			<div class="no-results section">
				<h2><?php esc_html_e( 'Nothing Found', 'velocity' ); ?></h2>
				<p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with different keywords.', 'velocity' ); ?></p>
				<?php get_search_form(); ?>
			</div>

		<?php endif; ?>

	</div>
</main>

<?php get_footer(); ?>
