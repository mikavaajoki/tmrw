<?php
defined( 'ABSPATH' ) || exit;

/**
 * CSV Import
 *
 * @property            SLPPower                     $addon                      This addon pack.
 * @property            boolean                      $adle_setting               Autodetect line endings setting.
 * @property-read       int                          $attachment_id              The ID of the media file being processed.
 * @property            string[]                     $data                       The current CSV data array.
 * @property            string[]                     $fieldnames                 List of field names being processed.
 * @property            mixed                        $filehandle                 The CSV file handle.
 * @property-read       boolean                      $load_data
 * @property            int                          $maxcols                    What is the maximum data columns allowed for this CSV file?
 * @property            SLP_Message_Manager          $messages                   The message stack for the current import operation.
 * @property-read       string                       $mode_for_current_record    What mode is the current record in 'add' || 'update' || 'skip' - set by internal processes
 * @property            string                       $output                     Output format 'html' or 'json'
 * @property            string[]                     $processing_report          The processing report.
 * @property-read       bool                         $reported_too_many_fields   Did we already report a too many fields problem?
 * @property-read       boolean                      $skip_geocoding             Should geocoding be skipped?
 */
class SLP_Power_Locations_Import extends SLPlus_BaseClass_Object {
	const hook = 'slp_import_locations';

    public $addon;
    private $adle_setting;
    private $attachment_id;
    private $base_fieldnames = array(
        'id'        => 'sl_id',
        'store'     => 'sl_store',
        'address'   => 'sl_address',
        'address2'  => 'sl_address2',
        'city'      => 'sl_city',
        'state'     => 'sl_state',
        'zip'       => 'sl_zip',
        'country'   => 'sl_country',
        'latitude'  => 'sl_latitude',
        'longitude' => 'sl_longitude',
    );
    public $filehandle;
    public $file_meta = NULL;
    private $data;
    private $fieldnames;
    private $load_data;
    private $maxcols;
    public $messages;
	private $mode_for_current_record;
    public $output = 'html';
    public $processing_report = array();
    private $reported_too_many_fields = false;
    private $skip_geocoding = false;

    public $update_codes = array( 'added' , 'exists' , 'not_updated' , 'skipped' , 'malformed' , 'updated' );

    /**
     * Things we do at startup.
     *
     * @uses \SLP_Power_Locations_Import::add_csv_mime_type
     */
    function initialize() {
    	$this->addon = $this->slplus->addon( 'Power' );
        $this->addon->create_object_import_messages();
	    $this->messages = $this->addon->messages['import'];
        $this->slplus->set_php_timeout();
        $this->skip_geocoding = $this->slplus->is_CheckTrue( $this->addon->options[ 'csv_skip_geocoding' ] );
        $this->load_data = $this->slplus->is_CheckTrue( $this->addon->options[ 'load_data' ] );
    }

	/**
	 * Add upload meta.
	 *
	 * @param array|bool $data          Array of meta data for the given attachment, or false
	 *                                  if the object does not exist.
	 * @param int        $attachment_id Attachment post ID.
	 *
	 * @return mixed
	 */
	public function add_upload_meta( $data, $attachment_id ) {
		if ( empty( $_REQUEST[ 'data_type' ] ) ) return $data;
		if ( $_REQUEST[ 'action' ] !== 'upload-attachment' ) return $data;

		$data[ 'data_type' ] = sanitize_key( $_REQUEST[ 'data_type' ] );
		$data[ 'processed' ] = false;
		$data[ 'record'    ] = 0;
		$data[ 'offset'    ] = 0;
		foreach ( $this->update_codes as $update_code ) {
			$data[ $update_code ] = 0;
		}

		if (  ! empty( $_FILES[ 'async-upload' ] ) ) {
			$data['original_name'] = ! empty( $_FILES[ 'async-upload' ]['name'] ) ? $_FILES[ 'async-upload' ]['name'] : '';
			$data['size'] = ! empty( $_FILES[ 'async-upload' ]['size'] ) ? $_FILES[ 'async-upload' ]['size'] : 0;
		}

		return $data;
	}

    /**
     * Add sl_ to any base data fields (id, store, address, address2, etc.) that are in the location data.
     *
     * @param   array $location_data
     * @return  array   location data with 'sl_' attached to the keys that are base fields.
     */
    public function add_sl_to_base_fieldnames( $location_data ) {
        $base_location_intersect = array_intersect_key( $this->base_fieldnames, $location_data );
        foreach ( $base_location_intersect as $csv_field => $location_data_field ) {
            if ( !empty( $location_data[ $csv_field ] ) ) {
                $location_data[ $location_data_field ] = $location_data[ $csv_field ];
                unset( $location_data[ $csv_field ] );
            }
        }

        return $location_data;
    }

    /**
     * Process the file being imported.
     *
     * cron_csv_import action takes 2 parameters:
     * param 1: the action to perform
     * param 2: the params to be sent to the action processor
     *
     * @param mixed $mode
     */
    public function execute_action( $mode = NULL ) {
        if ( $mode === NULL ) {
            $mode = $this->addon->options[ 'cron_import_recurrence' ];
        }

        // Cron Job CSV Import Processing
        //
        switch ( $mode ) {

            // Immediate Processing
            //
            case 'immediate':
            case 'none'     :
                wp_clear_scheduled_hook( 'cron_csv_import', array( 'import_csv', $this->file_meta ) );

                if ( $this->addon->options[ 'load_data' ] ) {
                    $this->load_directly_into_mysql();
                } else {
                    $this->process_File();
                }
                break;

            // Load a CSV file in temp directory.
            //
            case 'load':
                $this->open_file_and_start_importing();
                break;

            // ASAP
            //
            case 'at'  :
                $this->addon->create_cron_object();
                $this->addon->cron->schedule_one_time_job( $this->file_meta );
                break;

            // Hourly, Twice Daily, Daily
            //
            case 'recurring':
            default     :
                $this->addon->create_cron_object();
                $this->addon->cron->schedule_recurring_job( $this->file_meta );
                break;
        }
    }

    /**
     * Set the process count output strings the users sees after an upload.
     *
     * @return array
     */
    private function filter_SetMessages() {
        return
            array(
                'added'           => __( ' new locations added.', 'slp-power' ),
                'location_exists' => __( ' pre-existing locations skipped.', 'slp-power' ),
                'malformed'       => __( ' locations skipped due to missing or malformed CSV data.', 'slp-power' ),
                'not_updated'     => __( ' locations did not need to be updated.', 'slp-power' ),
                'skipped'         => __( ' locations were skipped due to duplicate address information.', 'slp-power' ),
                'updated'         => __( ' locations were updated.', 'slp-power' ),
            );
    }

	/**
	 * Get attachment details
	 *
	 * @param int $id
	 * @return array
	 */
	private function get_attachment_details( $id ) {
		$details = wp_get_attachment_metadata( $id );
		$details[ 'local_file' ] = ! empty( $details[ 'local_file' ] ) ? $details[ 'local_file' ] : '???';
		$details[ 'url'  ] = wp_get_attachment_url( $id );
		$details[ 'filename' ] = str_replace( $this->uploads['basedir'], '' , $details[ 'local_file' ] );
		return $details;
	}

	/**
	 * Return a list of active location imports by attachment ID.
	 *
	 * @used-by \SLP_Power_REST_Handler::get_imports
	 *
	 * @return array
	 */
	public function get_active_list() {
		$this->set_uploads_meta();
		$data = array();
		$cron_list = $this->addon->get_all_crons_for_hook( self::hook );
		foreach ( $cron_list as $meta ) {
			if ( ! empty( $meta[ 'args' ] ) ) {
				$data[ $meta[ 'args' ][0] ][ 'id'   ] = $meta[ 'args' ][0];
				$data[ $meta[ 'args' ][0] ][ 'meta' ] = $this->get_attachment_details( $meta[ 'args' ][0] );
			}
		}

		return array( 'data' =>  $data );
	}

	/**
	 * Get a remote file and store it locally.
	 *
	 * @param $remote_file
	 */
	private function get_remote_file( $remote_file ) {
		if ( $this->slplus->Helper->webItemExists( $remote_file ) ) {
			$response = wp_remote_get( $remote_file, array( 'timeout' => 300 ) );

			// File opened without any issues.
			//
			if ( is_array( $response ) && isset( $response[ 'body' ] ) && !empty( $response[ 'body' ] ) ) {
				$ftp_file = $response[ 'body' ];
				$local_file = wp_tempnam();

				file_put_contents( $local_file, $ftp_file );

				$this->file_meta[ 'csvfile' ] = array(
					'name'     => 'slp_locations.csv',
					'type'     => 'text/csv',
					'tmp_name' => $local_file,
					'error'    => ( is_bool( $ftp_file ) ? '4' : '0' ),
					'size'     => strlen( $ftp_file ),
					'source'   => 'direct_url',
				);

				// Houston, we have a problem...
				//
			} else {
				$this->addon->create_cron_object();
				$this->addon->cron->messages->add_message( __( 'Could not fetch the remote file.', 'slp-power' ) );
			}
		}
	}

	/**
	 * Process the locations csv file.
	 *
	 * @used-by \SLP_Power_Cron::import_locations
	 *
	 * @param int $attachment_id
	 */
	public function import( $attachment_id ) {
		$this->file_meta = wp_get_attachment_metadata( $attachment_id , true );
		if ( ! $this->file_meta ) return;

		// Stop processing if processed flag is set.
		//
		if ( ! empty( $this->file_meta[ 'processed'] ) ) {
			$this->stop_processing( $attachment_id );
			return;
		}

		// schedule another one of me for 2 minutes from now
		$this->respawn( $attachment_id );

		// don't work now if the last records was processed within the last 10 seconds
		if ( ! empty( $this->file_meta[ 'last_run' ] ) && ( time() - $this->file_meta[ 'last_run' ] < 10 ) ) {
			return;
		}

		// process the file
		//
		if ( ! isset( $this->file_meta[ 'local_file' ] ) ) {
			$filename                      = get_attached_file( $attachment_id, false );
			$this->file_meta['local_file'] = $filename;
		}

		$this->output = 'json';
		$this->attachment_id = $attachment_id;
		$this->skip_geocoding = true;           // We will geocode AFTER the import.

		$this->messages->add_message( __( 'Location import started.' , 'slp-power' ) );

		$this->prepare_for_import();
		$this->open_file_and_start_importing();
	}

    /**
     * Is this a valid CSV File?
     *
     * @return bool
     */
    private function is_valid_csv_file() {

        // Is the file name set?
        //
        if ( !isset( $this->file_meta[ 'csvfile' ] ) ) {
            $message = __( 'CSV File Meta not set.', 'slp-power' );
            $this->messages->add_message( $message );
            $this->slplus->notifications->add_notice( 'error', $message );

            return false;
        }


        // Is the file name set?
        //
        if ( !isset( $this->file_meta[ 'csvfile' ][ 'name' ] ) || empty( $this->file_meta[ 'csvfile' ][ 'name' ] ) ) {
            $message = __( 'Import file name not set.', 'slp-power' );
            $this->messages->add_message( $message );
            $this->slplus->notifications->add_notice( 'error', $message );

            return false;
        }

        // Does the file have any content?
        //
        if ( $this->file_meta[ 'csvfile' ][ 'size' ] <= 0 ) {
            switch ( $this->file_meta[ 'csvfile' ][ 'error' ] ) {
                case UPLOAD_ERR_INI_SIZE:
                    $message = __( 'Import file exceeds the upload_max_filesize in php.ini.', 'slp-power' );
                    break;

                case UPLOAD_ERR_PARTIAL:
                    $message = __( 'Import file was only partially loaded.', 'slp-power' );
                    break;

                case UPLOAD_ERR_NO_FILE:
                    $message = __( 'Import file seems to have gone missing.', 'slp-power' );
                    break;

                default:
                    $message = __( 'Import file is empty.', 'slp-power' );
                    break;
            }
            $this->messages->add_message( $message );
            $this->slplus->notifications->add_notice( 'error', $message );

            return false;
        }

        // Is the file CSV?
        //
        $arr_file_type = wp_check_filetype( basename( $this->file_meta[ 'csvfile' ][ 'name' ] ), array( 'csv' => 'text/csv' ) );
        if ( $arr_file_type[ 'type' ] != 'text/csv' ) {
            $message =
                __( 'Uploaded file needs to be in CSV format.', 'slp-power' ) .
                sprintf( __( 'Type was %s.', 'slp-power' ), $arr_file_type[ 'type' ] );
            $this->messages->add_message( $message );
            $this->slplus->notifications->add_notice( 'error', $message );

            return false;
        }

        return true;
    }

    /**
     * Load basic CSV files directly into MYSQL with LOAD DATA.
     *
     * This is MUCH faster than CSV parsing but requires the CSV has no extended data
     * such as categories, etc.
     */
    private function load_directly_into_mysql() {

        if ( !$this->is_valid_csv_file() ) {
            return;
        }

        $new_file = $this->move_csv_to_slpdir();
        if ( empty( $new_file ) ) {
            return;
        }

        if ( !$this->open_csv_file( $new_file ) ) {
            return;
        }

        fclose( $this->filehandle );


        $core_fieldnames = array_filter( $this->fieldnames, array( $this, 'return_base_fields_only' ) );
        $field_list = join( ',', $core_fieldnames );

        global $wpdb;
        $table_name = $wpdb->prefix . "store_locator";

        $load_data_sql =
            sprintf(
                "LOAD DATA LOCAL INFILE '%s' INTO TABLE %s  " .
                "FIELDS TERMINATED BY ','   " .
                "ENCLOSED BY '\"'           " .
                "ESCAPED BY '\\\\'           " .
                "%s " .
                "( %s )"
                ,
                $new_file,
                $table_name,
                'IGNORE 1 LINES',
                $field_list
            );
        $this->slplus->db->query( $load_data_sql );

        // Now geocode them.
        //
        if ( !$this->skip_geocoding ) {
            $this->addon->recode_all_uncoded_locations();
        }
    }

	/**
	 * Map field name "aliases" to location data fields for import.
	 *
	 * @param  array $location_data
	 * @return array
	 */
	private function map_fields( $location_data ) {
		$field_map = array (
			'name' => 'sl_store',
		);
		foreach ( $field_map    as $alias => $field ) {
			if ( ! isset( $location_data[$field] ) || empty( $location_data[$field] ) ) {
				if ( isset( $location_data[$alias] ) && ! empty( $location_data[$alias] ) ) {
					$location_data[ $field ] = $location_data[ $alias ];
				}
				if ( isset( $location_data[ 'sl_' . $alias] ) && ! empty( $location_data[ 'sl_' . $alias] ) ) {
					$location_data[ $field ] = $location_data[ 'sl_' . $alias ];
				}
			}
		}
		return $location_data;
	}

	/**
     * Look to see if incoming Identifier data is already in the extended data set.
     *
     * @used-by \SLP_Power_Locations_Import::prepare_for_import  via slp_csv_locationdata filter
     *
     * @param mixed[] $location_data
     *
     * @return mixed[] $location_data
     */
    public function match_identifier_field_on_import( $location_data ) {
        if ( isset( $location_data[ 'identifier' ] ) && !empty( $location_data[ 'identifier' ] ) ) {

            // Fetch sl_id from provided identifier.
            //
            $location_se_data = $this->slplus->database->get_Record(
                array( 'select_slid_from_extended_data', 'where_identifier_matches', ),
                array( $location_data[ 'identifier' ], )
            );

            // If there the select returned a valid data record object.
            //
            if ( is_array( $location_se_data ) && isset( $location_se_data[ 'sl_id' ] ) && !empty ( $location_se_data[ 'sl_id' ] ) ) {
                $location_data[ 'sl_id' ] = $location_se_data[ 'sl_id' ];
            } else {
            	$this->mode_for_current_record = 'add';
            }
        }

        return $location_data;
    }

    /**
     * Move the CSV File to a local directory.
     *
     * @return string
     */
    private function move_csv_to_slpdir() {

        // Check WordPress has an uploads directory.
        //
        if ( !is_dir( SLPLUS_UPLOADDIR ) ) {
            echo "<div class='updated fade'>" .
                sprintf(
                    __( 'WordPress upload directory %s is missing, check directory permissions.', 'slp-power' ),
                    SLPLUS_UPLOADDIR
                ) .
                '</div>';

            return NULL;
        }

        // Make the SLP CSV Upload Directory \
        //
        $updir = SLPLUS_UPLOADDIR . 'csv';
        if ( !is_dir( $updir ) ) {
            mkdir( $updir, 0755 );
        }

        $new_file = $updir . '/' . $this->file_meta[ 'csvfile' ][ 'name' ];

        // Move File -
        // If csvfile source is set to csv_file_url assume an http or ftp_get
        // direct to disk,
        //
        // otherwise
        //
        // Assume HTTP POST (browser direct) use move_uploaded_file
        //
        if (
            isset( $this->file_meta[ 'csvfile' ][ 'source' ] ) &&
            ( $this->file_meta[ 'csvfile' ][ 'source' ] === 'direct_url' )
        ) {
            if ( !rename( $this->file_meta[ 'csvfile' ][ 'tmp_name' ], $new_file ) ) {
                echo $this->slplus->Helper->create_string_wp_setting_error_box(
                    __( 'Imported CSV file could not be renamed.', 'slp-power' ) .
                    sprintf( __( 'Possibly out of disk space while trying to rename to %s', 'slp-power' ), $new_file )
                );

                return '';
            }

        } else {
            if ( !move_uploaded_file( $this->file_meta[ 'csvfile' ][ 'tmp_name' ], $new_file ) ) {
                echo $this->slplus->Helper->create_string_wp_setting_error_box(
                    __( 'Uploaded CSV file could not be moved.', 'slp-power' ),
                    sprintf( __( 'Check folder permissions for %s', 'slp-power' ), $new_file )
                );

                return '';
            }
        }

        return $new_file;
    }

    /**
     * Override this to add special processing that skips the data processing of the file.
     *
     * @return bool
     */
    private function ok_to_process_file() {
	    require( SLPLUS_PLUGINDIR . 'include/module/location/SLP_Location_Manager.php' );
	    if ( $this->slplus->Location_Manager->has_max_locations( true ) ) {
	    	$this->messages->add_message( sprintf( __( 'You have reached your %s location limit.' , 'slp-power' ) , $this->slplus->Location_Manager->location_limit ) );
	    	return false;
	    }
	    return true;
    }

    /**
     * Open the CSV File and set the filehandle.
     *
     * @param  mixed   $filename
     *
     * @return bool
     */
    private function open_csv_file( $filename ) {

        // If file cannot be opened , stop.
        //
        if ( ( ( $this->filehandle = fopen( $filename, "c+" ) ) === FALSE ) || ! flock( $this->filehandle , LOCK_EX) ) {
	        $this->messages->add_message( sprintf( __( 'Could not open %s for import.', 'slp-power' ) ,  $filename ) );
            return false;
        }

        // Set line endings.
	    //
	    $this->adle_setting = ini_get( 'auto_detect_line_endings' );
	    ini_set( 'auto_detect_line_endings', true );

	    // Read the first line of the file.
	    //
	    $this->set_field_names();

	    // Cron iterative processor
	    //
	    if ( is_array( $this->file_meta ) && ! empty( $this->file_meta[ 'data_type' ] ) ) {

	    	// We have an offset to start with.
		    if ( ! empty( $this->file_meta[ 'offset' ] ) ) {
			    fseek( $this->filehandle , $this->file_meta[ 'offset' ] );
			    $this->messages->add_message( sprintf( __( 'Continuing import at position %d record %d.', 'slp-power' ) , $this->file_meta[ 'offset' ] , $this->file_meta[ 'record' ] ) );

			// No offset - fresh start
		    } else {
			    $this->file_meta[ 'record'      ] = 0;
			    foreach ( $this->update_codes as $update_code ) {
				    $this->file_meta[ $update_code ] = 0;
			    }
			    $this->messages->add_message( sprintf( __( 'Starting import of %s .', 'slp-power' ) , $filename ) );
			    $this->messages->add_message( __( 'Your CSV file header is set to import the following fields: ', 'slp-power' ) , true );
			    if ( count( $this->fieldnames ) > 256 ) {
				    $field_name_message = join( ', ', array_slice( $this->fieldnames , 0 , 256 ) ) . '...';
			    } else {
				    $field_name_message = join( ', ', $this->fieldnames );
			    }
			    $this->messages->add_message( $field_name_message , true );
		    }
	    }

        return true;
    }

    /**
     * Load the CSV file and start importing it into memory.
     */
    private function open_file_and_start_importing() {
        if ( empty( $this->file_meta[ 'local_file' ] ) || ! $this->open_csv_file( $this->file_meta[ 'local_file' ] ) ) {
            return;
        }

        $this->processing_report = array();
        $this->processing_report[] = ( sprintf( __( 'Starting CSV import - duplicates %s .', 'slp-power' ) , $this->addon->options['csv_duplicates_handling'] ) );

        // Reset the notification message to get a clean message stack.
        //
        $this->slplus->notifications->delete_all_notices();

        $this->maxcols = count( $this->fieldnames );

	    $update_messages = $this->filter_SetMessages();

        // Turn off notifications for OK addresses.
        //
        $this->slplus->currentLocation->geocodeSkipOKNotices = true;
        $this->slplus->currentLocation->validate_fields = true;

        // Loop through all records
        //
        if ( $this->ok_to_process_file() ) {

            $this->messages->add_message( sprintf( __( 'Loading locations from %s ', 'slp-power' ), $this->file_meta[ 'local_file' ] ), true );
            while ( ( $this->data = fgetcsv( $this->filehandle , 0 , ',' , '"' , '"' ) ) !== FALSE ) {
                $this->data = array_map( array( $this, 'strip_utf8_control_chars' ), $this->data );
	            $this->mode_for_current_record = $this->addon->options['csv_duplicates_handling'];  // Needed for Identifier special field processing.

	            $this->process_each_csv_line();

	            // TODO: update MUP
		        do_action( 'slp_csv_processing' );

                $this->file_meta[ 'record' ]++;
                $this->update_position_meta();

                if ( $this->slplus->Location_Manager->has_max_locations() ) {
                	$this->messages->add_message( __( 'Reached maximum location limit. ' , 'slp-power' ) );
                	break;
                }
            }
        }
        fclose( $this->filehandle );
	    ini_set( 'auto_detect_line_endings', $this->adle_setting );

	    $this->stop_processing( $this->attachment_id );

        $this->slplus->currentLocation->validate_fields = false;

        if ( $this->file_meta[ 'record' ] > 0 ) {
            $this->processing_report[] = sprintf( __( '%d data lines read from the CSV file.', 'slp-power' ), $this->file_meta[ 'record' ] );
        } else  {
	        $this->processing_report[] = __( 'Could not find any records to import.', 'slp-power' );
	        $this->processing_report[] = __( 'Make sure you imported a valid CSV format.', 'slp-power' );
	    }

	    // Processing Report
	    //
	    foreach ( $this->update_codes as $update_code ) {
        	if ( isset( $this->file_meta[ $update_code ] ) && isset( $update_messages[ $update_code ] ) ) {
		        $this->processing_report[] = sprintf( "%d %s", $this->file_meta[ $update_code ], $update_messages[ $update_code ] );
	        }
        }

        if (  count( $this->processing_report ) > 0 ) {
            foreach ( $this->processing_report as $message ) {

            	// TODO : check this since SLP 4.9 we are likely to only be in JSON mode
	            if ( $this->output === 'json' ) {
		            $this->messages->add_message( $message , true );

	            } else {
		            printf( '<div class="updated fade">%s</div>', $message );
	            }
            }
        }

	    $this->messages->add_message(  __( 'Finished CSV import.', 'slp-power' ) );

        do_action( 'slp_csv_processing_complete' );
    }

    /**
     * Things we do to prepare for an import.
     */
    public function prepare_for_import() {
        add_filter( 'slp_csv_locationdata', array( $this, 'add_sl_to_base_fieldnames' ), 8 );
        add_filter( 'slp_csv_locationdata', array( $this, 'strip_extra_spaces_from_csv_location_data' ) );

        // Only do Identifier based matching if contact fields is enabled.
	    //
	    if ( $this->slplus->SmartOptions->use_contact_fields->is_true ) {
		    add_filter( 'slp_csv_locationdata', array( $this, 'match_identifier_field_on_import' ) );
	    }

        add_filter( 'slp_csv_locationdata', array( SLP_Power_Category_Manager::get_instance() , 'create_categories_from_location_data' ), 30 );
        add_filter( 'slp_extend_get_SQL', array( $this, 'sql_match_identifier' ) );

        add_action( 'slp_location_added', array( $this, 'update_category_and_page_id' ) );

        do_action( 'slp_prepare_location_import' );

        if ( $this->slplus->is_CheckTrue( $this->addon->options[ 'csv_clear_messages_on_import' ] ) ) {
            $this->messages->clear_messages();
        }

        return $this->set_FileMeta();
    }

    /**
     * Process the lines of the CSV file.
     *
     * @uses \SLP_Power_Locations_Import::add_sl_to_base_fieldnames                     via FILTER: slp_csv_locationdata Priority 8
     * @uses \SLP_Power_Locations_Import::strip_extra_spaces_from_csv_location_data     via FILTER: slp_csv_locationdata
     * @uses \SLP_Power_Locations_Import::match_identifier_field_on_import              via FILTER: slp_csv_locationdata
     * @uses \SLP_Power_Category_Manager::create_categories_from_location_data          via FILTER: slp_csv_locationdata
     * @uses \SLP_Premier_Admin_Locations::strip_sl_from_territory_fields               via FILTER: slp_csv_locationdata
     */
    public function process_each_csv_line() {
	    $num = count( $this->data );
	    if ( $num > $this->maxcols ) {
		    $this->report_malformed_csv_record( $num );
		    return;
	    }

	    // Set the locationData array with field names.
	    //
	    $locationData = array();
	    $all_empty = true;
	    for ( $fldno = 0; $fldno < $num; $fldno ++ ) {
		    $locationData[ $this->fieldnames[ $fldno ] ] = $this->data[ $fldno ];
		    if ( $all_empty && ( $this->data[ $fldno ] !== '' ) ) $all_empty = false;
	    }
	    if ( $all_empty ) {
		    $this->file_meta[ 'malformed' ]++;
	    	return;
	    }
	    $locationData = $this->map_fields( $locationData );

	    /**
	     * FILTER: slp_csv_locationdata
	     * Change location data before adding to database after read from CSV file.
	     *
	     * @params  array   $locationData       key = update status , value = text
	     *
	     * @return  array
	     */
	    $locationData = apply_filters( 'slp_csv_locationdata', $locationData );

	    if ( ! isset( $locationData['sl_latitude'] ) ) {
		    $locationData['sl_latitude'] = '';
	    }
	    if ( ! isset( $locationData['sl_longitude'] ) ) {
		    $locationData['sl_longitude'] = '';
	    }

	    // Reset data changed to true if we are in add mode. (patch for 2 identical records in a row)
        if ( $this->mode_for_current_record === 'add' ) {
            $this->slplus->currentLocation->dataChanged = true;
        }

	    // Go add the CSV Data to the locations table.
	    //
	    $resultOfAdd = $this->slplus->currentLocation->add_to_database(
		    $locationData,
		    $this->mode_for_current_record,
		    $this->skip_geocoding ||
		    (
			    $this->slplus->currentLocation->is_valid_lat( $locationData['sl_latitude'] ) &&
			    $this->slplus->currentLocation->is_valid_lng( $locationData['sl_longitude'] )
		    )
	    );

	    if ( $resultOfAdd == 'added' ) {
	    	$this->slplus->Location_Manager->increment_location_count();
	    }

        // Add the results of this location to the detailed message stack.
        //
	    $category_message = ! empty( $locationData['category'] ) ? ' [' . $locationData['category'] . ']' : '';

	    if ( ( $resultOfAdd !== 'added' ) && ( $resultOfAdd !== 'updated' ) && ( $resultOfAdd !== 'not_updated' ) ) {
		    $this->messages->add_message( "{$this->slplus->currentLocation->store}  {$resultOfAdd} {$category_message}" , true );
	    }

        // FILTER: slp_csv_locationdata_added
        // Post-location import processing.
        //
        // TODO: this should be do_action   change EXP, PREMIER, SME to add_action.
        //
        apply_filters( 'slp_csv_locationdata_added', $locationData, $resultOfAdd );

        // Update processing counts.
        //
        $this->file_meta[ $resultOfAdd ]++;
    }

    /**
     * Process a CSV File.
     */
    private function process_File() {
        if ( is_null( $this->file_meta ) ) {
            $this->file_meta = $_FILES;
        }

        if ( !$this->is_valid_csv_file() ) {
            return;
        }

        // Move file to temp directory.
        //
        $this->file_meta[ 'local_file' ] = $this->move_csv_to_slpdir();
        if ( empty( $this->file_meta[ 'local_file' ] ) ) {
            $this->messages->add_message( __( 'Local copy of CSV file could not be created.', 'slp-power' ) );

            return;
        }

        // Schedule Cron
        //
        $this->addon->create_cron_object();
        if ( wp_schedule_single_event( time(), 'cron_csv_import', array( 'process_csv', $this->file_meta ) ) === false ) {
            $this->messages->add_message(
                sprintf( __( 'Could not schedule the event to load the %s into the database.', 'slp-power' ), $this->file_meta[ 'csvfile' ][ 'name' ] )
            );

            return;
        }

        $this->messages->add_message( __( 'Starting the process of loading of the CSV file into the database.', 'slp-power' ) );

        // Start import process
        //
	    $this->addon->csvImporter->messages->add_message(__( 'CSV Import Process File.', 'slp-power' ) );
        $this->open_file_and_start_importing();
    }

    /**
     * Report a malformed CSV record.
     *
     * @param  int $field_count
     */
    private function report_malformed_csv_record( $field_count ) {
        $this->file_meta[ 'malformed' ]++;
        if ( !$this->reported_too_many_fields ) {
            $this->processing_report[] =
                __( 'The first line of your CSV file defines fields that do not match the data.', 'slp-power' ) . '<br/>' .
                sprintf( __( 'At least one line had %d fields while the header defined %d.', 'slp-power' ), $field_count, $this->maxcols ) . '<br/>' .
                __( 'Your header specified these fields: ', 'slp-power' ) . '<br/>' .
                join( ',', $this->fieldnames );
            $this->reported_too_many_fields = true;
        }
    }

	/**
	 * Respawn a single me, if I am not already in the spawning pool.
	 *
	 * CRON based import.
	 *
	 * @see https://developer.wordpress.org/reference/functions/wp_schedule_single_event/
	 *
	 * @param int $id
	 *
	 * @return false|int
	 */
	private function respawn( $id ) {
		$next_time = false;
		if ( ! wp_next_scheduled( self::hook , array( $id ) ) ) {
			$run_at = time();
			if ( wp_schedule_single_event( $run_at , self::hook , array( $id )) !== false ) {
				$next_time = $run_at;
			}
		}
		return ( $next_time );
	}

    /**
     * Only use the base table fields.
     *
     * @used-by \SLP_Power_Locations_Import::load_directly_into_mysql
     *
     * @param $var
     *
     * @return bool
     */
    public function return_base_fields_only( $var ) {
        $var = preg_replace( '/^sl_/', '', $var );

        return ( array_search( $var, $this->slplus->currentLocation->dbFields, true ) !== false );
    }

    /**
     * Set the field names array for the fields being processed.
     *
     */
    private function set_field_names() {

        // Try reading the first line of the file.
        //
        if ( ( $header_columns = fgetcsv( $this->filehandle , 0 , ',' , '"' , '"' ) ) !== FALSE ) {
            foreach ( $header_columns as $field_name ) {
                $this->fieldnames[] = sanitize_key( $field_name );
            }
	        $this->file_meta[ 'fields' ] = count( $this->fieldnames );

            if ( $this->file_meta[ 'fields' ] > 256 ) {
		        $this->messages->add_message( sprintf( __( 'Your are importing %s fields.  That seems wrong. ', 'slp-power' ) , $this->file_meta[ 'fields' ] ), true );
	        }

            // Could not read first line of file.
            //
        } else {
            $this->messages->add_message(
                __( 'Could not read CSV file header. ', 'slp-power' ) .
                __( 'Is the file empty? ', 'slp-power' ),
                true
            );
        }
    }

    /**
     * Set file meta for the import.
     *
     * Use the standard browser file upload objects $_FILES if set.
     *
     * If not set check for a remote file URL and use that.
     *
     */
    private function set_FileMeta() {
        if ( ! is_null( $this->file_meta ) ) {
            return NULL;
        }

        // Browser File Upload
        //
        if ( isset( $_FILES ) && ( !empty( $_FILES[ 'csvfile' ][ 'name' ] ) ) ) {
            $this->file_meta = $_FILES;

            return 'immediate';
        }

        // Remote File Set For Cron
        //
        if ( defined( 'DOING_CRON' ) ) {
            if ( empty( $this->addon->options[ 'csv_file_url' ] ) ) {
                $this->addon->cron->messages->add_message( __( 'File url is blank.', 'slp-power' ) );

                return NULL;
            }
            $remote_file = $this->addon->options[ 'csv_file_url' ];
            /** @noinspection PhpIncludeInspection */
            include_once( ABSPATH . 'wp-admin/includes/file.php' );

            // Remote File Direct Load
            //
        } else {
            if ( !isset( $_REQUEST[ 'slp-power' ] ) || !isset( $_REQUEST[ 'slp-power' ][ 'csv_file_url' ] ) || empty( $_REQUEST[ 'slp-power' ][ 'csv_file_url' ] ) ) {
                $this->messages->add_message( __( 'File url is blank.', 'slp-power' ) );

                return NULL;
            }
            $remote_file = $_REQUEST[ 'slp-power' ][ 'csv_file_url' ];
        }

        $this->get_remote_file( $remote_file );

        return NULL;
    }

	/**
	 * Set uploads meta for upload directory parsing.
	 */
	private function set_uploads_meta() {
		if ( isset( $this->uploads ) ) return;
		$this->uploads = wp_get_upload_dir();
	}

    /**
     * Add the identifier filter to the SQL where clause.
     *
     * @param   string $sql_slug The SQL command slug
     *
     * @return  string              The SQL string snippet
     */
    public function sql_match_identifier( $sql_slug ) {
        if ( $sql_slug === 'where_identifier_matches' ) {
            return $this->slplus->database->add_where_clause( "identifier='%s'" );
        } else {
            return $sql_slug;
        }
    }

	/**
	 * Start a detached import.
	 *
	 * @param int|object $attachment Attachment ID or object.
	 *
	 * @return false|int  int of the time import will run if ok , false if not
	 **/
	public function start_detached_import( $attachment ) {
		return $this->respawn( $attachment->ID );
	}

    /**
     * Start the CSV import of locations.
     */
    public function start_import() {
        $this->prepare_for_import();
        $this->process_File();
    }

	/**
	 * Stop file processing, update meta.
	 *
	 * @param int $id
	 */
	public function stop_processing ( $id ) {
		wp_clear_scheduled_hook( self::hook , array( $id ) );
		if ( empty( $id ) ) return;

		if ( is_array( $this->file_meta ) ) {
			$this->file_meta[ 'processed' ]         = true;
			$this->file_meta[ 'next_process_time' ] = false;
		}

		wp_update_attachment_metadata( $id , $this->file_meta );

		// Start Geocoding Process
		$geocoder = new SLP_Power_Locations_Geocode();
		$geocoder->start();
	}

	/**
     * Strip extra spaces from location data.
     *
     * @param $location_data
     *
     * @return string[] $location_data
     */
    public function strip_extra_spaces_from_csv_location_data( $location_data ) {
        return array_map( 'trim', $location_data );
    }

    /**
     * Strip UTF-8 control characters
     * @param string $string
     *
     * @return string
     */
    private function strip_utf8_control_chars( $string ) {
        return preg_replace( '/\p{Cc}/u', '', $string );
    }

    /**
     * Attach our category data to the update string.
     *
     * Put it in the sl_option_value field as a serialized string.
     *
     * Assumes currentLocation is set.
     */
    public function update_category_and_page_id() {
	    SLP_Power_Category_Manager::get_instance()->update_location_category();
    }

	/**
	 * Update the position meta data.
	 */
    private function update_position_meta() {
	    if ( empty( $this->attachment_id ) ) {
		    return;
	    }

	    $this->file_meta[ 'offset' ] = ftell( $this->filehandle );
	    $this->file_meta[ 'last_run' ] = time();

	    wp_update_attachment_metadata( $this->attachment_id , $this->file_meta );
	}
}
