<?php
defined( 'ABSPATH' ) || exit;

/**
 * SLP Text Modifier
 *
 * Used by the MySLP Dashboard Base Class on as well as the REST API handler.
 */
class SLP_Premier_Text_Admin_Experience extends SLP_Base_Text {

	/**
	 * General -- default (unspecified grouping)
	 *
	 * @param string $slug
	 * @param string $text
	 *
	 * @return string
	 */
	public function general( $slug , $text = '' ) {
		switch ( $slug ) {
			case 'red_light_grey'   : return __('Red on Light Grey'     , 'slp-premier' );
			case 'blue_light_grey'  : return __('Blue on Light Grey'    , 'slp-premier' );
			case 'teal_light_grey'  : return __('Teal on Light Grey'    , 'slp-premier' );
			case 'green_light_grey' : return __('Green on Light Grey'   , 'slp-premier' );
			case 'yellow_light_grey': return __('Yellow on Light Grey'  , 'slp-premier' );
			case 'amber_light_grey' : return __('Amber on Light Grey'   , 'slp-premier' );
			case 'grey_light_grey'  : return __('Grey on Light Grey'    , 'slp-premier' );
			case 'dark_light_grey'  : return __('Dark on Light Grey'    , 'slp-premier' );
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
	final protected function settings_group_header( $slug , $text ) {
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
	final protected function description( $slug , $text ) {
		switch ( $slug ) {
			case 'boundaries_influence_type'            : return __( 'Search boundaries influence how Google determines the best match for an address.'                                     , 'slp-premier' );
			case 'bubble_footnote'                      : return __( 'The text entered here will appear in the map info bubble where the [slp_option bubble_footnote] shortcode appears. '  , 'slp-premier' );
			case 'clusters_enabled'                     : return __( 'When checked employ map clusters when markers are within close proximity.'                                            , 'slp-premier' );
			case 'cluster_gridsize'                     : return __( 'How close to markers need to be to cluster together. '                                                                , 'slp-premier' ) .
			                                                     __( 'The number is "tile spaces" which changes based on the map zoom level. '                                              , 'slp-premier' );
			case 'cluster_minimum'                      : return __( 'The minimum number of markers required to make a cluster.'                                                            , 'slp-premier' );
			case 'loading_indicator_location'           : return __( 'Where to show the loading indicator on the map page.'                                                                 , 'slp-premier' );
			case 'loading_indicator_color'              : return __( 'Choose the color palette for the loading indicator.'                                                                  , 'slp-premier' );
			case 'map_marker_tooltip'                   : return __( 'Shows a tool tip of the location name when a user hovers over a map marker.'                                          , 'slp-premier' );

			case 'map_option_fullscreenControl'         : return __( 'Shows the full screen mode control on the map.'                                                                       , 'slp-premier' );
			case 'map_option_hide_streetview'           : return __( 'Hide the street view man on the Google map interface.'                                                                , 'slp-premier' );
			case 'map_option_zoomControl'               : return __( 'Shows the zoom in and zoom out controls on the map.'                                                                  , 'slp-premier' );
			
			case 'map_options_clickableIcons'           : return __( "When checked makes Google's default map icons clickable."                                                             , 'slp-premier' );
			case 'pagination_enabled'                   : return __( 'When checked the pagination label and previous/next page button is shown on results.'                                 , 'slp-premier' );
			case 'phone_extension_delimiter'            : return __( 'The character(s) that determine where a phone extensions starts. ' , 'slp-premier' ) .
																 __( 'Used when building tel: dial links to insert a pause for automatic dialing.' ,'slp-premier' ) .
																 __( 'For example - ext. or x' , 'slp-premier' );
			case 'results_click_animate_marker'         : return __( 'How to animate the map marker when a user clicks the corresponding result.'                                           , 'slp-premier' );
			case 'results_click_label_marker'           : return __( 'How to label the map marker when a user clicks the corresponding result.'                                             , 'slp-premier' );
			case 'results_click_map_movement'           : return __( 'How should the map behave when a location is clicked?'                                                                , 'slp-premier' );
			case 'results_click_marker_icon_behavior'   : return __( 'How should the marker icon change when a result is clicked?'                                                          , 'slp-premier' );
			case 'results_click_marker_icon'            : return __( 'What to change the marker to when clicked.'                                                                           , 'slp-premier' );
			case 'results_header'                       : return __( 'If set, output the HTML + shortcodes before displaying results. '                                                     , 'slp-premier' ).
			                                                     __( 'If you wish to attach the locations to an HTML element other than the default #map_sidebar div, '                     , 'slp-premier' ).
			                                                     __( 'add id="add_locations_here" to one of your Results Header elements such as a &lt;tbody&gt; tag. '                     , 'slp-premier' );
			case 'results_header_1'                     :
			case 'results_header_2'                     :
			case 'results_header_3'                     :
			case 'results_header_4'                     :
														  return __( 'Output a header on the results for plugin styles that support it. '                                                   , 'slp-premier' ) .
														         sprintf( __( 'Use the settings [slp_option name = "%s"] in the results layout. '                                           , 'slp-premier' ) , $slug );
			case 'results_no_wrapper'                   : return __( 'When checked do not wrap the individual location search results in a div.'                                            , 'slp-premier' ) .
			                                                     __( 'Useful for plugin styles that provide their own non-div wrappers such as tr.'                                         , 'slp-premier' );
			case 'search_box_subtitle'                  : return __( 'The label that goes in the search form box header, for plugin themes that support it. '                               , 'slp-premier' ) .
			                                                     __( 'Put this in a search form using the [slp_option name="search_box_subtitle"] shortcode. '                              , 'slp-premier' );
			case 'search_on_map_move'                   : return __( 'If a user moves the map by dragging it, update the map with a new location search based on the new map center. '      , 'slp-premier' );
			case 'style'                                : return __( 'What style do you want to display for your plugin.'                                                                   , 'slp-premier' ) .
			                                                     __( 'Replaces the plugin style system with an active online directory.'                                                    , 'slp-premier' );
			case 'use_territory_bounds'                 : return __( 'Activate the territories module.'                                                                                     , 'slp-premier' );
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
	final protected function label( $slug , $text ) {
		switch ( $slug ) {
			case 'boundaries_influence_type'            : return __( 'Boundaries Influence Guess'   , 'slp-premier' );
			case 'bubble_footnote'                      : return __( 'Bubble Footnote'              , 'slp-premier' );
			case 'clusters_enabled'                     : return __( 'Enable Clusters'              , 'slp-premier' );
			case 'cluster_gridsize'                     : return __( 'Cluster Radius'               , 'slp-premier' );
			case 'cluster_minimum'                      : return __( 'Cluster Minimum'              , 'slp-premier' );
			case 'loading_indicator_color'              : return __( 'Loading Indicator Color'      , 'slp-premier' );
			case 'loading_indicator_location'           : return __( 'Loading Indicator Location'   , 'slp-premier' );
			case 'map_appearance_cluster_header'        : return __( 'Cluster Markers'              , 'slp-premier' );
			case 'map_marker_tooltip'                   : return __( 'Marker Tooltip'               , 'slp-premier' );
			case 'map_option_fullscreenControl'         : return __( 'Full Screen'                  , 'slp-premier' );
			case 'map_option_hide_streetview'           : return __( 'Hide Street View'             , 'slp-premier' );
			case 'map_option_zoomControl'               : return __( 'Zoom'                         , 'slp-premier' );
			case 'map_options_clickableIcons'           : return __( 'Clickable Icons'              , 'slp-premier' );
			case 'pagination_enabled'                   : return __( 'Enable Pagination'            , 'slp-premier' );
			case 'phone_extension_delimiter'            : return __( 'Phone Extension Delimeter'    , 'slp-premier' );
			case 'results_click_animate_marker'         : return __( 'Animate Marker'               , 'slp-premier' );
			case 'results_click_label_marker'           : return __( 'Label Marker'                 , 'slp-premier' );
			case 'results_click_map_movement'           : return __( 'Map Movement'                 , 'slp-premier' );
			case 'results_click_marker_icon_behavior'   : return __( 'Marker Icon Behavior'         , 'slp-premier' );
			case 'results_click_marker_icon'            : return __( 'Active Marker Icon'           , 'slp-premier' );
			case 'results_header'                       : return __( 'Results Header'               , 'slp-premier' );
			case 'results_header_1'                     : return __( 'Results Header 1'             , 'slp-premier' );
			case 'results_header_2'                     : return __( 'Results Header 2'             , 'slp-premier' );
			case 'results_header_3'                     : return __( 'Results Header 3'             , 'slp-premier' );
			case 'results_header_4'                     : return __( 'Results Header 4'             , 'slp-premier' );
			case 'results_no_wrapper'                   : return __( 'Do Not Wrap Results In Div'   , 'slp-premier' );
			case 'search_box_subtitle'                  : return __( 'Search Box Subtitle'          , 'slp-premier' );
			case 'search_on_map_move'                   : return __( 'Search When Map Moves'        , 'slp-premier' );
			case 'style'                                : return __( 'Style'                        , 'slp-premier' );
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
	final protected function settings_group( $slug , $text ) {
		switch ( $slug ) {
			case 'results_interaction': return __('Results Interaction' , 'slp-premier' );
		}
		return $text;
	}
}
