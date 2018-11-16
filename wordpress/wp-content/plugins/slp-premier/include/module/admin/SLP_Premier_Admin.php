<?php
defined( 'ABSPATH' ) || exit;
require_once(SLPLUS_PLUGINDIR.'/include/base_class.admin.php');

/**
 * Holds the admin-only code.
 *
 * This allows the main plugin to only include this file in admin mode
 * via the admin_menu call.   Reduces the front-end footprint.
 *
 * @property            SLP_Premier                      $addon
 * @property-read		SLP_Premier_Admin_Experience	    $Admin_Experience
 * @property-read       SLP_Premier_Admin_General        $Admin_General
 * @property-read       SLP_Premier_Admin_Info           $Admin_Info
 * @property-read		SLP_Premier_Admin_Locations		$Admin_Locations
 * @property			array							$settings_pages		List of the checkboxes that live on various admin pages.
 *
 */
class SLP_Premier_Admin extends SLP_BaseClass_Admin {
    public  $addon;
    public  $settings_pages = array(
        'slp_experience' => array(
            'dropdown_autosubmit'       ,
            'region_influence_enabled'  ,
            'show_address_guess'        ,
        ),
        'slp_general'    => array(
            'show_location_on_order_email',
        ),
        'slp_manage_locations'  => array (
        )
    );

	protected $class_prefix = 'SLP_Premier_';
	protected $objects = array(
		'Admin_Experience' => array( 'subdir' => 'include/module/admin/' ),
		'Admin_General'    => array( 'subdir' => 'include/module/admin/' ),
		'Admin_Info'       => array( 'subdir' => 'include/module/admin/' ),
		'Admin_Locations'  => array( 'subdir' => 'include/module/admin/' ),
	);

	/**
	 * Admin specific hooks and filters.
	 */
	function add_hooks_and_filters() {
		parent::add_hooks_and_filters();

        // Load objects based on which admin page we are on.
        //
        if ( isset( $_REQUEST['page'] ) ) {
            switch ( $_REQUEST['page'] ) {
                case 'slp_experience':
                    $this->instantiate( 'Admin_Experience' );
	                $this->addon->create_object_category();
                    break;
                case 'slp_general':
                    $this->instantiate( 'Admin_General' );
                    break;
	            case 'slp_info':
		            $this->instantiate( 'Admin_Info' );
		            break;
                case 'slp_manage_locations':
                    $this->instantiate( 'Admin_Locations');
                    break;
            }
        }


        // Locations Import
        //
        add_action( 'slp_prepare_location_import'       , array( $this , 'prepare_woo_import' ) );

        // Locations Manage
        add_filter( 'slp_column_data'                             , array( $this , 'show_woo_products_on_manage_locations'  ), 20, 3 );
        add_filter( 'slp_column_data'                             , array( $this , 'checkmark_territory_bounds'  ), 20, 3 );

		// Update locations-based bounds if mode is set to locations.
		//
		if ( $this->slplus->SmartOptions->boundaries_influence_type->value === 'locations' ) {
			add_action( 'slp_location_added', array( $this, 'update_minmax_latlng' ) );
		}
		$this->js_settings = $this->addon->options;
	}

    /**
     * Replace JSON output with a simple checkmark.
     *
     * NOTE: setting display type to checkbox would do this as well.
     *
     * @param $data
     * @param $slug
     * @param $label
     *
     * @return string
     */
    function checkmark_territory_bounds( $data, $slug , $label ) {
        if ( $slug === 'territory_bounds' ) {
            $data = empty( $data ) ? '' : '<span class="dashicons dashicons-yes"></span>';
        }

        return $data;
    }

	/**
	 * Enqueue our CSS
	 * @param $hook
	 */
	function enqueue_admin_css( $hook ) {
		wp_enqueue_style('dashicons');
		parent::enqueue_admin_css( $hook );
	}

    /**
	 * Add the JS settings for admin.
	 */
	function enqueue_admin_javascript( $hook ) {
		$this->js_pages = array( SLP_ADMIN_PAGEPRE . 'slp_experience' , SLP_ADMIN_PAGEPRE . 'slp_manage_locations' );

		if ( ! parent::ok_to_enqueue_admin_js( $hook ) ) { return ; }

		$this->js_settings['bounds']               = $this->addon->set_location_search_boundaries();
		$this->js_settings['bounds_center_label' ] = __( 'Bounds Center', 'slp-premier' );
		$this->js_settings['bounds_center_marker'] = $this->addon->url . '/images/markers/plus_white_22x22.png';
        $this->js_settings['map_center_lat'      ] = $this->slplus->SmartOptions->map_center_lat->value;
        $this->js_settings['map_center_lng'      ] = $this->slplus->SmartOptions->map_center_lng->value;

		parent::enqueue_admin_javascript( $hook );

	}

    /**
     * Prepare for a WooCommerce Import
     */
    function prepare_woo_import() {
        $this->instantiate( 'Admin_Locations' );
    }

	/**
	 * Add UX checkboxes to the save ux settings, then run save ux tab settings.
	 */
	function save_my_settings() {
        if ( ! parent::save_my_settings() ) { return; }
        switch ( $_REQUEST['page'] ) {
            case 'slp_general':
                $this->instantiate( 'Admin_General' );
               break;
        }
	}

	/**
	 * Our default object options.
	 */
	protected function set_default_object_options() {
		$this->objects[ 'Admin_Experience' ][ 'options' ] = array( 'addon' => $this->addon );
		$this->objects[ 'Admin_General'    ][ 'options' ] = array( 'addon' => $this->addon , 'admin' => $this );
		$this->objects[ 'Admin_Info'       ][ 'options' ] = array( 'addon' => $this->addon );
		$this->objects[ 'Admin_Locations'  ][ 'options' ] = array( 'addon' => $this->addon );
	}

    /**
     * Show the Woo product prices on the manage locations table.
     *
     * @param    string $value_to_display the value of this field
     * @param    string $field_name the name of the field from the database
     * @param    string $column_label the column label for this column
     *
     * @return    string
     */
    function show_woo_products_on_manage_locations( $value_to_display, $field_name, $column_label ) {
        if ( ! $this->addon->is_woo_running() ) { return $value_to_display; }
        if ( empty( $value_to_display) ) { return ''; }
        $this->instantiate( 'Admin_Locations' );
        return $this->addon->WooCommerce_Glue->show_woo_products_on_manage_locations( $value_to_display , $field_name , $column_label );
    }

	/**
	 * Update the min/max latitude and longitude.
	 */
	function update_minmax_latlng() {
		$new_minmax = false;

		// Min Lat
		if ( floatval( $this->slplus->currentLocation->latitude ) < floatval( $this->addon->options['boundaries_influence_min_lat'] ) ) {
			$this->addon->options['boundaries_influence_min_lat'] = $this->slplus->currentLocation->latitude;
			$new_minmax = true;
		}
		// Min Lng
		if ( floatval( $this->slplus->currentLocation->longitude ) < floatval( $this->addon->options['boundaries_influence_min_lng'] ) ) {
			$this->addon->options['boundaries_influence_min_lng'] = $this->slplus->currentLocation->longitude;
			$new_minmax = true;
		}
		// Max Lat
		if ( floatval( $this->slplus->currentLocation->latitude ) > floatval( $this->addon->options['boundaries_influence_max_lat'] ) ) {
			$this->addon->options['boundaries_influence_max_lat'] = $this->slplus->currentLocation->latitude;
			$new_minmax = true;
		}
		// Max Lng
		if ( floatval( $this->slplus->currentLocation->longitude ) > floatval( $this->addon->options['boundaries_influence_max_lng'] ) ) {
			$this->addon->options['boundaries_influence_max_lng'] = $this->slplus->currentLocation->longitude;
			$new_minmax = true;
		}

		// New min/max - write to options table.
		//
		if ( $new_minmax ) {
			update_option( $this->addon->option_name , $this->addon->options );
		}

	}

}