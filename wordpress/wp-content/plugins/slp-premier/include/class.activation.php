<?php
if (! class_exists('SLP_Premier_Activation')) {
    require_once(SLPLUS_PLUGINDIR.'/include/base_class.activation.php');

    /**
     * Manage plugin activation.
     *
     * @property    SLP_Premier      $addon
     */
    class SLP_Premier_Activation  extends SLP_BaseClass_Activation {
		public $addon;
		protected $smart_options =  array(
			'boundaries_influence_type',
			'bubble_footnote',
			'clusters_enabled',
			'dropdown_style',
			'pagination_enabled',
			'map_option_hide_streetview',
			'results_header',
			'results_header_1',
			'results_header_2',
			'results_header_3',
			'results_header_4',
			'results_no_wrapper'        ,
			'search_box_subtitle'
		);

        /**
         * Update or create the data tables.
         *
         * This can be run as a static function or as a class method.
         */
        function update() {
			parent::update();

	        $this->slplus->WPOption_Manager->delete_smart_option( 'url_control' );

	        $this->addon->find_minmax_latlng();

			$this->add_woo_fields();

            if ( $this->slplus->SmartOptions->use_territory_bounds->is_true ) {
                $this->add_territory_bounds();
            }
        }

        /**
         * Add Territory Bounds field.
         */
        public function add_territory_bounds() {
            $this->slplus->database->extension->add_field(
                __( 'Boundary Unit'     ,'slp-premier' ),
                'text'   ,
                array(
                    'slug'              => 'territory_distance_unit'      ,
                    'addon'             => $this->addon->short_slug       ,
                    'display_type'      => 'list'                         ,
                    'custom'            => array( __('No Territory' , 'slp-premier' ) => '' , __( 'Miles' , 'slp-premier' ) => 'miles' , __('Kilometers' , 'slp-premier' ) => 'km' ),
                    'help_text'         => __( 'Should kilometers or miles be used when setting territory bounds when using distance from location?' , 'slp-premier' )
                )
            );
            $this->slplus->database->extension->add_field(
                __( 'North Boundary'     ,'slp-premier' ),
                'text'   ,
                array(
                    'slug'              => 'territory_distance_north'      ,
                    'addon'             => $this->addon->short_slug       ,
                    'display_type'      => 'text'                         ,
                    'help_text'         => __( 'Distance north of the location that is considered part of the covered territory.' , 'slp-premier' )
                )
            );
            $this->slplus->database->extension->add_field(
                __( 'South Boundary'     ,'slp-premier' ),
                'text'   ,
                array(
                    'slug'              => 'territory_distance_south'      ,
                    'addon'             => $this->addon->short_slug       ,
                    'display_type'      => 'text'                         ,
                    'help_text'         => __( 'Distance south of the location that is considered part of the covered territory.' , 'slp-premier' )
                )
            );
            $this->slplus->database->extension->add_field(
                __( 'East Boundary'     ,'slp-premier' ),
                'text'   ,
                array(
                    'slug'              => 'territory_distance_east'      ,
                    'addon'             => $this->addon->short_slug       ,
                    'display_type'      => 'text'                         ,
                    'help_text'         => __( 'Distance east of the location that is considered part of the covered territory.' , 'slp-premier' )
                )
            );
            $this->slplus->database->extension->add_field(
                __( 'West Boundary'     ,'slp-premier' ),
                'text'   ,
                array(
                    'slug'              => 'territory_distance_west'      ,
                    'addon'             => $this->addon->short_slug       ,
                    'display_type'      => 'text'                         ,
                    'help_text'         => __( 'Distance west of the location that is considered part of the covered territory.' , 'slp-premier' )
                )
            );
            $this->slplus->database->extension->add_field(
                __( 'Territory Bounds'     ,'slp-premier' ),
                'text'   ,
                array(
                    'slug'              => 'territory_bounds'             ,
                    'addon'             => $this->addon->short_slug       ,
                    'display_type'      => 'callback'
                )
            );

            $this->slplus->database->extension->update_data_table( array( 'mode' => 'force' ) );
        }

	    /**
	     * Add woo fields if WooCommerce is running.
	     */
	    private function add_woo_fields() {
		    if ( $this->addon->is_woo_running() ) {
			    $this->addon->instantiate('WooCommerce_Glue');
			    $this->addon->WooCommerce_Glue->add_extended_data_fields();
		    }
	    }
    }
}