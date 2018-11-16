<?php
if ( ! class_exists( 'SLP_Premier_Admin_Locations' ) ) {

    /**
     * Class SLP_Premier_Admin_Locations
     *
     * The things that modify the Admin / Locations interface.
     *
     * @package   StoreLocatorPlus\SLP_Premier\Admin\Locations
     * @author    Lance Cleveland <lance@storelocatorplus.com>
     * @copyright 2016 Charleston Software Associates, LLC
     *
     * Text Domain: slp-premier
     *
     * @property-read   SLP_Premier                  $addon
     * @property-read   SLP_Premier_Location_Add     $location_add
     * @property-read   SLP_Premier_LocationImport   $location_import
     * @property-read   boolean                     $location_was_geocoded  set to true if the current location has been goecoded.
     * @property        SLP_Premier_Territory        $territory
     *
     */
    class SLP_Premier_Admin_Locations extends SLP_Object_With_Objects {
        public $addon;
        private $location_add;
        private $location_was_geocoded = false;
        private $location_import;
        public  $territory;
        private $territory_properties = array(
            'territory_distance_unit' ,
            'territory_distance_north' ,
            'territory_distance_south' ,
            'territory_distance_east' ,
            'territory_distance_west'
        );

	    protected $class_prefix = 'SLP_Premier_';
	    protected $objects = array(
		    'Admin_Locations_Text' => array( 'subdir' => 'include/module/admin/', 'object' => null, 'auto_instantiate' => true,  ),
	    );


        /**
         * At startup.
         */
        protected function at_startup() {
        	
            // Add Woo Fields If Needed
            //
            if ( $this->addon->is_woo_running() ) {
                $this->addon->instantiate( 'WooCommerce_Glue' );
                $this->addon->WooCommerce_Glue->add_extended_data_fields();
            }

            // Territories Enabled
            //
            if ( $this->slplus->SmartOptions->use_territory_bounds->is_true ) {
                add_action( 'slp_add_location_custom_display', array( $this , 'extend_add_locations_form'       ), 10, 3 );
                add_action( 'slp_location_geocoded'          , array( $this , 'current_location_was_geocoded'   )        );
                add_action( 'slp_extended_data_update'       , array( $this , 'save_territory_bounds'           ), 10, 3 );
                add_filter( 'slp_csv_locationdata_added'     , array( $this , 'set_territory_bounds_on_import'  )        ); // PRO addon uses this method.
                add_filter( 'slp_csv_locationdata'           , array( $this , 'strip_sl_from_territory_fields'  )        ); // PRO addon uses this method.

            }
        }

        /**
         * Create and attach the location import object.
         */
        private function create_object_location_add() {
            if ( ! isset( $this->location_add ) ) {
                require_once( 'SLP_Premier_Location_Add.php' );
                $this->location_add = new SLP_Premier_Location_Add( array( 'addon' => $this->addon , 'location' => $this ) );
            }
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
         * The current location has been geocoded, rerun territory boundaries.
         *
         * @param SLPlus_Location $location
         */
        public function current_location_was_geocoded( $location ) {
            $this->location_was_geocoded = true;
        }

        /**
         * Map aliases for field names.
         *
         * @param   SLP_Settings $settings           SLP Settings Interface reference SLPlus->ManageLocations->settings
         * @param   array[]      $group_params       The metadata needed to build a settings group.
         * @param   array[]      $data_field         The current extended data field meta.
         */
        public function extend_add_locations_form( $settings , $group_params, $data_field ) {
            $this->create_object_location_add();
            $this->create_object_territory();
            $this->location_add->add_territories_interface( $settings , $group_params, $data_field  );
        }

        /**
         * Add the territory bounds to the extended data when it is updated/saved.
         *
         * @param   array $data         The new data to be written.
         * @param   array $current_data The pre-existing extended data for this location, to be changed.
         * @param   array $location_id  The ID of the location being updated.
         *
         * @return  array $data         Revised data to be inserted.
         */
        public function save_territory_bounds(  $data , $current_data , $location_id ) {

            // Force territory to recalc if the current location was geocoded again.
            if ( $this->location_was_geocoded ) {
                unset( $current_data[ 'territory_distance_unit' ] );        // A simple way to force set territory bounds to be run.
            }

            $this->create_object_territory();
            foreach ( $this->territory_properties as $property ) {
                if ( ! isset( $data[ $property ] ) ) { continue; }
                if ( ! isset( $current_data[ $property ] ) || ( $data[ $property ] !== $current_data[ $property ] ) ) {
                    $data = $this->territory->set_territory_bounds( $data );
                    break;
                }
            }

            return $data;
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
         * Set data properties without the leading 'sl_' added by Pro Pack import. WTF.
         * @param $data
         *
         * @return mixed
         */
        public function strip_sl_from_territory_fields( $data ) {
            foreach ( $this->territory_properties as $property ) {
                if ( isset( $data[ 'sl_' . $property ] ) ) {
                    $data[ $property ] = $data[ 'sl_' . $property ];
                }
            }
            return $data;
        }

    }

}
