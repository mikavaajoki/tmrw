<?php
defined( 'ABSPATH' ) || exit;

/**
 * WP REST API interface.
 */
class SLP_REST_Handler extends SLPlus_BaseClass_Object {

	/**
	 * Things we do at the start.
	 */
	function initialize() {
		if ( ! defined( 'REST_API_VERSION' )                    ) { return; }      // No WP REST API.  Leave.
		if ( version_compare( REST_API_VERSION , '2.0' , '<' )  ) { return; }      // Require REST API version 2.

		defined( 'SLP_REST_SLUG' ) || define( 'SLP_REST_SLUG' , 'store-locator-plus' );

		$this->set_rest_hooks();
	}

	/**
	 * Set the rest hooks.
	 */
	private function set_rest_hooks() {
		add_action( 'rest_api_init' , array( $this , 'setup_rest' ) );
	}

	/**
	 * Only if REST_REQUEST is defined.
	 */
	public function setup_rest() {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			$this->setup_rest_endpoints();
		}
	}

	/**
	 * Setup the REST endpoints for Store Locator Plus.
	 */
	private function setup_rest_endpoints() {
		$this->setup_rest_v1_endpoints();
		$this->setup_rest_v2_endpoints();
		do_action( 'slp_setup_rest_endpoints' );
	}

	/**
	 * Setup cross-version REST endpoints
	 *
	 * @param string $version
	 */
	private function setup_rest_cross_version_endpoints( $version ) {

		// V1
		if ( version_compare( $version , 'v1' , '>=' ) ) {

			/**
			 * Get a single of locations.
			 *
			 * @route   wp-json/store-locator-plus/<v1+>/locations/<id>
			 * @method  WP_REST_Server::READABLE (GET)
			 *
			 * @returns WP_Error | WP_REST_Reponse
			 */
			register_rest_route( SLP_REST_SLUG . '/' . $version, '/locations/(?P<id>\d+)', array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this , 'get_location_by_id' )
			) );
		}

		// V2
		if ( version_compare( $version , 'v2' , '>=' ) ) {

			/**
			 * Add a single location.
			 *
			 * Requires authentication.
			 *
			 * @route   wp-json/store-locator-plus/<v2+>/locations
			 * @method  WP_REST_Server::EDITABLE (POST, PUT, PATCH)
			 *
			 * @params  string  sl_store        required , name of store
			 * @params  string  <field_slug>    optional, other store data. Field slugs can match base or extended data fields.
			 *
			 * @returns WP_Error | WP_REST_Reponse
			 */
			register_rest_route( SLP_REST_SLUG . '/' . $version, '/locations/', array(
				'methods'               => WP_REST_Server::EDITABLE,
				'callback'              => array( $this , 'add_location' ) ,
				'permission_callback'   => array( $this , 'user_can_manage_slp' ) ,
				'args'                  => array(
					'sl_store'  => array( 'required'    => true ),
				)
			) );

			/**
			 * Get a list of locations.
			 *
			 * @route   wp-json/store-locator-plus/<v2+>/locations
			 * @method  WP_REST_Server::READABLE (GET)
			 *
			 * @returns WP_Error | WP_REST_Reponse
			 */
			register_rest_route( SLP_REST_SLUG . '/' . $version, '/locations/', array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_locations' )
			) );

			/**
			 * Update a single location.
			 *
			 * Requires authentication.
			 *
			 * @route   wp-json/store-locator-plus/<v2+>/locations
			 * @method  WP_REST_Server::EDITABLE (POST, PUT, PATCH)
			 *
			 * @params  string  sl_store        required , name of store
			 * @params  string  <field_slug>    optional, other store data. Field slugs can match base or extended data fields.
			 *
			 * @returns WP_Error | WP_REST_Reponse
			 */
			register_rest_route( SLP_REST_SLUG . '/' . $version, '/locations/(?P<id>\d+)', array(
				'methods'               => WP_REST_Server::EDITABLE,
				'callback'              => array( $this, 'update_location' ) ,
				'permission_callback'   => array( $this , 'user_can_manage_slp' ),
			) );

			/**
			 * Delete a single location.
			 *
			 * Requires authentication.
			 *
			 * @route   wp-json/store-locator-plus/<v2+>/locations/<id>
			 * @method  WP_REST_Server::DELETABLE (DELETE)
			 *
			 * @returns WP_Error | WP_REST_Reponse
			 */
			register_rest_route( SLP_REST_SLUG . '/' . $version, '/locations/(?P<id>\d+)', array(
				'methods'  => WP_REST_Server::DELETABLE,
				'callback' => array( $this, 'delete_location_by_id' ) ,
				'permission_callback' => array( $this , 'user_can_manage_slp' )
			) );


			/**
			 * Get the specified smart option.
			 *
			 * @route   wp-json/store-locator-plus/<v2+>/options/<slug>
			 * @method  WP_REST_Server::READABLE (GET)
			 *
			 * @returns WP_Error | WP_REST_Reponse
			 */
			register_rest_route( SLP_REST_SLUG . '/' . $version, '/options/(?P<slug>\w+)', array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_smart_option' )
			) );

		}
	}

	/**
	 * Setup the REST endpoints for Store Locator Plus.
	 */
	private function setup_rest_v1_endpoints() {
		$this->setup_rest_cross_version_endpoints( 'v1' );
	}

	/**
	 * Setup the REST endpoints for Store Locator Plus.
	 */
	private function setup_rest_v2_endpoints() {
		$this->setup_rest_cross_version_endpoints( 'v2' );
	}

	/**
	 * Return a list of locations.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	function get_locations( WP_REST_Request $request ) {
		$location_list = array();
		$offset = 0;
		do {

			$location = $this->slplus->database->get_Record(array('selectslid', 'where_default'), array(), $offset++);

			if ( is_wp_error( $location ) ) { return $location; }

			if ( ! empty ( $location['sl_id'] ) ) {
				$location_list[] = array( 'sl_id' => $location['sl_id'] );
			}
		} while ( ! empty ( $location['sl_id'] ) );


		$response = new WP_REST_Response( $location_list );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Add a location.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	function add_location( WP_REST_Request $request ) {

		// Set location data
		$location_data = $request->get_params();

		// Error During Prep
		//
		if ( empty( $location_data ) ) {
			return new WP_Error( 'slp_missing_location_data' , $this->slplus->Text->get_text_string( array( 'label' , 'slp_missing_location_data' ) ) , array( 'status' => 404 ) );
		}

		// Add Location
		//
		$result = $this->slplus->currentLocation->add_to_database( $location_data , 'add' , false );

		// Error During Add
		//
		if ( $result == 'not_updated' ) {
			return new WP_Error( 'slp_location_not_updated' , $this->slplus->Text->get_text_string( array( 'label' , 'slp_location_not_updated' ) ) , array( 'status' => 404 ) );
		}

		$response_data = array(
			'message_slug' => 'location_added' ,
			'message'      => __( 'Location added. ' , 'store-locator-le' ) ,
			'location_id'  => $this->slplus->currentLocation->id
		);
		$response = new WP_REST_Response( $response_data );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Return a single location.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	function get_location_by_id( WP_REST_Request $request ) {
		$result = $this->slplus->currentLocation->get_location( $request['id'] );

		if ( is_wp_error( $result ) ) { return $result; }

		$response = new WP_REST_Response( $this->slplus->currentLocation->locationData );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Return the current value of a smart option.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	function get_smart_option( WP_REST_Request $request ) {
		$property = $request['slug'];

		if ( property_exists( $this->slplus->SmartOptions , $property ) ) {
			$result = $this->slplus->SmartOptions->$property;

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			if ( ! is_a( $result , 'SLP_Option' ) ) {
				return new WP_Error( 'invalid_option' , __( 'Not a valid option slug.' , 'store-locator-le' ) );
			}
		} else {
			return new WP_Error( 'invalid_option' , __( 'Not a valid option slug.' , 'store-locator-le' ) );
		}

		// Blank out these things to lighten our load and prevent infinite recursion
		$return_data = json_decode( json_encode( $result ) );
		unset( $return_data->call_when_changed );
		unset( $return_data->slplus );
		$return_data->value = $result->value;
		$return_data->initial_value = $result->initial_value;


		$response = new WP_REST_Response( $return_data );
		$response->set_status( 201 );

		return $response;
	}


	/**
	 * Delete a single location
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	function delete_location_by_id( WP_REST_Request $request ) {
		$result = $this->slplus->currentLocation->delete( $request['id'] );

		if ( is_wp_error( $result ) ) { return $result; }

		$response_data = array(
			'message_slug' => 'location_deleted' ,
			'message'      => __( 'Location deleted. ' , 'store-locator-le' ) ,
			'location_id'  => $request['id']
		);
		$response = new WP_REST_Response( $response_data );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Update a location.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	function update_location( WP_REST_Request $request ) {

		// Get the location data
		//
		$result = $this->slplus->currentLocation->get_location( $request['id'] );

		if ( is_wp_error( $result ) ) { return $result; }

		// Set the incoming parameters array for the update
		//
		$location_data = $request->get_params();
		unset( $location_data['id'] );
		$location_data['sl_id'] = $this->slplus->currentLocation->id;
		foreach ( $location_data as $key=>$value ) {
			if ( is_numeric( $key ) ) { unset( $location_data[$key] ); }
		}

		// Error During Prep
		//
		if ( empty( $location_data ) ) {
			return new WP_Error( 'slp_missing_location_data' , $this->slplus->Text->get_text_string( array( 'label' , 'slp_missing_location_data' ) ) , array( 'status' => 404 ) );
		}

		// Update Location
		//
		$result = $this->slplus->currentLocation->add_to_database( $location_data , 'update' , false );

		// Error During Update
		//
		if ( $result !== 'updated' ) {
			return new WP_Error( 'slp_location_not_updated' , $this->slplus->Text->get_text_string( array( 'label' , 'slp_location_not_updated' ) ) , array( 'status' => 404 ) );
		}

		$response_data = array(
			'message_slug' => 'location_updated' ,
			'message'      => __( 'Location updated. ' , 'store-locator-le' ) ,
			'location_id'  => $this->slplus->currentLocation->id
		);
		$response = new WP_REST_Response( $response_data );
		$response->set_status( 201 );

		return $response;
	}

	/**
	 * Return true if user can manage SLP.
	 *
	 * @return bool
	 */
	function user_can_manage_slp() {
		return current_user_can( 'manage_slp_user' );
	}
}
