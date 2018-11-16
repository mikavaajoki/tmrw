<?php
defined( 'ABSPATH' ) || die();

/**
 * Class SLP_Service
 *
 * @property-read   string  $saas_url   URL for SaaS services
 * @property-read   string  $url        URL for main locator services
 */
class SLP_Service extends SLPlus_BaseClass_Object {
	private  $saas_url = 'https://dashboard.storelocatorplus.com/';
	private  $url = 'https://www.storelocatorplus.com/';

	/**
	 * Start the config.
	 */
	public function initialize() {
		if ( ! empty ( $_SERVER['SERVER_NAME'] ) && ( substr( $_SERVER['SERVER_NAME'] , -5 ) === '.test' ) ) {
			$this->saas_url = 'http://dashboard.test';
			$this->url = 'http://slp.test/';
		}
	}

	/**
	 * Fetch a URL, check the JSON is valid, and decode it if so.
	 *
	 * @param   string          $url
	 *
	 * @return WP_Error|array   Return an error or a decoded JSON array
	 */
	private function get_and_validate_json_response( $url ) {
		$json = wp_remote_get( $url );

		// Wrong...
		if ( is_wp_error( $json ) ) {
			return $json;
		}
		if ( ! is_array( $json ) ) {
			return new WP_Error( 'response_not_array' );
		}
		if ( empty( $json[ 'body'] ) ) {
			return new WP_Error( 'response_body_empty' );
		}

		// We did not get a 200 response
		$server_code = wp_remote_retrieve_response_code( $json );
		if ( $server_code !== 200 ) {
			return new WP_Error( 'not_200' , '' , $server_code );
		}

		// So Far, So Good...
		$json_response = json_decode( $json['body'] );
		if ( empty( $json_response ) ) {
			return new WP_Error( 'json_empty_inside' );
		}

		return $json_response;
	}

	/**
	 * Get the styles from the SLP server.
	 *
	 * @param string $style_selector
	 * @param string $request_params
	 *
	 * @return array|WP_Error
	 */
	public function get_styles( $style_selector , $request_params ) {
		return $this->get_and_validate_json_response( $this->url . SLP_Style_Manager::REST_ENDPOINT . $style_selector . $request_params );
	}

	/**
	 * Validate a SaaS account.
	 *
	 * <saas_url>/wp-json/myslp/v2/accounts/validate/<login>/<key>
	 *
	 * @param string $login
	 * @param string $key
	 *
	 * @return array|WP_Error
	 */
	public function validate_account( $login , $key ) {
		return $this->get_and_validate_json_response( $this->saas_url . SLP_Service_SaaS::REST_ENDPOINT . 'accounts/validate/' . $login . '/' . $key );
	}
}