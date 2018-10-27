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

class SmackUCIEventLogging {
	// declare log file and file pointer as private properties
	private $log_file, $fp;
	// set log file (path and name)
	public function lfile($path) {
		$this->log_file = $path;
	}
	// write message to the log file
	public function lwrite($message, $timestamp = true) {
		$message = strip_tags($message);
		// if file pointer doesn't exist, then open log file
		if (!is_resource($this->fp)) {
			$this->lopen();
		}
		// define script name
		$script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
		// define current time and suppress E_WARNING if using the system TZ settings
		// (don't forget to set the INI setting date.timezone)
		$time = '';
		if($timestamp == true) {
			$time = @date( '[Y-m-d H:i:s]' );
		}
		// write current time, script name and message to the log file
		#fwrite($this->fp, "$time ($script_name) $message" . PHP_EOL);
		fwrite($this->fp, "$time $message" . PHP_EOL);
	}
	// close log file (it's always a good idea to close a file when you're done with it)
	public function lclose() {
		fclose($this->fp);
	}
	// open log file (private method)
	private function lopen() {
		// in case of Windows set default log file
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$log_file_default = 'c:/php/logfile.txt';
		}
		// set default log file for Linux and other systems
		else {
			$log_file_default = '/tmp/logfile.txt';
		}
		// define log file from lfile method or use previously set default
		$lfile = $this->log_file ? $this->log_file : $log_file_default;
		// open log file for writing only and place file pointer at the end of the file
		// (if the file does not exist, try to create it)
		$this->fp = fopen($lfile, 'a') or exit("Can't open $lfile!");
	}
}
