<?php
defined( 'ABSPATH' ) || exit;
class SLP_Location_LoadFromWP extends SLP_Base_Object  {

	/**
	 * Import Locations
	 */
	public function import() {

		// Connect to REST API
		if ( ! $this->connect_to_site() ) {
			SLP_Admin_Locations::get_instance()->add_notice( __( 'URL not valid.', 'store-locator-le' ) );
			return;
		}

		// Process Results
	}

	/**
	 * Connect to the site.
	 */
	public function connect_to_site() {
		return false;
	}

}