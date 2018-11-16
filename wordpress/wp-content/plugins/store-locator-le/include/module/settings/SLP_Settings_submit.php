<?php
defined( 'ABSPATH' ) || exit;

/**
 * The submit setting.
 */
class SLP_Settings_submit extends SLP_Setting {
	public $onClick;

	/**
	 * The submit HTML.
	 *
	 * @param string $data
	 * @param string $attributes
	 *
	 * @return string
	 */
	protected function get_content( $data, $attributes ) {
		if ( ! empty ( $this->onClick ) ) {
			$onclick = " onclick='{$this->onClick}' ";
		} else {
			$onclick = '';
		}
		return
			"<input  name='{$this->name}' class='button-primary' type='submit' " .
			     "id='{$this->id}' value='{$this->display_value}' {$data} {$onclick}>";
	}
}
