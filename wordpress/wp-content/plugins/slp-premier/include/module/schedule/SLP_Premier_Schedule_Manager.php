<?php
defined( 'ABSPATH' ) || exit;
/**
 * The schedule manager.
 *
 * @link https://developer.wordpress.org/plugins/cron/
 *
 * @property    SLP_Message_Manager       $schedule_messages;
 * @property    SLP_Premier_Schedule_Item $schedule_for_geocoding
 * @property    SLP_Premier_Schedule_Item $schedule_for_initial_distance
 */
class SLP_Premier_Schedule_Manager extends SLPlus_BaseClass_Object {
	public $schedule_messages;
	public $schedule_for_geocoding;
	public $schedule_for_initial_distance;

	/**
	 * Things we do at the start.
	 */
	protected function initialize() {
		$this->create_object_schedule_messages();

		$this->schedule_for_geocoding = new SLP_Premier_Schedule_Item( array(
			'slug'     => 'slp_geocoding',
			'interval' => $this->slplus->SmartOptions->schedule_for_geocoding->value,
			'callback' => array( $this , 'geocode_uncoded' )
			) );
		$this->schedule_for_initial_distance = new SLP_Premier_Schedule_Item( array(
			'slug'     => 'slp_initial_distance',
			'interval' => $this->slplus->SmartOptions->schedule_for_initial_distance->value,
			'callback' => array( $this , 'calculate_initial_distance' )
		) );
	}

	/**
	 * Create the hook to be run during cron firing this thing off.
	 *
	 * @param string $slug
	 */
	public function create_hook( $slug ) {
		add_action( $this->{$slug}->slug , $this->{$slug}->callback );
	}

	/**
	 * Attach a message stack to this import object.
	 */
	public function create_object_schedule_messages() {
		if ( ! isset( $this->schedule_messages ) ) {
			$this->schedule_messages = SLP_Message_Manager::get_instance( array( 'slug' => 'schedule' ) );
		}
	}

	/**
	 * Put me on the schedule.
	 *
	 * @param string $slug
	 * @param string $interval
	 */
	public function put_on_schedule( $slug , $interval ) {
		$hook = $this->$slug->slug;
		$next = $this->$slug->next_event;

		// Clear out any next events when setting new schedule.
		while ( ! empty( $next ) ) {
			wp_unschedule_event( $next , $hook );
			$this->$slug->next_event = null;
			$next = $this->$slug->next_event;
		}

		// Anything but never, put it on the schedule top of the hour.
		//
		if ( $interval !== 'never' ) {
			$current_time = time();
			if ( $interval === 'now' ) {
				wp_schedule_single_event( $current_time + 10 , $hook );

			} else {
				$next_hour = $current_time - ( $current_time % 3600 ) + 3600;
				wp_schedule_event( $next_hour, $interval, $hook );
			}
			$this->$slug->next_event = null;
		}
	}

	/**
	 * Invoke the calculate initial distance process.
	 */
	public function calculate_initial_distance() {
		if ( $this->slplus->SmartOptions->schedule_for_initial_distance->value === 'now' ) {
			$this->slplus->options_nojs[ 'schedule_for_initial_distance' ] = 'never';
			$this->slplus->WPOption_Manager->update_wp_option( 'nojs' );
		}

		require_once( SLPLUS_PLUGINDIR . 'include/module/location/SLP_Location_Manager.php' );
		$this->slplus->Location_Manager->recalculate_initial_distance_where_zero( $this->schedule_messages );
	}

	/**
	 * Invoke the geocode uncoded process.
	 */
	public function geocode_uncoded() {
		if ( $this->slplus->SmartOptions->schedule_for_geocoding->value === 'now' ) {
			$this->slplus->options_nojs[ 'schedule_for_geocoding' ] = 'never';
			$this->slplus->WPOption_Manager->update_wp_option( 'nojs' );
		}
		if ( $this->slplus->AddOns->get(  'slp-power'  , 'active' ) ) {

			/**
			 * @var SLPPower $power
			 */
			$power = $this->slplus->AddOns->instances[ 'slp-power' ];
			if ( version_compare( $power->version, '4.7' , '<' ) ) {
				error_log( __( 'Power needs to be version 4.7' , 'slp-premier' ) );
			} else {
				$power->recode_all_uncoded_locations( $this->schedule_messages );
			}

		} else {
			error_log( __( 'Scheduled geocoding requires the Power add on be active.' , 'slp-premier' ) );
		}
	}
}