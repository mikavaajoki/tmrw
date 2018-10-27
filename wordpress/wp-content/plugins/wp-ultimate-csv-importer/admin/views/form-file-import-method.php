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
$ucisettings = get_option('sm_uci_pro_settings');
$main_mode = isset($ucisettings['enable_main_mode']) ? $ucisettings['enable_main_mode'] : '';
$active_plugins = get_option( "active_plugins" );
if (!in_array('import-users/index.php', $active_plugins)) {
   $user_import = 'no';
}
else{
   $user_import = 'yes';
}
?>
<div class="whole_body wp_ultimate_csv_importer_pro">
   <form class="form-horizontal" id="form_import_file" method="post" action= "<?php echo esc_url(admin_url() . 'admin.php?page=sm-uci-import&step=suggested_template');?>" enctype="multipart/form-data">
   <div id='wp_warning_main' class = 'updated notice'>
   <p>Supported file types .csv .zip .txt 
   </p></div>

 <?php if($main_mode == 'on') { ?>
      <div id='wp_warning_main' style = 'margin-top: 10px;font-size: 15px;color: red;' class = 'error' > Maintenance mode is enabled. <a style="cursor: pointer;" onclick="saveoptions('main_check_import_off', 'off')"> Disable </a> </div>
<?php } ?>
<input type="hidden" id="check_user_import" value="<?php echo $user_import; ?>">
<div id='user_import_warning' class = 'notice notice-warning is-dismissible' style="display: none;">
   <p> Importing User feature in Ultimate CSV Importer FREE moved to a separate add-on. To continue import users, kindly install <a href="https://wordpress.org/plugins/import-users/" target="blank">Import Users</a> addon.
   </p></div>

      <div id='wp_warning' style = 'display:none;' class = 'error'></div>
      <input type='hidden' id="siteurl" value="<?php echo site_url(); ?>" />
      <!-- Code Added For POP UP  Starts here -->
      <div class='modal fade' id = 'modal_zip' tabindex='-1' role='dialog' aria-labelledby='mymodallabel' aria-hidden='true'>
         <div class='modal-dialog'>
            <div class='modal-content'>
               <div class='modal-header'>
                  <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
                  <h4 class='modal-title' id='mymodallabel'> <?php echo esc_html_e("Choose CSV/XML to import","wp-ultimate-csv-importer");?> </h4>
               </div>
               <div class='modal-body' id = 'choose_file'>
                  ...
               </div>
               <div class='modal-footer'>
                  <!--<button type='button' class='btn btn-default' data-dismiss='modal'>close</button>  -->
                  <button type='button' class='smack-btn smack-btn-primary btn-radius' data-dismiss='modal'><?php echo esc_html_e("Close","wp-ultimate-csv-importer");?></button>
               </div>
            </div>
         </div>
      </div>
      <!-- Code Added For POP UP Ends here -->
      <div class="">
         <div class="list-inline pull-right mb10">
            <div class="col-md-6 mt10"><a href="https://goo.gl/jdPMW8" target="_blank"><?php echo esc_html__('Documentation','wp-ultimate-csv-importer');?></a></div>
            <div class="col-md-6 mt10"><a href="https://goo.gl/fKvDxH" target="_blank"><?php echo esc_html__('Sample CSV','wp-ultimate-csv-importer');?></a></div>
         </div>
      </div>
      <div class="clearfix"></div>
      <div class="panel upload-view" style="width: 98%;">
         <!-- <div class="panel-heading">
            <h1 class="text-center"><?php //echo esc_html__('Hello, Choose CSV/XML to import','wp-ultimate-csv-importer');?></h1>
            </div> -->
    <div id="warningsec" style="color:red;width:100%; min-height: 110px;border: 1px solid #d1d1d1;background-color:#fff;display:none;">
        <div id ="warning" class="display-warning" style="color:red;align:center;display:inline;font-weight:bold;font-size:15px; border: 1px solid red;margin:2% 2%;padding: 20px 0 20px;position: absolute;text-align: center;width:93%;display:none;"> </div>
</div>          
         <div class="panel-body">
            <div class="row">
               <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 bhoechie-tab-container">
                  <div class="col-lg-3 col-md-3 col-sm-4 col-xs-12 no-padding bhoechie-tab-menu">
                     <div class="list-group">
                        <a id="1" href="#" class="list-group-item active text-left" onclick="show_upload(this.id);">
                           <h4 class="glyphicon glyphicon-upload icon-cloud-upload"></h4>
                           <?php echo esc_html__('Upload from Desktop','wp-ultimate-csv-importer');?>
                        </a>
                        <a id="2" href="#" class="list-group-item text-left" disabled="disabled">
                           <h4 class="glyphicon glyphicon-upload icon-upload"></h4>
                           <?php echo esc_html__('Upload from FTP/SFTP','wp-ultimate-csv-importer');?>
                           <span style="background-color: #ec3939 !important" class="new badge">Pro</span>
                        </a>
                        <a id="3" href="#" class="list-group-item text-left" disabled="disabled">
                           <h4 class="glyphicon glyphicon-upload icon-link2"></h4>
                           <?php echo esc_html__('Upload from URL','wp-ultimate-csv-importer');?>
                           <span style="background-color: #ec3939 !important" class="new badge">Pro</span>
                        </a>
                        <a id="4" href="#" class="list-group-item text-left" disabled="disabled">
                           <h4 class="glyphicon glyphicon-upload icon-tree"></h4>
                           <?php echo esc_html__('Choose File in the Server','wp-ultimate-csv-importer');?>
                           <span style="background-color: #ec3939 !important" class="new badge">Pro</span>
                        </a>
                     </div>
                  </div>
                  <div class="col-lg-9 col-md-9 col-sm-8 col-xs-12 no-padding bhoechie-tab">
                     <div id ='displaysection' class="col-md-12" style='display: none;'>
                        <div id="displayname">
                           <div id="filenamedisplay"></div>
                        </div>
                        <div class="">
                           <!-- <progress id ='progressdiv' value="100" max="100"> </progress> -->
                           <div id="progress-div">
                              <div id="progress-bar">
                                 <span class="progresslabel">
                                 </span>
                              </div>
                           </div>
                           <div id="targetLayer"></div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="form-group mt10">
                           <label>
                              <input type="radio" name="import_mode" id="mode_insert" value="new_items" checked="checked"> <?php echo esc_html__('New Items','wp-ultimate-csv-importer');?>
                           </label>
                           <label class="pl20" title="Please upgrade to PRO for updating records">
                           <div class="col-xs-6 col-xs-offset-0 col-sm-3 col-sm-offset-0 col-md-2 col-md-offset-0"><label class="wp_img_size"><input style="display:none"id="mode_update" ></div>
                            <input type="radio" name="import_mode" id="mode_update" value="existing_items" disabled="disabled"><?php echo esc_html__(' Existing Items','wp-ultimate-csv-importer');?>
                           </label>
                        </div>
                        <div id="select_module" class="select_module col-md-8 col-md-offset-3">
                           <span>
                           <label class="import-textnew"><?php echo esc_html('Import each record as','wp-ultimate-csv-importer');?></label>
                           </span>
                           <span class="select_box" style="width:200px;height:40px;">
                              <select class="search_dropdown selectpicker" id="search_dropdowns" data-size="5" name ='posttype' style="width:200px;height:39px;">
                                 <?php global $uci_admin; $all_post_types = $uci_admin->get_import_post_types(); ?>
                                 <optgroup label="PostType">
                                    <?php foreach ($all_post_types as $key => $type) { ?>
                                    <option value="<?php print($type);?>"><?php print($key); ?></option>
                                    <?php }?>
                                 </optgroup>
                                 <?php #NOTE: Removed the import options for terms & taxonomies ?>
                              </select>
                           </span>
                        </div>
                        <div class="col-md-1 col-md-offset-10 col-sm-1 col-sm-offset-8 mt20">
                           <input type ="submit" class="smack-btn smack-btn-primary btn-radius ripple-effect continue-btn" disabled value="Continue">
                        </div>
                     </div>
                     <div class="bhoechie-tab-content active" id="division1">
                        <div class="file_upload">
                           <input id="upload_file" type="file" name = "files[]" onchange ="upload_method()"/>
                           <div class="file-upload-icon">   
            <span id="fileupload" style="cursor: pointer;" class="import-icon"> <img src="<?php echo plugins_url().'/'.SM_UCI_SLUG ;?>/assets/images/upload-128.png" width="60" height="60" /> </span>
                           <span class="file-upload-text"><?php echo esc_html__('Click here to upload from desktop','wp-ultimate-csv0-importer-pro');?></span>
             </div>
         </div>
                     </div>
                     <div class="bhoechie-tab-content" id="division5" style="width: 100%;text-align: center;margin-top: 150px;font-size: 2.2em;color: red;">
              <?php echo esc_html__('This feature is only available in PRO.','wp-ultimate-csv-importer');?>
                </div>
                  </div>
               </div>
               <!-- Row -->
            </div>
            <!-- Panel Body -->
         </div>
      </div>
      <script type="text/javascript">
         jQuery(document).ready(function() {
              jQuery('#mode_update').click(function(e) {
                swal('Warning!', 'Please upgrade to PRO', 'warning')
              });
            jQuery("div.bhoechie-tab-menu>div.list-group>a").click(function(e) {
               e.preventDefault();
               jQuery(this).siblings('a.active').removeClass("active");
               jQuery(this).addClass("active");
               var index = jQuery(this).index();
               if(index == 0) {
                  jQuery("div.bhoechie-tab>div.bhoechie-tab-content").removeClass("active");
                  jQuery("div.bhoechie-tab>div.bhoechie-tab-content").eq(index).addClass("active");
               } else {
                  jQuery("div.bhoechie-tab>div.bhoechie-tab-content").removeClass("active");
                  //jQuery("div.bhoechie-tab>div.bhoechie-tab-content").eq(5).addClass("active");
                  jQuery("div#division5").addClass("active");
               }
            });
         });
      </script>
      <input type='hidden' id='uploaded_name' name='uploaded_name' value =''>
      <input type='hidden' id='file_name' name='file_name' value =''>
      <input type="hidden" id="file_extension" name="file_extension" value="">
      <input type="hidden" id="import_method" name = "import_method" value="desktop">
      <input type='hidden' id='file_version' name='file_version' value=''>
      <input type='hidden' id='upload_max' name='upload_max' value='<?php echo ini_get('upload_max_filesize');?>'>
   </form>
</div>

<div style="font-size: 15px;text-align: center;padding-top: 20px">Powered by <a href="https://www.smackcoders.com?utm_source=wordpress&utm_medium=plugin&utm_campaign=free_csv_importer" target="blank">Smackcoders</a>.</div>
