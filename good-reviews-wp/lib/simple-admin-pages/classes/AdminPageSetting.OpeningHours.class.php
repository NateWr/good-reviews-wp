<?php

/**
 * Register, display and save a series of fields to specify the opening hours
 * of a business/company.
 *
 * This setting accepts the following arguments in its constructor function.
 *
 * $args = array(
 *		'id'			=> 'setting_id', 	// Unique id
 *		'title'			=> 'My Setting', 	// Title or label for the setting
 *		'description'	=> 'Description', 	// Help text description
 *		'weekday_names'	=> array(			// Optional array of custom 
 *			'monday'		=> 'Monday',	//	weekday names. These can be
 *			'tuesday'		=> 'Tuesday',   //	passed in any order to
 *			'wednesday'		=> 'Wednesday',	//	set a new start of the week.
 *			'thursday'		=> 'Thursday',
 *			'friday'		=> 'Friday',
 *			'saturday'		=> 'Saturday',
 *			'sunday'		=> 'Sunday'
 *		);
 * );
 *
 * @since 1.0
 * @package Simple Admin Pages
 */

class sapAdminPageSettingOpeningHours_1_1 extends sapAdminPageSetting_1_1 {

	public $sanitize_callback = 'sanitize_text_field';

	// Array of days of the week
	public $weekdays = array(
		'monday'		=> 'Monday',
		'tuesday'		=> 'Tuesday',
		'wednesday'		=> 'Wednesday',
		'thursday'		=> 'Thursday',
		'friday'		=> 'Friday',
		'saturday'		=> 'Saturday',
		'sunday'		=> 'Sunday'
	);

	/**
	 * Parse the arguments passed in the construction and assign them to
	 * internal variables.
	 * @since 1.0
	 */
	private function parse_args( $args ) {
		foreach ( $args as $key => $val ) {
			switch ( $key ) {

				case 'id' :
					$this->{$key} = esc_attr( $val );

				case 'weekdays' :
				
					$this->weekdays = $val;

					// Allow weekday names to be overwritten for easy translation
//					if ( is_array( $val ) && count( $val ) ) {
//						foreach ( $val as $id => $name ) {
//							if ( isset( $this->weekdays[$id] ) ) {
//								$this->weekdays[$id] = $name;
//							}
//						}
//					}

				default :
					$this->{$key} = $val;

			}
		}
	}

	/**
	 * Escape the value to display it in text fields
	 * @since 1.0
	 */
	public function esc_value( $values ) {

		// Loop over the values and sanitize them
		for ( $i = 0; $i < 7; $i++ ) {
			if ( isset( $values[ $i ] ) && is_array( $values[ $i ] ) ) {
				$values[$i]['day'] = esc_attr($values[$i]['day']);
				$values[$i]['hours'] = esc_attr($values[$i]['hours']);
			}
		}

		return $values;

	}

	/**
	 * Get a day's display name
	 * @since 1.0
	 */
	private function get_day_name( $day ) {
		foreach ( $this->weekdays as $id => $name ) {
			if ( $day == $id ) {
				return $name;
			}
		}
	}

	/**
	 * Display this setting
	 * @since 1.0
	 * @todo integrate time picker
	 */
	public function display_setting() {

		$this->display_description();

		for ($i = 0; $i < 7; $i++) {

		?>

			<table class="sap-opening-hours">
				<tr>
					<td>
						<input type="hidden" id="sap-opening-hours-day-<?php echo $i; ?>-name" name="<?php echo $this->id; ?>[<?php echo $i; ?>][day_name]" value="<?php echo esc_attr( $this->get_day_name( $this->value[$i]['day'] ) ); ?>">
						<select name="<?php echo $this->id; ?>[<?php echo $i; ?>][day]" id="<?php echo $this->id . '-' . $i; ?>-day" class="sap-opening-hours-day" data-target="#sap-opening-hours-day-<?php echo $i; ?>-name">
							<option value=""></option>

							<?php foreach ( $this->weekdays as $id => $name ) : ?>

							<option value="<?php echo $id; ?>" data-name="<?php echo esc_attr( $name ); ?>"<?php if ( $this->value[$i]['day'] == $id ) : ?> selected<?php endif; ?>>
								<?php echo $name; ?>
							</option>

							<?php endforeach; ?>

						</select>
					</td>
					<td>
						<input name="<?php echo $this->id; ?>[<?php echo $i; ?>][hours]" type="text" id="<?php echo $this->id . '-' . $i; ?>-hours" value="<?php echo $this->value[$i]['hours']; ?>" class="regular-text sap-opening-hours-hours" />
					</td>
				</tr>
			</table>

		<?php

		}

	}

	/**
	 * Sanitize the array of text inputs for this setting
	 * @since 1.0
	 */
	public function sanitize_callback_wrapper( $values ) {

		// If no sanitization callback exists, don't register the setting.
		if ( !isset( $this->sanitize_callback ) || !trim( $this->sanitize_callback ) ) {
			return;
		}

		// If this isn't an array, just sanitize it as a string
		if (!is_array( $values ) ) {
			return call_user_func( $this->sanitize_callback, $values );
		}

		// Loop over the values and sanitize them
		for ( $i = 0; $i < 7; $i++ ) {
			if ( isset( $values[ $i ] ) && is_array( $values[ $i ] ) ) {
				$values[$i]['day'] = call_user_func( $this->sanitize_callback, $values[$i]['day'] );
				$values[$i]['hours'] = call_user_func( $this->sanitize_callback, $values[$i]['hours'] );
			}
		}

		return $values;
	}

}
