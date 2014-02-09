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
			'gr_widget_reviews',
			__('Good Reviews', GR_TEXTDOMAIN),
			array( 'description' => __( 'Display one or all of your reviews.', GR_TEXTDOMAIN ), )
		);

	}

	/**
	 * Print the widget content
	 * @since 1.0
	 */
	public function widget( $args, $instance ) {

		// Get the settings
		$atts = array(
			'review' => null
		);
		if( isset( $instance['review'] ) ) {
			$atts['review'] = $instance['review'];
		}

		// Print the widget's HTML markup
		echo $args['before_widget'];
		if( isset( $instance['title'] ) ) {
			$title = apply_filters( 'widget_title', $instance['title'] );
			echo $args['before_title'] . $title . $args['after_title'];
		}
		echo gr_reviews_shortcode( $atts );
		echo $args['after_widget'];

	}

	/**
	 * Print the form to configure this widget in the admin panel
	 * @since 1.0
	 */
	public function form( $instance ) {

		$review = null;
		if ( isset( $instance['review'] ) ) {
			$review = $instance['review'];
		}

		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"> <?php _e( 'Title' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"<?php if ( isset( $instance['title'] ) ) : ?> value="<?php echo esc_attr( $instance['title'] ); ?>"<?php endif; ?>>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'review' ); ?>"> <?php _e( 'Reviews to display' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'review' ); ?>" name="<?php echo $this->get_field_name( 'review' ); ?>">
				<option value="">Show all reviews</option>

				<?php

				$reviews = new WP_Query( array(
						'posts_per_page' 	=> -1,
						'post_type' 		=> 'good-review'
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
			$instance['review'] = intval( $new_instance['review'] );
		}

		return $instance;

	}

}
