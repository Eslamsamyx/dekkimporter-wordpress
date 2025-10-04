<?php
/**
 * The template for displaying comments
 *
 * @package Velocity
 * @since 1.0.0
 */

if ( post_password_required() ) {
	return;
}
?>

<div id="comments" class="comments-area">

	<?php if ( have_comments() ) : ?>
		<h3 class="comments-title">
			<?php
			$comments_number = get_comments_number();
			if ( '1' === $comments_number ) {
				printf( esc_html__( 'One comment on "%s"', 'velocity' ), get_the_title() );
			} else {
				printf(
					esc_html( _nx( '%1$s comment on "%2$s"', '%1$s comments on "%2$s"', $comments_number, 'comments title', 'velocity' ) ),
					number_format_i18n( $comments_number ),
					get_the_title()
				);
			}
			?>
		</h3>

		<ol class="comment-list">
			<?php
			wp_list_comments( array(
				'style'       => 'ol',
				'short_ping'  => true,
				'avatar_size' => 60,
				'callback'    => 'velocity_comment',
			) );
			?>
		</ol>

		<?php
		the_comments_pagination( array(
			'prev_text' => '<span class="screen-reader-text">' . esc_html__( 'Previous', 'velocity' ) . '</span>',
			'next_text' => '<span class="screen-reader-text">' . esc_html__( 'Next', 'velocity' ) . '</span>',
		) );
		?>

	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'velocity' ); ?></p>
	<?php endif; ?>

	<?php
	comment_form( array(
		'title_reply_before' => '<h3 id="reply-title" class="comment-reply-title">',
		'title_reply_after'  => '</h3>',
		'class_submit'       => 'btn btn-primary',
	) );
	?>

</div>
