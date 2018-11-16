<?php
defined( 'ABSPATH' ) || exit;
/**
 * Handle location actions.
 *
 * Text Domain: slp-power
 *
 * @property        SLPPower                       $addon
 * @property        SLP_Power_Admin_Locations      $location_handler
 * @property-read   int                            $offset                 The action loop SQL offset for locations
 * @property        SLP_Admin_Locations_Actions    $slp_action_handler
 */
class SLP_Power_Admin_Locations_Actions extends SLPlus_BaseClass_Object {
    public  $addon;
    public  $location_handler;
    private $offset;
    public  $slp_action_handler;

    /**
     * Setup the show uncoded filter for manage locations.
     *
     * @param string $where
     *
     * @return string
     */
    function add_where_uncoded_to_sql( $where ) {
        return
            $where .
            ( empty( $where ) ? '' : ' AND ' ) .
            ' (NOT (' . $this->slplus->database->filter_SetWhereValidLatLong( '' ) . ') ' .
            "or sl_latitude IS NULL or sl_longitude IS NULL)";
    }

    /**
     * Perform the manage locations action for bulk categorization.
     */
    private function categorize_locations() {
        if ( ! $this->slp_action_handler->set_locations() ) {
            return;
        }

        // Extract the categories
        //
        require_once( SLPPOWER_REL_DIR . 'include/module/category/SLP_Power_Category_Manager.php' );
        $inputCats = $this->slplus->Power_Category_Manager->get_categories_from_input();
        if ( count( $inputCats ) <= 0 ) {
            return;
        }

        add_action( 'slp_location_add', array( $this, 'update_category_and_page_id' ) );
        add_action( 'slp_location_save', array( $this, 'update_category_and_page_id' ) );

        $id = $this->slp_action_handler->get_next_location();
        while ( ! is_null( $id ) ) {
            $this->slplus->currentLocation->set_PropertiesViaDB( $id );

            // The Action
            $this->update_category_and_page_id();
            $this->slplus->currentLocation->MakePersistent();

            if ( $this->slp_action_handler->all ) {
                $this->offset = 0;
            }
            $id = $this->slp_action_handler->get_next_location();
        }
    }

    /**
     * Export locations to a local CSV file that to be fetched manually.
     */
    private function export_locations_to_local_csv_file() {

        $this->slplus->set_php_timeout();

        $this->addon->create_CSVLocationExporter();

        $csv_dir = SLPLUS_UPLOADDIR . 'csv';
        if ( ! is_dir( $csv_dir ) ) {
            mkdir( $csv_dir, 0755 );
        }

        $this->addon->csvExporter->do_WriteFile( 'file://' . SLPLUS_UPLOADDIR . 'csv/exported_slp_locations.csv' );
    }

    /**
     * Create Pages
     *
     * @used-by \SLP_Power_AJAX::create_page
     * @used-by \SLP_Power_Admin_Locations_Actions::process
     *
     * @return  WP_Error | null
     */
    public function create_pages() {
	    $this->slp_action_handler = SLP_Admin_Locations_Actions::get_instance();
	    if ( ! $this->slp_action_handler->set_locations() ) {
            return new WP_Error( 'create_page_no_locations' , __( 'No locations found to create pages.' , 'slp-power' ) );
        }

        SLP_Power_Pages_Admin::get_instance();
        add_filter( 'slp_location_page_attributes', array( $this , 'set_taxonomy_for_location' ) );

        $page_id = null;
        $id = $this->slp_action_handler->get_next_location();
        while ( ! is_null( $id ) && $this->slplus->currentLocation->isvalid_ID( $id ) ) {
            $this->slplus->currentLocation->set_PropertiesViaDB( $id );

            $page_id = $this->slplus->currentLocation->crupdate_Page();

            if ( $this->slp_action_handler->all ) {
                $this->offset = 0;
            }
            $id = $this->slp_action_handler->get_next_location();
        }

        if ( empty( $page_id ) ) $page_id = new WP_Error( 'create_page_no_store_page' , __( 'Store Page could not be created.' , 'slp-power' ) );
        return $page_id;
    }

    /**
     * Delete Pages
     */
    private function delete_pages() {
        if ( ! $this->slp_action_handler->set_locations() ) {
            return;
        }

        $id = $this->slp_action_handler->get_next_location();
        while ( ! is_null( $id ) ) {
            $this->slplus->currentLocation->set_PropertiesViaDB( $id );

            // The Action
            $post = get_post( $this->slplus->currentLocation->linked_postid );
            if ( ( $post !== null ) && ( $post->post_type === SLPlus::locationPostType ) ) {
                wp_delete_post( $this->slplus->currentLocation->linked_postid, true );
            } else {
	            SLP_Power_Pages_Admin::get_instance()->delete_page( $this->slplus->currentLocation->linked_postid );
            }

            if ( $this->slp_action_handler->all ) {
                $this->offset = 0;
            }
            $id = $this->slp_action_handler->get_next_location();
        }
    }

    /**
     * Additional location processing on manage locations admin page.
     *
     * @param    string $action
     */
    public function process( $action ) {

        switch ( $action ) {
            // Categorize Locations
            //
            case 'categorize' :
                $this->categorize_locations();
                break;

            case 'createpage':
                $this->create_pages();
                break;

            case 'deletepage':
                $this->delete_pages();
                break;

            // Export Locations To CSV
            // Also filter by property
            //
            case 'export':
                $this->slplus->notifications->enabled = false;
                require_once( SLPPOWER_REL_DIR . 'include/module/admin/SLP_Power_Admin_Location_Filters.php' );
                add_action( 'slp_manage_location_where', array(
                    $this->location_handler,
                    'action_ManageLocations_ByProperty',
                ) );
                $this->slplus->notifications->enabled = true;
                break;

            case 'export_local':
                $this->slplus->notifications->enabled = false;
                $this->export_locations_to_local_csv_file();
                require_once( SLPPOWER_REL_DIR . 'include/module/admin/SLP_Power_Admin_Location_Filters.php' );
                add_action( 'slp_manage_location_where', array(
                    $this->location_handler,
                    'action_ManageLocations_ByProperty',
                ) );
                $this->slplus->notifications->enabled = true;
                break;

            // Filter Locations on Manage Locations List
            //
            case 'filter_by_category' :

                // Taxonomies are in REQUEST, filter by categories.
                //
                if ( isset( $_REQUEST['tax_input'] ) && isset( $_REQUEST['tax_input']['stores'] ) ) {
                    require_once( SLPPOWER_REL_DIR . 'include/module/admin/SLP_Power_Admin_Location_Filters.php' );
                    add_filter( 'slp_manage_location_where', array(
                        $this->slplus->Power_Admin_Location_Filters,
                        'filter_locations_by_category',
                    ) );
                }
                break;

            // Import a CSV File
            //
            case 'import':
                $_REQUEST['selected_nav_element'] = '#wpcsl-option-import';
                $this->addon->create_CSVLocationImporter();
                $this->addon->csvImporter->start_import();
                break;

            // Add tags to locations
            case 'add_tag':
                $this->tag_locations( 'add' );
                break;

            // Remove tags from locations
            case 'remove_tag':
                $this->tag_locations( 'remove' );
                break;

            // Recode The Selected Locations
            case 'recode_all':
                $this->addon->recode_all_uncoded_locations();
                break;

            // Recode The Address
            case 'recode':
                $this->slplus->notifications->delete_all_notices();
                if ( isset( $_REQUEST['sl_id'] ) ) {

                    // Process SL_ID Array
                    // TODO: use where clause in database property
                    //
                    foreach ( (array) $_REQUEST['sl_id'] as $location_id ) {
                        $this->slplus->currentLocation->set_PropertiesViaDB( $location_id );
                        $this->slplus->currentLocation->do_geocoding();
                        if ( $this->slplus->currentLocation->dataChanged ) {
                            $this->slplus->currentLocation->MakePersistent();
                        }
                    }
                }
                break;

            // Filter to show uncoded locations only.
            case 'show_uncoded':
                add_action( 'slp_manage_location_where', array( $this, 'add_where_uncoded_to_sql' ) );
                break;

            // Filter with specific location properties
            case 'add':
            case 'save':
            case 'filter_by_property':
                require_once( SLPPOWER_REL_DIR . 'include/module/admin/SLP_Power_Admin_Location_Filters.php' );
                add_action( 'slp_manage_location_where', array(
                    $this->location_handler,
                    'action_ManageLocations_ByProperty',
                ) );
                add_filter( 'slp_manage_locations_actionbar_ui', array(
                    $this->slplus->Power_Admin_Location_Filters,
                    'createstring_FilterDisplay',
                ) );
                break;

            // Reset on show_all
            case 'show_all':
                require_once( SLPPOWER_REL_DIR . 'include/module/admin/SLP_Power_Admin_Location_Filters.php' );
                $this->slplus->Power_Admin_Location_Filters->reset();
                break;

            default:
                break;
        }
    }

    /**
     * Set the Tagalong categories for the new store page.
     *
     * SLP Filter: slp_location_page_attributes
     *
     * @param mixed[] $pageAttributes - the wp_insert_post page attributes
     *
     * @return mixed[] - pageAttributes with tax_input set
     */
    function set_taxonomy_for_location( $pageAttributes ) {
        $this->addon->set_LocationCategories();
        return array_merge( $pageAttributes, array( 'tax_input' =>  array( SLPlus::locationTaxonomy => $this->addon->current_location_categories ) ) );
    }

    /**
     * Tag a location
     *
     * @param string $action = add or remove
     */
    private function tag_locations( $action ) {
        if ( ! $this->slp_action_handler->set_locations() ) {
            return;
        }

        $id = $this->slp_action_handler->get_next_location();
        while ( ! is_null( $id ) ) {
            $this->slplus->currentLocation->set_PropertiesViaDB( $id );

            //adding tags
            if ( $action === 'add' ) {
                $new_tags = empty( $this->slplus->currentLocation->tags ) ? '' : ',';
                $new_tags .= $_REQUEST['sl_tags'];
                $this->slplus->currentLocation->tags .= $new_tags;

                //removing tags
            } else {
                $this->slplus->currentLocation->tags = '';
            }
            $this->slplus->currentLocation->MakePersistent();

            if ( $this->slp_action_handler->all ) {
                $this->offset = 0;
            }
            $id = $this->slp_action_handler->get_next_location();
        }

    }

    /**
     * Attach our category data to the update string.
     *
     * Put it in the sl_option_value field as a seralized string.
     *
     * Assumes currentLocation is set.
     */
    public function update_category_and_page_id() {
        require_once( SLPPOWER_REL_DIR . 'include/module/category/SLP_Power_Category_Manager.php' );
        $this->slplus->Power_Category_Manager->set_categories_from_input();
        $this->slplus->Power_Category_Manager->update_location_category();
    }
}
