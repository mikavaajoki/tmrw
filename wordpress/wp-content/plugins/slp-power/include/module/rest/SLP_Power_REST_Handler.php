<?php
defined( 'ABSPATH' ) || exit;
class SLP_Power_REST_Handler extends SLPlus_BaseClass_Object {

    /**
     * Get us going.
     */
    public function initialize() {
        $this->setup_endpoints();
    }

    /**
     * Setup endpoints.
     */
    private function setup_endpoints() {

        /**
         * Get list of running imports.
         *
         * @route   wp-json/store-locator-plus/v2/imports/
         * @method  WP_REST_Server::READABLE (GET)
         *
         * @returns WP_Error | WP_REST_Response
         */
        register_rest_route(
            SLP_REST_SLUG . '/v2',
            '/imports/',
            array(
                'methods'  => WP_REST_Server::READABLE,
                'callback' => array( $this , 'get_imports' ),
            ));

	    /**
	     * Get list of running geocodes.
	     *
	     * @route   wp-json/store-locator-plus/v2/geocoding/
	     * @method  WP_REST_Server::READABLE (GET)
	     *
	     * @returns WP_Error | WP_REST_Response
	     */
	    register_rest_route(
		    SLP_REST_SLUG . '/v2',
		    '/geocoding/',
		    array(
			    'methods'  => WP_REST_Server::READABLE,
			    'callback' => array( $this , 'get_geocoding' ),
		    ));

    }

	/**
	 * Return a list of active location imports by attachment ID.
	 *
	 * @used-by \SLP_Power_REST_Handler::setup_endpoints    via READABLE REST Route store-locator-plus/location_imports/
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_geocoding( WP_REST_Request $request  ) {
		/* @var SLP_Power_Locations_Geocode $obj */
		$obj = SLP_Power_Locations_Geocode::get_instance();
		$response = new WP_REST_Response(  $obj->get_active_list()  );
		$response->set_status( 201 );
		return $response;
	}


	/**
	 * Return a list of active location imports by attachment ID.
	 *
	 * @used-by \SLP_Power_REST_Handler::setup_endpoints    via READABLE REST Route store-locator-plus/location_imports/
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_imports( WP_REST_Request $request  ) {
		$obj = SLP_Power_Locations_Import::get_instance();
		$response = new WP_REST_Response(  $obj->get_active_list()  );
		$response->set_status( 201 );
		return $response;
	}
}