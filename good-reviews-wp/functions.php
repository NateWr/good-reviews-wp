<?php
/**
 * Create a shortcode to one or all reviews
 * @since 0.1
 */
if ( !function_exists( 'grfwp_reviews_shortcode' ) ) {
function grfwp_reviews_shortcode( $atts ) {
	return grfwp_print_reviews(
		shortcode_atts(
			array(
				'review' => null
			),
			$atts
		)
	);
}
add_shortcode( 'good-reviews', 'grfwp_reviews_shortcode' );
} // endif;

/*
 * Print reviews
 * @since 0.1
 */
if ( !function_exists( 'grfwp_print_reviews' ) ) {
function grfwp_print_reviews( $args ) {

	// WP_Query args
	$q_args = array(
		'posts_per_page' => -1,
		'post_type' => GRFWP_REVIEW_POST_TYPE,
		'orderby' => 'menu-order',
		'order' => 'ASC'
	);

	$q_args = apply_filters( 'grfwp_query_args_defaults', $q_args );

	// Get just one or all reviews
	if ( isset( $args['review'] ) ) {
		$q_args['p'] = $args['review'];
		unset( $q_args['posts_per_page'] );
	}

	$q_args = apply_filters( 'grfwp_query_args', $q_args );

	// Get the query
	$reviews = new WP_Query( $q_args );

	$output = '';

	if( count( $reviews->posts ) ) :

		// Enqueue the frontend scripts and styles
		grfwpInit::enqueue_assets();

		// Get information about this site to use in the itemReviewed schema.
		$reviewed_name = esc_attr( get_bloginfo( 'name' ) );
		$reviewed_url = esc_attr( get_bloginfo( 'url' ) );
		$reviewed_description = esc_attr( get_bloginfo( 'description' ) );
		$reviewed_schema = 'Thing';

		// Capture output to return in one string
		// @note if we print directly here instead of capturing the output, the
		// menu item will appear above any other content in the page/post.
		ob_start();

		?>

		<div class="gr-reviews gr-reviews-<?php if ( isset( $args['review'] ) ) : ?>single<?php else : ?>all<?php endif; ?>">

		<?php

			// Loop over the results and display each review
			$i = 0;
			foreach( $reviews->posts as $post ) :

				// Track the iteration
				$i++;

				// Store css classes to adjust layout
				$css_classes = array( 'gr-review' );

				// Add a class to the last review
				if ( $i == count( $reviews->posts ) ) {
					array_push( $css_classes, 'gr-last-review' );
				}

				// Get the image
				if ( !$img = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'grfwp-reviewer' ) ) {
					array_push( $css_classes, 'gr-item-no-image' );
				}

				$this->cpts->get_post_metadata();
				$review_url = isset( $this->cpts->post_metadata['review_url'] ) ? $this->post_metadata['review_url'] : '';
				$reviewer_org = isset( $this->cpts->post_metadata['reviewer_org'] ) ? $this->post_metadata['reviewer_org'] : '';
				$reviewer_url = isset( $this->cpts->post_metadata['reviewer_url'] ) ? $this->post_metadata['reviewer_url'] : '';
				$reviewer_date = isset( $this->cpts->post_metadata['reviewer_date'] ) ? $this->post_metadata['reviewer_date'] : '';
				$rating = isset( $this->cpts->post_metadata['rating'] ) ? $this->post_metadata['rating'] : '';
				$rating_max = isset( $this->cpts->post_metadata['rating_max'] ) ? $this->post_metadata['rating_max'] : '';
				$rating_display = isset( $this->cpts->post_metadata['rating_display'] ) ? $this->post_metadata['rating_display'] : '';

				// Get the rating
				if ( $rating && $rating_display ) {
					array_push( $css_classes, 'gr-review-has-rating', 'gr-review-display-' . $rating_display );
				}

			?>

			<article <?php echo grfwpInit::format_classes( $css_classes ); ?> itemscope itemtype="http://schema.org/Review">
				<div itemprop="itemReviewed" itemscope itemtype="http://schema.org/<?php echo esc_attr( $reviewed_schema ); ?>">
					<meta itemprop="name" content="<?php echo esc_attr( $reviewed_name ); ?>">
					<meta itemprop="description" content="<?php echo esc_attr( $reviewed_description ); ?>">
					<meta itemprop="url" content="<?php echo esc_attr( $reviewed_url ); ?>">
				</div>

				<div class="gr-content">

					<?php if ( $rating &&  $rating_display ) : ?>

						<div class="gr-review-rating gr-rating-<?php echo esc_attr( $rating_display ); ?>" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
							<meta itemprop="worstRating" content="1">

						<?php if ( $rating_display == 'numbers' ) : ?>

							<span itemprop="ratingValue"><?php echo esc_attr( $rating ); ?></span>
							/
							<span itemprop="bestRating"><?php echo esc_attr( $rating_max ); ?></span>

						<?php elseif ( $rating_display == 'stars' ) : ?>

							<meta itemprop="ratingValue" content="<?php echo esc_attr( $rating ); ?>">
							<meta itemprop="bestRating" content="<?php echo esc_attr( $rating_max ); ?>">
							<?php echo str_repeat( '<img src="' . GRFWP_PLUGIN_URL . '/img/icons/star.png" alt="' . esc_attr( $rating ) . '/' . esc_attr( $rating_max ) . '">', esc_attr( $rating ) ); ?>

						<?php endif; ?>

						</div>

					<?php endif; ?>

					<div class="gr-review-body" itemprop="reviewBody"><?php echo $post->post_content; ?></div>

					<?php

					/**
					 * @todo <time> won't validate unless I have a properly
					 * formatted date. Implement a date-picker so I can control
					 * the date's input value and use the <time> element here.
					 * I'll probably also want to add some options for how the
					 * date is then displayed.
					 */
					if ( $reviewer_date ) :

					?>

						<span class="gr-review-date" itemprop="datePublished"><?php echo $reviewer_date; ?></span>

					<?php endif; ?>

					<?php if ( $review_url ) : ?>

								<a class="gr-review-url" itemprop="url" href="<?php echo esc_attr( $review_url ); ?>">
									<?php echo __( 'Read More', GRFWP_TEXTDOMAIN); ?>
								</a>

					<?php endif; ?>

					<div class="clearfix"></div>
				</div>

				<address class="gr-author" itemprop="author" itemscope itemtype="http://schema.org/Person">

					<div class="gr-author-text">
						<div class="gr-author-name" itemprop="name"><?php echo $post->post_title; ?></div>

						<?php if ( $reviewer_org ) : ?>

						<div class="gr-author-affiliation" itemprop="affiliation" itemscope itemtype="http://schema.org/Organization">

						<?php if ( $reviewer_url) : ?>

							<a class="gr-author-url" itemprop="url" href="<?php echo esc_attr( $reviewer_url ); ?>">
								<div itemprop="name"><?php echo $reviewer_org; ?></div>
							</a>

						<?php elseif ( !$reviewer_url ) : ?>

							<div class="gr-author-org" itemprop="name"><?php echo $reviewer_org; ?></div>

						<?php endif; ?>

						</div>

						<?php endif; ?>

					</div>

					<?php if ( $img ) : ?>

					<div class="gr-author-img">

							<img itemprop="image" src="<?php echo esc_attr( $img['0'] ); ?>" alt="<?php echo __( 'Photo of ', GRFWP_TEXTDOMAIN ) . esc_attr( $post->post_title ); ?>">

					</div>

					<?php endif; ?>

					<div class="clearfix"></div>
				</address>

			</article>

			<?php endforeach; ?>

		</div>

	<?php

		// Capture the HTML output
		$output = ob_get_contents();
		ob_end_clean();

	endif;

	// Reset the WP_Query() loop
	wp_reset_postdata();

	return $output;

}
} // endif;
