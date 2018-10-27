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

include_once ( plugin_dir_path(__FILE__) . '../includes/class-uci-helper.php' );

class SmackUCIAdmin extends SmackUCIHelper {

	public function __construct() {
		self::initializing_scheduler();
	}

	public static function show_admin_menus() {
		$is_author_can_import = get_option('sm_uci_pro_settings', null);
		$is_author_can_import = isset($is_author_can_import['author_editor_access']) ? $is_author_can_import['author_editor_access'] : '';
		if ( apply_filters( 'sm_uci_enable_setup_wizard', true ) && is_user_logged_in() &&  current_user_can( 'administrator' ) ) {
			add_action( 'admin_menu', array( __CLASS__, 'admin_menus' ) );
		}
		if ( is_user_logged_in() && ( current_user_can( 'author') || current_user_can('editor') ) && $is_author_can_import == 'on' ) {
			add_action( 'admin_menu', array( __CLASS__, 'admin_menus_for_other_roles' ) );
		}
	}

	/**
	 * Add admin menus/screens.
	 */
	public static function admin_menus() {
		global $submenu;
		$active_plugins = get_option( "active_plugins" );
		add_menu_page(SM_UCI_SETTINGS, SM_UCI_NAME, 'manage_options', SM_UCI_SLUG, array(__CLASS__, 'sm_uci_screens'),plugins_url("assets/images/wp-ultimate-csv-importer.png",dirname(__FILE__)));
		add_submenu_page(SM_UCI_SLUG, SM_UCI_NAME, esc_html__('Dashboard', 'wp-ultimate-csv-importer') , 'manage_options', 'sm-uci-dashboard', array(__CLASS__, 'sm_uci_screens'));
		add_submenu_page(SM_UCI_SLUG, SM_UCI_NAME, esc_html__('Import / Update','wp-ultimate-csv-importer'), 'manage_options', 'sm-uci-import', array(__CLASS__, 'sm_uci_screens'));
		add_submenu_page(SM_UCI_SLUG, SM_UCI_NAME, esc_html__('Managers','wp-ultimate-csv-importer'), 'manage_options', 'sm-uci-managers', array(__CLASS__, 'sm_uci_screens'));
		if(in_array('wp-ultimate-exporter/index.php', $active_plugins)){
		add_submenu_page(SM_UCI_SLUG, SM_UCI_NAME, esc_html__('Export','wp-ultimate-csv-importer'), 'manage_options', 'sm-uci-export', array(__CLASS__, 'sm_uci_screens'));
		}
		add_submenu_page(SM_UCI_SLUG, SM_UCI_NAME, esc_html__('Settings','wp-ultimate-csv-importer'), 'manage_options', 'sm-uci-settings', array(__CLASS__, 'sm_uci_screens'));
		add_submenu_page(SM_UCI_SLUG, SM_UCI_NAME, esc_html__('Addons','wp-ultimate-csv-importer'), 'manage_options', 'sm-uci-addons', array(__CLASS__, 'sm_uci_screens'));
		add_submenu_page(SM_UCI_SLUG, SM_UCI_NAME, esc_html__('Support','wp-ultimate-csv-importer'), 'manage_options', 'sm-uci-support', array(__CLASS__, 'sm_uci_screens'));
		unset($submenu[SM_UCI_SLUG][0]);
	}

	public static function admin_menus_for_other_roles() {
		global $submenu;
		$active_plugins = get_option( "active_plugins" );
		add_menu_page(SM_UCI_SETTINGS, SM_UCI_NAME, '2', SM_UCI_SLUG, array(__CLASS__, 'sm_uci_screens'),plugins_url("assets/images/wp-ultimate-csv-importer.png",dirname(__FILE__)));
		add_submenu_page(SM_UCI_SLUG, SM_UCI_NAME, 'Dashboard', '2', 'sm-uci-dashboard', array(__CLASS__, 'sm_uci_screens'));
		add_submenu_page(SM_UCI_SLUG, SM_UCI_NAME, 'Import / Update', '2', 'sm-uci-import', array(__CLASS__, 'sm_uci_screens'));
		add_submenu_page(SM_UCI_SLUG, SM_UCI_NAME, 'Managers', '2', 'sm-uci-managers', array(__CLASS__, 'sm_uci_screens'));
		if(in_array('wp-ultimate-exporter/index.php', $active_plugins)){
		add_submenu_page(SM_UCI_SLUG, SM_UCI_NAME, 'Export', '2', 'sm-uci-export', array(__CLASS__, 'sm_uci_screens'));
		}
		add_submenu_page(SM_UCI_SLUG, SM_UCI_NAME, 'Addons', '2', 'sm-uci-addons', array(__CLASS__, 'sm_uci_screens'));
		add_submenu_page(SM_UCI_SLUG, SM_UCI_NAME, 'Support', '2', 'sm-uci-support', array(__CLASS__, 'sm_uci_screens'));
		unset($submenu[SM_UCI_SLUG][0]);
	}

	public static function sm_uci_screens() {
		global $uci_admin;
		$uci_admin->show_top_navigation_menus();
		switch (sanitize_title($_REQUEST['page'])) {
			case 'sm-uci-dashboard':
				$uci_admin->show_uci_dashboard();
				break;
			case 'sm-uci-import':
				$uci_admin->show_import_screen();
				break;
			case 'sm-uci-managers':
				$uci_admin->show_manager_screen();
				break;
			case 'sm-uci-export':
				$uci_admin->show_export_screen();
				break;
			case 'sm-uci-settings':
				$uci_admin->show_settings_screen();
				break;
			case 'sm-uci-support':
				$uci_admin->show_support_screen();
				break;
			case 'sm-uci-addons':
				$uci_admin->show_addon_screen();
			break;
			default:
				break;
		}
		return false;
	}

	public function show_top_navigation_menus() {
		$active_plugins = get_option( "active_plugins" );

		$menu = '<div class="menu_bar wp_ultimate_csv_importer_pro">
                <h2 class="nav-tab-wrapper">
                        <a href="'. esc_url (admin_url() .'admin.php?page=sm-uci-dashboard') .'" class="nav-tab nav-tab-active" id = "menu1">'.esc_html__('Dashboard','wp-ultimate-csv-importer').'</a>
                        <a href="'. esc_url (admin_url() .'admin.php?page=sm-uci-import') . '" class="nav-tab" id = "menu2">'.esc_html__('Import / Update','wp-ultimate-csv-importer').'</a>
                        <a href="'. esc_url (admin_url() .'admin.php?page=sm-uci-managers') . '" class="nav-tab" id = "menu3">'.esc_html__('Manager','wp-ultimate-csv-importer').'</a>';
        if(in_array('wp-ultimate-exporter/index.php', $active_plugins))
        $menu .= '<a href="'. esc_url (admin_url() .'admin.php?page=sm-uci-export') . '" class="nav-tab" id = "menu4">'.esc_html__('Export','wp-ultimate-csv-importer').'</a>';
        $menu .= '<a href="'. esc_url (admin_url() .'admin.php?page=sm-uci-settings') . '" class="nav-tab" id = "menu5">'.esc_html__('Settings','wp-ultimate-csv-importer').'</a>
        <a href="'. esc_url (admin_url() .'admin.php?page=sm-uci-addons') . '" class="nav-tab" id = "menu6">'.esc_html__('Addons','wp-ultimate-csv-importer').'</a>
                        <a href="'. esc_url (admin_url() .'admin.php?page=sm-uci-support') . '" class="nav-tab" id = "menu7">'.esc_html__('Support','wp-ultimate-csv-importer').'</a>
                </h2>
		        </div>
		<div id="notification_wp_csv"></div>';
		echo $menu;
		$myDir = SM_UCI_DEFAULT_UPLOADS_DIR;
               if(is_dir($myDir)) {
                echo "<input type='hidden' id='is_found' name='is_found' value='dir found'/>";
               } else {
                echo "<input type='hidden' id='is_found' name='is_found' value='dir not found'/>";
               } if(is_writable($myDir)) {
                echo "<input type='hidden' id='is_perm_found' name='is_perm_found' value='perm found'/>";
               } else {
                echo "<input type='hidden' id='is_perm_found' name='is_perm_found' value='perm not found'/>";
               }

	}

	public function show_import_screen() {
		global $uci_admin;
		$parserObj = new SmackCSVParser();
		$uci_admin->show_notices($parserObj);
		$step = isset($_REQUEST['step']) ? sanitize_title($_REQUEST['step']) : '';
		switch ($step) {
			case 'import_file':     // Step one
				include ( 'views/form-file-import-method.php' );
				break;
			case 'suggested_template':
				# NOTE: Removed the suggested template view
				break;
			case 'mapping_config':  // Step two
				if(isset($_REQUEST['eventKey']) ? sanitize_key($_REQUEST['eventKey']):'' ) :
					if(isset($_POST) && !empty($_POST)) :
						$_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
						$parserObj->screenData = array('import_file' => $_POST);
						update_option($_REQUEST['eventKey'], $parserObj->screenData);
					else:
						$parserObj->screenData = get_option($_REQUEST['eventKey']);
					endif;
				endif;
				if(empty($parserObj->screenData)):
					$parserObj->wp_session = "Your mapping configuration may lost. Please configure your mapping again!";
				endif;
				if(isset($_REQUEST['mapping_type'])) {
					$mapping_type = $_REQUEST['mapping_type'];
				} else {
					$mapping_type = '';
				}
				switch($mapping_type) {
					case 'advanced':
						include ( 'views/form-advanced-mapping-configuration.php' );
						break;
					case 'normal':
					default:
						include ( 'views/form-mapping-configuration.php' );
						break;
				}
				break;
			case 'media_config':
				include ( 'views/form-media-handling.php' );
				break;
			case 'import_config':
				include ( 'views/form-import-configuration.php' );
				break;
			case 'confirm':
				include ( 'views/form-ignite-import.php' );
				break;
			default:
				include ( 'views/form-file-import-method.php' );
				break;
		}
		return true;
	}

	public function show_manager_screen() {
		include ('views/form-manager-view.php' );
		return true;
	}
	
	public function show_uci_dashboard() {
               include ('views/form-dashboard-view.php');
        }

	public function show_export_screen() {
		$active_plugins = get_option( "active_plugins" );
		if(in_array('wp-ultimate-exporter/index.php', $active_plugins)){
			global $expUci_admin;
			$expUci_admin->show_export_screen();
		}
		else{
			wp_redirect('admin.php?page=sm-uci-import'); die();
		}
		
	}

	public function show_settings_screen() {
		include ('views/form-settings-view.php' );
		return true;
	}

	public function show_support_screen() {
               include ('views/form-support-view.php' );
               return true;
        }

     public function show_addon_screen()
     {
     	include ('views/form-addons-view.php');
     	return true;
     }


	public function show_notices($parseObj) {

	}

	public static function PieChart(){
		echo '<canvas id="uci_pro_pieStats"></canvas>';
	}

	public static function LineChart(){
		echo '<canvas id="uci_pro_lineStats" style="height:250px;"></canvas>';
	}

	public function get_mapping_widgets_order ($import_type) {
		$widgets = array();
		global $uci_admin;
		$customposts = $uci_admin->get_import_custom_post_types();
		if (in_array($import_type, get_taxonomies())) {
			$import_type = 'Taxonomies';
		}
		if (in_array($import_type, $customposts)) {
			$import_type = 'CustomPosts';
		}
		switch ($import_type) {
			case 'Posts':
			case 'Pages':
			case 'CustomPosts':
			case 'Events':
			case 'ticket':
				$widgets = array('Core Fields', 'WordPress Custom Fields', 'ACF Pro Fields', 'ACF Fields', 'ACF Repeater Fields',
					'Types Custom Fields', 'PODS Custom Fields', 'CCTM Custom Fields', 'All-in-One SEO Fields',
					'Yoast SEO Fields', 'Terms and Taxonomies', 'Custom-Field-Suite Fields');
				break;
			case 'Users':
				$widgets = array('Core Fields','WordPress Custom Fields', 'Custom Fields by WP-Members', 'Billing And Shipping Information',
					'ACF Pro Fields', 'ACF Fields', 'ACF Repeater Fields', 'Types Custom Fields', 'PODS Custom Fields', 'CCTM Custom Fields');
				break;
			case 'Comments':
				$widgets = array('Core Fields');
				break;
			case 'WooCommerce':
			case 'MarketPress':
			case 'WPeCommerce':
			case 'eShop':
				$widgets = array('Core Fields', 'Product Meta Fields', 'WordPress Custom Fields', 'ACF Pro Fields',
					'ACF Fields', 'ACF Repeater Fields', 'Types Custom Fields', 'PODS Custom Fields', 'CCTM Custom Fields',
					'All-in-One SEO Fields', 'Yoast SEO Fields', 'Terms and Taxonomies');
				if($import_type == 'WPeCommerce')
					$widgets = array_merge($widgets,array('WP e-Commerce Custom Fields'));
				break;
			case 'WPeCommerceCoupons':
				$widgets = array('Core Fields', 'Product Meta Fields');
				break;
			case 'WooCommerceVariations':
				$widgets = array('Core Fields', 'Product Meta Fields');
				break;
			case 'WooCommerceOrders':
				$widgets = array('Core Fields', 'Product Meta Fields');
				break;
			case 'WooCommerceCoupons':
				$widgets = array('Core Fields', 'Product Meta Fields');
				break;
			case 'WooCommerceRefunds':
				$widgets = array('Core Fields', 'Product Meta Fields');
				break;
			case 'MarketPressVariations':
				$widgets = array('Core Fields', 'Product Meta Fields');
				break;
			case 'Taxonomies':
				$widgets = array('Core Fields');
				break;
			case 'Tags':
				$widgets = array('Core Fields');
				break;
			case 'Categories':
				$widgets = array('Core Fields');
				break;
			case 'CustomerReviews':
				$widgets = array('Core Fields');
				break;
			default:
				$widgets = array('Core Fields');
				break;
		}
		return $widgets;
	}

	public function available_widgets ($import_type, $importAs) {
		global $uci_admin;
		$widgets = array();
		$widgets_order = $uci_admin->get_mapping_widgets_order($import_type);
		$supported_widgets = $uci_admin->get_available_widgets($import_type, $importAs);
		if(!empty($widgets_order) && !empty($supported_widgets)) :
			foreach($widgets_order as $item) {
				# TODO: Need to check whether this item (widget) to be related with the $import_type
				if(array_key_exists($item, $supported_widgets)) :
					$widgets[$item] = $supported_widgets[$item];
				endif;
			}
		endif;
		return $widgets;
	}

	public function get_available_widgets ($import_type, $importAs) {
		$possible_widgets = array();
		global $uci_admin;
		$customposts = $uci_admin->get_import_custom_post_types();
		if (in_array($import_type, $customposts)) {
			$import_type = 'CustomPosts';
		}
		if (in_array($import_type, get_taxonomies())) {
			$import_type = 'Taxonomies';
		}
		$possible_widgets['Core Fields'] = 'CORE';
		if($import_type == 'Posts' || $import_type == 'Pages' || $import_type == 'CustomPosts' ||
		   $import_type == 'WooCommerce' || $import_type == 'MarketPress' || $import_type == 'WPeCommerce' || $import_type == 'eShop' || $import_type == 'Users') {
			$possible_widgets['WordPress Custom Fields'] = 'CORECUSTFIELDS';
		}
		$active_plugins = $this->get_active_plugins();
		if(!empty($active_plugins)) {
			foreach($active_plugins as $plugin) {
				switch ($plugin) {
					case 'advanced-custom-fields-pro/acf.php':
						$possible_widgets['ACF Pro Fields'] = 'ACF';
						break;
					case 'advanced-custom-fields/acf.php':
						/** ACF PRO version 5.3.7 */
						$acf_pro_pluginPath = WP_PLUGIN_DIR . '/advanced-custom-fields/pro';
						if(is_dir($acf_pro_pluginPath))
							$possible_widgets['ACF Pro Fields'] = 'ACF';
						else
							$possible_widgets['ACF Fields'] = 'ACF';
						break;
					case 'acf-repeater/acf-repeater.php':
						$possible_widgets['ACF Repeater Fields'] = 'RF';
						break;
					case 'custom-content-type-manager/index.php':
						$possible_widgets['CCTM Custom Fields'] = 'CCTM';
						break;
					case 'types/wpcf.php':
						$possible_widgets['Types Custom Fields'] = 'TYPES';
						break;
					case 'pods/init.php':
						$possible_widgets['PODS Custom Fields'] = 'PODS';
						break;
					case 'all-in-one-seo-pack/all_in_one_seo_pack.php':
						$possible_widgets['All-in-One SEO Fields'] = 'AIOSEO';
						break;
					case 'wordpress-seo/wp-seo.php':
						$possible_widgets['Yoast SEO Fields'] = 'YOASTSEO';
						break;
					case 'wordpress-seo-premium/wp-seo-premium.php':
                                                $possible_widgets['Yoast SEO Fields'] = 'YOASTSEO';
                                                break;
					case 'wp-e-commerce-custom-fields/custom-fields.php':
						$possible_widgets['WP e-Commerce Custom Fields'] = 'WPECOMMETA';
						break;
					case 'wp-members/wp-members.php':
						if($import_type == 'Users') {
							$possible_widgets['Custom Fields by WP-Members'] = 'WPMEMBERS';
						}
						break;
					// Custom Field Suite Support
					case 'custom-field-suite/cfs.php':
						$possible_widgets['Custom-Field-Suite Fields'] = 'CFS';
						break;
					// Custom Field Suite Support
					case 'woocommerce/woocommerce.php':
					case 'marketpress/marketpress.php':
					case 'wordpress-ecommerce/marketpress.php':
					case 'wp-e-commerce/wp-shopping-cart.php':
					case 'eshop/eshop.php':
						$possible_widgets['Product Meta Fields'] = 'ECOMMETA';
						break;
					case 'sitepress-multilingual-cms/sitepress.php':
						$possible_widgets['Multi Lingual Support'] = 'WPML';
						break;
				}
			}
			if($import_type == 'Users') {
				if( in_array('marketpress/marketpress.php', $this->get_active_plugins()) || in_array('wordpress-ecommerce/marketpress.php', $this->get_active_plugins()) || in_array('woocommerce/woocommerce.php', $this->get_active_plugins()) ) {
					$possible_widgets['Billing And Shipping Information'] = 'BSI';
				}
			}
			if($import_type != 'Pages')
				$possible_widgets['Terms and Taxonomies'] = 'TERMS';
		}
		return $possible_widgets;
	}

	public function get_widget_fields($widget_name, $import_type, $importAs, $mode = null) {
		$fields = array();
		switch ($widget_name) {
			case 'Core Fields':
				$fields = $this->coreFields($import_type);
				break;
			case 'Product Meta Fields':
				if($import_type === 'WPeCommerce') :
					$fields = $this->ecommerceMetaFields($import_type);
					//$fields = $this->WPeCommerceCustomFields();
				elseif ($import_type == 'WooCommerce'):
					$fields = $this->ecommerceMetaFields($import_type);
					if(in_array('woocommerce-chained-products/woocommerce-chained-products.php', $this->get_active_plugins())) :
						$fields['ECOMMETA'] = array_merge($fields['ECOMMETA'], $this->WooCommerceChainedProductFields());
					endif;
					if(in_array('woocommerce-product-retailers/woocommerce-product-retailers.php', $this->get_active_plugins())) :
						$fields['ECOMMETA'] = array_merge($fields['ECOMMETA'], $this->WooCommerceProductRetailerFields());
					endif;
					if(in_array('woocommerce-product-addons/product-addons.php', $this->get_active_plugins())) :
						$fields['ECOMMETA'] = array_merge($fields['ECOMMETA'], $this->WooCommerceProductAddOnsFields());
					endif;
					if(in_array('woocommerce-warranty/woocommerce-warranty.php', $this->get_active_plugins())) :
						$fields['ECOMMETA'] = array_merge($fields['ECOMMETA'], $this->WooCommerceWarrantyFields());
					endif;
					if(in_array('woocommerce-pre-orders/woocommerce-pre-orders.php', $this->get_active_plugins())) :
						$fields['ECOMMETA'] = array_merge($fields['ECOMMETA'], $this->WooCommercePreOrderFields());
					endif;
				else:
					$fields = $this->ecommerceMetaFields($import_type);
				endif;
				break;
			case 'WordPress Custom Fields':
				$fields = $this->WPCustomFields($import_type, $importAs, $mode);
				break;
			case 'ACF Pro Fields':
				$fields = $this->ACFProCustomFields($import_type, $importAs, $mode);
				break;
			case 'ACF Fields':
				$fields = $this->ACFCustomFields($import_type, $importAs, $mode);
				break;
			case 'ACF Repeater Fields':
				$fields = $this->ACFRepeaterFields($import_type, $importAs, $mode);
				break;
			case 'Types Custom Fields':
				$fields = $this->TypesCustomFields($import_type, $importAs, $mode);
				break;
			case 'PODS Custom Fields':
				$fields = $this->PODSCustomFields($import_type, $importAs, $mode);
				break;
			case 'CCTM Custom Fields':
				$fields = $this->CCTMCustomFields();
				break;
			case 'All-in-One SEO Fields':
				$fields = $this->AIOSEOFields();
				break;
			case 'Yoast SEO Fields':
				$fields = $this->YoastSEOFields();
				break;
			case 'Custom-Field-Suite Fields':
				$fields = $this->CFSFields();
				break;
			case 'Billing And Shipping Information':
				$active_plugins = get_option("active_plugins");
				if(in_array('import-users/index.php', $active_plugins)){
					global $userUci_admin;
					$fields = $userUci_admin->billing_information_for_users();
				}
				break;
			case 'WP e-Commerce Custom Fields':
				$fields = $this->WPeCommerceCustomFields();
				break;
			case 'Custom Fields by WP-Members':
				$fields = $this->custom_fields_by_wp_members();
				break;
			case 'Terms and Taxonomies':
				$fields = $this->terms_and_taxonomies($import_type, $importAs, $mode);
				break;
		}
		return $fields;
	}
	
	public function get_available_groups($type) {
		$groups = array();
		if ($type == 'Posts') {
			$groups = array('CORE', 'CCTM', 'ACF', 'RF', 'TYPES', 'PODS', 'AIOSEO', 'YOASTSEO', 'CORECUST', 'TERMS', 'TAXO');
		}
		if ($type == 'Pages') {
			$groups = array('CORE', 'CCTM', 'ACF', 'RF', 'TYPES', 'PODS', 'AIOSEO', 'YOASTSEO', 'CORECUST', 'TAXO');
		}
		if ($type == 'WooCommerce') {
			$groups = array('CORE', 'CCTM', 'ACF', 'RF', 'TYPES', 'PODS', 'AIOSEO', 'YOASTSEO', 'ECOMMETA', 'CORECUST', 'TERMS', 'TAXO');
		}
		return $groups;
	}

	public static function initializing_scheduler() {
		if( !wp_next_scheduled( 'smack_uci_image_scheduler' )) {
			wp_schedule_event(time(), 'wp_ultimate_csv_importer_scheduled_images', 'smack_uci_image_scheduler');
		}
		if( !wp_next_scheduled( 'smack_uci_email_scheduler' )) {
			wp_schedule_event(time(), 'wp_ultimate_csv_importer_scheduled_emails', 'smack_uci_email_scheduler');
		}
		if( !wp_next_scheduled( 'smack_uci_replace_inline_images' )) {
			wp_schedule_event(time(), 'wp_ultimate_csv_importer_replace_inline_images', 'smack_uci_replace_inline_images');
		}
	}
}
add_action('init', array('SmackUCIAdmin', 'show_admin_menus'));
global $uci_admin;
$uci_admin = new SmackUCIAdmin();
