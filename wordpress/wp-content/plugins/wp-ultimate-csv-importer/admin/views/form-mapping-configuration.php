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
$importAs = $templateName = $istemplate = '';
if(isset($_REQUEST['istemplate']) && sanitize_text_field($_REQUEST['istemplate']) == 'no') {
   $istemplate = 'no';
}

if($_POST) {
   if($_REQUEST['istemplate'] == 'no'){
      $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
      $records['import_file'] = $_POST;
      $uci_admin->SetPostValues(sanitize_key($_REQUEST['eventkey']), $records);
   }else{
      $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
      $records['suggested_template'] = $_POST;
      $post_values = $uci_admin->GetPostValues(sanitize_key($_REQUEST['eventkey']));
      
      $result = array_merge($post_values[$_REQUEST['eventkey']], $records);
      $uci_admin->SetPostValues(sanitize_key($_REQUEST['eventkey']), $result);
   }
}
$eventKey = sanitize_key($_REQUEST['eventkey']);
$get_records = $uci_admin->GetPostValues($eventKey);
$import_type = $get_records[$eventKey]['import_file']['posttype'];

if(!empty($get_records[$eventKey]['mapping_config']) && $get_records[$eventKey]['mapping_config']) {
   $mapping_screendata = $uci_admin->get_mapping_screendata( $import_type, $get_records[$eventKey]['mapping_config']);
}
$file = SM_UCI_IMPORT_DIR . '/' . $eventKey . '/' . $eventKey;
$parserObj->parseCSV($file, 0, -1);
$Headers = $parserObj->get_CSVheaders();
$Headers = $Headers[0];
global $wpdb;
if($istemplate == 'no'){
   $backlink = esc_url(admin_url() . 'admin.php?page=sm-uci-import&step=sm-uci-import');
   $actionURL = esc_url(admin_url() . 'admin.php?page=sm-uci-import&step=media_config&istemplate=no&eventkey='.$_REQUEST['eventkey']);
}else{
   $backlink = esc_url(admin_url() . 'admin.php?page=sm-uci-import&step=suggested_template&eventkey='.$_REQUEST['eventkey']);
   $actionURL = esc_url(admin_url() . 'admin.php?page=sm-uci-import&step=media_config&eventkey='.$_REQUEST['eventkey']);
}
$templateName = '';
if(isset($_REQUEST['templateid'])) {
   $templateInfo = $wpdb->get_results($wpdb->prepare("select templatename, mapping from wp_ultimate_csv_importer_mappingtemplate where id = %d", $_REQUEST['templateid']));
   $template_mapping = maybe_unserialize($templateInfo[0]->mapping);
   $templateName = $templateInfo[0]->templatename;
   $actionURL .= '&templateid=' . intval($_REQUEST['templateid']);
   $backlink .= '&templateid=' . intval($_REQUEST['templateid']);
}
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') {
   $templateInfo = $wpdb->get_results($wpdb->prepare("select templatename, mapping from wp_ultimate_csv_importer_mappingtemplate where eventKey = %s", $_REQUEST['eventkey']));
   $template_mapping = maybe_unserialize($templateInfo[0]->mapping);
   $templateName = $templateInfo[0]->templatename;
}
?>
<?php
$normal = 'active'; $advanced = '';
if(isset($_REQUEST['mapping_type']) && $_REQUEST['mapping_type'] == 'normal') {
   $normal = 'active';
   $advanced = '';
} elseif(isset($_REQUEST['mapping_type']) && $_REQUEST['mapping_type'] == 'advanced') {
   $normal = '';
   $advanced = 'active';
}
?>
<div class="col-md-6 col-md-offset-3">
   <ul class="mapping-switcher">
      <li class="<?php echo $normal; ?>" onclick="mapping_type('normal');">Advanced Mode</li>
      <li class="<?php echo $advanced; ?>" onclick="mapping_type('advanced');">Drag & Drop Mode</li>
   </ul>
</div>
<div class="clearfix"></div>


<div class="list-inline pull-right mb10 wp_ultimate_csv_importer_pro">
            <div class="col-md-6 mt10"><a href="https://goo.gl/jdPMW8" target="_blank">Documentation</a></div>
            <div class="col-md-6 mt10"><a href="https://goo.gl/fKvDxH" target="_blank">Sample CSV</a></div>
         </div>

<div class="template_body wp_ultimate_csv_importer_pro whole_body" style="margin-top: 40px;">
   <div>
      <h3 style="margin-left:2%;" class="csv-importer-heading"><?php echo esc_html__('Mapping Section','wp-ultimate-csv-importer');?></h3>
   </div>
   <form id = "mapping_section" method="post" action="<?php echo $actionURL;?>">
      <div id='wp_warning' style = 'display:none;' class = 'error'></div>
      <?php $import_mode = $get_records[sanitize_key($_REQUEST['eventkey'])]['import_file']['import_mode']; ?>
      <div class="mapping_table">
         <?php
	 $profeatures = array('acf_pro_fields','acf_fields','acf_repeater_fields','types_custom_fields','cctm_custom_fields','pods_custom_fields','yoast_seo_fields');
         $integrations = $uci_admin->available_widgets($import_type, $importAs);
         if(!empty($integrations)) :
            foreach($integrations as $widget_name => $plugin_file) {
               $widget_slug = strtolower(str_replace(' ', '_', $widget_name));
	       if(in_array($widget_slug,$profeatures)){
			$upgrade_pro = '<span style="background-color: #ec3939 !important;float:right;font-size:14px;" class="new badge">Upgrade to Pro</span>';
			$fields = '';
	       }else{
			$upgrade_pro = '';
               		$fields = $uci_admin->get_widget_fields($widget_name, $import_type, $importAs);
	       }
               ?>
               <div class="panel-group" id='accordion'>
                  <div class='panel panel-default' data-target="#<?php echo $widget_slug;?>" data-parent="#accordion">
                     <div id='<?php echo $widget_slug;?>' class='panel-heading' style='width:100%'  onclick="toggle_func('<?php echo $widget_slug;?>');">
                        <div id= "corehead" class="panel-title"> <b style=""> <?php if($widget_name == 'Core Fields'){ echo 'WordPress Fields'; } else { echo $widget_name; } ?> </b>
                           <span class = 'glyphicon glyphicon-plus' id = '<?php echo 'icon'.$widget_slug ?>' style="float:right;"> </span>
			   <?php echo $upgrade_pro; ?>
                        </div>
                     </div>
                     <div id= '<?php echo $widget_slug;?>toggle'  style="height:auto;">
                        <div class="grouptitlecontent " id="corefields_content">
                           <table class='table table-mapping custom_table' id='<?php echo $widget_slug;?>_table' style='font-size: 12px;margin-bottom:0px;' id='$tableid'>
                              <tbody>
                              <tr>
                                 <?php if($import_mode != 'new_items') { ?>
                                    <td style='width:10%;padding:15px;'>
                                       <input type='checkbox' name = 'name<?php  print($plugin_file); ?>' id = 'id<?php print($plugin_file); ?>' onClick="select_All(this.id,'<?php print($plugin_file);?>','<?php print($widget_slug);?>')">
                                    </td>
                                 <?php } ?>
                                 <td class='columnheader mappingtd_style'><label class='groupfield'><?php echo esc_html__('WP Fields','wp-ultimate-csv-importer');?></label></td>
                                 <td class='columnheader mappingtd_style'><label class='groupfield'><?php echo esc_html__('CSV Header','wp-ultimate-csv-importer');?></label></td>
                                 <td style='width:20%;'></td>
                                 <td style='width:30%;'></td>
                              </tr>
                              <div>
                                 <?php
                                 $CORE_count = 0; $mappingcount = 0;
                                 if(!empty($fields)){
                                    foreach($fields as $key => $value) {
                                       $prefix = $key;
                                       foreach ($value as $key1 => $value1) {
                                          $label = $value1['label'];
                                          $name = $value1['name']; ?>
                                          <tr id='<?php print($prefix); ?>_tr_count<?php print($CORE_count); ?>'>
                                             <td id='<?php print ($prefix); ?>_tdg_count<?php print($CORE_count); ?>' class='left_align' style='width:20%;'>
						 <label class='wpfields'> <?php print('<b style="">'.$label.'</b></label><br><label class="samptxt">[Name: '.$name.']'); ?> </label>
                                                <input type='hidden' name='<?php echo $key . '__fieldname' . $mappingcount; ?>' id='<?php echo $key . '__fieldname' . $mappingcount; ?>' value='<?php echo $name; ?>' class='hiddenclass'/>
                                             </td>
                                             <td class="mappingtd_style">
                                                <div id="headerlist" class="">
						<div class = "mapping-select-div">
                                                   <select class="selectpicker"  id="<?php print($prefix); ?>__mapping<?php print($mappingcount); ?>" style="height:25px;" name="<?php print($prefix); ?>__mapping<?php print($mappingcount); ?>" onchange="enable_mapping_fields('<?php echo $prefix; ?>', '<?php echo $mappingcount; ?>', this.id);">
                                                      <option value="--select--"> --select-- </option>
                                                      <?php foreach ($Headers as $csvkey => $csvheader) {
                                                         if(!empty($template_mapping[$key])) {
                                                            $mapping_selected = null;
                                                            if(array_key_exists($name,$template_mapping[$key]) && $csvheader == $template_mapping[$key][$name]) {
                                                               $mapping_selected = 'selected'; ?>
                                                               <?php
                                                            } ?>
                                                            <option value="<?php echo $csvheader; ?>" <?php echo $mapping_selected;?>> <?php echo $csvheader; ?> </option>
                                                         <?php } else {
                                                            if(!empty($mapping_screendata[$key])){
                                                      if(array_key_exists($name,$mapping_screendata[$key]) && $csvheader == $mapping_screendata[$key][$name]){
                                                      $mapping_selected = 'selected'; ?>

                                                      <option value="<?php echo $csvheader; ?>" <?php echo $mapping_selected;?>> <?php echo $csvheader; ?> </option>
                                                   <?php }
                                                   }
                                                            if($name == $csvheader) {?>
                                                               <option value="<?php print($csvheader); ?>" selected><?php print($csvheader); ?> </option>
                                                               <?php
                                                            } else { ?>
                                                               <option value="<?php echo $csvheader; ?>"><?php echo $csvheader; ?> </option>
                                                            <?php }
                                                         }
                                                      }?>
                                                      <option value="header_manip">Header Manipulation</option>
                                                   </select></div>
                                                </div>
                                             </td>
                                             <td class="mappingtd_style"></td>
                                             <td><div class="mapping-static-formula-group">
                                                <span title='Static' style='margin-right:15px;' id='<?php echo $prefix; ?>_staticbutton_mapping<?php echo $mappingcount; ?>' onclick="static_method(this.id, '<?php echo $prefix; ?>', '<?php echo $mappingcount; ?>', null)"><img style="margin-right:15px;" src="<?php echo plugins_url().'/'.SM_UCI_SLUG ;?>/assets/images/static.png" width="24" height="24" /></span>
                                                <span title='Formula' style='margin-right:15px;' id='<?php echo $prefix; ?>_formulabutton_mapping<?php echo $mappingcount; ?>' onclick="formula_method(this.id, '<?php echo $prefix; ?>', '<?php echo $mappingcount; ?>', null)"><img src="<?php echo plugins_url().'/'.SM_UCI_SLUG ;?>/assets/images/formula.png" width="24" height="24" /></span></div>
                                                <div class="mapping-select-close-div" id="<?php echo $prefix; ?>_customdispdiv_mapping<?php echo $mappingcount; ?>" style='height:246px;padding:8px;display:none;width:300px;border:3px solid #2ea2cc;margin-top:5px;position:absolute;background-color:#ffffff;z-index:99;'></div>
                                             </td>
                                          </tr>
                                       <?php
                                       if(!empty($template_mapping[$key][$name])) {
                                       if(preg_match_all("/({([a-z A-Z 0-9 | , _ -]+)(.*?)(}))/", $template_mapping[$key][$name], $results, PREG_PATTERN_ORDER)) { ?>
                                          <script>
                                             var dropdownid = '<?php echo $prefix.'__mapping'.$mappingcount;?>';
                                             document.getElementById(dropdownid).value = 'header_manip';
                                             var buttonid = '<?php echo $prefix.'_staticbutton_mapping'.$mappingcount;?>';
                                             var staticheader = '<?php echo $template_mapping[$key][$name];?>';
                                             static_method(buttonid,'<?php echo $prefix;?>','<?php echo $mappingcount;?>',staticheader);
                                          </script>
                                       <?php } elseif(preg_match_all("/({([\w]+)(.*?)(}))/", $template_mapping[$key][$name], $results, PREG_PATTERN_ORDER)) { ?>
                                          <script>
                                             var dropdownid = '<?php echo $prefix.'__mapping'.$mappingcount;?>';
                                             document.getElementById(dropdownid).value = 'header_manip';
                                             var buttonid = '<?php echo $prefix.'_formulabutton_mapping'.$mappingcount;?>';
                                             var formulaheader = '<?php echo $template_mapping[$key][$name];?>';
                                             formula_method(buttonid,'<?php echo $prefix;?>','<?php echo $mappingcount;?>',formulaheader);
                                          </script>
                                       <?php }
                                       }
                                          $CORE_count++;
                                          $mappingcount++;
                                       }
                                    }
                                 }?>
                              </tbody>
                           </table>
                           <input type='hidden' id='<?php echo $widget_slug;?>_count' value= '<?php echo $CORE_count; ?>'>
                           <?php
                           if($widget_slug=='wordpress_custom_fields'|| $widget_slug=='acf_pro_fields'|| $widget_slug=='acf_fields' || $widget_slug=='types_custom_fields' || $widget_slug=='pods_custom_fields') {
                             
       ?>

                              <div class="customfield_btndiv">
                                 
                              </div>
                           <?php } ?>
                        </div>
                     </div>
                  </div>
               </div>
               <script>
                  var id = '<?php echo $widget_slug;?>'+'toggle';
                  if(id != 'core_fieldstoggle') {
                     jQuery('#'+id).hide();
                  } else {
                     jQuery("#iconcore_fields").attr('class','glyphicon glyphicon-minus')
                  }
               </script>
               <?php
            }
         
         endif;
         $save_templatename = '';
         if($save_templatename == '' && $templateName == '') {
            $filename = isset($get_records[$eventKey]['import_file']['uploaded_name']) ? $get_records[$eventKey]['import_file']['uploaded_name'] : '';
            $file_extension = pathinfo($filename,PATHINFO_EXTENSION);
            $file_extn = '.'.$file_extension;
            $filename = explode($file_extn, $filename);
            $templateName = $filename[0];
         }
      
         ?>
      </div>
      <div class="col-md-12 mt20">
         <!-- back btn -->
         <div class="pull-left">
            <a class="smack-btn btn-default btn-radius" href="<?php echo $backlink;?>">Back</a>
       <!--        <input type="button" class="btn-link mapping_backbtn" value="<?php echo esc_attr__('Back','wp-ultimate-csv-importer');?>"> back btn end -->
         </div>
         <!--continue btn -->
         <div class="pull-right">
            <input type="button" class="smack-btn smack-btn-primary btn-radius" value="<?php echo esc_attr__('Continue','wp-ultimate-csv-importer'); ?>" onclick="save_template('<?php echo $import_type;?>');" />
                <!-- continue btn end -->
          </div>
      </div>
      <div class="clearfix"></div>
         <div class="mb20"></div>
      <input type="hidden" name="smack_uci_mapping_method" value="<?php if(isset($_REQUEST['mapping_type'])) echo sanitize_title($_REQUEST['mapping_type']); ?>">

   </form>
</div>
<input type='hidden' id='h1' name='h1' value='<?php if (isset($mappingcount)) { echo $mappingcount; } ?>'/>

<div style="font-size: 15px;text-align: center;padding-top: 20px">Powered by <a href="https://www.smackcoders.com?utm_source=wordpress&utm_medium=plugin&utm_campaign=free_csv_importer" target="blank">Smackcoders</a>.</div>
