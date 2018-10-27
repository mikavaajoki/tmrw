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

class SmackUCIUnInstall {
	/**
	 * UnInstall UCI Pro.
	 */
	public static function uninstall() {
		global $wpdb;

		$wpdb->hide_errors();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		// Roles + caps.
		#include_once ( 'includes/class-uci-install.php' );
		SmackUCIInstall::remove_options();
		$ucisettings = get_option('sm_uci_pro_settings');
		$droptable = isset($ucisettings['drop_table']) ? $ucisettings['drop_table'] : '';
		if(!empty($droptable) && $droptable == 'on'){
			#$tables[] = 'drop table smack_csv_dashboard';
			$tables[] = 'drop table smack_csv_manager';
			$tables[] = 'drop table smack_field_types';
			$tables[] = 'drop table smackcsv_status_log';
			$tables[] = 'drop table wp_ultimate_csv_importer_acf_fields';
			$tables[] = 'drop table wp_ultimate_csv_importer_eventkey_manager';
			$tables[] = 'drop table wp_ultimate_csv_importer_exclusion_lists';
			$tables[] = 'drop table wp_ultimate_csv_importer_external_file_schedules';
			$tables[] = 'drop table wp_ultimate_csv_importer_filemanager';
			$tables[] = 'drop table wp_ultimate_csv_importer_ftp_schedules';
			$tables[] = 'drop table wp_ultimate_csv_importer_log_values';
			$tables[] = 'drop table wp_ultimate_csv_importer_manageshortcodes';
			$tables[] = 'drop table wp_ultimate_csv_importer_mappingtemplate';
			$tables[] = 'drop table wp_ultimate_csv_importer_multisite_details';
			$tables[] = 'drop table wp_ultimate_csv_importer_scheduled_import';
			$tables[] = 'drop table wp_ultimate_csv_importer_shortcodes_statusrel';
			$tables[] = 'drop table wp_ultimate_csv_importer_uploaded_file_schedules';
			$tables[] = 'drop table SmackUCI_manage_records';
			$tables[] = 'drop table SmackUCI_event_informations';
			$tables[] = 'drop table smackuci_events';
			$tables[] = 'drop table smackuci_history';

			foreach($tables as $table) {
				$wpdb->query($table, array());
			}
		}
	}
}
