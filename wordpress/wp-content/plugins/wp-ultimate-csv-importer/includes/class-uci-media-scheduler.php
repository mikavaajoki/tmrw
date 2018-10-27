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

class SmackUCIMediaScheduler {

	public $admin_user_id = null;

	public static function get_admin_user_id() {

	}

	public static function populateFeatureImages() {
		global $wpdb;

		if(defined('DISABLE_WP_CRON') && DISABLE_WP_CRON == true)
			return false;

		$result = $wpdb->get_results($wpdb->prepare("select option_name, option_value from $wpdb->options where option_name like %s", '%smack_featured_%'));
		if(!empty($result)) {
			foreach($result as $val) {
				$thumbnailId = null;
				$post_id = explode('smack_featured_', $val->option_name);
				$postID = $post_id[1];
				$get_image_info = maybe_unserialize($val->option_value);
				$url = $get_image_info['value'];
				preg_match( '/[^\?]+\.(jpg|jpe|jpeg|gif|png)/i', $url, $matches );
				$renameImage = basename( $matches[0] );
				# Removed: NextGen Gallery Support
				$thumbnailId = self::convert_local_imageURL( $url, $postID, $renameImage, $get_image_info['media_settings'] );
				if($thumbnailId != null) {
					set_post_thumbnail( $postID, $thumbnailId );
					delete_option( $val->option_name );
				}
			}
		}
	}

	public static function convert_local_imageURL ($imageURL, $post_id, $renameimage = NULL, $media_settings = array()) {
		require_once(ABSPATH . "wp-includes/pluggable.php");
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$dir = wp_upload_dir();
		$existing_attachment = $existing_attachment_url = $file = array();
		$get_media_settings = get_option('uploads_use_yearmonth_folders');
		$img_extension = 'jpg';
		$mime_type = 'image/jpeg';
		$attach_id = null;
		$realImageURL = $imageURL;
		global $wpdb;
		if ($get_media_settings == 1) {
			$dirname = date('Y') . '/' . date('m');
			$full_path = $dir ['basedir'] . '/' . $dirname;
			$baseurl = $dir ['baseurl'] . '/' . $dirname;
		} else {
			$full_path = $dir ['basedir'];
			$baseurl = $dir ['baseurl'];
		}
		$fimg_path = $full_path;
		$image_name = basename($imageURL);
		$path_parts = pathinfo($imageURL);
		if (isset($path_parts['extension'])) {
			$img_extension = $path_parts['extension'];
		}
		$fimg_type = wp_check_filetype( $image_name, null );

		if (isset($fimg_type['type'])) {
			$mime_type = $fimg_type['type'];
		} elseif (isset($fimg_type['ext'])) {
			$mime_type = $fimg_type['ext'];
		}

		$real_image_name = $path_parts['filename'];
		$real_image_name = preg_replace("/[^a-zA-Z0-9.\s_-]/", "", $real_image_name);
		$real_image_name = preg_replace('/\s/', '-',$real_image_name);
		$real_image_name = urlencode($real_image_name);
		$media_settings['medium_large'] = $media_settings['large'] = 0;
		# Removed: Add custom media sizes

		//duplicate media
		$query = $wpdb->get_results($wpdb->prepare("select post_title, guid from $wpdb->posts where post_type = %s", 'attachment'));
		foreach($query as $key){
			$existing_attachment[] = $key->post_title;
			$existing_attachment_url[$key->post_title][] = $key->guid;
		}

		$rename_file_name = null;
		# Removed: Image renaming feature

		//duplicate media
		$is_exist = false;

		# Removed: Use existing & Overwriting existing image feature

		if($rename_file_name != null) {
			if(in_array($rename_file_name, $existing_attachment)){
				$imageURL = $existing_attachment_url[ $real_image_name ][0];
				$fimg_name = wp_unique_filename($fimg_path, $rename_file_name, $img_extension);
			} else{
				$fimg_name = $rename_file_name . '.' . $img_extension;
			}
		} else {
			if($is_exist == false) {
				$fimg_name = wp_unique_filename( $fimg_path, $path_parts['basename'], $img_extension );
				self::get_img_from_URL($imageURL, $fimg_path, $fimg_name);
				$imageURL = $baseurl . "/" . $fimg_name;
			} else {
				$fimg_name = $path_parts['basename'];
			}
		}

		$filepath = $fimg_path . "/" . $fimg_name;

		if(@getimagesize($filepath)) {
			$file ['guid'] = $imageURL;
			// Image title
			$file ['post_title'] = preg_replace('/\.[^.]+$/', '', @basename($file ['guid']));
			// Removed: Custom image title
			// Removed: Custom image description
			// Removed: Custom image caption
			// Removed: Custom image alt text
			$file ['post_status'] = 'inherit';
			$file ['post_type'] = 'attachment';
			$file ['post_mime_type'] = $mime_type;
		} else {
			$parserObj = new SmackCSVParser();
			$parserObj->logW('Invalid Image URL: ', $realImageURL);
			delete_option('smack_featured_' . $post_id);
			unset($parserObj);
		}

		if (!empty ($file)) {
			$attachment = $file;
			if ($get_media_settings == 1) {
				$generate_attachment = $dirname . '/' . $fimg_name;
			} else {
				$generate_attachment = $fimg_name;
			}
			$uploadedImage = $dir['path'] . '/' . $fimg_name;

			// Removed: Duplicate check for media

			$attach_id = wp_insert_attachment($attachment, $generate_attachment, $post_id);
			$attach_data = wp_generate_attachment_metadata($attach_id, $uploadedImage);
			wp_update_attachment_metadata($attach_id, $attach_data);
		}
		if($attach_id != null && isset($attachment['_wp_attachment_image_alt']))
			update_post_meta($attach_id, '_wp_attachment_image_alt', $attachment['_wp_attachment_image_alt']);

		return $attach_id;
	}

	public static function get_img_from_URL($f_img, $fimg_path, $fimg_name) {
		//$f_img = str_replace(" ", "%20", $f_img);
		if ($fimg_path != "" && $fimg_path) {
			$fimg_path = $fimg_path . "/" . $fimg_name;
		}
		// curl removed and used wordpress api for https image support
		$response = wp_remote_get($f_img);
		$rawdata =  wp_remote_retrieve_body($response);
		$http_code = wp_remote_retrieve_response_code($response);
		if ( $http_code != 200 && strpos( $rawdata, 'Not Found' ) != 0 ) {
			$rawdata = false;
		}
		if ( $rawdata == false ) {
			return null;
		} else {
			if ( file_exists( $fimg_path ) ) {
				unlink( $fimg_path );
			}
			$fp = fopen( $fimg_path, 'x' );
			fwrite( $fp, $rawdata );
			fclose( $fp );
		}
	}

	public static function active_plugins() {
		return get_option('active_plugins');
	}
}
