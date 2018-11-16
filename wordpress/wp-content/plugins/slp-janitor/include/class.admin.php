<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'SLPJanitor_Admin' ) ) {
	require_once( SLPLUS_PLUGINDIR . '/include/base_class.admin.php' );

	/**
	 * Holds the admin-only code.
	 *
	 * This allows the main plugin to only include this file in admin mode
	 * via the admin_menu call.   Reduces the front-end footprint.
	 *
	 * @property-read       SLPJanitor_Admin_Functions $admin_functions
	 * @property-read       string[] $defunct_options    List of defunct options.
	 * @property            string[] $optionList         Reset these even if the add-on packs are inactive. Also used for inspection.
	 * @property-read       array $product_info       List of product info settings.
	 *
	 */
	class SLPJanitor_Admin extends SLP_BaseClass_Admin {
		private $admin_functions;
		public $optionList = array(
			':: Our Add Ons',
			'-- Store Locator Plus',
			'csl-slplus-installed_base_version',
			'csl-slplus-options',
			'csl-slplus-options_nojs',
			'csl-slplus-theme',
			'csl-slplus-theme_array',
			'csl-slplus-theme_details',
			'csl-slplus-theme_lastupdated',
			'sl_map_overview_control',
			'sl_map_radii',
			'sl_name_label',
			'sl_radius_label',
			'sl_search_label',

			'-- Experience',
			'slp-experience',

			'-- Power',
			'slp-power',

			'-- Premier',
			'slp-premier-options',

			'-- Contact Extender',
			'slplus-extendo-contacts-options',

			'-- Directory  Builder',
			'slp-directory-builder-options',

			'-- Pages',
			'slp_storepages-options',

			'-- Tagalong',
			'csl-slplus-TAGALONG-options',


			':: Third Party Add Ons',

			'-- Event Location Manager',
			'slplus-event-location-manager-options',

			'-- Gravity Forms Integration',
			'slplus-gravity-forms-integration-options',

			'-- Multi Map',
			'-MM-options',

			'-- Social Media Extender',
			'slplus-social-media-extender-options',

			'-- User Managed Locations',
			'slplus-user-managed-locations-options',

			':: Legacy Add Ons',

			'-- Enhanced Map',
			'csl-slplus-EM-options',

			'-- Enhanced Results',
			'csl-slplus-ER-options',
			'csl-slplus_slper',
			'csl-slplus_disable_initialdirectory',
			'csl-slplus-enhanced_results_add_tel_to_phone',
			'csl-slplus-enhanced_results_hide_distance_in_table',
			'csl-slplus-enhanced_results_orderby',
			'csl-slplus-enhanced_results_show_country',
			'csl-slplus-enhanced_results_show_hours',
			'csl-slplus_maxreturned',
			'csl-slplus_message_noresultsfound',

			'-- Enhanced Search',
			'csl-slplus-ES-options',
			'csl-slplus_slpes',
			'csl-slplus-enhanced_search_hide_search_form',
			'csl-slplus_show_search_by_name',
			'csl-slplus_search_by_state_pd_label',
			'slplus_show_state_pd',

			'-- Pro Pack',
			'csl-slplus-PRO-options',

			'-- Widget Pack',
			'slp-widget-pack-options',
			'skel_slpWidgets_options',


		);

		private $defunct_options = array(
			'csl-slplus_slper',
			'csl-slplus-enhanced_results_orderby',
			'csl-slplus-enhanced_results_add_tel_to_phone',
			'csl-slplus_disable_initialdirectory',
			'csl-slplus-enhanced_results_hide_distance_in_table',
			'csl-slplus-enhanced_results_show_country',
			'csl-slplus-enhanced_results_show_hours',
		);

		private $product_info = array(
			'-- Store Locator Plus'        => array( 'product_url' => 'https://www.storelocatorplus.com/product/store-locator-plus/' ),
			'-- Experience'                => array( 'product_url' => 'https://www.storelocatorplus.com/product/experience/' ),
			'-- Event Location Manager'    => array( 'product_url' => 'https://www.storelocatorplus.com/product/event-location-manager/' ),
			'-- Gravity Forms Integration' => array( 'product_url' => 'https://www.storelocatorplus.com/product/gravity-forms-integration/' ),
			'-- Power'                     => array( 'product_url' => 'https://www.storelocatorplus.com/product/power/' ),
			'-- Premier'                   => array( 'product_url' => 'https://www.storelocatorplus.com/product/premier-subscription/' ),
			'-- Social Media Extender'     => array( 'product_url' => 'https://www.storelocatorplus.com/product/slp4-social-media-extender/' ),
			'-- User Managed Locations'    => array( 'product_url' => 'https://www.storelocatorplus.com/product/slp4-user-managed-locations/' ),
		);

		/**
		 * Add hooks and filters
		 *
		 * @uses \SLPJanitor_Admin::setup_janitor_scripts
		 */
		public function add_hooks_and_filters() {
			parent::add_hooks_and_filters();
			if ( ! empty ( $_REQUEST['page'] ) && ( $_REQUEST['page'] === 'slp-janitor' ) ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'setup_janitor_scripts' ) );
			}
		}

		/**
		 * Enqueue the admin scripts.
		 *
		 * @used-by \\SLPJanitor_Admin::add_hooks_and_filters::add_hooks_and_filters     FILTER: admin_enqueue_scripts
		 *
		 * @param string $hook
		 */
		public function setup_janitor_scripts( $hook ) {
			if ( $hook !== $this->slplus->admin_page_prefix . 'slp-janitor' ) {
				return;
			}

			wp_enqueue_script( 'slp_janitor', $this->addon->url . '/js/admin-janitor-tab.min.js' );
		}

		/**
		 * Add Navbar
		 */
		private function add_navbar() {
			$this->settings_interface->add_section( array(
				'name'        => 'Navigation',
				'div_id'      => 'navbar_wrapper',
				'description' => $this->slplus->AdminUI->create_Navbar(),
				'innerdiv'    => false,
				'is_topmenu'  => true,
				'auto'        => false,
				'headerbar'   => false
			) );
		}

		/**
		 * Add Settings subtab
		 */
		private function add_settings_subtab() {
			$this->current_section = __( 'Settings', 'slp-janitor' );
			$this->settings_interface->add_section( array( 'name' => $this->current_section ) );

			// Settings : Reset
			//
			$this->current_group = __( 'Actions', 'slp-janitor' );
			$this->add_sublabel(
				__( 'Reset', 'slp-janitor' ),
				__( 'Reset SLP Options will clear out all settings. ', 'slp-janitor' ) .
				__( 'This is a destructive process that cannot be undone. ', 'slp-janitor' ) .
				__( 'Locations will not be deleted. ', 'slp-janitor' ) .
				__( 'Make sure you have a full backup of your site before proceeding. ', 'slp-janitor' )
			);
			$this->add_button( __( 'Reset SLP Options', 'slp-janitor' ), 'reset_options', __( 'Reset ALL of the options listed below to blank?', 'slp-janitor' ) );

			$this->add_sublabel(
				__( 'Clear Plugin Styles', 'slp-janitor' ),
				__( 'This will clear all the CSS metadata for plugin styles. ', 'slp-janitor' ) .
				__( 'Next visit to the Experience tab will re-read all the CSS file headers. ', 'slp-janitor' ) .
				__( 'This will re-create the plugin styles listed under the Experience/View tab. ', 'slp-janitor' )
			);
			$this->add_button( __( 'Clear Plugin Style Cache', 'slp-janitor' ), 'clear_plugin_styles', __( 'Clear the Plugin Style cache?', 'slp-janitor' ) );


			// Settings: Inspect
			//
			// Show each option we know about and its current value.
			//
			foreach ( $this->optionList as $optionName ) {
				if ( substr( $optionName, 0, 2 ) === '::' ) {
					$this->current_group = sprintf( __( 'Inspect %s', 'slp-janitor' ), substr( $optionName, 3 ) );
					$this->add_sublabel(
						'',
						__( 'The current settings for SLP related options are noted below. ', 'slp-janitor' ) .
						__( 'Clicking the delete icon on a parent list item resets all children. ', 'slp-janitor' ) .
						__( 'Clicking the delete icon next to an individual item resets only that item. ', 'slp-janitor' ) .
						__( 'This is a destructive proces that cannot be undone. ', 'slp-janitor' )
					);

				} elseif ( substr( $optionName, 0, 2 ) === '--' ) {
					$this->add_sublabel( $this->set_product_header( $optionName ) );

				} else {
					$this->show_OptionValue( $optionName );
				}
			}
		}

		/**
		 * Add Tools subtab
		 */
		private function add_tools_subtab() {
			$this->current_section = __( 'Tools', 'slp-janitor' );
			$this->settings_interface->add_section( array( 'name' => $this->current_section ) );

			// Locations
			//
			$this->current_group = __( 'Locations', 'slp-janitor' );
			$this->add_sublabel(
				__( 'Clear', 'slp-janitor' ),
				__( 'Clearing ALL locations is a destructive process that cannot be undone. ', 'slp-janitor' ) .
				__( 'Make sure you have a full backup of your site before proceeding. ', 'slp-janitor' )
			);

			$this->add_button( __( 'Clear Locations', 'slp-janitor' ), 'clear_locations' , __( 'Clear ALL of the locations of Store Locator Plus?', 'slp-janitor' ) );
			$this->add_sublabel(
				__( 'Drop', 'slp-janitor' ),
				__( 'Drop Locations Table is faster but will not reset Store Pages, Tagalong and some other add-on pack data. ', 'slp-janitor' ) .
				__( 'Use this for clearing locations from the base plugin, Pro Pack, the Enhanced add-ons and the Extender add-ons. ', 'slp-janitor' ) .
				__( 'Drop Locations Table is a destructive process that cannot be undone. ', 'slp-janitor' ) .
				__( 'Make sure you have a full backup of your site before proceeding. ', 'slp-janitor' )
			);
			$this->add_button( __( 'Drop Locations Table', 'slp-janitor' ), 'drop_locations_table' ,__( 'Clear ALL of the locations of Store Locator Plus?', 'slp-janitor' ) );

			// Descriptions
			//
			$this->current_group = __( 'Descriptions', 'slp-janitor' );
			$this->add_sublabel(
				'',
				__( 'A bug in older versions of Store Locator Plus was storing HTML in encoded format. ', 'slp-janitor' ) .
				__( 'Click the button below to convert HTML codes such as &lt; back to the standard < format. ', 'slp-janitor' ) .
				__( 'This should only need to be done once. ', 'slp-janitor' ) .
				__( 'The process is not reversible, so make sure you have backed up your data first. ', 'slp-janitor' )
			);
			$this->add_button( __( 'Fix Description HTML', 'slp-janitor' ), 'fix_descriptions' ,__( 'Are you sure you convert all encoded HTML to standard HTML in the location descriptions?', 'slp-janitor' ) );

			// Extended Data
			//
			$this->current_group = __( 'Extended Data', 'slp-janitor' );
			$this->add_sublabel(
				'',
				__( 'Use these button to manage the metadata records that manage extended data fields. ', 'slp-janitor' ) .
				__( 'Rebuild Extended Data Tables will attempt to rebuild the extended data table without being destructive. ', 'slp-janitor' ) .
				__( 'Delete Extended Data Tables info will clear out all of the extended location data and data fields. ', 'slp-janitor' ) .
				__( 'Using the Delete option will require you to deactivate any extended data add-on packs and install a newer version to get field data back.', 'slp-janitor' ) .
				__( 'If there is not a newer version of the add-on  you can delete the "installed_version" setting for that add-on under Janitor Settings.', 'slp-janitor' )
			);
			$this->add_button( __( 'Rebuild Extended Data Tables', 'slp-janitor' ), 'rebuild_extended_tables' ,__( 'Are you sure you want to rebuild the extended data info?', 'slp-janitor' ) );
			$this->add_button( __( 'Delete Extended Data Tables', 'slp-janitor' ), 'delete_extend_datas' ,__( 'Are you sure you want to delete all extended data info?', 'slp-janitor' ) );


			// Tagalong
			//
			$this->current_group = __( 'Tagalong', 'slp-janitor' );
			$this->add_sublabel(
				'',
				__( 'Use this button to clear out the Tagalong categories table. ', 'slp-janitor' ) .
				__( 'The table is a helper table to speed up linking locations to categories. ', 'slp-janitor' )
			);
			$this->add_button( __( 'Delete Tagalong Category Helper Data', 'slp-janitor' ), 'delete_tagalong_helpers' ,__( 'Are you sure you want to delete the Tagalong category helper table?', 'slp-janitor' ) );
			$this->add_button( __( 'Rebuild Tagalong Category Helper Data', 'slp-janitor' ), 'rebuild_tagalong_helpers' ,__( 'Attempt to re-attach Tagalong categories?', 'slp-janitor' ) );
		}

		/**
		 * Create admin functions object.
		 */
		private function create_object_admin_functions() {
			if ( ! isset ( $this->admin_functions ) ) {
				require_once( 'class.admin.functions.php' );
				$this->admin_functions = new SLPJanitor_Admin_Functions( array( 'addon' => $this->addon ) );
			}
		}

		/**
		 * Set base class properties so we can have more cross-add-on methods.
		 */
		function set_addon_properties() {

			// Add registered add ons that are not listed in the links above.
			//
			foreach ( $this->slplus->add_ons->instances as $slug => $addon ) {
				$product_url        = '';
				$janitor_addon_slug = "-- {$addon->name}";

				// Add product URL info
				//
				if ( ! array_key_exists( $slug, $this->product_info ) ) {

					$product_url = $addon->get_meta( 'PluginURI' );

					if ( ! empty( $product_url ) ) {
						$this->product_info[ $janitor_addon_slug ] =
							array( 'product_url' => $product_url );
					}
				}


				// Registered add on not in option list
				//
				if ( ! in_array( $janitor_addon_slug, $this->optionList ) ) {
					$this->optionList[] = $janitor_addon_slug;
					$this->optionList[] = $addon->option_name;
				}

			}
		}

		/**
		 * Handle the incoming form submit action.
		 *
		 * @return mixed[] results of actions.
		 */
		function process_actions() {
			if ( ! isset( $_REQUEST['action'] ) ) {
				return array();
			}
			if ( ! check_admin_referer( 'csl-slplus-settings-options' ) ) {
				return array();
			}

			// Set execution time limit.
			//
			$this->slplus->set_php_timeout();
			switch ( $_REQUEST['action'] ) {

				// RESET OPTIONS
				//
				case 'clear_plugin_styles':
					$this->create_object_admin_functions();

					return $this->admin_functions->clear_plugin_styles();

				case 'reset_options':
					$this->create_object_admin_functions();

					return $this->admin_functions->reset_Settings();

				case 'fix_descriptions':
					$this->create_object_admin_functions();

					return $this->admin_functions->fix_Descriptions();

				case 'clear_locations':
					$this->create_object_admin_functions();

					return $this->admin_functions->clear_Locations();

				case 'delete_extend_datas':
					$this->create_object_admin_functions();

					return $this->admin_functions->delete_Extend_datas();

				case 'delete_tagalong_helpers':
					$this->create_object_admin_functions();

					return $this->admin_functions->delete_Tagalong_helpers();

				case 'drop_locations_table':
					$this->create_object_admin_functions();

					return $this->admin_functions->drop_locations();

				case 'rebuild_extended_tables':
					$this->create_object_admin_functions();

					return $this->admin_functions->rebuild_Extended_Tables();

				case 'rebuild_tagalong_helpers':
					$this->create_object_admin_functions();

					return $this->admin_functions->rebuild_Tagalong_helpers();

				default:
					if ( strrpos( $_REQUEST['action'], 'reset_single_' ) === 0 ) {
						$option_name = substr( $_REQUEST['action'], 13 );

						return $this->reset_single_setting( $option_name );

					} else if ( strrpos( $_REQUEST['action'], 'reset_serial_' ) === 0 ) {
						$matches = array();
						preg_match( '/^(.*?)\:\:(.*?)$/', substr( $_REQUEST['action'], 13 ), $matches );

						return $this->reset_serial_Settings( $matches[1], $matches[2] );

					}
			}

			return array();
		}

		/**
		 * Render the admin page
		 */
		function render_AdminPage() {

			// If we are running a reset.
			//
			$action_results = $this->process_actions();

			// Setup and render settings page
			//
			require_once( SLPLUS_PLUGINDIR . 'include/module/settings/SLP_Settings.php' );
			$this->settings_interface = new SLP_Settings( array(
				'name'        => $this->slplus->name . ' - ' . $this->addon->name,
				'form_action' => admin_url() . 'admin.php?page=' . $this->addon->short_slug,
				'form_name'   => 'slp_janitor',
				'save_text'   => ''
			) );

			$this->add_navbar();

			$this->add_settings_subtab();

			$this->add_tools_subtab();

			// Action notices
			//
			if ( is_array( $action_results ) ) {
				foreach ( $action_results as $result ) {
					$this->slplus->helper->create_string_wp_setting_error_box( $result );
				}
			}

			$this->settings_interface->render_settings_page();
		}


		/**
		 * Reset the serial settings.
		 */
		function reset_serial_Settings( $option_name, $name ) {
			$option_array          = get_option( $option_name, array() );
			$option_array[ $name ] = '';
			update_option( $option_name, $option_array );

			return array( sprintf( __( 'SLP serialized option %s[%s] has been deleted.', 'slp-janitor' ), $option_name, $name ) );
		}

		/**
		 * Reset the single settings.
		 */
		function reset_single_setting( $optionName ) {
			$resetInfo     = array();
			$saved_setting = $this->save_important_settings( $optionName );
			if ( delete_option( $optionName ) ) {
				$resetInfo[] = sprintf( __( 'SLP option %s has been deleted.', 'slp-janitor' ), $optionName );
			}
			if ( ! is_null( $saved_setting ) ) {
				$this->restore_important_settings( $optionName, $saved_setting );
			}

			return $resetInfo;
		}

		/**
		 * Save Important Settings.
		 *
		 * @param $option_name
		 *
		 * @return mixed|null|void
		 */
		private function save_important_settings( $option_name ) {
			$important_settings = apply_filters( 'slp_janitor_important_settings', array() );
			if ( in_array( $option_name, $important_settings ) ) {
				return get_option( $option_name );
			}

			return null;
		}

		/**
		 * Restore Important Settings.
		 *
		 * @param $option_name
		 * @param $saved_setting
		 */
		private function restore_important_settings( $option_name, $saved_setting ) {
			if ( ! is_null( $saved_setting ) ) {
				do_action( 'slp_janitor_restore_important_setting', $option_name, $saved_setting );
			}
		}

		/**
		 * Set a product header for each setting section.
		 *
		 * @param $option_name
		 *
		 * @return string
		 */
		private function set_product_header( $option_name ) {
			$output = substr( $option_name, 3 );

			// We have product info, linkage please...
			//
			if ( isset( $this->product_info[ $option_name ] ) ) {
				if ( isset( $this->product_info[ $option_name ]['product_url'] ) && ! empty( $this->product_info[ $option_name ]['product_url'] ) ) {
					$output = sprintf(
						'<a href="%s" target="slp">%s</a>',
						$this->product_info[ $option_name ]['product_url'],
						$output
					);
				}
			}

			return $output;
		}

		/**
		 * Show the option value data on the inspect/reset settings interface.
		 *
		 * @param string $optionName
		 */
		private function show_OptionValue( $optionName ) {
			$option_value = get_option( $optionName );
			if ( $option_value === false ) {
				return;
			}

			// Defunct
			//
			$is_defunct = ( in_array( $optionName, $this->defunct_options ) );

			$label = str_replace( 'csl-slplus', '', $optionName );
			if ( $is_defunct ) {
				$label .= ' (' . __( 'defunct', 'slp-janitor' ) . ')';
			}

			// Array Options = serialized data
			//
			if ( is_array( $option_value ) ) {
				$label  = $label;
				$custom =
					'<div class="parent">' .
					$this->createstring_CustomSettingInput(
						$optionName,
						htmlspecialchars( print_r( $option_value, true ) ),
						"reset_single_{$optionName}"
					) .
					'</div>';

				// Then the individual options
				//
				foreach ( $option_value as $name => $value ) {
					if ( is_array( $value ) ) {
						$value  = print_r( $value, true );
						$action = '';
					} else {
						$action = "reset_serial_{$optionName}::{$name}";
					}
					$custom .=
						'<div class="child">' .
						"<label>$name:</label>" .
						$this->createstring_CustomSettingInput(
							$optionName,
							htmlspecialchars( $value ),
							$action
						) .
						'</div>';
				}

				// Non-Array = individual settings
				//
			} else {
				$custom = $this->createstring_CustomSettingInput( $optionName, htmlspecialchars( $option_value ), "reset_single_{$optionName}" );
			}

			$this->settings_interface->add_ItemToGroup(
				array(
					'section'    => $this->current_section,
					'group'      => $this->current_group,
					'label'      => $label,
					'setting'    => $optionName,
					'use_prefix' => false,
					'disabled'   => true,
					'type'       => 'custom',
					'custom'     => $custom
				)
			);
		}

		/**
		 * Create the input HTML string for settings.
		 *
		 * @param string $optionName
		 * @param string $showValue
		 * @param string $action_name ('reset_single_<option_name>' , 'reset_serial_<option_name>_<name>')
		 *
		 * @return string
		 */
		private function createstring_CustomSettingInput( $optionName, $showValue, $action_name ) {
			$html_string =
				"<input type='text' disabled='disable' name='{$optionName}' value='{$showValue}' />";
			if ( ! empty ( $action_name ) ) {
				$message = __( 'Reset this option?' , 'slp-janitor' );
				$html_string .=
					'<a class="dashicons dashicons-trash slp-no-box-shadow" alt="reset option" title="reset option" ' .
					"data-field='{$action_name}' data-related_to='{$message}'></a>";
			}

			return $html_string;
		}

		/**
		 * Add a button to the settings.
		 *
		 * @param string $label
		 * @param string $action
		 * @param string $message
		 */
		public function add_button( $label, $action, $message ) {
			$this->settings_interface->add_ItemToGroup( array(
				'section'    => $this->current_section,
				'group'      => $this->current_group,
				'type'       => 'submit_button',
				'show_label' => false,
				'value'      => $label,
				'data_field' => $action,
				'related_to' => $message
			) );
		}

	}

}
