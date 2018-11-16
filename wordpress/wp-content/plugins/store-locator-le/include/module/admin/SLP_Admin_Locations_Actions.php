<?php
defined( 'ABSPATH' ) || exit;

/**
 * Admin Locations Tab Actions Processing
 *
 * @property        boolean                     $all
 * @property-read   string[]                    $locations  The list of locations (IDs) to be processed.
 * @property-read   int                         $offset     Current location offset.
 * @property        SLPlus                      $slplus
 * @property        SLPlus_AdminUI_Locations    $screen     The screen we are processing.
 */
class SLP_Admin_Locations_Actions extends SLPlus_BaseClass_Object {
    public  $all;
    private $locations;
    private $offset;
    public  $screen;

    /**
     * Add a location.
     *
     * Called for add mode only.
     */
    private function add_location() {
        $form_has_data = false;
        $response_code = 'invalid_form';

        // Add the location based on post data.
        //
        $locationData = array();
        foreach ( $_POST as $key => $value ) {
            if ( ! $this->slplus->currentLocation->valid_location_property( $key )  ) continue;
            if ( ( $key === 'latitude'  ) && ! $this->slplus->currentLocation->is_valid_lat( $value ) ) continue;
            if ( ( $key === 'longitude' ) && ! $this->slplus->currentLocation->is_valid_lng( $value ) ) continue;

            $data_field = $this->slplus->currentLocation->is_base_field( $key ) ? $this->slplus->currentLocation->dbFieldPrefix . $key : $key;
            $locationData[ $data_field ] = empty( $value ) ?  '' : $value;
            if ( ! $form_has_data ) $form_has_data = ! empty( $value );
        }

        if ( $form_has_data ) {
            $skipGeocode =
	            $this->slplus->currentLocation->is_valid_lat( $this->null_coal( $locationData , 'sl_latitude' ) ) &&
				$this->slplus->currentLocation->is_valid_lng( $this->null_coal( $locationData , 'sl_longitude' ) )
                ;
            $response_code = $this->slplus->currentLocation->add_to_database( $locationData , 'none' , $skipGeocode );
        }

        require_once( SLPLUS_PLUGINDIR . 'include/module/location/SLP_Location_Manager.php' );
        $this->slplus->Location_Manager->create_notification( $response_code );
    }

    /**
     * Our own null coal.
     * @param $array
     * @param $key
     *
     * @return mixed
     */
    private function null_coal( $array , $key ) {
        return empty( $array[$key] ) ? '' : $array[ $key ];
    }

    /**
     * Delete location(s) action.
     */
    private function delete() {
        if ( ! $this->set_locations() ) { return; }

        $id = $this->get_next_location();
        while ( ! is_null( $id ) ) {
            $this->slplus->currentLocation->delete( $id , $this->all );
            if ( $this->all ) { $this->offset = 0; }
            $id = $this->get_next_location();
        }
    }

    /**
     * Get the next location on the location list.
     *
     * @return mixed|null
     */
    public function get_next_location() {
        if ( $this->all ) {
            $data = $this->slplus->database->get_Record(array('selectslid') , array() , $this->offset++ );
            return ( $data['sl_id'] > 0 ) ? $data['sl_id'] : null;
        } else {
            return ( $this->offset < count( $this->locations) ) ? $this->locations[ $this->offset++ ] : null;
        }
    }

    /**
     * Process any incoming actions.
     *
     * @see http://docs.storelocatorplus.com/plugindevelopment/store-locator-plus-location-actions/
     */
    public function process_actions() {
        if ( empty( $this->screen->current_action ) ) {
            return;
        }
        switch ( $this->screen->current_action ) {

            // ADD - the add location form was submitted
            //
            case 'add' :
                $this->add_location();
                $this->slplus->clean[ 'selected_nav_element' ] = '#wpcsl-option-current_locations';
                $_REQUEST[ 'selected_nav_element' ] = $this->slplus->clean[ 'selected_nav_element' ];
                break;

            // Save - the edit location form was submitted
            //
            case 'save':
                $this->save_edited_location();
                $this->slplus->clean[ 'selected_nav_element' ] = '#wpcsl-option-current_locations';
                $_REQUEST[ 'selected_nav_element' ] = $this->slplus->clean[ 'selected_nav_element' ];
                break;

            // Delete - manage locations delete a location.
            //
            case 'delete':
                $this->delete();
                break;

            // Load locations from a WordPress site
	        case 'load_from_wp':
		        SLP_Location_LoadFromWP::get_instance()->import();
	        	break;
        }

        /**
         * Hook executes when processing a manage locations action.
         *
         * @action  slp_manage_locations_action
         */
        do_action( 'slp_manage_locations_action' , $this);
    }

    /**
     * Save a location when the edit location form is submitted.
     */
    private function save_edited_location() {
        if ( ! $this->slplus->currentLocation->isvalid_ID( null , 'locationID' ) ) {
	        return;
        }
        $this->slplus->notifications->delete_all_notices();

        // Get our original address first
        //
        $this->slplus->currentLocation->set_PropertiesViaDB( $this->slplus->clean[ 'locationID' ] );

        // Add Checkboxes
        if ( ! isset( $_POST[ 'private' ] ) ) {
	        $_POST[ 'private' ] = '0';
        }

        // Update The Location Data
        //
	    $address_changed = false;
	    $geocode_triggers = array( 'address', 'address2' , 'city' , 'state' , 'zip', 'country' );
        foreach ( $_POST as $key => $value ) {
	        if ( ! $this->slplus->currentLocation->valid_location_property( $key ) ) {
		        continue;
	        }
	        if ( ( $key === 'latitude' ) && ( $value !== '' ) && ! $this->slplus->currentLocation->is_valid_lat( $value ) ) {
		        continue;
	        }
	        if ( ( $key === 'longitude' ) && ( $value !== '' ) && ! $this->slplus->currentLocation->is_valid_lng( $value ) ) {
		        continue;
	        }

	        // Has the data changed?
	        //
	        $stripped_value = stripslashes_deep( $value );
	        if (
	            ( ! is_null( $this->slplus->currentLocation->$key ) || ! empty( $stripped_value ) ) &&
	            ( $this->slplus->currentLocation->$key !== $stripped_value )
	        ){
		        $this->slplus->currentLocation->dataChanged = true;
		        $this->slplus->currentLocation->$key = $stripped_value;

		        // If the field that changed affects geocoding, trigger a change.
		        if ( in_array( $key , $geocode_triggers ) ) {
			        if ( ! $address_changed ) {
			        	$this->slplus->currentLocation->latitude = '';
			        	$address_changed = true;
			        }
		        }
	        }
        }

        // goecode if... lat or lng is now blank
	    //
	    if ( $address_changed || ! $this->slplus->currentLocation->is_valid_lat() || ! $this->slplus->currentLocation->is_valid_lng() ) {
		    $newAddress =
			    $this->slplus->currentLocation->address . ' ' .
			    $this->slplus->currentLocation->address2 . ', ' .
			    $this->slplus->currentLocation->city . ', ' .
			    $this->slplus->currentLocation->state . ' ' .
			    $this->slplus->currentLocation->zip   . ' ' .
			    $this->slplus->currentLocation->country;
		    $this->slplus->currentLocation->do_geocoding( $newAddress );
		    $this->slplus->currentLocation->dataChanged = true;
	    }


        // Extended Data Boolean Check
        //
	    $this->slplus->currentLocation->dataChanged =  $this->set_extended_data_booleans() || $this->slplus->currentLocation->dataChanged;

        /**
         * HOOK: slp_location_save
         *
         * Executes when a location save action is called from manage locations.
         *
         * @action slp_location_save
         */
        do_action( 'slp_location_save' );
        if ( $this->slplus->currentLocation->dataChanged ) {
	        if ( ! $this->slplus->currentLocation->MakePersistent() ) {
		        $this->slplus->notifications->add_notice( '1' , __( 'Could not update the location data.' , 'store-locator-le' ) );
	        }

        }

        /**
         * HOOK: slp_location_saved
         *
         * Executes after a location has been saved from the manage locations interface.  After EDIT only!
         *
         * @action slp_location_saved
         *
         */
        do_action( 'slp_location_saved' );
    }

    /**
     * Set extended data booleans.
     *
     * @return  boolean     True if any of these fields changed.
     */
    private function set_extended_data_booleans() {
        $something_changed = false;
        $this->screen->set_active_columns();
        foreach ( $this->screen->active_columns as $extraColumn ) {
            $slug = $extraColumn->slug;
            if ( $extraColumn->type === 'boolean' ) {
                $new_setting = empty( $_REQUEST[ $slug ] ) ? '0' : '1';
                if ( $this->slplus->currentLocation->exdata[$slug] !== $new_setting ) {
                    $this->slplus->currentLocation->exdata[ $slug ] = $new_setting;
                    $something_changed = true;
                }
            }
        }
        return $something_changed;
    }

    /**
     * Set the location list.
     *
     * @return  boolean        True if apply_to_all or the location id(s) array is set.  False if not doing any locations.
     */
    public function set_locations() {
        $this->offset = 0;
        $this->all = ( isset( $_REQUEST['apply_to_all'] ) && ( $_REQUEST['apply_to_all'] === '1' ) );
        if ( $this->all ) { return true; }

        if ( isset( $_REQUEST['sl_id']  ) ) { $this->locations = (array) $_REQUEST['sl_id']; return true; }
        if ( isset( $_REQUEST['id']     ) ) { $this->locations = (array) $_REQUEST['id']; return true; }

        return false;
    }
}
