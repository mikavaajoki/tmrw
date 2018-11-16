<?php
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'SLP_Premier_URL_Control' ) ) {
	/**
	 * Class SLP_Premier_URL_Control
	 */
	class SLP_Premier_URL_Control extends SLPlus_BaseClass_Object {

		/**
		 * Our Startup Stuff
		 */
		protected function initialize() {
			add_filter( 'slp_js_options' , array( $this , 'handle_location_in_url' ) , 90 );
		}

        /**
         * Change the search results limit for 'standard_location_search'
         * @param array     $query_params
         * @param string    $query_slug
         * @return array                    modified array assumes param #4 = the limit
         */
		public function change_result_limit( $query_params = null , $query_slug = null ) {
		    // Setup
		    if ( is_null( $query_params ) && is_null( $query_slug ) ) {
		        if ( ! empty( $_REQUEST['options']['limit'] ) ) add_filter( 'slp_ajaxsql_queryparams' , array( $this, __FUNCTION__ ) , 99 , 2 );
		        return;
            }

            // Execution
            switch ( $query_slug ) {

                // Standard Location Search
                case 'standard_location_search':
                    if (count($query_params) !== 5)  return $query_params;
                    $query_params[4] = (int)$_REQUEST['options']['limit'];
                    break;

                case 'standard_location_load':
                    $query_params[count($query_params)-1] = (int)$_REQUEST['options']['limit'];
                    break;

            }

            return $query_params;
        }

		/**
		 * Get the location from the URL.
		 *
		 * @return int      0 if not a location, int > 0 if a valid location.
		 */
		private function get_location_from_url() {
		    if ( empty( $_REQUEST[ 'location' ] ) && ! empty( $_REQUEST[ 'sl_id' ] ) ) {
		        $_REQUEST[ 'location' ] = $_REQUEST[ 'sl_id' ];
            }
			if ( empty ( $_REQUEST[ 'location' ] ) ) {
				return 0;
			}
			if ( $this->slplus->SmartOptions->allow_location_in_url->is_false ) {
				return 0;
			}
            return (int) $_REQUEST[ 'location' ];

        }

		/**
         * UI Processing: set the active_location option
         *
		 * @param array $options    the options going to the SLP JS handler
		 *
		 * @return array
		 */
		public function handle_location_in_url( $options ) {
			$options[ 'active_location' ] = $this->get_location_from_url();
			return $options;
		}

        /**
         * AJAX Processing: Are we looking at a search form post whose params matches our active location URL?
         * @return bool
         */
		private function post_matches_url_location() {
		    if ( empty( $_POST['options']['active_location'] ) ) return false;
            if ( empty( $_POST['lat'] ) ) return false;
            if ( empty( $_POST['lng'] ) ) return false;
            if ( empty( $_POST['options']['map_center'] ) ) return false;
            if ( empty( $_POST['options']['map_center_lat'] ) ) return false;
            if ( empty( $_POST['options']['map_center_lng'] ) ) return false;


            $this->slplus->currentLocation->get_location( $_POST['options']['active_location'] );

            if ( $_POST['options']['map_center_lat'] != $this->slplus->currentLocation->latitude ) return false;
            if ( $_POST['options']['map_center_lng'] != $this->slplus->currentLocation->longitude ) return false;
            if ( $_POST['options']['map_center'] != $this->slplus->currentLocation->latitude.','.$this->slplus->currentLocation->longitude ) return false;

            return true;
        }

        /**
         * AJAX Processing: Drop the distance qualifier when using the location ID.
         *
         * @param null $having_clauses
         * @return string|void
         */
		public function remove_distance_qualifier( $having_clauses = null ) {
		    // Setup
		    if ( is_null( $having_clauses ) ) {
		        if ( $this->post_matches_url_location() ) add_filter('slp_location_having_filters_for_AJAX', array($this, __FUNCTION__));
                return;
            }

            // Execution
            $having_clauses = array_filter( $having_clauses , array( $this->slplus->AJAX , 'remove_distance_clauses' ) );

            return $having_clauses;
        }

        /**
         * UI Processing: Set the map center to the incoming location lat/lng.
         *
         * Dual purpose - sets the filter AND processes the filter.
         *
         * @param string|null   $options     default (null) - add the filter, array incoming means processing filter override of options
         * @return string|void
         */
		public function set_center_map_to_location( $options = null ) {
		    // Setup
		    if ( is_null( $options ) ) {
                $location = $this->get_location_from_url();
		        if ( ! empty( $location ) ) add_filter('slp_js_options', array($this, __FUNCTION__), 99);
		        return;
            }

            // Execution
		    $this->slplus->currentLocation->get_location( $this->get_location_from_url() );
		    $options['map_center'] = $this->slplus->currentLocation->latitude . ',' . $this->slplus->currentLocation->longitude;
            $options['map_center_lat'] = $this->slplus->currentLocation->latitude;
            $options['map_center_lng'] = $this->slplus->currentLocation->longitude;
		    return $options;
        }

        /**
         * Set a location limit via URL controls.
         *
         * Dual purpose - sets the filter AND processes the filter.
         *
         * @param string|null   $options     default (null) - add the filter, array incoming means processing filter override of options
         * @return string|void
         */
        public function set_location_limits( $options = null ) {
            // Setup
            if ( is_null( $options ) ) {
                if ( ! empty( $_REQUEST[ 'limit' ] ) ) add_filter( 'slp_js_options' , array( $this , __FUNCTION__ ) , 99 );
                return;
            }

            // Execution
            $options['limit'] = (int) $_REQUEST['limit'];
            return $options;
        }
	}

	global $slplus;
	if ( is_a( $slplus, 'SLPlus' ) ) {
		$slplus->add_object( new SLP_Premier_URL_Control() );
	}
}