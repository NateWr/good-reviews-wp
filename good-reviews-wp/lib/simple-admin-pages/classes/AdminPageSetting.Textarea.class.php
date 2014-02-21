<?php

/**
 * Register, display and save a textarea field setting in the admin menu
 *
 * @since 1.0
 * @package Simple Admin Pages
 *
 * @todo textareas should have an option to swap new lines for <br>s
 */

class sapAdminPageSettingTextarea_1_1 extends sapAdminPageSetting_1_1 {

	/*
	 * Size of this textarea
	 * 
	 * This is put directly into a css class [size]-text,
	 * and setting this to 'large' will link into WordPress's existing textarea
	 * style for full-width textareas.
	 */
	private $size = 'small';

	public $sanitize_callback = 'sanitize_text_field';
	
	/**
	 * Escape the value to display it safely HTML textarea fields
	 * @since 1.0
	 */
	public function esc_value( $val ) {
		return esc_textarea( $val );
	}
	
	/**
	 * Set the size of this textarea field
	 * @since 1.0
	 */
	public function set_size( $size ) {
		$this->size = esc_attr( $size );
	}

	/**
	 * Display this setting
	 * @since 1.0
	 */
	public function display_setting() {
		?>

		<textarea name="<?php echo $this->id; ?>" id="<?php echo $this->id; ?>" class="<?php echo $this->size; ?>-text"><?php echo $this->value; ?></textarea>

		<?php
		
		$this->display_description();
		
	}

}
