<?php
/**
 * Plugin Name: Good Reviews for WordPress
 * Plugin URI: http://themeofthecrop.com
 * Description: Add snippets of positive reviews and link to good reviews of your product or services on other websites. Outputs proper Schema.org markup to ensure the reviews are picked up by Google and other search engines.
 * Version: 0.0.1
 * Author: NateWr
 * Author URI: http://themeofthecrop.com
 * License:     GNU General Public License v2.0 or later
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
	 * Initialize the plugin and register hooks
	 */
	public function __construct() {

		// Common strings
		define( 'GRFWP_TEXTDOMAIN', 'grfwpdomain' );
		define( 'GRFWP_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'GRFWP_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
		define( 'GRFWP_PLUGIN_FNAME', plugin_basename( __FILE__ ) );
		define( 'GRFWP_REVIEW_POST_TYPE', 'grfwp-review' );
		define( 'GRFWP_REVIEW_CATEGORY', 'grfwp-category' );

		// Load template functions
		require_once( 'functions.php' );

		// Initialize the plugin
		add_action( 'init', array( $this, 'load_config' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Load custom post types
		require_once( 'custom-post-types.php' );
		$this->cpts = new grfwpCustomPostTypes();

		// Load settings page
		require_once( 'settings.php' );
		$this->settings = new grfwpSettings();

		// Reword the title placeholder text for a review post type
		add_filter( 'enter_title_here', array( $this, 'rename_review_title' ) );

		// Order review posts in admin screen by menu order
		add_filter('pre_get_posts', array( $this, 'admin_order_posts' ) );

		// Transform review $content variable to output review
		add_filter( 'the_content', array( $this, 'append_to_content' ) );

		// Flush the rewrite rules for the custom post types
		register_activation_hook( __FILE__, array( $this, 'rewrite_flush' ) );

		// Register the widget
		require_once( 'widgets/WidgetReviews.class.php' );
		add_action( 'widgets_init', create_function( '', 'return register_widget( "grfwpWidgetReviews" );' ) );

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
	 * Load the plugin's configuration settings and default content
	 * @since 0.0.1
	 */
	public function load_config() {

		// Generate a new thumbnail size for reviewers
		$size = apply_filters( 'grfwp_thumbnail_size', array( 'x' => '300', 'y' => '300' ) );
		add_image_size( 'grfwp-reviewer', $size['x'], $size['y'], true );
	}

	/**
	 * Load the plugin textdomain for localistion
	 * @since 0.0.1
	 */
	public function load_textdomain() {
		load_plugin_textdomain( GRFWP_TEXTDOMAIN, false, plugin_basename( dirname( __FILE__ ) ) . "/languages" );
	}

	/**
	 * Enqueue stylesheets
	 * @since 0.0.1
	 */
	public function enqueue_assets() {
		wp_enqueue_style( 'gr-frontend', GRFWP_PLUGIN_URL . '/assets/css/style.css', '1.0' );
	}

	/**
	 * Reword the title placeholder text for a review post type
	 * @since 0.1
	 */
	public function rename_review_title( $title ){
		 $screen = get_current_screen();

		 if  ( $screen->post_type == GRFWP_REVIEW_POST_TYPE ) {
			  $title = __( 'Enter reviewer here', GRFWP_TEXTDOMAIN );
		 }

		 return $title;
	}

	/**
	 * Order the reviews by menu order parameter in the admin interface
	 * @since 0.1
	 */
	public function admin_order_posts( $query ) {

		if( ( is_admin() && $query->is_admin ) && $query->get( 'post_type' ) == GRFWP_REVIEW_POST_TYPE ) {
			$query->set( 'orderby', 'menu_order' );
			$query->set( 'order', 'ASC' );
		}

		return $query;
	}

	/**
	 * Run callback on every element in array recursively
	 *
	 * Used to sanitize all values in an array
	 * @since 0.1
	 */
	public function array_filter_recursive( $arr, $callback ) {
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
	 * Transform review $content variable to output review
	 * @since 0.1
	 */
	function append_to_content( $content ) {
		global $post;

		if ( GRFWP_REVIEW_POST_TYPE !== $post->post_type || !is_main_query() || !in_the_loop() ) {
			return $content;
		}

		// We must disable this filter while we're rendering the menu in order to
		// prevent it from falling into a recursive loop with each review's
		// content.
		remove_action( 'the_content', array( $this, 'append_to_content' ) );

		$args = array(
			'review'	=> $post->ID
		);
		$args = apply_filters( 'grfwp_post_content_args', $args );

		$content = grfwp_print_reviews( $args );

		// Restore this filter
		add_action( 'the_content', array( $this, 'append_to_content' ) );

		return $content;
	}

}
} // endif;

$grfwp_controller = new grfwpInit();