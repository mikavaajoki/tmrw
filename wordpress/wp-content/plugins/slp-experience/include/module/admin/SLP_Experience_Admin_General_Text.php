<?php
defined( 'ABSPATH' ) || exit;

/**
 * SLP Text Modifier
 */
class SLP_Experience_Admin_General_Text extends SLP_Base_Text {

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
			case 'url_allow_address'     : return __( 'Allow Address In URL' , 'slp-experience' );
		}
		return $text;
	}

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
			case 'url_allow_address'     : return __( 'If checked an address can be pre-loaded via a URL string ?address=my+town. ' , 'slp-experience' );
		}
		return $text;
	}
}
