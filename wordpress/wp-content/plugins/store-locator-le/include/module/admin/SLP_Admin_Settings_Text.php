<?php
defined( 'ABSPATH' ) || die();
/**
 * Class SLP_Admin_Settings_Text
 */
class SLP_Admin_Settings_Text extends SLPlus_BaseClass_Object {
	private $text_strings;
	public $uses_slplus = false;

	/**
	 * Things we do at the start.
	 */
	public function initialize() {
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
		if ( isset( $this->text_strings[ $slug[0] ][ $slug[1] ] ) ) {
			return $this->text_strings[ $slug[0] ][ $slug[1] ];
		}

		return $text;
	}

	/**
	 * Set our text strings.
	 */
	private function set_text_strings() {
		if ( isset( $this->text_strings ) ) {
			return;
		}

		// Has dependents
		$this->text_strings['changes_location_tab_label'] =
			__( 'Changes the Locations tab field label on the admin interface. ', 'store-locator-le' );


		// Sections
		$this->text_strings['settings_section']['map']     = __( 'Map', 'store-locator-le' );
		$this->text_strings['settings_section']['results'] = __( 'Results', 'store-locator-le' );
		$this->text_strings['settings_section']['search']  = __( 'Search', 'store-locator-le' );
		$this->text_strings['settings_section']['view']    = __( 'View', 'store-locator-le' );

		// Groups
		$this->text_strings['settings_group']['appearance']      = __( 'Appearance', 'store-locator-le' );
		$this->text_strings['settings_group']['after_search']    = __( 'After Search', 'store-locator-le' );
		$this->text_strings['settings_group']['at_startup']      = __( 'At Startup', 'store-locator-le' );
		$this->text_strings['settings_group']['functionality']   = __( 'Functionality', 'store-locator-le' );
		$this->text_strings['settings_group']['labels']          = __( 'Labels', 'store-locator-le' );
		$this->text_strings['settings_group']['markers']         = __( 'Markers', 'store-locator-le' );
		$this->text_strings['settings_group']['search_features'] = __( 'Search Features', 'store-locator-le' );
		$this->text_strings['settings_group']['search_labels']   = __( 'Search Labels', 'store-locator-le' );
		$this->text_strings['settings_group']['settings']        = __( 'Settings', 'store-locator-le' );
		$this->text_strings['settings_group']['startup']         = __( 'Startup', 'store-locator-le' );

		// Subheader labels & descriptions
		$this->text_strings['label'][ 'map_controls_subheader']  =  __( 'Map Controls' , 'store-locator-le' );
		$this->text_strings['description'][ 'map_controls_subheader'] =  __( 'Determines which user interface controls appear on the map.' , 'store-locator-le' );


		// Settings labels and descriptions
		$this->text_strings['label']['distance_unit']       = __( 'Distance Unit', 'store-locator-le' );
		$this->text_strings['description']['distance_unit'] =
			__( 'Set the distance label for the radius drop down and search results. ', 'store-locator-le' ) .
			__( 'This also changes how a distance is calculated between a user-entered address and a location in the database. ', 'store-locator-le' );

		$this->text_strings['label']['immediately_show_locations']       = __( 'Show Locations', 'store-locator-le' );
		$this->text_strings['description']['immediately_show_locations'] =
			__( 'Display locations as soon as map loads, based on map center and default radius. ', 'store-locator-le' ) .
			__( 'The settings in the startup section here impact how this mode works. ', 'store-locator-le' );

		$this->text_strings['label']['initial_radius']       = __( 'Initial Search Radius', 'store-locator-le' );
		$this->text_strings['description']['initial_radius'] =
			__( 'What should immediately show locations use as the default search radius? ', 'store-locator-le' ) .
			__( 'Leave empty to use map radius default or set to a large number like 25000 to search everywhere.', 'store-locator-le' );

		$this->text_strings['label']['initial_results_returned']       = __( 'Number To Show Initially', 'store-locator-le' );
		$this->text_strings['description']['initial_results_returned'] =
			__( 'How many locations should be shown when Immediately Show Locations is checked. ', 'store-locator-le' ) .
			__( 'Recommended maximum is 50.', 'store-locator-le' );

		$this->text_strings['label']['instructions']       = __( 'Instructions', 'store-locator-le' );
		$this->text_strings['description']['instructions'] =
			__( 'Search results instructions shown if immediately show locations is not selected.', 'store-locator-le' );

		$this->text_strings['label']['label_directions']       = __( 'Directions', 'store-locator-le' );
		$this->text_strings['description']['label_directions'] =
			__( 'What to put on the search results for the directions link. ', 'store-locator-le' );

		$this->text_strings['label']['label_email']       = __( 'Email', 'store-locator-le' );
		$this->text_strings['description']['label_email'] =
			__( 'What to put on the search results in place of an email address. ', 'store-locator-le' ) .
			$this->text_strings['changes_location_tab_label'];

		$this->text_strings['label']['label_fax']       = __( 'Fax', 'store-locator-le' );
		$this->text_strings['description']['label_fax'] =
			__( 'What to put on the search results preceding the fax number. ', 'store-locator-le' ) .
			$this->text_strings['changes_location_tab_label'];

		$this->text_strings['label']['label_hours']       = __( 'Hours', 'store-locator-le' );
		$this->text_strings['description']['label_hours'] =
			__( 'What to put in search results for hours. ', 'store-locator-le' ) .
			$this->text_strings['changes_location_tab_label'];

		$this->text_strings['label']['label_image']       = __( 'Image', 'store-locator-le' );
		$this->text_strings['description']['label_image'] =
			__( 'Changes the Locations tab field label on the admin interface. ', 'store-locator-le' );

		$this->text_strings['label']['label_phone']       = __( 'Phone', 'store-locator-le' );
		$this->text_strings['description']['label_phone'] =
			__( 'What to put on the search results preceding the phone number on search results. ', 'store-locator-le' ) .
			$this->text_strings['changes_location_tab_label'];

		$this->text_strings['label']['label_radius']       = __( 'Radius', 'store-locator-le' );
		$this->text_strings['description']['label_radius'] =
			__( 'Search form radius label. ', 'store-locator-le' );

		$this->text_strings['label']['label_search']       = __( 'Address', 'store-locator-le' );
		$this->text_strings['description']['label_search'] =
			__( 'Search form address label. ', 'store-locator-le' );

		$this->text_strings['label']['label_website']       = __( 'Website URL', 'store-locator-le' );
		$this->text_strings['description']['label_website'] =
			__( 'Search results text for the website link. ', 'store-locator-le' ) .
			$this->text_strings['changes_location_tab_label'];

		$this->text_strings['label']['loading_indicator']       = __( 'Loading Indicator', 'store-locator-le' );
		$this->text_strings['description']['loading_indicator'] =
			__( 'Select the style of loading indicator should be shown while waiting for locations to load. ', 'store-locator-le' ) .
			SLP_Text::get_instance()->get_web_link( 'shop_for_premier' ) . __( ' members have additional options available. ', 'store-locator-le' );

		$this->text_strings['label']['map_center']       = __( 'Center Map At', 'store-locator-le' );
		$this->text_strings['description']['map_center'] =
			__( 'Enter an address to serve as the initial focus for the map. ', 'store-locator-le' ) .
			__( 'Set to blank to reset this to the center of your Map Domain country. ', 'store-locator-le' ) .
			__( 'Default is the center of the country. ', 'store-locator-le' );

		$this->text_strings['label']['map_center_lat']       = __( 'Center Latitude Fallback', 'store-locator-le' );
		$this->text_strings['description']['map_center_lat'] =
			__( 'Where to center the map when Google geocoding is offline. ', 'store-locator-le' ) .
			__( 'Set to blank and save settings to reset to the center of the default country if Center Map At is Blank. ', 'store-locator-le' ) .
			__( 'If Center Map At has an address and this is set to blank, that address will be re-geocoded and stored here.', 'store-locator-le' );

		$this->text_strings['label']['map_center_lng']       = __( 'Center Longitude Fallback', 'store-locator-le' );
		$this->text_strings['description']['map_center_lng'] =
			__( 'Where to center the map when Google geocoding is offline. ', 'store-locator-le' ) .
			__( 'Set to blank and save settings to reset to the center of the default country if Center Map At is Blank. ', 'store-locator-le' ) .
			__( 'If Center Map At has an address and this is set to blank, that address will be re-geocoded and stored here.', 'store-locator-le' );

		$this->text_strings['label']['map_end_icon']       = __( 'Location Marker', 'store-locator-le' );
		$this->text_strings['description']['map_end_icon'] =
			__( 'The default marker to use on the map to show locations.', 'store-locator-le' );

		$this->text_strings['label']['map_height']       = __( 'Map Height', 'store-locator-le' );
		$this->text_strings['description']['map_height'] =
			__( 'The initial map height in pixels or percent of initial page height. ', 'store-locator-le' ) .
			__( 'Can also use rules like auto and inherit if Height Units is set to blank ', 'store-locator-le' );

		$this->text_strings['label']['map_height_units']       = __( 'Height Units', 'store-locator-le' );
		$this->text_strings['description']['map_height_units'] =
			__( 'Is the width a percentage of page width or absolute pixel size? ', 'store-locator-le' ) .
			__( 'Select blank to use CSS rules like auto or inherit in the Map Height setting.', 'store-locator-le' );

		$this->text_strings['label']['map_home_icon']       = __( 'Home Marker', 'store-locator-le' );
		$this->text_strings['description']['map_home_icon'] =
			__( 'Place the specified marker on the map when a user enters a search address.', 'store-locator-le' );

		$this->text_strings['label']['map_type']       = __( 'Map Type', 'store-locator-le' );
		$this->text_strings['description']['map_type'] =
			__( 'What style map do you want to use? ', 'store-locator-le' );

		$this->text_strings['label']['map_width']       = __( 'Map Width', 'store-locator-le' );
		$this->text_strings['description']['map_width'] =
			__( 'The initial map width in pixels or percent of initial page width. ', 'store-locator-le' ) .
			__( 'Can also use rules like auto and inherit if Width Units is set to blank ', 'store-locator-le' );

		$this->text_strings['label']['max_results_returned']       = __( 'Number To Show', 'store-locator-le' );
		$this->text_strings['description']['max_results_returned'] =
			__( 'How many locations does a search return? Default is 25.', 'store-locator-le' );

		$this->text_strings['label']['map_width_units']       = __( 'Width Units', 'store-locator-le' );
		$this->text_strings['description']['map_width_units'] =
			__( 'Is the width a percentage of page width or absolute pixel size? ', 'store-locator-le' ) .
			__( 'Select blank to use CSS rules like auto or inherit in the Map Width setting.', 'store-locator-le' );

		$this->text_strings['label'      ]['message_bad_address'] = __( 'Bad Address Message', 'store-locator-le' );
		$this->text_strings['description']['message_bad_address'] = __( 'The text to show in the results area when user enters an invalid address.', 'store-locator-le' );

		$this->text_strings['label'      ]['message_no_results'] = __( 'No Results Message', 'store-locator-le' );
        $this->text_strings['description']['message_no_results'] = __( 'The text to show in the results area when no results are found.', 'store-locator-le' );

		$this->text_strings['label']['radii']              = __( 'Radii Options', 'store-locator-le' );
		$this->text_strings['description']['radii']        =
			__( 'Separate each number with a comma ",". Put parenthesis "( )" around the default.', 'store-locator-le' );

		$this->text_strings['label']['remove_credits']       = __( 'Remove Credits', 'store-locator-le' );
		$this->text_strings['description']['remove_credits'] = __( 'Remove the search provided by tagline under the map. ', 'store-locator-le' );

		$this->text_strings['label'      ]['style'      ] = __( 'Locator Style', 'store-locator-le' );
		$this->text_strings['description']['style'      ] = __( 'How do you want the locator to look and function? ', 'store-locator-le' ) .
		                                                    __( 'Selecting a new style will "turn the dials" to set a new layout and tune functionality. ', 'store-locator-le' ) .
		                                                    sprintf( __( 'Each style changes different global settings in %s, overwriting any modifications you may have made previously. ' , 'store-locator-le' ) , SLPLUS_NAME);

		$this->text_strings['label'      ]['theme'      ] = __( 'Plugin Style', 'store-locator-le' );
		$this->text_strings['description']['theme'      ] = __( 'This is the older hard-coded CSS file implementation of locator styles. ', 'store-locator-le' ) .
															__( 'Most users will want to leave this set to "A Gallery Style" and select a style above. ', 'store-locator-le' ) .
															__( 'This determines how the locator looks within your page. ', 'store-locator-le' );

		$this->text_strings['label'      ]['zoom_level'] = __( 'Zoom Level', 'store-locator-le' );
		$this->text_strings['description']['zoom_level'] = __( 'Initial zoom level of the map if "immediately show locations" is NOT selected or if only a single location is found.', 'store-locator-le' ) .
		                                                   __( '0 = world view, 19 = house view.', 'store-locator-le' );

		$this->text_strings['label'      ]['zoom_tweak'] = __( 'Zoom Adjustment', 'store-locator-le' );
		$this->text_strings['description']['zoom_tweak'] = __( 'Changes how tight auto-zoom bounds the locations shown.  Lower numbers are closer to the locations.', 'store-locator-le' );
	}
}
