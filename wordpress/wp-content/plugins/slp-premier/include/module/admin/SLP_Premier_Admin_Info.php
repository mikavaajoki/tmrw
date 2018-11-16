<?php
if ( ! class_exists( 'SLP_Premier_Admin_Info' ) ) {

	/**
     * The things that modify the Admin / Info Tab.
     *
	 * @property    SLP_Premier                      $addon
	 * @property    SLP_Premier_Admin_Info_Text      $Admin_Info_Text
	 */
	class SLP_Premier_Admin_Info extends SLP_Object_With_Objects {
		public $addon;
		private $group_params;

		protected $class_prefix = 'SLP_Premier_';
		protected $objects = array(
			'Admin_Info_Text' => array( 'subdir' => 'include/module/admin/', 'object' => null, 'auto_instantiate' => true,  ),
		);


		/**
		 * Things we do at the start.
		 */
		function initialize() {
			parent::initialize();
			$this->group_params = array( 'plugin' => $this->addon , 'section_slug' => null , 'group_slug' => null );
            add_action( 'slp_build_info_tab' , array( $this , 'add_schedule_subtab'  ), 90 );
		}

        /**
         * Info / Schedule
         *
         * @param   SLP_Settings    $settings
         */
        public function add_schedule_subtab( $settings ) {
        	$this->group_params[ 'section_slug' ] = 'schedule';
        	$this->group_params[ 'group_slug' ] = 'wp_cron_list';

            $settings->add_ItemToGroup(array(
                'group_params'  => $this->group_params,
                'type'          => 'details'   ,
                'description'   => $this->get_wp_cron_list()
            ));
        }

		/**
		 * Get a formatted list of WP Cron items.
		 *
		 * @return string
		 */
		private function get_wp_cron_list( ) {
			$crons = _get_cron_array();
			if ( empty($crons) ) {
				return __( 'There is nothing scheduled to run automatically on this site.', 'slp-premier' );
			}

			$cron_events = array();
			$html =
				'<p class="cron_entry">' .
				'<span class="hook">%s</span>' .
				'</p>';
			foreach ( $crons as $timestamp => $cron ) {
				foreach( $cron as $slug => $details ) {
					$mdkey = key( $details );
					$cron_events[ $details[$mdkey]['schedule'] ][ $timestamp ][] = sprintf( $html, $slug );
				}
			}

			$cron_table = '';
			foreach ( $cron_events as $schedule => $list_of_times ) {
				$cron_table .= sprintf( '<h2 class="cron_schedule">%s</h2>', $this->Admin_Info_Text->get_schedule_text( $schedule ) );
				$previous_run_at = null;
				foreach ( $list_of_times as $run_at => $list_of_events ) {
					if ( is_null( $previous_run_at ) ) {
						$cron_table .= sprintf( '<h3 class="run_time">%s</h3>' , date( "d F Y H:i:s", $run_at ) );
						$previous_run_at = $run_at;
					}
					foreach ( $list_of_events as $event_details ) {
						$cron_table .= sprintf( '<div class="event">%s</div>', $event_details );
					}
				}
			}

			$message = __( 'This list shows the internal ID for the WP Cron tasks followed by the next time the task will run. ' , 'slp-premier' );
			$more_message = __( 'Store Locator Plus scheduled items are found under the General / Schedule tab and Location / Import tab.' , 'slp-premier' );
			$what_time_is_it = __( 'The current time is ' , 'slp-premier' );
			$hammer_time = date("d F Y H:i:s",time());
			$html =
				'<p>%s</p>'.
				'<p>%s</p>'.
				'<p>%s %s</p>';
			$header = sprintf( $html , $message, $more_message , $what_time_is_it, $hammer_time );

			return $header . $cron_table;
		}

	}
}