<?php
defined( 'ABSPATH' ) || exit;

/**
 * The checkbox setting.
 */
class SLP_Settings_textarea extends SLP_Setting {

	/**
	 * The checkbox HTML.
	 *
	 * @param string $data
	 * @param string $attributes
	 *
	 * @return string
	 */
	protected function get_content( $data, $attributes ) {
		return
			"<textarea  name='{$this->name}' id='{$this->id}' cols='50' rows='5' {$data} {$attributes}>".
				$this->display_value .
			"</textarea>";
	}

	/**
	 * Set the display value
	 */
	protected function set_display_value() {
		if ( isset( $this->display_value ) ) {
			return;
		}
		$this->display_value =  esc_textarea( $this->value );
	}
}
