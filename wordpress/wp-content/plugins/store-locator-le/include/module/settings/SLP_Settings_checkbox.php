<?php
defined( 'ABSPATH' ) || exit;
/**
 * The checkbox setting.
 */
class SLP_Settings_checkbox extends SLP_Setting {
	public $uses_slplus = true;

	/**
	 * The checkbox HTML.
	 *
	 * @param string $data
	 * @param string $attributes
	 *
	 * @return string
	 */
	protected function get_content( $data, $attributes ) {
		$checked = $this->slplus->is_CheckTrue( $this->display_value ) ? 'checked' : '';
		return
			"<input type='checkbox' id='{$this->id}'  name='{$this->name}' {$data} value='1' {$checked} {$attributes}/>";
	}
}