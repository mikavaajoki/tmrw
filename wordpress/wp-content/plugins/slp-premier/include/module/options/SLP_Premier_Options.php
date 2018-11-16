<?php
require_once( SLPLUS_PLUGINDIR . '/include/base/SLP_AddOn_Options.php' );

/**
 * Class SLP_Premier_Options
 *
 * The options management for the premier add on
 *
 * @property        SLP_Premier  addon
 * @property-read   boolean     subscription_is_valid
 */
class SLP_Premier_Options extends SLP_AddOn_Options {
    private $subscription_is_valid;
    public $slug = 'slp-premier';


    /**
     * Create our options.
     */
    protected function create_options() {
    	$this->addon = $this->slplus->addon( 'premier' );

        $this->subscription_is_valid = $this->slplus->AddOns->is_premier_subscription_valid();

        $this->augment_settings_search_functionality();

	    $this->map_functionality();
	    $this->map_appearance();
	    $this->map_markers();

        $this->results_interaction();
	    $this->results_appearance();
	    $this->results_labels();

        $this->search_appearance_search_form_style();
        $this->search_labels();


        $this->create_general_schedule_options();
        $this->general_server_web_app_settings();
        $this->general_server_security();

        $this->general_ui_url_control();
    }

	/**
	 * Settings / Search / Functionality
	 */
	private function augment_settings_search_functionality() {
		$new_options[ 'boundaries_influence_type' ] = array(
			'use_in_javascript' => true,
			'related_to' => 'boundaries_map_wrapper' ,
			'type'      => 'dropdown' ,
			'default'   => 'none' ,
			'options'   => array(
				array( 'label' => __( 'None'            , 'slp-premier' ), 'value' => 'none'        , 'description' => __( 'a rectangle that surrounds all of your locations.'                                  , 'slp-premier' ) ),
				array( 'label' => __( 'Locations'       , 'slp-premier' ), 'value' => 'locations'   , 'description' => __( 'Defined Boundary is a rectangle that you define with a map that will appear below.' , 'slp-premier' ) ),
				array( 'label' => __( 'Defined Boundary', 'slp-premier' ), 'value' => 'boundary'    , 'description' => __( 'Use standard Google address lookup rules. (default).'                               , 'slp-premier' ) ),
			),
			'call_when_changed' => array( $this , 'set_boundary_influence_lat_lng' ),
		);
		$this->attach_to_slp( $new_options , array( 'page' => 'slp_experience' , 'section' => 'search' , 'group' => 'functionality' ) );
	}

	/**
	 * Set the boundary influence to a border around all locations.
	 *
	 * @param $key
	 * @param $old_value
	 * @param $new_value
	 */
	public function set_boundary_influence_lat_lng( $key , $old_value , $new_value ) {
		if ( $new_value === 'locations' ) {
			$this->__get('addon' );
			$this->addon->find_minmax_latlng();
		}
	}

	/**
	 * Map > Functionality
	 */
	private function map_functionality() {
		$new_options[ 'search_on_map_move' ] = array( 'type' => 'checkbox' , 'default' => '0' );
		$this->attach_to_slp( $new_options , array( 'page' => 'slp_experience' , 'section' => 'map' , 'group' => 'functionality' , 'use_in_javascript' => true, 'add_to_settings_tab' => $this->subscription_is_valid ) );
	}

    /**
     * Map > Markers
     */
    private function map_markers() {
        // Markers
	    $new_options[ 'map_marker_tooltip' ] = array( 'type' => 'checkbox' , 'default' => '1' );

        // Cluster Markers
        //
	    $related_to = 'map_appearance_cluster_header,clusters_enabled,cluster_gridsize,cluster_minimum';
	    $new_options[ 'map_appearance_cluster_header' ] = array( 'related_to' => $related_to , 'type' => 'subheader' , 'description' => '' );
        $new_options[ 'clusters_enabled' ]   = array( 'related_to' => $related_to , 'type' => 'checkbox' , 'default' => '1' );
        $new_options[ 'cluster_gridsize' ]   = array(
            'related_to'        => $related_to ,
            'type'              => 'dropdown' ,
            'default'           => '60' ,
            'options'           => array(
                array( 'label' => __( '90' , 'slp-premier' ) , 'value' => '90' , 'description' => __( 'Larger areas cluster together; zoom in more to break clusters.' , 'slp-premier' ) ) ,
                array( 'label' => __( '60 (Default)' , 'slp-premier' ) , 'value' => '60' , 'description' => __( 'The default value.' , 'slp-premier' ) ) ,
                array( 'label' => __( '30' , 'slp-premier' ) , 'value' => '30' , 'description' => __( 'Smaller areas cluster together; zoom in less to break clusters. ' , 'slp-premier' ) ) ,
            ) ,
        );
        $new_options[ 'cluster_minimum' ]    = array( 'related_to' => $related_to , 'default' => '3' );

        $this->attach_to_slp( $new_options , array( 'page' => 'slp_experience' , 'section' => 'map' , 'group' => 'markers' , 'use_in_javascript' => true, 'add_to_settings_tab' => $this->subscription_is_valid ) );
    }

    /**
     * Settings | Results | Interaction
     */
    private function results_interaction() {

    	$new_options[ 'phone_extension_delimiter' ] = array( 'related_to' => 'add_tel_to_phone' );

        $new_options[ 'results_click_animate_marker' ]       = array(
            'use_in_javascript' => true ,
            'type'              => 'dropdown' ,
            'default'           => 'none' ,
            'options'           => array(
                array( 'label' => __( 'None' , 'slp-premier' ) , 'value' => 'none' , 'description' => __( 'No animation for the marker.' , 'slp-premier' ) ) ,
                array( 'label' => __( 'Bounce' , 'slp-premier' ) , 'value' => 'BOUNCE' , 'description' => __( 'Continually bounce the marker. ' , 'slp-premier' ) ) ,
                array( 'label' => __( 'Drop' , 'slp-premier' ) , 'value' => 'DROP' , 'description' => __( 'Drop the marker from the "sky" with a single bounce.' , 'slp-premier' ) ) ,
            ) ,
        );
        $new_options[ 'results_click_label_marker' ]         = array(
            'use_in_javascript' => true ,
            'type'              => 'dropdown' ,
            'default'           => 'no_label' ,
            'options'           => array(
                array( 'label' => __( 'No Label' , 'slp-premier' ) , 'value' => 'no_label' , 'description' => __( 'Do not add a label.' , 'slp-premier' ) ) ,
                array( 'label' => __( 'Name' , 'slp-premier' ) , 'value' => 'name' , 'description' => __( 'Show the store name. ' , 'slp-premier' ) ) ,
            ) ,
        );
        $new_options[ 'results_click_map_movement' ]         = array(
            'use_in_javascript' => true ,
            'type'              => 'dropdown' ,
            'default'           => 'stationary' ,
            'options'           => array(
                array( 'label' => __( 'Stationary' , 'slp-premier' ) , 'value' => 'stationary' , 'description' => __( 'Do not move.' , 'slp-premier' ) ) ,
                array( 'label' => __( 'Center Location' , 'slp-premier' ) , 'value' => 'center' , 'description' => __( 'Center the location on the map. ' , 'slp-premier' ) ) ,
            ) ,
        );
        $new_options[ 'results_click_marker_icon_behavior' ] = array(
            'related_to'        => 'results_click_marker_icon' ,
            'use_in_javascript' => true ,
            'type'              => 'dropdown' ,
            'default'           => 'as_is' ,
            'options'           => array(
                array( 'label' => __( 'Keep As Is' , 'slp-premier' ) , 'value' => 'as_is' , 'description' => __( 'Do not change the marker.' , 'slp-premier' ) ) ,
                array( 'label' => __( 'Use Active Marker Icon' , 'slp-premier' ) , 'value' => 'use_active' , 'description' => __( 'Use the active marker icon specified here. ' , 'slp-premier' ) ) ,
            ) ,
        );
        $new_options[ 'results_click_marker_icon' ]          = array(
            'related_to'        => 'results_click_marker_icon_behavior' ,
            'use_in_javascript' => true ,
            'type'              => 'icon' ,
        );

        $this->attach_to_slp( $new_options , array( 'page' => 'slp_experience' , 'section' => 'results' , 'group' => 'results_interaction' ) );
    }

	/**
	 * Map | Appearance
	 */
	private function map_appearance() {
		$new_options[ 'map_options_clickableIcons' ] = array( 'type' => 'checkbox' , 'default' => '1' , 'related_to' => 'map_home_icon,map_end_icon' );

		$new_options[ 'bubble_footnote'            ] = array( 'type' => 'textarea' , 'allow_empty' => true , 'related_to' => 'bubblelayout,hide_bubble' );

		$related = 'map_controls_subheader,map_options_scaleControl,map_options_mapTypeControl,map_option_zoomControl,map_option_fullscreenControl,map_option_hide_streetview';
		$new_options[ 'map_controls_subheader' ] = array( 'type' => 'subheader' , 'related_to' => $related );
		$new_options[ 'map_option_zoomControl' ] = array( 'type' => 'checkbox' , 'default' => '1' , 'related_to' => $related );
		$new_options[ 'map_option_fullscreenControl' ] = array( 'type' => 'checkbox' , 'default' => '1' , 'related_to' => $related );
		$new_options[ 'map_option_hide_streetview' ] = array( 'type' => 'checkbox' , 'default' => '0' , 'related_to' => $related );

		$this->attach_to_slp( $new_options , array( 'page' => 'slp_experience' , 'section' => 'map' , 'group' => 'appearance'  , 'add_to_settings_tab' => $this->subscription_is_valid ,'use_in_javascript' => true  ) );
	}

	/**
	 * Results | Appearance
	 */
    private function results_appearance() {
	    $this->slplus->SmartOptions->message_bad_address->add_to_settings_tab = $this->subscription_is_valid;

	    $new_options[ 'results_header'      ] = array( 'type' => 'textarea' , 'allow_empty' => true , 'related_to' => 'results_header_1,results_header_2,results_header_3,results_header_4,results_header'  );
	    $new_options[ 'results_no_wrapper'  ] = array( 'type' => 'checkbox' ,  'default' => '0' );
	    $new_options[ 'pagination_enabled'  ] = array( 'type' => 'checkbox' ,  'default' => '0' , 'related_to' => 'pagination_label' );

	    if ( $this->subscription_is_valid ) {
	    	$this->slplus->SmartOptions->loading_indicator->options[] = array( 'label' => __( 'Circular' , 'slp-premier') , 'value' => 'circular' );

		    $new_options[ 'loading_indicator_location' ] = array(
			    'type'  => 'dropdown',
			    'default'    => 'map' ,
			    'use_in_javascript' => true,
			    'related_to' => 'loading_indicator,loading_indicator_location, loading_indicator_color' ,
			    'classes'       => array( 'quick_save' ),
			    'options'    => array(
				    array( 'label' => 'Map'          , 'value' => 'map' ) ,
				    array( 'label' => 'Results'      , 'value' => 'results' ) ,
				    array( 'label' => 'Search'       , 'value' => 'search_form' ) ,
			    ) ,
		    );

		    /** @var SLP_Premier_Text_Admin_Experience $text */
		    $text = SLP_Premier_Text_Admin_Experience::get_instance();
		    $new_options[ 'loading_indicator_color' ] = array(
			    'type'  => 'dropdown',
			    'default'    => 'blue_light_grey' ,
			    'use_in_javascript' => true,
			    'related_to' => 'loading_indicator,loading_indicator_location, loading_indicator_color' ,
			    'classes'       => array( 'quick_save' ),
			    'options'    => array(
				    array( 'label' => $text->general( 'amber_light_grey' ) , 'value' => 'amber_light_grey' ) ,
				    array( 'label' => $text->general( 'blue_light_grey'  ) , 'value' => 'blue_light_grey'  ) ,
				    array( 'label' => $text->general( 'dark_light_grey'  ) , 'value' => 'dark_light_grey'  ) ,
				    array( 'label' => $text->general( 'green_light_grey' ) , 'value' => 'green_light_grey' ) ,
				    array( 'label' => $text->general( 'grey_light_grey'  ) , 'value' => 'grey_light_grey'  ) ,
				    array( 'label' => $text->general( 'red_light_grey'   ) , 'value' => 'red_light_grey'   ) ,
				    array( 'label' => $text->general( 'teal_light_grey'  ) , 'value' => 'teal_light_grey'  ) ,
				    array( 'label' => $text->general( 'yellow_light_grey') , 'value' => 'yellow_light_grey') ,
			    ) ,
		    );


	    }

	    $this->attach_to_slp( $new_options , array( 'page' => 'slp_experience' , 'section' => 'results' , 'group' => 'appearance', 'use_in_javascript' => true ) );
    }

	/**
	 * Results | Labels
	 */
	private function results_labels() {
		$new_options[ 'results_header_1' ] = array();
		$new_options[ 'results_header_2' ] = array();
		$new_options[ 'results_header_3' ] = array();
		$new_options[ 'results_header_4' ] = array();
		$this->attach_to_slp( $new_options , array( 'page' => 'slp_experience' , 'section' => 'results' , 'group' => 'labels' , 'add_to_settings_tab' => $this->subscription_is_valid ,'use_in_javascript' => true , 'allow_empty' => true , 'related_to' => 'results_header_1,results_header_2,results_header_3,results_header_4,results_header' ) );
	}

	/**
	 * Search | Labels
	 */
	private function search_labels() {
		$new_options[ 'search_box_subtitle' ] = array();
		$this->attach_to_slp( $new_options , array( 'page' => 'slp_experience' , 'section' => 'search' , 'group' => 'labels' , 'add_to_settings_tab' => $this->subscription_is_valid , 'use_in_javascript' => true, 'allow_empty' => true ) );
	}

    /**
     * Search | Appearance | Search Form Style
     */
    private function search_appearance_search_form_style() {
        if ( ! $this->subscription_is_valid )
            return;
        $this->slplus->SmartOptions->dropdown_style->options[] = array( 'label' => __( 'Lightness' , 'slp-premier' ) , 'value' => 'lightness' );
        $this->slplus->SmartOptions->dropdown_style->options[] = array( 'label' => __( 'Smoothness' , 'slp-premier' ) , 'value' => 'smoothness' );
        $this->slplus->SmartOptions->dropdown_style->options[] = array( 'label' => __( 'Vader' , 'slp-premier' ) , 'value' => 'vader' );
    }

    /**
     * General / Schedule
     */
    private function create_general_schedule_options() {
        // Tasks
        $new_options[ 'schedule_for_geocoding' ]        = array(
            'page'              => 'slp_general' ,
            'section'           => 'schedule' ,
            'group'             => 'tasks' ,
            'type'              => 'dropdown' ,
            'default'           => 'never' ,
            'options'           => array(
                array( 'label' => __( 'Never' , 'slp-premier' ) , 'value' => 'never' , 'description' => __( 'Not scheduled.' , 'slp-premier' ) ) ,
                array( 'label' => __( 'Now' , 'slp-premier' ) , 'value' => 'now' , 'description' => __( 'Run just once.' , 'slp-premier' ) ) ,
                array( 'label' => __( 'Hourly' , 'slp-premier' ) , 'value' => 'hourly' , 'description' => __( 'Run every hour.' , 'slp-premier' ) ) ,
                array( 'label' => __( 'Twice Daily' , 'slp-premier' ) , 'value' => 'twicedaily' , 'description' => __( 'Run twice per day.' , 'slp-premier' ) ) ,
                array( 'label' => __( 'Daily' , 'slp-premier' ) , 'value' => 'daily' , 'description' => __( 'Run once per day.' , 'slp-premier' ) ) ,
            ) ,
            'call_when_changed' => array( $this , 'schedule_changes' ) ,
            'call_when_time'    => array( $this , 'register_cron_hooks' ) ,
        );
        $new_options[ 'schedule_for_initial_distance' ] = array(
            'page'              => 'slp_general' ,
            'section'           => 'schedule' ,
            'group'             => 'tasks' ,
            'type'              => 'dropdown' ,
            'default'           => 'never' ,
            'options'           => array(
                array( 'label' => __( 'Never' , 'slp-premier' ) , 'value' => 'never' , 'description' => __( 'Not scheduled.' , 'slp-premier' ) ) ,
                array( 'label' => __( 'Now' , 'slp-premier' ) , 'value' => 'now' , 'description' => __( 'Run just once.' , 'slp-premier' ) ) ,
                array( 'label' => __( 'Hourly' , 'slp-premier' ) , 'value' => 'hourly' , 'description' => __( 'Run every hour.' , 'slp-premier' ) ) ,
                array( 'label' => __( 'Twice Daily' , 'slp-premier' ) , 'value' => 'twicedaily' , 'description' => __( 'Run twice per day.' , 'slp-premier' ) ) ,
                array( 'label' => __( 'Daily' , 'slp-premier' ) , 'value' => 'daily' , 'description' => __( 'Run once per day.' , 'slp-premier' ) ) ,
            ) ,
            'call_when_changed' => array( $this , 'schedule_changes' ) ,
            'call_when_time'    => array( $this , 'register_cron_hooks' ) ,
        );

        $this->attach_to_slp( $new_options );
    }

    /**
     * General / User Interface
     */
    private function general_ui_url_control() {
    	$checkbox_off = array( 'type' => 'checkbox' , 'default' => '0' , 'add_to_settings_tab' => $this->subscription_is_valid );

        $new_options[ 'allow_limit_in_url'      ] = $checkbox_off;
        $new_options[ 'allow_location_in_url'   ] = $checkbox_off;
	    $new_options[ 'allow_tag_in_url'        ] = $checkbox_off;

        $this->attach_to_slp( $new_options , array( 'page' => 'slp_general' , 'section' => 'user_interface' , 'group' => 'url_control' ) );
    }

    /**
     * General / Server / Web App Settings
     */
    private function general_server_web_app_settings() {
        $new_options[ 'use_territory_bounds' ] = array(
            'type'                => 'checkbox' ,
            'default'             => '0' ,
            'call_when_changed'   => array( $this , 'toggle_option' ) ,
            'add_to_settings_tab' => $this->subscription_is_valid ,
        );

        $this->attach_to_slp( $new_options ,  array( 'page' => 'slp_general' , 'section' => 'server' , 'group' => 'web_app_settings' ));
    }

    /**
     * General / Server / Security
     */
    private function general_server_security() {
        $time_strings = array(
            'hour' => strval(HOUR_IN_SECONDS),
            'day' => strval(DAY_IN_SECONDS),
            'week' => strval(WEEK_IN_SECONDS),
        );

        $options[ 'block_ip_limit' ] = array(
            'add_to_settings_tab' => $this->subscription_is_valid ,
        );

        $options[ 'block_ip_period' ] = array(
            'type'                => 'dropdown' ,
            'add_to_settings_tab' => $this->subscription_is_valid ,
            'options'           => array(
                array( 'label' => __( 'Never'   , 'slp-premier' ) , 'value' => '0'                   , 'description' => __( 'Do not block access' , 'slp-premier' ) ) ,
                array( 'label' => __( 'Hour'    , 'slp-premier' ) , 'value' => $time_strings['hour'] , 'description' => __( 'per hour' , 'slp-premier' ) ) ,
                array( 'label' => __( 'Day'     , 'slp-premier' ) , 'value' => $time_strings['day' ] , 'description' => __( 'per day' , 'slp-premier' ) ) ,
                array( 'label' => __( 'Week'    , 'slp-premier' ) , 'value' => $time_strings['week'] , 'description' => __( 'per week' , 'slp-premier' ) ) ,
            ) ,

        );

        $options[ 'block_ip_release_after' ] = array(
            'type'                => 'dropdown' ,
            'add_to_settings_tab' => $this->subscription_is_valid ,
            'options'           => array(
                array( 'label' => __( 'Hour'    , 'slp-premier' ) , 'value' => $time_strings['hour'] , 'description' => __( 'clear after an hour' , 'slp-premier' ) ) ,
                array( 'label' => __( 'Day'     , 'slp-premier' ) , 'value' => $time_strings['day' ] , 'description' => __( 'clear after a day'   , 'slp-premier' ) ) ,
                array( 'label' => __( 'Week'    , 'slp-premier' ) , 'value' => $time_strings['week'] , 'description' => __( 'clear after a week'  , 'slp-premier' ) ) ,
            ) ,

        );

        $options[ 'ip_whitelist' ] = array(
            'type'                => 'textarea' ,
            'add_to_settings_tab' => $this->subscription_is_valid ,
        );

        $this->attach_to_slp( $options , array( 'page' => 'slp_general' , 'section' => 'server' , 'group' => 'security' ) );
    }

    /**
     * Register cron hooks.
     *
     * @param string $slug the property "name"
     */
    public function register_cron_hooks( $slug ) {
        if ( ! in_array( $slug , $this->our_options ) ) {
            return;
        }
	    $this->slplus->AddOns->register( $this->addon->slug , $this->addon );
        $schedule_manager = new SLP_Premier_Schedule_Manager();
        $schedule_manager->create_hook( $slug );
    }

    /**
     * Process schedule changes.
     *
     * @param string $slug the property "name"
     * @param string $old_value
     * @param string $new_value
     */
    public function schedule_changes( $slug , $old_value , $new_value ) {
        switch ( $this->slplus->SmartOptions->$slug->page ) {
            case 'slp_general':
	            $schedule_manager = new SLP_Premier_Schedule_Manager();
                $schedule_manager->put_on_schedule( $slug , $new_value );
                break;
        }
    }

    /**
     * Toggle options on and off.
     *
     * @param $key
     * @param $original
     * @param $new
     */
    public function toggle_option( $key , $original , $new ) {
        switch ( $key ) {
            case 'use_territory_bounds':
                if ( ! is_null( $original ) ) {
                    if ( $this->slplus->is_CheckTrue( $new ) ) {
                        require_once( SLPPREMIER_REL_DIR . 'include/class.activation.php' );
                        $activation = new SLP_Premier_Activation( array( 'addon' => $this->slplus->addon( 'premier' ) ) );
                        $activation->add_territory_bounds();
                    }
                    $this->slplus->notifications->add_notice( 'info' , sprintf( __( 'Territory Bounds has been %s.' , 'slp-premier' ) , $this->slplus->is_CheckTrue( $new ) ? __( 'activated' , 'slp-premier' ) : __( 'deactivated' , 'slp-premier' ) ) );
                }
                break;
        }
    }
}
