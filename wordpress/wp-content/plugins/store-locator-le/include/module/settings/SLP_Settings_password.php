<?php
defined( 'ABSPATH' ) || exit;

/**
 * The input setting.
 */
class SLP_Settings_password extends SLP_Setting {

	/**
	 * The password HTML.
	 *
	 * @param string $data
	 * @param string $attributes
	 *
	 * @return string
	 */
	protected function get_content( $data, $attributes ) {
		return
			"<input type='password' id='{$this->id}'  name='{$this->name}' data-field='{$this->data_field}' value='{$this->display_value}' {$attributes}>";
	}
}