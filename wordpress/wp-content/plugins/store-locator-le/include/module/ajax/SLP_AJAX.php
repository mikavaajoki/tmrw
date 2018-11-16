<?php
defined( 'ABSPATH' ) || exit;
require_once( SLPLUS_PLUGINDIR . 'include/base_class.ajax.php' );

/**
 * Holds the ajax-only code.
 *
 * This allows the main plugin to only include this file in AJAX mode
 * via the slp_init when DOING_AJAX is true.
 *
 * @property-read   string                    $basic_query        The basic query string before the prepare.
 * @property        array                     $formdata_defaults  Default formdata values.
 * @property-read   array                     $location_queries   The executed queries as formatted
 * @property        string                    $name               TODO: LEGACY support for 4.2 addons
 * @property        array                     $options            TODO: LEGACY support for 4.2 addons
 * @property        SLPlus                    $plugin             TODO: LEGACY support for 4.2 addons : DIR, GFI
 * @property        int                       $query_limit        The query limit.
 * @property        string[]                  $valid_actions
 *
 * @property-read   boolean                   $distance_param_removed   true if we already removed the distance param
 */
class SLP_AJAX extends SLP_BaseClass_AJAX {
    public  $valid_actions      = array(
        'csl_ajax_onload',
        'csl_ajax_search',
        'slp_clear_schedule_messages',
        'slp_delete_location',
        'slp_change_option',
    );
    public  $formdata_defaults  = array(
        'addressInput'      => '',
        'addressInputState' => '',
        'nameSearch'        => '',
    );
    public  $query_params_valid = array();
    private $location_queries = array();
    private $basic_query;
    public  $options            = array( 'installed_version' => SLPLUS_VERSION );
    public  $query_limit;
    public  $name               = 'AjaxHandler';
    public  $plugin;
    private $distance_param_removed = false;

    /**
     * Instantiate a new AJAX handler object.
     */
    function __construct() {
        add_filter( 'slp_valid_ajax_query_params', array( $this, 'set_valid_query_params' ) );
        parent::__construct();
        $this->plugin = $this->slplus;
    }

    /**
     * Add our AJAX hooks.
     *
     * @uses \SLP_AJAX::slp_delete_location for the AJAX 'slp_delete_location' action
     */
    public function add_ajax_hooks() {
        add_action( 'wp_ajax_csl_ajax_search', array( $this, 'csl_ajax_search' ) );
        add_action( 'wp_ajax_nopriv_csl_ajax_search', array( $this, 'csl_ajax_search' ) );

        add_action( 'wp_ajax_csl_ajax_onload', array( $this, 'csl_ajax_onload' ) );
        add_action( 'wp_ajax_nopriv_csl_ajax_onload', array( $this, 'csl_ajax_onload' ) );

        add_action( 'wp_ajax_slp_delete_location', array( $this, 'slp_delete_location' ) );
        add_action( 'wp_ajax_slp_unhide_column', array( $this, 'slp_unhide_column' ) );

        add_action( 'wp_ajax_slp_change_option', array( $this, 'slp_change_option' ) );
    }

    /**
     * Add sort by distance ASC as default order.
     */
    function add_distance_sort_to_orderby() {
        $this->slplus->database->extend_order_array( 'sl_distance ASC' );
    }

    /**
     * Add sort by distance ASC as default order.
     */
    function add_initial_distance_sort_to_orderby() {
	    $this->slplus->database->extend_order_array( 'sl_initial_distance ASC' );
    }

    /**
     * Handle AJAX request for OnLoad action.
     *
     */
    function csl_ajax_onload() {
        $this->find_locations( 'load' );
    }

    /**
     * Handle AJAX request for Search calls.
     */
    function csl_ajax_search() {
        $this->find_locations( 'search' );
    }

    /**
     * Run a database query to fetch the locations the user asked for.
     *
     *
     * @param string    $query_slug         the slug for this query
     * @param string[]  $query_statements   an array of query statement slugs to run as the main query
     *
     * @return object a MySQL result object
     */
    private function execute_location_query( $query_slug , $query_statements ) {
        do_action( 'slp_ajax_execute_location_query_start' , $query_slug );

        // Distance Unit (KM or MI) Modifier
        // Since miles is default, if kilometers is selected, divide by 1.609344 in order to convert the kilometer value selection back in miles
        //
        $multiplier = ( $this->slplus->SmartOptions->distance_unit->value == 'km' ) ? SLPlus::earth_radius_km : SLPlus::earth_radius_mi;

        /**
         * FILTER: slp_location_filters_for_AJAX
         * Add all the location filters together for SQL statement.
         */
        $filterClause = '';
        foreach ( apply_filters( 'slp_location_filters_for_AJAX', array(), $query_slug ) as $filter ) {
            $filterClause .= $filter;
        }

        // ORDER BY
        //
        if ( $query_slug === 'standard_location_load' ) {
	        add_action( 'slp_orderby_default', array( $this, 'add_initial_distance_sort_to_orderby' ), 100 );
        }   else {
	        add_action( 'slp_orderby_default', array( $this, 'add_distance_sort_to_orderby' ), 100 );
        }

        /**
         * FILTER: slp_location_having_filters_for_AJAX
         * append new having clause logic to the array and return the new array to extend/modify the having clause.
         *
         * Do filter after sl_distance has been calculated, HAVING must be used for calculated SQL fields.
         * WHERE only works for built-in columns in the table(s).
         */
        $having_pieces = ( ( $query_slug === 'standard_location_load' ) || empty( $this->query_params['radius'] ) ) ? array() : array( '(sl_distance < %f) ', 'OR (sl_distance IS NULL) ' );
        $havingClauseElements = apply_filters( 'slp_location_having_filters_for_AJAX', $having_pieces, $query_slug );

        // If there are element for the having clause set it
        // otherwise leave it as a blank string
        //
        $having_clause = '';
        if ( count( $havingClauseElements ) > 0 ) {
            foreach ( $havingClauseElements as $filter ) {
                $having_clause .= $filter;
            }
            $having_clause = trim( $having_clause );
            $having_clause = preg_replace( '/^OR /', '', $having_clause );

            if ( ! empty( $having_clause ) ) {
                $having_clause = 'HAVING ' . $having_clause;
            }

        }

        // WHERE clauses
        //
        add_filter( 'slp_ajaxsql_where', array( $this, 'filter_out_private_locations' ) );

        $slp_standard_query = $this->slplus->database->get_SQL( $query_statements );
        $slp_standard_query .= " {$filterClause} ";
        $slp_standard_query .= " {$having_clause} ";
        $slp_standard_query .= $this->slplus->database->get_SQL( 'orderby_default' );
        $slp_standard_query .= 'LIMIT %d';

        /**
         * FILTER: slp_ajaxsql_fullquery
         *
         * @param   string  $slp_standard_query     The full SQL query
         * @param   string  $query_slug             The slug for the running query
         *
         * @return  string                          Modified SQL query
         */
        $this->basic_query = apply_filters( 'slp_ajaxsql_fullquery', $slp_standard_query, $query_slug );

        // Set the query parameters
        //
        $this->distance_param_removed = true;
        $default_query_parameters   = array();
        if ( $query_slug !== 'standard_location_load' ) {
	        $default_query_parameters[] = $multiplier;
	        $default_query_parameters[] = $this->query_params['lat'];
	        $default_query_parameters[] = $this->query_params['lng'];
	        $default_query_parameters[] = $this->query_params['lat'];
	        if ( ! empty( $having_clause ) ) {
		        $default_query_parameters[] = $this->query_params['radius'];
                $this->distance_param_removed = false;
	        }
        } else {
            if ( in_array( 'where_initial_distance' , $query_statements ) ) {
		        $default_query_parameters[] = $this->query_params['radius'];
                $this->distance_param_removed = false;
	        }
        }

        $default_query_parameters[] = $this->query_limit;

        /**
         * FILTER: slp_ajaxsql_queryparams
         */
        $query_params = apply_filters( 'slp_ajaxsql_queryparams', $default_query_parameters, $query_slug );

        // Run the query
        //
        // First convert our placeholder basic_query into a string with the vars inserted.
        // Then turn off errors so they don't munge our JSONP.
        //
	    /**
	     * @var WPDB $wpdb
	     */
        global $wpdb;
        $query = $wpdb->prepare( $this->basic_query, $query_params );
        $wpdb->hide_errors();
        $result = $wpdb->get_results( $query, ARRAY_A );

        // Problems?  Oh crap.  Die.
        //
        if ( $result === null ) {
            wp_die( json_encode( array(
                'success'        => false,
                'response'       => 'Invalid query: ' . $wpdb->last_error,
                'message'        => $this->slplus->options_nojs['invalid_query_message'],
                'basic_query'    => $this->basic_query,
                'default_params' => $default_query_parameters,
                'query_params'   => $query_params,
                'query_slug'     => $query_slug,
                'query'          => $wpdb->last_query,
            ) ) );
        }

        /**
         * FILTER: slp_ajaxsql_results
         *
         * @param   array   $result     the search results
         * @param   string  $query_slug the slug for the query that generated the results
         *
         * @return  array               modified results
         */
        $filtered_results = apply_filters( 'slp_ajaxsql_results', $result, $query_slug );

        $this->location_queries[ $query_slug ] = array(
            'query_slugs' => $query_statements,
            'query'     => $wpdb->last_query,
            'params'    => $query_params,
            'locations' => array( 'pre-filter' => count( $result ) , 'filtered' => count( $filtered_results ) ),
        );

        do_action( 'slp_ajax_execute_location_query_end' , $query_slug );

        return $filtered_results;
    }

    /**
     * Do not return private locations by default.
     *
     * @param    string $where the current where clause
     *
     * @return    string            the extended where clause
     */
    function filter_out_private_locations( $where ) {
        return $this->slplus->database->extend_Where( $where, ' ( NOT sl_private OR sl_private IS NULL) ' );
    }

    /**
     * Find locations
     *
     * @param  string  $mode       which mode are we in, usually 'search' or 'load'
     */
    private function find_locations( $mode = 'search' ) {
        $this->slplus->notifications->enabled = false;
        $response          = array();

        if ( $mode === 'search' ) {
            $this->query_limit = $this->slplus->options_nojs['max_results_returned'];
        } else {
            $this->query_limit = $this->slplus->SmartOptions->initial_results_returned->value;
        }


        // -79.85208180000001
        // -79.8520818

        /**
         * Search Mode or Center Does Not Match Default
         */
        $query_lat = sprintf( '%0.7f' , $this->query_params['lat'] );
        $query_lng = sprintf( '%0.7f' , $this->query_params['lng'] );
        $center_lat = sprintf( '%0.7f' , $this->slplus->SmartOptions->map_center_lat->initial_value );
        $center_lng = sprintf( '%0.7f' , $this->slplus->SmartOptions->map_center_lng->initial_value );
        if ( ( $mode === 'search' ) || ( $query_lat !== $center_lat ) || ( $query_lng !== $center_lng ) ) {
            $queries = array(
	            'standard_location_search' => array( 'selectall_with_distance', 'where_default_validlatlong' )
            );
        } else {
            $queries = array(
	            'standard_location_load' => array( 'selectall_initial_distance', 'where_default_validlatlong' )
            );
	        if ( $this->query_params['radius'] > 0 ) {
	            $queries['standard_location_load'][] = 'where_initial_distance';
	        }
        }

        /**
         * FILTER: slp_ajax_location_queries
         *
         * Extend the list of queries executed by a SLP location load or search from the UI.
         * Used to run multiple queries to extend the locations returned when the base where and select modifications will not suffice.
         */
        $queries = apply_filters( 'slp_ajax_location_queries' , $queries );

        foreach ( $queries as $query_slug => $query_statements ) {
            $locations = $this->execute_location_query( $query_slug , $query_statements );
            foreach ( $locations as $row ) {
                $thisLocation = $this->slp_add_marker( $row );
                if ( !empty( $thisLocation ) ) {
                    $response[] = $thisLocation;
                }
            }
        }

        // TODO: remove all this when POWER is updated to use slp_ajax_find_locations_complete action
        if ( $mode === 'search' ) {
            $location_ids = array();
            if ( ! empty( $response ) ) {
                if ( function_exists( 'array_column' ) ) {
                    $location_ids = array_column( $response, 'id' );
                } else {
                    foreach ( $response as $location ) {
                        $location_ids[] = $response[ 'id' ];
                    }
                }
            }
            do_action( 'slp_report_query_result', $this->query_params, $location_ids );
        }

        $results = array(
            'count'        => count( $response ),
            'type'         => $mode,
            'http_query'   => $this->query_params,
            'response'     => $response,
            );

        /**
         * Filter to add HTML to the top of the add/edit location form.
         *
         * @filter     slp_ajax_find_locations_complete
         *
         * @params     array        The JSONP response array.
         * @return     string[]     A modified JSONP response array.
         */

        $results = apply_filters( 'slp_ajax_find_locations_complete' ,  $results );

        $this->renderJSON_Response( $results );
    }

    /**
     * Return true if the AJAX action is one we process.
     */
    function is_valid_ajax_action() {
        if ( empty( $this->slplus->clean[ 'action' ] ) ) {
            return false;
        }

        foreach ( $this->valid_actions as $valid_ajax_action ) {
            if ( $this->slplus->clean[ 'action' ] === $valid_ajax_action ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Clear the import messages transient.
     *
     * Runs via do_ajax_startup() in the base class.
     */
    public function process_slp_clear_schedule_messages() {
	    $messages = SLP_Message_Manager::get_instance( array( 'slug' => 'schedule' ) );
	    $messages->clear_messages();
	    die( 'ok' );
    }

    /**
     * Process the location manager requests.
     *
     * @used-by \SLP_BaseClass_AJAX::do_ajax_startup        default for AJAX action 'location_manager'
     */
    public function process_location_manager() {
	    SLP_AJAX_Location_Manager::get_instance();
    }

    /**
     * Remove any having clauses with sl_distance in them from the DB Query.
     *
     * @param string $array_value
     *
     * @return bool when used with array_filter this will keep an array element if the return is .t. , remove it if .f.
     */
    public function remove_distance_clauses( $array_value ) {
        $no_distance_clause =( stripos( $array_value , 'sl_distance' ) === false );

        // Removed a param-based clause
        //
        if ( ( ! $no_distance_clause ) && strpos( $array_value , '%' ) ) {
            add_filter( 'slp_ajaxsql_queryparams', array( $this, 'remove_SQL_distance_param' ), 8 );            }

        return $no_distance_clause;
    }

    /**
     * Remove the distance parameter from the SQL params list.
     *
     * Distance param is assumed to be the second-to-last param.
     *
     * @param $query_params
     * @return mixed
     */
    public function remove_SQL_distance_param( $query_params ) {
        if ( ! $this->distance_param_removed ) {
            $limit_param = array_pop($query_params);
            array_pop($query_params);
            $query_params[] = $limit_param;
            $this->distance_param_removed = true;
        }
        return $query_params;
    }

    /**
     * Output a JSON response based on the incoming data and die.
     *
     * Used for AJAX processing in WordPress where a remote listener expects JSON data.
     *
     * @param mixed[] $data named array of keys and values to turn into JSON data
     *
     * @return null dies on execution
     */
    function renderJSON_Response( $data ) {

        // What do you mean we didn't get an array?
        //
        if ( ! is_array( $data ) ) {
            $data = array(
                'success' => false,
                'count'   => 0,
                'message' => __( 'renderJSON_Response did not get an array()', 'store-locator-le' ),
            );
        }

        // Add our SLP Version and DB Query to the output
        //
        $data = array_merge(
            array(
                'success'     => true,
                'slp_version' => SLPLUS_VERSION,
                'data_queries'=> $this->location_queries,
            ),
            $data
        );
        $data = apply_filters( 'slp_ajax_response', $data );

        // Tell them what is coming...
        //
        header( "Content-Type: application/json" );

        // Go forth and spew data
        //
        echo json_encode( $data );

        // Then die.
        //
        wp_die();
    }

    /**
     * Set the valid AJAX params based on the incoming action.
     *
     * @param $valid_params
     *
     * @return array
     */
    public function set_valid_query_params( $valid_params ) {

        switch ( $this->slplus->clean[ 'action' ] ) {
            case 'slp_delete_location':
                $valid_params[] = 'location_id';
	            break;

            case 'csl_ajax_onload':
            case 'csl_ajax_search':
                $valid_params[] = 'address';
                $valid_params[] = 'lat';
                $valid_params[] = 'lng';
                $valid_params[] = 'radius';
                $valid_params[] = 'tags';
                break;
        }

        return $valid_params;
    }

    /**
     * Change a single option via immediate AJAX mode.
     */
    function slp_change_option() {
        $match_this = '/^(.*?)\[(.*?)\]/';
        preg_match( $match_this, $this->formdata['option_name'], $matches );
        $plugin_slug = $matches[1];

        // User Meta
        if ( $plugin_slug === 'user_meta' ) {
	        $match_this = '/' . $plugin_slug . '\[(?P<option_slug>.*?)\]' . '/';
	        preg_match( $match_this, $this->formdata['option_name'], $matches );
	        if ( isset( $matches['option_slug'] ) ) {
	            $user_id = get_current_user_id();
	            if ( $user_id <= 0 ) return;
	            update_user_meta( $user_id , $matches['option_slug'] , $this->formdata['option_value'] );
	        }

	    // SLP
        } elseif ( ( $plugin_slug === 'options' ) || ( $plugin_slug === 'options_nojs' ) ) {
            $match_this = '/' . $plugin_slug . '\[(?P<option_slug>.*?)\]' . '/';
            preg_match( $match_this, $this->formdata['option_name'], $matches );
            if ( isset( $matches['option_slug'] ) ) {
                if ( $plugin_slug === 'options' ) {
                    $this->slplus->set_ValidOptions( $this->formdata['option_value'], $matches[1] );
                    $this->slplus->WPOption_Manager->update_wp_option( 'default', $this->slplus->options );
                } else {
                    $this->slplus->set_ValidOptionsNoJS( $this->formdata['option_value'], $matches['option_slug'] );
                    $this->slplus->WPOption_Manager->update_wp_option( 'nojs', $this->slplus->options_nojs );
                }
            }

        // ADD ON
        } else {
            if ( ! $this->slplus->AddOns->get( $plugin_slug , 'active') ) {
                $response = array(
                    'action'       => 'slp_change_option',
                    'option_name'  => $this->formdata['option_name'],
                    'option_value' => $this->formdata['option_value'],
                    'id'           => new WP_Error( 'slp_invalid_option_name', $this->slplus->Text->get_text_string( array( 'label' , 'slp_missing_location_data' ) ), array( 'status' => 404 ) ),
                );
                wp_die( json_encode ( $response  ) );
            }
            $plugin     = $this->slplus->AddOns->instances[ $plugin_slug ];
            $match_this = '/' . $plugin->option_name . '\[(?P<option_slug>.*?)\]' . '/';
            preg_match( $match_this, $this->formdata['option_name'], $matches );
            if ( isset( $matches[1] ) ) {
                $plugin->set_ValidOptions( $this->formdata['option_value'], $matches['option_slug'] );
                $plugin->WPOption_Manager->update_wp_option( 'default', $plugin->options );
            }
        }

        $response = array (
            'status' => 'ok' ,
            'option' => $matches['option_slug'] ,
            'value' => $this->formdata['option_value']
        );

        wp_die( json_encode ( $response ) );
    }

    /**
     * Delete a single location.
     */
    public function slp_delete_location() {
        $response = array( 'status' => 'failed' );
	    if ( ! empty( $_POST[ 'screenoptionnonce' ] ) && wp_verify_nonce( $_POST[ 'screenoptionnonce' ] ,'screen-options-nonce'  ) ) {
		    SLP_AJAX_Location_Manager::get_instance()->delete_location();
		    $response[ 'status' ] = 'ok';

	    }
	    echo json_encode( $response );
	    wp_die();
    }

    /**
     * Format the result data into a named array.
     *
     * We will later use this to build our JSONP response.
     *
     * @param null mixed[] $row
     *
     * @return mixed[]
     */
    public function slp_add_marker( $row = null ) {
        if ( $row == null ) {
            return '';
        }

        $this->slplus->currentLocation->set_PropertiesViaArray( $row );

        /** @var  SLP_Location_Utilities $location_utils */
        $location_utils = SLP_Location_Utilities::get_instance();

        $marker = array(
            'name'          => esc_attr( $row['sl_store'] ),
            'address'       => esc_attr( $row['sl_address'] ),
            'address2'      => esc_attr( $row['sl_address2'] ),
            'city'          => esc_attr( $row['sl_city'] ),
            'state'         => esc_attr( $row['sl_state'] ),
            'zip'           => esc_attr( $row['sl_zip'] ),
            'country'       => esc_attr( $row['sl_country'] ),
            'lat'           => $row['sl_latitude'],
            'lng'           => $row['sl_longitude'],
            'description'   => html_entity_decode( $row['sl_description'] ),
            'url'           => esc_url( $row['sl_url'] ),
            'sl_pages_url'  => esc_url( $row['sl_pages_url'] ),
            'email'         => esc_attr( $row['sl_email'] ),
            'email_link'    => $location_utils->create_email_link( $row[ 'sl_email' ] ),
            'hours'         => esc_attr( $row['sl_hours'] ),
            'phone'         => esc_attr( $row['sl_phone'] ),
            'fax'           => esc_attr( $row['sl_fax'] ),
            'image'         => esc_attr( $row['sl_image'] ),
            'distance'      => isset( $row['sl_distance'] ) ? $row['sl_distance'] : '',
            'tags'          => esc_attr( $row['sl_tags'] ),
            'option_value'  => esc_js( $row['sl_option_value'] ),
            'attributes'    => maybe_unserialize( $row['sl_option_value'] ),
            'id'            => $row['sl_id'],
            'linked_postid' => $row['sl_linked_postid'],
            'neat_title'    => esc_attr( $row['sl_neat_title'] ),
            'data'          => $row,
	        'city_state_zip' => $location_utils->create_city_state_zip(),
        );

        // Need to come after $marker[url] is set above.
	    $marker[ 'web_link' ]  = ( empty( $marker['url'] ) ) ? '' : sprintf( "<a href='%s' target='_blank' class='storelocatorlink'>%s</a><br/>", $marker['url'], $this->slplus->Text->get_text( 'label_website' ) );
	    $marker[ 'url_link' ]  = ( empty( $marker['url'] ) ) ? '' : sprintf( "<a href='%s' target='_blank' class='storelocatorlink'>%s</a><br/>", $marker['url'], $marker['url'] );


        // FILTER: slp_results_marker_data
        // Modify the map marker object that is sent back to the UI in the JSONP response.
        //
        $marker = apply_filters( 'slp_results_marker_data', $marker );

        return $marker;
    }
}