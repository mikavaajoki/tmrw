<?php
defined( 'ABSPATH' ) || exit;
if (! class_exists( 'SLP_Experience_Admin' )) {

    require_once( SLPLUS_PLUGINDIR . 'include/base_class.admin.php');

    /**
     * Holds the admin-only code.
     *
     * This allows the main plugin to only include this file in admin mode
     * via the admin_menu call.   Reduces the front-end footprint.
     *
     * @property        SLP_Experience                $addon
     * @property     	string                        $current_section;
     * @property	    string                        $current_group;
     */
    class SLP_Experience_Admin extends SLP_BaseClass_Admin {
        public $addon;
	    protected $class_prefix = 'SLP_Experience_';
	    protected $slug = 'slp-experience';
        public  $settings_pages = array(
            'slp_experience' => array(
                    'disable_initial_directory'         ,
                    'hide_address_entry'                ,
                    'hide_bubble'                       ,       // serialized
                    'hide_distance'                     ,       // serialized
                    'hide_map'                          ,       // serialized
                    'hide_radius_selector'              ,       // serialized
                    'map_options_scaleControl'          ,       // serialized
                    'map_options_scrollwheel'           ,       // serialized
                    'map_options_mapTypeControl'        ,       // serialized
                    'search_by_name'                    ,       // serialized
                    'show_country'                      ,       // serialized
                    'show_hours'                        ,       // serialized
                    'show_maptoggle'                    ,       // serialized
            )
        ) ;
	    public  $settings_interface;

        /**
         * Add our SLP hooks and Filters for Admin Mode
         */
        public function add_hooks_and_filters() {
	        parent::add_hooks_and_filters();

	        if ( !empty( $_REQUEST['page'] ) ) {
	        	switch ( $_REQUEST['page'] ) {
			        case 'slp_experience':
				        new SLP_Experience_Admin_Settings();
				        new SLP_Experience_Admin_Settings_Text();
				        break;
			        case 'slp_general':
				        new SLP_Experience_Admin_General_Text();
				        break;
		        }
	        }

            // Admin : Locations
            //
            add_action('slp_manage_locations_action'        , array( $this , 'feature_locations_action' ) );
            add_filter('slp_locations_manage_bulkactions'   , array($this,'filter_LocationsBulkAction'  ) );
            add_filter('slp_locations_manage_cssclass'      , array($this,'filter_HighlightFeatured'    ) );

            // Power Imports
            //
            add_filter('slp_csv_locationdata_added'         , array($this,'filter_CSVImportLocationFeatures' ), 90 , 2   );
        }

        /**
         * Deactivate any plugins that this add-on replaces.
         */
        private function deactivate_replaced_addons() {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            $replaced_addons = array( 'slp-enhanced-map' , 'slp-enhanced-results' , 'slp-enhanced-search' , 'slp-widgets' );
            foreach ( $replaced_addons as $addon_slug ) {
                if ( $this->slplus->AddOns->get( $addon_slug , 'active' ) ) {
                    deactivate_plugins( $this->slplus->AddOns->instances[$addon_slug]->file );
                    $this->slplus->Helper->add_wp_admin_notification(
                        sprintf(
                            __( 'The %s add-on deactivated the conflicting %s add-on. '  , 'slp-experience' ) ,
                            $this->addon->name ,
                            $this->slplus->AddOns->instances[$addon_slug]->name
                        )
                    );
                }
            }
        }

        /**
         * Additional location processing on manage locations admin page.
         *                                            
         * @param   SLP_Admin_Locations_Actions  $processor
         */
        function feature_locations_action( $processor ) {
            if ( ! $processor->set_locations() ) { return; }

            if ( $_REQUEST['act'] === 'feature_location' ) {
	            require_once( SLP_EXPERIENCE_REL_DIR . 'include/module/admin/SLP_Experience_Admin_Locations.php');
	            $this->slplus->Experience_Admin_Locations->feature_locations( 'add' , $processor );
            } elseif ( $_REQUEST['act'] === 'remove_feature_location' ) {
	            require_once( SLP_EXPERIENCE_REL_DIR . 'include/module/admin/SLP_Experience_Admin_Locations.php');
	            $this->slplus->Experience_Admin_Locations->feature_locations( 'remove' , $processor );
            }
        }

        /**
         * Process incoming CSV import data and add our extended field attributes.
         *
         * note: CSV import field names always get the sl_ prefixed.
         *
         * @param array $locationData
         * @param string $result
         * @return array the original data, unchanged
         */
        function filter_CSVImportLocationFeatures($locationData, $result) {
            $newData = array();
            $extended_data_fields = array('featured', 'rank');
            foreach ($extended_data_fields as $field) {
                if ( isset($locationData['sl_'.$field]) ) { $newData[$field] = $locationData['sl_'.$field]; }
            }
            if ( count( $newData ) > 0 ) {
                $this->slplus->database->extension->update_data(
                    $this->slplus->currentLocation->id,
                    $newData
                );
            }
            return array($locationData,$result);
        }

        /**
         * Highlight the featured elements on the manage locations panel.
         *
         * @param string $extraCSSClasses
         * @return string
         */
        function filter_HighlightFeatured($extraCSSClasses) {
            return $extraCSSClasses . (($this->slplus->currentLocation->featured)?' featured ':'');
        }

        /**
         * Add more actions to the Bulk Action drop down on the admin Locations/Manage Locations interface.
         *
         * @param mixed[] $items
         *
         * @return mixed[]
         */
        function filter_LocationsBulkAction($items) {
            return
                array_merge(
                    $items,
                    array(
                        array(
                            'label'     =>  __('Feature Location','slp-experience')      ,
                            'value'     => 'feature_location'                        ,
                        ),
                        array(
                            'label'     =>  __('Stop Featuring Location','slp-experience') ,
                            'value'     => 'remove_feature_location'                 ,
                        ),
                    )
                );
        }

        /**
         * Deactivate the competing add-on packs.
         *
         * Does not deal with Widget pack since that is called in WP init, way before this sucker.
         */
        function update_install_info() {
            parent::update_install_info();
            $this->deactivate_replaced_addons();
        }
    }
}
