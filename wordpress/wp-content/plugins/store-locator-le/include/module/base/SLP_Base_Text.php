<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class SLP_Base_Text
 */
class SLP_Base_Text extends SLPlus_BaseClass_Object {
	protected $uses_slplus = false;

	/**
	 * SLP Text Manager Hooks
	 */
	protected function initialize() {
		add_filter( 'slp_get_text_string', array( $this, 'augment_text_string' ), 10, 2 );
	}

	/**
	 * Replace the SLP Text Manager Strings at startup.
	 *
	 * @uses \SLP_Experience_Admin_Settings_Text::description
	 * @uses \SLP_Experience_Admin_Settings_Text::label
	 * @uses \SLP_Experience_Admin_Settings_Text::option_default
	 *
	 * @param string $text the original text
	 * @param string $slug the slug being requested
	 *
	 * @return string            the new SLP text manager strings
	 */
	public function augment_text_string($text, $slug) {
		if ( ! is_array( $slug ) ) {
			$slug = array( 'general' , $slug );
		}

		if ( method_exists( $this , $slug[0] ) ) {
			$text = $this->{$slug[0]}( $slug[1] , $text );
		}

		return $text;
	}
}