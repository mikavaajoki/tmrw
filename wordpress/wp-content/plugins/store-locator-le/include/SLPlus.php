<?php
if ( ! class_exists( 'SLPlus' ) ) {
	require_once( SLPLUS_PLUGINDIR . 'include/base_class.object.php' );
	require_once( SLPLUS_PLUGINDIR . 'include/base/SLP_Object_With_Objects.php' );

	/**
	 * The base plugin class for Store Locator Plus.
	 *
	 * @property        SLP_Admin_Activation                    Activation
	 * @property        string                            admin_page_prefix              Admin page prefix, needs to be changed if the
	 * @property        array                             clean                          Sanitized stuff we care about.
	 * @property        SLP_BaseClass_Addon               current_addon                  The current addon being processed.
	 * @property        SLPlus_Location                   currentLocation                The current location.
	 * @property        SLP_Data                          database                       The data interface helper.
	 * @property        wpdb                              db                             The global $wpdb object for WordPress.
	 * @property        string                            dir                            Full path to this plugin directory.
	 * @property        array                             infoFetched                    Array of slugs + booleans for plugins we've already fetched info for. named array, key = slug, value = true
	 * @property        string                            installed_version              The version that was installed at the start of the plugin (prior installed version).
	 * @property        boolean                           javascript_is_forced           Quick reference for the Force Load JavaScript setting.
	 * @property-read   int                               main_id                        The main blog ID.
	 * @property        string                            name                           TODO: deprecate when no longer referenced use SLPLUS_NAME constant. (ELM, SME, UML, J, PAGES )
	 * @property        array                             options                        The options that the user has set for Store Locator Plus that get localized to the slp.js script.
	 * @property        array                             options_default                The default options (before being read from DB)
	 * @property        array                             options_nojs                   The options that the user has set for Store Locator Plus that are NOT localized to the slp.js script.
	 * @property        array                             options_nojs_default           The default options_nojs (before being read from DB).
	 * @property        string                            plugin_url                     The URL that reaches the home directory for the plugin.
	 * @property-read   SLP_REST_Handler                  rest_handler                   The WP REST API handler.
	 * @property        bool                              shortcode_was_rendered
	 * @property        boolean                           slider_rendered                True if slider was rendered, preventing multiple inline CSS calls.
	 * @property        string                            slp_store_url                  The SLP Store URL.
	 * @property        string                            slug                           What slug do we go by?    May need to exists if we "eat our own food" extending base classes
	 * @property        string                            support_url                    The SLP Support Site URL.
	 * @property        SLP_Notifications_Manager         notifications
	 * @property        string                            prefix                         TODO: deprecate when all references use the SLPLUS_PREFIX constant (MUP, ELM, GFI, SME, UML)
	 * @property        string                            url                            Full URL to this plugin directory.
	 *
	 * @property        array                             objects                        Objects we care about, similar to objects of objects.
	 *
	 * @property        SLP_AddOns                        add_ons                        TODO: remove when all ->add-ons references are ->AddOns Remove  (MUP, CEX, ELM, GFI, J, SMI, UML)
	 * @property        SLP_AJAX                          ajax                           TODO: remove when all references are ->AJAX not ->ajax
	 * @property        SLP_AJAX                          AjaxHandler                    TODO: remove when all things use ->AJAX (GFI , GFL , UML)
	 * @property        SLP_Admin_Helper                  helper                         TODO: remove when all ->helper references are ->Helper (MySLP-Dashboard, ELM, J, SME )
	 * @property        SLP_Admin_UI                      AdminUI                        TODO: remove when all ->AdminUI references are Use SLP_Admin_UI::get_instance() for Admin_UI references. (GFI ,
	 * @property        SLP_SmartOptions                  smart_options                  TODO: remove when all ->smart_options references are ->SmartOptions (MySLP-Dashboard)
	 *
	 *
	 * -- SLP
	 * @property        SLP_Actions                       Actions                        Manages high-level WordPress action setup.
	 * @property        SLP_AddOns                        AddOns                         Manager our add on objects and meta.
	 * @property        SLP_Admin_Locations               Admin_Locations                Locations admin interface.
	 * @property        SLP_Admin_Locations_Add           Admin_Locations_Add            Manage location add UX.
	 * @property        SLP_AJAX                          AJAX
	 * @property        SLP_Country_Manager               Country_Manager
	 * @property        SLP_Admin_Helper                  Helper                         Methods that handle generic functions we use in a few classes.
	 * @property        SLP_Internet_Helper               Internet_Helper                Assist with Internet queries like remote gets and JSON parsing.
	 * @property        SLP_Location_Manager              Location_Manager               Higher level location manager things like initial distance updater and total location count management.
	 * @property        SLP_SmartOptions                  SmartOptions                   The new settings interface for this plugin.
	 * @property        SLP_Text                          Text                           The text manager to corral i18n/l10n gettext stuff.
	 * @property        SLP_UI                            UI                             The front-end user interface functions.
	 * @property        SLP_WPOption_Manager              WPOption_Manager               Augment the WordPress get/update/delete option functions with fiters.
	 *
	 * -- Experience Plugin
	 * @property        SLP_Experience_Admin_Locations    Experience_Admin_Locations
	 *
	 * -- Premier Plugin
	 * @property        SLP_Premier_Category              Premier_Category
	 * @property        SLP_Premier_Category_UI           Premier_Category_UI
	 * @property        SLP_Premier_URL_Control           Premier_URL_Control
	 *
	 * -- Power Plugin
	 * @property        SLP_Power_Admin_General           Power_Admin_General
	 * @property        SLP_Power_Admin_General_Text      Power_Admin_General_Text
	 * @property        SLP_Power_Admin_Locations         Power_Admin_Locations
	 * @property        SLP_Power_Admin_Locations_Actions Power_Admin_Locations_Actions
	 * @property        SLP_Power_Admin_Location_Filters  Power_Admin_Location_Filters
	 * @property        SLP_Power_AJAX                    Power_AJAX
	 * @property        SLP_Power_Text                    Power_Text
	 * @property        SLP_Power_Category_Manager        Power_Category_Manager
	 */
	class SLPlus extends SLP_Object_With_Objects {
		const earth_radius_km  = 6371;         // Earth Volumetric Mean Radius (km)
		const earth_radius_mi  = 3959;         // Earth Volumetric Mean Radius (km)
		const locationPostType = 'store_page'; // Define the location post type.
		const locationTaxonomy = 'stores';     // Define the location post taxonomy.
		const menu_icon        = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyMC4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHZpZXdCb3g9IjAgMCAzMy4zIDQyLjkiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDMzLjMgNDIuOTsiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPHN0eWxlIHR5cGU9InRleHQvY3NzIj4NCgkuc3Qwe2ZpbGw6dXJsKCNTVkdJRF8xXyk7fQ0KPC9zdHlsZT4NCjx0aXRsZT5TTFAtbG9nby1zbWFsbC1jb2xvcjwvdGl0bGU+DQo8bGluZWFyR3JhZGllbnQgaWQ9IlNWR0lEXzFfIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9IjMzNy43ODY3IiB5MT0iLTI4Ny40ODIyIiB4Mj0iMzY5LjA5NjYiIHkyPSItMjg3LjQ4MjIiIGdyYWRpZW50VHJhbnNmb3JtPSJtYXRyaXgoMSAwIDAgLTEgLTMzNi42MiAtMjY1Ljk4NjcpIj4NCgk8c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojRUY1MzIzIi8+DQoJPHN0b3AgIG9mZnNldD0iMC4xNiIgc3R5bGU9InN0b3AtY29sb3I6I0UwNDkyNiIvPg0KCTxzdG9wICBvZmZzZXQ9IjAuNDMiIHN0eWxlPSJzdG9wLWNvbG9yOiNDRDNDMkEiLz4NCgk8c3RvcCAgb2Zmc2V0PSIwLjcxIiBzdHlsZT0ic3RvcC1jb2xvcjojQzIzNTJDIi8+DQoJPHN0b3AgIG9mZnNldD0iMSIgc3R5bGU9InN0b3AtY29sb3I6I0JFMzIyRCIvPg0KPC9saW5lYXJHcmFkaWVudD4NCjxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0zMi41LDE3LjJjMC00LjgtMi4yLTkuMy01LjktMTIuM2MtMC42LTAuNS0xLjUtMC40LTIsMC4yYzAsMCwwLDAsMCwwbC0xLjEsMS40bDAsMGwtMS44LDIuM2wtMC4xLTAuMQ0KCWMtMC40LTAuMi0wLjgtMC40LTEuMi0wLjZjLTAuNy0wLjMtMS40LTAuNS0yLjItMC42Yy0wLjMtMC4xLTAuNy0wLjEtMS0wLjFjLTAuNCwwLTAuOCwwLTEuMSwwYy0wLjMsMC0wLjYsMC4xLTAuOSwwLjENCgljLTAuNCwwLjEtMC43LDAuMS0xLDAuMkMxMy44LDgsMTMuNCw4LjIsMTMsOC40Yy0wLjMsMC4xLTAuNiwwLjMtMC45LDAuNWMtMC40LDAuMi0wLjgsMC41LTEuMSwwLjhjLTAuNCwwLjMtMC44LDAuNy0xLjIsMS4xDQoJYy0wLjYsMC43LTEuMSwxLjQtMS41LDIuM2MtMC4yLDAuNC0wLjQsMC45LTAuNSwxLjNjLTAuMSwwLjItMC4xLDAuNC0wLjIsMC42djAuMWMwLDAuMi0wLjEsMC40LTAuMSwwLjdzMCwwLjQtMC4xLDAuNg0KCWMtMC4xLDAuNS0wLjEsMSwwLDEuNWMwLjEsMC41LDAuNSwwLjgsMC45LDAuOGgxNC41bDAsMGMtMC4xLDAuNC0wLjMsMC44LTAuNCwxLjJjLTAuMiwwLjQtMC40LDAuNy0wLjYsMWMtMC4yLDAuMy0wLjUsMC42LTAuNywwLjgNCgljLTAuMiwwLjItMC41LDAuNC0wLjcsMC41Yy0wLjMsMC4yLTAuNywwLjQtMSwwLjVjLTAuNCwwLjItMC44LDAuMy0xLjIsMC40Yy0wLjQsMC4xLTAuOCwwLjEtMS4yLDAuMWMtMC4zLDAtMC43LDAtMSwwDQoJYy0wLjctMC4xLTEuNC0wLjMtMi4xLTAuNmwtMC40LTAuMmMtMC40LTAuMi0wLjgtMC41LTEuMS0wLjljLTAuMi0wLjItMC40LTAuMy0wLjctMC4zYy0wLjIsMC0wLjUsMC4xLTAuNiwwLjNsLTAuOCwwLjhsLTAuMSwwLjENCgljLTAuMSwwLjEtMC4xLDAuMS0wLjIsMC4ybC0yLjcsMi43bC0wLjEtMC4xbC0wLjEtMC4xTDcsMjQuOWMtMS45LTIuMy0yLjktNS4zLTIuNy04LjNDNC41LDExLDguNSw2LjIsMTQuMSw0LjkNCgljMS44LTAuNCwzLjctMC40LDUuNSwwYzAuNywwLjIsMS40LTAuMSwxLjgtMC42YzAuNS0wLjcsMC40LTEuNi0wLjItMi4xQzIxLDIuMSwyMC44LDIsMjAuNiwxLjljLTguNC0yLjEtMTYuOSwzLTE5LDExLjQNCgljLTEuMiw0LjgtMC4xLDkuOCwzLDEzLjZsMCwwTDYsMjguNmMwLjUsMC42LDEuNSwwLjcsMi4xLDAuMmMwLDAsMC4xLTAuMSwwLjEtMC4xTDkuOSwyN2wwLDBsMi0yYzAuMiwwLjEsMC41LDAuMywwLjgsMC40DQoJYzAuOCwwLjQsMS43LDAuNywyLjUsMC44YzAuNCwwLjEsMC44LDAuMSwxLjIsMC4xaDAuNGMwLjcsMCwxLjMtMC4xLDItMC4yYzAuNC0wLjEsMC44LTAuMiwxLjItMC4zYzEtMC40LDEuOS0wLjksMi44LTEuNQ0KCWMwLjctMC41LDEuMi0xLjEsMS43LTEuOGMwLjUtMC42LDAuOC0xLjMsMS4xLTIuMWMwLjItMC41LDAuMy0wLjksMC40LTEuNGMwLjEtMC4zLDAuMS0wLjYsMC4xLTAuOWMwLTAuMywwLTAuNiwwLjEtMXYtMC4xDQoJYzAtMC4yLDAtMC40LDAtMC41YzAtMC4xLDAtMC4xLDAtMC4yaC0wLjhsMC44LDBjMC0wLjUtMC4zLTAuOS0wLjgtMC45Yy0wLjEsMC0wLjEsMC0wLjIsMGgtNi42Yy0wLjEsMC0wLjIsMC0wLjMsMGgtNy43bDAuMS0wLjINCgljMC40LTEuNCwxLjMtMi43LDIuNi0zLjVjMC43LTAuNCwxLjQtMC43LDIuMi0wLjljMC4zLTAuMSwwLjctMC4xLDEtMC4xYzAuMywwLDAuNiwwLDAuOSwwYzAuMiwwLDAuNSwwLjEsMC43LDAuMQ0KCWMwLjMsMC4xLDAuNywwLjIsMSwwLjNsMC4zLDAuMWwwLjcsMC40bDAuMSwwYzAuMywwLjIsMC42LDAuNSwwLjksMC44YzAuMiwwLjIsMC40LDAuMywwLjYsMC4zYzAuMiwwLDAuNS0wLjEsMC42LTAuM2wwLjgtMC44DQoJbDAuMy0wLjNsMi4zLTIuOWM0LjQsNC41LDQuNywxMS42LDAuOCwxNi41TDI2LjUsMjVsLTkuNywxMi4xTDE0LDMzLjZsMS4zLDAuMWgwLjJjMC45LDAsMS41LTAuNywxLjUtMS42YzAtMC44LTAuNi0xLjQtMS40LTEuNQ0KCWMtMS43LTAuMi0zLjUtMC4yLTUuMi0wLjJjLTAuNiwwLjEtMS4xLDAuNy0xLjIsMS4zbC0wLjYsNC42QzguNiwzNy4yLDkuMiwzNy45LDEwLDM4aDAuMmMwLjgsMCwxLjQtMC42LDEuNS0xLjRsMC4xLTAuOWw0LjQsNS40DQoJYzAuMywwLjMsMC44LDAuNCwxLjEsMC4xYzAsMCwwLjEtMC4xLDAuMS0wLjFMMjksMjYuOGwwLDBDMzEuMiwyNCwzMi40LDIwLjcsMzIuNSwxNy4yeiIvPg0KPC9zdmc+DQo='; // SVG SLP Logo

		public $uses_slplus = false;

		public $min_add_on_versions = array(
			'slp-premier'                     => '4.9.16' ,
			'slp-power'                       => '4.9.16' ,
			'slp-experience'                  => '4.9.16' ,
			'slp-janitor'                     => '4.9' ,

			// 3rd Party
			'slp-extended-data-manager'       => '4.5.01' ,
			'slp-gravity-forms-integration'   => '4.7.6' ,
			'slp-gravity-forms-location-free' => '4.7.10' ,

			// Unsupported
			'slp-contact-extender'            => '99.99.99' ,
			'slp-directory-builder'           => '99.99.99' ,
			'slp-enhanced-map'                => '99.99.99' ,
			'slp-enhanced-results'            => '99.99.99' ,
			'slp-enhanced-search'             => '99.99.99' ,
			'slp-pages'                       => '99.99.99' ,
			'slp-pro'                         => '99.99.99' ,
			'slp-tagalong'                    => '99.99.99' ,
			'slp-widgets'                     => '99.99.99' ,

			'slp-event-location-manager'      => '99.99.99' ,
			'slp-multi-map'                   => '99.99.99' ,
			'slp-social-media-extender'       => '99.99.99' ,
			'slp-user-managed-locations'      => '99.99.99' ,


		);

		public $objects;

		public $options = array(
			'ignore_radius' => '0' , // Passed in as form var from Experience
			'map_domain'    => 'maps.google.com' ,
			'no_autozoom'   => '0' ,
		);

		public $options_nojs = array(
			'broadcast_timestamp'     => '0' ,
			'default_country'         => 'us' ,
			'extended_data_tested'    => '0' ,
			'force_load_js'           => '0' ,
			'geocode_retries'         => '3' ,
			'http_timeout'            => '10' , // HTTP timeout for GeoCode Requests (seconds)
			'map_language'            => 'en' ,
			'next_field_id'           => 1 ,
			'next_field_ported'       => '' ,
			'no_google_js'            => '0' ,
			'premium_user_id'         => '' ,
			'premium_subscription_id' => '' ,
			'radius_behavior'         => 'always_use' ,
			'retry_maximum_delay'     => '5.0' ,
			'slplus_plugindir'        => SLPLUS_PLUGINDIR ,
			'slplus_basename'         => SLPLUS_BASENAME ,
			'themes_last_updated'     => '0' ,
			'ui_jquery_version'       => 'WP' ,
		);

		public  $Activation;
		public  $add_ons;
		public  $admin_page_prefix      = SLP_ADMIN_PAGEPRE;
		public  $AjaxHandler;                                   // TODO: remove when all things use ->AJAX (GFI)
		public  $class_prefix           = 'SLP_';
		public  $clean;
		public  $currentLocation;
		public  $database;
		public  $db;
		public  $dir;
		public  $helper;
		public  $infoFetched            = array();
		public  $installed_version;
		public  $javascript_is_forced   = true;
		public  $location_manager;
		public  $name                   = SLPLUS_NAME;
		public  $network_multisite      = false;
		public  $notifications;
		public  $options_default        = array();
		public  $options_nojs_default   = array();
		public  $plugin_url             = SLPLUS_PLUGINURL;
		public  $prefix                 = SLPLUS_PREFIX;
		private $rest_handler;
		public  $short_slug             = 'store-locator-le';
		public  $shortcode_was_rendered = false;
		public  $slider_rendered        = false;
		public  $slp_store_url          = 'https://wordpress.storelocatorplus.com/';
		public  $slug;
		public  $smart_options;
		public  $support_url            = 'https://docs.storelocatorplus.com';
		public  $url;
		public  $WPML;      // TODO: remove when all things reference SLP_Text::get_instance()

		private $main_id;

		/**
		 * Initialize a new SLPlus Object
		 */
		public function __construct() {
			defined( 'WP_DEBUG' ) || define( 'WP_DEBUG' , false );

			$this->dir               = plugin_dir_path( SLPLUS_FILE );
			$this->slug              = plugin_basename( SLPLUS_FILE );
			$this->url               = plugins_url( '' , SLPLUS_FILE );
			$this->installed_version = get_option( SLPLUS_PREFIX . "-installed_base_version" , '' );     // Single Installs, or Multisite Per-Site Activation, on MS Network Activation returns version of MAIN INSTALL only
			spl_autoload_register( array( $this , 'auto_load' ) );

			if ( ! SLP_DETECTED_HEARTBEAT ) $this->sanitize_things();
		}

		/**
		 * Add meta links.
		 *
		 * TODO: ADMIN ONLY
		 *
		 * @param string[] $links
		 * @param string   $file
		 *
		 * @return string
		 */
		function add_meta_links( $links , $file ) {
			if ( $file == SLPLUS_BASENAME ) {
				$links[] = '<a href="' . $this->support_url . '" title="' . __( 'Documentation' , 'store-locator-le' ) . '">' . __( 'Documentation' , 'store-locator-le' ) . '</a>';
				$links[] = '<a href="' . $this->slp_store_url . '" title="' . __( 'Add Ons' , 'store-locator-le' ) . '">' . __( 'Add Ons' , 'store-locator-le' ) . '</a>';
				$links[] = '<a href="' . admin_url( 'admin.php?page=slp_experience' ) . '" title="' . __( 'Settings' , 'store-locator-le' ) . '">' . __( 'Settings' , 'store-locator-le' ) . '</a>';
			}

			return $links;
		}

		/**
		 * Setup WordPress action scripts.
		 *
		 * Note: admin_menu is not called on every admin page load
		 * Reference: http://codex.wordpress.org/Plugin_API/Action_Reference
		 */
		function add_actions() {
			if ( is_admin() ) {
				add_action( 'admin_notices' , array( $this->notifications , 'display' ) );
			}
			add_action( 'plugins_loaded' , array( $this , 'initialize_after_plugins_loaded' ) , 11 );

		}

		/**
		 * Return an active addon object.
		 *
		 * @param $slug
		 *
		 * @return mixed|null   null if not add on is active.
		 */
		public function addon( $slug ) {
			switch ( strtolower( $slug ) ) {
				case 'slp-myslp-dashboard':
				case 'dashboard':
					$slug = 'slp-myslp-dashboard';
					break;
				case 'slp-experience':
				case 'experience':
					$slug = 'slp-experience';
					break;
				case 'slp-power':
				case 'power':
					$slug = 'slp-power';
					break;
				case 'slp-premier':
				case 'premier':
					$slug = 'slp-premier';
					break;
				default:
					return apply_filters( 'slp_get_addon', $this , $slug );
			}
			$addon = $this->add_ons->get( $slug , 'instance' );

			return ! empty( $addon ) ? $addon : $this;
		}

		/**
		 * Auto-loads classes whenever new ClassName() is called.
		 *
		 * Loads them from the module/<submodule> directory for the add on.  <submodule> is the part after the class prefix before an _ or .
		 * For example SLP_Power_Admin would load the include/module/admin/SLP_Power_Admin.php file.
		 *
		 * @param $class_name
		 */
		public function auto_load( $class_name ) {
			if ( strpos( $class_name, 'SLP_' ) !== 0 ) {
				return;
			}


			$plugin = $this->find_addon( $class_name );
			$prefix = $plugin->class_prefix;
			$dir    = $plugin->dir;

			// Do not require already-invoked SLP objects.
			//
			if ( ! empty( $this->objects ) ) {
				$object_name = substr( $class_name, 4 );
				if ( array_key_exists( $object_name, $this->objects ) ) {
					return;
				}
			}

			// Set submodule and file name.
			//
			preg_match( "/{$prefix}([a-zA-Z]*)/", $class_name, $matches );
			$file_name = $dir . 'include/module/' . ( isset( $matches[1] ) ? strtolower( $matches[1] ) . '/' : '' ) . $class_name . '.php';

			// If the include/module/submodule/class.php file exists, load it.
			//
			if ( is_readable( $file_name ) ) {
				require_once( $file_name );
			}
		}

		/**
		 * Find add on by class name.
		 *
		 * @param string $class_name
		 *
		 * @return mixed|null|SLPlus
		 */
		public function find_addon( $class_name ) {
			// No active addons, assume base plugin
			if ( empty( $this->add_ons->instances ) || empty( $this->add_ons->instances ) ) {
				$plugin = $this;

				// Add ons active, check them.
			} else {
				$matches = array();
				preg_match( "/SLP_([a-zA-Z]*)/", $class_name, $matches );
				$plugin = $this->addon( $matches[1] );
				$plugin->class_prefix = ! empty( $plugin->class_prefix ) ? $plugin->class_prefix :  $matches[0] . '_' ;
			}
			return $plugin;
		}

		/**
		 * Instantiate Activation Object
		 */
		public function createobject_Activation() {
			if ( ! isset( $this->Activation ) ) {
				$this->Activation = SLP_Admin_Activation::get_instance();
			}
		}

		/**
		 * @deprecated 4.7.6
		 *
		 * TODO: Remove when ELM / GFI no longer call this directly
		 */
		public function createobject_AddOnManager() {}

		/**
		 * Create the AJAX processing object and attach to this->ajax
		 *
		 * @used-by \MySLP_REST_API::set_options
		 *
		 * TODO: remove ->ajax, ->AJAX, ->AjaxHandler when all things use slplus->AJAX  instead of ->AjaxHandler (MUP , GFI ) or ->ajax (GFI)
		 */
		function createobject_AJAX() {
			if ( ! isset( $this->ajax ) ) {
				$this->AjaxHandler = $this->AJAX = $this->ajax = SLP_AJAX::get_instance();
			}
		}

		/**
		 * Finish our starting constructor elements.
		 */
		public function initialize() {
			if ( class_exists( 'SLPlus_Location' ) == false ) {
				require_once( 'unit/SLPlus_Location.php' );
			}
			$this->currentLocation = new SLPlus_Location( array( 'slplus' => $this ) );

			// TODO: remove lowercase helper when J and MUP are updated
			$this->helper = $this->Helper = SLP_Admin_Helper::get_instance();

			require_once( SLPLUS_PLUGINDIR . 'include/module/options/SLP_WPOption_Manager.php' );
			require_once( SLPLUS_PLUGINDIR . 'include/module/options/SLP_SmartOptions.php' );

			$this->AddOns = SLP_AddOns::get_instance();
			$this->add_ons = $this->AddOns; // TODO: remove when all ->add_ons references are ->AddOns (MUP, CEX, ELM, GFI, J, SMI, UML)

			// Properties pointing to objects
			// TODO: replace all property references with the global object reference.
			//
			if ( ! isset( $this->notifications ) ) {
				$this->notifications = new SLP_Notifications_Manager();
			}

			// Setup pointers and WordPress connections
			//
			$this->add_actions();

			global $wpdb;
			$this->db       = $wpdb;
			$this->database = new SLP_Data();

			// REST Processing
			// Needs to be loaded all the time.
			// The filter on REST_REQUEST true is after rest_api_init has been called.
			//
			if ( ! isset( $this->rest_handler ) ) {
				$this->rest_handler = new SLP_REST_Handler();
			}

			$this->add_object( new SLP_Actions() );
			$this->add_object( SLP_UI::get_instance() );
		}

		/**
		 * Things to do after all plugins are loaded.
		 */
		public function initialize_after_plugins_loaded() {
			if ( ! isset( $this->WPML ) ) {
				if ( ! isset( $this->Text ) ) {
					$this->Text = SLP_Text::get_instance();
				}
				$this->WPML = $this->Text;
			}

			$this->SmartOptions->initialize_after_plugins_loaded();

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				$this->createobject_AJAX();
			}

			// Version update.
			if ( version_compare( $this->installed_version , SLPLUS_VERSION , '<' ) ) {
				$this->createobject_Activation();
				$this->Activation->update();
				if ( $this->Activation->disabled_experience ) {
					SLP_Admin_Helper::get_instance()->add_wp_admin_notification(
						__( 'You must upgrade Experience add-on to 4.7.6 or higher or your site will crash. ' , 'store-locator-le' )
					);
				}
			}
		}

		/**
		 * Enqueue the Google Maps Script
		 */
		public function enqueue_google_maps_script() {
			wp_enqueue_script( 'google_maps' , $this->get_google_maps_url() , array() , SLPLUS_VERSION , ! $this->javascript_is_forced );
		}

		/**
		 * Get the Google Maps URL (front end)
		 */
		private function get_google_maps_url() {

			// Google JavaScript API server Key
			//
			$server_key = ! empty( $this->SmartOptions->google_server_key->value ) ? '&key=' . $this->SmartOptions->google_server_key->value : '';

			// Set the map language
			//
			$language = 'language=' . $this->options_nojs[ 'map_language' ];
			if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
				$lang_var = ICL_LANGUAGE_CODE;
				if ( ! empty( $lang_var ) ) {
					$language = 'language=' . ICL_LANGUAGE_CODE;
				}
			}

			// Base Google API URL
			//
			$google_api_url = 'https://maps.googleapis.com/maps/api/js?libraries=geometry&';

			// Region
			//
			require_once( SLPLUS_PLUGINDIR . 'include/module/i18n/SLP_Country_Manager.php' );
			if ( isset( $this->Country_Manager->countries[ $this->options_nojs[ 'default_country' ] ] ) ) {
				$country = strtoupper( $this->Country_Manager->countries[ $this->options_nojs[ 'default_country' ] ]->cctld );
			} else {
				$country = '';
			}
			$region = ! empty( $country ) ? '&region=' . $country : '';

			return $google_api_url . $language . $region . $server_key;
		}

		/**
		 * Get the product URL from the add-on manager.
		 *
		 * TODO: Remove when add ons use slplus->AddOns->get_product_url( $slug ) ( MUP )
		 *
		 * @deprecated 4.7.6 use slplus->AddOns->get( $slug , 'active' ) phpStorm replace slplus->get_product_url\((.*?)\) with slplus->AddOns->get\( $1 \)
		 *
		 * @param $slug
		 *
		 * @return string
		 */
		function get_product_url( $slug ) {
			return $this->AddOns->get_product_url( $slug );
		}

		/**
		 * Return true if the named add-on pack is active.
		 *
		 * TODO: Remove when add ons use slplus->AddOns->get( $slug , 'active' ) ( MUP , GFI , CPROF )
		 *
		 * @deprecated 4.7.6 use slplus->AddOns->get( $slug , 'active' ) phpStorm replace ->is_AddonActive\((.*?)\) with ->AddOns->get\( $1 , 'active' \)
		 *
		 * @param string $slug
		 *
		 * @return boolean
		 */
		public function is_AddonActive( $slug ) {
			return $this->AddOns->get( $slug , 'active' );
		}

		/**
		 * Return '1' if the given value is set to 'true', 'on', or '1' (case insensitive).
		 * Return '0' otherwise.
		 *
		 * Useful for checkbox values that may be stored as 'on' or '1'.
		 *
		 * @param        $value
		 * @param string $return_type
		 *
		 * @return bool|string
		 */
		public function is_CheckTrue( $value , $return_type = 'boolean' ) {
			if ( $return_type === 'string' ) {
				$true_value  = '1';
				$false_value = '0';
			} else {
				$true_value  = true;
				$false_value = false;
			}

			// No arrays allowed.
			//
			if ( is_array( $value ) ) {
				error_log( __( 'Array provided to SLPlus::is_CheckTrue()' , 'store-locator-le' ) );

				return $false_value;
			}

			if ( strcasecmp( $value , 'true' ) == 0 ) {
				return $true_value;
			}
			if ( strcasecmp( $value , 'on' ) == 0 ) {
				return $true_value;
			}
			if ( strcasecmp( $value , '1' ) == 0 ) {
				return $true_value;
			}
			if ( $value === 1 ) {
				return $true_value;
			}
			if ( $value === true ) {
				return $true_value;
			}

			return $false_value;
		}

		/**
		 * Check if certain safe mode restricted functions are available.
		 *
		 * exec, set_time_limit
		 *
		 * @param $funcname
		 *
		 * @return mixed
		 */
		public function is_func_available( $funcname ) {
			static $available = array();

			if ( ! isset( $available[ $funcname ] ) ) {
				$available[ $funcname ] = true;
				if ( ini_get( 'safe_mode' ) ) {
					$available[ $funcname ] = false;
				} else {
					$d = ini_get( 'disable_functions' );
					$s = ini_get( 'suhosin.executor.func.blacklist' );
					if ( "$d$s" ) {
						$array = preg_split( '/,\s*/' , "$d,$s" );
						if ( in_array( $funcname , $array ) ) {
							$available[ $funcname ] = false;
						}
					}
				}
			}

			return $available[ $funcname ];
		}

		/**
		 * Are we on the main blog ID (first site)?
		 *
		 * @return bool
		 */
		public function is_main_blog() {
			return ( get_current_blog_id() === $this->get_main_blog_id() );
		}

		/**
		 * Get the main blog ID.
		 *
		 * @return int|null
		 */
		private function get_main_blog_id() {
			if ( empty( $this->main_id ) ) {
				$this->main_id = get_main_network_id();
			}
			return $this->main_id;
		}


		/**
		 * Checks if a URL is valid.
		 *
		 * @param $url
		 *
		 * @return bool
		 */
		public function is_valid_url( $url ) {
			$url = trim( $url );

			return ( ( strpos( $url , 'http://' ) === 0 || strpos( $url , 'https://' ) === 0 ) && filter_var( $url , FILTER_VALIDATE_URL ) !== false );
		}

		/**
		 * Load the selected jQuery UI theme.
		 *
		 * @param string $theme the jQuery UI theme
		 */
		public function load_jquery_theme( $theme = 'none' ) {
			$handle        = 'jquery-ui-' . $theme;
			$relative_file = "/css/jquery/{$handle}.min.css";
			if ( is_readable( SLPLUS_PLUGINDIR . $relative_file ) ) {
				wp_enqueue_style( $handle , $this->plugin_url . $relative_file );
			}
		}

		/**
		 * Re-center the map as needed.
		 *
		 * Sets Center Map At ('map-center') and Lat/Lng Fallback if any of those entries are blank.
		 *
		 * Uses the Map Domain ('default_country') as the source for the new center.
		 */
		public function recenter_map() {
			if ( empty( $this->SmartOptions->map_center->value ) ) {
				$this->set_map_center();
			}
			if ( empty( $this->SmartOptions->map_center_lat->value ) ) {
				$this->set_map_center_fallback( 'lat' );
			}
			if ( empty( $this->SmartOptions->map_center_lng->value ) ) {
				$this->set_map_center_fallback( 'lng' );
			}
		}

		/**
		 * Sanitize some stuff we care about.
		 */
		private function sanitize_things() {

			// Keylike inputs
			$request_params = array( 'act' , 'action' , 'deactivate' , 'option_page' , 'page' , 'sortorder' );
			foreach ( $request_params as $key ) {
				$this->clean[ $key ] = ! empty( $_REQUEST[ $key ] ) ? sanitize_key( $_REQUEST[ $key ] ) : '';
			}

			// Int only inputs
			$request_params = array( 'locationID' , 'start' );
			foreach ( $request_params as $key ) {
				$this->clean[ $key ] = ! empty( $_REQUEST[ $key ] ) ? intval( $_REQUEST[ $key ] ) : 0;
			}

			// Text only inputs
			$request_params = array( 'selected_nav_element' );
			foreach ( $request_params as $key ) {
				$this->clean[ $key ] = ! empty( $_REQUEST[ $key ] ) ? sanitize_text_field( $_REQUEST[ $key ] ) : '';
			}

			// Orderby inputs
			$request_params = array( 'orderBy' );
			foreach ( $request_params as $key ) {
				$this->clean[ $key ] = ! empty( $_REQUEST[ $key ] ) ? sanitize_sql_orderby( $_REQUEST[ $key ] ) : '';
			}

		}

		/**
		 * Set the Center Map At if the setting is empty.
		 */
		public function set_map_center() {
			require_once( SLPLUS_PLUGINDIR . 'include/module/i18n/SLP_Country_Manager.php' );
			$this->options[ 'map_center' ] = $this->Country_Manager->countries[ $this->options_nojs[ 'default_country' ] ]->name;
		}

		/**
		 * Set the map center fallback for the selected country.
		 *
		 * @param string $for latlng | lat | lng
		 */
		private function set_map_center_fallback( $for = 'latlng' ) {
			require_once( SLPLUS_PLUGINDIR . 'include/module/i18n/SLP_Country_Manager.php' );

			// If the map center is set to the country.
			//
			if ( $this->options[ 'map_center' ] == $this->Country_Manager->countries[ $this->options_nojs[ 'default_country' ] ]->name ) {

				// Set the default country lat
				//
				if ( ( $for === 'latlng' ) || ( $for === 'lat' ) ) {
					$this->SmartOptions->map_center_lat->value = $this->Country_Manager->countries[ $this->options_nojs[ 'default_country' ] ]->map_center_lat;
					$this->options[ 'map_center_lat' ]         = $this->SmartOptions->map_center_lat->value;
				}

				// Set the default country lng
				//
				if ( ( $for === 'latlng' ) || ( $for === 'lng' ) ) {
					$this->SmartOptions->map_center_lng->value = $this->Country_Manager->countries[ $this->options_nojs[ 'default_country' ] ]->map_center_lng;
					$this->options[ 'map_center_lng' ]         = $this->SmartOptions->map_center_lng->value;
				}
			}

			// No Lat or Lng in Country Data?  Go ask Google.
			//
			if ( empty( $this->SmartOptions->map_center_lng->value ) || empty( $this->SmartOptions->map_center_lat->value ) ) {
				$json = $this->currentLocation->get_LatLong( $this->SmartOptions->map_center->value );
				if ( ! empty( $json ) ) {
					$json = json_decode( $json );
					if ( is_object( $json ) && ( $json->{'status'} === 'OK' ) ) {
						if ( empty( $this->SmartOptions->map_center_lat->value ) ) {
							$this->SmartOptions->map_center_lat->value = $json->results[ 0 ]->geometry->location->lat;
							$this->options[ 'map_center_lat' ]         = $this->SmartOptions->map_center_lat->value;
						}
						if ( empty( $this->SmartOptions->map_center_lng->value ) ) {
							$this->SmartOptions->map_center_lng->value = $json->results[ 0 ]->geometry->location->lng;
							$this->options[ 'map_center_lng' ]         = $this->SmartOptions->map_center_lng->value;
						}
					}
				}
			}
		}

		/**
		 * Set the PHP max execution time.
		 */
		public function set_php_timeout() {
			ini_set( 'max_execution_time' , $this->SmartOptions->php_max_execution_time->value );
			if ( $this->is_func_available( 'set_time_limit' ) ) {
				set_time_limit( $this->SmartOptions->php_max_execution_time->value );
			}
		}

		/**
		 * Set valid options from the incoming REQUEST
		 *
		 * @param mixed  $val - the value of a form var
		 * @param string $key - the key for that form var
		 */
		function set_ValidOptions( $val , $key ) {
			require_once( 'module/options/SLP_SmartOptions.php' );
			$this->SmartOptions->set_valid_options( $val , $key );
		}

		/**
		 * Set valid options from the incoming REQUEST
		 *
		 * Set this if the incoming value is not an empty string.
		 *
		 * @param mixed  $val - the value of a form var
		 * @param string $key - the key for that form var
		 */
		function set_ValidOptionsNoJS( $val , $key ) {
			require_once( 'module/options/SLP_SmartOptions.php' );
			$this->SmartOptions->set_valid_options_nojs( $val , $key );
		}
	}

	/**
	 * @var SLPlus $slplus
	 * @var SLPlus $slplus_plugin
	 */
	global $slplus;
	global $slplus_plugin;
	$slplus        = new SLPlus();
	$slplus_plugin = $slplus;

	// We do not do any heartbeat processing
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $_POST[ 'action' ] ) && ( $_POST[ 'action' ] === 'heartbeat' ) ) {
		return;
	}

	$slplus->initialize();
}