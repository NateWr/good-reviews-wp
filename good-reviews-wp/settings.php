<?php

if ( !defined( 'ABSPATH' ) )
	exit;

/**
 * Class to register all settings in the settings panel
 */
class grfwpSettings {

	public function __construct() {

		// Call when plugin is initialized on every page load
		add_action( 'init', array( $this, 'load_settings_panel' ) );

	}

	/**
	 * Load the admin settings page
	 * @since 1.1
	 * @sa https://github.com/NateWr/simple-admin-pages
	 */
	public function load_settings_panel() {

		require_once('lib/simple-admin-pages/simple-admin-pages.php');
		$sap = sap_initialize_library(
			array(
				'version'		=> '1.1', // Version of the library
				'lib_url'		=> GRFWP_PLUGIN_URL . '/lib/simple-admin-pages/', // URL path to sap library
			)
		);
		$sap->add_page(
			'submenu', 				// Admin menu which this page should be added to
			array(					// Array of key/value pairs matching the AdminPage class constructor variables
				'parent_menu'	=> 'edit.php?post_type=grfwp-review',
				'id'			=> 'grfwp-review-settings',
				'title'			=> __( 'Settings for Good Reviews', GRFWP_TEXTDOMAIN ),
				'menu_title'	=> __( 'Settings', GRFWP_TEXTDOMAIN ),
				'description'	=> '',
				'capability'	=> 'manage_options'
			)
		);
		$sap->add_section(
			'grfwp-review-settings',	// Page to add this section to
			array(								// Array of key/value pairs matching the AdminPageSection class constructor variables
				'id'			=> 'grfwp-general-settings',
				'title'			=> __( 'Setup', GRFWP_TEXTDOMAIN )
			)
		);
		
		/* @todo get a list of schema.org types and make a select dropdown
		$sap->add_setting(
			'grfwp-review-settings',
			'grfwp-general-settings',
			'select',
			array(
				'id'			=> 'grfwp-schema-type',
				'title'			=> __( 'What is being reviewed?', GRFWP_TEXTDOMAIN ),
				'description'	=> __( 'Select the type of object that best matches what is being reviewed. Select the most descriptive object you can, but don\'t worry if you need to use a generic term. These options match the Schema.org classification system to help search engines understand the reviews.', GRFWP_TEXTDOMAIN ),
				'blank_option'	=> false,
				'options'		=> array(
					'option-1'	=> 'Option 1',
					'option-2'	=> 'Option 2'
				)
			)
		);
		*/

		// Create filter so addons can modify the settings page or add new pages
		$sap = apply_filters( 'grfwp_settings_page', $sap );

		// Register all admin pages and settings with WordPress
		$sap->add_admin_menus();
	}

}