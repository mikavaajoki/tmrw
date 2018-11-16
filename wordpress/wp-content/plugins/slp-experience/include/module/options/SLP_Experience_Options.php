<?php
defined( 'ABSPATH' ) || exit;
require_once( SLPLUS_PLUGINDIR . '/include/base/SLP_AddOn_Options.php' );

/**
 * SmartOptions for the Experience add on.
 */
class SLP_Experience_Options extends SLP_AddOn_Options {

	/**
	 * Create our options.
	 */
	protected function create_options() {
		$this->general_ui_url_controls();

		$this->settings_map_appearance();
		$this->settings_map_startup();

		$this->results_interaction();
		$this->experience_results_labels();

		$this->settings_search_functionality();
		$this->experience_search_appearance();
		$this->experience_search_labels();

		$this->experience_view();
	}

	/**
	 * Settings / Map / Appearance
	 */
	private function settings_map_appearance() {
		$related = 'map_controls_subheader,map_options_scaleControl,map_options_mapTypeControl,map_option_zoomControl,map_option_fullscreenControl,map_option_hide_streetview';
		$new_options[ 'map_controls_subheader' ] = array( 'type' => 'subheader' , 'related_to' => $related );
		$new_options[ 'map_options_scaleControl' ] = array( 'type' => 'checkbox' , 'default' => '1' , 'related_to' => $related );
		$new_options[ 'map_options_mapTypeControl' ] = array( 'type' => 'checkbox' , 'default' => '1' , 'related_to' => $related );

		$new_options[ 'google_map_style' ] = array( 'type'    => 'textarea', 'use_in_javascript' => true, 'is_text' => true ,);
		$this->attach_to_slp( $new_options , array( 'page'    => 'slp_experience', 'section' => 'map', 'group'   => 'appearance' ) );
	}

	/**
	 * Settings / Map / Startup
	 */
	private function settings_map_startup() {
		$related = 'map_initial_display,starting_image';
		$new_options[ 'map_initial_display'  ] = array( 'related_to' => $related, 'type'    => 'dropdown' , 'default' => 'map' ,
            'options'           => array(
				array( 'label' => __( 'Show Map'            , 'slp-experience' ) , 'value' => 'map'     , 'description' => __( 'Display a map.'                                 , 'slp-experience' ) ) ,
				array( 'label' => __( 'Hide Until Search'   , 'slp-experience' ) , 'value' => 'hide'    , 'description' => __( 'Display nothing until an address is searched.'  , 'slp-experience' ) ) ,
				array( 'label' => __( 'Image Until Search'  , 'slp-experience' ) , 'value' => 'image'   , 'description' => __( 'Display the image set by Starting Image. '      , 'slp-experience' ) ) ,
			)
		);
		$new_options[ 'starting_image'       ] = array( 'related_to' => $related );

		$new_options[ 'no_autozoom'          ] = array( 'type'    => 'checkbox', 'use_in_javascript' => true );
		$new_options[ 'no_homeicon_at_start' ] = array( 'type'    => 'checkbox', 'use_in_javascript' => true , 'default' => true );
		$this->attach_to_slp( $new_options , array( 'page'    => 'slp_experience', 'section' => 'map', 'group'   => 'at_startup' ) );
	}

	/**
	 * Settings | Results | Interaction
	 */
	private function results_interaction() {
		$new_options[ 'add_tel_to_phone' ] = array( 'type' => 'checkbox' , 'default' => '0' , 'related_to' => 'phone_extension_delimiter' );
		$this->attach_to_slp( $new_options , array( 'page' => 'slp_experience' , 'section' => 'results' , 'group' => 'results_interaction' ) );
	}


	/**
	 * Experience / Results / Labels
	 */
	private function experience_results_labels() {
		$new_options[ 'results_box_title' ] = array( 'is_text' => true, );
		$this->attach_to_slp( $new_options , array( 'page'    => 'slp_experience', 'section' => 'results', 'group'   => 'labels',) );
	}

	/**
	 * Experience / Search / Appearance
	 */
	private function experience_search_appearance() {
		$new_options[ 'hide_search_form' ] = array( 'type' => 'checkbox' , 'default' => '0' );
		$this->attach_to_slp( $new_options , array( 'page' => 'slp_experience' , 'section' => 'search' , 'group' => 'appearance' ) );
	}

	/**
	 * Experience / Search / Labels
	 */
	private function experience_search_labels() {
		$new_options[ 'address_placeholder' ] = array(  'is_text'    => true, 'related_to' => 'label_search,hide_address_entry' );
		$this->attach_to_slp( $new_options , array( 'page' => 'slp_experience' , 'section' => 'search' , 'group' => 'labels' ) );
	}

	/**
	 * Experience / View
	 */
	private function experience_view() {
		$this->slplus->SmartOptions->bubblelayout->add_to_settings_tab = true;
		$this->slplus->SmartOptions->layout->add_to_settings_tab = true;
		$this->slplus->SmartOptions->maplayout->add_to_settings_tab = true;
		$this->slplus->SmartOptions->resultslayout->add_to_settings_tab = true;
		$this->slplus->SmartOptions->searchlayout->add_to_settings_tab = true;
	}

	/**
	 * General / User Interface / URL Controls
	 */
	private function general_ui_url_controls() {
		$new_options[ 'url_allow_address' ] = array( 'type' => 'checkbox' , 'default' => '0' );
		$this->attach_to_slp( $new_options , array( 'page' => 'slp_general' , 'section' => 'user_interface' , 'group' => 'url_control' ) );
	}

	/**
	 * Settings / Search / Functionality
	 */
	private function settings_search_functionality() {
		$new_options[ 'append_to_search' ] = array( 'use_in_javascript' => true );

		$this->attach_to_slp( $new_options , array( 'page' => 'slp_experience' , 'section' => 'search' , 'group' => 'functionality' ) );
	}
}
