<?php
defined( 'ABSPATH' ) || exit;

/**
 * The dropdown setting.
 */
class SLP_Settings_dropdown extends SLP_Setting {
	public $empty_ok = false;
	public $get_items_callback;
	public $selectedVal = '';
	public $uses_slplus = true;

	/**
	 * The dropdown HTML.
	 *
	 * @param string $data
	 * @param string $attributes
	 *
	 * @return string
	 */
	protected function get_content( $data, $attributes ) {
		if ( ! empty( $this->get_items_callback ) ) {
			$this->custom = call_user_func( $this->get_items_callback );
		}

		return
			$this->slplus->Helper->createstring_DropDownMenu(
				array(
					'id'          => $this->name,
					'name'        => $this->name,
					'items'       => $this->custom,
					'onchange'    => $this->onChange,
					'disabled'    => $this->disabled,
					'selectedVal' => $this->selectedVal,
					'empty_ok'    => $this->empty_ok,
				)
			);
	}

}
