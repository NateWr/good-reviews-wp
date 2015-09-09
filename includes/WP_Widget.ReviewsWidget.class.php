<?php

/**
 * Add a widget to display one or all reviews
 *
 * @since 1.0
 * @package Good Reviews
 */
class grfwpWidgetReviews extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 * @since 1.0
	 */
	function __construct() {

		parent::__construct(
			'grfwp_widget_reviews',
			__( 'Good Reviews', 'good-reviews-wp' ),
			array( 'description' => __( 'Display one or all of your reviews.', 'good-reviews-wp' ), )
		);

	}

	/**
	 * Print the widget content
	 * @since 1.0
	 */
	public function widget( $args, $instance ) {

		if ( isset( $instance['review'] ) && substr( $instance['review'], 0, 4 ) === 'cat-' ) {
			$instance['category'] = substr( $instance['review'], 4 );
			unset( $instance['review'] );
		}

		// Get the settings
		$atts = array(
			'review' => null,
			'category' 	=> null,
			'random'	=> false,
			'limit'		=> null,
			'cycle'		=> false,
			'excerpt'	=> false,
		);
		$atts = array_merge( $atts, $instance );

		// Print the widget's HTML markup
		echo $args['before_widget'];
		if( isset( $instance['title'] ) ) {
			$title = apply_filters( 'widget_title', $instance['title'] );
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// Always display the full review in widgets, so modify the global
		// variable...
		global $more;
		$more_setting = $more;
		$more = 1;
		echo grfwp_reviews_shortcode( $atts );
		$more = $more_setting; // ... but don't forget to put it back where you found it!

		echo $args['after_widget'];

	}

	/**
	 * Print the form to configure this widget in the admin panel
	 * @since 1.0
	 */
	public function form( $instance ) {

		$review = empty( $instance['review'] ) ? '' : $instance['review'];
		$cycle = empty( $instance['cycle'] ) ? '' : $instance['cycle'];
		$excerpt = empty( $instance['excerpt'] ) ? '' : $instance['excerpt'];
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"> <?php _e( 'Title', 'good-reviews-wp' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"<?php if ( isset( $instance['title'] ) ) : ?> value="<?php echo esc_attr( $instance['title'] ); ?>"<?php endif; ?>>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'review' ); ?>"> <?php _e( 'Reviews to display' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'review' ); ?>" name="<?php echo $this->get_field_name( 'review' ); ?>">
				<option value=""><?php _e( 'Show all reviews', 'good-reviews-wp' ); ?></option>

				<?php

				$categories = get_terms(
					GRFWP_REVIEW_CATEGORY,
					array(
						'hide_empty'	=> true
					)
				);

				foreach( $categories as $category ) {
					?>
					<option value="cat-<?php echo $category->slug; ?>"<?php if ( $review == 'cat-' . $category->slug ) : ?> selected<?php endif; ?>><?php echo __( 'Category: ', 'good-reviews-wp' )  . esc_attr( $category->name ); ?></option>
					<?php
				}

				$reviews = new WP_Query( array(
						'posts_per_page' 	=> -1,
						'post_type' 		=> GRFWP_REVIEW_POST_TYPE
					)
				);

				// Loop over all promotion post types
				while( $reviews->have_posts() ) :
					$reviews->next_post();

				?>

				<option value="<?php echo $reviews->post->ID; ?>"<?php if ( $reviews->post->ID == $review ) : ?> selected<?php endif; ?>>
					<?php echo esc_attr( $reviews->post->post_title ); ?>
				</option>

				<?php

				endwhile;

				// Reset the loop so we don't interfere with normal template functions
				wp_reset_postdata();

				?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'cycle' ); ?>"> <?php _e( 'Review cycle mode' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'cycle' ); ?>" name="<?php echo $this->get_field_name( 'cycle' ); ?>">
				<option value="">List all reviews</option>
				<option value="fader"<?php if ( $cycle == 'fader' ) : ?> selected<?php endif; ?>>Fade between reviews</option>
			</select>
		</p>
		<p>
			<input type="checkbox" id="<?php echo $this->get_field_id( 'excerpt' ); ?>" name="<?php echo $this->get_field_name( 'excerpt' ); ?>" value="1" <?php checked( $excerpt, 1 ); ?>>
			<label for="<?php echo $this->get_field_id( 'excerpt' ); ?>"><?php _e( 'Show excerpt only', GRFWP_REVIEW_POST_TYPE ); ?></label>
		</p>

		<?php
	}

	/**
	 * Sanitize and save the widget form values.
	 * @since 1.0
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = array();
		
		if ( !empty( $new_instance['title'] ) ) {
			$instance['title'] = strip_tags( $new_instance['title'] );
		}
		
		if ( !empty( $new_instance['review'] ) ) {
			if ( substr( $new_instance['review'], 0, 4 ) === 'cat-' ) {
				$instance['review'] = strip_tags( $new_instance['review'] );
			} else {
				$instance['review'] = intval( $new_instance['review'] );
			}
		}

		$instance['cycle'] = $new_instance['cycle'] == 'fader' ? 'fader' : '';
		$instance['excerpt'] = $new_instance['excerpt'] == 1 ? true : false;

		return $instance;

	}

}
