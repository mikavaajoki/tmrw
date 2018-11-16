<?php
defined( 'ABSPATH' ) || exit;

/**
 * SLP Text Modifier
 */
class SLP_Experience_Admin_Settings_Text extends SLP_Base_Text {

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
			case 'add_tel_to_phone'         : return __( 'When checked, wraps the phone number in the results in a tel: href tag.', 'slp-experience' );
			case 'address_placeholder'      : return __( 'Instructions to place in the address input.', 'slp-experience' );
			case 'append_to_search'         : return __( 'Anything you enter in this box will automatically be appended to the address a user types into the locator search form address box on your site.', 'slp-experience' );
			case 'bubblelayout'             : return __( 'Set the HTML and shortcodes that determine how the inside of the map info bubble is rendered. ', 'slp-experience' );
			case 'google_map_style'         : return __( 'Enter the JSON-style map style rules. ', 'slp-experience' );
			case 'hide_search_form'         : return __( 'Hide the user input on the search page, regardless of the SLP theme used.', 'slp-experience' );
			case 'layout'                   : return __( 'Set the HTML and shortcodes to determine the macro-level layout for the locator. ', 'slp-experience' ).
			                                         __( 'Does the search box go before or after the map, for example.  ', 'slp-experience' );
			case 'map_initial_display'      : return __( 'Set what to display when the page loads. ', 'slp-experience' );
			case 'map_options_scaleControl' : return __( 'Show the scale on the map. ', 'slp-experience' );
			case 'map_options_mapTypeControl'   : return __( 'Show the map type selector on the map. ', 'slp-experience' );
			case 'maplayout'                : return __( 'Set the HTML and shortcodes use to display the map and tagline. ', 'slp-experience' );
			case 'no_autozoom'              : return __( 'Use only the "zoom level" setting when rendering the initial map for show locations at startup. ', 'slp-experience' ) .
			                                         __( 'Do not automatically zoom the map to show all initial locations.', 'slp-experience' );
			case 'no_homeicon_at_start'     : return __( 'Do not include the home map marker for the initial map loading with show locations at startup enabled.', 'slp-experience' );
			case 'results_box_title'        : return __( 'Displayed where [slp_option nojs="search_box_title"] appears in layout settings. ', 'slp-experience' ) .
			                                         __( 'Newer plugin styles that support the Experience add on may use this. ' , 'slp-experience' );
			case 'resultslayout'            : return __( 'Set the HTML and shortcodes that determine how each location is rendered in the location list. '  , 'slp-experience' );
			case 'searchlayout'             : return __( 'Set the HTML and shortcodes to display the location search form to the user.'                     , 'slp-experience' );
			case 'starting_image'           : return __( 'If set, this image will be displayed until a search is performed. '                               , 'slp-experience' ).
			                                         __( 'Enter the full URL for the image.'                                                                , 'slp-experience' );
			case 'url_allow_address'        : return __( 'If checked an address can be pre-loaded via a URL string ?address=my+town. '                      , 'slp-experience' ).
			                                         __( 'This will disable the location sensor whenever the address is used in the URL.'                   , 'slp-experience' );
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
			case 'add_tel_to_phone'     : return __( 'Use Dial Link For Phone'  , 'slp-experience' );
			case 'address_placeholder'  : return __( 'Address Placeholder'      , 'slp-experience' );
			case 'append_to_search'     : return __( 'Append This To Searches'  , 'slp-experience' );
			case 'bubblelayout'         : return __( 'Bubble Layout'            , 'slp-experience' );
			case 'google_map_style'     : return __( 'Map Style'                , 'slp-experience' );
			case 'hide_search_form'     : return __( 'Hide Search Form'         , 'slp-experience' );
			case 'layout'               : return __( 'Layout'                   , 'slp-experience' );
			case 'map_initial_display'  : return __( 'Map Display'              , 'slp-experience' );
			case 'map_options_scaleControl'  : return __( 'Map Scale'              , 'slp-experience' );
			case 'map_options_mapTypeControl'  : return __( 'Map Type'              , 'slp-experience' );
			case 'maplayout'            : return __( 'Map Layout'               , 'slp-experience' );
			case 'no_autozoom'          : return __( 'Do Not Autozoom'          , 'slp-experience' );
			case 'no_homeicon_at_start' : return __( 'Hide Home Marker'         , 'slp-experience' );
			case 'results_box_title'    : return __( 'Results Box Title'        , 'slp-experience' );
			case 'resultslayout'        : return __( 'Results Layout'           , 'slp-experience' );
			case 'searchlayout'         : return __( 'Search Layout'            , 'slp-experience' );
			case 'starting_image'       : return __( 'Starting Image'           , 'slp-experience' );
			case 'url_allow_address'    : return __( 'Allow Address In URL'     , 'slp-experience' );
		}

		return $text;
	}

	/**
	 * Option Default
	 *
	 * @param string $slug
	 * @param string $text
	 *
	 * @return string
	 */
	protected function option_default( $slug , $text ) {
		switch ( $slug ) {
			case 'address_placeholder'  : return '';
			case 'google_map_style'     : return '';
			case 'results_box_title'    : return __( 'Your Closest Locations', 'slp-experience' );
		}

		return $text;
	}
}