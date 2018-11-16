<?php

if ( ! class_exists( 'SLP_Premier_Admin_Info_Text' ) ):

/**
 * SLP Text Modifier
 *
 * @var array    text    array of our text modifications key => SLP text manager slug, value = our replacement text
 *
 * @property    SLP_Premier $addon
 */
class SLP_Premier_Admin_Info_Text extends SLPlus_BaseClass_Object {
	public $addon;
	private $text;

	/**
	 * SLP Text Manager Hooks
	 */
	private function add_hooks() {
		add_filter( 'slp_get_text_string'    , array( $this , 'augment_text_string' ) , 10 , 2 );
	}

	/**
	 * Replace the SLP Text Manager Strings at startup.
	 *
	 * @param string $text  the original text
	 * @param string $slug  the slug being requested
	 *
	 * @return array            the new SLP text manager strings
	 */
	public function augment_text_string( $text , $slug ) {
		$this->init_text();
		if ( ! is_array( $slug ) ) {
			if ( isset( $this->text[ $slug ] ) ) {
				return $this->text[ $slug ];
			}
		} else {
			if ( isset( $this->text[ $slug[0] ] ) && isset( $this->text[ $slug[0] ][ $slug[1] ] ) ){
				return $this->text[ $slug[0] ][ $slug[1] ];
			}
		}
		return $text;
	}

	/*
	 * Return Schedule Text
	 */
	public function get_schedule_text( $schedule_slug ) {
		if ( ! empty( $this->text[ $schedule_slug ] ) ) {
			return $this->text[ $schedule_slug ];
		} else {
			return ucfirst( $schedule_slug );
		}
	}

	/**
	 * Initialize our text modification array.
	 */
	private function init_text() {
		if ( isset( $this->text ) ) {
			return;
		}

		$this->text['settings_section']['schedule' ] = __( 'Schedule', 'slp-premier' );

		$this->text['settings_group'  ]['wp_cron_list'    ] = __( 'Events'   , 'slp-premier' );
		$this->text['settings_group_header' ] = $this->text[ 'settings_group' ];

		$this->text[ '0' ]            = __( 'Run Once' , 'slp-premier' );
		$this->text[ 'hourly' ]       = __( 'Hourly' , 'slp-premier' );
		$this->text[ 'twicedaily' ]   = __( 'Twice Daily' , 'slp-premier' );
	}

	/**
	 * Things we do at the start.
	 */
	public function initialize() {
		$this->add_hooks();
	}
}
endif;