<?php
defined( 'ABSPATH' ) || exit;
/**
 * SLP Text Modifier
 */
class SLP_Premier_Admin_General_Text extends SLPlus_BaseClass_Object {

	/**
	 * SLP Text Manager Hooks
	 */
	protected function initialize() {
		add_filter( 'slp_get_text_string'    , array( $this , 'augment_text_string' ) , 10 , 2 );
	}

	/**
	 * Replace the SLP Text Manager Strings at startup.
	 *
	 * @uses \SLP_Premier_Admin_General_Text::description
	 * @uses \SLP_Premier_Admin_General_Text::label
	 * @uses \SLP_Premier_Admin_General_Text::settings_group
	 * @uses \SLP_Premier_Admin_General_Text::settings_group_header
	 * @uses \SLP_Premier_Admin_General_Text::settings_section
	 *
	 * @param string $text the original text
	 * @param string $slug the slug being requested
	 *
	 * @return string            the new SLP text manager strings
	 */
	public function augment_text_string($text, $slug) {
		if ( ! is_array( $slug ) ) {
			$slug = array( 'general' , $slug );
		}

		if ( method_exists( $this , $slug[0] ) ) {
			$text = $this->{$slug[0]}( $slug[1] , $text );
		}

		return $text;
	}

	/**
	 * Settings Group Headers = Settings Groups
	 *
	 * @param string $slug
	 * @param string $text
	 *
	 * @return string
	 */
	private function settings_group_header( $slug , $text ) {
		return $this->settings_group( $slug , $text);
	}

	/**
	 * Descriptions
	 *
	 * @param string $slug
	 * @param string $text
	 *
	 * @return string
	 */
	private function description( $slug , $text ) {
		switch ( $slug ) {
			case 'allow_limit_in_url'             : return __('Limit on the number of locations to be returned via the limit parameter.'           , 'slp-premier' ).'<br/><br/>?limit=3';
			case 'allow_location_in_url'          : return __('Select a specific location to activate via the location parameter. '                , 'slp-premier' ).'<br/><br/>?location=542';
			case 'allow_tag_in_url'               : return __('Filter locations to those with the specified tag via the tag parameter. '           , 'slp-premier' ) .'<br/><br/>?only_with_tag=red';
			case 'block_ip_limit'                 : return __('What is the maximum number of requests a single IP address can make over the specified time span before they are blocked. ', 'slp-premier' );
			case 'block_ip_period'                : return __('The time span over which to apply the limit on maximum number of locator requests. ', 'slp-premier' ) .
													       __('If you change this from "never" to any other value it can generate a substantial volume of data. ', 'slp-premier' ).
													       __('Make sure your WordPress database can handle the logging of every page load IP address for over this period of time. ', 'slp-premier' );
			case 'block_ip_release_after'         : return __('How long is the IP address blocked for before making another request? '             , 'slp-premier' );
			case 'ip_whitelist'                   : return __('IP addresses on this list are whitelisted for locator access. '                     , 'slp-premier' ) .
													       __('Enter one address per line. '                                                       , 'slp-premier' ) .
													       __('CIDR "slash-dot" notation is allowed (001.002.003.000/24). '                        , 'slp-premier' );
			case 'block_ip_limit'                 : return __('What is the maximum number of requests a single IP address can make over the specified time span before they are blocked. ', 'slp-premier' );
			case 'schedule_for_geocoding'         : return __('When should uncoded locations be automatically geocoded? '                          , 'slp-premier' ) .
													       __('Will run on the hour and repeat every hour, 12 hour or 24 hours. '                  , 'slp-premier' ) .
			                                               $this->set_schedule_description( $slug );
			case 'schedule_for_initial_distance'  : return __('When should locations have their initial distance automatically calculated? '       , 'slp-premier' ) .
			                                               __('Will run on the hour and repeat every hour, 12 hour or 24 hours. '                  , 'slp-premier' ) .
			                                               $this->set_schedule_description( $slug );
			case 'use_territory_bounds'           : return __( 'Activate the territories module.' , 'slp-premier' );
		}

		return $text;
	}

	/**
	 * Labels
	 *
	 * @param string $slug
	 * @param string $text
	 *
	 * @return string
	 */
	private function label( $slug , $text ) {
		switch ( $slug ) {
			case 'allow_limit_in_url'           : return __('Location Limit'           , 'slp-premier' );
			case 'allow_location_in_url'        : return __('Location Selection'       , 'slp-premier' );
			case 'allow_tag_in_url'             : return __('Filter By Tag'            , 'slp-premier' );
			case 'block_ip_limit'               : return __('Block Requests Limit'     , 'slp-premier' );
			case 'block_ip_period'              : return __('Block Requests Time Span' , 'slp-premier' );
			case 'block_ip_release_after'       : return __('Release IP After'         , 'slp-premier' );
			case 'ip_whitelist'                 : return __('IP Whitelist'             , 'slp-premier' );
			case 'schedule_for_geocoding'       : return __('Geocoding'                , 'slp-premier' );
			case 'block_ip_limit'               : return __('Block Requests Limit'     , 'slp-premier' );
			case 'schedule_for_initial_distance': return __('Initial Distance'         , 'slp-premier' );
			case 'use_territory_bounds'         : return __( 'Use Territory Bounds'    , 'slp-premier' );
		}
		return $text;
	}

	/**
	 * Settings Groups
	 *
	 * @param string $slug
	 * @param string $text
	 *
	 * @return string
	 */
	private function settings_group( $slug , $text ) {
		switch ( $slug ) {
			case 'tasks': return __( 'Tasks' , 'slp-premier' );
		}
		return $text;
	}

	/**
	 * Settings Sections
	 *
	 * @param string $slug
	 * @param string $text
	 *
	 * @return string
	 */
	private function settings_section( $slug , $text ) {
		switch ( $slug ) {
			case 'schedule': return __( 'Schedule' , 'slp-premier' );
		}
		return $text;
	}


	/**
	 * Set the schedule info for the help text on scheduled options.
	 *
	 * @param string $slug
	 *
	 * @return string
	 */
	public function set_schedule_description( $slug ) {
		$schedule_manager = new SLP_Premier_Schedule_Manager();
		$extended_text =
			empty( $schedule_manager->$slug->next_event ) ?
				__( 'This event is not scheduled.' , 'slp-premier' ) :
				sprintf (
					__('Next Event happens in %s on %s' , 'slp-premier') ,
					$schedule_manager->$slug->next_event_time_to_text,
					$schedule_manager->$slug->next_event_text
				);
		return '<span class="scheduled_time">' .  $extended_text . '</span>';
	}
}
