<?php
defined( 'ABSPATH' ) || exit;
/**
 * Class SLP_Admin_General_Text
 *
 * Extend text for admin general tab
 */
class SLP_Admin_General_Text {
	private $text;

	/**
	 * SLP_Admin_Settings_Text constructor.
	 */
	public function __construct() {
		$this->initialize();
	}

	/**
	 * Do at the start.
	 */
	private function initialize() {
		$this->set_text_strings();
		add_filter( 'slp_get_text_string', array( $this, 'get_text_string' ), 10, 2 );
	}

	/**
	 * Set the strings we need on the admin panel.
	 *
	 * @param string   $text
	 * @param string[] $slug
	 *
	 * @return string
	 */
	public function get_text_string( $text, $slug ) {
		if ( $slug[0] === 'settings_group_header' ) {
			$slug[0] = 'settings_group';
		}
		if ( isset( $this->text[ $slug[0] ][ $slug[1] ] ) ) {
			return $this->text[ $slug[0] ][ $slug[1] ];
		}

		return $text;
	}

	/**
	 * Set our text strings.
	 */
	private function set_text_strings() {
		if ( isset( $this->text ) ) {
			return;
		}
		global $slplus;
		$slp_text = SLP_Text::get_instance();

		$this->text['settings_section']['admin'         ]   = __( 'Admin'           , 'store-locator-le' );
		$this->text['settings_section']['user_interface']   = __( 'User Interface'  , 'store-locator-le' );
		$this->text['settings_section']['server'        ]   = __( 'Server'          , 'store-locator-le' );
		$this->text['settings_section']['data'          ]   = __( 'Data'            , 'store-locator-le' );
		$this->text['settings_section']['schedule'      ]   = __( 'Schedule'        , 'store-locator-le' );

		$this->text['settings_group']['locations'       ]   = __( 'Locations'       , 'store-locator-le' );
		$this->text['settings_group']['add_on_packs'    ]   = __( 'Add Ons'         , 'store-locator-le' );
		$this->text['settings_group']['messages'        ]   = __( 'Messages'        , 'store-locator-le' );
		$this->text['settings_group']['web_app_settings']   = __( 'Web App Settings', 'store-locator-le' );

		$this->text['label'      ]['log_schedule_messages'] = __( 'Log Schedule Messages' , 'store-locator-le' );
		$this->text['description']['log_schedule_messages'] = __( 'Scheduled tasks such as the Power scheduled import and Premier scheduled geocoding can log progress messages by turning this on.' , 'store-locator-le' );

		$this->text['label'      ]['php_max_execution_time'] = __( 'PHP Time Limit' , 'store-locator-le' );
		$this->text['description']['php_max_execution_time'] =
			__( 'Maximum execution time, in seconds, for PHP processing. ', 'store-locator-le' ) .
			__( 'Affects all CSV imports for add-ons and Janitor delete all locations. ', 'store-locator-le' ) .
			__( 'SLP Default 600. ', 'store-locator-le' ) .
			__( 'On most servers you will need to edit this setting in the php.ini file. ', 'store-locator-le' ) .
			sprintf( __( 'Your server default %s. ', 'store-locator-le' ), ini_get( 'max_execution_time' ) );


		$this->text[ 'label'            ][ 'slp_apikey'         ] = __( 'MySLP API Key' , 'store-locator-le' );
		$this->text[ 'description'      ][ 'slp_apikey'         ] = sprintf( __( 'Your API key from your %s login. ' , 'store-locator-le' ) , $slp_text->get_web_link( 'myslp' ) );

		$this->text[ 'label'            ][ 'slp_userid'         ] = __( 'MySLP Login' , 'store-locator-le' );
		$this->text[ 'description'      ][ 'slp_userid'         ] = sprintf( __( 'Your %s login. ' , 'store-locator-le' ) , $slp_text->get_web_link( 'myslp' ) );

		$this->text[ 'label'            ][ 'url_control_description'        ] = '';
		$this->text[ 'description'      ][ 'url_control_description'        ] = __('These settings determine what information can be passed along in the URL for pages that display the locator. ', 'store-locator-le' );
		if ( ! $slplus->AddOns->get( 'slp-premier' , 'active' ) ) {
			$this->text[ 'description' ][ 'url_control_description' ] .= ' ' . sprintf( __( '%s provides additional URL controls. ' , 'store-locator-le' ), $slp_text->get_web_link( 'shop_for_premier' ) );
		}

	}
}
