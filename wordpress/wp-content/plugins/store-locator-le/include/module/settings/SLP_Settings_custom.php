<?php
defined( 'ABSPATH' ) || exit;

/**
 * The custom setting.
 *
 * This is the default for any Setting type if the SLP_Setting_<type>.php file is not found.
 */
class SLP_Settings_custom extends SLP_Setting {

	/**
	 * The custom HTML.
	 *
	 * @param string $data
	 * @param string $attributes
	 *
	 * @return mixed
	 */
	protected function get_content( $data, $attributes ) {
		return $this->custom;
	}
}