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
#Removed: Helper class inclusion for managers.
    $active_plugins = get_option( "active_plugins" );
?>
<div class="list-inline pull-right mb10 wp_ultimate_csv_importer_pro">
            <div class="col-md-6 mt10"><a href="https://goo.gl/jdPMW8" target="_blank"><?php echo esc_html__('Documentation','wp-ultimste-csv-importer');?></a></div>
            <div class="col-md-6 mt10"><a href="https://goo.gl/fKvDxH" target="_blank"><?php echo esc_html__('Sample CSV','wp-ultimste-csv-importer');?></a></div>
         </div>
<div class="whole_body wp_ultimate_csv_importer_pro" style="margin-top: 40px">
    <form>
        <div class="import_holder" id="import_holder" >
            <div class="panel " style="width: 99%;">
            <div class="panel-body no-padding">
            <div class="col-md-3 file-manager-list no-padding" id="manager_left_sidebar">
                               <ul id="example">
                    <li id='1' class="bg-leftside selected">
                        <a style="font-size: 17px;" href="<?php echo esc_url(add_query_arg(array('page' => 'sm-uci-managers','step' => 'filemanager')));?>"><span class="icon-copy-file"></span><?php echo esc_html__('File Manager','wp-ultimate-csv-importer');?></a>
                    </li>
                    <?php  if(in_array('wp-ultimate-exporter/index.php', $active_plugins)) { ?>
                    <li id='2'  class="bg-leftside">
                        <a style="font-size: 17px;" href="<?php echo esc_url(add_query_arg(array('page' => 'sm-uci-managers','step' => 'schedulemanager')));?>"><span class="icon-calendar3"></span><?php echo esc_html__('Smart Schedule','wp-ultimate-csv-importer');?></a>
                    </li>
                    <?php } ?>
                    <li id='3' class="bg-leftside">
                        <a style="font-size: 17px;" href="<?php echo esc_url(add_query_arg(array('page' => 'sm-uci-managers','step' => 'templatemanager')));?>"><span class="icon-insert-template"></span><?php echo esc_html__('Templates','wp-ultimate-csv-importer');?></a></li>
                    <!--<li id='4'  class="bg-leftside notselect">
                        <span class="header-icon glyphicon glyphicon-pushpin"></span>
                        <a href="<?php echo esc_url(add_query_arg(array('page' => 'sm-uci-managers','step' => 'shortcodemanager')));?>">Image Shortcodes</a></li>-->
                    <li  id="5" class="bg-leftside">
                        <a style="font-size: 17px;" href="<?php echo esc_url(add_query_arg(array('page' => 'sm-uci-managers','step' => 'logmanager')));?>"><span class="icon-document-diagrams"></span><?php echo esc_html__('Log Manager','wp-ultimate-csv-importer');?></a></li>
                    </li>
                </ul>
            </div>
            <!-- <div class="rightside_content" id="manager_rightside_content"> -->
            <div class="col-md-9" id="manager_rightside_content">
                <?php $manager_type = isset($_REQUEST['step']) ? sanitize_title($_REQUEST['step']) : '';?>
         <!--       <script>
                change_css(<?php //echo $manager_type;?>);
                </script>-->
                <?php
                switch($manager_type){
                    case 'filemanager':
                        include (SM_UCI_PRO_DIR . 'admin/views/form-file-manager.php' );
                        break;
                    case 'schedulemanager':
                        include (SM_UCI_PRO_DIR . 'admin/views/form-schedule-manager.php');
                        break;
                    case 'templatemanager':
                        include (SM_UCI_PRO_DIR . 'admin/views/form-template-manager.php');
                        break;
                    case 'shortcodemanager':
                        include(SM_UCI_PRO_DIR . 'admin/views/form-shortcode-manager.php');
                        break;
                    case 'logmanager':
                        include(SM_UCI_PRO_DIR . 'admin/views/form-log-manager.php');
                        //require_once(SM_UCI_PRO_DIR . 'managers/class-uci-logmanager.php');
                        break;
                    default: {
                        include(SM_UCI_PRO_DIR . 'admin/views/form-log-manager.php');
                        break;
                    }
                }
                ?>
            </div>
            </div>
            </div>
        </div>
    </form>
</div>
<script>
    jQuery(function () {
        jQuery(".selected").addClass("right-arrow");
    });
</script>

<div style="font-size: 15px;text-align: center;padding-top: 20px">Powered by <a href="https://www.smackcoders.com?utm_source=wordpress&utm_medium=plugin&utm_campaign=free_csv_importer" target="blank">Smackcoders</a>.</div>

