<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class SLP_Settings_subheader
 */
class SLP_Settings_subheader extends SLP_Setting {

	/**
	 * Details needs to set some defaults.
	 */
	protected function initialize() {
		$this->show_label  = false;
		parent::initialize();
	}

	/**
	 * Render me.
	 */
	public function display() {
		if ( ! empty( $this->label ) ) {
			$this->content .= sprintf ( '<h3>%s</h3>' , $this->label );
		}
		if ( ! empty( $this->description ) ) {
			$this->content .= "<p class='slp_subheader_description' id='{$this->id}_p'>{$this->description}</p>";
			$this->description = '';
		}

		$this->wrap_in_default_html();
	}
}
