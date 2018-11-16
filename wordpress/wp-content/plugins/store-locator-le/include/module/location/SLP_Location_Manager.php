<?php
defined( 'ABSPATH' ) or exit;
if ( ! class_exists( 'SLP_Location_Manager' ) ):

	/**
	 * Class SLP_Location_Manager
	 *
	 * @property-read    int     location_count    Current location count, init with get_location_count.
	 * @property         int     location_limit    Limit on locations allowed to be processed.
	 */
	class SLP_Location_Manager extends SLPlus_BaseClass_Object {
		private $location_count;
		public  $location_limit;
		public $location_limit_int;

		/**
		 * Things we do at the start.
		 */
		protected function initialize() {
			$this->location_limit = apply_filters( 'slp_location_limit' , defined( 'PHP_INT_MAX' ) ? PHP_INT_MAX : 2147483647 );
			$this->location_limit = (int) $this->location_limit;
		}

		/**
		 * Add status messages to the admin UI.
		 *
		 * @param string $response_code
		 */
		public function create_notification( $response_code ) {
			switch ( $response_code ) {
				case 'added':
				case 'updated':
					$this->slplus->notifications->add_notice( 'info',
						stripslashes_deep( $_POST['store'] ) . ' ' .
						$this->slplus->Text->get_text_with_variables_replaced( 'successfully_completed', $response_code )
					);
					break;

				case 'invalid_form':
					$this->slplus->notifications->add_notice( 'info',
						$this->slplus->Text->get_text_string( array( 'admin' , 'location_not_added' ) ) .
						$this->slplus->Text->get_text_string( array( 'admin' , 'location_form_incorrect' ) )

					);
					break;

				case 'skipped':
					$this->slplus->notifications->add_notice( 'info',
						$this->slplus->Text->get_text_string( array( 'admin' , 'location_skipped' ) )
					);
					break;

				case 'not_updated':
					$this->slplus->notifications->add_notice( 'info',
						$this->slplus->Text->get_text_string( array( 'admin' , 'location_not_updated' ) )
					);
					break;

				default:
					$this->slplus->notifications->add_notice( 'info',
						$this->slplus->Text->get_text_string( array( 'admin' , 'location_not_added' ) ) . ' ' .
						$this->slplus->Text->get_text_with_variables_replaced( 'error_code', $response_code )
					);
			}
		}

		/**
		 * Decrement the location count.
		 */
		public function decrement_location_count() {
			$this->get_location_count();
			$this->location_count--;
		}

		/**
		 * Get the current location count.
		 *
		 * @param boolean $force
		 *
		 * @return array
		 */
		public function get_location_count( $force = false ) {
			if ( $force || ! isset( $this->location_count ) ) {
				$the_count = $this->slplus->database->get_Value( array( 'selectall_count', 'where_default' ) );
				$this->location_count = is_wp_error( $the_count ) ? 0 : $the_count;
			}
			return $this->location_count;
		}

		/**
		 * Check if we have hit our max locations limit.
		 *
		 * @param bool $force force a recount if true
		 * @return bool
		 */
		public function has_max_locations( $force =false ) {
			return ( $this->get_location_count( $force ) >= $this->location_limit );
		}

		/**
		 * Increment the location count.
		 */
		public function increment_location_count() {
			$this->get_location_count();
			$this->location_count++;
		}

		/**
		 * Recalculate the initial distance for all locations.
		 *
		 * @param    SLP_Message_Manager    $messages
		 * @param    string                 $where      SQL where clause
		 */
		public function recalculate_initial_distance( $messages = null , $where = null ) {
			$logging_enabled = ! is_null( $messages) && defined( 'DOING_CRON' ) && $this->slplus->SmartOptions->log_schedule_messages->is_true;

			if ( ! $this->slplus->currentLocation->is_valid_lat( $this->slplus->SmartOptions->map_center_lat->value ) ) {
				if ( $logging_enabled ) {
					$messages->add_message(
						sprintf( __( 'Recalculate initial distance needs map center to have a valid latitude. ( %s )' , 'store-locator-le' ) ,
							$this->slplus->SmartOptions->map_center_lat->value
						) );
				}
				return;
			}
			if ( ! $this->slplus->currentLocation->is_valid_lng( $this->slplus->SmartOptions->map_center_lng->value ) ) {
				if ( $logging_enabled ) {
					$messages->add_message(
						sprintf( __( 'Recalculate initial distance needs map center to have a valid longitude. ( %s )' , 'store-locator-le' ) ,
							$this->slplus->SmartOptions->map_center_lng->value
						) );
				}
				return;
			}

			if ( is_null( $where ) ) {
				$where = '';
			}

			$location_table = $this->slplus->database->info['table'];
			$prepared_sql =	$this->slplus->database->db->prepare(
				"UPDATE {$location_table} SET sl_initial_distance =  ( %d * acos( cos( radians( %f ) ) * cos( radians( sl_latitude ) ) * cos( radians( sl_longitude ) - radians( %f ) ) + sin( radians( %f ) ) * sin( radians( sl_latitude ) ) ) ) {$where}",
				( $this->slplus->SmartOptions->distance_unit->value === 'miles' ) ? SLPlus::earth_radius_mi : SLPlus::earth_radius_km,
				$this->slplus->SmartOptions->map_center_lat->value ,
				$this->slplus->SmartOptions->map_center_lng->value ,
				$this->slplus->SmartOptions->map_center_lat->value
			);

			$this->slplus->database->db->query( $prepared_sql );

			if ( $logging_enabled ) {
				$messages->add_message( __( 'Recalculate initial distance finished.' , 'store-locator-le' ) );
			}
		}

		/**
		 * Recalculate initial distance where distance is zero.
		 *
		 * @param    SLP_Message_Manager    $messages
		 */
		public function recalculate_initial_distance_where_zero( $messages = null ) {
			$this->recalculate_initial_distance( $messages , 'WHERE sl_initial_distance = 0 or sl_initial_distance IS NULL');
		}


	}

	global $slplus;
	if ( is_a( $slplus, 'SLPlus' ) ) {
		$slplus->add_object( new SLP_Location_Manager() );
	}

endif;