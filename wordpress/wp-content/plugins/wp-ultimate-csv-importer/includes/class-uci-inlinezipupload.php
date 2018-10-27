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
global $wpdb;
$uploadDir = wp_upload_dir();
if(isset($_FILES['file'])) {
	if (0 < $_FILES['file']['error']) {
		if ($_FILES['file']['error'] == 1 || $_FILES['file']['error'] == 2) {
			echo 'Uploaded file size exceeds the MAX Size in php.ini';
		} else {
			echo 'Error: ' . $_FILES['file']['error'] . '<br>';
		}
	} else {
		if (!empty($_FILES['file']['name'])) {
			$uploaded_compressedFile = '';
			$eventkey = sanitize_key($_REQUEST['eventkey']);
			$uploaded_compressedFile = $_FILES['file']['tmp_name'];
			$uploadeddir = SM_UCI_IMPORT_DIR .'/'. $eventkey . '/inline_zip_uploads';
			if (!is_dir($uploadeddir)) {
				wp_mkdir_p($uploadeddir);
			}
			$location_to_extract = $uploadeddir;
			if (class_exists('ZipArchive')) {
				$zip = new ZipArchive;
				if ($zip->open($uploaded_compressedFile) === TRUE) {
					for($i = 0; $i < $zip->numFiles; $i++)
					 {
						$filterfiles = $zip->getNameIndex($i);
						if (!preg_match('#\.(html|php|js|zip|xml)$#i', $filterfiles))
						{
						    $zip->extractTo($location_to_extract,$filterfiles);
						}
					}
					$zip->close();
					echo 'success';
				} else {
					echo 'failure';
				}
			}
		}
	}
}
die();
