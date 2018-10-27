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
class SmackUCICustomerReviews {

	public function importDataForCustomerReviews ($data_array, $mode, $eventKey, $duplicateHandling) {
		global $wpdb, $uci_admin;
		$is_handle_duplicate = $duplicateHandling['is_duplicate_handle'];
		$conditions = $duplicateHandling['conditions'];
		$duplicate_action = $duplicateHandling['action'];
		if($is_handle_duplicate != 'off' && ($duplicate_action == 'Update' || $duplicate_action == 'Skip')):
			$mode = 'Update';
		endif;
		$crid = null;
		$reviewId = '';
		$returnArray = array('MODE' => $mode);
		$mode_of_affect = 'Inserted';
		$update_review_info = false;
		if($mode != 'Insert' && !empty($conditions)):
			if (in_array('review_id', $conditions)) {
				$update_review_info = true;
			}
		endif;
		if(isset($data_array['review_format'])) {
		#$reviewFormat = strtolower($data_array['review_format']);
		if(!array_key_exists('review_format', $data_array) && !isset($data_array['review_format']) && !empty($data_array['review_format'])) {
			$reviewFormat = 'business';
		} else {
			$reviewFormat = strtolower($data_array['review_format']);
		}
		}
		$post_id = $data_array['page_id'];
		$post_exists = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE id = '" . $post_id . "' and post_status in ('publish','draft','future','private','pending')", 'ARRAY_A');
		$get_available_plugin_lists = $uci_admin->get_active_plugins();
		if($mode == 'Insert') {
			if ($post_exists) {
				update_post_meta($post_id, 'wpcr3_enable', 1);
				update_post_meta($post_id, 'wpcr3_format', $reviewFormat);
				if(in_array('wp-customer-reviews/wp-customer-reviews-3.php', $get_available_plugin_lists)) {
					$review_date = current_time('mysql', 0);
					if(isset($data_array['date_time'])) {
						$review_date = date( 'Y-m-d H:i:s', strtotime( $data_array['date_time'] ) );
					}
					$review_title = $data_array['reviewer_name'] . ' @ ' . $review_date;
					$review_slug = preg_replace('/[^a-zA-Z0-9._\-\s]/', '', $review_title);
					$review_slug = wp_unique_filename('', $review_slug);
					if(isset($data_array['status'])) {
						$data_array['status'] = strtolower( $data_array['status'] );
					}
					if ($data_array['status'] != 'publish' && $data_array['status'] != 'private' && $data_array['status'] != 'draft' && $data_array['status'] != 'pending' && $data_array['status'] != 'sticky') {
						$data_array ['post_password'] = '';
						$stripPSF = strpos($data_array['status'], '{');
						if ($stripPSF === 0) {
							$poststatus = substr($data_array['status'], 1);
							$stripPSL = substr($poststatus, -1);
							if ($stripPSL == '}') {
								$postpwd = substr($poststatus, 0, -1);
								$data_array['status'] = 'publish';
								$data_array ['post_password'] = $postpwd;
							} else {
								$data_array['status'] = 'publish';
								$data_array ['post_password'] = $poststatus;
							}
						} else {
							$data_array['status'] = 'publish';
						}
					}
					$review_array = array(
						'post_author' => '1',
						'post_date' => $review_date,
						'post_content' => $data_array['review_text'],
						'post_title' => $review_title,
						'post_status' => $data_array['status'],
						'comment_status' => 'closed',
						'ping_status' => 'closed',
						'post_password' => $data_array['post_password'],
						'post_name' => $review_slug,
						'post_parent' => 0,
						'post_type' => 'wpcr3_review',
					);
					$reviewId = wp_insert_post($review_array);
					if(is_wp_error($reviewId)) {
						$uci_admin->setSkippedRowCount($uci_admin->getSkippedRowCount() + 1);
						$uci_admin->detailed_log[$uci_admin->processing_row_id]['Message'] = "Can't insert this Review. " . $reviewId->get_error_message();
						return $returnArray;
					}
					$guId = site_url() . '/?post_type=wpcr3_review&#038;p=' . $reviewId;
					wp_update_post(array('ID' => $reviewId, 'guid' => $guId));
					// Review meta information
					$review_meta_data = array(
						'wpcr3_review_ip'       => $data_array['reviewer_ip'],
						'wpcr3_review_post'     => $data_array['page_id'],
						'wpcr3_review_name'     => $data_array['reviewer_name'],
						'wpcr3_review_email'    => $data_array['reviewer_email'],
						'wpcr3_review_rating'   => $data_array['review_rating'],
						'wpcr3_review_title'    => $data_array['review_title'],
						'wpcr3_review_website'  => $data_array['reviewer_url'],
						'wpcr3_review_admin_response' => $data_array['review_response'],
						'wpcr3_f1'  => $data_array['custom_field1'],
						'wpcr3_f2'  => $data_array['custom_field2'],
						'wpcr3_f3'  => $data_array['custom_field3'],
					);
					foreach($review_meta_data as $metaKey => $metaValue) {
						update_post_meta($reviewId, $metaKey, $metaValue);
					}
				} else {
					$wpdb->insert($wpdb->wpcreviews, array('date_time' => $data_array['date_time'], 'reviewer_name' => $data_array['reviewer_name'], 'reviewer_email' => $data_array['reviewer_email'], 'reviewer_ip' => $data_array['reviewer_ip'], 'review_title' => $data_array['review_title'], 'review_text' => $data_array['review_text'], 'review_response' => $data_array['review_response'], 'status' => $data_array['status'], 'review_rating' => $data_array['review_rating'], 'reviewer_url' => $data_array['reviewer_url'], 'page_id' => $data_array['page_id']));
					$reviewId = $wpdb->insert_id;
					if(is_wp_error($reviewId)) {
						$uci_admin->detailed_log[$uci_admin->processing_row_id]['Message'] = "Can't insert this Review. " . $reviewId->get_error_message();
						return $returnArray;
					}
				}
				$_SESSION[$eventKey]['summary']['inserted'][] = $reviewId;
				$uci_admin->setInsertedRowCount($uci_admin->getInsertedRowCount() + 1);
				$uci_admin->detailed_log[$uci_admin->processing_row_id]['Message'] = 'Inserted Review ID: ' . $reviewId;
				$mode_of_affect = 'Inserted';
			}
		} else {
			if($post_exists) {
				if( ($mode == 'Update' || $mode == 'Schedule') && $update_review_info == true ) {
					if($duplicate_action == 'Skip') {
						$uci_admin->setSkippedRowCount($uci_admin->getSkippedRowCount() + 1);
						$uci_admin->detailed_log[$uci_admin->processing_row_id]['Message'] = "Skipped, Due to duplicate Review found!.";
						$returnArr['MODE'] = $mode;
						return $returnArr;
					}
					update_post_meta($post_id, 'wpcr3_enable', 1);
					update_post_meta($post_id, 'wpcr3_format', $reviewFormat);
					if(in_array('wp-customer-reviews/wp-customer-reviews-3.php', $get_available_plugin_lists)) {
						$query = "select *from $wpdb->posts where ID = '{$data_array['review_id']}' and post_type = 'wpcr3_review'";
						$id_results = $wpdb->get_results($query);
						$reviewId = $id_results[0]->ID;
						$review_date = current_time('mysql', 0);
						if(isset($data_array['date_time'])) {
							$review_date = date( 'Y-m-d H:i:s', strtotime( $data_array['date_time'] ) );
						}
						$review_title = $data_array['reviewer_name'] . ' @ ' . $review_date;
						$review_slug = preg_replace('/[^a-zA-Z0-9._\-\s]/', '', $review_title);
						$review_slug = wp_unique_filename('', $review_slug);
						if(isset($data_array['status'])) {
							$data_array['status'] = strtolower( $data_array['status'] );
						}
						if ($data_array['status'] != 'publish' && $data_array['status'] != 'private' && $data_array['status'] != 'draft' && $data_array['status'] != 'pending' && $data_array['status'] != 'sticky') {
							$stripPSF = strpos($data_array['status'], '{');
							if ($stripPSF === 0) {
								$poststatus = substr($data_array['status'], 1);
								$stripPSL = substr($poststatus, -1);
								if ($stripPSL == '}') {
									$postpwd = substr($poststatus, 0, -1);
									$data_array['status'] = 'publish';
									$data_array ['post_password'] = $postpwd;
								} else {
									$data_array['status'] = 'publish';
									$data_array ['post_password'] = $poststatus;
								}
							} else {
								$data_array['status'] = 'publish';
							}
						}
						$review_array = array(
							'post_author' => '1',
							'post_date' => $review_date,
							'post_content' => $data_array['review_text'],
							'post_title' => $review_title,
							'post_status' => $data_array['status'],
							'comment_status' => 'closed',
							'ping_status' => 'closed',
							'post_password' => $data_array['post_password'],
							'post_name' => $review_slug,
							'post_parent' => 0,
							'post_type' => 'wpcr3_review',
						);
						if ( $reviewId == null ) {
							$reviewId = wp_insert_post($review_array);
							if(is_wp_error($reviewId)) {
								$uci_admin->detailed_log[$uci_admin->processing_row_id]['Message'] = "Can't insert this Review. " . $reviewId->get_error_message();
								return $returnArray;
							}
							$_SESSION[$eventKey]['summary']['inserted'][] = $reviewId;
							//$uci_admin->inserted = $uci_admin->inserted + 1;
							$uci_admin->setInsertedRowCount($uci_admin->getInsertedRowCount() + 1);
							$uci_admin->detailed_log[$uci_admin->processing_row_id]['Message'] = 'Inserted Review ID: ' . $reviewId;
							$mode_of_affect = 'Inserted';
						} else {
							$review_array['ID'] = $reviewId;
							wp_update_post($review_array);
							$_SESSION[$eventKey]['summary']['updated'][] = $reviewId;
							$uci_admin->setUpdatedRowCount($uci_admin->getUpdatedRowCount() + 1);
							$uci_admin->detailed_log[$uci_admin->processing_row_id]['Message'] = 'Updated Review ID: ' . $reviewId;
							$mode_of_affect = 'Updated';
						}
						$guId = site_url() . '/?post_type=wpcr3_review&#038;p=' . $reviewId;
						wp_update_post(array('ID' => $reviewId, 'guid' => $guId));
						// Review meta information
						$review_meta_data = array(
							'wpcr3_review_ip'       => $data_array['reviewer_ip'],
							'wpcr3_review_post'     => $data_array['page_id'],
							'wpcr3_review_name'     => $data_array['reviewer_name'],
							'wpcr3_review_email'    => $data_array['reviewer_email'],
							'wpcr3_review_rating'   => $data_array['review_rating'],
							'wpcr3_review_title'    => $data_array['review_title'],
							'wpcr3_review_website'  => $data_array['reviewer_url'],
							'wpcr3_review_admin_response' => $data_array['review_response'],
							'wpcr3_f1'  => $data_array['custom_field1'],
							'wpcr3_f2'  => $data_array['custom_field2'],
							'wpcr3_f3'  => $data_array['custom_field3'],
						);
						foreach($review_meta_data as $metaKey => $metaValue) {
							update_post_meta($reviewId, $metaKey, $metaValue);
						}
					} else {
						$query = "select id from $wpdb->wpcreviews where (review_title = '{$data_array['review_title']}') and (page_id = '{$data_array['page_id']}') ";
						$id_results = $wpdb->get_results( $query );
						$reviewId   = $id_results[0]->id;
						if ( $reviewId == null ) {
							$wpdb->insert( $wpdb->wpcreviews, array(
								'date_time'       => $data_array['date_time'],
								'reviewer_name'   => $data_array['reviewer_name'],
								'reviewer_email'  => $data_array['reviewer_email'],
								'reviewer_ip'     => $data_array['reviewer_ip'],
								'review_title'    => $data_array['review_title'],
								'review_text'     => $data_array['review_text'],
								'review_response' => $data_array['review_response'],
								'status'          => $data_array['status'],
								'review_rating'   => $data_array['review_rating'],
								'reviewer_url'    => $data_array['reviewer_url'],
								'page_id'         => $data_array['page_id']
							) );
							$mode_of_affect = 'Inserted';
							$_SESSION[$eventKey]['summary']['inserted'][] = $reviewId;
							//$uci_admin->inserted = $uci_admin->inserted + 1;
							$uci_admin->setInsertedRowCount($uci_admin->getInsertedRowCount() + 1);
							$uci_admin->detailed_log[$uci_admin->processing_row_id]['Message'] = 'Inserted Review ID: ' . $reviewId;
							$reviewId = $wpdb->insert_id;
						} else {
							$wpdb->update( $wpdb->wpcreviews, array(
								'date_time'       => $data_array['date_time'],
								'reviewer_name'   => $data_array['reviewer_name'],
								'id'              => $reviewId,
								'reviewer_email'  => $data_array['reviewer_email'],
								'reviewer_ip'     => $data_array['reviewer_ip'],
								'review_title'    => $data_array['review_title'],
								'review_text'     => $data_array['review_text'],
								'review_response' => $data_array['review_response'],
								'status'          => $data_array['status'],
								'review_rating'   => $data_array['review_rating'],
								'reviewer_url'    => $data_array['reviewer_url'],
								'page_id'         => $data_array['page_id']
							) );
							$mode_of_affect = 'Updated';
							$_SESSION[$eventKey]['summary']['updated'][] = $reviewId;
							$uci_admin->setUpdatedRowCount($uci_admin->getUpdatedRowCount() + 1);
							$uci_admin->detailed_log[$uci_admin->processing_row_id]['Message'] = 'Updated Review ID: ' . $reviewId;
						}
					}
				}
			}
		}
		#$retID = $reviewId;
		return array('ID' => $reviewId, 'MODE' => $mode_of_affect);
	}
}
