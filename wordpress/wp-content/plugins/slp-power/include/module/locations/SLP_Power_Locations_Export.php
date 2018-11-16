<?php
defined( 'ABSPATH'     ) || exit;

/**
 * CSV Export for Power
 *
 * @property        array    $active_columns;
 * @property-read   string[] $dbFields      The fields we want to export.
 */
class SLP_Power_Locations_Export extends SLPlus_BaseClass_Object {
    private $active_columns;
    private $dbFields = array(
        'sl_id',
        'sl_store',
        'sl_address',
        'sl_address2',
        'sl_city',
        'sl_state',
        'sl_zip',
        'sl_country',
        'sl_latitude',
        'sl_longitude',
        'sl_tags',
        'sl_description',
        'sl_email',
        'sl_url',
        'sl_hours',
        'sl_phone',
        'sl_fax',
        'sl_image',
        'sl_private',
        'sl_neat_title',
    );
    protected $type;

    /**
     * Things we do at the start.
     */
    function initialize() {

        /**
         * FILTER: slp-pro-dbfields
         *
         * Sets the list of db fields to export.
         *
         * TODO: Remove when POWER uses slp-power-dbfields
         */
        $this->add_unique_field_slugs_to_export( apply_filters( 'slp-pro-dbfields', $this->dbFields ) );

        /**
         * FILTER: slp-power-dbfields
         *
         * Sets the list of db fields to export, new "power" version.
         */
        $this->add_unique_field_slugs_to_export(  apply_filters( 'slp-power-dbfields', $this->dbFields ) );

	    $this->add_unique_field_slugs_to_export( array( 'category' , 'category_slug' ) );

        $this->add_extended_fields_to_export();
    }

    /**
     * Add extended data fields to the csv export
     */
    private function add_extended_fields_to_export() {
        $this->active_columns = $this->slplus->database->extension->get_active_cols();
        $slug_list = array();
        foreach ( $this->active_columns as $col ) {
            $slug_list[] = $col->slug;
        }
        $this->add_unique_field_slugs_to_export( $slug_list );
    }

    /**
     * Add categories to the location data.
     *
     * @param mixed[] $locationArray
     *
     * @return mixed[]
     */
    public function add_tagalong_data_to_export( $locationArray ) {
        $locationArray[ 'category' ] = '';
        $locationArray[ 'category_slug' ] = '';
        $offset = 0;
        while ( $category = $this->addon->category_data->get_Record( array( 'tagalong_selectall', 'whereslid' ), $locationArray[ 'sl_id' ], $offset++ ) ) {
            $categoryData = get_term( $category[ 'term_id' ], 'stores' );
            if ( ( $categoryData !== NULL ) && !is_wp_error( $categoryData ) ) {
                $locationArray[ 'category_slug' ] .= $categoryData->slug . ',';
                $locationArray[ 'category' ] .= $categoryData->name . ',';
            } else {
                if ( is_wp_error( $categoryData ) ) {
                    $locationArray[ 'category' ] .= $categoryData->get_error_message() . ',';
                }
            }
        }
        $locationArray[ 'category' ] = preg_replace( '/,$/', '', $locationArray[ 'category' ] );
        $locationArray[ 'category_slug' ] = preg_replace( '/,$/', '', $locationArray[ 'category_slug' ] );

        return $locationArray;
    }

    /**
     * Only add unique slugs to the field list.
     *
     * @param string[] $slug_list
     */
    private function add_unique_field_slugs_to_export( $slug_list ) {
        if ( ! is_array( $slug_list) ) {
            return;
        }
        if ( empty( $slug_list ) ) {
            return;
        }
        sort( $slug_list );
        foreach ( $slug_list as $slug ) {
            if ( ! in_array( $slug , $this->dbFields ) ) {
                $this->dbFields[] = $slug;
            }
        }
    }

    /**
     * AJAX handler to send the data to a download file for the user.
     */
    public function do_SendFile() {
        $this->send_Header();
        $this->send_locations();
        die();
    }

    /**
     * Write a file without sending an HTML header, this is for local disk writes.
     *
     * @param string $output_file the output location php://output for download files
     */
    public function do_WriteFile( $output_file ) {
        $this->send_locations( $output_file );
    }

    /**
     * Send the CSV Header
     */
    public function send_Header() {
        header( 'Content-Description: File Transfer' );
        header( 'Content-Disposition: attachment; filename=slplus_' . $_REQUEST[ 'filename' ] . '.csv' );
        header( 'Content-Type: application/csv;' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );
    }

    /**
     * Send Locations
     *
     * @param string $output_location the output location php://output for download files
     */
    private function send_locations( $output_location = 'php://output' ) {
	    $this->addon = $this->slplus->addon( 'power' );

	    // Get the SQL command and where params
	    //
        $admin_location_filters = SLP_Power_Admin_Location_Filters::get_instance();
        list( $sqlCommand, $sqlParams ) = $admin_location_filters->create_LocationSQLCommand( $_REQUEST );

        // Open stdout
        // Byte Order Mark (BOM) for UTF-8 is \xEF\xBB\xBF
        // Byte Order Mark (BOM) for UTF-16 is \xFE\xFF
        // @see http://en.wikipedia.org/wiki/Byte_order_mark#UTF-8
        $stdout = fopen( $output_location, 'w' );
        fputs( $stdout, "\xEF\xBB\xBF" );

        // Export Header
        //
        fputcsv( $stdout, $this->dbFields );

        // Export records
        //
        $offset = 0;
        $sqlCommand[] = 'limit_one';
        $sqlCommand[] = 'manual_offset';
        $sqlParams[] = $offset;
        $last_param = count( $sqlParams ) - 1;
        while ( $locationArray = $this->slplus->database->get_Record( $sqlCommand, $sqlParams, 0 ) ) {

            // FILTER: slp-pro-csvexport
	        // TODO: Remove slp-pro-csvexport when Experience switches to slp-power-csvexport
            $locationArray = apply_filters( 'slp-pro-csvexport', $locationArray );

            $locationArray = apply_filters( 'slp-power-csvexport', $locationArray );
	        $locationArray = $this->add_tagalong_data_to_export( $locationArray );

	        // Fill in any gaps in the location data.
            //
            $locationArray = array_merge( array_fill_keys( $this->dbFields, '' ), $locationArray );

            fputcsv( $stdout, array_intersect_key( $locationArray, array_flip( $this->dbFields ) ), ',', '"' );
            $sqlParams[ $last_param ] = ++$offset;
        }

        // Close stdout
        //
        fflush( $stdout );
        fclose( $stdout );
    }

}