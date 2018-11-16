<?php
defined( 'ABSPATH' ) || exit;
if (! class_exists('SLP_Experience_Activation')) {
    require_once( SLPLUS_PLUGINDIR . 'include/base_class.activation.php');

    /**
     * Manage plugin activation.
     */
    class SLP_Experience_Activation extends SLP_BaseClass_Activation {

	    /**
		 * Change these single get_option() settings into an slp-experience serialized option.
	     * key = old setting key , value = new options array key
	     * @var array
	     */
	    public $legacy_options = array(
            'csl-slplus-custom_css'                                 => array( 'key' => 'custom_css'                         , 'since'	=> '4.4' ),
            'csl-slplus_disable_initialdirectory'                   => array( 'key' => 'disable_initial_directory'          , 'since'	=> '4.4' ),
            'csl-slplus_disable_scrollwheel'                        => array( 'key' => 'map_options_scrollwheel'            , 'since'   => '4.4' , 'callback' => 'SLP_Experience_Activation::inverse_legacy_boolean' ),
            'csl-slplus_disable_scalecontrol'                       => array( 'key' => 'map_options_scaleControl'           , 'since'   => '4.4' , 'callback' => 'SLP_Experience_Activation::inverse_legacy_boolean' ),
            'csl-slplus_disable_maptypecontrol'                     => array( 'key' => 'map_options_mapTypeControl'         , 'since'   => '4.4' , 'callback' => 'SLP_Experience_Activation::inverse_legacy_boolean' ),
            'csl-slplus-enhanced_results_hide_distance_in_table'    => array( 'key' => 'hide_distance'                      , 'since'	=> '4.4' ),
            'csl-slplus-enhanced_results_orderby'                   => array( 'key' => 'orderby'                            , 'since'	=> '4.4' ),
            'csl-slplus-enhanced_results_show_country'              => array( 'key' => 'show_country'                       , 'since'	=> '4.4' ),
            'csl-slplus-enhanced_results_show_hours'                => array( 'key' => 'show_hours'                         , 'since'	=> '4.4' ),
            'csl-slplus-enhanced_search_hide_search_form'           => array( 'key' => 'hide_search_form'                   , 'since'	=> '4.4' ),
		    'csl-slplus-enmap_hidemap'                              => array( 'key' => 'hide_map'                           , 'since'	=> '4.4' ),
            'csl-slplus-es_allow_addy_in_url'                       => array( 'key' => 'url_allow_address'                  , 'since'	=> '4.4' ),
            'csl-slplus_find_button_label'                          => array( 'key' => 'label_for_find_button'              , 'since'	=> '4.4' ),
            'csl-slplus_hide_address_entry'                         => array( 'key' => 'hide_address_entry'                 , 'since'	=> '4.4' ),
            'csl-slplus_hide_radius_selections'                     => array( 'key' => 'hide_radius_selector'               , 'since'   => '4.4' ),
            'csl-slplus-maptoggle_label'                            => array( 'key' => 'label_for_map_toggle'               , 'since'	=> '4.4' ),
            'csl-slplus-no_autozoom'                                => array( 'key' => 'no_autozoom'                        , 'since'	=> '4.8' ),
            'csl-slplus_search_by_city_pd_label'                    => array( 'key' => 'first_entry_for_city_selector'      , 'since'	=> '4.4' ),
            'csl-slplus_search_by_country_pd_label'                 => array( 'key' => 'first_entry_for_country_selector'   , 'since'	=> '4.4' ),
            'csl-slplus_search_by_state_pd_label'                   => array( 'key' => 'first_entry_for_state_selector'     , 'since'	=> '4.4' ),
		    'csl-slplus-show_maptoggle'                             => array( 'key' => 'show_maptoggle'                     , 'since'	=> '4.4' ),
            'csl-slplus_show_search_by_name'                        => array( 'key' => 'search_by_name'                     , 'since'	=> '4.4' ),
            'csl-slplus_message_noresultsfound'                     => array( 'key' => 'message_no_results'                 , 'since'	=> '4.4' ),
            'sl_name_label'                                         => array( 'key' => 'label_for_name'                     , 'since'	=> '4.4' ),
            'sl_starting_image'                                     => array( 'key' => 'starting_image'                     , 'since'	=> '4.4' ),
            'sl_use_city_search'                                    => array( 'key' => 'city_selector'                      , 'since'	=> '4.4' , 'callback' =>  'SLP_Experience_Activation::set_search_selector'   ) ,
            'sl_use_country_search'                                 => array( 'key' => 'country_selector'                   , 'since'	=> '4.4' , 'callback' =>  'SLP_Experience_Activation::set_search_selector'   ) ,
            'slplus_show_state_pd'                                  => array( 'key' => 'state_selector'                     , 'since'	=> '4.4' , 'callback' =>  'SLP_Experience_Activation::set_search_selector'   ) ,
	    );

	    /**
	     * Options no longer supported but may be ported during upgrades.
	     */
	    protected $obsolete_options = array(
		    'csl-slplus_hide_radius_selections',
		    'csl-slplus-no_autozoom',
		    'hide_results',
	    	'immediately_show_locations',
            'initial_results_returned',
            'message_no_results',
	    );

	    /**
	     * Going to be SLP Smart Options now
	     */
	    protected $smart_options = array(
		    'add_tel_to_phone',
		    'address_placeholder',
		    'append_to_search',
		    'google_map_style',
		    'hide_search_form',
		    'map_options_scaleControl',
		    'map_options_mapTypeControl',
		    'no_autozoom',
		    'no_homeicon_at_start',
		    'results_box_title',
	        'url_allow_address',
		    'layout',
		    'bubblelayout',
		    'map_initial_display',
		    'maplayout',
		    'resultslayout',
		    'searchlayout',
		    'starting_image',
	    );

        /**
         * Add extended data fields used by this plugin.
         */
        private function add_extended_data_fields() {
            $this->slplus->database->extension->add_field(
                __('Featured' ,'slp-experience') , 'boolean' ,
                array(
                    'slug'          => 'featured'               ,
                    'addon'         => $this->addon->short_slug ,
                    'display_type'  => 'checkbox',
                    'help_text'     =>
                        __( 'If checked the location will be marked as featured. '                                , 'slp-experience' ) .
                        __( 'Featured locations may display differently depending on the plugin style selected. ' , 'slp-experience' )
                    ) ,
                'wait'
                );

            $this->slplus->database->extension->add_field(
                __('Rank'     ,'slp-experience') , 'int'     ,
                array(
                    'slug'      => 'rank'                   ,
                    'addon'     => $this->addon->short_slug ,
                    'help_text' =>
                        __( 'Determine the sort order for this location, lower numbers are displayed first. '   , 'slp-experience' ) .
                        __( 'Sort order is determined by the order by settings.'                                , 'slp-experience' )
                    ) ,
                'wait'
                );

            $this->slplus->database->extension->add_field(
                __('Map Marker'   ,'slp-experience') , 'varchar' ,
                array(
                    'slug'          => 'marker'                 ,
                    'addon'         => $this->addon->short_slug ,
                    'display_type'  => 'icon'                  ,
                    'help_text'     => __( 'This image will be the map marker at all times regardless of assigned category or other map marker settings.' , 'slp-experience' )
                    ) ,
                'wait'
                );

            $this->slplus->database->extension->update_data_table( array('mode'=>'force'));
        }

        /**
         * Inverse a legacy settings boolean logic.
         *
         * @param $legacy_value
         * @return bool
         */
        static function inverse_legacy_boolean( $legacy_value ) {
            return ( $legacy_value === '0' );
        }

        /**
         * Set the city/state/country pulldown selector.
         *
         * @param $legacy_value
         * @return string
         */
        static function set_search_selector( $legacy_value ) {
            return ( $legacy_value === '1' ) ? 'dropdown_addressinput' : 'hidden';
        }

        /**
         * Convert single option settings to serialized & merge in EM/ER/ES/W options into this add-on.
         *
         * $this->addon->options['installed_version'] = version of the prior install
         * $this->addon->version = version of current product
         */
        function update() {

            if ( version_compare( $this->updating_from , '4.4.06' , '<=' ) ) {
                $this->migrate_legacy_options( 'csl-slplus-EM-options' );
                $this->migrate_legacy_options( 'csl-slplus-ER-options' );
                $this->migrate_legacy_options( 'csl-slplus_slper' );
                $this->migrate_legacy_options( 'csl-slplus-ES-options' );
                $this->migrate_legacy_options( 'csl-slplus_slpes' );
                $this->migrate_legacy_options( 'slp-widget-pack-options' );

                if ( isset( $this->addon->options[ 'radius_behavior' ] ) ) {
                    $this->slplus->options_nojs[ 'radius_behavior' ] = $this->addon->options[ 'radius_behavior' ];
                    $this->slplus->WPOption_Manager->update_wp_option( 'default' );
                } else {
                    if ( isset( $this->addon->options[ 'ignore_radius' ] ) && ( $this->addon->options[ 'ignore_radius' ] === '1' ) ) {
                        $this->slplus->options_nojs[ 'radius_behavior' ] = 'always_ignore';
                        $this->slplus->WPOption_Manager->update_wp_option( 'default' );
                    }
                }
            }

            if ( version_compare( $this->updating_from , '4.6.5' , '<=' ) && ! empty( $this->addon->options['message_no_results'] ) ) {
                $this->slplus->options['message_no_results'] = $this->addon->options['message_no_results'];
                $this->slplus->WPOption_Manager->update_wp_option( 'default' );
            }

            parent::update();

            $this->add_extended_data_fields();

            if ( version_compare( $this->updating_from , '4.4.01' , '<=' ) ) {
                $this->update_location_markers();
            }
        }

        /**
         * Version 4.4.01 stores per-location markers in the extended data 'markers' field.  Move from the old-school attributes field.
         */
        private function update_location_markers() {

            $location_updates = 0;
            $offset = 0;

            // For all locations where sl_option_value is not empty
            //
            add_filter( 'slp_extend_get_SQL' , array( $this , 'select_where_location_has_option_value' ) );
            $data = $this->slplus->database->get_Record(array('selectall','where_has_options','limit_one'));
            while (( $data['sl_id'] > 0)) {
                $this->slplus->currentLocation->set_PropertiesViaArray($data);

                // If extended marker field is not set
                // and marker is not empty
                // update marker data
                if ( ! $this->location_extended_marker_set() && $this->location_legacy_marker_set() ) {
                    $location_updates++;
                    $this->slplus->currentLocation->exdata['marker'] = $this->slplus->currentLocation->attributes['marker'];
                    $this->slplus->currentLocation->dataChanged = true;
                }

                // Extended marker is now set OR the marker is empty
                // clear the legacy marker
                //
                if ( $this->location_extended_marker_set() || $this->location_legacy_marker_empty() ) {
                    if ( isset( $this->slplus->currentLocation->attributes['marker'] ) ) {
                        unset($this->slplus->currentLocation->attributes['marker']);
                        if ( empty( $this->slplus->currentLocation->attributes ) ) {
                            $this->slplus->currentLocation->attributes = null;
                        }
                        $this->slplus->currentLocation->dataChanged = true;
                    }
                }

                // Write Data Changes
                //
                if ( $this->slplus->currentLocation->dataChanged ) {
                    $this->slplus->currentLocation->MakePersistent();
                }

                // Go To Next Record
                //
                $data = $this->slplus->database->get_Record(array('selectall','where_has_options','limit_one'));
            }

            // Show message of data change.
            //
            if ( $location_updates > 0 ) {
                $this->slplus->Helper->add_wp_admin_notification(
                    sprintf(
                        __('As part of the %s upgrade, %s per-location map markers were updated.', 'slp-experience') ,
                        $this->addon->name ,
                        $location_updates
                    ) ,
                    'info'
                );
            }

        }

        /**
         * Return true if the current location extended marker is set.
         *
         * @return bool
         */
        private function location_extended_marker_set() {
            if (   empty( $this->slplus->currentLocation->exdata             ) ) { return false; }
            if ( ! isset( $this->slplus->currentLocation->exdata['marker']   ) ) { return false; }
            return ! empty( $this->slplus->currentLocation->exdata['marker'] );
        }

        /**
         * Return true if the current location has the legacy marker stored in attributes (option-value).
         *
         * @return bool
         */
        private function location_legacy_marker_empty() {
            if ( ! isset( $this->slplus->currentLocation->attributes           ) ) { return false; }
            if (   empty( $this->slplus->currentLocation->attributes           ) ) { return false; }
            if ( ! isset( $this->slplus->currentLocation->attributes['marker'] ) ) { return false; }
            return empty( $this->slplus->currentLocation->attributes['marker'] );
        }

        /**
         * Return true if the current location has the legacy marker stored in attributes (option-value).
         *
         * @return bool
         */
        private function location_legacy_marker_set() {
            if ( ! isset( $this->slplus->currentLocation->attributes           ) ) { return false; }
            if (   empty( $this->slplus->currentLocation->attributes           ) ) { return false; }
            if ( ! isset( $this->slplus->currentLocation->attributes['marker'] ) ) { return false; }
            return ! empty( $this->slplus->currentLocation->attributes['marker'] );
        }

        /**
         * Only select locations with option values set.
         *
         * @param $command
         * @return string
         */
        public function select_where_location_has_option_value( $command ) {
            if ( $command !== 'where_has_options' ) { return $command; }
            return $this->slplus->database->add_where_clause(" (sl_option_value IS NOT NULL) AND (sl_option_value!='') AND (sl_option_value LIKE '%%marker%%') ");
        }

    }
}