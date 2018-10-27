<?php
/*********************************************************************************
 * WP Ultimate CSV Importer is a Tool for importing CSV for the Wordpress
 * plugin developed by Smackcoders. Copyright (C) 2016 Smackcoders.
 *
 * WP Ultimate CSV Importer is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License version 3
 * as published by the Free Software Foundation with the addition of the
 * following permission added to Section 15 as permitted in Section 7(a): FOR
 * ANY PART OF THE COVERED WORK IN WHICH THE COPYRIGHT IS OWNED BY WP Ultimate
 * CSV Importer, WP Ultimate CSV Importer DISCLAIMS THE WARRANTY OF NON
 * INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * WP Ultimate CSV Importer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public
 * License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program; if not, see http://www.gnu.org/licenses or write
 * to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA 02110-1301 USA.
 *
 * You can contact Smackcoders at email address info@smackcoders.com.
 *
 * The interactive user interfaces in original and modified versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * WP Ultimate CSV Importer copyright notice. If the display of the logo is
 * not reasonably feasible for technical reasons, the Appropriate Legal
 * Notices must display the words
 * "Copyright Smackcoders. 2016. All rights reserved".
 ********************************************************************************/

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class SmackUCIInstall {
	/**
	 * Install UCI Pro
	 */

	/** @var array DB updates that need to be run */
	private static $db_updates = array(
		'3.5'   => 'updates/sm-uci-update-3.5.php',
		'3.5.3' => 'updates/sm-uci-update-3.5.3.php',
		'3.6'   => 'updates/sm-uci-update-3.6.php',
		'4.0.0' => 'updates/sm-uci-update-4.0.0.php',
		'4.1.0' => 'updates/sm-uci-update-4.1.0.php',
		'4.x'   => 'updates/sm-uci-update-4.x.php',
	);

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'check_version' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( __CLASS__, 'smack_uci_action_links' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );
		#add_filter( 'cron_schedules', array( __CLASS__, 'cron_schedules' ) );
	}

	/**
	 * Check WPUltimateCSVImporterPro version.
	 */
	public static function check_version() {
		if ( get_option( 'ULTIMATE_CSV_IMP_VERSION' ) != SmackUCI()->version )  {
			self::install();
			do_action( 'sm_uci_pro_updated' );
		}
	}

	/**
	 * Install actions when a update button is clicked.
	 */
	public static function install_actions() {
		if ( ! empty( $_GET['do_update_sm_uci_pro'] ) ) {
			self::update();
			#WC_Admin_Notices::remove_notice( 'update' );
			#add_action( 'admin_notices', array( __CLASS__, 'updated_notice' ) );
		}
	}

	/**
	 * Show notice stating update was successful.
	 */
	public static function updated_notice() {
		?>
		<div id="message" class="updated uci-message wc-connect">
			<p><?php _e( 'Ultimate CSV Importer PRO data update complete. Thank you for updating to the latest version!', 'wp_ultimate_csv_importer_pro' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Install WUCI.
	 */
	public static function install() {

		if ( ! defined( 'SM_UCI_INSTALLING' ) ) {
			define( 'SM_UCI_INSTALLING', true );
		}
		// Ensure needed classes are loaded
		//include_once( '../admin/class-wuci-admin-notices.php' );

		// Queue upgrades/setup wizard
		$current_uci_version    = get_option( 'ULTIMATE_CSV_IMP_VERSION', null );
		#$upgraded_version = get_option('ULTIMATE_CSV_IMPORTER_UPGRADE_VERSION', null);
		#$major_wc_version = substr( SmackUCI()->version, 0, strrpos( SmackUCI()->version, '.' ) );
		self::init();
		// No versions? This is a new install :)
		if ( is_null( $current_uci_version ) && apply_filters( 'sm_uci_enable_setup_wizard', true ) ) {
			self::create_options();         // Create option data on the initial stage
			self::create_tables();          // Create tables on the fresh install
			#self::create_cron_jobs();       // Create default cron jobs
			self::create_files();           // Create needed files on the fresh installation
		}

		self::update_uci_version();

		// Flush rules after install
		flush_rewrite_rules();

		// Trigger action
		do_action( 'sm_uci_installed' );
	}

	/**
	 * Update UCI version to current.
	 */
	private static function update_uci_version() {
		delete_option( 'ULTIMATE_CSV_IMP_VERSION' );
		add_option( 'ULTIMATE_CSV_IMP_VERSION', SmackUCI()->version );
	}

	/**
	 * @param null $version
	 * Update DB version to current.
	 */
	private static function update_db_version( $version = null ) {
		delete_option( 'sm_uci_db_version' );
		add_option( 'sm_uci_db_version', is_null( $version ) ? SmackUCI()->version : $version );
	}

	/**
	 * Handle updates.
	 */
	private static function update() {
		$current_db_version = get_option( 'woocommerce_db_version' );

		foreach ( self::$db_updates as $version => $updater ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				include_once ( $updater );
				self::update_db_version( $version );
			}
		}

		self::update_db_version();
	}

	/**
	 * Add more cron schedules.
	 * @param  array $schedules
	 * @return array
	 */
	public static function cron_schedules( $schedules ) {
		return array(
			'wp_ultimate_csv_importer_scheduled_images' => array(
				'interval' => 10, // seconds
				'display' => __('Schedule images on every second', SM_UCI_SLUG)
			),
			'wp_ultimate_csv_importer_scheduled_emails' => array(
				'interval' => 5, // seconds
				'display' => __('Schedule emails on every second', SM_UCI_SLUG)
			),
		);
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 */
	private static function create_options() {

		// We assign the default option data for the fresh instalization
		$settings = array('debug_mode' => 'off',
		                  'send_log_email' => 'on',
		                  'drop_table' => 'off',
		                  'author_editor_access' => 'off',
		                  'woocomattr' => 'off'
		);

		add_option('sm_uci_pro_settings', $settings);

	}

	/**
	 * Set up the database tables which the plugin needs to function.
	 *
	 * Tables:
	 * smackuci_events             - Table for storing all the events information's
	 * wp_ultimate_csv_importer_manageshortcodes       - Table for storing all short-code information's to the specific event
	 * wp_ultimate_csv_importer_shortcodes_statusrel   - Table for storing all short-code relational status to the specific event
	 * wp_ultimate_csv_importer_log_values      - Table for storing all log values to the specific events
	 *
	 */
	private static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		foreach(self::get_schema() as $table) {
			dbDelta($table);
		}
		/** Removed: Insert all custom field controls of ACF,PODS and TYPES */
	}

	/**
	 * Table schema for Dashboards, Import Events, Scheduling Events, Logs, File Manager, Short-code & Exclusions.
	 * @return string
	 */
	private static function get_schema() {
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty( $wpdb->charset ) ) {
				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$collate .= " COLLATE $wpdb->collate";
			}
		}

		$tables = array(
			"CREATE TABLE IF NOT EXISTS `wp_ultimate_csv_importer_manageshortcodes` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `pID` int(20) DEFAULT NULL,
                        `shortcode` varchar(110) DEFAULT NULL,
                        `eventkey` varchar(60) DEFAULT NULL,
                        `mode_of_code` varchar(20) DEFAULT NULL,
                        `module` varchar(20) DEFAULT NULL,
                        `populate_status` int(5) DEFAULT '1',
                        PRIMARY KEY (`id`)
	                ) $collate;",
			"CREATE TABLE IF NOT EXISTS `wp_ultimate_csv_importer_shortcodes_statusrel` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `eventkey` varchar(60) DEFAULT NULL,
                        `shortcodes_count` int(20) DEFAULT NULL,
                        `shortcode_mode` varchar(20) DEFAULT NULL,
                        `current_status` varchar(20) DEFAULT 'Pending',
                        PRIMARY KEY (`id`)
	                ) $collate;",
			"CREATE TABLE IF NOT EXISTS  `wp_ultimate_csv_importer_log_values` (
                        `id` int(10) NOT NULL AUTO_INCREMENT,
                        `eventKey` varchar(50) NOT NULL,
                        `recordId` int(10) NOT NULL,
                        `module` varchar(50) NOT NULL,
                        `method_of_import` varchar(50) NOT NULL,
                        `log_message` blob NOT NULL,
                        `imported_time` varchar(100) NOT NULL,
                        `mode_of_import` varchar(100) NOT NULL,
                        `sequence` varchar(100) NOT NULL,
                        `status` varchar(100) NOT NULL,
                        `assigned_user_id` int(10) NOT NULL,
                        `imported_by` int(100) NOT NULL,
                        PRIMARY KEY (`id`)
	                ) $collate;",
			"CREATE TABLE IF NOT EXISTS `smackuci_events` (
						`id` bigint(20) NOT NULL AUTO_INCREMENT,
						`revision` bigint(20) NOT NULL default 0,
						`name` varchar(255),
						`original_file_name` varchar(255),
						`friendly_name` varchar(255),
						`import_type` varchar(32),
						`filetype` text,
						`filepath` text,
						`eventKey` varchar(32),
						`registered_on` datetime NOT NULL default '0000-00-00 00:00:00',
						`parent_node` varchar(255),
						`processing` tinyint(1) NOT NULL default 0,
						`executing` tinyint(1) NOT NULL default 0,
						`triggered` tinyint(1) NOT NULL default 0,
						`event_started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						`count` bigint(20) NOT NULL default 0,
						`processed` bigint(20) NOT NULL default 0,
						`created` bigint(20) NOT NULL default 0,
						`updated` bigint(20) NOT NULL default 0,
						`skipped` bigint(20) NOT NULL default 0,
						`deleted` bigint(20) NOT NULL default 0,
						`is_terminated` tinyint(1) NOT NULL default 0,
						`terminated_on` datetime NOT NULL default '0000-00-00 00:00:00',
						`last_activity` datetime NOT NULL default '0000-00-00 00:00:00',
						`siteid` int(11) NOT NULL DEFAULT 1,
						`month` varchar(60) DEFAULT NULL,
                        `year` varchar(60) DEFAULT NULL,
						PRIMARY KEY ( id )
					) $collate;",
			"CREATE TABLE IF NOT EXISTS `smackuci_history` (
						`id` bigint(20) NOT NULL AUTO_INCREMENT,
						`event_id` bigint(20) NOT NULL,
						`time_taken` text,
						`date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
						`summary` text,
						PRIMARY KEY (id)
					) $collate;",
		);

		return $tables;
	}

	/**
	 * Todo: add PHP docs
	 */
	public static function remove_options() {
		delete_option('ULTIMATE_CSV_IMP_VERSION');
		delete_option('ULTIMATE_CSV_IMPORTER_UPGRADE_VERSION');
	}

	/**
	 * Create files/directories.
	 */
	private static function create_files() {
		// Install files and folders for uploading files and prevent hot linking

		$files = array(
			array(
				'base'          => SM_UCI_FILE_MANAGING_DIR,
				'file'          => '.htaccess',
				'content'       => 'deny from all'
			),
			array(
				'base'          => SM_UCI_FILE_MANAGING_DIR,
				'file'          => 'index.html',
				'content'       => ''
			),
			array(
				'base'          => SM_UCI_LOG_DIR,
				'file'          => '.htaccess',
				'content'       => 'deny from all'
			),
			array(
				'base'          => SM_UCI_LOG_DIR,
				'file'          => 'index.html',
				'content'       => ''
			),
			array(
				'base'          => SM_UCI_EXPORT_DIR,
				'file'          => '.htaccess',
				'content'       => 'deny from all'
			),
			array(
				'base'          => SM_UCI_EXPORT_DIR,
				'file'          => 'index.html',
				'content'       => ''
			)
		);
		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				@chmod($file['base'], 0777);
				if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}
		}
	}

	/**
	 * @param $links
	 *
	 * @return array
	 */
	public function smack_uci_action_links( $links ) {
		$links[] = '<a href="'. esc_url( get_admin_url(null, 'admin.php?page=sm-uci-settings') ) .'">Settings</a>';
		$links[] = '<a href="http://wp-buddy.com" target="_blank">More plugins by WP-Buddy</a>';
		return $links;
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param       mixed $links Plugin Row Meta
	 * @param       mixed $file  Plugin Base file
	 * @return      array
	 */
	public static function plugin_row_meta( $links, $file ) {
		if ( $file == SM_UCI_PLUGIN_BASENAME ) {
			$row_meta = array(
				'upgrade_to_csv_pro' => '<a style="font-weight: bold;color: #d54e21;font-size: 105%;" href="' . esc_url( apply_filters( 'upgrade_to_csv_pro_url',  'http://www.smackcoders.com/wp-ultimate-csv-importer-pro.html?utm_source=plugin&utm_campaign=csv_importer_free&utm_medium=wordpress' ) ) . '" title="' . esc_attr( __( 'Upgrade to Pro', 'wp-ultimate-csv-importer' ) ) . '" target="_blank">' . __( 'Upgrade to Pro', 'wp-ultimate-csv-importer' ) . '</a>',
				'docs'    => '<a href="' . esc_url( apply_filters( 'sm_uci_docs_url', 'http://www.smackcoders.com/documentation/ultimate-csv-importer-pro/how-to-import-csv?utm_source=plugin&utm_campaign=csv_importer_pro&utm_medium=wordpress' ) ) . '" title="' . esc_attr( __( 'View WP Ultimate CSV Importer Pro Documentation', 'wp-ultimate-csv-importer' ) ) . '" target="_blank">' . __( 'Docs', 'wp-ultimate-csv-importer' ) . '</a>',
				'videos' => '<a href="' . esc_url( apply_filters( 'sm_uci_videos_url', 'https://www.youtube.com/embed/GbDlQcbnNJY?utm_source=plugin&utm_campaign=csv_importer_free&utm_medium=wordpress' ) ) . '" title="' . esc_attr( __( 'View Videos For WP Ultimate CSV Importer Pro', 'wp-ultimate-csv-importer' ) ) . '" target="_blank">' . __( 'Videos', 'wp-ultimate-csv-importer' ) . '</a>',
				'support' => '<a href="' . esc_url( apply_filters( 'sm_uci_support_url', admin_url() . 'admin.php?page=sm-uci-support' ) ) . '" title="' . esc_attr( __( 'Contact Support', 'wp-ultimate-csv-importer' ) ) . '" target="_blank">' . __( 'Support', 'wp-ultimate-csv-importer' ) . '</a>',
				'free_trial' => '<a href="' . esc_url( apply_filters( 'sm_uci_support_url', 'http://www.smackcoders.com/wp-ultimate-csv-importer-pro.html?utm_source=plugin&utm_campaign=csv_importer_free&utm_medium=wordpress' ) ) . '" title="' . esc_attr( __( 'Get your free trial', 'wp-ultimate-csv-importer' ) ) . '" target="_blank">' . __( 'Free Trial', 'wp-ultimate-csv-importer' ) . '</a>',
			);
			unset( $links['edit'] );
			//unset($links['View details']);

			return array_merge( $row_meta, $links );
		}

		return (array) $links;
	}

	public static function showUpgradeNotification($currentPluginMetadata, $newPluginMetadata){
		// check "upgrade_notice"
		if (isset($newPluginMetadata->upgrade_notice) && strlen(trim($newPluginMetadata->upgrade_notice)) > 0){
			echo '<p style="background-color: #d54e21; padding: 10px; color: #f9f9f9; margin-top: 10px"><strong>'.esc_html__('Important Upgrade Notice:','wp-ultimate-csv-importer').'</strong> ';
			echo esc_html($newPluginMetadata->upgrade_notice), '</p>';
		}
	}

	public static function important_upgrade_notice() {
		$get_notice = get_option('smack_uci_upgrade_notice');
		if($get_notice != 'off') {
			?>
			<div class="notice notice-error is-dismissible" onclick="dismiss_notices('upgrade_notice');">
				<p style="margin-top: 10px"><strong><?php echo esc_html__('Upgrade Notice:','wp-ultimate-csv-importer');?> </strong> <?php echo esc_html__('Download and replace the latest version of','wp-ultimate-csv-importer');?> <a href="https://wordpress.org/plugins/wp-ultimate-csv-importer/" target="_blank">WP Ultimate CSV Importer</a> <?php echo esc_html__('for 10x faster import performance with easy user interface.','wp-ultimate-csv-importer');?> </p>
			</div>
			<?php
		}
	}

	 public static function important_cron_notice() {
                $get_notice = get_option('smack_uci_enable_cron_notice');
                if($get_notice != 'off' && isset($_REQUEST['page']) && $_REQUEST['page'] == 'sm-uci-import') {
                        ?>
                        <div class="notice notice-error wc-connect is-dismissible" onclick="dismiss_notices('enable_cron_notice');" >
                        <p style="margin-top: 10px">
                        <strong><?php echo esc_html__( 'Notice:', 'wp-ultimate-csv-importer' ); ?> </strong> <?php echo esc_html__( 'To populate Featured images, Please make sure that CRON is enabled in your server. ', 'wp-ultimate-csv-importer' ); ?></p>
                        </div>
                        <?php
                        if(function_exists( 'curl_version' ) == null || function_exists( 'curl_version' ) == '' && isset($_REQUEST['page']) && $_REQUEST['page'] == 'sm-uci-import') { ?>
                                <div class="notice notice-error">
                                        <p style="margin-top: 10px;">
                                                <strong><?php echo esc_html__( 'Notice:', 'wp-ultimate-csv-importer' ); ?> </strong> <?php echo esc_html__( 'Please install CURL & enable it in your server. ', 'wp-ultimate-csv-importer' ); ?>
                                        </p>
                                </div>
                        <?php }
                }
        }
	public static function wp_ultimate_csv_importer_notice() {
                $get_notice = get_option('smack_uci_rating_notice');
                $smack_uci_pages = array('sm-uci-import', 'sm-uci-dashboard', 'sm-uci-managers', 'sm-uci-export', 'sm-uci-settings', 'sm-uci-support');
                if($get_notice != 'off' && isset($_REQUEST['page']) && in_array($_REQUEST['page'], $smack_uci_pages)) {
                        ?>
                        <div class='notice Updated uci-message wc-connect is-dismissible' onclick="dismiss_notices('rating_notice');">
                        <p><?php echo esc_html__("If you love WP Ultimate CSV Importer show us you care with a 5-star review on","wp-ultimate-csv-importer")?> <a href='https://wordpress.org/support/plugin/wp-ultimate-csv-importer/reviews/?rate=5#new-post' target='_blank'><?php echo esc_html__('wordpress.org!','wp-ultimate-csv-importer') ?></a></p>
                        </p></div>
                        <?php
                }
        } 
}
