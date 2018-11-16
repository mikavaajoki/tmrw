<?php
defined( 'ABSPATH' ) || exit;

/**
 * Store Locator Plus Activation handler.
 *
 * Handles data structure changes as well as settings migration between versions.
 * Update the plugin version in config.php on every structure change.
 *
 */
class SLP_Admin_Activation extends SLPlus_BaseClass_Object {
	public $disabled_experience = false;

	/**
	 * Add roles and cap.
	 */
	function add_slplus_roles_and_caps() {
		$role = get_role( 'administrator' );
		if ( is_object( $role ) && ! $role->has_cap( 'manage_slp_admin' ) ) {
			$role->add_cap( 'manage_slp' );
			$role->add_cap( 'manage_slp_admin' );
			$role->add_cap( 'manage_slp_user' );
		}
	}

	/**
	 * Is this add-on being deactivated?
	 *
	 * @return bool
	 */
	public function being_deactivated() {
		if ( empty( $_REQUEST ) || ( empty ( $this->slplus->clean['action'] ) && empty( $this->slplus->clean['deactivate'] ) ) ) {
			return false;
		}

		$action_is_deactivate = ! empty( $this->slplus->clean['action'] ) && ( $this->slplus->clean['action'] === 'deactivate' );
		$deactivate_is_true   = ! empty( $this->slplus->clean['deactivate'] ) && ( $this->slplus->clean['deactivate'] === 'true' );
		$plugin_is_this_one   = isset( $_REQUEST['plugin'] ) && ( $_REQUEST['plugin'] === $this->slplus->slug );

		return ( $plugin_is_this_one && ( $action_is_deactivate || $deactivate_is_true ) );
	}

	/**
	 * Check if there are any add on updates.  Inactive plugins too.
	 */
	private function check_for_addon_updates() {
		$plugins = get_plugins();  // contains all plugins active or not key = slug (store-locator-le/store-locator-le.php)

		// Check all plugins
		foreach ( $plugins as $file => $p ) {
			$file_parts = explode( '/', $file );

			// If not ours move on.
			if ( empty( $file_parts ) || ! array_key_exists( $file_parts[0], $this->slplus->min_add_on_versions ) ) {
				continue;
			}

			/** @var SLP_AddOn $addon */
			$addon = $this->slplus->addon( $file_parts[0] );

			/** @var SLP_AddOn_Updates $update_engine */
			$update_engine = SLP_AddOn_Updates::get_instance();

			// If add on is active.
			if ( $addon !== $this->slplus ) {
				$available_version = $addon->latest_version;

			// Inactive addon.
			} else {
				$available_version = $update_engine->getRemote_version( $file_parts[0] );

			}

			// Check if remote version is newer
			if ( version_compare( $p['Version'], $available_version, '<' ) ) {
				$update_engine->plugin_slug = $file;
				$update_engine->slug = $file_parts[0];
				$update_engine->current_version = $p['Version'];
				$update_engine->set_new_version_available();
			}

		}
	}

	/**
	 * Copy non-empty, readable files to destination if they are newer than the destination file.
	 * OR if the destination file does not exist.
	 *
	 * @param $source_file
	 * @param $destination_file
	 */
	public function copy_newer_files( $source_file, $destination_file ) {
		if ( empty( $source_file ) ) {
			return;
		}
		if ( ! is_readable( $source_file ) ) {
			return;
		}
		if (
			! file_exists( $destination_file ) ||
			(
				file_exists( $destination_file ) &&
				( filemtime( $source_file ) > filemtime( $destination_file ) )
			)
		) {
			copy( $source_file, $destination_file );
		}
	}

	/**
	 * Recursively copy source directory (or file) into destination directory.
	 *
	 * @param string $source can be a file or a directory
	 * @param string $dest   can be a file or a directory
	 */
	private function copyr( $source, $dest ) {
		if ( ! file_exists( $source ) ) {
			return;
		}

		// Make destination directory if necessary
		//
		if ( ! is_dir( $dest ) ) {
			wp_mkdir_p( $dest );
		}

		// Loop through the folder
		$dir = dir( $source );
		if ( is_a( $dir, 'Directory' ) ) {
			while ( false !== $entry = $dir->read() ) {

				// Skip pointers
				if ( $entry == '.' || $entry == '..' ) {
					continue;
				}

				$source_file = "{$source}/{$entry}";
				$dest_file   = "{$dest}/{$entry}";

				// Copy Files
				//
				if ( is_file( $source_file ) ) {
					$this->copy_newer_files( $source_file, $dest_file );
				}

				// Copy Symlinks
				//
				if ( is_link( $source_file ) ) {
					symlink( readlink( $source_file ), $dest_file );
				}

				// Directories, go deeper
				//
				if ( is_dir( $source_file ) ) {
					$this->copyr( $source_file, $dest_file );
				}
			}

			// Clean up
			$dir->close();
		}
	}

	/**
	 * Update the data structures on new db versions.
	 *
	 * @param string $sql
	 * @param string $table_name
	 *
	 * @return string
	 */
	private function dbupdater( $sql, $table_name ) {

		/** @var wpdb $wpdb */
		global $wpdb;
		$retval = ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) ? 'new' : 'updated';

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$were_showing_errors = $wpdb->show_errors;
		$wpdb->hide_errors();
		dbDelta( $sql );
		global $EZSQL_ERROR;
		$EZSQL_ERROR = array();
		if ( $were_showing_errors ) {
			$wpdb->show_errors();
		}

		return $retval;
	}

	/**
	 * Updates specific to 3.8.6
	 *
	 * @param string $iconFile
	 *
	 * @return string icon file
	 */
	function iconMapper( $iconFile ) {

		// Azure Bulb Name Change (default destination marker)
		//
		$newIcon =
			str_replace(
				'/store-locator-le/core/images/icons/a_marker_azure.png',
				'/store-locator-le/images/icons/bulb_azure.png',
				$iconFile
			);
		if ( $newIcon != $iconFile ) {
			return $newIcon;
		}

		// Box Yellow Home (default home marker)
		//
		$newIcon =
			str_replace(
				'/store-locator-le/core/images/icons/sign_yellow_home.png',
				'/store-locator-le/images/icons/box_yellow_home.png',
				$iconFile
			);
		if ( $newIcon != $iconFile ) {
			return $newIcon;
		}

		// General core/images/icons replaced with images/icons
		$newIcon =
			str_replace(
				'/store-locator-le/core/images/icons/',
				'/store-locator-le/images/icons/',
				$iconFile
			);
		if ( $newIcon != $iconFile ) {
			return $newIcon;
		}

		return $newIcon;
	}

	/**
	 * Setup the extended data tables.
	 *
	 * @used-by \SLPJanitor_Admin_Functions::drop_locations
	 */
	public function install_ExtendedDataTables() {
		global $wpdb;
		$charset_collate = '';
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}

		// Meta Data Table
		// Contains the architecture of the extended data fields.
		//
		$table_name   = $wpdb->prefix . 'slp_extendo_meta';
		$sql          = "CREATE TABLE $table_name (
                    id mediumint(8) unsigned not null auto_increment,
                    field_id varchar(15),
                    label varchar(250),
                    slug varchar(250),
                    type varchar(55),
                    options text,
                    PRIMARY KEY  (id),                        
                    KEY field_id (field_id),
                    KEY label (label),
                    KEY slug (slug)
            )
            $charset_collate
            ";
		$table_status = $this->dbupdater( $sql, $table_name );

		// Extendo Table Was Already Here
		// Set the next field id based on the extendo options and write it back out.
		//
		if ( $table_status === 'updated' ) {

			// Check that we did not import the next field ID first.
			//
			$slplus_options = get_option( SLPLUS_PREFIX . '-options_nojs' );
			if ( ! isset( $slplus_options['next_field_ported'] ) || empty( $slplus_options['next_field_ported'] ) ) {

				// Get the next field ID From Extendo
				//
				$extendo_options = get_option( 'slplus-extendo-options' );
				if ( isset ( $extendo_options['next_field_id'] ) ) {
					if ( ! is_array( $slplus_options ) ) {
						$slplus_options = array();
					}
					if ( isset ( $extendo_options['installed_version'] ) ) {
						unset( $extendo_options['installed_version'] );
					}
					array_merge( $slplus_options, $extendo_options );
				}

				// Set the ported flag and write that along with next field ID back to database.
				//
				$slplus_options['next_field_ported'] = '1';
				update_option( SLPLUS_PREFIX . '-options_nojs', $slplus_options );
			}
		}
	}

	/**
	 * Update main table
	 *
	 * As of version 3.5, use sl_option_value to store serialized options
	 * related to a single location.
	 *
	 * Update the plugin version in config.php on every structure change.
	 *
	 */
	function install_main_table() {

		global $wpdb;

		$charset_collate = '';
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= " COLLATE $wpdb->collate";
		}
		$table_name = $wpdb->prefix . "store_locator";
		$sql        = "CREATE TABLE $table_name (
            sl_id mediumint(8) unsigned NOT NULL auto_increment,
            sl_store varchar(255) NULL,
            sl_address varchar(255) NULL,
            sl_address2 varchar(255) NULL,
            sl_city varchar(255) NULL,
            sl_state varchar(255) NULL,
            sl_zip varchar(255) NULL,
            sl_country varchar(255) NULL,
            sl_latitude char(14) NULL,
            sl_longitude char(14) NULL,
            sl_tags mediumtext NULL,
            sl_description text NULL,
            sl_email varchar(255) NULL,
            sl_url varchar(255) NULL,
            sl_hours text NULL,
            sl_phone varchar(255) NULL,
            sl_fax varchar(255) NULL,
            sl_image varchar(255) NULL,
            sl_private varchar(1) NULL,
            sl_neat_title varchar(255) NULL,
            sl_linked_postid int(11) NULL,
            sl_pages_url varchar(255) NULL,
            sl_pages_on varchar(1) NULL,
            sl_option_value longtext NULL,
            sl_initial_distance numeric(15,10),
            sl_lastupdated timestamp NOT NULL default CURRENT_TIMESTAMP,
            PRIMARY KEY  (sl_id),
            KEY sl_store (sl_store),
            KEY latlng (sl_latitude,sl_longitude),
            KEY sl_initial_distance (sl_initial_distance)
            ) 
            $charset_collate
            ";

		// If we updated an existing DB, do some mods to the data
		//
		if ( $this->dbupdater( $sql, $table_name ) === 'updated' ) {

			// We are upgrading from something less than 4.6
			//
			if ( floatval( $this->slplus->installed_version ) < 4.6 ) {
				/** @noinspection PhpIncludeInspection */
				require_once( SLPLUS_PLUGINDIR . 'include/module/location/SLP_Location_Manager.php' );
				$this->slplus->Location_Manager->recalculate_initial_distance();
			}
		}
	}

	/**
	 * Multisite upgrade.
	 */
	private function multisite_upgrade_options() {

		// WordPress 4.6
		//
		if ( function_exists( 'get_sites' ) && class_exists( 'WP_Site_Query' ) ) {

			/**  @var   SLP_Admin_Upgrade $Upgrade * */
			$Upgrade = SLP_Admin_Upgrade::get_instance();

			$sites = get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				$Upgrade->migrate_settings();
				update_option( SLPLUS_PREFIX . '-installed_base_version', SLPLUS_VERSION );
				restore_current_blog();
			}

			return;
		}

		// WordPress < 4.6
		//
		if ( function_exists( 'wp_get_sites' ) ) {
			/**  @var   SLP_Admin_Upgrade $Upgrade * */
			$Upgrade = SLP_Admin_Upgrade::get_instance();

			/** @noinspection PhpDeprecationInspection */
			$sites = wp_get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site['blog_id'] );
				$Upgrade->migrate_settings();
				update_option( SLPLUS_PREFIX . '-installed_base_version', SLPLUS_VERSION );
				restore_current_blog();
			}

			return;
		}
	}

	/**
	 * Update the plugin.
	 */
	public function update() {
		if ( $this->being_deactivated() ) {
			return;
		}

		// Updating Previous Installation
		//
		if ( ! empty( $this->slplus->installed_version ) && ! is_null( $this->slplus->installed_version ) ) {

			// Restore Custom CSS Files
			$this->copyr( SLPLUS_UPLOADDIR . "css", SLPLUS_PLUGINDIR . "css" );

			// Core Icons Moved
			// Change home and end icon if it was in core/images/icons
			// @since 3.8.6
			//
			if ( is_dir( SLPLUS_PLUGINDIR . 'core/images/icons/' ) ) {
				$this->slplus->options['map_home_icon'] = $this->iconMapper( get_option( 'sl_map_home_icon' ) );
				$this->slplus->options['map_end_icon']  = $this->iconMapper( get_option( 'sl_map_end_icon' ) );
			}

			// Not multisite or not network active, only update settings for this one site.
			//
			if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}
			if ( ! is_multisite() || ! is_plugin_active_for_network( $this->slplus->slug ) ) {

				/**  @var   SLP_Admin_Upgrade $Upgrade * */
				$Upgrade = SLP_Admin_Upgrade::get_instance();
				$Upgrade->migrate_settings();

				// Multisite Network Active - migrate settings for each subsite
				//
			} else {
				$this->multisite_upgrade_options();
			}
		}

		// Update Tables, Setup Roles
		//
		$this->install_main_table();
		$this->install_ExtendedDataTables();
		$this->add_slplus_roles_and_caps();
		$this->check_for_addon_updates();

		// Always update these options
		//
		update_option( SLPLUS_PREFIX . '-installed_base_version', SLPLUS_VERSION );

		// Fresh install.
		//
		if ( empty( $this->slplus->installed_version ) ) {
			$this->slplus->database->set_database_meta();                                           // Connect DB meta info after create data objects.
			update_option( SLPLUS_PREFIX . '-options_nojs', $this->slplus->options_nojs );          // Save default options_nojs
			update_option( SLPLUS_PREFIX . '-options', $this->slplus->options );               // Save default options
		}
	}

}
