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

if(!defined('ABSPATH'))
	die('Your requested url were wrong! Please contact your admin.');
$group = sanitize_text_field($_REQUEST['group']);
$mapping = sanitize_text_field($_REQUEST['mappingcount']);
$buttonid = sanitize_text_field($_REQUEST['buttonid']);
if ($buttonid == $group.'_staticbutton_mapping'.$mapping) {
	$static = "<div  id='".$group."_staticdiv_mapping$mapping' style='height:auto;'><textarea rows='4' cols='15' id='".$group."_statictext_mapping$mapping' name='".$group."_statictext_mapping$mapping' style='width:100%' onblur=static_validator(this.id);></textarea>";
	$static .= "<p style='margin-top:10px;border-radius:4px;border:1px solid #ddd;padding:10px;'>". __('HINT: Specify the CSV header to be added in between the curley braces({ }).','wp-ultimate-csv-importer')."<br /> ". __('Example: {post_title}','wp-ultimate-csv-importer')."</p>";
	$static .= "<a class='smack-btn mapping-close-btn' onclick=static_formula_divclose('".$group."','".$mapping."') style='float:right;color:red;'> ". __('Close','wp-ultimate-csv-importer'). " </a>";
	$static .="</div>";
	#       $static .="<div class='black_overlay' id='".$group."_blur_mapping$mapping'></div>";
	print_r($static);
	die;
} elseif ($buttonid == $group.'_formulabutton_mapping'.$mapping)  {
	$formula = "<div id='".$group."_formuladiv_mapping$mapping' ><textarea rows='4' cols='15' id='".$group."_formulatext_mapping$mapping' name='".$group."_formulatext_mapping$mapping' style='width:100%' onblur=formula_validator(this.id);></textarea>";
	$formula .= "<p style='margin-top:10px;border-radius:4px;border:1px solid #ddd;padding:7px;'>". __('HINT: Specify operator(+,-,*,/) and CSV header as operand in MATH() within {}.Example: MATH ({product_quantity}/{discount_price})','wp-ultimate-csv-importer')."</p>";
	$formula .= "<a class='smack-btn mapping-close-btn' style='float:right;color:red' onclick=static_formula_divclose('".$group."','".$mapping."')>". __('Close','wp-ultimate-csv-importer')."</a> </div>";
	print_r($formula);
	die;
} else {

}
