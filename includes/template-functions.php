<?php
/**
 * Create a shortcode to one or all reviews
 * @since 0.1
 */
if ( !function_exists( 'grfwp_reviews_shortcode' ) ) {
function grfwp_reviews_shortcode( $atts ) {

	// We must disable the automatic append to content filter while we're
	// rendering the shortcode to prevent it from displaying twice
	global $grfwp_controller;
	remove_action( 'the_content', array( $grfwp_controller, 'append_to_content' ) );

	$output = grfwp_print_reviews(
		shortcode_atts(
			array(
				'review' => null,
				'category' => null
			),
			$atts
		)
	);

	// Restore the_content filter
	add_action( 'the_content', array( $grfwp_controller, 'append_to_content' ) );

	return $output;
}
add_shortcode( 'good-reviews', 'grfwp_reviews_shortcode' );
} // endif;

/*
 * Print reviews
 * @since 0.1
 */
if ( !function_exists( 'grfwp_print_reviews' ) ) {
function grfwp_print_reviews( $args = array() ) {

	$output = apply_filters( 'grfwp_print_reviews_output', false, $args );
	if ( $output !== false ) {
		return $output;
	}

	$output = '';

	global $grfwp_controller;
	$grfwp_controller->get_query_args( $args );

	$reviews = new WP_Query( $grfwp_controller->args );

	if ( $reviews->have_posts() ) :

		// Enqueue the frontend stylesheet
		if ( apply_filters( 'grfwp-load-frontend-assets', true ) ) {
			wp_enqueue_style( 'dashicons' );
			wp_enqueue_style( 'gr-reviews' );
		}

		// Get information about this site to use in the itemReviewed schema.
		$grfwp_controller->get_reviewed_item();

		global $more;

		// Capture output to return in one string
		ob_start();
		?>

		<div class="gr-reviews gr-reviews-<?php if ( !empty( $args['review'] ) ) : ?>single<?php else : ?>all<?php endif; ?>">

		<?php
		while( $reviews->have_posts() ) :
			$reviews->the_post();

			$grfwp_controller->cpts->get_post_metadata( get_the_ID() );
			$post_meta = $grfwp_controller->cpts->post_metadata;
			$post_meta['img'] = get_the_post_thumbnail( get_the_ID(), apply_filters( 'grfwp_the_post_thumbnail_size', 'thumbnail' ) );

			// Remove some of the meta if we're in an archive
			if ( !$more ) {
				$post_meta['review_date'] = '';
				$post_meta['review_url'] = '';
				$post_meta['img'] = '';
			}

			$post_meta = apply_filters( 'grfwp_post_meta', $post_meta );

			// Store css classes to adjust layout
			$classes = grfwp_get_review_css_classes( $post_meta );
			?>

			<blockquote <?php echo $grfwp_controller->format_classes( $classes ); ?> itemscope itemtype="http://schema.org/Review">
				<?php grfwp_the_schema_item_reviewed( $grfwp_controller->reviewed ); ?>

				<div class="gr-content">

					<?php if ( grfwp_has_rating() ) : ?>

					<div class="gr-review-rating gr-rating-<?php echo esc_attr( $post_meta['rating_display'] ); ?>" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
						<meta itemprop="worstRating" content="1">

						<?php if ( $post_meta['rating_display'] == 'numbers' ) : ?>
						<span itemprop="ratingValue"><?php echo esc_attr( $post_meta['rating'] ); ?></span> /
						<span itemprop="bestRating"><?php echo esc_attr( $post_meta['rating_max'] ); ?></span>

						<?php elseif ( $post_meta['rating_display'] == 'stars' ) : ?>
						<meta itemprop="ratingValue" content="<?php echo esc_attr( $post_meta['rating'] ); ?>">
						<meta itemprop="bestRating" content="<?php echo esc_attr( $post_meta['rating_max'] ); ?>">
						<span class="screen-reader-text"><?php echo esc_attr( $post_meta['rating'] ) . '/' . esc_attr( $post_meta['rating_max'] ); ?></span>
						<?php echo str_repeat( grfwp_the_star( true ), $post_meta['rating'] ); ?>
						<?php echo str_repeat( grfwp_the_star( false ), ( $post_meta['rating_max'] - $post_meta['rating'] ) ); ?>

						<?php endif; ?>
					</div>

					<?php endif; ?>

					<div class="gr-review-body" itemprop="reviewBody">
					<?php if ( !$more ) : ?>
						<?php echo the_excerpt(); ?>
					<?php else : ?>
						<?php echo the_content(); ?>
					<?php endif; ?>
					</div>

					<?php
					/**
					 * @todo <time> won't validate unless I have a properly
					 * formatted date. Implement a date-picker so I can control
					 * the date's input value and use the <time> element here.
					 * I'll probably also want to add some options for how the
					 * date is then displayed.
					 */
					if ( $post_meta['review_date'] ) :
					?>
					<span class="gr-review-date" itemprop="datePublished"><?php echo $post_meta['review_date']; ?></span>
					<?php endif; ?>

					<?php if ( $post_meta['review_url'] ) : ?>
					<a class="gr-review-url" itemprop="url" href="<?php echo esc_attr( $post_meta['review_url'] ); ?>">
						<?php echo __( 'Read More', GRFWP_TEXTDOMAIN); ?>
					</a>
					<?php endif; ?>

				</div>

				<?php if ( $more ) : ?>
				<cite class="gr-author" itemprop="author" itemscope itemtype="http://schema.org/Person">
					<div class="gr-author-text">
						<span class="gr-author-name" itemprop="name"><?php echo the_title(); ?></span>

						<?php if ( $post_meta['reviewer_org'] ) : ?>
						<div class="gr-author-affiliation" itemprop="affiliation" itemscope itemtype="http://schema.org/Organization">

							<?php if ( $post_meta['reviewer_url'] ) : ?>
							<a class="gr-author-url" itemprop="url" href="<?php echo esc_attr( $post_meta['reviewer_url'] ); ?>">
								<span itemprop="name"><?php echo $post_meta['reviewer_org']; ?></span>
							</a>
							<?php else : ?>
							<span class="gr-author-org" itemprop="name"><?php echo $post_meta['reviewer_org']; ?></span>
							<?php endif; ?>

						</div>
						<?php endif; ?>

					</div>
					<?php if ( !empty( $post_meta['img'] ) ) : ?>
					<div class="gr-author-img">
							<?php echo $post_meta['img']; ?>
					</div>
					<?php endif; ?>

				</cite>
				<?php endif; ?>

			</blockquote>

		<?php endwhile; ?>

		</div>

	<?php
		$output = ob_get_clean();

	endif;

	// Reset the WP_Query() loop
	wp_reset_postdata();

	return $output;

}
} // endif;

/**
 * Print the schema.org output for the item being reviewed
 * @since 0.0.1
 */
if ( !function_exists( 'grfwp_the_schema_item_reviewed' ) ) {
function grfwp_the_schema_item_reviewed( $item ) {
	?>
	<div itemprop="itemReviewed" itemscope itemtype="http://schema.org/<?php echo $item['schema']; ?>">
		<meta itemprop="name" content="<?php echo $item['name']; ?>">
		<meta itemprop="description" content="<?php echo $item['description']; ?>">
		<meta itemprop="url" content="<?php echo $item['url']; ?>">
	</div>
	<?php
}
} //endif;

/**
 * Specify CSS classes that should be added to each review for easier styling
 * @since 0.0.1
 */
if ( !function_exists( 'grfwp_get_review_css_classes' ) ) {
function grfwp_get_review_css_classes( $post_meta ) {

	$classes = array( 'gr-review' );

	$classes[] = $post_meta['img'] ? 'gr-item-has-image' : 'gr-item-no-image';
	$classes[] = ( $post_meta['rating_display'] && $post_meta['rating'] ) ? 'gr-review-has-rating gr-review-display-' . $post_meta['rating_display'] : '';

	$classes = apply_filters( 'grfwp_review_css_classes', $classes );

	return $classes;
}
} //endif;

/**
 * Wrapper function to check if a review has a rating
 * @since 0.0.1
 */
if ( !function_exists( 'grfwp_has_rating' ) ) {
function grfwp_has_rating() {
	global $grfwp_controller;
	return $grfwp_controller->cpts->has_rating();
}
} //endif;

/**
 * Output HTML markup for a rating star
 * @var $state = boolean; true is filled, false is empty
 * @since 0.0.1
 */
if ( !function_exists( 'grfwp_the_star' ) ) {
function grfwp_the_star( $state ) {
	if ( $state ) {
		return apply_filters( 'grfwp_star_html_filled', '<span class="dashicons dashicons-star-filled"></span>' );
	} else {
		return apply_filters( 'grfwp_star_html_empty', '<span class="dashicons dashicons-star-empty"></span>' );
	}
}
} //endif;
