<?php
/**
 * Store Locator Plus General Settings Interface
 *
 * @property    SLP_Admin_General_Text $Admin_General_Text
 * @property    SLP_Message_Manager    $Message_Manager
 * @property    SLP_Settings           $Settings
 */
class SLP_Admin_General extends SLP_Object_With_Objects {
	protected $objects = array(
		'Admin_General_Text' => array(
			'auto_instantiate' => true,
			'subdir'           => 'include/module/admin_tabs/',
		),
		'Message_Manager'    => array(
			'auto_instantiate' => true,
			'subdir'           => 'include/module/message/',
		),
		'Settings'           => array(
			'auto_instantiate' => true,
			'subdir'           => 'include/module/settings/',
		),
	);

	/**
	 * Add Admin Add On Packs group
	 *
	 * @param string $section_slug
	 */
	private function add_admin_add_on_packs_group( $section_slug ) {
		$group_params['header']       = __( 'Add On Packs', 'store-locator-le' );
		$group_params['group_slug']   = 'add_on_packs';
		$group_params['section_slug'] = $section_slug;
		$group_params['plugin']       = $this->slplus;

		if ( is_network_admin() ) {
			$group_params['intro'] =
				$this->slplus->Text->get_web_link( 'premier_subscription' ) .
				__( 'An active Premier subscription is required to properly license paid add ons for a WordPress multisite installation. ', 'store-locator-le' ) .
				sprintf( __( 'Settings that impact %s functionality are set on a per-site basis. ', 'store-locator-le' ), SLPLUS_NAME);
		}
		$this->Settings->add_group( $group_params );

		$this->add_network_aware_addon_settings( $group_params );
	}

	/**
	 * Add the Admin Locations Group
	 *
	 * @param string $section_slug
	 */
	private function add_admin_locations_group( $section_slug ) {
		$group_params['group_slug']   = 'locations';
		$group_params['section_slug'] = $section_slug;
		$group_params['plugin']       = $this->slplus;

		$this->Settings->add_group( $group_params );

		$this->Settings->add_ItemToGroup( array(
			'group_params' => $group_params,
			'option_name'  => 'user_meta',
			'option'       => 'locations_per_page',
			'type'         => 'hyperbutton',
			'button_label' => __( 'Reset Manage Locations', 'store-locator-le' ),
			'value'        => '50',
			'description'  => __( 'Reset Locations Manage tab to show 50 locations at a time.', 'store-locator-le' ),
			'onClick'      => 'SLP_ADMIN.options.change_option(this); alert("' . __( 'Manage Locations set to show 50 locations.', 'store-locator-le' ) . '"); return "10"; ',
		) );
	}

	/**
	 * General / Data
	 *
	 * @param   SLP_Settings $settings
	 */
	public function build_data_tab( $settings ) {
		$section_params['slug'] = 'data';
		$settings->add_section( $section_params );

		$group_params['group_slug']   = 'packaged_data_extensions';
		$group_params['section_slug'] = $section_params['slug'];
		$group_params['intro']        = __( 'Add pre-defined groups of extended data fields to your locations. ', 'store-locator-le' );
		$group_params['plugin']       = $this->slplus;
		$settings->add_group( $group_params );

		// Don't show the notice if these add-ons are active.
		//
		if ( $this->slplus->AddOns->get( 'slp-premier' , 'active' ) ) {
			return;
		}
		if ( $this->slplus->AddOns->get( 'slp-power' , 'active' ) ) {
			return;
		}

		// Note that some add-on packs will put settings here.
		//
		$settings->add_ItemToGroup( array(
			'group_params' => $group_params,
			'type'         => 'details',
			'custom'       => $this->slplus->Text->get_web_link( 'visit_site_for_addons' ),
		) );
	}

	/**
	 * Add on settings with multisite awareness.  Display different things depend on the WP mode.
	 *
	 * @param $group_params
	 */
	private function add_network_aware_addon_settings( $group_params ) {

		// Do not show prerelease/production or subscription settings on Multisite if SLP is network enabled
		// Unless we are on the network admin page or the main site General settings page
		if ( is_multisite() ) {

			$main_site = ( get_current_blog_id() == get_main_network_id() );
			if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}
			if ( is_plugin_active_for_network( $this->slplus->slug ) && ! $main_site && ! is_network_admin() ) {
				return;
			}
		}

		$this->add_premium_subscription_settings( $group_params );
	}

	/**
	 * Add Premium Subscription section to settings page.
	 *
	 * @param array $group_params
	 */
	function add_premium_subscription_settings( $group_params ) {

		if ( $this->slplus->AddOns->is_premier_subscription_valid() ) {
			$description = __('Your Premier Subscription has been validated.  Thank you for your continued support.' , 'store-locator-le' );
		} else {
			$description = $this->slplus->Text->get_web_link( 'premier_member_updates' ) . '<br/>' .
			               $this->slplus->Text->get_text_string( array( 'admin', 'premium_member_support' ) ) . '<br/>' .
                           ( is_wp_error( $this->slplus->AddOns->premier_subscription_error ) ? ' ' . $this->slplus->AddOns->premier_subscription_error->get_error_message() : '' )
                            ;
		}
		$this->Settings->add_ItemToGroup( array(
			'group_params' => $group_params,
			'label'        => $this->slplus->Text->get_text_string( array( 'admin', 'premium_members' ) ),
			'type'         => 'subheader',
			'description'  => $description,
		) );

		$this->Settings->add_ItemToGroup( array(
			'group_params' => $group_params,
			'option_name'  => 'options_nojs',
			'option'       => 'premium_user_id',
			'label'        => $this->slplus->Text->get_text_string( array(
				'admin',
				'premium_user_id_label',
			) ),
			'description'  => $this->slplus->Text->get_text_string( array(
				'admin',
				'premium_user_id_help',
			) ),
		) );

		$this->Settings->add_ItemToGroup( array(
			'group_params' => $group_params,
			'option_name'  => 'options_nojs',
			'option'       => 'premium_subscription_id',
			'type'         => 'password',
			'label'        => $this->slplus->Text->get_text_string( array(
				'admin',
				'premium_subscription_id_label',
			) ),
			'description'  => $this->slplus->Text->get_text_string( array(
				'admin',
				'premium_subscription_id_help',
			) ),
		) );
	}

	/**
	 * Add the General / UI / JavaScript group.
	 *
	 * @param string $section_slug
	 */
	private function add_ui_javascript_group( $section_slug ) {
		$group_params['section_slug'] = $section_slug;
		$group_params['group_slug']   = 'javascript';
		$group_params['intro']        = __( 'These settings impact how JavaScript works on your site. ', 'store-locator-le' );
		$group_params['plugin']       = $this->slplus;
		if ( $this->slplus->is_CheckTrue( $this->slplus->options_nojs['force_load_js'] ) ) {
			$group_params['intro'] .=
				'<br/>' .
				sprintf(
					__( '<a href="%s" target="csa">Force Load JavaScript</a> is ON. ', 'store-locator-le' ),
					$this->slplus->support_url . '/blog/category/general/'
				) .
				__( 'You are running a less-capable version Store Locator Plus. ', 'store-locator-le' );
		}

		$this->Settings->add_group( $group_params );

		$this->Settings->add_ItemToGroup( array(
			'group_params' => $group_params,
			'option_name'  => 'options_nojs',
			'option'       => 'ui_jquery_version',
			'disabled'     => true,
			'label'        => __( 'UI jQuery Version', 'store-locator-le' ),
			'description'  => __( 'This is the version of jQuery running on your UI.', 'store-locator-le' ),
		) );

		$this->Settings->add_ItemToGroup( array(
			'group_params' => $group_params,
			'option_name'  => 'options_nojs',
			'option'       => 'force_load_js',
			'type'         => 'checkbox',
			'label'        => __( 'Force Load JavaScript', 'store-locator-le' ),
			'description'  =>
				__( 'Force the JavaScript to load in the header of EVERY page on your site. ', 'store-locator-le' ) .
				__( 'This can slow down your site and will disable various features. ', 'store-locator-le' ) .
				__( 'If you need to do this to make SLP work you should ask your theme author to add proper wp_footer() support to their code. ', 'store-locator-le' ),
		) );

		$this->Settings->add_ItemToGroup( array(
			'group_params' => $group_params,
			'option_name'  => 'options_nojs',
			'option'       => 'no_google_js',
			'type'         => 'checkbox',
			'label'        => __( 'Turn Off SLP Maps', 'store-locator-le' ),
			'description'  => __( 'Check this box if your Theme or another plugin is providing Google Maps and generating warning messages. ', 'store-locator-le' ) .
			                  __( 'THIS MAY BREAK THIS PLUGIN.', 'store-locator-le' ),
		) );
	}

	/**
	 * Execute the save settings action.
	 */
	function save_options() {
		if ( empty( $_REQUEST ) ) return;

		/**
		 * ACTION: slp_save_generalsettings
		 *
		 * Runs before saving the general settings data.
		 *
		 * TODO: only used by EDM , can this be done another way through the base class admin_startup() maybe?
		 */
		do_action( 'slp_save_generalsettings' );

		// Serialized Checkboxes, Need To Blank If Not Received
		//
		$BoxesToHit = array(
			'force_load_js',
			'no_google_js',
		);
		foreach ( $BoxesToHit as $BoxName ) {
			if ( ! isset( $_REQUEST[ $BoxName ] ) ) {
				$_REQUEST[ $BoxName ] = '0';
			}
		}

		$this->slplus->SmartOptions->set_checkboxes( $this->Settings->current_admin_page );

		// Serialized Options Setting for stuff going into slp.js.
		// This should be used for ALL new JavaScript options.
		//
		array_walk( $_REQUEST, array( $this->slplus, 'set_ValidOptions' ) );
		if ( isset( $_REQUEST['options'] ) ) {
			array_walk( $_REQUEST['options'], array( $this->slplus, 'set_ValidOptions' ) );
		}
		$this->slplus->WPOption_Manager->update_wp_option( 'js' );

		// Serialized Options Setting for stuff NOT going to slp.js.
		// This should be used for ALL new options not going to slp.js.
		//
		array_walk( $_REQUEST, array( $this->slplus, 'set_ValidOptionsNoJS' ) );
		if ( isset( $_REQUEST['options_nojs'] ) ) {
			array_walk( $_REQUEST['options_nojs'], array( $this->slplus, 'set_ValidOptionsNoJS' ) );
		}
		$this->slplus->WPOption_Manager->update_wp_option( 'nojs' );

		// Smart Option Change Callback
		$this->slplus->SmartOptions->execute_change_callbacks();
	}

	/**
	 * Set our object options.
	 */
	protected function set_default_object_options() {
		$this->objects['Settings']['options'] = array(
			'name'        => __( 'General', 'store-locator-le' ),
			'form_action' => ( is_network_admin() ? network_admin_url() : admin_url() ) . 'admin.php?page=' . $this->slplus->clean[ 'page' ],
		);

		$this->objects['Message_Manager']['options'] = array( 'slug' => 'schedule' );
	}

	/**
	 * Build the admin tab.
	 */
	function build_admin_tab() {
		$section_params['slug'] = 'admin';

		$this->Settings->add_section( $section_params );

		if ( ! is_network_admin() ) {
			$this->add_admin_locations_group( $section_params['slug'] );
		}
		$this->add_admin_add_on_packs_group( $section_params['slug'] );
	}

	/**
	 * Build the server tab.
	 */
	function build_server_tab() {
		$section_params['slug'] = 'server';
		$this->Settings->add_section( $section_params );

		// Geocoding Settings
		//
		$group_params['header']       = __( 'Geocoding', 'store-locator-le' );
		$group_params['group_slug']   = 'geocoding';
		$group_params['section_slug'] = $section_params['slug'];
		$group_params['plugin']       = $this->slplus;
		$this->Settings->add_group( $group_params );

		$this->Settings->add_ItemToGroup( array(
			'group_params' => $group_params,
			'option_name'  => 'options_nojs',
			'option'       => 'http_timeout',
			'type'         => 'list',
			'custom'       => array(
				'Slow'   => '30',
				'Normal' => '10',
				'Fast'   => '3',
			),
			'label'        => __( 'Server-To-Server Speed', 'store-locator-le' ),
			'description'  =>
				__( 'How fast is your server when communicating with other servers like Google? ', 'store-locator-le' ) .
				__( 'Set this to slow if you get frequent geocoding errors but geocoding works sometimes. ', 'store-locator-le' ) .
				__( 'Set this to fast if you never have geocoding errors and are bulk loading more than 100 locations at a time. ', 'store-locator-le' ),
		) );

		$this->Settings->add_ItemToGroup( array(
			'group_params' => $group_params,
			'option_name'  => 'options_nojs',
			'option'       => 'geocode_retries',
			'type'         => 'list',
			'custom'       => array(
				'None' => 0,
				'1'    => '1',
				'2'    => '2',
				'3'    => '3',
				'4'    => '4',
				'5'    => '5',
				'6'    => '6',
				'7'    => '7',
			),
			'label'        => __( 'Geocode Retries', 'store-locator-le' ),
			'description'  =>
				__( 'How many times should we try to set the latitude/longitude for a new address? ', 'store-locator-le' ) .
				__( 'Higher numbers mean slower bulk uploads. ', 'store-locator-le' ) .
				__( 'Lower numbers make it more likely the location will not be set during bulk uploads. ', 'store-locator-le' ),
		) );

		$this->Settings->add_ItemToGroup( array(
			'group_params' => $group_params,
			'option_name'  => 'options_nojs',
			'option'       => 'retry_maximum_delay',
			'label'        => __( 'Maximum Retry Delay', 'store-locator-le' ),
			'description'  => __( 'Maximum time to wait between retries, in seconds. ', 'store-locator-le' ) .
			                  __( 'Use multiples of 1. ', 'store-locator-le' ) .
			                  __( 'Recommended value is 5. ', 'store-locator-le' ),
		) );
	}

	/**
	 * Add the schedule messages group.
	 *
	 * @param SLP_Settings $settings
	 */
	public function build_schedule_tab( $settings ) {
		$group_params = array(
			'plugin'       => $this->slplus,
			'section_slug' => 'schedule',
			'group_slug'   => 'messages',
		);

		$this->instantiate( 'Message_Manager' );

		$settings->add_ItemToGroup( array(
			'group_params' => $group_params,
			'type'         => 'details',
			'label'        => 'Schedule Messages',
			'custom'       => $this->Message_Manager->get_message_string(),
		) );

		if ( $this->Message_Manager->exist() ) {
			$clear_text = __( 'Clear schedule messages.', 'store-locator-le' );
			$settings->add_ItemToGroup( array(
				'group_params' => $group_params,
				'type'         => 'hyperbutton',
				'button_label' => $clear_text,
				'id'           => 'schedule_messages_clear',
				'onClick'      => 'SLP_GENERAL.messages.clear_schedule_messages()',
			) );
		}
	}

	/**
	 * Build the User Settings Panel
	 */
	function build_user_interface_tab() {
		$section_params['slug'] = 'user_interface';

		$this->Settings->add_section( $section_params );

		$this->add_ui_javascript_group( $section_params['slug'] );

		/**
		 * ACTION: slp_generalsettings_modify_userpanel
		 *
		 * Modify the General / User Interface Subtab
		 *
		 * @used-by SLP_Premier_Admin_General::initialize()
		 *
		 * @param   SLP_Settings $settings
		 * @param   string       $section_name
		 * @param   array        $section_params ['name'] = string of name, ['slug'] = string of unique slug
		 */
		do_action( 'slp_generalsettings_modify_userpanel', $this->Settings, $this->Settings->sections[ $section_params['slug'] ]->name, $section_params );
	}

	/**
	 * Render the map settings admin page.
	 */
	public function display() {
		$section_params['slug']        = 'navigation';
		$section_params['name']        = __( 'Navigation', 'store-locator-le' );
		$section_params['description'] = SLP_Admin_UI::get_instance()->create_Navbar();
		$section_params['div_id']      = 'navbar_wrapper';
		$section_params['innerdiv']    = false;
		$section_params['is_topmenu']  = true;
		$section_params['auto']        = false;

		$this->Settings->add_section( $section_params );

		// Show this on main site for MySLP
		add_action( 'slp_build_general_settings_panels' , array( $this , 'build_admin_tab' ) , 10 );

		// Do not show all this on network admin interface.
		//
		if ( ! is_network_admin() ) {
			add_action( 'slp_build_general_settings_panels', array( $this, 'build_user_interface_tab' ), 20 );
			add_action( 'slp_build_general_settings_panels', array( $this, 'build_server_tab' ), 30 );
			add_action( 'slp_build_general_settings_panels', array( $this, 'build_data_tab' ), 50 );
			add_action( 'slp_build_general_settings_panels', array( $this, 'build_schedule_tab' ), 60 );
		}

		/**
		 * ACTION: slp_build_general_settings_panels
		 *
		 * Modify the General Tab
		 *
		 * @param   SLP_Settings $settings
		 */
		do_action( 'slp_build_general_settings_panels', $this->Settings );

		$this->Settings->render_settings_page();
	}
}