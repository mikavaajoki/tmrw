<?php
defined( 'ABSPATH' ) || exit;

/**
 * The list setting.
 */
class SLP_Settings_list extends SLP_Setting {
	public $get_items_callback;
	public $uses_slplus = true;

	/**
	 * Get the list items.
	 */
	private function get_list_items() {
		if ( ! empty( $this->custom ) && is_array( $this->custom ) ) {
			return $this->custom;
		}
		if ( ! empty( $this->get_items_callback ) ) {
			$this->custom = call_user_func( $this->get_items_callback );
		}
	}

	/**
	 * The list HTML.
	 * 
	 * @param string $data
	 * @param string $attributes
	 *
	 * @return string
	 */
	protected function get_content( $data, $attributes ) {
		$this->get_list_items();

		$html     = "<select id='{$this->id}'  name='{$this->name}' class='csl_select' data-field='{$this->data_field}' {$this->onChange} >";

		if ( ! empty( $this->custom ) ) {
			$selectMatch = $this->value;
			foreach ( $this->custom as $key => $value ) {
				if ( $selectMatch === $value ) {
					$html .= "<option class='csl_option' value='{$value}' selected='selected'>{$key}</option>\n";
				} else {
					$html .= "<option class='csl_option' value='{$value}'>{$key}</option>\n";
				}
			}
		}

		$html .= "</select>\n";

		return $html;
	}
}