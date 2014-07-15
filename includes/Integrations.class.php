<?php
/**
 * 
 */

if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'grfwpIntegrations' ) ) {
/**
 * Integrations class
 *
 * This class integrates Good Reviews for WordPress with other plugins and 
 * services.
 *
 * @since 0.0.1
 */
class grfwpIntegrations {

	public function __construct() {
	
		// Business Profile
		if ( defined( 'BPFWP_VERSION' ) ) {
			
			add_filter( 'grfwp_reviewed_values', array( $this, 'bpfwp_set_reviewed_schema' ) );

		}
	}
	
	/**
	 * Get schema.org values to use from the Business Profile settings
	 * @since 0.0.1
	 */
	public function bpfwp_set_reviewed_schema( $reviewed ) {
		
		global $bpfwp_controller;
		
		$reviewed['schema'] = $bpfwp_controller->settings->get_setting( 'schema_type' );
		$reviewed['name'] = $bpfwp_controller->settings->get_setting( 'name' );
		
		return $reviewed;
	}
	 
}
} // endif;
