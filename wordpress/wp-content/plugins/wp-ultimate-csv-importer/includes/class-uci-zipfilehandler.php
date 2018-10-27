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

if(!defined('ABSPATH'))
	exit();
$import_method = isset($_POST['import_method']) ? sanitize_text_field($_POST['import_method']) : '';
$eventkey = isset($_POST['eventkey']) ? sanitize_key($_POST['eventkey']) : '';
$get_upload_url = wp_upload_dir();
//if($import_method == 'desktop'){
$put_zip_url = $get_upload_url['baseurl'] . '/smack_uci_uploads/imports/'. $eventkey . '/' . $eventkey;
$put_zip_path = $get_upload_url['basedir'] . '/smack_uci_uploads/imports/'. $eventkey . '/' . $eventkey;
$oldpath = $put_zip_path;
$target_path = $get_upload_url['basedir'] . '/smack_uci_uploads/imports/'. $eventkey;
$newpath = $target_path . '.zip';
copy($oldpath, $newpath);
unlink($put_zip_path);
if (class_exists('ZipArchive')) {
	$zip = new ZipArchive;
	$res = $zip->open($newpath);
	if ($res === TRUE) {
		$content = wp_csv_importer_generate_content($zip, $target_path, $import_method);
	}
	else {
		$returnData['return_message'] = 'Error Occured while extracting zip file.';
	}
	$returnData['data'] = $content;
	$returnData['import_method'] = $import_method;
	$returnData['path'] = $put_zip_url;
}else{
	echo 'ZipArchive class not exists';
}
echo json_encode($returnData);die;
//}
function wp_csv_importer_generate_content($zip, $dir, $import_method)
{
	$content = "";
	$get_upload_dir = wp_upload_dir();
	//$zip->extractTo($dir);
	for($i = 0; $i < $zip->numFiles; $i++)
	{
		$filterfiles = $zip->getNameIndex($i);
		if (!preg_match('#\.(html|php|js|zip|xml)$#i', $filterfiles))
		{
			$zip->extractTo($dir,$filterfiles);
		}
	}
	$ext_files = scandir($dir);
	$filesAndFoldersPath = array();
	$zipExtractFolder = $dir;
	$get_upload_dirpath =  $get_upload_dir['basedir'];
	$get_upload_dirurl =  $get_upload_dir['baseurl'];
	$filesList = wp_csv_importer_fetch_all_files($zipExtractFolder);
	foreach($filesList as $singleFile)      {
		$get_file_name = explode('/',$singleFile);
		$c = count($get_file_name);
		$temp_file_name = $get_file_name[$c - 1];
		$file_extension = pathinfo($temp_file_name, PATHINFO_EXTENSION);
		/*if($file_extension == 'csv'){
				$get_extension = explode('.csv', $temp_file_name);
		}else if($file_extension == 'xml'){
				$get_extension = explode('.xml', $temp_file_name);
		}else if($file_extension == 'txt'){
				$get_extension = explode('.txt', $temp_file_name);
		}*/
		//              $get_extension = explode('.',$temp_file_name);

		// getting folder path
		/*$getFileRealPath = str_replace($zipExtractFolder, "", $singleFile);
		$getFileRealPath = str_replace($temp_file_name, "", $getFileRealPath);
		$getFileRealPath = rtrim($getFileRealPath, '/');*/
		$getFileRealPath = explode($get_upload_dirpath,$singleFile);
		$getFileRealPath = $get_upload_dirurl.$getFileRealPath[1];
		if($file_extension == 'csv' || $file_extension == 'xml' || $file_extension == 'txt')  {
			$content .= "<label> <span class = 'radio label_model_radio'> <input type ='radio' id = '$temp_file_name' name = 'list' onclick='choose_file_from_zip(this.id, \"$getFileRealPath\",\"$import_method\");'/>";
			$content .= "<span class = 'label label-default label_model_radio_text'> $temp_file_name </span> </label> <br/>";
		}
	}
	$zip->close();
	return $content;
}
/**
 * scan folder directory and return all files
 * @param string $dir
 * @return array $files
 */
function wp_csv_importer_fetch_all_files($dir)
{
	$root = scandir($dir);
	foreach($root as $value)
	{
		if($value === '.' || $value === '..')
			continue;

		if(is_file("$dir/$value"))      {
			$files[] = "$dir/$value";continue;
		}

		foreach(wp_csv_importer_fetch_all_files("$dir/$value") as $value)
		{
			$files[] = $value;
		}
	}
	return $files;
}
