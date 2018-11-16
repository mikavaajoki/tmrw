<?php
defined( 'ABSPATH' ) || exit;

/**
 * The file setting.
 */
class SLP_Settings_file extends SLP_Setting {
    public $button_text;

	/**
	 * The file HTML.
	 *
	 * @param string $data
	 * @param string $attributes
	 *
	 * @return string
	 */
	protected function get_content( $data, $attributes ) {
		$button_text = ! empty( $this->button_text ) ? $this->button_text : 'Choose A File';
		return
			$button_text .
			"<input type='file' id='{$this->id}' name='{$this->name}' {$data} {$attributes}>"
			;
	}

}
