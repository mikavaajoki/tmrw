<?php
defined( 'ABSPATH' ) || exit;

class SLP_Power_Locations_Geocode  extends SLPlus_BaseClass_Object {
	const cron_hook = 'slp_geocode_locations';

	/**
	 * Geocode up to the max ID.
	 *
	 * @param int $max_id       highest location ID that was not geocoded
	 */
	public function geocode( $max_id ) {

		// Stop processing if we are at/above the max ID
		//
		$count = $this->get_count_of_uncoded_locations();
		$last  = $this->slplus->SmartOptions->last_geocoded_location->value;
		if (
			( $this->get_count_of_uncoded_locations() <= 0 ) ||
			( $this->slplus->SmartOptions->last_geocoded_location->value >= $max_id )
		){
			$this->stop_processing( $max_id );

		// schedule another one of me for 2 minutes from now
		} else {
			$this->respawn( $max_id );
		}

		// Recode all uncoded
		$this->slplus->addon( 'Power' )->recode_all_uncoded_locations( null , $max_id );
	}

	/**
	 * Return a list of active location imports by attachment ID.
	 *
	 * @used-by \SLP_Power_REST_Handler::get_geocoding
	 *
	 * @return array
	 */
	public function get_active_list( ) {
		$data = array(
			'current_location' => $this->slplus->SmartOptions->last_geocoded_location->value,
			'current_uncoded' => $this->get_count_of_uncoded_locations(),
			'jobs' => array()
		);
		$cron_list = $this->slplus->addon( 'Power' )->get_all_crons_for_hook( self::cron_hook );
		foreach ( $cron_list as $meta ) {
			if ( ! empty( $meta[ 'args' ] ) ) {
				$data['jobs'][] = array(
					'max'               =>  $meta[ 'args' ][0] ,
					'start_uncoded'     => 	get_site_transient( self::cron_hook . '_' .  $meta[ 'args' ][0] )
				);
			}
		}

		return array( 'data' =>  $data );
	}

	/**
	 * Get max uncoded slid
	 *
	 * @return int
	 */
	public function get_max_uncoded_id() {
		$power_addon = $this->slplus->addon( 'Power' );
		add_filter( 'slp_location_where' , array( $power_addon , 'set_where_not_valid_lat_long' ) );
		return $this->slplus->database->get_Value( array( 'select_max_slid' , 'where_default' ) );
	}

	/**
	 * Get total count of uncoded IDs.
	 *
	 * @return int
	 */
	public function get_count_of_uncoded_locations() {
		$power_addon = $this->slplus->addon( 'Power' );
		add_filter( 'slp_location_where' , array( $power_addon , 'set_where_not_valid_lat_long' ) );
		return $this->slplus->database->get_Value( array( 'selectall_count' , 'where_default' ) );
	}

	/**
	 * Reset last geocoded location.
	 */
	private function reset_last_geocoded_location() {
		$this->slplus->SmartOptions->set( 'last_geocoded_location' ,  0 );
		SLP_WPOption_Manager::get_instance()->update_wp_option( 'nojs' );
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
		if ( ! wp_next_scheduled( self::cron_hook , array( $id ) ) ) {
			$run_at = time();
			if ( wp_schedule_single_event( $run_at , self::cron_hook , array( $id )) !== false ) {
				$next_time = $run_at;
			}
		}
		return ( $next_time );
	}

	/**
	 * Start the Cron-based geocoding process.
	 */
	public function start() {
		$max_id = $this->get_max_uncoded_id();
		if ( empty( $max_id ) ) return;

		set_site_transient( self::cron_hook . '_' . $max_id , $this->get_count_of_uncoded_locations() , DAY_IN_SECONDS );
		$this->reset_last_geocoded_location();

		$this->respawn( $max_id );
	}

	/**
	 * Stop file processing, update meta.
	 *
	 * @param int $id
	 */
	private function stop_processing ( $id ) {
		wp_clear_scheduled_hook( self::cron_hook , array( $id ) );
	}
}