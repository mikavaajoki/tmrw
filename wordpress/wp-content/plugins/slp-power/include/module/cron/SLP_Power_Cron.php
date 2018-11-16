<?php
defined( 'ABSPATH' ) || exit;

/**
 * The cron job processing class.
 *
 * @property        SLPPower            $addon
 * @property        SLPPower_Messages   $messages           The message stack for the current import operation.
 */
class SLP_Power_Cron extends SLPlus_BaseClass_Object {
    private $addon;
    private $messages;

	/**
	 * Connnect the Power add on pointer and messages as needed.
	 */
    private function connect_addon() {
	    $this->addon = $this->slplus->addon( 'Power' );
	    $this->addon->create_object_schedule_messages();
	    $this->messages = $this->addon->messages['schedule'];
    }

    /**
     * Import a location CSV file.
     *
     * @param string $mode  What mode to run in : see SLP_Power_Locations_Import execute_action
     */
    private function import_csv( $mode ) {
        $this->messages->add_message( __( 'Import CSV started.' , 'slp-power' ) );
        $this->addon->create_CSVLocationImporter();
        $this->addon->csvImporter->prepare_for_import();
        $this->addon->csvImporter->execute_action( $mode );
        if ( count($this->addon->csvImporter->processing_report) > 0 ) {
            foreach ( $this->addon->csvImporter->processing_report as $message ) {
                $this->add_message( $message );
            }
        }
        $this->messages->add_message(__( 'Import CSV finished.', 'slp-power' )  );
        $this->addon->slplus->notifications->delete_all_notices();
    }

	/**
	 * Add our hooks that spin off bot babies.
	 *
	 * @uses \SLP_Power_Cron::import_locations  for Cron hook slp_import_locations
	 */
    public function make_bot_babies() {

	    // Geocode Locations Bot...
	    add_action( SLP_Power_Locations_Geocode::cron_hook, array( $this , 'geocode_locations') );


	    // Import Locations Bot...
	    add_action( SLP_Power_Locations_Import::hook, array( $this , 'import_locations') );

	    // Old school Power import (scheduled import)
	    add_action( 'cron_csv_import', array( $this , 'old_import_locations' ), 10, 2 );

    }

	/**
	 * Start the location geocoding.
	 *
	 * @param int $max_id
	 */
	public function geocode_locations( $max_id ) {
		$location_geocoder = new SLP_Power_Locations_Geocode();
		$location_geocoder->geocode( $max_id );
	}

	/**
	 * Start the location import.
	 *
	 * @uses \SLP_Power_Locations_Import::import
	 *
	 * @used-by \SLP_Power_Cron::make_bot_babies
	 *
	 * @param int $attachment_id
	 */
    public function import_locations( $attachment_id ) {
	    $location_importer = SLP_Power_Locations_Import::get_instance();
	    $location_importer->import( $attachment_id );
    }

	/**
	 * Old import locations on a schedule.
	 *
	 * @used-by \SLP_Power_Cron::make_bot_babies        manages these cron calls:
	 * @used-by \CSVImportLocations::process_File       from MUP via Cron 'cron_csv_import' hook.
	 * @used-by \SLP_Power_Cron::schedule_one_time_job  via Cron 'cron_csv_import'
	 * @used-by \SLP_Power_Cron::schedule_recurring_job via Cron 'cron_csv_import'
	 *
	 * @param string $action    'import_csv' or 'process_csv'
	 * @param array  $params    the file_meta
	 */
    public function old_import_locations( $action , $params ) {
    	if ( empty( $action ) ) return;

		$this->connect_addon();

	    $this->messages->add_message( sprintf( __( 'Cron action %s initialized.', 'slp-power' ) , $action ) );

	    switch ( $action ) {
		    case 'import_csv':
			    $this->import_csv( 'immediate' );
			    break;

		    case 'process_csv':
			    $this->import_csv( 'recurring' );  // TODO - check this, this may need to be different.
			    break;

		    default:
			    $this->messages->add_message( sprintf(__('Action %s is unsupported','slp-power') , $action ) );
			    break;
	    }
    }

    /**
     * Schedule a one-time import.
     *
     * @used-by \SLP_Power_Locations_Import::execute_action
     *
     * @param array $file_meta
     */
	public function schedule_one_time_job( $file_meta ) {
	    $this->connect_addon();

        if ( empty( $this->addon->options['cron_import_timestamp'] ) ) {
            $this->addon->options['cron_import_timestamp'] = 'now';
            $timestamp = time();
        } else {
            $timestamp = strtotime( $this->addon->options['cron_import_timestamp'] );
        }

        $scheduled_without_problems = wp_schedule_single_event( $timestamp, 'cron_csv_import', array( 'import_csv', $file_meta ) );

        if ( $scheduled_without_problems !== false ) {
            $this->messages->add_message( sprintf( __( 'Scheduled a one-time import at %s (%s).', 'slp-power' ) , $this->addon->options['cron_import_timestamp'],  $timestamp ) );
        } else {
            $this->messages->add_message( sprintf( __( 'Could not a one-time import at %s (%s).', 'slp-power' ) , $this->addon->options['cron_import_timestamp'] , $timestamp ) );
        }
    }

    /**
     * Schedule a recurring cron job.
     *
     * @used-by \SLP_Power_Locations_Import::execute_action
     *
     * @param array $file_meta
     */
    public function schedule_recurring_job( $file_meta ) {
	    $this->connect_addon();

        if ( empty( $this->addon->options['cron_import_timestamp'] ) ) {
            $this->addon->options['cron_import_timestamp'] = 'now';
            $timestamp = time();
        } else {
            $timestamp = strtotime( $this->addon->options['cron_import_timestamp'] );
        }

        $scheduled_without_problems = wp_schedule_event( $timestamp, $this->addon->options['cron_import_recurrence'], 'cron_csv_import', array( 'import_csv', $file_meta ) );

        if ( $scheduled_without_problems !== false ) {
            $this->messages->add_message( sprintf( __( 'Scheduled a recurring %s import at %s (%s).', 'slp-power' ) , $this->addon->options['cron_import_recurrence'] , $this->addon->options['cron_import_timestamp'] ,$timestamp ) );
        } else {
            $this->messages->add_message( sprintf( __( 'Could not schedule a recurring %s import at %s (%s).', 'slp-power' ) , $this->addon->options['cron_import_recurrence'] , $this->addon->options['cron_import_timestamp'] ,$timestamp ) );
        }
    }
}
