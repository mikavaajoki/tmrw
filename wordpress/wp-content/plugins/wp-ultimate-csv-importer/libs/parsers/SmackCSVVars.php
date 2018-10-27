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

class SmackCSVVars extends SmackCSVLogger
{

    #Plugin information
    #var $version = '4.5';
    var $plugin_name = "Ultimate CSV Importer PRO";

    var $plugin_slug = "wp-ultimate-csv-importer";

    # default delimiter (comma) and default enclosure (double quote)
    var $delimiter = ',';
    var $enclosure = '"';
    var $escape = "\\";

    # number of rows to ignore from beginning of data
    var $offset = 2;

    # limits the number of returned rows to specified amount
    var $limit = 2;

    # preferred delimiter characters
    var $delimiters = array(
        ';'  => 0,
        ','  => 0,
        "\t" => 0,
        "|"  => 0,
        ":"  => 0,
    );

    # current file
    var $file;

    # loaded file contents
    var $csvfile_data;

    # current CSV header data
    var $csvfile_header;

    #Logger configuration
    var $log_file = SM_UCI_DEBUG_LOG;

    #String status - "INFO"/"DEBUG"/"ERROR"/"WARNING"/"ALL"/"NONE"
    var $log_status = "ERROR";

    #total row count
    var $total_row_cont;

    #XML string
    var $xmlstring;

    var $integrations = array();

    var $plugin_basename;

    var $plugin_location;

    var $plugin_logfile_location;

    var $uploaded_file_location;

    var $exported_file_location;

    var $zip_file_location;

    var $screenData = array();

    // @var string CSV upload directory name
    var $uploadDir = 'imports';

    // @var Export CSV directory name
    var $exportDir = 'exports';

    // @var string ZIP handle dir  directory name
    var $zipDir = 'zip_files';

    // @var string CSV Log directory name
    var $logDir = 'import_logs';

    // @var string event screen data storage location
    var $screenDataDir = 'screens_data';

    #Using WP session variable for development purposes
    #TODO:Remove this when implementing in WordPress
    var $wp_session;
}
