<?php
defined( 'ABSPATH' ) || exit;
if (! class_exists('SLP_BaseClass_AJAX')) {

    /**
     * A base class that helps add-on packs separate AJAX functionality.
     *
     * Add on packs should include and extend this class.
     *
     * This allows the main plugin to only include this file in AJAX mode.
     *
	 * @property		SLP_BaseClass_Addon 	$addon						This addon pack.
	 * @property 		array 					$formdata					Form data that comes into the AJAX request in the formdata variable.
	 * @property 		array 					$formdata_defaults			The formdata default values.
	 * @property-read 	bool 					$formdata_set				Has the formdata been set already?
     * @property        array                   $query_params
     * @property        string[]                $query_params_valid     	Array of valid AJAX query parameters
	 * @property-read 	string 					$short_action 				The shortened (csl_ajax prefix dropped) AJAX action.
	 * @property		string[]				$valid_actions				What AJAX actions are valid for this add on to process?
	 * 					Override in the extended class if not serving the default SLP actions:
	 * 						csl_ajax_onload
	 * 						csl_ajax_search
	 *
     */
    class SLP_BaseClass_AJAX extends SLPlus_BaseClass_Object {
        public 	    $addon;
        protected 	$formdata 				= array();
		protected 	$formdata_defaults 		= array();
		private 	$formdata_set		 	= false;
		public    	$query_params         	= array();
		protected 	$query_params_valid   	= array();
	    private 	$short_action;
	    protected 	$valid_actions = array(
		    'csl_ajax_onload',
		    'csl_ajax_search'
	    );

        /**
         * Instantiate the admin panel object.
         *
         * Sets short_action property.
         * Calls do_ajax_startup.
         * - sets Query Params (formdata)
         * - Calls process_{short_action} if method exists.
         *
         */
        function initialize() {
			if ( empty( $this->slplus->clean[ 'action' ] ) ) { return; }
	        $this->short_action = str_replace('csl_ajax_','', $this->slplus->clean[ 'action' ] );
            $this->do_ajax_startup();
        }

	    /**
	     * Override this with the WordPress AJAX hooks you want to invoke.
	     *
	     * example:
	     * 	    add_action('wp_ajax_csl_ajax_search' , array( $this,'csl_ajax_search' ));         // For logged in users
	     *      add_action('wp_ajax_nopriv_csl_ajax_search' , array( $this,'csl_ajax_search' ));  // Not logged-in users
	     */
	    function add_ajax_hooks() {

	    }

        /**
         * Things we want our add on packs to do when they start in AJAX mode.
         *
         * Add methods named process_{short_action_name} to the extended class,
         * or override this method.
         *
         * @uses \SLP_AJAX::process_location_manager
         *
         * NOTE: If you name something with process_{short_action_name} this will bypass the WordPress AJAX hooks and will run IMMEDIATELY when this class is instantiated.
         */
        function do_ajax_startup() {
            if ( ! $this->is_valid_ajax_action() ) { return; }
	        $this->set_QueryParams();
            $action_name = 'process_' . $this->short_action;
            if ( method_exists( $this , $action_name ) ) {
                $this->$action_name();
            }
			$this->add_ajax_hooks();
        }

	    /**
	     * Return true if the AJAX action is one we process.
		 *
		 * TODO: add a "source" parameter as well and set to "slp" then check that to make sure we only process SLP requests
	     */
	    function is_valid_ajax_action() {
		    if ( empty( $this->slplus->clean[ 'action' ] ) ) return false;
		    return in_array( $this->slplus->clean[ 'action' ] , $this->valid_actions );
	    }

		/**
		 * Output a JSON response based on the incoming data and die.
		 *
		 * Used for AJAX processing in WordPress where a remote listener expects JSON data.
		 *
		 * @param mixed[] $data named array of keys and values to turn into JSON data
		 */
		function send_JSON_response($data) {

			// What do you mean we didn't get an array?
			//
			if (!is_array($data)) {
				$data = array(
					'success'       => false,
					'count'         => 0,
					'message'       => __('renderJSON_Response did not get an array()','store-locator-le')
				);
			}

			// Add our SLP Version and DB Query to the output
			//
			$data = array_merge(
				array(
					'success'       => true,
				),
				$data
			);

			// Tell them what is coming...
			//
			header( "Content-Type: application/json" );

			// Go forth and spew data
			//
			echo json_encode($data);

			// Then die.
			//
			wp_die();
		}

        /**
         * Set incoming query and request parameters into object properties.
         */
        function set_QueryParams() {
            if ( ! $this->formdata_set ) {
                if ( isset( $_REQUEST['formdata'] ) ) {
                    $this->formdata = apply_filters( 'slp_modify_ajax_formdata' , wp_parse_args($_REQUEST['formdata'], $this->formdata_defaults) );
                }
                $this->formdata_set = true;
            }

	        // Incoming Query Params
	        //
	        $this->query_params_valid = apply_filters( 'slp_valid_ajax_query_params' , $this->query_params_valid );
	        $this->query_params['QUERY_STRING'] = isset( $_SERVER['QUERY_STRING'] ) ? $_SERVER['QUERY_STRING'] : '' ;
	        foreach ( $this->query_params_valid as $key ) {
		        $this->query_params[$key] = isset( $_POST[$key] ) ? $_POST[$key] : '';
	        }

	        // Incoming options - set them in SLPLUS for options or options_nojs.
	        //
	        if ( isset( $_REQUEST['options'] ) && is_array( $_REQUEST['options'] ) ) {
		        if ( isset( $this->addon ) ) {
			        array_walk( $_REQUEST['options'], array( $this->addon, 'set_ValidOptions' ) );
		        }
		        array_walk( $_REQUEST['options'] , array($this->slplus, 'set_ValidOptions'      ) );
		        array_walk( $_REQUEST['options'] , array($this->slplus, 'set_ValidOptionsNoJS'  ) );
	        }
        }

    }
}