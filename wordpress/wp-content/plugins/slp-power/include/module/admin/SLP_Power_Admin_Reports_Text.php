<?php
defined( 'ABSPATH' ) || exit;

/**
 * SLP Text Modifier
 */
class SLP_Power_Admin_Reports_Text extends SLP_Base_Text {

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
			case 'delete_history_before_this_date'    : return __( 'Delete historical search records older than the date entered. '     , 'slp-power' ).
			                                                   __( 'Any SQL date will work, usually YYYY-MM-DD will suffice.  '         , 'slp-power' ).
			                                                   __( 'You can be as specific as YYYY-MM-DD hh:mm:ss.  '                   , 'slp-power' ).
			                                                   '<p>' .
			                                                   __( 'Save settings to perform the record clean up. '                     , 'slp-power' ).
															   __( 'Default: blank (keep all search history forever)'                   , 'slp-power' ) .
			                                                   '</p>'
																;

			case 'reporting_enabled'                  : return __( 'Enables tracking of searches and returned results. '                , 'slp-power' ) .
															   __( 'The added overhead can increase how long it takes to return location search results.', 'slp-power' );
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
			case 'delete_history_before_this_date'  : return __( 'Remove History Before'    , 'slp-power' );
			case 'reporting_enabled'                : return __( 'Enable Reporting'         , 'slp-power' );
		}

		return $text;
	}
}
