<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package p2-breathe
 */

if ( ! function_exists( 'breathe_content_nav' ) ) :
/**
 * Display navigation to next/previous pages when applicable
 */
function breathe_content_nav( $nav_id ) {
	global $wp_query, $post;

	// Don't print empty markup on single pages if there's nowhere to navigate.
	if ( is_single() ) {
		$previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
		$next = get_adjacent_post( false, '', false );

		if ( ! $next && ! $previous )
			return;
	}

	// Don't print empty markup in archives if there's only one page.
	if ( $wp_query->max_num_pages < 2 && ( is_home() || is_archive() || is_search() ) )
		return;

	$nav_class = ( is_single() ) ? 'navigation-post' : 'navigation-paging';

	?>
	<nav role="navigation" id="<?php echo esc_attr( $nav_id ); ?>" class="<?php echo $nav_class; ?>">
		<h1 class="screen-reader-text"><?php _e( 'Post navigation', 'p2-breathe' ); ?></h1>

	<?php if ( is_single() ) : // navigation links for single posts ?>

		<?php previous_post_link( '<div class="nav-previous">%link</div>', '<span class="meta-nav">' . _x( '&larr;', 'Previous post link', 'p2-breathe' ) . '</span> %title' ); ?>
		<?php next_post_link( '<div class="nav-next">%link</div>', '%title <span class="meta-nav">' . _x( '&rarr;', 'Next post link', 'p2-breathe' ) . '</span>' ); ?>

	<?php elseif ( $wp_query->max_num_pages > 1 && ( is_home() || is_archive() || is_search() ) ) : // navigation links for home, archive, and search pages ?>

		<?php if ( get_next_posts_link() ) : ?>
		<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'p2-breathe' ) ); ?></div>
		<?php endif; ?>

		<?php if ( get_previous_posts_link() ) : ?>
		<div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'p2-breathe' ) ); ?></div>
		<?php endif; ?>

	<?php endif; ?>

	</nav><!-- #<?php echo esc_html( $nav_id ); ?> -->
	<?php
}
endif; // breathe_content_nav

if ( ! function_exists( 'breathe_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 */
function breathe_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
	?>
	<li class="post pingback">
		<p><?php _e( 'Pingback:', 'p2-breathe' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( 'Edit', 'p2-breathe' ), '<span class="edit-link">', '<span>' ); ?></p>
	<?php
			break;
		default :
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<article id="comment-<?php comment_ID(); ?>" class="comment">
			<footer>
				<?php echo get_avatar( $comment, 32 ); ?>

				<div class="comment-meta commentmetadata">
				<?php echo get_comment_author_link(); ?>

				<span class="comment-date">
					<?php breathe_date_time_with_microformat( 'comment' ); ?>
				</span>
				<span class="comment-actions">
					<?php do_action( 'breathe_comment_actions', $args, $depth ); ?>
				</span>
				</div><!-- .comment-meta .commentmetadata -->
			</footer>

			<div class="comment-content"><?php comment_text(); ?></div>
		</article><!-- #comment-## -->

	<?php
			break;
	endswitch;
}
endif; // ends check for breathe_comment()

/**
 * Returns true if a blog has more than 1 category
 */
function breathe_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'all_the_cool_cats' ) ) ) {
		// Create an array of all the categories that are attached to posts
		$all_the_cool_cats = get_categories( array(
			'hide_empty' => 1,
		) );

		// Count the number of categories that are attached to the posts
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'all_the_cool_cats', $all_the_cool_cats );
	}

	if ( '1' != $all_the_cool_cats ) {
		// This blog has more than 1 category so breathe_categorized_blog should return true
		return true;
	} else {
		// This blog has only 1 category so breathe_categorized_blog should return false
		return false;
	}
}

/**
 * Flush out the transients used in breathe_categorized_blog
 */
function breathe_category_transient_flusher() {
	// Like, beat it. Dig?
	delete_transient( 'all_the_cool_cats' );
}
add_action( 'edit_category', 'breathe_category_transient_flusher' );
add_action( 'save_post', 'breathe_category_transient_flusher' );

/**
 *
 */
function breathe_tags_with_count( $format = 'list', $before = '', $sep = '', $after = '' ) {
	global $post;
	echo breathe_get_tags_with_count( $post, $format, $before, $sep, $after );
}

	/**
	 * Get tags with count
	 */
	function breathe_get_tags_with_count( $post, $format = 'list', $before = '', $sep = '', $after = '' ) {
		$posttags = get_the_tags($post->ID, 'post_tag' );

		if ( !$posttags )
			return '';

		foreach ( $posttags as $tag ) {
			if ( $tag->count > 1 && !is_tag($tag->slug) ) {
				$tag_link = '<a href="' . get_term_link($tag, 'post_tag' ) . '" rel="tag">' . $tag->name . ' ( ' . number_format_i18n( $tag->count ) . ' )</a>';
			} else {
				$tag_link = $tag->name;
			}

			if ( $format == 'list' )
				$tag_link = '<li>' . $tag_link . '</li>';

			$tag_links[] = $tag_link;
		}

		return apply_filters( 'breathe_tags_with_count', $before . join( $sep, $tag_links ) . $after, $post );
	}

function breathe_date_time_with_microformat( $type = 'post' ) {
	echo breathe_get_date_time_with_microformat( $type );
}

	function breathe_get_date_time_with_microformat( $type = 'post' ) {
		$d = 'comment' == $type ? 'get_comment_time' : 'get_post_time';
		return '<abbr title="' . $d( 'Y-m-d\TH:i:s\Z', true ) . '">' . sprintf( __( '%1$s <em>on</em> %2$s', 'p2-breathe' ),  $d( get_option( 'time_format' ) ), $d( get_option( 'date_format' ) ) ) . '</abbr>';
	}

function breathe_page_number() {
	echo breathe_get_page_number();
}

	function breathe_get_page_number() {
		global $paged;
		return apply_filters( 'breathe_get_page_number', $paged );
	}
