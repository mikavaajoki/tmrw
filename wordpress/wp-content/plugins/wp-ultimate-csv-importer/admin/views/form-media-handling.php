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
	$records['mapping_config'] = $_POST;
	$auto_save_template = isset($_POST['template']) ? sanitize_text_field($_POST['template']) : '';
	$post_values = $uci_admin->GetPostValues(sanitize_key($_REQUEST['eventkey']));
	$result = array_merge($post_values[$_REQUEST['eventkey']], $records);
	$uci_admin->SetPostValues(sanitize_key($_REQUEST['eventkey']), $result);
	#NOTE: Removed save mapping template feature
}
if(isset($_REQUEST['istemplate']) && sanitize_title($_REQUEST['istemplate']) == 'no') {
	$actionURL = esc_url(admin_url() . 'admin.php?page=sm-uci-import&step=import_config&istemplate=no&eventkey='.$_REQUEST['eventkey']);
	$backlink = esc_url(admin_url() . 'admin.php?page=sm-uci-import&step=mapping_config&istemplate=no&eventkey='.$_REQUEST['eventkey']);
} else {
	$actionURL = esc_url(admin_url() . 'admin.php?page=sm-uci-import&step=import_config&eventkey='.$_REQUEST['eventkey']);
	$backlink = esc_url(admin_url() . 'admin.php?page=sm-uci-import&step=mapping_config&eventkey='.$_REQUEST['eventkey']);
}
if(isset($_REQUEST['templateid'])) {
	$actionURL .= '&templateid=' . intval($_REQUEST['templateid']);
	$backlink .= '&templateid=' . intval($_REQUEST['templateid']);
}
$records =  $uci_admin->GetPostValues(sanitize_key($_REQUEST['eventkey']));
?>
<div class="list-inline pull-right mb10 wp_ultimate_csv_importer_pro">
            <div class="col-md-6 mt10"><a href="https://goo.gl/jdPMW8" target="_blank"><?php echo esc_html__('Documentation','wp-ultimate-csv-importer');?></a></div>
            <div class="col-md-6 mt10"><a href="https://goo.gl/fKvDxH" target="_blank"><?php echo esc_html__('Sample CSV','wp-ultimate-csv-importer');?></a></div>
         </div>

<div class="template_body whole_body wp_ultimate_csv_importer_pro" style="margin-top: 40px;">
	<form class="form-inline" method="post" action="<?php echo $actionURL;?>">
		<div id='wp_warning' style = 'display:none;' class = 'error'></div>
		<h3 class="media_head csv-importer-heading"><?php echo esc_html__('Media Handling','wp-ultimate-csv-importer');?></h3>
		<input type="hidden" id="eventkey" name="eventkey" value="<?php echo sanitize_text_field($_REQUEST['eventkey']); ?>" />
		<!-- table div start -->	<div class="media_data">
			<!-- tr div start <div>-->

			<div class="col-md-12">
				<fieldset class="scheduler-border"> <legend class="scheduler-border" style="margin-top:20px;"><?php echo esc_html__('Image Handling','wp-ultimate-csv-importer');?></legend>
					<!--	</div> tr div end -->
					<!-- tr div start  <div>-->
					<div class="col-md-12">
						<div  class="col-xs-0 col-xs-offset-0 col-sm-7 col-md-6 col-md-offset-1"><label class="external_img_label"><?php echo esc_html__('Download external images to your media','wp-ultimate-csv-importer');?></label></div>
						<div id='divtd' class="col-xs-6  col-sm-3 col-md-3 col-md-offset-0 col-xs-offset-1">

							<!-- first button -->
							<input id="image-handling-btn" type='checkbox' class="tgl tgl-skewed noicheck" name='download_img_tag_src' id='download_imgon' value='on' checked="checked"  style="display:none" onclick="saveoptions(this.id, this.name);" />
							<label data-tg-off="OFF" data-tg-on="ON" for="image-handling-btn"  id="download_on" class="tgl-btn" >
							</label>
							<!-- first btn -->
						</div></div>
					<div  class="col-md-12 mb20" id="image-handling-btn-opt">

						<div class="col-md-offset-2 col-sm-offset-1"><label class="external_img_label"><input type="radio"  class="upgrade_pro_checkbox" name="imageprocess" id="use_existing_images" value="use_existing_images" onclick="displayselect(this.id);"><?php echo esc_html__('Use media images if already available','wp-ultimate-csv-importer');?></label></div>
						<div class="col-md-offset-2 col-sm-offset-1"><label class="external_img_label"><input type="radio" class="upgrade_pro_checkbox" name="imageprocess" id="overwrite_existing_images" value="overwrite_existing_images" onclick="displayselect(this.id);"><?php echo esc_html__('Do you want to overwrite the existing images','wp-ultimate-csv-importer');?></label></div>
					</div>
					<?php if(in_array('nextgen-gallery/nggallery.php', $uci_admin->getActivePlugins())) { ?>
					<div class="col-md-12 mt20">
						<div  class="col-xs-0 col-xs-offset-0 col-sm-7 col-md-6 col-md-offset-1">
							<label class="external_img_label"><?php echo esc_html__('NextGEN Gallery support on featured image', 'wp-ultimate-csv-importer')?></label>
						</div>
						<div id='divtd' class="col-xs-6 col-sm-3 col-xs-offset-1 col-md-3 col-md-offset-0">
							<!-- second  button code -->
							<input id="gallery-support-btn" type='checkbox' class="tgl tgl-skewed noicheck" name='nextgen_featured_image' id='download_imgon' style="display:none" onclick="saveoptions(this.id, this.name);" />
							<label data-tg-off="OFF" data-tg-on="ON" for="gallery-support-btn"  id="download_on" class="tgl-btn" >
							</label>
						</div>
					</div>  <!-- container div close </div>-->
					<?php } ?>
				</fieldset></div>
			<div class="col-md-12">
				<fieldset class="scheduler-border"> <legend class="scheduler-border" style="margin-top:20px;">Image Sizes</legend>
					<div class="row">
						<div class="col-xs-6 col-xs-offset-0 col-sm-3 col-sm-offset-1 col-md-2 col-md-offset-1"><label class="wp_img_size"><input  class="upgrade_pro_checkbox" type="checkbox" name="media_thumbnail_size" id="thumbnail_size" value="on" checked="checked"><?php echo esc_html__('Thumbnail','wp-ultimate-csv-importer');?></label></div>
						<div class="col-xs-6 col-xs-offset-0 col-sm-3 col-sm-offset-0 col-md-2 col-md-offset-0"><label class="wp_img_size"><input class="upgrade_pro_checkbox" type="checkbox" name="media_medium_size" id="medium_size" ><?php echo esc_html__('Medium','wp-ultimate-csv-importer');?></label></div>
						<div class="col-xs-6 col-sm-3 col-sm-offset-0 col-md-3 col-md-offset-0"><label class="wp_img_size"><input  class="upgrade_pro_checkbox" type="checkbox" name="media_medium_large_size" id="medium_large_size"><?php echo esc_html__('Medium Large','wp-ultimate-csv-importer');?></label></div>
						<div class="col-xs-6 col-sm-3 col-sm-offset-0 col-md-2"><label class="wp_img_size"><input class="upgrade_pro_checkbox" type="checkbox" name="media_large_size" id="large_size" ><?php echo esc_html__('Large','wp-ultimate-csv-importer');?></label></div>
					</div>
					<!--<div> tr div end -->
					<!-- add custom  -->
					<div class="col-md-12">
						<div class="col-md-offset-1 mt20">
							<table class="table">
								<thead>
								</thead>
								<tbody id="TextBoxContainer">
								</tbody>
								<tfoot>
								<tr>
									<th colspan="2">
										<p id="custom-size-add"  class="smack-btn smack-btn-info" style="cursor: pointer;color:#008080;font-size:15px;"><i class="icon-circle-plus"></i>&nbsp;<?php echo esc_html__('Add custom sizes','wp-ultimate-csv-importer');?></p></th>
								</tr>
								</tfoot>
							</table>
						</div></div>
				</fieldset></div>
			<!--	</div> tr div end -->
			<!-- image hole div end -->
			<!-- advanced media option start -->
			<!-- tr div start <div>-->
			<div class="col-md-12"><fieldset class="scheduler-border"> <legend class="scheduler-border" style="margin-top:20px;"><?php echo esc_html__('Media SEO & Advanced Options','wp-ultimate-csv-importer');?></legend>
					<!--	</div> tr div end -->
					<!-- tr div start <div>-->
					<div class="col-md-12">
						<div class="col-xs-12 col-sm-6 col-md-4 col-md-offset-1"><label class="external_img_label"><input  class="upgrade_pro_checkbox" type="checkbox" name="media_seo_title" id="media_seo_title" data-key="title" class="media_seo" onclick="enable_media_seo_headers('title');"/><?php echo esc_html__('Set image Title:','wp-ultimate-csv-importer');?></label></div>
						<div class="col-xs-12 col-sm-6 col-md-3 mb10"><?php print( $uci_admin->getCSVHeader('title') ); ?></div>
					</div>
					<!--	</div> tr div end -->
					<!-- tr div start  <div>-->
					<div class="col-md-12">
						<div class="col-xs-12 col-sm-6 col-md-4 col-md-offset-1">
							<label class="external_img_label"><input class="upgrade_pro_checkbox" type="checkbox" name="media_seo_caption" id="media_seo_caption" data-key="caption" class="media_seo" onclick="enable_media_seo_headers('caption');"/><?php echo esc_html__('Set image Caption:','wp-ultimate-csv-importer');?></label></div>
						<div class="col-xs-12 col-sm-6 col-md-3 mb10 "><?php print( $uci_admin->getCSVHeader('caption') ); ?></div>
					</div>
					<!--	</div> tr div end -->
					<!--	<div> tr div start -->
					<div class="col-md-12">
						<div class="col-xs-12 col-sm-6 col-md-4 col-md-offset-1">
							<label class="external_img_label"><input class="upgrade_pro_checkbox" type="checkbox" name="media_seo_alttext" id="media_seo_alttext" data-key="alttext" class="media_seo" onclick="enable_media_seo_headers('alttext');"/><?php echo esc_html__('Set image Alt Text:','wp-ultimate-csv-importer');?></label></div>
						<div class="col-xs-12 col-sm-6 col-md-3 mb10"><?php print( $uci_admin->getCSVHeader('alttext') ); ?></div>
					</div>
					<!--	</div> tr div end -->
					<!-- tr div start <div>-->
					<div class="col-md-12">
						<div class="col-xs-12 col-sm-6 col-md-4 col-md-offset-1"><label class="external_img_label"><input class="upgrade_pro_checkbox" type="checkbox" name="media_seo_description" data-key="description" class="media_seo" id="media_seo_description" onclick="enable_media_seo_headers('description');"/><?php echo esc_html__('Set image Description:','wp-ultimate-csv-importer');?></label></div>
						<div class="col-xs-12 col-sm-6 col-md-3 mb10">	<?php print( $uci_admin->getCSVHeader('description') ); ?></div>
					</div>
					<!--	</div> tr div end -->
					<!--	<div> tr div start -->
					<div  class="col-md-12">
						<div class="col-xs-12 col-sm-6 col-md-4 col-md-offset-1"><label class="external_img_label"><input class="upgrade_pro_checkbox" type="checkbox" name="change_media_file_name" class="media_seo" data-key="imageName" id="change_media_file_name" onclick="enable_media_seo_headers('imageName');"><?php echo esc_html__('Change image file name to:','wp-ultimate-csv-importer');?></label></div>
						<div class="col-xs-12 col-sm-6 col-md-3 mb10"><?php print( $uci_admin->getCSVHeader('imageName') ); ?></div>
					</div>
					<!--	</div> tr div end -->
				</fieldset></div>
			<!-- advanced media option end -->
			<!-- table div end -->	</div>
		<div class="clearfix"></div>
		<div class="col-md-12 mt15 mb15">
			<div class="pull-left">
				<a class="smack-btn btn-default btn-radius" href="<?php echo $backlink;?>"><?php echo esc_html__('Back','wp-ultimate-csv-importer');?></a></div>
			<div class="pull-right">
				<input type="submit" class="smack-btn smack-btn-primary btn-radius" value="<?php echo esc_attr__('Continue','wp-ultimate-csv-importer');?>">
			</div>
		</div>
		<div class="clearfix"></div>
		<div class="mb20"></div>
	</form>
</div>

<div style="font-size: 15px;text-align: center;padding-top: 20px">Powered by <a href="https://www.smackcoders.com?utm_source=wordpress&utm_medium=plugin&utm_campaign=free_csv_importer" target="blank">Smackcoders</a>.</div>
