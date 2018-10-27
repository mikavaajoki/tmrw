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
<div align="center">
    <div class = "title">
        <h3>Shortcodes Data</h3>
    </div>
    <div>
        <table class="manager_table">
            <tr style="width:100px">
                <th width="10%"><input type="checkbox" id="selectAllid" name="selectAllid"></th>
                <th width="48%"><h3 id="row-title">File Info</h3></th>
                <th width="26%"><h3 id="row-title">Image Info</h3></th>
                <th><h3 id="row-title">Status</h3></th>
            </tr>
        </table>
        <hr />
    </div>
    <div style="height:450px;overflow-y:scroll;">
        <table class="manager_table">
            <?php for($i = 0;$i<10;$i++){?>
                <tbody onmouseover = "show_fileEvents(<?php echo $i;?>);" onmouseout="hide_fileEvents(<?php echo $i;?>);">
                <tr>
                    <td><input type="checkbox" name = "selectAllid" id="selectAllid" /></td>
                    <td class="schedule-name">File Name</td>
                    <td>:</td>
                    <td class = "schedule-filename">post_csv.csv</td>
                    <td width = "16%">Shortcode Mode</td>
                    <td>:</td>
                    <td>Inline</td>
                    <td>Replaced</td>
                </tr>
                <tr>
                    <td></td>
                    <td>Event Key</td>
                    <td>:</td>
                    <td>ddr4553g8992</td>
                    <td>Module</td>
                    <td>:</td>
                    <td colspan="2">Post</td>
                </tr>
                <tr>
                    <td></td>
                    <td>Revision</td>
                    <td>:</td>
                    <td>1</td>
                    <td>No.of.shortcodes</td>
                    <td>:</td>
                    <td colspan="2">5</td>
                </tr>
                <tr id = "file_events<?php echo $i;?>" class="row-links">
                    <td></td>
                    <td colspan="6">
                        Populate |
                        Update
                    </td>
                </tr>
                <tr>
                    <td colspan="8"><hr /></td>
                </tr>
                </tbody>
            <?php }?>
        </table>
    </div>
    </div>
<script>
    jQuery(document).ready(function () {
        var i;
        jQuery('#4').addClass("selected");
        jQuery('#4').removeClass("bg-leftside");
        for(i=1;i<=5;i++) {
            if(i == 4)
                continue;
            jQuery('#'+i).addClass("bg-leftside");
            jQuery('#'+i).removeClass("selected");
        }
    });
</script>
