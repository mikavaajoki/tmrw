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
global $wp_version, $wpdb;
$ucisettings = get_option('sm_uci_pro_settings');
$ucioptimize = get_option('sm_uci_pro_optimization');
$droptable = isset($ucisettings['drop_table']) ? $ucisettings['drop_table'] : '';
$schedule_mail = isset($ucisettings['send_log_email']) ? $ucisettings['send_log_email'] : '';
$main_mode = isset($ucisettings['enable_main_mode']) ? $ucisettings['enable_main_mode'] : '';
$maintenance_text = isset($ucisettings['main_mode_text']) ? $ucisettings['main_mode_text'] : '';
$send_password = isset($ucisettings['send_user_password']) ? $ucisettings['send_user_password'] : '';
$woocomattr = isset($ucisettings['woocomattr']) ? $ucisettings['woocomattr'] : '';
$author_editor_access = isset($ucisettings['author_editor_access']) ? $ucisettings['author_editor_access'] : '';
if(!empty($droptable)){
    if($droptable == 'on'){
        $data['drop_on'] = 'enablesetting';
        $data['drop_off'] = 'disablesetting';
        $data['dropon_status'] = 'checked';
        $data['dropoff_status'] = '';
        $drop_table_status = "checked='checked'";
    } else{
        $data['drop_off'] = 'enablesetting';
        $data['drop_on'] = 'disablesetting';
        $data['dropon_status'] = '';
        $data['dropoff_status'] = 'checked';
        $drop_table_status = "";
    }
}
else
     $drop_table_status = "";

if(!empty($schedule_mail)){
    if($schedule_mail == 'on'){
        $data['mail_on'] = 'enablesetting';
        $data['mail_off'] = 'disablesetting';
        $data['mailon_status'] = 'checked';
        $data['mailoff_status'] = '';
    }else{
        $data['mail_off'] = 'enablesetting';
        $data['mail_on'] = 'disablesetting';
        $data['mailon_status'] = '';
        $data['mailoff_status'] = 'checked';
    }
}
if(!empty($main_mode)){
    if($main_mode == 'on'){
        $data['maintenance_on'] = 'enablesetting';
        $data['maintenance_off'] = 'disablesetting';
        $data['maintenance_status'] = 'checked';
        $data['maintenance_status'] = '';
        $main_mode = "checked='checked'";
        $mainmode_hide = '';
    } else {
        $data['maintenance_off'] = 'enablesetting';
        $data['maintenance_on'] = 'disablesetting';
        $data['maintenance_status'] = '';
        $data['maintenance_status'] = 'checked';
        $main_mode = "";
        $mainmode_hide = 'hidden';
    }
}
else{
    $mainmode_hide = 'hidden';
}
if(!empty($send_password)){
    if($send_password == 'on'){
        $data['mail_on'] = 'enablesetting';
        $data['mail_off'] = 'disablesetting';
        $data['mailon_status'] = 'checked';
        $data['mailoff_status'] = '';
        $send_password = "checked='checked'";
    } else {
        $data['mail_off'] = 'enablesetting';
        $data['mail_on'] = 'disablesetting';
        $data['mailon_status'] = '';
        $data['mailoff_status'] = 'checked';
        $send_password = "";
    }
}
if(!empty($woocomattr)){
    if($woocomattr == 'on'){
        $data['wooattr_on'] = 'enablesetting';
        $data['wooattr_off'] = 'disablesetting';
        $data['wooon_status'] = 'checked';
        $data['woooff_status'] = '';

    }else{
        $data['wooattr_off'] = 'enablesetting';
        $data['wooattr_on'] = 'disablesetting';
        $data['wooon_status'] = '';
        $data['woooff_status'] = 'checked';
    }
}
if(!empty($author_editor_access)){
    if($author_editor_access == 'on'){
        $data['access_on'] = 'enablesetting';
        $data['access_off'] = 'disablesetting';
        $data['accesson_status'] = 'checked';
        $data['accessoff_status'] = '';
        $author_editor_access = "checked='checked'";
    }else{
        $data['access_off'] = 'enablesetting';
        $data['access_on'] = 'disablesetting';
        $data['accesson_status'] = '';
        $data['accessoff_status'] = 'checked';
        $author_editor_access = "";
    }
}
//database optimization
if(isset($ucioptimize['delete_all_orphaned_post_page_meta'])) {
    $delete_all_post_page = $ucioptimize['delete_all_orphaned_post_page_meta'];
} else {
    $delete_all_post_page = '';
}
if(isset($ucioptimize['delete_all_unassigned_tags'])) {
    $delete_all_unassigned_tag = $ucioptimize['delete_all_unassigned_tags'];
} else {
    $delete_all_unassigned_tag = '';
}
if(isset($ucioptimize['delete_all_post_page_revisions'])) {
    $delete_all_page_revisions = $ucioptimize['delete_all_post_page_revisions'];
} else {
    $delete_all_page_revisions = '';
}
if(isset($ucioptimize['delete_all_auto_draft_post_page'])) {
    $delete_all_auto_draft_page = $ucioptimize['delete_all_auto_draft_post_page'];
} else {
    $delete_all_auto_draft_page = '';
}
if(isset($ucioptimize['delete_all_post_page_in_trash'])) {
    $delete_all_post_page_trash = $ucioptimize['delete_all_post_page_in_trash'];
} else {
    $delete_all_post_page_trash = '';
}
if(isset($ucioptimize['delete_all_spam_comments'])) {
    $delete_all_spam_comments = $ucioptimize['delete_all_spam_comments'];
} else {
    $delete_all_spam_comments = '';
}
if(isset($ucioptimize['delete_all_comments_in_trash'])) {
    $delete_all_comments_trash = $ucioptimize['delete_all_comments_in_trash'];
} else {
    $delete_all_comments_trash = '';
}
if(isset($ucioptimize['delete_all_unapproved_comments'])) {
    $delete_all_unapproved_comments = $ucioptimize['delete_all_unapproved_comments'];
} else {
    $delete_all_unapproved_comments = '';
}
if(isset($ucioptimize['delete_all_pingback_commments'])) {
    $delete_all_pingback_comments = $ucioptimize['delete_all_pingback_commments'];
} else {
    $delete_all_pingback_comments = '';
}
if(isset($ucioptimize['delete_all_trackback_comments'])) {
    $delete_all_trackback_comments = $ucioptimize['delete_all_trackback_comments'];
} else {
    $delete_all_trackback_comments = '';
} ?>
<div class="list-inline pull-right mb10 wp_ultimate_csv_importer_pro">
            <div class="col-md-6 mt10"><a href="https://goo.gl/jdPMW8" target="_blank"><?php echo esc_html__('Documentation','wp-ultimate-csv-importer');?></a></div>
            <div class="col-md-6 mt10"><a href="https://goo.gl/fKvDxH" target="_blank"><?php echo esc_html__('Sample CSV','wp-ultimate-csv-importer');?></a></div>
         </div>
<div class="whole_body wp_ultimate_csv_importer_pro" style="margin-top: 40px;">
    <form id="form_import_file">
        <div class="import_holder" id="import_holder" >
            <div class="panel " style="width: 99%;">
                <div id="warningsec" style="color:red;width:100%; min-height: 110px;border: 1px solid #d1d1d1;background-color:#fff;display:none;">
                    <div id ="warning" class="display-warning" style="color:red;align:center;display:inline;font-weight:bold;font-size:15px; border: 1px solid red;margin:2% 2%;padding: 20px 0 20px;position: absolute;text-align: center;width:93%;display:none;"> </div>
                </div>
                <div class="panel-body no-padding">
                    <div style="height:300px;" class="col-md-3 setting-manager-list no-padding" id="left_sidebar">
                        <ul id="example">
                            <li id='1' class="bg-leftside selected right-arrow" onclick="settings_div_selection(this.id);">
                                <span class=" icon-settings2"></span>
                                <span><?php echo esc_html__('General Settings','wp-ultimate-csv-importer');?></span>
                            </li>
                            <li id='2' class="bg-leftside" onclick="settings_div_selection(this.id);">
                                <span class="icon-database" style="margin-top: -10px;"></span>
                                <span><?php echo esc_html__('Database optimization','wp-ultimate-csv-importer');?></span>
                            </li>
                            <li id='3' class="bg-leftside" onclick="settings_div_selection(this.id);">
                                <span class="icon-lock4" style="margin-top: -10px;"></span>
                                <span><?php echo esc_html__('Security and Performance','wp-ultimate-csv-importer');?></span>
                            </li>
                            <li id='4'  class="bg-leftside" onclick="settings_div_selection(this.id);">
                                <span class="icon-document-movie2" style="font-size: 1.4em; margin-top: -10px;"></span>
                                <span><?php echo esc_html__('Documentation','wp-ultimate-csv-importer');?></span>
                            </li>
                        </ul>
                    </div>
                    <div  class="col-md-9" id="rightside_content">
                        <div id="division1">
                            <h3 class="csv-importer-heading"><?php echo esc_html_e('General Settings','wp-ultimate-csv-importer'); ?></h3>
                            <div class="col-md-11 col-md-offset-1 mt20 mb40">
                                <div class="form-group">
                                    <div class="col-xs-12 col-sm-8 col-md-8  nopadding">
                                        <h4 ><?php echo esc_html_e('Drop Table','wp-ultimate-csv-importer'); ?></h4>
                                        <p><?php echo esc_html_e('If enabled plugin deactivation will remove plugin data, this cannot be restored.','wp-ultimate-csv-importer'); ?></p>
                                    </div>
                                    <div class="col-xs-12 col-sm-4 col-md-3">
                                        <div class="mt20">
                                            <!-- Drop Table button -->
                                            <input id="drop_table" type='checkbox' class="tgl tgl-skewed noicheck" name='drop_table' style="display:none;" <?php echo $drop_table_status; ?> onclick="saveoptions(this.id, this.name);" />
                                            <label data-tg-off="NO" data-tg-on="YES" for="drop_table" id="download_on" class="tgl-btn" style="font-size: 16px;" >
                                            </label>
                                            <!-- Drop Table btn End -->
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                                <div class="form-group mt20">
                                    <div class="col-xs-12 col-sm-8 col-md-8  nopadding">
                                        <h4><?php echo esc_html_e('Scheduled log mails','wp-ultimate-csv-importer'); ?></h4>
                                        <p><?php echo esc_html_e('Enable to get scheduled log mails.','wp-ultimate-csv-importer'); ?></p>
                                    </div>
                                    <div class="col-xs-12 col-sm-4 col-md-3">
                                        <div class="mt20">
                                            <!-- Scheduled log button -->
                                            <input id="send_log_email" type='checkbox' class="tgl tgl-skewed noicheck" name='send_log_email' style="display:none" onclick="pro_feature();" />
                                            <label data-tg-off="NO" data-tg-on="YES" for="send_log_email" id="download_on" class="tgl-btn" style="font-size: 16px;" >
                                            </label>
                                            <!-- Scheduled log btn End -->
                                        </div>
                                    </div>
                                </div>
                                <!-- <div class="form-group mt20">
                                    <div class="col-xs-12 col-sm-8 col-md-8  nopadding">
                                        <h4><?php //echo esc_html_e('Maintenance mode','wp-ultimate-csv-importer-pro'); ?></h4>
                                        <p><?php //echo esc_html_e('Enable to maintain your Wordpress site.','wp-ultimate-csv-importer-pro'); ?></p>
                                    </div>
                                    <div class="col-xs-12 col-sm-4 col-md-3">
                                        <div class="mt20">
                                            <input id="enable_main_mode" type='checkbox' class="tgl tgl-skewed noicheck" name='enable_main_mode' <?php //echo $main_mode; ?> style="display:none" onclick="saveoptions(this.id, this.name);" />
                                            <label data-tg-off="NO" data-tg-on="YES" for="enable_main_mode" id="download_on" class="tgl-btn" style="font-size: 16px;" >
                                            </label>
                                         </div>
                                    </div>
                                </div>
                                 <div class="clearfix"></div>
                                <div class="form-group mt20" <?php //echo $mainmode_hide; ?> >
                                    <div class="col-xs-12 col-sm-12 col-md-10  nopadding">
                                    <input type="text" id='main_mode_text' class="form-control" name = 'main_mode_text'  placeholder = 'Site is under maintenance mode. Please wait few min!' value='<?php //echo $maintenance_text;?>' onblur="saveoptions(this.id, this.name);" >
                                    </div>
                                    </div> -->
                                    <div class="clearfix"></div>
                                <div class="form-group mt20">
                                    <div class="col-xs-12 col-sm-8 col-md-8  nopadding">
                                        <h4><?php echo esc_html_e('Send password to user','wp-ultimate-csv-importer'); ?></h4>
                                        <p><?php echo esc_html_e('Enable to send password information through email.','wp-ultimate-csv-importer'); ?></p>
                                    </div>
                                    <div class="col-xs-12 col-sm-4 col-md-3">
                                        <div class="mt20">
                                            <!-- Scheduled log button -->
                                            <input id="send_user_password" type='checkbox' class="tgl tgl-skewed noicheck" name='send_user_password' <?php echo $send_password; ?> style="display:none" onclick="saveoptions(this.id, this.name);" />
                                            <label data-tg-off="NO" data-tg-on="YES" for="send_user_password" id="download_on" class="tgl-btn" style="font-size: 16px;" >
                                            </label>
                                            <!-- Scheduled log btn End -->
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                                <div class="form-group mt20">
                                    <div class="col-xs-12 col-sm-8 col-md-8  nopadding">
                                        <h4 ><?php echo esc_html_e('Woocommerce Custom attribute','wp-ultimate-csv-importer'); ?></h4>
                                        <p><?php echo esc_html_e('Enables to register woocommrce custom attribute.','wp-ultimate-csv-importer'); ?></p>
                                    </div>
                                    <div class="col-xs-12 col-sm-4 col-md-3 mb20">
                                        <div class="mt20">
                                            <!-- Scheduled log button -->
                                            <input id="woocomattr" type='checkbox' class="tgl tgl-skewed noicheck" name='woocomattr' style="display:none" onclick="pro_feature();" />
                                            <label data-tg-off="NO" data-tg-on="YES" for="woocomattr" id="download_on" class="tgl-btn" style="font-size: 16px;" >
                                            </label>
                                            <!-- Scheduled log btn End -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="division2" style="display:none;">
                            <h3 class="csv-importer-heading"><?php echo esc_html_e('Database Optimization','wp-ultimate-csv-importer'); ?></h3>
                            <div class="col-md-12 mt30 ">
                                <div class="bhoechie-tab-content active" id="division5" style="width: 100%;text-align: center;margin-top: 10%;font-size: 2.2em;color: red;">
                                    <?php echo esc_html__('This feature is only available in PRO','wp-ultimate-csv-importer');?>
                                </div>
                            </div>
                        </div>
                        <div id="division3" style="display:none;">
                            <h3 class="csv-importer-heading">
                                <?php echo esc_html_e('Security and Performance','wp-ultimate-csv-importer'); ?>
                            </h3>
                            <div style="margin-left: 50px; margin-top: 20px;">
                                <!-- Allow/author-editor import start-->
                                <table class="securityfeatures" style="width: 100%">
                                    <tr>
                                        <td>
                                            <h4><?php echo esc_html_e('Allow authors/editors to import','wp-ultimate-csv-importer'); ?></h4>
                                            <p><?php echo esc_html_e('This enables authors/editors to import.','wp-ultimate-csv-importer'); ?></p>
                                        </td>
                                        <td id='divtd'>
                                            <div class="col-xs-12 col-sm-4 col-md-8 mb15">
                                                <div class="mt20">
                                                    <!-- Scheduled log button -->

                                                    <input id="author_editor_access" type='checkbox' class="tgl tgl-skewed noicheck" name='author_editor_access' <?php echo $author_editor_access; ?>  style="display:none" onclick="saveoptions(this.id, this.name);" />
                                                    <label data-tg-off="NO" data-tg-on="YES" for="author_editor_access" id="enableimport" class="tgl-btn" style="font-size: 16px;" >
                                                    </label>
                                                    <!-- Scheduled log btn End -->
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                <!-- Allow/author-editor import end-->
                                <!-- Max/Min required start-->
                                <table class="table table-striped">
                                    <tr>
                                        <th colspan="3" >
                                            <h4 class="text-danger" ><?php echo esc_html_e('Minimum required php.ini values (Ini configured values)','wp-ultimate-csv-importer'); ?></h4 >
                                        </th>
                                    </tr>
                                    <tr>
                                        <th>
                                            <label><?php echo esc_html_e('Variables','wp-ultimate-csv-importer'); ?></label>
                                        </th>
                                        <th class='ini-configured-values'>
                                            <label><?php echo esc_html_e('System values','wp-ultimate-csv-importer'); ?></label>
                                        </th>
                                        <th class='min-requirement-values'>
                                            <label><?php echo esc_html_e('Minimum Requirements','wp-ultimate-csv-importer'); ?></label>
                                        </th>
                                    </tr>
                                    <tr>
                                        <td><?php echo esc_html_e('post_max_size','wp-ultimate-csv-importer'); ?> </td>
                                        <td class='ini-configured-values'><?php echo ini_get('post_max_size') ?></td>
                                        <td class='min-requirement-values'>10M</td>
                                    </tr>
                                    <tr>
                                        <td><?php echo esc_html_e('auto_append_file','wp-ultimate-csv-importer'); ?></td>
                                        <td class='ini-configured-values'>- <?php echo ini_get('auto_append_file') ?></td>
                                        <td class='min-requirement-values'>-</td>
                                    </tr>
                                    <tr>
                                        <td><?php echo esc_html_e('auto_prepend_file','wp-ultimate-csv-importer'); ?> </td>
                                        <td class='ini-configured-values'>- <?php echo ini_get('auto_prepend_file') ?></td>
                                        <td class='min-requirement-values'>-</td>
                                    </tr>
                                    <tr>
                                        <td><?php echo esc_html_e('upload_max_filesize','wp-ultimate-csv-importer'); ?> </td>
                                        <td class='ini-configured-values'><?php echo ini_get('upload_max_filesize') ?></td>
                                        <td class='min-requirement-values'>2M</td>
                                    </tr>
                                    <tr>
                                        <td><?php echo esc_html_e('file_uploads','wp-ultimate-csv-importer'); ?> </td>
                                        <td class='ini-configured-values'><?php echo ini_get('file_uploads') ?></td>
                                        <td class='min-requirement-values'>1</td>
                                    </tr>
                                    <tr>
                                        <td><?php echo esc_html_e('allow_url_fopen','wp-ultimate-csv-importer'); ?> </td>
                                        <td class='ini-configured-values'><?php echo ini_get('allow_url_fopen') ?></td>
                                        <td class='min-requirement-values'>1</td>
                                    </tr>
                                    <tr>
                                        <td><?php echo esc_html_e('max_execution_time','wp-ultimate-csv-importer'); ?> </td>
                                        <td class='ini-configured-values'><?php echo ini_get('max_execution_time') ?></td>
                                        <td class='min-requirement-values'>3000</td>
                                    </tr>
                                    <tr>
                                        <td><?php echo esc_html_e('max_input_time','wp-ultimate-csv-importer'); ?> </td>
                                        <td class='ini-configured-values'><?php echo ini_get('max_input_time') ?></td>
                                        <td class='min-requirement-values'>3000</td>
                                    </tr>
                                    <tr>
                                        <td><?php echo esc_html_e('max_input_vars','wp-ultimate-csv-importer'); ?> </td>
                                        <td class='ini-configured-values'><?php echo ini_get('max_input_vars') ?></td>
                                        <td class='min-requirement-values'>3000</td>
                                    </tr>
                                    <tr>
                                        <td><?php echo esc_html_e('memory_limit','wp-ultimate-csv-importer'); ?> </td>
                                        <td class='ini-configured-values'><?php echo ini_get('memory_limit') ?></td>
                                        <td class='min-requirement-values'>99M</td>
                                    </tr>
                                </table>
                                <!-- Max/Min required end-->
                                <!-- Extension modules start-->
                                <h3 class="divinnertitle" colspan="2" ><?php echo esc_html_e('Required to enable/disable Loaders, Extentions and modules:','wp-ultimate-csv-importer'); ?></h3>
                                <table class="table table-striped">
                                    <?php $loaders_extensions = get_loaded_extensions();?>
                                    <?php if(function_exists('apache_get_modules')){
                                        $mod_security = apache_get_modules();
                                    } ?>
                                    <tr>
                                        <td><?php echo esc_html_e('PDO','wp-ultimate-csv-importer'); ?> </td>
                                        <td><?php if(in_array('PDO', $loaders_extensions)) {
                                                echo '<label style="color:green;">';echo __('Yes','wp-ultimate-csv-importer'); echo '</label>';
                                            } else {
                                                echo '<label style="color:red;">';echo __('No','wp-ultimate-csv-importer'); echo '</label>';
                                            } ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo esc_html_e('Curl','wp-ultimate-csv-importer'); ?> </td>
                                        <td><?php if(in_array('curl', $loaders_extensions)) {
                                                echo '<label style="color:green;">';echo __('Yes','wp-ultimate-csv-importer'); echo '</label>';
                                            } else {
                                                echo '<label style="color:red;">';echo __('No','wp-ultimate-csv-importer'); echo '</label>';
                                            } ?></td>
                                        <td></td>
                                    </tr>
                                <?php if(defined('DISABLE_WP_CRON') && DISABLE_WP_CRON == true) { ?>    
				<tr>
                                        <td>echo esc_html_e('WP CRON','wp-ultimate-csv-importer'); ?> </td>
                                        <td><?php echo '<label style="color:green;">'; echo __('Disabled','wp-ultimate-csv-importer'); echo '</label>';
					    ?></td>
                                     <tr>
				<?php } ?>
                                </table>
                                <!-- Extension modules end-->
                                <!-- Debug info start-->
                                <h3 class="divinnertitle" colspan="2" ><?php echo esc_html_e('Debug Information:','wp-ultimate-csv-importer'); ?></h3>
                                <table class="table table-striped">
                                    <tr>
                                        <td class='debug-info-name'><?php echo esc_html_e('WordPress Version','wp-ultimate-csv-importer'); ?></td>
                                        <td><?php echo $wp_version; ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class='debug-info-name'><?php echo esc_html_e('PHP Version','wp-ultimate-csv-importer'); ?></td>
                                        <td><?php echo phpversion(); ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class='debug-info-name'><?php echo esc_html_e('MySQL Version','wp-ultimate-csv-importer'); ?></td>
                                        <td><?php echo $wpdb->db_version(); ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class='debug-info-name'><?php echo esc_html_e('Server SoftWare','wp-ultimate-csv-importer'); ?></td>
                                        <td><?php echo $_SERVER[ 'SERVER_SOFTWARE' ]; ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class='debug-info-name'><?php echo esc_html_e('Your User Agent','wp-ultimate-csv-importer'); ?></td>
                                        <td><?php echo $_SERVER['HTTP_USER_AGENT']; ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class='debug-info-name'><?php echo esc_html_e('WPDB Prefix','wp-ultimate-csv-importer'); ?></td>
                                        <td><?php echo $wpdb->prefix; ?></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class='debug-info-name'><?php echo esc_html_e('WP Multisite Mode','wp-ultimate-csv-importer'); ?></td>
                                        <td><?php if ( is_multisite() ) { echo '<label style="color:green;">'; __('Enabled','wp-ultimate-csv-importer'); echo '</label>'; } else { echo '<label style="color:red;">'; __('Disabled','wp-ultimate-csv-importer');echo '</label>'; } ?> </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td class='debug-info-name'><?php echo esc_html_e('WP Memory Limit','wp-ultimate-csv-importer'); ?></td>
                                        <td><?php echo (int) ini_get('memory_limit'); ?></td>
                                        <td></td>
                                    </tr>
                                </table>
                                <!-- Debug info end-->
                                <div class="clearfix"></div>
                                <div class="mb20"></div>

                            </div>

                        </div>
                        <div id="division4" style="display:none;">
                            <div class="divtitle">
                                <h3><?php echo esc_html_e('Documentation','wp-ultimate-csv-importer'); ?></h3>
                            </div>
                            <div id ='divdata'>
                                <div id="video">
                                    <iframe width="560" height="315" src="https://www.youtube.com/embed/GbDlQcbnNJY"  frameborder="0" allowfullscreen></iframe>
                                </div>
                                <div id="relatedpages">
                                    <h2 id="doctitle"><?php echo esc_html_e('Smackcoders Guidelines','wp-ultimate-csv-importer'); ?> </h2 >
                                    <p> <a href=" https://goo.gl/gbS3fs" target="_blank"> <?php echo __('24 hours FREE Pro Trial','wp-ultimate-csv-importer'); ?> </a> </p>
				    <p> <a href="https://goo.gl/wy5OCm" target="_blank"> <?php echo __('Live Demo','wp-ultimate-csv-importer'); ?> </a> </p>
				    <p> <a href="https://goo.gl/KSIEhI" target="_blank"> <?php echo __('Development News','wp-ultimate-csv-importer'); ?> </a> </p>
                                    <p> <a href="https://goo.gl/gbS3fs" target="_blank"><?php echo __('Whats New?','wp-ultimate-csv-importer'); ?> </a> </p>
                                    <p> <a href="https://goo.gl/jdPMW8" target="_blank"><?php echo __(' Documentation','wp-ultimate-csv-importer'); ?> </a> </p>
                                    <p> <a href="https://goo.gl/RzUvqS" target="_blank"> <?php echo __('Youtube Channel','wp-ultimate-csv-importer'); ?> </a> </p>
                                    <p> <a href="https://goo.gl/tcDgx4" target="_blank"><?php echo __(' Other WordPress Plugins','wp-ultimate-csv-importer'); ?> </a> </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </form>
</div>
<div style="font-size: 15px;text-align: center;padding-top: 20px">Powered by <a href="https://www.smackcoders.com?utm_source=wordpress&utm_medium=plugin&utm_campaign=free_csv_importer" target="blank">Smackcoders</a>.</div>
<script>
    jQuery(function () {
        //getting click event to show modal
        jQuery('#database_optimization').click(function () {
            jQuery('.myModals').modal();
        });
    });
</script>
