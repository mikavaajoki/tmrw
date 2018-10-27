<?php
/*********************************************************************************
 * WP Ultimate CSV Importer is a Tool for importing CSV for the Wordpress
 * plugin developed by Smackcoders. Copyright (C) 2016 Smackcoders.
 *
 * WP Ultimate CSV Importer is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License version 3
 * as published by the Free Software Foundation with the addition of the
 * following permission added to Section 15 as permitted in Section 7(a): FOR
 * ANY PART OF THE COVERED WORK IN WHICH THE COPYRIGHT IS OWNED BY WP Ultimateheck
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
$available_fields = array();
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
$file = SM_UCI_IMPORT_DIR . '/' . $eventKey . '/' . $eventKey;
$parserObj->parseCSV($file, 0, -1);
$rowData = $parserObj->parseCSV($file, 1, -1);
$Headers = $parserObj->get_CSVheaders();
$Headers = $Headers[0];
$import_mode = $get_records[sanitize_key($_REQUEST['eventkey'])]['import_file']['import_mode'];
$integrations = $uci_admin->available_widgets($import_type, $importAs);
# print '<pre>';
# print_r($Headers); print_r($rowData);
# print $import_type;
if(!empty($integrations)) :
   foreach($integrations as $widget_name => $plugin_file) {
      $widget_slug = strtolower( str_replace( ' ', '_', $widget_name ) );
      $fields = $uci_admin->get_widget_fields( $widget_name, $import_type, $importAs );
      if(!empty($fields[$plugin_file]))
         $available_fields[$plugin_file] = $fields[$plugin_file];
   }
endif;
# print_r($integrations);
# print_r($available_fields);
# print '</pre>';
$get_post_type = $uci_admin->import_post_types($get_records[$eventKey]['import_file']['posttype']);
global $wpdb;
if($istemplate == 'no'){
   $backlink = esc_url(admin_url() . 'admin.php?page=sm-uci-import&step=sm-uci-import');
   $actionURL = esc_url(admin_url() . 'admin.php?page=sm-uci-import&step=media_config&istemplate=no&eventkey='.$_REQUEST['eventkey']);
} else {
   $backlink = esc_url(admin_url() . 'admin.php?page=sm-uci-import&step=suggested_template&eventkey='.$_REQUEST['eventkey']);
   $actionURL = esc_url(admin_url() . 'admin.php?page=sm-uci-import&step=media_config&eventkey='.$_REQUEST['eventkey']);
}
$templateName = ''; $template_mapping = array();
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
$ecommerce_supported_modules = array('WooCommerceVariations', 'WooCommerceOrders', 'WooCommerceCoupons', 'WooCommerceRefunds', 'MarketPressVariations', 'WPeCommerceCoupons', 'event', 'event-recurring', 'ticket', 'location');
$ecommerce_module = array('WooCommerce', 'MarketPress', 'WPeCommerce', 'eShop');
?>
<!-- <head>
   <meta charset="UTF-8">
   <title>Mapping Section</title>
   <link rel="stylesheet" type="text/css" href="style.css">
   </head> -->
<form id ="mapping_section" autocomplete="off" method="post" action="<?php echo $actionURL;?>">
   <main id="main ">
      <div id="mapping-container">
         <div id="header1"></div>
         <div id="mapping-content">
            <!-- <div class="col-md-6 col-md-offset-3 mapping-option-section" style="background-color: #fff;padding:10px;">
               <div class="col-md-6" style="text-align:center;"> <label class="control-label"><input name="ping_status" type="radio"></label></div>
               <div class="col-md-6" style="text-align:center;"><label class="control-label"><input name="ping_status" type="radio"></label></div>
            </div> -->
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
                  <li class="<?php echo $normal; ?>"  onclick="mapping_type('normal');">Advanced Mode</li>
                  <li class="<?php echo $advanced; ?>" onclick="mapping_type('advanced');">Drag & Drop Mode</li>
               </ul>
            </div>

            <div class="uci_mapping_panel mt20 content">
               <?php if(in_array($get_post_type, get_post_types()) && !in_array($get_records[$eventKey]['import_file']['posttype'], $ecommerce_supported_modules)) { ?>
                  <div class="dropitems col-md-12 mt20">
                     <div class="panel-group" id='accordion'>
                        <div class='panel' data-target="#core_fields" data-parent="#accordion">
                           <div id="corehead" class="core_info panel-heading csv-importer-heading" onclick="toggle_func('core_fields');">Title &amp; Content
                              <span class="icon-circle-down pull-right" id="iconcore_fields"></span>
                           </div>
                           <div id="core_fieldstoggle" class="widget_fields panel-body widget_open_field" >
                              <div class="droppableHolder">
                                 <div class="wp_csv_ftp form-group">
                                    <input type="text" placeholder="Drag & drop any element on the right to set the title." name="CORE__post_title" id="CORE__post_title" class="droppable form-control" value="<?php if(isset($template_mapping['CORE']['post_title'])) { echo $template_mapping['CORE']['post_title']; } ?>">
                                 </div>
                                 <div class="wp_csv_ftp form-group">
                                   <!--  <input type="text" placeholder="Content" name="CORE__post_content" id="post_content" class="droppable form-control" value="<?php //echo $template_mapping['CORE']['post_content']; ?>"> -->
                                   <textarea id="CORE__post_content" class="droppable post_content" name="CORE__post_content"><?php if(isset($template_mapping['CORE']['post_content'])) { echo $template_mapping['CORE']['post_content']; } ?></textarea>
                                 </div>
                                 <div class="wp_csv_ftp form-group">
                                    <!-- <input type="text" placeholder="Short Description" name="CORE__post_excerpt" id="post_excerpt" class="droppable form-control" value="<?php //echo $template_mapping['CORE']['post_excerpt']; ?>"> -->
                                    <textarea class="form-control droppable" name="CORE__post_excerpt" id="CORE__post_excerpt"><?php if(isset($template_mapping['CORE']['post_excerpt'])) { echo $template_mapping['CORE']['post_excerpt']; } ?></textarea>
                                 </div>
                                 <div class="wp_csv_ftp form-group">
                                    <input type="text" placeholder="Featured Image" class="form-control droppable" name="CORE__featured_image" id="CORE__featured_image" value="<?php if(isset($template_mapping['CORE']['featured_image'])) { echo $template_mapping['CORE']['featured_image']; } ?>">
                                 </div>
                              </div>
                              <div class="pull-right">
                                 <input type="button" id="smack_uci_preview" name="smack_uci_preview" value="Preview" class="smack-btn smack-btn-primary btn-radius" data-toggle="modal" data-target=".preview_model">
                              </div>
                              <div class="modal animated zoomIn preview_model col-md-6 col-md-offset-1" style="top: 15% !important; left: 20%;" role="dialog">
                                    <!-- Modal content-->
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close text-danger" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">Preview of Row # <span id="csv_row"></span></h4>
                                        </div>
                                        <div class="modal-body"></div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="dropitems col-md-12">
                     <div class="panel-group" id='accordion'>
                        <div class='panel' data-target="#terms_fields" data-parent="#accordion">
                           <div class="terms_info panel-heading csv-importer-heading" onclick="toggle_func('terms_fields');">Taxonomies, Categories &amp; Tags
                              <span class="icon-circle-down pull-right" id="iconterms_fields"></span>
                           </div>
                           <div id="terms_fieldstoggle" class="widget_fields panel-body">
                              <div class="droppableHolder">
                                 <?php foreach($available_fields['TERMS'] as $index => $field_info) { ?>
                                    <div class="wp_csv_ftp form-group">
                                       <input type="text" placeholder="<?php echo $field_info['label']; ?>" name="TERMS__<?php echo $field_info['name']; ?>" id="post_status" class="droppable form-control" value="<?php if(isset($template_mapping['TERMS'][$field_info['name']])) { echo $template_mapping['TERMS'][$field_info['name']]; } ?>">
                                    </div>
                                 <?php } ?>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               <?php } else { ?>
                  <div class="dropitems col-md-12 mt20">
                     <div class="panel-group" id='accordion'>
                        <div class='panel' data-target="#core_fields" data-parent="#accordion">
                           <div id="corehead" class="core_info panel-heading csv-importer-heading" onclick="toggle_func('core_fields');">
                              WordPress Fields <span class="icon-circle-down pull-right" id="iconcore_fields"></span>
                           </div>
                           <div id="core_fieldstoggle" class="widget_fields panel-body widget_open_field" >
                              <div class="droppableHolder">
                                 <?php
                                 $fields = $uci_admin->get_widget_fields('Core Fields', $import_type, $importAs);
                                 #print '<pre>'; print_r($fields); print '</pre>';
                                 if(!empty($fields)) {
                                    foreach ( $fields as $key => $value ) {
                                       foreach ($value as $key1 => $value1) { ?>
                                          <div class="wp_csv_ftp form-group">
                                             <input type="text" placeholder="<?php echo $key1; ?>" name="<?php echo $key;?>__<?php echo $value1['name']; ?>" id="<?php echo $value1['name']; ?>" class="droppable form-control" value="<?php if(isset($template_mapping['CORE'][$value1['name']])) { echo $template_mapping['CORE'][$value1['name']]; } ?>">
                                          </div>
                                       <?php }
                                    }
                                 }
                                 ?>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
		  <?php if( $get_post_type == 'users' ){ ?>
                  <!--WP-Members-->
                  <div class="dropitems col-md-12">
                     <div class="panel-group" id='accordion'>
                        <div class='panel' data-target="#wp_members" data-parent="#accordion">
                           <div id="corehead" class="core_info panel-heading csv-importer-heading" onclick="toggle_func('wp-members');">
                              Custom Fields by WP-Members <span class="icon-circle-down pull-right" id="iconcore_fields"></span>
                           </div>
                           <div id="wp-memberstoggle" class="widget_fields panel-body" >
                              <div class="droppableHolder">
                                 <?php
                                 if(!empty($available_fields['WPMEMBERS'])) {
                                    foreach($available_fields['WPMEMBERS'] as $index => $field_info) { ?>
                                       <div class="form-group wp_csv_ftp">
                                          <input type="text" placeholder="<?php echo $field_info['label']; ?>" name="WPMEMBERS__<?php echo $field_info['name']; ?>" id="post_status" class="droppable form-control" value="<?php if(isset($template_mapping['WPMEMBERS'][$field_info['name']])) { echo $template_mapping['WPMEMBERS'][$field_info['name']]; } ?>">
                                       </div>
                                    <?php }
                                 }
                                 ?>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <!-- END WP-Members -->
                  <!--Ultimate Member-->
                  <div class="dropitems col-md-12">
                     <div class="panel-group" id='accordion'>
                        <div class='panel' data-target="#ultimate_member" data-parent="#accordion">
                           <div id="corehead" class="core_info panel-heading csv-importer-heading" onclick="toggle_func('ultimate-member');">
                              Custom Fields by Ultimate Member <span class="icon-circle-down pull-right" id="iconcore_fields"></span>
                           </div>
                           <div id="ultimate-membertoggle" class="widget_fields panel-body" >
                              <div class="droppableHolder">
                                 <?php
                                 if(!empty($available_fields['ULTIMATEMEMBER'])) {
                                    foreach($available_fields['ULTIMATEMEMBER'] as $index => $field_info) { ?>
                                       <div class="form-group wp_csv_ftp">
                                          <input type="text" placeholder="<?php echo $field_info['label']; ?>" name="ULTIMATEMEMBER__<?php echo $field_info['name']; ?>" id="post_status" class="droppable form-control" value="<?php if(isset($template_mapping['ULTIMATEMEMBER'][$field_info['name']])) { echo $template_mapping['ULTIMATEMEMBER'][$field_info['name']]; } ?>">
                                       </div>
                                    <?php }
                                 }
                                 ?>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <!-- END Ultimate Member -->
                  <!--Billing and Shipping Information-->
                  <div class="dropitems col-md-12">
                     <div class="panel-group" id='accordion'>
                        <div class='panel' data-target="#bsi" data-parent="#accordion">
                           <div id="corehead" class="core_info panel-heading csv-importer-heading" onclick="toggle_func('bsi');">
                              Billing And Shipping Information <span class="icon-circle-down pull-right" id="iconcore_fields"></span>
                           </div>
                           <div id="bsitoggle" class="widget_fields panel-body" >
                              <div class="droppableHolder">
                                 <?php
                                 if(!empty($available_fields['BSI'])) {
                                    foreach($available_fields['BSI'] as $index => $field_info) { ?>
                                       <div class="form-group wp_csv_ftp">
                                          <input type="text" placeholder="<?php echo $field_info['label']; ?>" name="BSI__<?php echo $field_info['name']; ?>" id="post_status" class="droppable form-control" value="<?php if(isset($template_mapping['BSI'][$field_info['name']])) { echo $template_mapping['BSI'][$field_info['name']]; } ?>">
                                       </div>
                                    <?php }
                                 }
                                 ?>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <!-- END Billing and Shipping Information -->
               <?php } }
               if(!in_array($get_records[$eventKey]['import_file']['posttype'], $ecommerce_supported_modules)) { ?>
                  <div class="dropitems col-md-12">
                     <div class="panel-group" id='accordion'>
                        <div class='panel' data-target="#custom_fields" data-parent="#accordion">
                           <div class="custom_fields_info panel-heading csv-importer-heading"
                                onclick="toggle_func('custom_fields');">Custom Fields
                              <span class="icon-circle-down pull-right" id="iconcustom_fields"></span>
                           </div>
                           <div id="custom_fieldstoggle" class="widget_fields panel-body">
                              <div class="row">
                                 <hr class="border-bottom-hr">
                              </div>
                              <div class="col-md-12 mt20">
                                 <div class="wpecommerce_tab">
                                    <ul class="nav nav-tabs" role="tablist">
                                       <li role="presentation">
                                          <a href="#acf" aria-controls="acf" role="tab" data-toggle="tab">ACF</a>
                                       </li>
                                       <li role="presentation">
                                          <a href="#types" aria-controls="types" role="tab" data-toggle="tab">TYPES</a>
                                       </li>
                                       <li role="presentation">
                                          <a href="#pods" aria-controls="pods" role="tab" data-toggle="tab">PODS</a>
                                       </li>
                                       <li role="presentation" class="active">
                                          <a href="#custom-fields" aria-controls="custom-fields" role="tab" data-toggle="tab">Custom Fields</a>
                                       </li>
                                    </ul>
                                    <div class="tab-content">
                                       <div role="tabpanel" class="tab-pane" id="acf">
                                          <div class="col-md-12 mt20">
                                             <?php
                                             if(!empty($available_fields['ACF'])) {
                                                $field_row = 0;
                                                foreach ( $available_fields['ACF'] as $index => $field_info ) {
                                                   $field_row++; ?>
                                                   <div class="form-group" id="ACF-row<?php echo $field_row; ?>">
                                                      <div class="col-md-10">
                                                         <input type="text" placeholder="<?php echo $field_info['label']; ?>" name="ACF__<?php echo $field_info['name']; ?>" id="post_status" class="droppable form-control" value="<?php if(isset($template_mapping['ACF'][ $field_info['name'] ])) { echo $template_mapping['ACF'][ $field_info['name'] ]; } ?>">
                                                      </div>
                                                      <div class="col-md-2"><i class="icon-trash4" onclick='removeRow("ACF-row<?php echo $field_row; ?>")'></i></div>
                                                   </div>
                                                <?php }
                                             } ?>
                                          </div>
                                          <div class="clearfix"></div>
                                       </div>
                                       <!-- tabpanel ENd hOMe -->
                                       <div role="tabpanel" class="tab-pane" id="types">
                                          <div class="col-md-12 mt20">
                                             <?php
                                             if(!empty($available_fields['TYPES'])) {
                                                $field_row = 0;
                                                foreach ( $available_fields['TYPES'] as $index => $field_info ) {
                                                   $field_row++; ?>
                                                   <div class="form-group" id="TYPES-row<?php echo $field_row; ?>">
                                                      <div class="col-md-10">
                                                         <input type="text" placeholder="<?php echo $field_info['label']; ?>" name="TYPES__<?php echo $field_info['name']; ?>" id="post_status" class="droppable form-control" value="<?php if(isset($template_mapping['TYPES'][ $field_info['name'] ])) { echo $template_mapping['TYPES'][ $field_info['name'] ]; } ?>">
                                                      </div>
                                                      <div class="col-md-2"><i class="icon-trash4" onclick='removeRow("TYPES-row<?php echo $field_row; ?>");'></i></div>
                                                   </div>
                                                <?php }
                                             } ?>
                                          </div>
                                          <div class="clearfix"></div>
                                       </div>
                                       <div role="tabpanel " class="tab-pane" id="pods">
                                          <div class="col-md-12 mt20">
                                             <div class="form-group ">
                                                <?php
                                                if(!empty($available_fields['PODS'])) {
                                                   $field_row = 0;
                                                   foreach ( $available_fields['PODS'] as $index => $field_info ) {
                                                      $field_row++; ?>
                                                      <div class="form-group" id="PODS-row<?php echo $field_row; ?>">
                                                         <div class="col-md-10">
                                                            <input type="text" placeholder="<?php echo $field_info['label']; ?>" name="PODS__<?php echo $field_info['name']; ?>" id="post_status" class="droppable form-control" value="<?php if(isset($template_mapping['PODS'][ $field_info['name'] ])) { echo $template_mapping['PODS'][ $field_info['name'] ]; } ?>">
                                                         </div>
                                                         <div class="col-md-2"><i class="icon-trash4" onclick='removeRow("PODS-row<?php echo $field_row; ?>");'></i></div>
                                                      </div>
                                                   <?php }
                                                } ?>
                                             </div>
                                          </div>
                                          <div class="clearfix"></div>
                                       </div>
                                       <div role="tabpanel " class="tab-pane active" id="custom-fields">
                                          <div class="col-md-12 mt20">
                                             <div class="form-group ">
                                                <?php
                                                if(!empty($available_fields['CORECUSTFIELDS'])) {
                                                   $field_row = 0;
                                                   foreach ( $available_fields['CORECUSTFIELDS'] as $index => $field_info ) {
                                                      $field_row++; ?>
                                                      <div class="form-group" id="CORECUSTFIELDS-row<?php echo $field_row; ?>">
                                                         <div class="col-md-10">
                                                            <input type="text" placeholder="<?php echo $field_info['label']; ?>" name="CORECUSTFIELDS__<?php echo $field_info['name']; ?>" id="post_status" class="droppable form-control" value="<?php if(isset($template_mapping['CORECUSTFIELDS'][ $field_info['name'] ])) { echo $template_mapping['CORECUSTFIELDS'][ $field_info['name'] ]; } ?>">
                                                         </div>
                                                         <div class="col-md-2"><i class="icon-trash4" onclick='removeRow("CORECUSTFIELDS-row<?php echo $field_row; ?>");'></i></div>
                                                      </div>
                                                   <?php }
                                                } ?>
                                             </div>
                                          </div>
                                          <!-- <div class="clearfix"></div> -->
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <!-- wpecommerce_tab ID End -->
                              <!-- <div class="droppableHolder">
                           <?php
                              if ( ! empty( $available_fields['CORECUSTFIELDS'] ) ) {
                                 foreach ( $available_fields['CORECUSTFIELDS'] as $index => $field_info ) { ?>
                              <div class="wp_csv_ftp form-group">
                                 <input type="text" placeholder="<?php echo $field_info['label']; ?>" name="CORECUSTFIELDS__<?php echo $field_info['name']; ?>" id="post_status" class="droppable form-control">
                              </div>
                              <?php }
                              } ?>
                           </div> -->
                           </div>
                        </div>
                     </div>
                  </div>
                  <?php
               }
               // Section for eCommerce Meta Information
               #print '<pre>'; print_r($get_records); print '</pre>';
               if(in_array($get_records[$eventKey]['import_file']['posttype'], $ecommerce_module) || $get_records[$eventKey]['import_file']['posttype'] == 'WooCommerceVariations' || $get_records[$eventKey]['import_file']['posttype'] == 'MarketPressVariations') { ?>
                  <div class="dropitems col-md-12">
                     <div class="panel-group" id='accordion'>
                        <div class='panel' data-target="#ecom_meta_fields" data-parent="#accordion">
                           <div class="other_info panel-heading csv-importer-heading" onclick="toggle_func('ecom_meta_fields');">eCommerce Meta Information
                              <span class="icon-circle-down pull-right" id="iconecom_meta_fields"></span>
                           </div>
                           <div id="ecom_meta_fieldstoggle" class="widget_fields panel-body">
                              <div class="droppableHolder">
                                 <hr class="border-bottom-hr">
                                 <?php
                                 if(in_array($get_records[$eventKey]['import_file']['posttype'], array('WPeCommerce', 'WPeCommerceCoupons'))) {
                                    require_once ('form-wpecommerce-meta.php');
                                    wpecommerce_meta_api($template_mapping);
                                 } elseif(in_array($get_records[$eventKey]['import_file']['posttype'], array('MarketPress', 'MarketPressVariations'))) {
                                    require_once ('form-marketpress-meta.php');
                                    if(in_array('marketpress/marketpress.php', $uci_admin->get_active_plugins())) {
                                       marketpress_premium_meta_api($template_mapping);
                                    } elseif(in_array('wordpress-ecommerce/marketpress.php', $uci_admin->get_active_plugins())) {
                                       marketpress_lite_meta_api($template_mapping);
                                    }
                                 } else {
                                    if (in_array('import-woocommerce/index.php', $uci_admin->getActivePlugins() )) {
                                       global $wcomUci_admin;
                                       $wcomUci_admin->getWoocomDataHtml($template_mapping);
                                    }
                                    // require_once ('form-woocommerce-meta.php');
                                    // woocommerce_meta_api($template_mapping);
                                 }
                                 ?>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               <?php }
               elseif(in_array($get_records[$eventKey]['import_file']['posttype'], $ecommerce_supported_modules) && $get_records[$eventKey]['import_file']['posttype'] != 'WooCommerceVariations') {
                  $eCommMetaFields = $uci_admin->ecommerceMetaFields($get_records[$eventKey]['import_file']['posttype']); ?>
                  <div class="dropitems col-md-12 mt20">
                  <div class="panel-group" id='accordion'>
                  <div class='panel' data-target="#core_fields" data-parent="#accordion">
                  <div id="corehead" class="core_info panel-heading csv-importer-heading" onclick="toggle_func('meta_fields');">
                     Meta Information <span class="icon-circle-down pull-right" id="iconcore_fields"></span>
                  </div>
                  <div id="meta_fieldstoggle" class="widget_fields panel-body widget_open_field" >
                  <div class="droppableHolder">
                  <?php if ( ! empty( $eCommMetaFields['ECOMMETA'] ) ) {
                     foreach ( $available_fields['ECOMMETA'] as $index => $field_info ) { ?>
                        <div class="wp_csv_ftp form-group">
                           <input type="text" placeholder="<?php echo $field_info['label']; ?>" name="ECOMMETA__<?php echo $field_info['name']; ?>" id="" class="droppable form-control">
                        </div>
                     <?php }
                   } ?>
                  </div>
                  </div>
                  </div>
                  </div>
                  </div>
               <?php }
               // Section for SEO fields (All in One SEO Free & Premium, Yoast SEO Free & Premium)
               if(in_array($get_post_type, get_post_types()) && !in_array($get_records[$eventKey]['import_file']['posttype'], $ecommerce_supported_modules)) { ?>
                  <div class="dropitems col-md-12 mt20">
                     <div class="panel-group" id='accordion'>
                        <div class='panel' data-target="#seo_fields" data-parent="#accordion">
                           <div id="seohead" class="core_info panel-heading csv-importer-heading" onclick="toggle_func('seo_fields');">
                              SEO Fields <span class="icon-circle-down pull-right" id="iconseo_fields"></span>
                           </div>
                           <div id="seo_fieldstoggle" class="widget_fields panel-body widget_open_field" >
                              <div class="droppableHolder">
                                 <?php
                                 $seo_fields = array();
                                 if(in_array('all-in-one-seo-pack/all_in_one_seo_pack.php', $uci_admin->get_active_plugins())) {
                                    $seo_fields = $uci_admin->get_widget_fields('All-in-One SEO Fields', $import_type, $importAs);
                                 } elseif(in_array('all-in-one-seo-pack-pro/all_in_one_seo_pack.php', $uci_admin->get_active_plugins())) {
                                    $seo_fields = $uci_admin->get_widget_fields('All-in-One SEO Fields', $import_type, $importAs);
                                 } elseif(in_array('wordpress-seo/wp-seo.php', $uci_admin->get_active_plugins())) {
                                    $seo_fields = $uci_admin->get_widget_fields('Yoast SEO Fields', $import_type, $importAs);
                                 } elseif(in_array('wordpress-seo-premium/wp-seo-premium.php', $uci_admin->get_active_plugins())) {
                                    $seo_fields = $uci_admin->get_widget_fields('Yoast SEO Fields', $import_type, $importAs);
                                 }
                                 if(!empty($seo_fields)) {
                                    foreach ( $seo_fields as $key => $value ) {
                                       foreach ($value as $key1 => $value1) { ?>
                                          <div class="wp_csv_ftp form-group">
                                             <input type="text" placeholder="<?php echo $value1['name']; ?>" name="<?php echo $key;?>__<?php echo $value1['name']; ?>" id="<?php echo $value1['name']; ?>" class="droppable form-control" value="<?php if(isset($template_mapping[$key][$value1['name']])) echo $template_mapping[$key][$value1['name']]; ?>">
                                          </div>
                                       <?php }
                                    }
                                 }
                                 ?>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               <?php } ?>
               <!-- This widget will be available only for the post types -->
               <?php if(in_array($get_post_type, get_post_types()) && !in_array($get_records[$eventKey]['import_file']['posttype'], $ecommerce_supported_modules)) { ?>
                  <div class="mapping_widget dropitems col-md-12">
                     <div class="panel-group" id='accordion'>
                        <div class='panel' data-target="#other_fields" data-parent="#accordion">
                           <div class="other_info panel-heading csv-importer-heading" onclick="toggle_func('other_fields');">
                              Other Information<span class="icon-circle-down pull-right" id="iconother_fields"></span>
                           </div>
                           <div id="other_fieldstoggle" class="widget_fields panel-body" id="post-data">
                              <div class="droppableHolder">
                                 <table class="table">
                                    <tr class="wp_ultimate_container">
                                       <td>
                                          <h6>Post Status</h6>
                                          <div class="form-group wp_ultimate_container">
                                             <label class="control-label col-md-12"><input checked="checked" id="post_status_published" data-key="false" class="wp_ultimate_slide" name="CORE__is_post_status" type="radio" value="publish">Published</label>
                                             <label class="control-label col-md-12"><input  name="CORE__is_post_status" id="post_status_draft" data-key="false" class="wp_ultimate_slide" type="radio" value="draft">Draft</label>
                                             <label class="control-label col-md-12"><input name="CORE__is_post_status" id="post_status_csv" data-key="true" class="wp_ultimate_slide" type="radio">Set with CSV</label>
                                             <div class="col-md-8 mt10 set_from_csv source-post_status_csv" style="display: none;">
                                                <input type="text" class="form-control droppable" name="CORE__post_status" value="<?php if(isset($template_mapping['CORE']['post_status'])) echo $template_mapping['CORE']['post_status']; ?>">
                                             </div>
                                          </div>
                                          <div class="clearfix"></div>
                                          <hr class="border-bottom-hr no-padding">
                                          </hr>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <h6>Post Date</h6>
                                          <div class="form-group">
                                             <!-- <label class="control-label col-md-12"><input checked="checked" name="post_dates"  type="radio">As specified</label> -->
                                             <!-- <label class="control-label col-md-12"><input checked="checked" name="CORE__post_date_option"  type="radio">Random dates</label>
                                             <div class="col-md-4 mt10">
                                                <input type="text" class="form-control datepicker" name="CORE__from_post_date">
                                             </div>
                                             <div class="col-md-4 mt10">
                                                <input type="text" class="form-control datepicker" name="CORE__to_post_date">
                                             </div>
                                             <label class="control-label col-md-12"><input checked="checked" name="CORE__post_date_option"  type="radio">Set with CSV</label> -->
                                             <div class="col-md-4">
                                                <input type="text" class="form-control droppable" name="CORE__post_date" value="<?php if(isset($template_mapping['CORE']['post_date'])) echo $template_mapping['CORE']['post_date']; ?>">
                                             </div>
                                          </div>
                                          <div class="clearfix"></div>
                                          <hr class="border-bottom-hr no-padding">
                                          </hr>
                                       </td>
                                    </tr>
                                    <tr class="wp_ultimate_container">
                                       <td>
                                          <h6>Comments</h6>
                                          <div class="form-group wp_ultimate_container">
                                             <label class="control-label col-md-12"><input checked="checked" id="post_comments_open" data-key="false" class="wp_ultimate_slide" name="CORE__post_comment_status" type="radio" value="open">Open</label>
                                             <label class="control-label col-md-12"><input  name="CORE__post_comment_status" id="post_comments_closed" data-key="false" class="wp_ultimate_slide" type="radio" value="closed">Closed</label>
                                             <label class="control-label col-md-12"><input name="CORE__post_comment_status" id="post_comments_csv" data-key="true" class="wp_ultimate_slide" type="radio" value="set_from_csv">Set with CSV</label>
                                             <div class="col-md-8 mt10 set_from_csv source-post_comments_csv" style="display: none;">
                                                <input type="text" class="form-control droppable" name="CORE__comment_status" value="<?php if(isset($template_mapping['CORE']['comment_status'])) echo $template_mapping['CORE']['comment_status']; ?>">
                                             </div>
                                          </div>
                                          <div class="clearfix"></div>
                                          <hr class="border-bottom-hr no-padding">
                                          </hr>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <h6>Trackbacks and Pingbacks</h6>
                                          <div class="form-group wp_ultimate_container">
                                             <label class="control-label col-md-12"><input checked="checked" id="post_comments_open" data-key="false" class="wp_ultimate_slide" name="CORE__post_ping_status" type="radio">Open</label>
                                             <label class="control-label col-md-12"><input name="CORE__post_ping_status" id="ping_status_closed" data-key="false" class="wp_ultimate_slide" type="radio">Closed</label>
                                             <label class="control-label col-md-12"><input name="CORE__post_ping_status" id="ping_status_csv" data-key="true" class="wp_ultimate_slide" type="radio">Set with CSV</label>
                                             <div class="col-md-8 mt10 set_from_csv source-ping_status_csv" style="display: none;">
                                                <input type="text" class="form-control droppable" name="CORE__ping_status" value="<?php if(isset($template_mapping['CORE']['ping_status'])) echo $template_mapping['CORE']['ping_status']; ?>">
                                             </div>
                                          </div>
                                          <div class="clearfix"></div>
                                          <hr class="border-bottom-hr no-padding">
                                          </hr>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <h6>Post Slug</h6>
                                          <div class="col-md-8 pl0">
                                             <input type="text" name="CORE__post_slug" class="form-control droppable" value="<?php if(isset($template_mapping['CORE']['post_slug'])) echo $template_mapping['CORE']['post_slug']; ?>" >
                                          </div>
                                          <div class="clearfix"></div>
                                          <hr class="border-bottom-hr no-padding">
                                          </hr>
                                       </td>
                                    </tr>
                                    <tr>
                                       <td>
                                          <h6>Post Author</h6>
                                          <div class="col-md-8 pl0">
                                             <input type="text" class="form-control droppable" name="CORE__post_author" value="<?php if(isset($template_mapping['CORE']['post_author'])) echo $template_mapping['CORE']['post_author']; ?>">
                                          </div>
                                          <div class="col-md-1"><a href="#help" class="vertical-middle" >?</a>
                                          </div>
                                          <div class="clearfix"></div>
                                          <hr class="border-bottom-hr no-padding">
                                          </hr>
                                       </td>
                                    </tr>
<!--                                    <tr>-->
<!--                                       <td>-->
<!--                                          <h6>Download & Import Attachments<span class="pull-right pr50 text-hint">Separated by</span></h6>-->
<!--                                          <div class="form-group">-->
<!--                                             <div class="col-md-9 pl0">-->
<!--                                                <input type="text" class="form-control droppable" name="CORE__featured_image" value="--><?php //echo $template_mapping['CORE']['featured_image']; ?><!--">-->
<!--                                                <label class="control-label"><input type="checkbox" name="">Search for existing attachments to prevent duplicates in media library </label>-->
<!--                                             </div>-->
<!--                                             <div class="col-md-2 pl30 pr30">-->
<!--                                                <input type="text" value="," class="form-control" name="CORE__image_separated_by">-->
<!--                                             </div>-->
<!--                                          </div>-->
<!--                                          <div class="clearfix"></div>-->
<!--                                          <hr class="border-bottom-hr no-padding">-->
<!--                                          </hr>-->
<!--                                       </td>-->
<!--                                    </tr>-->
                                    <?php if($get_records[$eventKey]['import_file']['posttype'] == 'Posts') { ?>
                                       <tr class="wp_ultimate_container">
                                          <td>
                                             <h6>Post Format</h6>
                                             <div class="form-group">
                                                <label class="control-label col-md-12 pl0"><input id="post_format_standard" data-key="false" class="wp_ultimate_slide" name="CORE__post_format_option" type="radio" checked="checked">
                                                   Standard</label>
                                                <label class="control-label col-md-12 pl0"><input id="post_format_aside" value="1" class="wp_ultimate_slide" data-key="false" name="CORE__post_format_option" type="radio">
                                                   Aside</label>
                                                <label class="control-label col-md-12 pl0"><input id="post_format_image" value="2" class="wp_ultimate_slide" data-key="false" name="CORE__post_format_option" value="" type="radio">
                                                   Image</label>
                                                <label class="control-label col-md-12 pl0"><input id="post_format_video" value="3" class="wp_ultimate_slide" data-key="false" name="CORE__post_format_option" value="" type="radio">
                                                   Video</label>
                                                <label class="control-label col-md-12 pl0"><input id="post_format_quote" value="4" class="wp_ultimate_slide" data-key="false" name="CORE__post_format_option" value="" type="radio">
                                                   Quote</label>
                                                <label class="control-label col-md-12 pl0"><input id="post_format_link" value="5" class="wp_ultimate_slide" data-key="false" name="CORE__post_format_option" value="" type="radio">
                                                   Link</label>
                                                <label class="control-label col-md-12 pl0"><input id="post_format_gallery" value="6" class="wp_ultimate_slide" data-key="false" name="CORE__post_format_option" value="" type="radio">
                                                   Gallery</label>
                                                <label class="control-label col-md-12 pl0"><input id="post_format_audio" value="7" class="wp_ultimate_slide" data-key="false" name="CORE__post_format_option" value="" type="radio">
                                                   Audio</label>
                                                <label class="control-label col-md-12 pl0"><input id="post_format_csv" class="wp_ultimate_slide" data-key="true" name="CORE__post_format_option" value="xpath" type="radio">
                                                   Set with CSV</label>
                                                <div class="set_from_csv source-post_format_csv col-md-8 pl0 mt10" style="display: none;">
                                                   <input type="text" class="form-control droppable" name="CORE__post_format" value="<?php if(isset($template_mapping['CORE']['post_format'])) echo $template_mapping['CORE']['post_format']; ?>">
                                                </div>
                                             </div>
                                             <div class="clearfix"></div>
                                             <hr class="border-bottom-hr no-padding">
                                             </hr>
                                          </td>
                                       </tr>
                                    <?php } ?>
                                    <tr>
                                       <td>
                                          <h6>Menu Order</h6>
                                          <div class="col-md-8 pl0">
                                             <input type="text" class="form-control droppable" name="CORE__menu_order" value="<?php if(isset($template_mapping['CORE']['menu_order'])) echo $template_mapping['CORE']['menu_order']; ?>">
                                          </div>
                                          <div class="clearfix"></div>
                                          <hr class="border-bottom-hr no-padding">
                                          </hr>
                                       </td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               <?php } ?>
            </div>
            <div id="mapping-sidebar">
               <div>
                  <table class="mapping-sidebar-title">
                     <tr>
                        <td class="mapping-sidebar-arrow"><i class="icon-circle-left" onclick="retrieve_record('prev', null);"></i> </td>
                        <td class="mapping-sidebar-textbox-section"><strong><input id="current_row" value="1"  type="text" onblur="retrieve_record('', this.value)"></strong> <span class="mapping-textbox-out-of"> of<strong> <?php echo $parserObj->total_row_cont; ?></strong></span>
                        </td>
                        <td class="mapping-sidebar-arrow"><i class="icon-circle-right" onclick="retrieve_record('next', null);"></i></td>
                     </tr>
                  </table>
                  <input type="hidden" id="total_no_of_records" value="<?php echo $parserObj->total_row_cont; ?>" />
                  <input type="hidden" id="event_key" value="<?php echo $eventKey; ?>">
               </div>
			   <div class="route-loader-container"></div>
               <div class="mapping-sidebar-content-section">
                  <div class="uci_mapping_attr">
                     <ul class="uci_mapping_attr_value">
                        <?php foreach($rowData[1] as $key => $value) { ?>
                           <div class="uci_mapping_csv_column">
                              <li class="uci_csv_column_header draggable" draggable="true" ondragstart="drag(event)" title="<?php echo $key; ?>" style="color: #00A699; font-weight: 600;"><?php echo $key; ?></li>
                              <li class="uci_csv_column_val" style="border-right: none;"><?php if(strlen($value) > 150) { echo substr($value, 0, 150) . "<span style='color: red;'> [more]</span>"; } else { echo $value; } ?></li>
                           </div>
                        <?php } ?>
                     </ul>
                  </div>
               </div>
            </div>
            <!--mapping section content end -->
         </div>
      </div>
      <div class="clearfix"></div>
      <div id="mapping-footer"></div>
      <div class="mb20"></div>
      <?php if(isset($_REQUEST['action']) && sanitize_title($_REQUEST['action']) == 'edit') { ?>
         <div align="center" id = 'newmappingtemplatename'><?php echo __("Save this mapping as Template"); ?>
            <input type='text' id='templatename' name = 'templatename' style='margin-left:10px; width: 25% !important;' placeholder='<?php echo $templateName; ?>' value = '<?php echo $templateName;?>'/>
         </div>
         <input type="button" class="smack-btn smack-btn-primary btn-radius mapping_continuebtn" value="<?php echo esc_attr__('Save','wp-ultimate-csv-importer');?>" style="margin-bottom:5%;" onclick="update_template('<?php echo sanitize_key($_REQUEST['eventkey']);?>');">
      <?php } else { ?>
         <div class="col-md-12 mt20">
            <!-- back btn -->
            <div class="pull-left">
               <a class="smack-btn btn-default btn-radius" href="<?php echo $backlink;?>">Back
               </a> <!-- back btn end -->
            </div>
            <!--continue btn -->
            <div class="pull-right">
               <input type="button" class="smack-btn smack-btn-primary btn-radius" value="<?php echo esc_attr__('Continue','wp-ultimate-csv-importer');?>" onclick="save_template('<?php echo $import_type;?>');">
            </div>
         </div>
         <!-- continue btn end -->
         <div class="clearfix"></div>
         <div class="mb20"></div>
      <?php }?>
      <input type="hidden" name="smack_uci_mapping_method" value="<?php echo sanitize_title($_REQUEST['mapping_type']); ?>">
   </main>
   <!-- Main End -->
</form>
<div style="font-size: 15px;text-align: center;padding-top: 20px">Powered by <a href="https://www.smackcoders.com?utm_source=wordpress&utm_medium=plugin&utm_campaign=free_csv_importer" target="blank">Smackcoders</a>.</div>
<script type="text/javascript">
   jQuery(function() {
      CKEDITOR.replace('CORE__post_content');
   });
   function allowDrop(ev) {
      ev.preventDefault();
   }

   function drag(ev) {
      var titleval = '{'+ev.target.title+'}';
      if (titleval)
         ev.dataTransfer.setData("text",titleval);
   }

   jQuery(function () {
      jQuery("#ecommerce-custom-field-add").bind("click", function () {
         var div = jQuery("<tr/>");
         div.html(GetDynamicTextBox(""));
         jQuery("#ecommerce-meta-textbox").append(div);
      });
      jQuery("body").on("click", ".remove", function () {
         jQuery(this).closest("tr").remove();
      });
   });

   function GetDynamicTextBox(value) {
      return '<td><input name="custom_meta_name[]" type="text" value="' + value + '" class="form-control" placeholder="Name"/></td>' + '<td><input name="custom_meta_value[]" type="text" value="' + value + '" class="form-control" placeholder="Value" /></td>' + '<td><span class="ecommerce-meta-trash remove"><i class="icon-trash4"></i></span></td>'
   }

   jQuery(".datepicker").each(function() {
      jQuery(this).datepicker();
      jQuery('.datepicker').datepicker('setDate', 'today');
   });

   jQuery('#smack_uci_preview').click(function () {

      var current_record = jQuery('#current_row').val();
      var eventKey = jQuery('#event_key').val();
      jQuery('#csv_row').html(current_record);
      var row_no = current_record;

      var title = jQuery('#CORE__post_title').val();
      var content = CKEDITOR.instances.CORE__post_content.getData();
      var excerpt = jQuery('#CORE__post_excerpt').val();
      var image = jQuery('#CORE__featured_image').val();

      jQuery.ajax({
         url: ajaxurl,
         type: 'post',
         data: {
            'action': 'preview_record',
            'title': title,
            'content': content,
            'excerpt': excerpt,
            'image': image,
            'row_no': parseInt(row_no),
            'event_key': eventKey,
         },
         success: function (response) {
            jQuery('.modal-body').empty();
            jQuery('.modal-body').append(response);
            jQuery('#current_row').val(row_no);
         }
      });
      jQuery('.preview_model').modal();
   });
</script>
