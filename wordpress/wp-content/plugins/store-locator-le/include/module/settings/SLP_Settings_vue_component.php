<?php
defined( 'ABSPATH' ) || exit;

/**
 * The input setting.
 */
class SLP_Settings_vue_component extends SLP_Setting {
	public $component;
	public $wrapper = false;

	/**
	 * The input HTML.
	 *
	 * @param string $data
	 * @param string $attributes
	 *
	 * @return string
	 */
	protected function get_content( $data, $attributes ) {
		$vue = SLP_Template_Vue::get_instance();
		$content = $vue->get_content( $this->component);
		return $content;
	}
}
