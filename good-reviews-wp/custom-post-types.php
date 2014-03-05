<?php
/**
 * Class to handle all custom post type definitions for Tour Packages for WordPress
 */

if ( !defined( 'ABSPATH' ) )
	exit;

if ( !class_exists( 'grfwpCustomPostTypes' ) ) {
class grfwpCustomPostTypes {

	public $post_metadata = null;

	public $current_post = 0;

	// Default metadata values
	public $metadata_defaults = array(
		'review_url' 		=> '',
		'review_date' 		=> '',
		'reviewer_org' 		=> '',
		'reviewer_url' 		=> '',
		'rating' 			=> '',
		'rating_max' 		=> '',
		'rating_display'	=> '',
	);

	public function __construct() {

		// Call when plugin is initialized on every page load
		add_action( 'init', array( $this, 'load_cpts' ) );

		// Handle metaboxes
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_meta' ) );

	}

	/**
	 * Initialize custom post types
	 * @since 0.1
	 */
	public function load_cpts() {

		// Define the tour taxonomies
		$review_taxonomies = array(

			// Create tour sections (day trips, walking tours, etc)
			GRFWP_REVIEW_CATEGORY	=> array(
				'hierarchical'		=> true,
				'labels' 		=> array(
					'name' 			=> _x( 'Review Categories', 'taxonomy general name', GRFWP_TEXTDOMAIN ),
					'singular_name' => _x( 'Review Category', 'taxonomy singular name', GRFWP_TEXTDOMAIN ),
					'search_items' 	=> __( 'Search Review Categories', GRFWP_TEXTDOMAIN ),
					'all_items' 	=> __( 'All Review Categories', GRFWP_TEXTDOMAIN ),
					'parent_item' 	=> __( 'Review Category', GRFWP_TEXTDOMAIN ),
					'parent_item_colon' => __( 'Review Category:', GRFWP_TEXTDOMAIN ),
					'edit_item' 	=> __( 'Edit Review Category', GRFWP_TEXTDOMAIN ),
					'update_item' 	=> __( 'Update Review Category', GRFWP_TEXTDOMAIN ),
					'add_new_item' 	=> __( 'Add New Review Category', GRFWP_TEXTDOMAIN ),
					'new_item_name' => __( 'Review Category', GRFWP_TEXTDOMAIN ),
				)
			)
		);

		// Create filter so addons can modify the taxonomies
		$review_taxonomies = apply_filters( 'grfwp_review_taxonomies', $review_taxonomies );

		// Register taxonomies
		foreach( $review_taxonomies as $id => $taxonomy ) {
			register_taxonomy(
				$id,
				'',
				$taxonomy
			);
		}

		// Define the tour custom post type
		$args = array(
			'has_archive' => __( 'reviews', GRFWP_TEXTDOMAIN ),
			'labels' => array(
				'name'               => __( 'Good Reviews',                   GRFWP_TEXTDOMAIN ),
				'singular_name'      => __( 'Good Review',                    GRFWP_TEXTDOMAIN ),
				'menu_name'          => __( 'Good Reviews',                   GRFWP_TEXTDOMAIN ),
				'name_admin_bar'     => __( 'Good Reviews',                   GRFWP_TEXTDOMAIN ),
				'add_new'            => __( 'Add New',                 GRFWP_TEXTDOMAIN ),
				'add_new_item'       => __( 'Add New Good Review',            GRFWP_TEXTDOMAIN ),
				'edit_item'          => __( 'Edit Good Review',               GRFWP_TEXTDOMAIN ),
				'new_item'           => __( 'New Good Review',                GRFWP_TEXTDOMAIN ),
				'view_item'          => __( 'View Good Review',               GRFWP_TEXTDOMAIN ),
				'search_items'       => __( 'Search Good Reviews',            GRFWP_TEXTDOMAIN ),
				'not_found'          => __( 'No good reviews found',          GRFWP_TEXTDOMAIN ),
				'not_found_in_trash' => __( 'No good reviews found in trash', GRFWP_TEXTDOMAIN ),
				'all_items'          => __( 'All Good Reviews',               GRFWP_TEXTDOMAIN ),
			),
			'menu_position' => 25,
			'public' => true,
			'rewrite' => array(
				'slug' => 'review'
			),
			'show_ui' => true,
			'supports' => array(
				'title',
				'editor',
				'thumbnail',
				'page-attributes',
				'revisions'
			),
			'taxonomies' => array_keys( $review_taxonomies )
		);

		// Create filter so addons can modify the arguments
		$args = apply_filters( 'grfwp_review_args', $args );

		// Add an action so addons can hook in before the review is registered
		do_action( 'grfwp_review_pre_register' );

		// Register the review post type
		register_post_type( GRFWP_REVIEW_POST_TYPE, $args );

		// Add an action so addons can hook in after the review is registered
		do_action( 'grfwp_review_post_register' );

	}

	/**
	 * Add metaboxes to specify custom post type data
	 * @since 0.1
	 */
	public function add_meta_boxes() {

		$meta_boxes = array(

			// Link to the review
			'grfwp_review_url' => array (
				'id'		=>	'grfwp_review_url',
				'title'		=> __( 'Review Link', GRFWP_TEXTDOMAIN ),
				'callback'	=> array( $this, 'show_review_url' ),
				'post_type'	=> GRFWP_REVIEW_POST_TYPE,
				'context'	=> 'normal',
				'priority'	=> 'default'
			),

			// Reviewer details
			'grfwp_reviewer' => array (
				'id'		=>	'grfwp_reviewer',
				'title'		=> __( 'Reviewer Details', GRFWP_TEXTDOMAIN ),
				'callback'	=> array( $this, 'show_reviewer' ),
				'post_type'	=> GRFWP_REVIEW_POST_TYPE,
				'context'	=> 'normal',
				'priority'	=> 'default'
			),

			// Rating details
			'grfwp_rating' => array (
				'id'		=>	'grfwp_rating',
				'title'		=> __( 'Rating', GRFWP_TEXTDOMAIN ),
				'callback'	=> array( $this, 'show_rating' ),
				'post_type'	=> GRFWP_REVIEW_POST_TYPE,
				'context'	=> 'normal',
				'priority'	=> 'default'
			),

			// Show the shortcode
			'grfwp_shortcode' => array (
				'id'		=>	'grfwp_shortcode',
				'title'		=> __( 'Review Shortcode', GRFWP_TEXTDOMAIN ),
				'callback'	=> array( $this, 'show_shortcode' ),
				'post_type'	=> GRFWP_REVIEW_POST_TYPE,
				'context'	=> 'side',
				'priority'	=> 'core'
			),

		);

		// Create filter so addons can modify the metaboxes
		$meta_boxes = apply_filters( 'grfwp_meta_boxes', $meta_boxes );

		// Create the metaboxes
		foreach ( $meta_boxes as $meta_box ) {
			add_meta_box(
				$meta_box['id'],
				$meta_box['title'],
				$meta_box['callback'],
				$meta_box['post_type'],
				$meta_box['context'],
				$meta_box['priority']
			);
		}
	}

	/**
	 * Print the review URL metabox HTML
	 * @since 0.1
	 */
	public function show_review_url() {

		$this->get_post_metadata();
		$review_url = isset( $this->post_metadata['review_url'] ) ? $this->post_metadata['review_url'] : '';
		?>

		<p>
			<label for="grfwp[review_url]" class="screen-reader-text"><?php echo __( 'Review Link', GRFWP_TEXTDOMAIN ); ?></label>
			<input class="large-text" type="text" name="grfwp[review_url]" id="grfwp[review_url]" value="<?php echo esc_attr( $review_url ); ?>" placeholder="http://">
		</p>

		<?php
	}

	/**
	 * Print the reviewer details metabox HTML
	 * @since 0.1
	 */
	public function show_reviewer() {

		$this->get_post_metadata();
		$reviewer_org = isset( $this->post_metadata['reviewer_org'] ) ? $this->post_metadata['reviewer_org'] : '';
		$reviewer_url = isset( $this->post_metadata['reviewer_url'] ) ? $this->post_metadata['reviewer_url'] : '';
		$review_date = isset( $this->post_metadata['review_date'] ) ? $this->post_metadata['review_date'] : '';
		?>

		<p>
			<label for="grfwp[reviewer_org]"><?php echo __( 'Reviewer Organization', GRFWP_TEXTDOMAIN ); ?></label><br>
			<input type="text" name="grfwp[reviewer_org]" id="grfwp[reviewer_org]" value="<?php echo esc_attr( $reviewer_org ); ?>">
		</p>
		<p>
			<label for="grfwp[reviewer_url]"><?php echo __( 'Organization Website', GRFWP_TEXTDOMAIN ); ?></label><br>
			<input type="text" name="grfwp[reviewer_url]" id="grfwp[reviewer_url]" value="<?php echo esc_attr( $reviewer_url ); ?>" placeholder="http://">
		</p>
		<p>
			<label for="grfwp[review_date]"><?php echo __( 'Review Date', GRFWP_TEXTDOMAIN ); ?></label><br>
			<input type="text" name="grfwp[review_date]" id="grfwp[review_date]" value="<?php echo esc_attr( $review_date ); ?>">
		</p>

		<?php
	}

	/**
	 * Print the rating details metabox HTML
	 * @since 0.1
	 */
	public function show_rating() {

		$this->get_post_metadata();
		$rating = isset( $this->post_metadata['rating'] ) ? $this->post_metadata['rating'] : '';
		$rating_max = isset( $this->post_metadata['rating_max'] ) ? $this->post_metadata['rating_max'] : 5; // default of 5 is set
		$rating_display = isset( $this->post_metadata['rating_display'] ) ? $this->post_metadata['rating_display'] : '';
		?>

		<p>
			<label for="grfwp[rating]"><?php echo __( 'Rating', GRFWP_TEXTDOMAIN ); ?></label><br>
			<input type="number" step="1" min="0" class="inline-text-number" name="grfwp[rating]" id="grfwp[rating]" value="<?php echo esc_attr( $rating ); ?>">
			<?php echo __( 'out of', GRFWP_TEXTDOMAIN ); ?>
			<label for="grfwp[rating_max]" class="screen-reader-text"><?php echo __( 'Maximum rating possible', GRFWP_TEXTDOMAIN ); ?></label>
			<input type="number" step="1" min="0" class="inline-text-number" name="grfwp[rating_max]" id="grfwp[rating_max]" value="<?php echo esc_attr( $rating_max ); ?>">
		</p>
		<p>
			<input type="radio" name="grfwp[rating_display]" id="grfwp[rating_display]" value=""<?php if( !$rating_display ) : ?> checked="checked"<?php endif; ?>>
			<label for="grfwp[rating_display]"><?php echo __( 'Don\'t show the rating', GRFWP_TEXTDOMAIN ); ?></label>
		</p>
		<p>
			<input type="radio" name="grfwp[rating_display]" id="grfwp[rating_display][numbers]" value="numbers"<?php if( $rating_display == 'numbers' ) : ?> checked="checked"<?php endif; ?>>
			<label for="grfwp[rating_display][numbers]"><?php echo __( 'Show rating as numbers (9/10)', GRFWP_TEXTDOMAIN ); ?></label>
		</p>
		<p>
			<input type="radio" name="grfwp[rating_display]" id="grfwp[rating_display][stars]" value="stars"<?php if( $rating_display == 'stars' ) : ?> checked="checked"<?php endif; ?>>
			<label for="grfwp[rating_display][stars]"><?php echo __( 'Show rating as stars', GRFWP_TEXTDOMAIN ); ?></label>
		</p>

		<?php
	}

	/**
	 * Print the review shortcode HTML on the edit page for easy reference
	 * @since 0.1
	 */
	public function show_shortcode() {

		global $post;

		// Add the nonce here for security
		?>

		<input type="hidden" name="grfwp_nonce" value="<?php echo wp_create_nonce( basename( __FILE__ ) ); ?>">

		<?php
		// Show advisory note when shortcode isn't ready
		if ( get_post_status( $post->ID ) != 'publish' ) {
		?>

			<p><?php echo __( 'Once this review is published, look here to find the shortcode you will use to display this review in any post, page or text widget.', GRFWP_TEXTDOMAIN ); ?></p>

		<?php
		// Show the shortcode
		} else {
			?>

				<p><?php echo __( 'Copy and paste the snippet below into any post, page or text widget in order to display this review.', GRFWP_TEXTDOMAIN ); ?></p>
				<code>[good-reviews review=<?php echo $post->ID; ?>]</code>
				<p><?php echo __( 'To show all reviews in a list, use the snippet below.', GRFWP_TEXTDOMAIN ); ?></p>
				<code>[good-reviews]</code>

			<?php
		}
	}

	/**
	 * Retrieve post metadata
	 * @since 0.1
	 */
	public function get_post_metadata( $id = null) {
		if ( !isset( $id ) ) {
			global $post;
			$id = $post->ID;
		}
		if ( !isset( $this->post_metadata ) || $id != $this->current_post ) {
			$this->current_post = $id;

			$this->metadata_defaults = apply_filters( 'grfwp_review_metadata_defaults', $this->metadata_defaults );
			$this->post_metadata = array_merge( $this->metadata_defaults, get_post_meta( $id, 'grfwp', true ) );
			$this->post_metadata = apply_filters( 'grfwp_review_metadata', $this->post_metadata );
		}
	}

	/**
	 * Check if post has a rating to display
	 * @since 0.1
	 */
	public function has_rating() {

		if ( $this->post_metadata['rating_display'] && $this->post_metadata['rating'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Save the metabox data
	 * @since 1.0
	 */
	public function save_meta( $post_id ) {

		// Verify nonce
		if ( !isset( $_POST['grfwp_nonce'] ) || !wp_verify_nonce( $_POST['grfwp_nonce'], basename( __FILE__ ) ) ) {
			return $post_id;
		}

		// Check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check permissions
		if ( !current_user_can( 'edit_page', $post_id ) ) {
			return $post_id;
		} elseif ( !current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// Save the metadata
		if ( GRFWP_REVIEW_POST_TYPE == $_REQUEST['post_type'] ) {
			$cur = get_post_meta( $post_id, 'grfwp', true );
			$new = grfwpInit::array_filter_recursive( $_REQUEST['grfwp'], 'sanitize_text_field' );
			if ( $new && $new != $cur ) {
				update_post_meta( $post_id, 'grfwp', $new );
			} elseif ( $new == '' && $cur ) {
				delete_post_meta( $post_id, 'grfwp', $cur );
			}
		}
	}

}
} // endif