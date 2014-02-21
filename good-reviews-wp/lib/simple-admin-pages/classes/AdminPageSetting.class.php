<?php

/**
 * Register, display and save a setting on a custom admin menu
 *
 * All settings accept the following arguments in their constructor functions.
 *
 * $args = array(
 *		'id'			=> 'setting_id', 	// Unique id
 *		'title'			=> 'My Setting', 	// Title or label for the setting
 *		'description'	=> 'Description' 	// Help text description
 * );
 *
 * @since 1.0
 * @package Simple Admin Pages
 */

abstract class sapAdminPageSetting_1_1 {

	// Page defaults
	public $id; // used in form fields and database to track and store setting
	public $title; // setting label
	public $description; // optional description of the setting
	public $value; // value of the setting, if a value exists
	
	// Array to store errors
	public $errors = array();

	/*
	 * Function to use when sanitizing the data
	 *
	 * We set this to null in this class to prevent it from being used to
	 * register a setting. $this->register_setting() will not be successful if
	 * no $sanitize_callback value is found.
	 *
	 * @since 1.0
	 */
	public $sanitize_callback = null; // sanitize the incoming data

	/**
	 * Initialize the setting
	 *
	 * By default, every setting takes an id, title and description in the $args
	 * array.
	 *
	 * @since 1.0
	 */
	public function __construct( $args ) {

		// Parse the values passed
		$this->parse_args( $args );
		
		// Get any existing value
		$this->value = $this->esc_value( get_option( $this->id ) );

		// Set an error if the object is missing necessary data
		if ( $this->missing_data() ) {
			$this->set_error();
		}
	}

	/**
	 * Parse the arguments passed in the construction and assign them to
	 * internal variables. This function will be overwritten for most subclasses
	 * @since 1.0
	 */
	private function parse_args( $args ) {
		foreach ( $args as $key => $val ) {
			switch ( $key ) {

				case 'id' :
					$this->{$key} = esc_attr( $val );
				
				default :
					$this->{$key} = $val;
					
			}
		}
	}

	/**
	 * Check for missing data when setup.
	 * @since 1.0
	 */
	private function missing_data(  ) {
	
		$error_type = 'missing_data';
		
		// Required fields
		if ( empty( $this->id ) ) {
			$this->set_error(
				array( 
					'type'		=> $error_type,
					'data'		=> 'id'
				)
			);
		}
		if ( empty( $this->title ) ) {
			$this->set_error(
				array(
					'type'		=> $error_type,
					'data'		=> 'title'
				)
			);
		}
	}

	/**
	 * Escape the value to display it in text fields and other input fields
	 *
	 * We use esc_attr() here so that the default is quite strict, but other
	 * setting types should override this function with the appropriate escape
	 * function. See: http://codex.wordpress.org/Data_Validation
	 *
	 * @since 1.0
	 */
	public function esc_value( $val ) {
		return esc_attr( $val );
	}

	/**
	 * Wrapper for the sanitization callback function.
	 *
	 * This just reduces code duplication for child classes that need a custom
	 * callback function.
	 * @since 1.0
	 */
	public function sanitize_callback_wrapper( $value ) {
		return call_user_func( $this->sanitize_callback, $value );
	}

	/**
	 * Display this setting
	 * @since 1.0
	 */
	abstract public function display_setting();

	/**
	 * Display a description for this setting
	 * @since 1.0
	 */
	public function display_description() {

		if ( trim( $this->description ) != '' ) {

		?>

			<p class="description"><?php echo $this->description; ?></p>

		<?php

		}
	}

	/**
	 * Add and register this setting
	 * @since 1.0
	 */
	public function add_register_setting( $page_slug, $section_id ) {

		// If no sanitization callback exists, don't register the setting.
		if ( !isset( $this->sanitize_callback ) || !trim( $this->sanitize_callback ) ) {
			return;
		}

		add_settings_field( $this->id, $this->title, array( $this, 'display_setting' ), $page_slug, $section_id );
		register_setting( $page_slug, $this->id, array( $this, 'sanitize_callback_wrapper' ) );

	}
	
	/**
	 * Set an error
	 * @since 1.0
	 */
	public function set_error( $error ) {
		$this->errors[] = array_merge(
			$error,
			array(
				'class'		=> get_class( $this ),
				'id'		=> $this->id,
				'backtrace'	=> debug_backtrace()
			)
		);
	}
}
