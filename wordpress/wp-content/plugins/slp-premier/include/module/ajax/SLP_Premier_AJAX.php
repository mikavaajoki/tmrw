<?php
defined( 'ABSPATH' ) || exit;
require_once(SLPLUS_PLUGINDIR.'/include/base_class.ajax.php');


/**
 * Holds the ajax-only code.
 *
 * This allows the main plugin to only include this file in AJAX mode
 * via the slp_init when DOING_AJAX is true.
 *
 * @property        SLP_Premier                      $addon
 * @property-read   SLP_Premier_Territory            $territory
 *
 */
class SLP_Premier_AJAX extends SLP_BaseClass_AJAX {
    private $territory;
    public $valid_actions = array(
        'csl_ajax_onload',
        'csl_ajax_search',
        'slp_new_upload'
    );

	/**
	 * Initialize
	 */
    final function initialize() {
	    $this->addon = $this->slplus->AddOns->instances['slp-premier'];
	    parent::initialize();
    }

	/**
     * Things we do to latch onto an AJAX processing environment.
     *
     * Add WordPress and SLP hooks and filters only if in AJAX mode.
     *
     * WP syntax reminder: add_filter( <filter_name> , <function> , <priority> , # of params )
     *
     * Remember: <function> can be a simple function name as a string
     *  - or - array( <object> , 'method_name_as_string' ) for a class method
     * In either case the <function> or <class method> needs to be declared public.
     *
     * @link http://codex.wordpress.org/Function_Reference/add_filter
     *
     */
    public function add_ajax_hooks() {

        // Only incur this overhead if pagination is enabled.
        //
        if ( $this->slplus->SmartOptions->pagination_enabled->is_true ) {
	        $pagination = SLP_Premier_AJAX_Pagination::get_instance();
	        add_filter('slp_ajax_response'      , array( $pagination , 'modify_ajax_response'     ) );
            add_filter('slp_ajaxsql_fullquery'  , array( $pagination , 'modify_sql_for_pagination') );
        }

        // Territories Enabled
        //
        if ( $this->slplus->SmartOptions->use_territory_bounds->is_true ) {

            switch ( $this->slplus->options_nojs['radius_behavior'] ) {

                case 'in_radius_and_in_territory':
                    add_filter( 'slp_ajaxsql_where'     , array( $this , 'where_territory_is_set'               ) );
                    add_filter( 'slp_ajaxsql_results'   , array( $this , 'drop_locations_not_in_radius_and_serving_territory' ) ,10 , 2);  // This runs here so we can skip some overhead of 'slp_results_marker_data'
                    break;

                case 'in_radius_no_terr_set_or_in_territory':
                    add_filter( 'slp_ajax_location_queries' , array( $this , 'set_location_search_queries' ) );
                    break;
            }

            add_filter( 'slp_csv_locationdata_added', array( $this , 'set_territory_bounds_on_import' ) );        // Power addon uses this method.
        }

        // URL Control
        if ( $this->addon->has_url_controls() ) require_once( SLPPREMIER_REL_DIR . 'include/module/url/SLP_Premier_URL_Control.php' );
        if ( $this->slplus->SmartOptions->allow_location_in_url->is_true ) $this->slplus->Premier_URL_Control->remove_distance_qualifier();
        if ( $this->slplus->SmartOptions->allow_limit_in_url->is_true ) $this->slplus->Premier_URL_Control->change_result_limit();

        // Block IP
        if ( ! empty( $this->slplus->SmartOptions->block_ip_period->value ) ) {
            require_once( SLPPREMIER_REL_DIR . 'include/module/security/SLP_Premier_Block_IP.php' );
        }

        add_filter( 'slp_category_name_separator'    , array( $this , 'get_category_name_separator' ) );

	    $this->add_global_hooks();
    }

	/**
	 * Global hooks, exposed possibly even outside AJAX.
	 */
	public function add_global_hooks() {
		// Only incur this if Woo is running.
		//
		if ( $this->addon->is_woo_running() ) {
			$this->addon->instantiate( 'WooCommerce_Glue' );
			add_filter('slp_results_marker_data'      , array( $this->addon->WooCommerce_Glue , 'modify_marker_data' ));
		}
		add_filter( 'slp_results_marker_data'   , array( $this , 'set_marker_in_territory_flag' ) );
	}

    /**
     * Add the address within territory query to the stack of AJAX load/search queries on locations.
     *
     * @param  array $queries  the standard query
     * @return array           the standard query PLUS the address within territory query
     */
    public function set_location_search_queries( $queries ) {
        $this->create_object_territory();
        return $this->territory->set_location_search_queries( $queries );
    }

    /**
     * Setup the Tagalong interface.
     *
     * @param $separator
     * @return mixed
     */
    public function get_category_name_separator( $separator ) {
        return $this->slplus->AddOns->instances['slp-premier']->options['category_name_separator'];
    }

    /**
     * Create and attach the location import object.
     */
    private function create_object_territory() {
        if ( ! isset( $this->territory ) ) {
            require_once( $this->addon->dir . 'include/module/territory/SLP_Premier_Territory.php' );
            $this->territory = new SLP_Premier_Territory( array( 'addon' => $this->addon ) );
        }
    }

    /**
     * Remove any locations whose territory does not cover the given lat/long.
     *
     * @param array $results    Results from a wpdb select
     * @return array $results
     */
    public function drop_locations_not_in_radius_and_serving_territory( $results , $query_slug ) {
        if ( ! $this->slplus->database->has_extended_data() ) { return array(); }
        $this->create_object_territory();
        $this->set_QueryParams();
        return $this->territory->remove_results_not_in_territory( $results );
    }

    /**
     * Add a marker property to note whether a location services the user's location.
     *
     * @param   array $marker
     * @return array $marker
     */
    public function set_marker_in_territory_flag( $marker ) {
        $this->create_object_territory();
        return $this->territory->set_marker_in_territory_property( $marker );
    }

    /**
     * Add the territory bounds for imported CSV data.
     *
     * POWER add-on uses AJAX for this.
     *
     * @param $data
     *
     * @return array
     */
    public function set_territory_bounds_on_import( $data ) {
        $this->create_object_territory();
        $this->territory->clear_points();
        $new_data = $this->territory->set_territory_bounds( $data );
        $this->slplus->database->extension->update_data(
            $this->slplus->currentLocation->id,
            array( 'territory_bounds' => $new_data['territory_bounds'] )
        );
    }

    /**
     * Modify the AJAX selector to only get locations where address is within the territory.
     *
     * @param string $where_clause
     * @return string $where_clause
     */
    public function where_address_within_territory( $where_clause ) {
        $this->create_object_territory();
        return $this->territory->sql_filter_where_address_within_territory( $where_clause );
    }

    /**
     * Modify the AJAX selector to only get locations serving this spot.
     *
     * Since this is a WHERE clause and we don't have a MySQL PointInPolygon function , only do basic filtering of locations.
     *
     * If territory bounds is empty, do not return the location.
     * If the distance unit is none do not return the location.
     *
     * @param string $where_clause
     * @return string $where_clause
     */
    public function where_territory_is_set( $where_clause ) {
        $this->create_object_territory();
        return $this->territory->sql_filter_where_territory_is_set( $where_clause );
    }
}