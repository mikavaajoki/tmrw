<?php
defined( 'ABSPATH' ) || exit;

/**
 * Handle the AJAX location_manager requests.
 */
class SLP_Power_AJAX_Location_Manager extends SLPlus_BaseClass_Object {

	/**
	 * Return a list of countries.
	 */
	public function get_country_list() {
		$sql_commands = array( 'select_distinct_country' , 'where_valid_country' , 'order_by_country' );

		$country_list = $this->slplus->database->get_Record( $sql_commands , array() , 0 , ARRAY_A , 'get_col');

		$response = array(
			'count'  => count( $country_list ),
			'states' => $country_list
		);

		wp_send_json_success( $response );
	}

    /**
     * Return a list of states.
     */
    public function get_state_list() {
	    $sql_commands = array( 'select_distinct_states' , 'where_valid_state' , 'order_by_state' );

	    $state_list = $this->slplus->database->get_Record( $sql_commands , array() , 0 , ARRAY_A , 'get_col');

	    $response = array(
		    'count'  => count( $state_list ),
	        'states' => $state_list
	    );

	    wp_send_json_success( $response );
    }


}
