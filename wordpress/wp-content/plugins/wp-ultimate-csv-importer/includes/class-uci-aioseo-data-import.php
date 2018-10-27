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

class SmackUCIAIOSEODataImport {
	
	public function __construct() {
		global $uci_admin;
		$aioseodata = $uci_admin->getRowMapping();
		$data_array = $aioseodata['AIOSEO'];
		if( !empty($aioseodata)) {
			if(in_array('all-in-one-seo-pack/all_in_one_seo_pack.php', $uci_admin->get_active_plugins())) {
				$this->importDataForAIOSEOFields( $data_array, $uci_admin->getImportAs(), $uci_admin->getLastImportId());
			}
		}
	}

	public function importDataForAIOSEOFields ($data_array, $importas,$pID) {
		$createdFields = array();
		foreach($data_array as $dkey => $dvalue) {
			$createdFields[] = $dkey;
		}
		if(isset($data_array['keywords'])) {
			$custom_array['_aioseop_keywords'] = $data_array['keywords'];
		}
		if(isset($data_array['description'])) {
			$custom_array['_aioseop_description'] = $data_array['description'];
		}
		if(isset($data_array['title'])) {
			$custom_array['_aioseop_title'] = $data_array['title'];
		}
		if(isset($data_array['noindex'])) {
			$custom_array['_aioseop_noindex'] = $data_array['noindex'];
		}
		if(isset($data_array['nofollow'])) {
			$custom_array['_aioseop_nofollow'] = $data_array['nofollow'];
		}
		if(isset($data_array['custom_link'])) {
                       $custom_array['_aioseop_custom_link'] = $data_array['custom_link'];
                }
		if(isset($data_array['noodp'])) {
			$custom_array['_aioseop_noodp'] = $data_array['noodp'];
		}
		if(isset($data_array['noydir'])) {
			$custom_array['_aioseop_noydir'] = $data_array['noydir'];
		}
		if(isset($data_array['titleatr'])) {
			$custom_array['_aioseop_titleatr'] = $data_array['titleatr'];
		}
		if(isset($data_array['menulabel'])) {
			$custom_array['_aioseop_menulabel'] = $data_array['menulabel'];
		}
		if(isset($data_array['disable'])) {
			$custom_array['_aioseop_disable'] = $data_array['disable'];
		}
		if(isset($data_array['disable_analytics'])) {
			$custom_array['_aioseop_disable_analytics'] = $data_array['disable_analytics'];
		}
		if(!empty ($custom_array)) {
			foreach($custom_array as $custom_key => $custom_value) {
				update_post_meta($pID, $custom_key, $custom_value);
			}
		}
		return $createdFields;
	}
}

return new SmackUCIAIOSEODataImport();
