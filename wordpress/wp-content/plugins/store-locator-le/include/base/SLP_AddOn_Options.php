<?php
defined( 'ABSPATH' ) || exit;
/**
 * Class SLP_AddOn_Options
 *
 * @property-read   boolean     initialized     Have our options been initialized
 * @property        string[]    our_options     The slugs for options that this add on manipulates
 */
class SLP_AddOn_Options extends SLPlus_BaseClass_Object {
	protected $addon;
	private $initialized = false;
	public $our_options = array();

	/**
	 * Go and create the smart options.
	 *
	 * @param array $these_options
	 * @param array $defaults
	 */
	protected function attach_to_slp( $these_options , $defaults = array() ) {
		$this->our_options = array_merge( $this->our_options , array_keys( $these_options ) );
		$this->slplus->SmartOptions->create_smart_options( $these_options , $defaults );
	}

	/**
	 * Things we do at the start.
	 */
	public function initialize() {
		if ( $this->initialized ) return;

		$this->create_options();

		$this->initialized = true;
	}

	/**
	 * Override to setup options.
	 */
	protected function create_options() {}

}