<?php
defined( 'ABSPATH' ) || exit;

/**
 * SLP Text Modifier
 */
class SLP_Power_Admin_General_Text extends SLP_Base_Text {

	/**
	 * Descriptions
	 *
	 * @param string $slug
	 * @param string $text
	 *
	 * @return string
	 */
	protected function description( $slug , $text ) {
		switch ( $slug ) {
			case 'log_import_messages'  : return __( 'Log import messages.' , 'slp-power' );
			case 'use_contact_fields'   : return __( 'Add detailed contacts fields to your locations data. ' , 'slp-power' ) .
			                                     __( 'Includes an intelligent "Identifier" field that maintains links to external data sources through CSV import.' , 'slp-power' );
			case 'use_nonces'           : return __( 'Use use nonces on [slp_directory] shortcode links. ', 'slp-power' ) .
			                                     __( 'Nonces make it harder to build search engine optimized links but harder for competitors to steal data. ', 'slp-power' );
			case 'use_pages'            : return __( 'Pages are search engine friendly and are linked directly to your live location data.' , 'slp-power' );
			case 'use_sensor'           : return __( 'Will ask visitors if they want to allow location sensing (gps). ', 'slp-power') .
			                                     __( 'Your site MUST be using HTTPS for the location sensor to work.' , 'slp-power' );

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
	protected function label( $slug , $text ) {
		switch ( $slug ) {
			case 'highlight_uncoded'    : return __( 'Highlight Uncoded'        , 'slp-power' );
			case 'log_import_messages'  : return __( 'Log Import Messages'      , 'slp-power' );
			case 'use_contact_fields'   : return __( 'Enable Contact Fields'    , 'slp-power' );
			case 'use_nonces'           : return __( 'Use Nonces On Directory'  , 'slp-power' );
			case 'use_pages'            : return __( 'Enable Pages'             , 'slp-power' );
			case 'use_sensor'           : return __( 'Use Location Sensor'      , 'slp-power' );
		}

		return $text;
	}
}
