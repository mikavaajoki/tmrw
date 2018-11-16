<?php
defined( 'ABSPATH' ) || exit;

class SLP_Settings_details extends SLP_Setting {

	/**
	 * Details needs to set some defaults.
	 */
	protected function initialize() {
		$this->content = empty( $this->custom ) ?  $this->description : $this->custom;
		$this->description = null;
		$this->show_label  = false;
		$this->value       = '';
		parent::initialize();
	}

	/**
	 * Render me.
	 */
	public function display() {
		$this->wrap_in_default_html();
	}
}
