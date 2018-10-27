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
class SmackUCIAdminAjax {

	/* public static function init() {

		self::smuci_ajax_events();
	} */

	public static function smuci_ajax_events() {
		$ajax_actions = array(
				'upload_actions' => false,
				'ftp_actions' => false,
				'uci_picklist_handler' => false,
				'external_file_actions' => false,
				'file_treeupload' => false,
				'parseDataToImport' => false,
				'parseDataToExport' => false,
				'sendmail' => false,
				'send_subscribe_email' =>false,
				'retrieve_record' => false,
				'static_formula_method_handler' => false,
				'check_templatename' => false,
				'delete_template' => false,
				'update_template' => false,
				'delete_schedule' => false,
				'edit_schedule' => false,
				'filter_template' => false,
				'search_template' => false,
				'get_mediaimg_size' => false,
				'get_headerData' => false,
				'update_event' => false,
				'selectrevision' => false,
				'downloadFile' => false,
				'download_AllFiles' => false,
				'deleteFileEvent' => false,
				'deleteRecordEvent' => false,
				'deleteAllEvents' => false,
				'deleteScheduledFile' => false,
				'deleteAllScheduledEvent' => false,
				'trashRecords' => false,
				'downloadLog' => false,
				'register_acfpro_fields' => false,
				'register_acf_free_fields' => false,
				'delete_acf_pro_fields' => false,
				'delete_acf_free_fields' => false,
				'register_pods_fields' => false,
				'delete_pods_fields' => false,
				'add_bidirectional_fields' => false,
				'register_types_fields' => false,
				'delete_types_fields' => false,
				'check_CFRequiredFields' => false,
				'schedule_your_current_event' => false,
				'inlineimage_upload' => false,
				'set_post_types' => false,
				'FetchPieChartData' => false,
				'FetchBarStackedChartData' => false,
				'FetchLineChartData' => false,
				'options_savein_ajax' => false,
				'database_optimization_settings' => false,
				'database_optimization_process' => false,
				'upload_zipfile_handler' => false,
				'get_schedule_event_info' => false,
				'dismiss_notices' => false,
				'sendmail' => false,
				'send_subscribe_email' =>false,
				'retrieve_record' => false,
				'preview_record' => false,
				);
		foreach($ajax_actions as $action => $value ){
			add_action('wp_ajax_'.$action, array(__CLASS__, $action));
		}
	}

	public static function dismiss_notices() {
		update_option('smack_uci_' . $_POST['notice'], 'off');
	}

	public static function get_schedule_event_info() {
		global $wpdb;
		$scheduled_event_info = array();
		$get_scheduled_event_info = $wpdb->get_results($wpdb->prepare("select scheduledtimetorun, scheduleddate, frequency from wp_ultimate_csv_importer_scheduled_import where id = %d", $_POST['id']));
		$scheduled_event_info['scheduledTime'] = $get_scheduled_event_info[0]->scheduledtimetorun;
		$scheduled_event_info['scheduledDate'] = $get_scheduled_event_info[0]->scheduleddate;
		$scheduled_event_info['frequency'] = $get_scheduled_event_info[0]->frequency;
		$scheduled_event_info['event_id'] = $_POST['id'];
		$scheduled_event_info = json_encode($scheduled_event_info);
		print_r($scheduled_event_info);
		die();
	}

	public static function check_requiredfields() {
		global $wpdb;
		$req_arr = array();
		$wobj = new WPClassifyFields();
		$i = 0;
		$import_type = isset($_REQUEST['import_type']) ? sanitize_text_field($_REQUEST['import_type']) : '' ;
		//TYPES Fields
		$types_fields = $wobj->TypesCustomFields();
		if($import_type == 'users')
			$getOptions = get_option('wpcf-usermeta');
		else
			$getOptions = get_option('wpcf-fields');
		if(!empty($types_fields) && is_array($types_fields) && array_key_exists('TYPES',$types_fields)){
			foreach($types_fields['TYPES'] as $key => $value){
				$pt_title = $value['label'];
				if(is_array($getOptions)) {
					if(array_key_exists($pt_title,$getOptions)){
						foreach($getOptions[$pt_title] as $okey => $ovalue){
							if(is_array($ovalue)){
								if(array_key_exists('validate',$ovalue)){
									foreach($ovalue['validate'] as $typeskey => $typesval){
										if($typeskey == 'required'){
											if(is_array($typesval)){
												if(array_key_exists('active',$typesval)){
													if($typesval['active'] == 1){
														$req_arr[$i] = $value['name'];
														$i++;
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}

			}
		}

		//ACF Fields
		$acf_fields = $wobj->ACFCustomFields();
		if(!empty($acf_fields) && is_array($acf_fields) && array_key_exists('ACF',$acf_fields)){
			foreach($acf_fields['ACF'] as $key => $value){
				$pt_title = $value['label'];
				//$acf_postcont = $wpdb->get_col("SELECT post_content FROM $wpdb->posts where post_title = '$pt_title'");
				$acf_postcont = $wpdb->get_col($wpdb->prepare("SELECT post_content FROM $wpdb->posts where post_title = %s",$pt_title));
				if(!empty($acf_postcont)){
					$acf_postcont = unserialize($acf_postcont[0]);
					if($acf_postcont['required'] == 1){
						$req_arr[$i] = $value['name'];
						$i++;
					}
				}
			}
		}

		//ACF Fields (Free)
		$getacf_fields = $wpdb->get_col("SELECT meta_value FROM $wpdb->postmeta
                                                        GROUP BY meta_key
                                                        HAVING meta_key LIKE 'field_%'
                                                        ORDER BY meta_key");
		if(!empty($getacf_fields) && is_array($getacf_fields)){
			foreach($getacf_fields as $acfkey =>$acfval){
				$acf_arr = @unserialize($acfval);
				if($acf_arr !== false){
					if(array_key_exists('required',$acf_arr) && $acf_arr['required'] == 1){
						$req_arr[$i] = $acf_arr['label'];
						$i++;
					}
				}
			}
		}

		//PODS Fields
		$pods_fields = $wobj->PODSCustomFields();
		if(!empty($pods_fields) && is_array($pods_fields) && array_key_exists('PODS',$pods_fields)){
			foreach($pods_fields['PODS'] as $key => $value){
				$pt_title = $value['label'];
				//$pods_postid = $wpdb->get_col("SELECT id FROM $wpdb->posts where post_title = '$pt_title'");
				$pods_postid = $wpdb->get_col($wpdb->prepare("SELECT id FROM $wpdb->posts where post_title = %s",$pt_title));
				if(!empty($pods_postid)){
					$pods_postid = $pods_postid[0];
					//$pods_reqval = $wpdb->get_col("SELECT meta_value from $wpdb->postmeta where meta_key = 'required' and post_id = $pods_postid");
					$pods_reqval = $wpdb->get_col($wpdb->prepare("SELECT meta_value from $wpdb->postmeta where meta_key = %s and post_id = %d",'required',$pods_postid));
					if(!empty($pods_reqval)){
						if($pods_reqval[0] == 1){
							$req_arr[$i] = $value['name'];
							$i++;
						}
					}
				}
			}
		}
		$req_arr = json_encode($req_arr);
		print_r($req_arr);
		die();
	}

	public static function upload_actions() {
		include_once('class-uci-upload-handler.php');
		die();
	}

	public static function ftp_actions() {
		#NOTE: Removed FTP file handler.
		die;
	}

	public static function  external_file_actions() {
		#NOTE: Removed external file handler.
		die;
	}

	public static function file_treeupload() {
		#NOTE: Removed server file handler.
		die;
	}

	public static function parseDataToImport() {
		global $uci_admin;
		$event_information = $uci_admin->getEventInformation();
		if(empty($event_information)) {
			$uci_admin->setEventKey(sanitize_text_field($_POST['postData']['event_key']));
			$uci_admin->setImportType(sanitize_text_field($_POST['postData']['import_type']));
			$uci_admin->setImportMethod(sanitize_text_field($_POST['postData']['importMethod']));
			$uci_admin->setInsertedRowCount(intval( $_POST['postData']['inserted'] ));
			$uci_admin->setUpdatedRowCount(intval( $_POST['postData']['updated'] ));
			$uci_admin->setSkippedRowCount(intval( $_POST['postData']['skipped'] ));
			$additional_event_info = $uci_admin->GetPostValues($uci_admin->getEventKey());
			$uci_admin->setEventFileInformation($additional_event_info[$uci_admin->getEventKey()]['import_file']);
			$uci_admin->setMappingConfiguration($additional_event_info[$uci_admin->getEventKey()]['mapping_config']);
			$uci_admin->setMediaConfiguration($additional_event_info[$uci_admin->getEventKey()]['media_handling']);
			$uci_admin->setImportConfiguration($additional_event_info[$uci_admin->getEventKey()]['import_config']);
			$uci_admin->setFileType(pathinfo($additional_event_info[$uci_admin->getEventKey()]['import_file']['uploaded_name'], PATHINFO_EXTENSION));

			// Get mode of the current event
			$mode = $uci_admin->getEventFileInformation('import_mode');

			if($mode == 'new_items') {
				$uci_admin->setMode('Insert');
			} else {
				$uci_admin->setMode('Update');
			}
		}

		$startLimit = intval( $_POST['postData']['startLimit'] );
		$endLimit = intval( $_POST['postData']['endLimit'] );
		$limit = intval( $_POST['postData']['Limit'] );
		$totalCount = intval( $_POST['postData']['totalcount'] );
		$affectedRecords = array(
			'inserted'  => intval( $_POST['postData']['inserted'] ),
			'updated'   => intval( $_POST['postData']['updated'] ),
			'skipped'   => intval( $_POST['postData']['skipped'] )
		);
		$totalCount = intval( $_POST['postData']['totalcount'] );
		$data = $dataToBeImport = array();

		$eventMapping = $uci_admin->getMappingConfiguration();
		$mediaConfig = $uci_admin->getMediaConfiguration();
		$importConfig = $uci_admin->getImportConfiguration();
		$original_file_name = $uci_admin->getEventFileInformation('uploaded_name');
		$file_name = $uci_admin->getEventFileInformation('file_name');
		$version = $uci_admin->getEventFileInformation('file_version');
		$mode = $uci_admin->getMode();
		$fileType = $uci_admin->getFileType();
		$importType = $uci_admin->getImportType();
		$importMethod = $uci_admin->getImportMethod();
		$eventKey = $uci_admin->getEventKey();
		$eventDir = SM_UCI_IMPORT_DIR . '/' . $eventKey;
		$eventLog = '';
		// Mapped array for the event by group
		// Read file based on the $fileType, $offset & $limit
		switch($fileType) {
			case 'xml':
				$parserObj = new SmackXMLParser();
				$eventFile = $uci_admin->getUploadDirectory($parserObj) . '/' . $eventKey . '/' . $eventKey;
				$root_element = $parserObj->getNodeOccurrences($eventFile);
				$xml_arr = $parserObj->readData($eventFile, $startLimit, $limit);
				$data = $uci_admin->xml_file_data($xml_arr, $data);
				break;
			case 'csv':
			default:
				$parserObj = new SmackCSVParser();
				#$parserObj->eventkey = $eventKey;
				$eventFile = $eventDir . '/' . $eventKey;
				//$eventFile = $uci_admin->getUploadDirectory($parserObj) . '/' . $eventKey;
				$data = $parserObj->parseCSV($eventFile, $startLimit , $limit);
				//$total_row_count = $parserObj->total_row_cont - 1;
				#TODO $data[$limit] improvement
				break;
		}
		for ($i = $startLimit; $i < $endLimit; $i++) {
			try {
				$uci_admin->importData($eventKey, $importType, $importMethod, $mode, $data[$i], $i, $eventMapping, $affectedRecords, $mediaConfig, $importConfig);
				$manage_records[$mode][] = $uci_admin->getLastImportId();
				$detailed_log = $uci_admin->detailed_log;
				if (!empty($detailed_log)) {
					$uciEventLogger = new SmackUCIEventLogging();
					$eventLogFile = $eventDir . '/'.$eventKey.'.log';
					$eventInfoFile = $eventDir . '/'.$eventKey.'.txt';
					$recordId = array($uci_admin->getLastImportId());
					$contents = array();
					if(file_exists($eventInfoFile)) {
						$handle   = fopen( $eventInfoFile, 'r' );
						$contents = json_decode( fread( $handle, filesize( $eventInfoFile ) ) );
						fclose( $handle );
					}
					$fp = fopen($eventInfoFile, 'w+');
					if(!empty($contents) && $contents != null) {
						$contents = array_merge( $contents, $recordId );
						$contents = json_encode( $contents );
					} else {
						$contents = json_encode( $recordId );
					}
					fwrite($fp, $contents);
					fclose($fp);
					$uciEventLogger->lfile("$eventLogFile");
					if($startLimit == 1) {
						$uciEventLogger->lwrite("File has been used for this event: " . $original_file_name, false);
						$uciEventLogger->lwrite("Type of the imported file: " . $fileType, false);
						$uciEventLogger->lwrite("Revision of the which is used: " . $version, false);
						$uciEventLogger->lwrite("Mode of event: " . $mode, false);
						$uciEventLogger->lwrite("Total no of records: " . $totalCount, false);
						$uciEventLogger->lwrite("Rows handled on each iterations (Based on your server configuration): " . $limit, false);
						$uciEventLogger->lwrite("File used to import data into: " . $uci_admin->getImportAs() . ' (' . $importType . ')', false);
					}
					foreach ($uci_admin->detailed_log as $lkey => $lvalue) {
						$eventLog = '<div style="margin-left:10px; margin-right:10px;"><table>';
						$verify_link = '';
						foreach ($lvalue as $lindex => $lresult) {
							if($lindex != 'VERIFY')
								$eventLog .= '<tr><td><p><b>' . $lindex . ': </b>' . $lresult . ' </td><p></tr>';
							else
								$verify_link = '<tr><td><p>' . $lresult . ' </td><p></tr>';
						}
						$eventLog .= $verify_link;
					}
					$eventLog .= '</table></div>';
					$uciEventLogger->lwrite($eventLog);
					$uci_admin->setProcessedRowCount($i);
					echo json_encode(array(
							'total_no_of_rows' => $totalCount,
							'processed' => $uci_admin->getProcessedRowCount(),
							'inserted' => $uci_admin->getInsertedRowCount(),
							'updated'  => $uci_admin->getUpdatedRowCount(),
							'skipped'  => $uci_admin->getSkippedRowCount(),
							'eventLog' => $eventLog)
					);
				}
			} catch (Exception $e) {
				$parserObj->logE('ERROR:', $e);
			}
		}

		$fileInfo = array(
			'file_name' => $file_name,
			'original_file_name' => $original_file_name,
			'file_type' => $fileType,
			'revision'  => $version,
		);
		$eventInfo = array(
			'count' => $totalCount,
			'processed' => $uci_admin->getProcessedRowCount(),
			'inserted' => $uci_admin->getInsertedRowCount(),
			'updated'  => $uci_admin->getUpdatedRowCount(),
			'skipped'  => $uci_admin->getSkippedRowCount(),
			'eventLog' => $eventLog
		);
		$uci_admin->manage_records($manage_records, $fileInfo, $eventKey, $importType, $mode, $eventInfo);
		die();
	}

	public static function parseDataToExport() {
		global $expUci_admin;
		$expUci_admin->includeExp();
		die();
	}

	public static function static_formula_method_handler(){
		require_once(SM_UCI_PRO_DIR . "admin/views/form-static-formula-views.php");
		die();
	}

	public static function uci_picklist_handler() {
		require_once(SM_UCI_PRO_DIR . "admin/views/form-add-custom-field.php");
		die();
	}

	public static function check_templatename() {
		#NOTE: Removed check template feature based on the template name.
		die();
	}

	public static function delete_template() {
		#NOTE: Removed delete template feature.
		die;
	}

	public static function update_template() {
		#NOTE: Removed update template feature.
		die;
	}

	public static function delete_schedule() {
		#NOTE: Removed delete scheduled event feature.
		die;
	}

	public static function edit_schedule() {
		#NOTE: Removed edit template feature.
		die;
	}

	public static function get_mediaimg_size() {
		global $_wp_additional_image_sizes;
		$sizes = array();
		foreach ( get_intermediate_image_sizes() as $_size ) {
			if ( in_array( $_size, array('thumbnail', 'medium', 'large') ) ) {
				$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
				$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
			}
		}
		print_r(json_encode($sizes));
		die;
	}

	/**
	 * Filter the templates
	 */
	public static function filter_template() {
		#NOTE: Removed filter template feature.
		die;
	}

	public static function search_template() {
		#NOTE: Removed search template feature.
		die;
	}

	public static function get_headerData() {
		global $uci_admin;
		$eventkey = sanitize_text_field($_POST['eventkey']);
		$headers = $_POST['headers'];
		$post_values = $uci_admin->GetPostValues($eventkey);
		$core_group = array('Core Fields' => 'CORE');
		$import_data = $uci_admin->generateDataArrayBasedOnGroups($core_group,$post_values[$eventkey]['mapping_config']);
		$headers = array_intersect_key($import_data['CORE'],$headers);
		print_r(json_encode($headers));
		die;
	}

	public static function schedule_your_current_event() {
		#NOTE: Removed save scheduling current event feature.
		die;
	}

	public static function update_event() {
		#NOTE: Removed update event feature
		die;
	}

	public static function downloadFile() {
		#NOTE: Removed download file feature
		die;
	}

	public static function selectrevision() {
		#NOTE: Removed get file info based on the selected revision
		die;
	}


	public static function deleteFileEvent() {
		#NOTE: Removed delete file feature based on the selected revision
		die;
	}

	public static function deleteRecordEvent() {
		#NOTE: Removed delete records based on the selected revision
		die;
	}

	public static function deleteAllEvents() {
		#NOTE: Removed delete files & records based on the selected file
		die;
	}

	public static function deleteAllScheduledEvent() {
		#NOTE: Removed scheduled events
		die;
	}

	public static function trashRecords() {
		#NOTE: Removed trash records based on the selected revision
		die;
	}

	public static function deleteScheduledFile() {
		#NOTE: Removed the specific scheduled event
		die;
	}

	public static function downloadLog() {
		require_once(SM_UCI_PRO_DIR . "managers/class-uci-logmanager.php");
		global $log_managerObj;
		$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		$log_managerObj->logDownload($_POST);
		die;
	}

	public static function download_AllFiles() {
		#NOTE: Removed download all logs feature
		die;
	}

	/** Field Registration */
	public static function register_acfpro_fields() {
		#NOTE: Removed ACF Pro field registration
		die;
	}

	public static function register_acf_free_fields() {
		#NOTE: Removed ACF Free field registration
		die;
	}

	public static function register_pods_fields() {
		#NOTE: Removed PODS field registration
		die;
	}

	public static function register_types_fields() {
		#NOTE: Removed Toolset Types field registration
		die;
	}

	public static function delete_acf_pro_fields() {
		#NOTE: Removed ACF Pro field de-registration
		die;
	}

	public static function delete_acf_free_fields() {
		#NOTE: Removed ACF Free field de-registration
		die;
	}

	public static function delete_pods_fields() {
		#NOTE: Removed PODS field de-registration
		die;
	}

	public static function delete_types_fields() {
		#NOTE: Removed Toolset Types field de-registration
		die;
	}

	public  static function add_bidirectional_fields() {
		#NOTE: Removed PODS bi-directional field registration
		die;
	}
	/** End Field Registration */

	public static function check_CFRequiredFields() {
		#NOTE: Removed check required fields for ACF, PODS & Toolset Types fields
		die;
	}

	public static function inlineimage_upload(){
		require_once(SM_UCI_PRO_DIR . "includes/class-uci-inlinezipupload.php");
	}

	public static function set_post_types() {
		global $uci_admin;
		$parserObj = new SmackCSVParser();
		$eventKey = isset($_POST['filekey']) ? sanitize_key($_POST['filekey']) : '';
		$uploadedname = isset($_POST['uploadedname']) ? sanitize_text_field($_POST['uploadedname']) : '';
		$file = SM_UCI_IMPORT_DIR . '/' . $eventKey . '/' . $eventKey;
		$parserObj->parseCSV($file, 0, -1);
		$Headers = $parserObj->get_CSVheaders();
		$Headers = $Headers[0];
		$type = 'Posts';
		if(in_array('wp_page_template', $Headers) && in_array('menu_order', $Headers)){
			$type = 'Pages';
		} elseif(in_array('user_login', $Headers) || in_array('role', $Headers) || in_array('user_email', $Headers) ){
			$type = 'Users';
		} elseif( in_array('reviewer_name', $Headers) || in_array('reviewer_email', $Headers)){
			$type = 'CustomerReviews';
		} elseif(in_array('comment_author', $Headers) || in_array('comment_content', $Headers) ||  in_array('comment_approved', $Headers) ){
			$type = 'Comments';
		}
		elseif (in_array('sku', $Headers)) {
			$type = 'WooCommerce';
		}

		/*
		 * Note: Removed auto detect type of module for the below lists
		 *
		 * -- WooCommerce Products, -- WooCommerce Variations, -- WooCommerce Orders,
		 * -- WooCommerce Refunds, -- WooCommerce Coupons, -- MarketPress Products,
		 * -- MarketPress Variations, -- WPeCommerce Products, -- WPeCommerce Coupons,
		 * -- Customer Reviews, Bulk Terms & Taxonomies,
		 * -- Events, -- Recurring Events, -- Event Locations, -- Event Tickets,
		*/

		$result = $template_order = array();
		$result['is_template'] = 'no';

		# Note: Removed the priority check of the suggested templates

		$result['type'] = $type;
		print_r(json_encode($result));
		die();
	}

	public static function FetchBarStackedChartData() {
		global $wpdb, $uci_admin;
		$available_types = array();
		foreach($uci_admin->get_import_post_types() as $name => $type) {
			$available_types[$name] = $type;
		}
		foreach (get_taxonomies() as $item => $taxonomy_name) {
			$available_types[$item] = $taxonomy_name;
		}
		$available_types = array_flip($available_types);
		$returnArray = array();
		$today = date("Y-m-d H:i:s");
		$j = 0;
		for($i = 11; $i >= 0; $i--) {
			$month[$j] = date("M", strtotime( $today." -$i months"));
			$year[$j]  = date("Y", strtotime( $today." -$i months"));
			$j++;
		}
		$get_list_of_imported_types = $wpdb->get_col($wpdb->prepare("select distinct( import_type ) from smackuci_events", array()));
		$count = 1;
		foreach($get_list_of_imported_types as $import_type) {
			$get_chart_data = $wpdb->get_results( $wpdb->prepare( "select sum(created) as created, sum(updated) as updated, sum(skipped) as skipped from smackuci_events where import_type = %s", $import_type, $import_type ) );
			if(array_key_exists($import_type,$available_types)){
                                $import_type_data = $available_types[$import_type];
                        } else {
                                $import_type_data = $import_type;
                        }

			if($get_chart_data[0]->created) {
				$returnArray[ $import_type_data ]['created'] = $get_chart_data[0]->created;
			} else {
				$returnArray[ $import_type_data ]['created'] = 0;
			}
			if($get_chart_data[0]->updated) {
				$returnArray[ $import_type_data ]['updated'] = $get_chart_data[0]->updated;
			} else {
				$returnArray[ $import_type_data ]['updated'] = 0;
			}
			if($get_chart_data[0]->skipped) {
				$returnArray[ $import_type_data ]['skipped'] = $get_chart_data[0]->skipped;
			} else {
				$returnArray[ $import_type_data ]['skipped'] = 0;
			}
			$count++;
		}
		echo json_encode($returnArray);
		die();
	}

	public static function FetchPieChartData() {
		global $wpdb, $uci_admin;
		$available_types = array();
		foreach($uci_admin->get_import_post_types() as $name => $type) {
			$available_types[$name] = $type;
		}
		foreach (get_taxonomies() as $item => $taxonomy_name) {
			$available_types[$item] = $taxonomy_name;
		}
		$available_types = array_flip($available_types);
		$returnArray = array();
		$today = date("Y-m-d H:i:s");
		$j = 0;
		for($i = 11; $i >= 0; $i--) {
			$month[$j] = date("M", strtotime( $today." -$i months"));
			$year[$j]  = date("Y", strtotime( $today." -$i months"));
			$j++;
		}
		$get_list_of_imported_types = $wpdb->get_col($wpdb->prepare("select distinct( import_type ) from smackuci_events", array()));
		$count = 1;
		foreach($get_list_of_imported_types as $import_type) {
			$get_chart_data = $wpdb->get_results( $wpdb->prepare( "select sum(created) as %s from smackuci_events where import_type = %s", $import_type, $import_type ) );
			if(array_key_exists($import_type,$available_types)){
				$import_type_data = $available_types[$import_type];
			} else {
				$import_type_data = $import_type;
			}
			if($get_chart_data[0]->$import_type) {
				$data = $get_chart_data[0]->$import_type;
				$returnArray[ $count ][ $import_type_data ] = $data;
			} else {
				$returnArray[ $count ][ $import_type_data ] = 0;
			}
			$count++;
		}
		echo json_encode($returnArray);
		die();
	}

	public static function FetchLineChartData() {
		global $wpdb, $uci_admin;
		$available_types = array();
		foreach($uci_admin->get_import_post_types() as $name => $type) {
			$available_types[$name] = $type;
		}
		foreach (get_taxonomies() as $item => $taxonomy_name) {
			$available_types[$item] = $taxonomy_name;
		}
		$available_types = array_flip($available_types);
		$returnArray = array();
		$today = date("Y-m-d H:i:s");
		$j = 0;
		for($i = 11; $i >= 0; $i--) {
			$month[$j] = date("M", strtotime( $today." -$i months"));
			$year[$j]  = date("Y", strtotime( $today." -$i months"));
			$j++;
		}
		$get_list_of_imported_types = $wpdb->get_col($wpdb->prepare("select distinct( import_type ) from smackuci_events", array()));
		foreach($get_list_of_imported_types as $import_type) {
			$data = '';
			for($i = 0; $i <= 11; $i++) {
				$count = 0;
				$get_chart_data = $wpdb->get_results( $wpdb->prepare( "select sum(created) as %s from smackuci_events where import_type = %s and month = %s and year = %d", $import_type, $import_type, $month[$i], $year[$i] ) );
				if($get_chart_data[0]->$import_type) {
					$data .= $get_chart_data[0]->$import_type . ',';
				} else {
					$data .= $count . ',';
				}
			}
			if(array_key_exists($import_type,$available_types)){
                                $import_type_data = $available_types[$import_type];
                        } else {
                                $import_type_data = $import_type;
                        }

			$returnArray[ $import_type_data ] = substr($data, 0, -1);
		}
		echo json_encode($returnArray);
		die();
	}

	public static function options_savein_ajax(){
		$ucisettings = get_option('sm_uci_pro_settings');
		$option = sanitize_text_field($_REQUEST['option']);
		$value = sanitize_text_field($_REQUEST['value']);
		foreach ($ucisettings as $key => $val) {
			$settings[$key] = $val;
		}
		$settings[$option] = $value;
		update_option('sm_uci_pro_settings', $settings);
	}

	public static function database_optimization_settings(){
		$get_optimize = get_option('sm_uci_pro_optimization');
		if (is_array($get_optimize)) {
			foreach($get_optimize as $key => $value) {
				if(isset($key))
					$optimize_settings[$key] = $value;
			}
		}
		$optimize_settings[sanitize_text_field($_POST['option'])] = sanitize_text_field($_POST['value']);
		update_option('sm_uci_pro_optimization', $optimize_settings);
	}

	public static function database_optimization_process(){
		#NOTE: Removed database optimisation feature
	}

	public static function upload_zipfile_handler(){
		require_once(SM_UCI_PRO_DIR . "includes/class-uci-zipfilehandler.php");
	}
		
	public static function sendmail(){
		if($_POST){
			$email = $_POST['email'];
			$url = get_option('siteurl');
			$site_name = get_option('blogname');
			$headers = "From: " . $site_name . "<$email>" . "\r\n";
			$headers.= 'MIME-Version: 1.0' . "\r\n";
			$headers.= "Content-type: text/html; charset=iso-8859-1 \r\n";
			$to = 'support@smackcoders.com';
			$subject = $_POST['query'];
			$message = "Site URL: " . $url . "\r\n";
			$message .= "Plugin Name: " . SM_UCI_SETTINGS . "\r\n";
			$message .= "Message: " . $_POST['message'] . "\r\n";
			//send email
			if(wp_mail($to, $subject, $message, $headers)) {
				echo 'Mail Sent!';
			} else {
				echo "Please draft a mail to support@smackcoders.com. If you doesn't get any acknowledgement within an hour!";
			} //This method sends the mail.
			die;
		}
	}

	public static function send_subscribe_email(){
		if($_POST){
			$email = $_POST['subscribe_email'];
			$url = get_option('siteurl');
			$site_name = get_option('blogname');
			$headers = "From: " . $site_name . "<$email>" . "\r\n";
			$headers.= 'MIME-Version: 1.0' . "\r\n";
			$headers.= "Content-type: text/html; charset=iso-8859-1 \r\n";
			$to = 'support@smackcoders.com';
			$subject = 'Newsletter Subscription';
			$message = "Site URL: " . $url . "\r\n";
			$message .= "Plugin Name: " . SM_UCI_SETTINGS . "\r\n";
			$message .= "Message: Hi Team, I want to subscribe to your newsletter." . "\r\n";
			//send email
			if(wp_mail($to, $subject, $message, $headers)) {
				echo 'Mail Sent!';
			} else {
				echo "Please draft a mail to support@smackcoders.com. If you doesn't get any acknowledgement within an hour!";
			} //This method sends the mail.
			die;
		}
	}

	public function retrieve_record() {
		$parserObj = new SmackCSVParser();
		if($_POST) {
			$file = SM_UCI_IMPORT_DIR . '/' . $_POST['event_key'] . '/' . $_POST['event_key'];
			$csv_row = $parserObj->parseCSV($file, $_POST['row_no']);
			print_r(json_encode($csv_row[$_POST['row_no']]));
		}
		die;
	}

	public function preview_record() {
		$parserObj = new SmackCSVParser();
		$modified_result = array();
		$result = '';
		if($_POST) {
			$file = SM_UCI_IMPORT_DIR . '/' . $_POST['event_key'] . '/' . $_POST['event_key'];
			$csv_row = $parserObj->parseCSV($file, $_POST['row_no']);
			$data = $csv_row[$_POST['row_no']];
			$mapping = array('title' => $_POST['title'], 'content' => $_POST['content'], 'excerpt' => $_POST['excerpt'], 'image' => $_POST['image']);
			foreach($mapping as $key => $val) {
				$pattern = "/({([a-z A-Z 0-9 | , _ -]+)(.*?)(}))/";
				preg_match_all($pattern, $val, $results, PREG_PATTERN_ORDER);
				for($i=0; $i<count($results[2]); $i++) {
					$oldWord = $results[0][$i];
					$get_val = $results[2][$i];
					//TODO xml
					if(isset($data[$get_val])) {
						$newWord = $data[$get_val];
					} else {
						$newWord = $get_val;
					}
					$val = str_replace($oldWord, ' ' . $newWord, $val);
				}
				$modified_result[$key] = $val;
			}
			$result .= '<table class="table table-striped">';
			$result .= '<tr>';
			$result .= '<td><p><b>' . $modified_result['title'] . '</b></p></td>';
			$result .= '</tr>';
			$result .= '<tr>';
			$result .= '<td><p>' . $modified_result['content'] . '</p></td>';
			$result .= '</tr>';
			$result .= '<tr>';
			$result .= '<td><p><img src="' . $modified_result['image'] . '" width="50" height="50" /></p></td>';
			$result .= '</tr>';
			$result .= '<tr>';
			$result .= '<td><p>' . $modified_result['excerpt'] . '</p></td>';
			$result .= '</tr>';

			$result .= '</table>';
			print $result;
		}
		die;
	}
}
