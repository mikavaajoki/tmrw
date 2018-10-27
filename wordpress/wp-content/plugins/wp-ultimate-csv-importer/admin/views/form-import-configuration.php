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

global $uci_admin;
if($_POST) {
	$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
	$records['media_handling'] =$_POST;
	$get_records = $uci_admin->GetPostValues(sanitize_key($_REQUEST['eventkey']));
	$result = array_merge($get_records[$_REQUEST['eventkey']], $records);
	$uci_admin->SetPostValues(sanitize_key($_REQUEST['eventkey']), $result);
}
$post_values = $uci_admin->GetPostValues(sanitize_key($_REQUEST['eventkey']));
$eventkey = sanitize_title($_REQUEST['eventkey']);
$import_mode = '';
if($post_values[$eventkey]['import_file']['import_mode'] == 'existing_items') {
	$import_mode = "checked = 'checked'";
}
$import_type = $post_values[$eventkey]['import_file']['posttype'];
$importAs = '';
$server_request = $uci_admin->serverReq_data();
$file = SM_UCI_IMPORT_DIR . '/' . $eventkey . '/' . $eventkey;
$parserObj->parseCSV($file, 0, -1);
$total_row_count = $parserObj->total_row_cont - 1;
$actionURL = esc_url(admin_url() . 'admin.php?page=sm-uci-import&step=confirm&eventkey='.$_REQUEST['eventkey']);
$backlink = esc_url(admin_url() . 'admin.php?page=sm-uci-import&step=media_config&eventkey='.$_REQUEST['eventkey']);
if(isset($_REQUEST['templateid'])) {
	$actionURL .= '&templateid=' . intval($_REQUEST['templateid']);
	$backlink .= '&templateid=' . intval($_REQUEST['templateid']);
} 
$ucisettings = get_option('sm_uci_pro_settings');
$main_mode = isset($ucisettings['enable_main_mode']) ? $ucisettings['enable_main_mode'] : '';
if($main_mode == 'on'){
	$config_checkbox = "checked = 'checked'";
}
else{
 $config_checkbox = "";
}
?>
	<div class="list-inline pull-right mb10 wp_ultimate_csv_importer_pro">
            <div class="col-md-6 mt10"><a href="https://goo.gl/jdPMW8" target="_blank"><?php echo esc_html__('Documentation','wp-ultimate-csv-importer');?></a></div>
            <div class="col-md-6 mt10"><a href="https://goo.gl/fKvDxH" target="_blank"><?php echo esc_html__('Sample CSV','wp-ultimate-csv-importer');?></a></div>
         </div>

	<div class="template_body whole_body wp_ultimate_csv_importer_pro" style="font-size: 14px; margin-top: 40px;">
		<h3 style="margin-left:2%;" class="csv-importer-heading"><?php echo esc_html__('Import configuration Section','wp-ultimate-csv-importer');?></h3>
		<form class="form-inline" method="post" action="<?php echo $actionURL;?>">
			<div id='wp_warning' style = 'display:none;' class = 'error'></div>
			<div class="config_table">
			<div class="col-md-12 mt20" id="main_ch">
				<div class="col-md-12 mb15">
					<label><input type = "checkbox"  class="import_config_checkbox" name = "main_mode_config" id = "main_mode_config" <?php echo $config_checkbox; ?> ><?php echo esc_html__('Do you want to SWITCH ON Maintenance mode while import ?');?></label></div>
			</div>
				<div class="col-md-12 mt20">
					<div class="col-md-12 mb15">
						<label style="display:inline;">
							<input type="checkbox" name="duplicate" id="duplicate" class="import_config_checkbox" onclick = "toggle_configdetails(this.id);" /><?php echo esc_html__('Do you want to handle the duplicate on existing records ?','wp-ultimate-csv-importer');?></label></div>
				</div>
				<div id="duplicate_headers" class="mb40" style="display:none;">
					<div class="col-md-12 mb15">
						<div class = "col-md-6 col-md-offset-1 col-sm-7 col-sm-offset-1">
							<label>
								<?php echo esc_html__('Mention the fields which you want to handle duplicates','wp-ultimate-csv-importer');?>
							</label></div>
						<div class="col-xs-offset-3 col-xs-0">
							<select class="dropdown-search-multiple selectpicker" name="duplicate_conditions[]" id="duplicate_conditions" disabled>
								<?php
								$fields = $uci_admin->get_widget_fields('Core Fields', $post_values[$eventkey]['import_file']['posttype'],$importAs);
								foreach( $fields['CORE'] as $wp_fieldLabel => $wp_fieldarray){ ?>
									<option value="<?php echo esc_html($wp_fieldarray['name']);?>">
										<?php echo esc_html($wp_fieldarray['name']);?>
									</option>
								<?php } ?>
							</select>
						</div>
					</div>
				</div>
				<div class="col-md-12 mt20">
					<div class="col-md-12 mb15">
						<label><input class="upgrade_pro_checkbox" style="background-color: blue" type = "checkbox" class="import_config_checkbox" name = "schedule" id = "schedule" onclick = "toggle_configdetails(this.id);"><?php echo esc_html__('Do you want to Schedule this Import');?></label></div>
				</div>
				
			</div>
			<input type="hidden" id="eventkey" value="<?php echo sanitize_key($_REQUEST['eventkey']);?>">
			<input type="hidden" id="import_type" value="<?php echo $import_type;?>">
			<div class="clearfix"></div>
			<div class="col-md-12 mt40">
				<div class="pull-left">
					<a class="smack-btn btn-default btn-radius" href="<?php echo $backlink;?>"><?php echo esc_html__('Back','wp-ultimate-csv-importer');?></a>
				</div>
				<div class="pull-right mb20" style="margin-top: -10px;">
					<input type="submit" class="smack-btn smack-btn-primary btn-radius" id="ignite_import" name="ignite_import" value="<?php echo esc_attr__('Import','wp-ultimate-csv-importer');?>" onsubmit="schedule_rightnow();">
					<input style="display:none;" disabled="disabled" type="button" class="smack-btn smack-btn-primary btn-radius" id="schedule_import_btn" name="schedule_import" value="<?php echo esc_attr__('Schedule','wp-ultimate-csv-importer');?>" onclick="igniteSchedule();"></div>
			</div>
			<div class="clearfix"></div>
		</form>
	</div>

<?php if($import_mode != '') { ?>
	<script type="application/javascript">
	//	swal('Warning!', 'Please upgrade to PRO for duplicate handling.', 'warning')

	</script>
<?php } ?>

<div style="font-size: 15px;text-align: center;padding-top: 20px">Powered by <a href="https://www.smackcoders.com?utm_source=wordpress&utm_medium=plugin&utm_campaign=free_csv_importer" target="blank">Smackcoders</a>.</div>
