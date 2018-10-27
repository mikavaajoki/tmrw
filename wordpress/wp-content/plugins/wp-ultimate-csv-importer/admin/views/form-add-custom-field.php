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

$prefix = sanitize_text_field($_REQUEST['prefix']);
$core_count = $mapping_count = intval($_REQUEST['count']);
$eventKey = sanitize_key($_REQUEST['eventkey']);
global $uci_admin;
$parserObj = new SmackCSVParser();
$get_records = $uci_admin->GetPostValues($eventKey);
$import_type = $get_records[$eventKey]['import_file']['posttype'];
$mode = $get_records[$eventKey]['import_file']['import_mode'];
$file = SM_UCI_IMPORT_DIR . '/' . $eventKey . '/' . $eventKey;
$parserObj->parseCSV($file, 0, -1);
$Headers = $parserObj->get_CSVheaders();
$Headers = $Headers[0];
add_customrow_for_register_field($mode,$mapping_count,$prefix,$core_count,$Headers,$import_type);


function add_customrow_for_register_field($mode,$mapping_count,$prefix,$core_count,$Headers,$import_type){
	global $uci_admin;
	$slug = sanitize_text_field($_REQUEST['slug']);
	if($prefix == 'CORECUSTFIELDS'){
		add_corecustomfields($mode, $mapping_count, $prefix, $core_count, $Headers, $slug, $import_type);
	}
	# Removed: ACF, PODS & Toolset Types field registration feature
}

function add_corecustomfields($mode, $mapping_count, $prefix, $core_count, $Headers, $slug, $import_type) {
	$result = "<tr id='".$prefix."_tr_count".$core_count."'>";
	$result .= "<td id='".$prefix."_tdg_count".$core_count."' class='left_align' style='width:20%'>";
	$result .= "<label class='wpfields' name='".$prefix."_CustomField' id='".$mapping_count."_CustomField'><input type='textbox' class='form-control new_custom_field' style='margin-top:20px;' id='".$prefix."__CustomField$mapping_count' data-key='".$prefix."__mapping$mapping_count' value='' onblur= SetWPRegisterData(this.id,'".$mapping_count."','".$prefix."')></label><br><label id = '".$prefix."CustomField$mapping_count' class='samptxt'>[Name: CustomField$mapping_count]</label>";
	$result .= " <input type='hidden' name='".$prefix."__fieldname". $mapping_count."' id='".$prefix."__fieldname".$mapping_count."' value = '' class='req_hiddenclass'/>";
	$result .= "</td>";
	$result .= "<td class='mappingtd_style' style='width:20%'>";
	$result .= "<span id='".$prefix."_mapping$mapping_count' >";
	$result .= "<div style='width:135px;height:27px;margin-bottom:10px;'><div class='mapping-select-div'><select class='selectpicker' style='height: 30px;width: 180px;margin-bottom:100px;' disabled name='".$prefix."__mapping$mapping_count'  id='".$prefix."__mapping$mapping_count'>";
	$result .= "<option id='select' value = '--select--'>-- Select --</option>";
	/** Mapping Headers **/
	foreach($Headers as $header_key => $header_value) {
		$result .= "<option value=".$header_value.">$header_value</option>";
	}
	$result .= "<option value='header_manip'>Header Manipulation</option>";
	$result .= "</select></div></div></span></td>";
	/** End Mapping Headers **/
	$result .= "<td class='mappingtd_style'>";
	$result .= "</td>";
	$result .= "<td>";
	$result .= "<div class='mapping-static-formula-group col-md-5' style='margin-left:-15px;'><span title='Static' id='".$prefix."_staticbutton_mapping$mapping_count' style='margin-right:15px;' onclick=static_method(this.id,'".$prefix."','".$mapping_count."')><img src='".plugins_url()."/".SM_UCI_SLUG."/assets/images/static.png' width='24' height='24' style='margin-right:15px;' /></span>";
	$result .= "<span title='Formula' style='margin-right:15px;' id='".$prefix."_formulabutton_mapping$mapping_count' onclick=formula_method(this.id,'".$prefix."','".$mapping_count."')><img src='".plugins_url()."/".SM_UCI_SLUG."/assets/images/formula.png' width='24' height='24' /></span></div>";
	$result .= "<div  id='".$prefix."_customdispdiv_mapping$mapping_count' class='mapping-select-close-div' style='height:246px;padding:8px;display:none;width:267px;border:3px solid #2ea2cc;margin-top:27px;position:absolute;background-color:#ffffff;z-index: 99;'></div>";
	$result .= "<input type='button' value='Delete' id='".$prefix."Delete".$mapping_count."' style='float:right; display:none;' class='btn btn-danger' onclick=DeleteCF('".$import_type."','".$prefix."',".$core_count.",\"".$slug."\")> ";
	$result .= "</td></tr>";
	print_r($result);
	die;
}
