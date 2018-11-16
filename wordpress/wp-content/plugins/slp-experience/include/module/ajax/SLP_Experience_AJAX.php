<?php
defined( 'ABSPATH' ) || exit;
require_once( SLPLUS_PLUGINDIR .'include/base_class.ajax.php');


/**
 * Holds the ajax-only code.
 *
 * This allows the main plugin to only include this file in AJAX mode
 * via the slp_init when DOING_AJAX is true.
 *
 */
class SLP_Experience_AJAX extends SLP_BaseClass_AJAX {

    // Extend our valid actions
    //
    function initialize() {
    	$this->addon = $this->slplus->addon( 'experience' );
        $this->valid_actions[] = 'email_form';
	    $this->valid_actions[] = 'get_cities';
	    $this->valid_actions[] = 'slp_list_location_zips';
        parent::initialize();
    }

    /**
     * Add custom ER sort to Order By clause.
     */
    function add_custom_sort_to_orderby() {
        $our_order =
            ( ! empty( $this->slplus->options['orderby'] ) ) ?
                $this->slplus->options['orderby'] :
                $this->addon->options['orderby'];

        $our_order = $this->check_custom_fields( $our_order );

        switch ( $our_order ) {
            case 'random':
                $this->slplus->database->extend_order_array( 'sl_distance ASC' );
                break;

            default:
                $this->slplus->database->extend_order_array(
                    $this->create_string_CustomSQLOrder(
                        $our_order
                    )
                );
                break;
        }
    }

    /**
     * Check that ER extended data fields exist, if they do not then remove them from the order by clause.
     *
     * Also check if any extended data records exist.
     *
     * @param 	string $order_clause
     * @return 	string
     */
    private function check_custom_fields( $order_clause ) {
        $fields_to_check = array( 'featured' , 'rank' );
        foreach ( $fields_to_check as $field ) {
            if ( strpos( $order_clause , $field ) !== false ) {
                if ( ! $this->slplus->database->extension->has_field( $field ) ||
                    ! $this->slplus->database->has_extended_data()
                ) {
                    $order_clause = preg_replace( "/{$field}\s+\w+\W+/" , '' , $order_clause );
                }
            }
        }
        return $order_clause;
    }

    /**
     * Replace the featured location sort order with the more advanced case statement.
     *
     * By default any pre-existing / non-edited locations will set the featured field to NULL
     * Featured locations are set to 1
     * Non-featured locations are set to 0
     *
     * MySQL sorts features in DESC order to be: 1, 0, NULL
     *
     * That messes up order by featured DESC when you have order by featured desc, rank asc, sl_distance
     * as any location that was marked featured then unmarked will come BEFORE all unedited (non-featured)
     * locations that may be closer.   This is not what the user expects.
     *
     * As such we must weight NULL to be the same as not featured.
     *
     * @param $our_order
     *
     * @return mixed
     */
    private function create_string_CustomSQLOrder( $our_order ) {

        // Fix featured when it is null or 0
        //
        $find_this = 'featured DESC';
        $replace_with = 'COALESCE(featured,0) DESC';
        $our_order = str_replace( $find_this , $replace_with , $our_order );

        // Fix rank when it is null or 0
        // 0 should NOT be the first ranked
        //
        $find_this = 'rank ASC';
        $replace_with = 'CASE WHEN rank IS NULL THEN 99999 WHEN rank = 0 THEN 99999 WHEN rank > 0 THEN rank END ASC';
        $our_order = str_replace( $find_this , $replace_with , $our_order );


        return $our_order;
    }

    /**
     * Add our specific AJAX filters.
     */
    public function add_ajax_hooks() {
        if ( ! $this->is_valid_ajax_action() ) { return; }

        $this->add_action_handlers();

        add_filter( 'slp_ajaxsql_fullquery'                 , array( $this , 'remove_SQL_limit'                 ) , 120     ); // WIDGET

        add_filter( 'slp_ajaxsql_results'                   , array( $this , 'filter_AJAX_ModifyResults'        ) , 50      );

        add_filter( 'slp_ajaxsql_where'                     , array( $this , 'filter_JSONP_SearchFilters'       ) , 20      );
        add_filter( 'slp_ajaxsql_where'                     , array( $this , 'filter_JSONP_SearchFilters_W'     ) , 120     );  // WIDGET

        add_filter( 'slp_location_filters_for_AJAX'         , array( $this , 'filter_JSONP_SearchByStore'       )           );

        add_filter( 'slp_location_having_filters_for_AJAX'  , array( $this , 'modify_having_clause'             ) , 55      );

        add_action( 'slp_orderby_default'                   , array( $this , 'add_custom_sort_to_orderby'		) , 15 	    );

        $this->add_global_hooks();
    }

    /**
     * Global hooks, exposed possibly even outside AJAX.
     */
    public function add_global_hooks() {
        add_filter(	'slp_results_marker_data'               , array( $this , 'modify_marker'                    ) , 15 , 1  );
    }

    /**
     * Add Action Handlers
     */
    private function add_action_handlers() {

        // email_form
        add_action( 'wp_ajax_email_form'			, array( $this , 'process_popup_email_form' )   );
        add_action( 'wp_ajax_nopriv_email_form'     , array( $this , 'process_popup_email_form'	)   );

        // get_cities
        //
        add_action( 'wp_ajax_get_cities'			, array( $this , 'get_cities'               )   );
        add_action( 'wp_ajax_nopriv_get_cities'     , array( $this , 'get_cities'               )   );

        // slp_list_location_zips
        //
        add_action('wp_ajax_slp_list_location_zips' , array( $this , 'list_location_zips'       )   );
    }

    /**
     * Add having clause to sql which do query work by ajaxhandler
     *
     * @param   array   $having_clauses
     *
     * @return  array
     */
    public function modify_having_clause($having_clauses) {
        $this->set_QueryParams();
        $distance_removed = false;

        // Only On Search : Ignore Radius Remove Distance Clause
        //
        if ( $_REQUEST['action'] === 'csl_ajax_search' ) {
            $this->init_OptionsViaAJAX();
            if ( $this->radius_behavior_ignores_radius() ) {
                $having_clauses = array_filter($having_clauses, array( $this->slplus->AJAX, 'remove_distance_clauses'));
                $distance_removed = true;
            }
        }

        // Widget Search With Discrete State
        //
        if ( ! $distance_removed && $this->is_discrete_widget_search( 'state' ) ) {
            $having_clauses = array_filter($having_clauses, array($this->slplus->AJAX, 'remove_distance_clauses'));
        }

        // Always Show Featured Locations
        //
        if (
            ! empty( $having_clauses ) &&
	        $this->slplus->database->has_extended_data() &&
	        ( $this->addon->options['featured_location_display_type'] === 'show_always' )
        ){
	        array_push( $having_clauses, ' OR (featured = 1) ');
        }

        return $having_clauses;
    }

    /**
     * Randomize the results order if random order is selected for search results output.
     *
     * @param mixed[] $results the named array location results from an AJAX search
     * @return mixed[]
     */
    public function filter_AJAX_ModifyResults($results) {
        if ( $this->addon->options['orderby'] === 'random' ) {
            shuffle($results);
        }
        return $results;
    }

    /**
     * Add the store name condition to the MySQL statement used to fetch locations with JSONP.
     *
     * @used-by \SLP_AJAX::execute_location_query via Filter slp_location_filters_for_AJAX
     *
     * @param string $currentFilters
     * @return string the modified where clause
     */
    public function filter_JSONP_SearchByStore( $currentFilters ) {
        if (empty($_REQUEST['name'])) { return $currentFilters; }

        $posted_name = preg_replace('/^\s+(.*?)/','$1',$_POST['name']);
        $posted_name = preg_replace('/(.*?)\s+$/','$1',$posted_name);

        $currentFilters[] = " AND (sl_store LIKE '%%{$posted_name}%%')";

        return $currentFilters;
    }

    /**
     * Add the selected filters to the search results.
     *
     * @param $where
     * @return string
     */
    public function filter_JSONP_SearchFilters($where) {
        if ( !isset( $this->slplus->AJAX ) ) { return $where; }

        $this->set_QueryParams();

        $ajax_options       = $this->addon->options;
        $discrete_settings  = array('hidden', 'discrete' , 'dropdown_discretefilter' , 'dropdown_discretefilteraddress' );

        // Discrete City Output
        //
        if (
            ! empty( $this->formdata['addressInputCity'] )                   &&
            in_array( $ajax_options['city_selector'] , $discrete_settings )
        ){
            $sql_city_expression =
                (preg_match('/, /',$this->slplus->AJAX->formdata['addressInputCity']) === 1) ?
                    'CONCAT_WS(", ",sl_city,sl_state)=%s'   :
                    'sl_city=%s'                            ;

            $where =
                $this->slplus->database->extend_Where(
                    $where,
                    $this->slplus->db->prepare(
                        $sql_city_expression,
                        sanitize_text_field($this->slplus->AJAX->formdata['addressInputCity'])
                    )
                );
        }

        // Discrete State Output
        //
        if (
            ! empty( $this->formdata['addressInputState'] )                   &&
            in_array( $ajax_options['state_selector'] , $discrete_settings )
        ){
            $where = $this->slplus->database->extend_WhereFieldMatches( $where , 'trim(sl_state)' , $this->formdata['addressInputState']);
        }

        // Discrete Country Output
        //
        if (
            ! empty( $this->formdata['addressInputCountry'] )                   &&
            in_array( $ajax_options['country_selector'] , $discrete_settings )
        ) {
            $where = $this->slplus->database->extend_WhereFieldMatches( $where , 'trim(sl_country)' , $this->formdata['addressInputCountry']);
        }

        return $where;
    }

    /**
     * Add the selected filters to the search results.
     *
     * @param $where
     * @return string
     */
    public function filter_JSONP_SearchFilters_W($where) {
        if ( ! isset( $this->slplus->AJAX )    ) { return $where; }

        $this->set_QueryParams();

        if ( ! $this->addon->widget->is_initial_widget_search( $this->formdata ) ) { return $where; }
        if ( ! isset( $this->formdata['slp_widget']          )      ) { return $where; }

        // Discrete State Output
        //
        if ( ! empty( $this->formdata['slp_widget']['state'] ) ) {
            $where = $this->slplus->database->extend_WhereFieldMatches( $where , 'sl_state' , $this->formdata['slp_widget']['state']);
        }

        // Discrete City Output
        //
        if ( ! empty( $this->formdata['slp_widget']['city'] ) ) {
            $where = $this->slplus->database->extend_WhereFieldMatches( $where , 'sl_city' , $this->formdata['slp_widget']['city']);
        }


        return $where;
    }

    /**
     * Return a list of cities in JSONP
     */
    public function get_cities() {

        add_filter( 'slp_extend_get_SQL' , array( $this->addon , 'select_cities_in_state')	 );
        $this->set_autocomplete_vars();
        $sql_parameters = array( $this->formdata['filter_match'] );
        $cities = $this->slplus->database->get_Record( 'select_cities_in_state', $sql_parameters, 0, ARRAY_A , 'get_col' );
        $data = array( 'cities' => $cities );
        $this->send_JSON_response( $data );
    }

    /**
     * Get the email address from the location's email field, fetch by id.
     *
     * @param $id
     * @return string
     */
    private function get_email_by_slid( $id ) {
        if ( ! $this->slplus->currentLocation->isvalid_ID( $id ) ) { return ''; }
        $this->slplus->currentLocation->set_PropertiesViaDB( $id );
        return $this->slplus->currentLocation->email;
    }

    /**
     * Set options based on the AJAX formdata properties.
     *
     * This will allow AJAX entries to take precedence over local options.
     * Typically these are passed via slp.js by using hidden fields with the name attribute.
     * The name must match the options available to this add-on pack for jQuery to pass them along.
     */
    private function init_OptionsViaAJAX() {
        $this->set_QueryParams();
        if ( !empty( $this->formdata ) ) {
            array_walk($this->formdata , array($this,'set_ValidOptions') );
        }
    }

    /**
     * Check the nonce is valid and that the widget field is set for unlimited results within a region (state, country, etc.)
     *
     * @param string[] $field_list
     * @return bool
     */
    private function is_discrete_widget_search( $field_list ) {
        if ( ! $this->addon->widget->is_initial_widget_search( $this->formdata ) ) { return false; }
        $field_list =  is_array( $field_list) ? $field_list :  array( $field_list );
        foreach ( $field_list as $field ) {
            if ( isset( $this->formdata['slp_widget'][$field] ) && ! empty( $this->formdata['slp_widget'][$field] ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * List the zip codes for the locations in the system for autocomplete.
     *
     * @return string
     */
    public function list_location_zips() {
        require_once( SLP_EXPERIENCE_REL_DIR . 'include/module/data/SLP_Experience_Data.php' );
        $this->set_autocomplete_vars();
        $sql_parameters = array( $this->formdata['address'] );
        $zips = $this->slplus->database->get_Record( 'select_location_zips', $sql_parameters, 0, ARRAY_A , 'get_col' );
        die( json_encode( $zips ) );
    }

    /**
     * Modify the marker data.
     *
     * @param mixed[] $marker the current marker data
     * @return mixed[]
     */
    public function modify_marker($marker) {


        if ( $this->slplus->SmartOptions->add_tel_to_phone->is_true ) {
            $marker['dial'] = ! empty( $this->slplus->SmartOptions->phone_extension_delimiter ) ? str_replace( $this->slplus->SmartOptions->phone_extension_delimiter , ',,' , $marker['phone'] ) : $marker[ 'phone '];
            $marker['phone'] = sprintf('<a href="tel:%s">%s</a>',$marker['dial'],$marker['phone']);
        }

        if (($this->addon->options['show_country'] == 0)) {
            $marker['country'] = '';
        }

        // Email Link
        //
        if ( ! empty( $marker['email'] ) ) {
            switch ( $this->addon->options['email_link_format'] ) {

                // Default for SLP: the email label linked to the mailto: address
                case 'label_link':
                    $marker['email_link'] =
                        sprintf(
                            '<a href="mailto:%s" target="_blank" class="storelocatorlink"><nobr>%s</nobr></a>',
                            $marker['email'],
                            $this->slplus->WPML->get_text('label_email')
                        );
                    break;

                // The email itself linked to the mailto: address (pre SLP 4.2.26 default)
                case 'email_link':
                    $marker['email_link'] =
                        sprintf(
                            '<a href="mailto:%s" target="_blank" class="storelocatorlink"><nobr>%s</nobr></a>',
                            $marker['email'],
                            $marker['email']
                        );
                    break;

                // An email popup form.
                case 'popup_form':
                    $marker['email_link'] =
                        sprintf(
                            '<a href="#" target="_blank" class="storelocatorlink" ' .
                            "alt='Email {$marker['email']}' title='Email {$marker['email']}' " .
                            'onClick="return SLPEXP.email_form.show_form('.$marker['id'].');">' .
                            '<nobr>%s</nobr>' .
                            '</a>',
                            $this->slplus->options['label_email']
                        );
                    break;
            }
        }

        // Add Extended Data Fields
        //
        if ( $this->slplus->database->is_Extended() && $this->slplus->database->has_extended_data() ) {
            $exData = $this->slplus->currentLocation->exdata;
            $marker['exdata'] = $exData;
            foreach ($exData as $slug => $value) {

                // Special featured setting (v. just returning "on")
                if (($slug === 'featured') && $this->slplus->is_CheckTrue($value)) {
                    $value = 'featured';
                }

                $marker[$slug] = $value;
            }
            if ( ! isset($marker['featured']) ) { $marker['featured'] = ''; }
            if ( ! isset($marker['rank'    ]) ) { $marker['rank'    ] = ''; }
        }

        if ( isset( $marker['exdata'] ) && isset( $marker['exdata']['marker'] ) ) {
            $icon = $marker['exdata']['marker'];
        } elseif ( isset( $marker['icon'] ) ) {
            $icon = $marker['icon'];
        } else {
            $icon = '';
        }

        return
            array_merge(
                $marker,
                array(
                    'icon' => $icon
                )
            );
    }

    /**
     * Process a popup email form.
     */
    public function process_popup_email_form() {
        $this->set_QueryParams();

        if ( ! wp_verify_nonce( $this->formdata['email_nonce'] , 'email_form' ) ) {
            die( 'Cheatin huh?' );
        }

        // Find the to email from the store id
        //
        $to_email = $this->get_email_by_slid( $this->formdata[ 'sl_id' ] );

        if ( empty( $to_email ) ) {
            die( 'Cheatin huh?' );
        }

        $message_headers =
            "From: \"{$this->formdata[ 'email_from' ]}\" <{$this->formdata[ 'email_from' ]}>\n" .
            "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"\n";

        wp_mail(
            $to_email ,
            $this->formdata[ 'email_subject' ] ,
            $this->formdata[ 'email_message' ] ,
            $message_headers
        );

        die( __('email sent to ' . $to_email , 'slp-experience') );
    }

    /**
     * Does the current radius behavior mean we should ignore the radius?
     *
     * @return bool
     */
    private function radius_behavior_ignores_radius() {
        if ( $this->slplus->options_nojs['radius_behavior'] == "always_ignore"                                                       ) return true;
        if ( $this->slplus->options_nojs['radius_behavior'] == "ignore_with_blank_addr"  && empty( $this->formdata['addressInput'] ) ) return true;
        return false;
    }

    /**
     * Strip out the SQL Limit
     *
     * Do this for the search and state queries.
     *
     * @param $query
     * @return string
     */
    public function remove_SQL_limit( $query ) {
        $this->set_QueryParams();
        if ( $this->is_discrete_widget_search( array( 'state' , 'search' ) ) ) {
            $count = 0;
            $query = str_replace( 'LIMIT %d' , '' , $query , $count );
            if ( $count > 0 ) { add_filter( 'slp_ajaxsql_queryparams' , array( $this , 'remove_SQL_limit_param' ) , 9 ); }
        }

        return $query;
    }

    /**
     * Remove the limit parameter from the SQL params list.
     *
     *
     * @param $query_params
     * @return mixed
     */
    public function remove_SQL_limit_param( $query_params ) {
        array_pop( $query_params );
        return $query_params;
    }


    /**
     * For autocomplete entries just jam the $_REQUEST variables into the formdata property.
     */
    private function set_autocomplete_vars() {
        $this->formdata = wp_parse_args( $_REQUEST , $this->formdata_defaults );
    }

    /**
     * Set valid options from the incoming REQUEST
     *
     * @param mixed $val - the value of a form var
     * @param string $key - the key for that form var
     */
    public function set_ValidOptions($val,$key) {
        $simpleKey = str_replace(SLPLUS_PREFIX.'-','',$key);
        if (array_key_exists($simpleKey, $this->addon->options)) {
            $this->addon->options[$simpleKey] = stripslashes_deep($val);
        }
    }
}
