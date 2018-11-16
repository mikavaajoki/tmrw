<?php
defined( 'ABSPATH' ) || exit;
require_once( SLPLUS_PLUGINDIR . '/include/base/SLP_AddOn_Options.php' );

/**
 * Class SLP_Power_Options
 */
class SLP_Power_Options extends SLP_AddOn_Options {

	/**
	 * Create our options.
	 */
	protected function create_options() {
		SLP_Power_Text::get_instance();

		$this->augment_system_wide_options();

		$this->general_admin_messages();
		$this->augment_general_data_packaged_data_extensions();
		$this->augment_general_server_security();
		$this->augment_general_server_web_app();
		$this->augment_general_user_interface_javascript();

		$this->augment_settings_map_markers();
		$this->augment_settings_results_appearance();
		$this->augment_settings_results_functionality();
		$this->augment_settings_search_appearance();
		$this->augment_settings_view_appearance();

		$this->pages_tab();

		$this->report_settings();
	}

	/**
	 * Do this when the use contact fields checkbox has changed.
	 * @param $key
	 * @param $old_value
	 * @param $new_value
	 */
	public function activate_contact_fields( $key , $old_value, $new_value ) {
		if ( $new_value ) {
			global $slplus;
			require_once( SLPPOWER_REL_DIR . 'include/class.activation.php' );
			$activation = new SLPPower_Activation( array( 'addon' => $slplus->AddOns->instances['slp-power'] ) );
			$activation->add_data_extensions();
			$this->slplus->notifications->add_notice( 'info' , __( 'Extended contact fields have been activated.' , 'slp-power' ) );
		}
	}

	/**
	 * General | Admin | Messages
	 */
	private function general_admin_messages() {
		$new_options[ 'log_import_messages' ] = array( 'type' => 'checkbox' , 'default' => '0' );
		$this->attach_to_slp( $new_options , array( 'page'=> 'slp_general','section' => 'admin', 'group' => 'messages' ) );
	}

	/**
	 * General / Data / Packaged Data Extensions
	 */
	private function augment_general_data_packaged_data_extensions() {
		$new_options['use_contact_fields'] = array( 'type' => 'checkbox', 'default' > '0' , 'call_when_changed' => array( $this , 'activate_contact_fields' ) );
		$this->attach_to_slp( $new_options , array( 'page'=> 'slp_general','section' => 'data', 'group' => 'packaged_data_extensions' ) );
	}

	/**
	 * General / Server / Security
	 */
	private function augment_general_server_security() {
		$new_options['use_nonces'] = array( 'type' => 'checkbox', 'default' > '1' , 'use_in_javascript' => true );
		$this->attach_to_slp( $new_options , array( 'page'=> 'slp_general','section' => 'server', 'group' => 'security' ) );
	}

	/**
	 * General / Server / Web App
	 */
	private function augment_general_server_web_app() {
		$new_options['use_pages'] = array( 'type' => 'checkbox', 'default' > '0' );
		$this->attach_to_slp( $new_options , array( 'page'=> 'slp_general','section' => 'server', 'group' => 'web_app_settings' ) );
	}

	/**
	 * General / UI / JavaScript
	 */
	private function augment_general_user_interface_javascript() {
		$new_options['use_sensor'] = array( 'type' => 'checkbox', 'default' > '1' , 'use_in_javascript' => true );
		$this->attach_to_slp( $new_options , array( 'page'=> 'slp_general','section' => 'user_interface', 'group' => 'javascript' ) );
	}

	/**
	 * Settings / Map / Markers
	 *
	 */
	private function augment_settings_map_markers() {

		$related_to = 'map_end_icon,default_icons';
		$new_options[ 'default_icons' ] = array( 'related_to' => $related_to , 'type' => 'checkbox' );

		$this->attach_to_slp( $new_options , array( 'page'=> 'slp_experience','section' => 'map', 'group' => 'markers' ) );
	}

	/**
	 * Settings / Results / Appearance
	 *
	 */
	private function augment_settings_results_appearance() {

		$related_to = 'show_icon_array';
		$new_options[ 'show_icon_array' ] = array( 'related_to' => $related_to , 'type' => 'checkbox' );

		$this->attach_to_slp( $new_options , array( 'page'=> 'slp_experience','section' => 'results', 'group' => 'appearance' ) );
	}

	/**
	 * Settings / Results / Functionality
	 *
	 */
	private function augment_settings_results_functionality() {

		$related_to = 'ajax_orderby_catcount,orderby';
		$new_options[ 'ajax_orderby_catcount' ] = array( 'related_to' => $related_to , 'type' => 'checkbox' );

		$this->attach_to_slp( $new_options , array( 'page'=> 'slp_experience','section' => 'results', 'group' => 'functionality' ) );
	}

	/**
	 * Settings / Search / Appearance
	 *
	 */
	private function augment_settings_search_appearance() {

		// Category Selector Options For Front End
		$related_to = 'search_appearance_category_header,label_category,show_cats_on_search,show_option_all,hide_empty';
		$new_options[ 'search_appearance_category_header' ] = array( 'related_to' => $related_to , 'type' => 'subheader' , 'description' => '' );
		$new_options[ 'show_cats_on_search'               ] = array( 'related_to' => $related_to , 'type' => 'dropdown' , 'default' => 'none' , 'get_items_callback' => array( $this , 'get_show_cats_on_search_items' ) );
		$new_options[ 'label_category'                    ] = array( 'related_to' => $related_to , 'default' => __( 'Category' , 'slp-power') , 'allow_empty' => true );
		$new_options[ 'show_option_all'                   ] = array( 'related_to' => $related_to , 'default' => __( 'Any' , 'slp-power') , 'allow_empty' => true );
		$new_options[ 'hide_empty'                        ] = array( 'related_to' => $related_to , 'type' => 'checkbox' );

		$this->attach_to_slp( $new_options , array( 'page'=> 'slp_experience','section' => 'search', 'group' => 'appearance' ) );
	}

	/**
	 * Settings / View / Appearance
	 *
	 */
	private function augment_settings_view_appearance() {

		$related_to = 'show_legend_text';
		$new_options[ 'show_legend_text' ] = array( 'related_to' => $related_to , 'type' => 'checkbox' );

		$this->attach_to_slp( $new_options , array( 'page'=> 'slp_experience','section' => 'view', 'group' => 'appearance' ) );
	}

	/**
	 * System wide (not directly settable) options.
	 */
	private function augment_system_wide_options() {
		$new_options[ 'last_geocoded_location' ] = array();
		$this->attach_to_slp( $new_options );
	}

	/**
	 * Get the dropdown selections for the category selector.
	 *
	 * @return mixed
	 */
	public function get_show_cats_on_search_items() {
		/** @var SLP_Power_Category_Selector_Manager $cat_manager **/
		$cat_manager = SLP_Power_Category_Selector_Manager::get_instance();
		return $cat_manager->get_selectors();
	}

	/**
	 * Set Pages Tab options
	 */
	private function pages_tab() {
		$this->pages_settings_appearance();
	}

	/**
	 * Pages | Settings | Appearance
	 */
	private function pages_settings_appearance() {
		$new_options[ 'pages_directory_wrapper_css_class' ] = array( 'default' => 'slp_pages_list' , 'allow_empty' => true );
		$new_options[ 'pages_directory_entry_css_class' ] = array( 'default' => 'slp_page location_details' , 'allow_empty' => true );

		$this->attach_to_slp( $new_options , array( 'page'=> 'slp-pages','section' => 'settings', 'group' => 'appearance' ) );
	}

	/**
	 * Report > Settings
	 */
	private function report_settings() {
		$new_options[ 'reporting_enabled' ] = array( 'type' => 'checkbox' );
		$new_options[ 'delete_history_before_this_date' ] = array( 'classes' => array( 'quick_save' ) );
		$this->attach_to_slp( $new_options , array( 'page'=> 'slp_reports','section' => 'settings', 'group' => 'settings' ) );
	}

}