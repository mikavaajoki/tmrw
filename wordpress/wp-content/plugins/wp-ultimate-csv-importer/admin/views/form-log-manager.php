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
?>
<div align="center" class="wp_ultimate_csv_importer_pro" style="font-size: 13px;">
	<div class = "title">
		<h3 class="csv-importer-heading"><?php echo esc_html__('Log Info','wp-ultimate-csv-importer');?></h3>
		<div style="">
			<?php
			global $fileObj;
			global $wpdb;
			$records = $wpdb->get_results("SELECT * FROM smackuci_events");
			?>
			<table class="table table-mapping table-fixed table-manager mt30">
				<thead>
				<tr class="" style="">
					<th width="27%"><?php echo esc_html__('FileName','wp-ultimate-csv-importer');?></th>
					<th width="28%"><?php echo esc_html__('Module','wp-ultimate-csv-importer');?></th>
					<th width="10%"><?php echo esc_html__('Inserted','wp-ultimate-csv-importer');?></th>
					<th width="10%"><?php echo esc_html__('Updated','wp-ultimate-csv-importer');?></th>
					<th width="10%"><?php echo esc_html__('Skipped','wp-ultimate-csv-importer');?></th>
					<th width="15%" style="text-align: center;"><?php echo esc_html__('Download','wp-ultimate-csv-importer');?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach($records as $record_data) { ?>
					<tr style="padding: 20px 0 10px 0;">
						<td width="27%" style="overflow-wrap: break-word;">
							<?php echo $record_data->original_file_name;?>
							<br>
							<b><?php echo esc_html__('Revision: ','wp-ultimate-csv-importer');?> </b> <?php echo $record_data->revision; ?>
						</td>
						<td width="28%"><?php echo $record_data->import_type;?></td>
						<td align="center" width="10%" style="text-align: center;"><?php echo $record_data->created;?></td>
						<td align="center" width="10%" style="text-align: center;"><?php echo $record_data->updated;?></td>
						<td align="center" width="10%" style="text-align: center;"><?php echo $record_data->skipped;?></td>
						<td align="center" width="15%" class="row-links" style="text-align: center;">
							<?php
							$get_upload_url = wp_upload_dir();
							$eventkey =  $record_data->eventKey;
							$uploadLogURL = $get_upload_url['baseurl'] . '/smack_uci_uploads/imports/' . $eventkey . '/' . $eventkey;
							$logfilename = $uploadLogURL.".log";
							?>
							<div class="download-icon">
							<a href="<?php echo $logfilename; ?>" download id="dwnldlog" style="margin-left:5px;font-size:15px;">
								<span class="icon-cloud-download3"></span>
							</a><span class="download-text"><?php echo esc_html__('Download','wp-ultimate-csv-importer');?></span>
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<script>
	jQuery(document).ready(function () {
		var i;
		for(i=1;i<5;i++) {
			jQuery('#'+i).addClass("bg-leftside");
			jQuery('#'+i).removeClass("selected");
		}
		jQuery('#5').addClass("selected");
		jQuery('#5').removeClass("bg-leftside");
	});
</script>
