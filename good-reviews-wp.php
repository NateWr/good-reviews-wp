<?php
/**
 * Plugin Name: Good Reviews for WordPress
 * Plugin URI: http://themeofthecrop.com
 * Description: Add snippets of positive reviews and link to good reviews of your product or services on other websites. Outputs proper Schema.org markup so the reviews can be picked up by Google and other search engines.
 * Version: 1.2.1
 * Author: Theme of the Crop
 * Author URI: http://themeofthecrop.com
 * License: GNU General Public License v2.0 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain: grfwpdomain
 * Domain Path: /languages/
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License as published by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not, write
 * to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( !class_exists( 'grfwpInit' ) ) {
class grfwpInit {

	/**
	 * The single instance of this class
	 */
	private static $instance;

	/**
	 *  WP_Query arguments when retrieving reviews
	 */
	public $args = array();

	/**
	 * Details about the object  being reviews for schema.org markup
	 */
	public $reviewed = array();

	/**
	 * IDs of any printed review sets, to distinguish between multiple
	 * widgest or sets of reviews on a single page
	 */
	public $ids = array();

	/**
	 * Create or retrieve the single instance of the class
	 *
	 * @since 0.1
	 */
	public static function instance() {

		if ( !isset( self::$instance ) ) {

			self::$instance = new grfwpInit();

			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * Initialize the plugin and register hooks
	 */
	public function init() {

		// Common strings
		define( 'GRFWP_TEXTDOMAIN', 'good-reviews-wp' ); // keep for back compat
		define( 'GRFWP_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'GRFWP_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'GRFWP_PLUGIN_FNAME', plugin_basename( __FILE__ ) );
		define( 'GRFWP_REVIEW_POST_TYPE', 'grfwp-review' );
		define( 'GRFWP_REVIEW_CATEGORY', 'grfwp-category' );

		// Initialize the plugin
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Load custom post types
		require_once( GRFWP_PLUGIN_DIR . '/includes/CustomPostTypes.class.php' );
		$this->cpts = new grfwpCustomPostTypes();

		// Load template functions
		require_once( GRFWP_PLUGIN_DIR . '/includes/template-functions.php' );

		// Load code to integrate with other plugins
		require_once( GRFWP_PLUGIN_DIR . '/includes/Integrations.class.php' );
		new grfwpIntegrations();

		// Register assets
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Reword the title placeholder text for a review post type
		add_filter( 'enter_title_here', array( $this, 'rename_review_title' ) );

		// Order review posts in admin screen by menu order
		add_filter('pre_get_posts', array( $this, 'admin_order_posts' ) );

		// Transform review $content variable to output review
		add_filter( 'the_content', array( $this, 'append_to_content' ) );

		// Register the widget
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );

		// Add links to plugin listing
		add_filter('plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2);

	}

	/**
	 * Flush the rewrite rules when this plugin is activated to update with
	 * custom post types
	 * @since 0.1
	 */
	public function rewrite_flush() {
		$this->cpts->load_cpts();
		flush_rewrite_rules();
	}

	/**
	 * Load the plugin textdomain for localistion
	 * @since 0.0.1
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'good-reviews-wp', false, plugin_basename( dirname( __FILE__ ) ) . "/languages/" );

		// Backwards compatibility
		load_plugin_textdomain( 'grfwpdomain', false, plugin_basename( dirname( __FILE__ ) ) . "/languages/" );
	}

	/**
	 * Register stylesheet
	 * @since 0.0.1
	 */
	public function register_assets() {
		wp_register_style( 'gr-reviews', GRFWP_PLUGIN_URL . '/assets/css/style.css', '1.0' );
		wp_register_script( 'gr-reviews', GRFWP_PLUGIN_URL . '/assets/js/frontend.js', array( 'jquery' ), false, true  );
	}

	/**
	 * Enqueue the admin-only CSS
	 * @since 1.1
	 */
	public function enqueue_admin_assets() {
		wp_add_inline_style( 'wp-admin', '#adminmenu #menu-posts-grfwp-review .wp-menu-image:before { content: "\f313"; }' );
	}

	/**
	 * Reword the title placeholder text for a review post type
	 * @since 0.1
	 */
	public function rename_review_title( $title ){
		 $screen = get_current_screen();

		 if  ( $screen->post_type == GRFWP_REVIEW_POST_TYPE ) {
			  $title = __( 'Enter reviewer here', 'good-reviews-wp' );
		 }

		 return $title;
	}

	/**
	 * Order the reviews by menu order parameter in the admin interface
	 * @since 0.1
	 */
	public function admin_order_posts( $query ) {

		if( ( is_admin() && $query->is_admin ) && $query->get( 'post_type' ) == GRFWP_REVIEW_POST_TYPE ) {

			// Don't override an existing orderby setting. This prevents other
			// orderby options from breaking.
			if ( !$query->get( 'orderby' ) ) {
				$query->set( 'orderby', 'menu_order' );
				$query->set( 'order', 'ASC' );
			}
		}

		return $query;
	}

	/**
	 * Run callback on every element in array recursively
	 *
	 * Used to sanitize all values in an array
	 * @since 0.1
	 */
	public static function array_filter_recursive( $arr, $callback ) {
		foreach ( $arr as &$value ) {
			if ( is_array( $value ) ) {
				$value = grfwpInit::array_filter_recursive( $value, $callback );
			}
		}
		return array_filter( $arr, $callback );
	}

	/**
	 * Tranform an array of CSS classes into an HTML attribute
	 * @since 0.1
	 */
	public function format_classes( $classes ) {
		if ( count( $classes ) ) {
			return ' class="' . join(" ", $classes) . '"';
		}
	}

	/**
	 * Process review query arguments
	 * @since 0.1
	 */
	public function get_query_args( $args ) {

		// Set and filter defaults
		$this->args = array(
			'posts_per_page' => 100, // sane upper limit
			'post_type' => GRFWP_REVIEW_POST_TYPE,
			'orderby' => 'menu_order',
			'order' => 'ASC',
			'cycle'	=> false,
			'excerpt'	=> false,
		);
		$this->args = apply_filters( 'grfwp_query_args_defaults', $this->args );

		if ( !empty( $args['review'] ) ) {
			$this->args['p'] = $args['review'];
			unset( $this->args['posts_per_page'] );
		}

		if ( !empty( $args['category'] ) ) {
			$this->args[GRFWP_REVIEW_CATEGORY] = $args['category'];
		}

		if ( !empty( $args['random'] ) ) {
			$this->args['orderby'] = 'rand';
		}

		if ( !empty( $args['limit'] ) ) {
			$this->args['posts_per_page'] = $args['limit'];
		}

		if ( !empty( $args['cycle'] ) ) {
			$this->args['cycle'] = $args['cycle'];
		}

		if ( !empty( $args['excerpt'] ) ) {
			$this->args['excerpt'] = $args['excerpt'];
		}

		$this->args = apply_filters( 'grfwp_query_args', $this->args );
	}

	/**
	 * Retrieve schema.org details for the item being reviews
	 * @note $args is in place for future compatibility, for instance if support
	 *		for multiple venues is added
	 * @since 0.1
	 */
	public function get_reviewed_item( $args = array() ) {

		// Set and filter defaults
		// @todo use schema from settings
		$this->reviewed = array(
			'name'			=> esc_attr( get_bloginfo( 'name' ) ),
			'url'			=> esc_attr( get_bloginfo( 'url' ) ),
			'description'	=> esc_attr( get_bloginfo( 'description' ) ),
			'schema'		=> 'Thing',
		);

		$this->reviewed = apply_filters( 'grfwp_reviewed_defaults', $this->reviewed );

		$this->reviewed = array_merge( $this->reviewed, $args );

		$this->reviewed = apply_filters( 'grfwp_reviewed_values', $this->reviewed );
	}

	/**
	 * Transform review $content variable to output review
	 * @since 0.1
	 */
	function append_to_content( $content ) {
		global $post;

		if ( !in_the_loop() || !is_main_query() || is_search() || GRFWP_REVIEW_POST_TYPE !== $post->post_type ) {
			return $content;
		}

		// Allow overrides to disable the automatic append to content filter
		if ( !apply_filters( 'grfwp_append_to_content', true ) ) {
			return $content;
		}

		// We must disable this filter while we're rendering the reiew in order
		// to prevent it from falling into a recursive loop with each review's
		// content.
		remove_action( 'the_content', array( $this, 'append_to_content' ) );

		$args = array(
			'review'	=> $post->ID,
		);
		$args = apply_filters( 'grfwp_post_content_args', $args );

		$content = grfwp_print_reviews( $args );

		// Restore this filter
		add_action( 'the_content', array( $this, 'append_to_content' ) );

		return $content;
	}

	/**
	 * Register the widgets
	 * @since 0.0.1
	 */
	public function register_widgets() {
		require_once( GRFWP_PLUGIN_DIR . '/includes/WP_Widget.ReviewsWidget.class.php' );
		register_widget( 'grfwpWidgetReviews' );
	}

	/**
	 * Add links to the plugin listing on the installed plugins page
	 * @since 0.0.1
	 */
	public function plugin_action_links( $links, $plugin ) {

		if ( $plugin == GRFWP_PLUGIN_FNAME ) {

			$links['help'] = '<a href="' . GRFWP_PLUGIN_URL . '/docs" title="' . __( 'View the help documentation for Business Profile', 'good-reviews-wp' ) . '">' . __( 'Help', 'good-reviews-wp' ) . '</a>';
		}

		return $links;

	}

}
} // endif;

// Global instance
$grfwp_controller = grfwpInit::instance();

// Flush the rewrite rules for the custom post types
register_activation_hook( __FILE__, array( $grfwp_controller, 'rewrite_flush' ) );
