<?php
defined( 'ABSPATH'     ) || exit;
require_once( SLPLUS_PLUGINDIR . 'include/module/admin_tabs/SLP_BaseClass_Admin.php' );

/**
 * Store Locator Plus basic admin user interface.
 *
 * @property-read   boolean                $already_enqueue    True if admin stylesheet enqueued.
 *
 * @property        string[]                $cache             Run time cache to make things faster.
 *                                                             used by \SLP_BaseClass_Admin::ok_to_enqueue_admin_js
 *
 * @property-read   boolean                $isOurAdminPage     True if we are on an admin page for the plugin.
 * @property        string[]               $admin_slugs        The registered admin page hooks for the plugin.
 * @property-read    SLP_Admin_General     $Admin_General      General Tab Manager
 * @property-read    SLP_Admin_Info        $Admin_Info         Info Tab Manager
 * @property        string                 $styleHandle
 *
 * @property        SLP_Admin_Locations    $ManageLocations    // TODO: remove when all ->AdminUI->ManageLocations reference slplus->Admin_Locations (EM, PRO, TAG, PAGES, POW,
 */
class SLP_Admin_UI extends SLP_BaseClass_Admin {
	protected $objects = array(
		'Admin_General'      => array( 'subdir' => 'include/module/admin_tabs/' ),
		'Admin_Info'         => array( 'subdir' => 'include/module/admin_tabs/' ),
	);
	public $cache = array(
		'admin_pages'   => array(),
	);

	public $menu_items = array();

	private $already_enqueued  = false;
	public  $Experience;

	private $icon_selector_files;
	private $icon_selector_urls;

	private $is_our_admin_page = false;
	public  $ManageLocations;
	private $slp_admin_slugs   = array(
		'toplevel_page_slp-network-admin',
		'slp_general',
		'settings_page_csl-slplus-options',
		'slp_general',
		'slp_info',
		'slp_manage_locations',
		'slp_experience',
	);

	public  $styleHandle;

	/**
	 * Add hooks and filters.
	 */
	public function add_hooks_and_filters() {
		parent::add_hooks_and_filters();
		add_filter( 'all_plugins' , array( $this , 'add_plugin_list_icon' ) );
	}

	/**
	 * Add icons to our premium plugins.
	 *
	 * @param array $plugin_data
	 *
	 * @return mixed
	 */
	public function add_plugin_list_icon( $plugin_data ) {
		foreach ( $plugin_data as $slug => $meta ) {
			if ( strpos( $slug , 'slp-' ) !== 0 ) continue;
			if ( $meta[ 'Author' ] !== 'Store Locator Plus™' ) continue;
			$plugin_data[ $slug ][ 'icons' ][ 'default' ] = SLPLUS_PLUGINURL . '/images/slp_240x240.png';
		}
		return $plugin_data;
	}

	/**
	 * Add filters to save/restore important settings for the Janitor reset.
	 */
	private function add_janitor_hooks() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if ( ! function_exists( 'is_plugin_active' ) || ! is_plugin_active( 'slp-janitor/slp-janitor.php' ) ) {
			return;
		}
		add_filter( 'slp_janitor_important_settings', array( $this, 'set_janitor_important_fields' ) );
		add_action( 'slp_janitor_restore_important_setting', array(
			$this,
			'restore_janitor_important_fields',
		), 5, 2 );
	}

	/**
	 * Create the admin menu.
	 *
	 * Roles and Caps
	 * manage_slp_admin
	 * manage_slp_user
	 *
	 * WordPress Store Locator Plus Menu Roles & Caps
	 *
	 * Info : manage_slp_admin
	 * Locations: manage_slp_user
	 * Settings: manage_slp_admin
	 * General: manage_slp_admin
	 */
	public function create_admin_menu() {
		do_action( 'slp_admin_menu_starting' );

		// The main hook for the menu
		//
		$slp_menu_name = apply_filters( 'slp_admin_menu_text' , SLPLUS_NAME );
		$this->slplus->admin_page_prefix = sanitize_title( $slp_menu_name ) . '_page_';
		$main_hook = add_menu_page( $slp_menu_name, $slp_menu_name, 'manage_slp',  SLPLUS_PREFIX, array( $this, 'renderPage_GeneralSettings' ), SLPlus::menu_icon , 31 );

		// Default menu items
		//
		$this->menu_items['slp_manage_locations'] =
			array(
				'label'          => __( 'Locations', 'store-locator-le' ),
				'slug'           => 'slp_manage_locations',
				'class'          => $this,
				'function'       => 'renderPage_Locations',
			    'screen_options' => 'slp_manage_locations_screen_options' ,
			);
		$this->menu_items['slp_experience'] =
			array(
				'label'    => __( 'Settings', 'store-locator-le' ),
				'slug'     => 'slp_experience',
				'class'    => $this,
				'function' => 'render_experience_tab'
			);
		$this->menu_items['slp_general'] =
			array(
				'label'    => __( 'General', 'store-locator-le' ),
				'slug'     => 'slp_general',
				'class'    => $this,
				'function' => 'renderPage_GeneralSettings'
			);

		add_filter( 'slp_menu_items' , array( $this , 'add_info_menu' ) , 90 );

		// Third party plugin add-ons
		//
		$new_menu_items = apply_filters( 'slp_menu_items', $this->menu_items );
		foreach( $new_menu_items as $slug => $menu_item ) {
			if ( ! array_key_exists( $slug , $this->menu_items )  ){
				$this->menu_items[ $menu_item['slug'] ] = $menu_item;
			}
		}



		// Attach Menu Items To Sidebar and Top Nav
		//
		foreach ( $this->menu_items as $slug => $menu_item ) {

			// Sidebar connect...
			//
			// Differentiate capability for User Managed Locations
			if ( $menu_item['label'] == __( 'Locations', 'store-locator-le' ) ) {
				$slpCapability = 'manage_slp_user';
			} else {
				$slpCapability = 'manage_slp_admin';
			}

			// Using class names (or objects)
			//
			if ( isset( $menu_item['class'] ) ) {
				$this->menu_items[ $slug ][ 'hook' ] = add_submenu_page(
					SLPLUS_PREFIX,
					$menu_item['label'],
					$menu_item['label'],
					$slpCapability,
					$menu_item['slug'],
					array( $menu_item['class'], $menu_item['function'] )
				);
				if ( ! empty( $this->menu_items[ $slug ][ 'screen_options' ] ) ) {
					add_action( 'load-' . $this->menu_items[ $slug ][ 'hook' ] , array( $this , $this->menu_items[ $slug ][ 'screen_options' ] ) );
				}

				// Full URL or plain function name
				//
			} else {
				if ( isset( $menu_item['url'] ) && isset( $menu_item['label'] ) ) {
					$this->menu_items[ $slug ][ 'hook' ] =  add_submenu_page(
						SLPLUS_PREFIX,
						$menu_item['label'],
						$menu_item['label'],
						$slpCapability,
						$menu_item['url']
					);

				}
			}
		}

		$this->menu_items[ SLPLUS_PREFIX ][ 'hook' ] = $main_hook;

		// Remove the duplicate menu entry
		//
		remove_submenu_page( SLPLUS_PREFIX, SLPLUS_PREFIX );
	}

	/**
	 * @param $menu_entries
	 *
	 * @return array
	 */
	public function add_info_menu ( $menu_entries ) {
		$menu_entries[ 'slp_info' ] = array(
			'label'    => __( 'Info', 'store-locator-le' ),
			'slug'     => 'slp_info',
			'class'    => $this,
			'function' => 'render_info_tab'
		);
		return $menu_entries;
	}

	/**
	 * Invoke the AdminUI class.
	 */
	function initialize() {
		$this->addon = $this->slplus;
		$this->slplus->AdminUI = $this;
		parent::initialize();
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_stylesheet' ), 5 );

		if ( ! empty( $this->slplus->clean[ 'page' ] ) && ( $this->slplus->clean[ 'page' ] === 'slp_manage_locations' ) ) {
			add_action( 'admin_enqueue_scripts', array( $this->slplus, 'enqueue_google_maps_script' ) );

		}

		$this->styleHandle = $this->slplus->styleHandle;
		$this->add_janitor_hooks();

		// Called after admin_menu and admin_init when the current screen info is available.
		//
		add_action( 'current_screen', array( $this, 'setup_admin_screen' ) );

		/**
		 * HOOK: slp_admin_init_complete
		 */
		do_action( 'slp_admin_init_complete' );
	}

	/**
	 * Sets $this->isOurAdminPage true if we are on a SLP managed admin page.  Returns true/false accordingly.
	 *
	 * @param string $hook
	 *
	 * @return boolean
	 */
	function is_our_admin_page( $hook ) {
		if ( ! is_admin() ) {
			$this->is_our_admin_page = false;
			return false;
		}

		// Our Admin Page : true if we are on the admin page for this plugin
		// or we are processing the update action sent from this page
		//
		$this->is_our_admin_page = ( $hook == SLPLUS_PREFIX . '-options' ) || ( $hook === 'slp_info' ) || strpos( $hook , SLP_ADMIN_PAGEPRE ) === 0 ;
		if ( $this->is_our_admin_page ) return true;

		/**
		 * Check our menu hooks.
		 */
		foreach ($this->menu_items as $slug => $meta ) {
			if ( $meta[ 'hook' ] === $hook ) {
				$this->is_our_admin_page = true;
				return true;
			}
		}

		// Request Action is "update" on option page
		$this->is_our_admin_page = ! empty( $this->slplus->clean[ 'action' ] ) &&
		                           ( $this->slplus->clean[ 'action' ] === 'update' ) &&
		                           ! empty( $this->slplus->clean[ 'option_page' ] ) &&
		                           ( substr( $this->slplus->clean[ 'option_page' ], 0, strlen( SLPLUS_PREFIX ) ) === SLPLUS_PREFIX );
		if ( $this->is_our_admin_page ) return true;

		// This test allows for direct calling of the options page from an
		// admin page call direct from the sidebar using a class/method
		// operation.
		//
		// To use: pass an array of strings that are valid admin page slugs for
		// this plugin.  You can also pass a single string, we catch that too.
		//
		$this->is_our_admin_page =  in_array( $hook , apply_filters( 'wpcsl_admin_slugs', $this->slp_admin_slugs ) );

		return $this->is_our_admin_page;
	}

	/**
	 * Make options_nojs a setting we want to process during janitor reset settings.
	 *
	 * @param $field_array
	 *
	 * @return array
	 */
	public function set_janitor_important_fields( $field_array ) {
		return array_merge( $field_array, array( 'csl-slplus-options_nojs' ) );
	}

	/**
	 * @param $option_name
	 * @param $saved_setting
	 */
	public function restore_janitor_important_fields( $option_name, $saved_setting ) {
		if ( $option_name === 'csl-slplus-options_nojs' ) {
			$this->slplus->options_nojs                      = $this->slplus->WPOption_Manager->get_wp_option( 'nojs' );
			$this->slplus->options_nojs['next_field_id']     = $saved_setting['next_field_id'];
			$this->slplus->options_nojs['next_field_ported'] = $saved_setting['next_field_ported'];
			$this->slplus->WPOption_Manager->update_wp_option( 'nojs', $this->slplus->options_nojs );
		}
	}

	/**
	 * Build a query string of the add-on packages.
	 *
	 * @return string
	 */
	public function create_addon_query() {
		$addon_slugs    = array_keys( $this->slplus->AddOns->instances );
		$addon_versions = array();
		foreach ( $addon_slugs as $addon_slug ) {
			if ( is_object( $this->slplus->AddOns->instances[ $addon_slug ] ) ) {
				$addon_versions[ $addon_slug . '_version' ] = $this->slplus->AddOns->instances[ $addon_slug ]->options['installed_version'];
			}
		}

		return
			http_build_query( $addon_slugs, 'addon_' ) . '&' .
			http_build_query( $addon_versions );
	}

	/**
	 * Render the admin page navbar (tabs)
	 *
	 * @global mixed[] $submenu the WordPress Submenu array
	 *
	 * @return string
	 */
	public function create_Navbar() {
		global $submenu;
		$navbar_items = empty( $submenu[ SLPLUS_PREFIX ] ) ? null : $submenu[ SLPLUS_PREFIX ];
		if ( ! is_array( $navbar_items ) || empty( $navbar_items ) ) {
			return '';
		}

		$li_classes = array();
		foreach ( $this->menu_items as $slug=>$menu ) {
			if ( empty ( $menu[ 'label' ] ) ) continue;
			$slug = sanitize_key( $menu['label'] );
			$li_classes[ $slug ] = ! empty( $menu['css_class'] ) ? $menu['css_class'] : '';
		}

		$content =
			'<header id="myslp-header" class="panel-navbar">' .
			'<ul class="navbar">';

		// Loop through all SLP sidebar menu items on admin page
		// slp_menu_item [0] = name , [1] = cap , [2] = slug || url , [3] = name
		//
		foreach ( $navbar_items as $slp_menu_item ) {
			$slug = sanitize_key( $slp_menu_item[0] );
			$li_class = ! empty( $li_classes[ $slug ] ) ? $li_classes[ $slug ] : '';

			$current_class = ( ( ! empty( $this->slplus->clean[ 'page' ] ) && ( $this->slplus->clean[ 'page' ] === $slp_menu_item[2] ) ) ? 'current' : '' );

			if ( ! preg_match('/^[a-z0-9-]+?\.php/i', $slp_menu_item[2]) ) {
				$item_url = menu_page_url( $slp_menu_item[2], false );
				$menu_class = sanitize_key( $slp_menu_item[2] );
			} else {
				$item_url = admin_url( $slp_menu_item[2] );
				$menu_class = sanitize_key( str_replace( ' ' , '-' , $slp_menu_item[0] ) );
			}
			$hyperlink = "<a class='navbar-link {$menu_class}' href='{$item_url}'>{$slp_menu_item[0]}</a>";

			$content .= "<li class='navbar-item {$menu_class} {$li_class} {$current_class}'>{$hyperlink}</li>";
		}

		$content .= '</ul><div class="alert_box">' . $this->slplus->notifications->get_html() . '</div></header>';

		return $content;
	}

	/**
	 * Return the icon selector HTML for the icon images in saved markers and default icon directories.
	 *
	 * @used-by \SLP_Settings_icon::get_content
	 * @used-by \SLP_Power_Category_Manager::render_ExtraCategoryFields
	 * @used-by \MySLP_Admin::admin_hook
	 * @used-by \MySLP_Admin::get_icons_array
	 *
	 * @param string|null $field_id
	 * @param string|null $image_id
	 *
	 * @return string
	 */
	public function create_string_icon_selector( $field_id = null, $image_id = null ) {
		if ( ( $field_id == null ) || ( $image_id == null ) ) {
			return '';
		}

		$htmlStr = '';
		$files   = array();
		$fqURL   = array();

		// If we already got a list of icons and URLS, just use those
		//
		if ( isset( $this->icon_selector_files ) && isset( $this->icon_selector_urls ) ) {
			$files = $this->icon_selector_files;
			$fqURL = $this->icon_selector_urls;

			// If not, build the icon info but remember it for later
			// this helps cut down looping directory info twice (time consuming)
			// for things like home and end icon processing.
			//
		} else {

			// Load the file list from our directories
			//
			// using the same array for all allows us to collapse files by
			// same name, last directory in is highest precedence.
			$iconAssets = apply_filters( 'slp_icon_directories',
				array(
					array(
						'dir' => SLPLUS_UPLOADDIR . 'saved-icons/',
						'url' => SLPLUS_UPLOADURL . 'saved-icons/',
					),
					array(
						'dir' => SLPLUS_ICONDIR,
						'url' => SLPLUS_ICONURL,
					),
				)
			);
			$fqURLIndex = 0;
			foreach ( $iconAssets as $icon ) {
				if ( is_dir( $icon['dir'] ) ) {
					if ( $iconDir = opendir( $icon['dir'] ) ) {
						$fqURL[] = $icon['url'];
						while ( $filename = readdir( $iconDir ) ) {
							if ( strpos( $filename, '.' ) === 0 ) {
								continue;
							}
							$files[ $filename ] = $fqURLIndex;
						};
						closedir( $iconDir );
						$fqURLIndex ++;
					} else {
						$this->slplus->notifications->add_notice(
							9,
							sprintf(
								__( 'Could not read icon directory %s', 'store-locator-le' ),
								$icon['dir']
							)
						);
					}
				}
			}
			ksort( $files );
			$this->icon_selector_files = $files;
			$this->icon_selector_urls  = $fqURL;
		}

		// Build our icon array now that we have a full file list.
		//
		foreach ( $files as $filename => $fqURLIndex ) {
			if (
				( preg_match( '/\.(png|gif|jpg)/i', $filename ) > 0 ) &&
				( preg_match( '/shadow\.(png|gif|jpg)/i', $filename ) <= 0 )
			) {
				$htmlStr .= <<<HTML
					<div class='slp_icon_selector_box'>
					<img data-filename='{$filename}' class='slp_icon_selector' src='{$fqURL[$fqURLIndex]}{$filename}' />
					</div>
HTML;

			}
		}

		// Wrap it in a div
		//
		if ( $htmlStr != '' ) {
			$htmlStr = '<div id="' . $field_id . '_icon_row" class="slp_icon_row">' . $htmlStr . '</div>';

		}

		return $htmlStr;
	}

	/**
	 * Enqueue the admin stylesheet when needed.
	 *
	 * @param string $hook Current page hook.
	 */
	public function enqueue_admin_stylesheet( $hook ) {
		if ( ! $this->is_our_admin_page( $hook ) || $this->already_enqueued ) {
			return;
		}

		$this->slplus->load_jquery_theme( 'base' );
		$this->enqueue_admin_css( $hook );
		wp_enqueue_style( 'font-awesome', SLPLUS_PLUGINURL . '/css/admin/font-awesome.min.css' );

		$this->already_enqueued = true;
	}

	/**
	 * Render the experience tab.
	 */
	function render_experience_tab() {
		SLP_Admin_Settings_Tab::get_instance()->display();
	}

	/**
	 * Render the Info page.
	 *
	 */
	function render_info_tab() {
		$this->Admin_Info->display();
	}

	/**
	 * Render the General Settings page.
	 * @used-by create_admin_menu()
	 */
	public function renderPage_GeneralSettings() {
		$this->instantiate( 'Admin_General' );
		$this->Admin_General->display();
	}

	/**
	 * Render the Locations admin page.
	 */
	function renderPage_Locations() {
		$this->slplus->Admin_Locations->display();
	}

	/**
	 * Attach the wanted screen object and save the settings if appropriate.
	 *
	 * @param   WP_Screen $current_screen The current screen object.
	 */
	function setup_admin_screen( $current_screen ) {
		switch ( $current_screen->id ) {

			// Experience Tab
			//
			case $this->slplus->admin_page_prefix . 'slp_experience':
				if ( isset( $_POST ) && ! empty( $_POST ) ) {
					SLP_Admin_Settings_Tab::get_instance()->save_options();
				}
				break;

			// General Tab
			//
			case 'toplevel_page_slp-network-admin-network':
			case $this->slplus->admin_page_prefix . 'slp_general':
				$this->instantiate( 'Admin_General' );
				if ( isset( $_POST ) && ! empty( $_POST ) ) {
					$this->Admin_General->save_options();
				}
				break;

			// Info Tab
			//
			case $this->slplus->admin_page_prefix . 'slp_info':
				$this->instantiate( 'Admin_Info' );
				break;

			// Locations Tab
			case $this->slplus->admin_page_prefix . 'slp_manage_locations':
				require_once( SLPLUS_PLUGINDIR . 'include/module/admin_tabs/SLP_Admin_Locations.php' );
				$this->slplus->Admin_Locations->screen = $current_screen->id;
				$this->ManageLocations                 = $this->slplus->Admin_Locations;    // TODO: remove when all ->AdminUI->ManageLocations reference slplus->Admin_Locations (EM, PRO, TAG, PAGES, POW)
				break;

			// Unknown
			//
			default:
				break;
		}
	}

	/**
	 * Add Manage Locations Screen Options
	 */
	public function slp_manage_locations_screen_options() {
		$this->slplus->Admin_Locations->add_screen_options();
	}
}
