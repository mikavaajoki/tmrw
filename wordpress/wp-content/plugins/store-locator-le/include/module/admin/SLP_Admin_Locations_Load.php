<?php
defined( 'ABSPATH' ) || exit;
class SLP_Admin_Locations_Load extends SLP_Base_Object  {
	const subtab_slug = 'load';
	private $settings;

	public function initialize() {
		add_action( 'slp_build_locations_panels' , array( $this , 'add_load_tab' ) , 40 );
	}

	public function add_load_tab( $settings ) {
		$this->settings = $settings;
		$section_params['name'] = __( 'Load', 'store-locator-le' );
		$section_params['slug'] = self::subtab_slug;
		$this->settings->add_section( $section_params );

		$this->add_import_from_wpslp_interface();
	}

	/**
	 * Add Import From WPSLP Group
	 */
	private function add_import_from_wpslp_interface() {
		global $slplus_plugin;

		$group_params['header'      ] = __( 'Load From WordPress', 'store-locator-le' );
		$group_params['intro'       ] = sprintf( __( 'Enter the URL of a site running %s or a MySLP location connector plugin.' , 'store-locator-le' ), SLPLUS_NAME ) .
		                                '<p class="warning">' .
		                                __( 'NOTE: This will attempt to load all locations from the listed site. ' , 'store-locator-le' ) .
		                                __( 'It does not check that the locations already exist on this site. ' , 'store-locator-le' ) .
		                                __( 'Which may result in duplicate listings. ' , 'store-locator-le' ) .
		                                '</p>';
		$group_params['group_slug'  ] = 'load_from_wp';
		$group_params['section_slug'] = self::subtab_slug;
		$group_params['plugin'      ] = $slplus_plugin;
		$this->settings->add_group(  $group_params );

		$this->settings->add_ItemToGroup( array(
			'group_params' => $group_params ,
			'type'         => 'vue_component' ,
			'component'    =>  'locations_import_from_wpslp',
		) );
	}
}