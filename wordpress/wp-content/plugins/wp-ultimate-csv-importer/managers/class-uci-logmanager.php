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


class SmackUCILogManager
{
    /**
     * @param $fileid
     */
    public function logDownload($fileid){
        global $wpdb;
        global $fileObj;
        $pathList = array();
        $recordDetails = $wpdb->get_results($wpdb->prepare("select *from smackuci_events where id = %d", $fileid['id']));
        foreach($recordDetails as $record_data) {
            $filepath = maybe_unserialize($record_data->revision);
            foreach($filepath as $path) {
                $key_data = explode('/',$path);
                $key_data = $key_data[count($key_data) - 1];
                $eventkey = str_replace('.txt','',$key_data);
                $log_path = SM_UCI_LOG_DIR . $eventkey;
                if(file_exists($log_path)) {
                    $pathList[] = $log_path;
                }
            }
        }
        $zip_path = SM_UCI_LOG_DIR . $recordDetails[0]->filename . 'zip';
        if(file_exists($zip_path)) :
            unlink($zip_path);
        endif;
        $zipped_file = $fileObj->create_zip($pathList, $zip_path , false);
        if($zipped_file) :
            header("Content-type: application/zip");
            header("Content-Disposition: attachment; filename=" . basename($zip_path));
            header("Pragma: no-cache");
            header("Expires: 0");
            readfile($zip_path);
            die;
        else :
            $msg['notice'] =  "File Not Exists";
	        print_r(json_encode($msg));
            die;
        endif;
    }

}
global $log_managerObj;
$log_managerObj = new SmackUCILogManager();
