<?php
/**
 * Plugin Name: Good Reviews
 * Plugin URI: http://themeofthecrop.com
 * Description: Add snippets of positive reviews and link to good reviews of your product or services on other websites. Outputs proper Schema.org markup to ensure the reviews are picked up by Google and other search indexers.
 * Version: 0.0.1
 * Author: Theme of the Crop
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

		// Initialize the plugin
		add_action( 'init', array( $this, 'load_config' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );

	}

	/**
	 * Load the plugin's configuration settings and default content
	 * @since 0.0.1
	 */
	public function load_config() {
	
		// Generate a new thumbnail size for reviewers
		$size = apply_filters( 'grfwp_thumbnail_size', array( 'x' => '300', 'y' => '300' ) );
		add_image_size( 'fdm-item-thumb', $size['x'], $size['y'] ), true );
	}

	/**
	 * Load the plugin textdomain for localistion
	 * @since 0.0.1
	 */
	public function load_textdomain() {
		load_plugin_textdomain( GRFWP_TEXTDOMAIN, false, plugin_basename( dirname( __FILE__ ) ) . "/languages" );
	}

}
} // endif;

$grfwp_controller = new grfwpInit();