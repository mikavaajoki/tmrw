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

class SmackUCIHelper {

	private $event_information = array();

	#public $event_key = null;

	#public $event_information = array();

	#public $fileType;

	public function __construct() {
		$this->event_information['active_plugins'] = get_option('active_plugins');
	}

	public function setEventKey($eventKey) {
		$this->event_information['event_key'] = $eventKey;
	}

	public function setLastImportId($record_id) {
		$this->event_information['last_import_id'] = $record_id;
	}

	public function setRowMapping($mapping_template) {
		$this->event_information['csv_row_mapping'] = array();
		if(!empty($mapping_template))
			$this->event_information['csv_row_mapping'] = $mapping_template;
	}

	public function setEventFileInformation($file_info) {
		$this->event_information['import_file_info'] = $file_info;
	}

	public function setMappingConfiguration($mapping_info) {
		$this->event_information['mapping_config'] = $mapping_info;
	}

	public function setMediaConfiguration($media_info) {
		$this->event_information['media_handling'] = $media_info;
	}

	public function setImportConfiguration($import_config) {
		$this->event_information['import_config'] = $import_config;
	}

	public function setImportMethod($importMethod) {
		$this->event_information['import_method'] = $importMethod;
	}

	public function setImportType($importType) {
		$this->event_information['import_type'] = $importType;
	}

	public function setImportAs($importAs) {
		$this->event_information['import_as'] = $importAs;
	}

	public function setProcessedRowCount($processed) {
		$this->event_information['processed'] = $processed;
	}

	public function setInsertedRowCount($inserted) {
		$this->event_information['inserted'] = $inserted;
	}

	public function setUpdatedRowCount($updated) {
		$this->event_information['updated'] = $updated;
	}

	public function setSkippedRowCount($skipped) {
		$this->event_information['skipped'] = $skipped;
	}

	public function setMode($mode) {
		$this->event_information['mode'] = $mode;
	}

	public function setDetailedLog($detailed_log) {
		$this->event_information['detailed_log'] = $detailed_log;
	}

	public function setFileType($fileType) {
		$this->event_information['fileType'] = $fileType;
	}

	public function setAffectedRecords($id) {
		if(!isset($this->event_information['affected_records']))
			$this->event_information['affected_records'][] = $id;
		if(!in_array($id, $this->event_information['affected_records']))
			$this->event_information['affected_records'][] = $id;
	}

	public function setEventLog($key, $log) {
		$this->event_information['detailed_log'][$key] = $log;
	}

	public function setEventInformation($index, $data) {
		$this->event_information[$index] = $data;
	}

	public function getAffectedRecords() {
		if(!empty($this->event_information['affected_records']))
			return $this->event_information['affected_records'];

		$this->event_information['affected_records'] = array();
		return $this->event_information['affected_records'];
	}

	public function getActivePlugins() {
		if(!empty($this->event_information['active_plugins']))
			return $this->event_information['active_plugins'];
		else
			return get_option('active_plugins');
	}

	public function getLastImportId() {
		#if(isset($this->event_information['last_import_id']))
			return isset($this->event_information['last_import_id']) ? $this->event_information['last_import_id'] : '';
	}

	public function getEventInformation() {
		return $this->event_information;
	}

	public function getImportType() {
		return $this->event_information['import_type'];
	}

	public function getImportMethod() {
		return $this->event_information['import_method'];
	}

	public function getImportAs() {
		return $this->event_information['import_as'];
	}

	/* public function getOriginalFileName() {
		if(isset($this->event_information['import_file']['uploaded_name']))
			return $this->event_information['import_file']['uploaded_name'];
		return null;
	} */

	public function getEventFileInformation($key) {
		if(isset($this->event_information['import_file_info'][$key]))
			return $this->event_information['import_file_info'][$key];

		return null;
	}

	public function getMode() {
		if(isset($this->event_information['mode']))
			return $this->event_information['mode'];
		return null;
	}

	public function getFileType() {
		return $this->event_information['fileType'];
	}

	public function getMappingConfiguration() {
		return $this->event_information['mapping_config'];
	}

	public function getMediaConfiguration() {
		return $this->event_information['media_handling'];
	}

	public function getImportConfiguration() {
		return $this->event_information['import_config'];
	}

	public function getEventKey() {
		return $this->event_information['event_key'];
	}

	public function getDetailedLog() {
		return $this->event_information['detailed_log'];
	}

	public function getRowMapping($key = null) {
		if(empty($this->event_information['csv_row_mapping']))
			$this->event_information['csv_row_mapping'] = array();

		if(!empty($this->event_information['csv_row_mapping'][$key]) && $key != null)
			return $this->event_information['csv_row_mapping'][$key];

		return $this->event_information['csv_row_mapping'];
	}

	public function getRowData($key = null) {
		if(empty($this->event_information['csv_row_data']))
			$this->event_information['csv_row_data'] = array();

		if(!empty($this->event_information['csv_row_data'][$key]) && $key != null)
			return $this->event_information['csv_row_data'][$key];

		return $this->event_information['csv_row_data'];
	}

	public function getProcessedRowCount() {
		if(!isset($this->event_information['processed']))
			$this->event_information['processed'] = 0;

		return $this->event_information['processed'];

	}

	public function getInsertedRowCount() {
		if(!isset($this->event_information['inserted']))
			$this->event_information['inserted'] = 0;

		return $this->event_information['inserted'];
	}

	public function getUpdatedRowCount() {
		if(!isset($this->event_information['updated']))
			$this->event_information['updated'] = 0;

		return $this->event_information['updated'];
	}

	public function getSkippedRowCount() {
		if(!isset($this->event_information['skipped']))
			$this->event_information['skipped'] = 0;

		return $this->event_information['skipped'];
	}

	/* public $last_import_id;

	public $groupName;

	public $groupMapping = array();

	public $data = array();

	public $importType;

	public $importMethod;

	public $importAs;

	public $processed = 0;

	public $inserted = 0;

	public $updated = 0;

	public $skipped = 0;

	public $mode; */

	public $processing_row_id;

	public $detailed_log = array();

	// public $def_mpCols = array('Shipping Email' => 'msi_email',
	//                            'Shipping Name' => 'msi_name',
	//                            'Shipping Address1' => 'msi_address1',
	//                            'Shipping Address2' => 'msi_address2',
	//                            'Shipping City' => 'msi_city',
	//                            'Shipping State' => 'msi_state',
	//                            'Shipping Zip' => 'msi_zip',
	//                            'Shipping Country' => 'msi_country',
	//                            'Shipping Phone' => 'msi_phone',
	//                            'Billing Email' => 'mbi_email',
	//                            'Billing Name' => 'mbi_name',
	//                            'Billing Address1' => 'mbi_address1',
	//                            'Billing Address2' => 'mbi_address2',
	//                            'Billing City' => 'mbi_city',
	//                            'Billing State' => 'mbi_state',
	//                            'Billing Zip' => 'mbi_zip',
	//                            'Billing Country' => 'mbi_country',
	//                            'Billing Phone' => 'mbi_phone'
	// );

	// public $def_wcCols = array('Billing First Name' => 'billing_first_name',
	//                            'Billing Last Name' => 'billing_last_name',
	//                            'Billing Company' => 'billing_company',
	//                            'Billing Address1' => 'billing_address_1',
	//                            'Billing Address2' => 'billing_address_2',
	//                            'Billing City' => 'billing_city',
	//                            'Billing PostCode' => 'billing_postcode',
	//                            'Billing State' => 'billing_state',
	//                            'Billing Country' => 'billing_country',
	//                            'Billing Phone' => 'billing_phone',
	//                            'Billing Email' => 'billing_email',
	//                            'Shipping First Name' => 'shipping_first_name',
	//                            'Shipping Last Name' => 'shipping_last_name',
	//                            'Shipping Company' => 'shipping_company',
	//                            'Shipping Address1' => 'shipping_address_1',
	//                            'Shipping Address2' => 'shipping_address_2',
	//                            'Shipping City' => 'shipping_city',
	//                            'Shipping PostCode' => 'shipping_postcode',
	//                            'Shipping State' => 'shipping_state',
	//                            'Shipping Country' => 'shipping_country',
	//                            'API Consumer Key' => 'woocommerce_api_consumer_key',
	//                            'API Consumer Secret' => 'woocommerce_api_consumer_secret',
	//                            'API Key Permissions' => 'woocommerce_api_key_permissions',
	//                            'Shipping Region' => '_wpsc_shipping_region' ,
	//                            'Billing Region' => '_wpsc_billing_region',
	//                            'Cart' => '_wpsc_cart'
	// );

	public function get_active_plugins() {
		$active_plugins = get_option('active_plugins');
		return $active_plugins;
	}

	public function overwrite_globals($data) {
		global $smack_uci_globals;
		$GLOBALS['smack_uci_globals'] = $data;
	}

	public function coreFields($type) {
		$defCols = $coreFields = array();
		// Core fields for Posts / Pages / CustomPosts / WooCommerce / MarketPress / WPeCommerce / eShop
		$taxonomies = get_taxonomies();
		if (in_array($type, $taxonomies)) {
			if($type == 'category' || $type == 'product_category' || $type == 'product_cat' || $type == 'wpsc_product_category' || $type == 'event-categories'):
				$type = 'Categories';
			elseif($type == 'product_tag' || $type == 'event-tags' || $type == 'post_tag'):
				$type = 'Tags';
			elseif($type == 'comments'):
				$type = 'Comments';
			else:
				$type = 'Taxonomies';
			endif;
		}
		if ($type != 'Users' && $type != 'Taxonomies' && $type != 'CustomerReviews' && $type != 'Categories' && $type != 'Comments' && $type != 'MarketPressVariations') {
			$defCols = array(
				'Title' => 'post_title',
				'ID' => 'ID',
				'Content' => 'post_content',
				'Short Description' => 'post_excerpt',
				'Publish Date' => 'post_date',
				'Slug' => 'post_name',
				'Author' => 'post_author',
				'Status' => 'post_status',
				'Featured Image' => 'featured_image',
				'Nexgen Gallery' => 'nextgen-gallery',
			);
			if ($type === 'Posts' || $type === 'CustomPosts') {
				$defCols['Format'] = 'post_format';
				$defCols['Comment Status'] = 'comment_status';
				$defCols['Ping Status'] = 'ping_status';
			}
			if ($type === 'Pages') {
				$defCols['Parent'] = 'post_parent';
				$defCols['Order'] = 'menu_order';
				$defCols['Page Template'] = 'wp_page_template';
				$defCols['Comment Status'] = 'comment_status';
				$defCols['Ping Status'] = 'ping_status';
			}
			if (in_array('sitepress-multilingual-cms/sitepress.php', $this->get_active_plugins())) {
				$defCols['Language Code'] = 'language_code';
				$defCols['Translated Post Title'] = 'translated_post_title';
			}
		}
		// Core fields for Users
		if($type === 'Users') {
			$active_plugins = get_option( "active_plugins" );
			if(in_array('import-users/index.php', $active_plugins)){
				global $userUci_admin;
				$defCols = $userUci_admin->getUserWidgets();

			}
		}
		if(in_array('events-manager/events-manager.php', $this->get_active_plugins()) && $type === 'event' || $type === 'event-recurring' || $type === 'location' ){
			$customarray = array(
				'Event_start_date' => 'event_start_date',
				'Event_end_date' => 'event_end_date',
				'Event_start_time' => 'event_start_time',
				'Event_end_time' => 'event_end_time',
				'Event_all_day' => 'event_all_day',
				//'Event_rsvp' => 'event_rsvp',
				'Event_rsvp_date' => 'event_rsvp_date',
				'Event_rsvp_time' => 'event_rsvp_time',
				'Event_rsvp_spaces' => 'event_rsvp_spaces',
				'Event_spaces' => 'event_spaces',
				'Recurrence_interval' => 'recurrence_interval',
				'Recurrence_freq' => 'recurrence_freq',
				'Recurrence_byday' => 'recurrence_byday',
				'Recurrence_byweekno'=>'recurrence_byweekno',
				'Recurrence_days' => 'recurrence_days',
				'Recurrence_rsvp_days' => 'recurrence_rsvp_days',
				'Location_name' => 'location_name',
				'Location_address' => 'location_address',
				'Location_town' =>'location_town',
				'Location_state' => 'location_state',
				'Location_postcode' => 'location_postcode',
				'Location_region' => 'location_region',
				'Location_country' => 'location_country',
				'Ticket_name' => 'ticket_name',
				'Ticket_description' => 'ticket_description',
				'Ticket_price' => 'ticket_price',
				//'Ticket_start' => 'ticket_start',
				//'Ticket_end' => 'ticket_end',
				'Ticket_start_date' => 'ticket_start_date',
                                'Ticket_end_date' => 'ticket_end_date',
                                'Ticket_start_time' => 'ticket_start_time',
                                'Ticket_end_time' => 'ticket_end_time',
				'Ticket_min' => 'ticket_min',
				'Ticket_max' => 'ticket_max',
				'Ticket_spaces' => 'ticket_spaces',
				'Ticket_members' => 'ticket_members',
				'Ticket_members_roles' =>'ticket_members_roles',
				'Ticket_guests' => 'ticket_guests',
				'Ticket_required' => 'ticket_required',
			);
			foreach($customarray as $key => $value){
				$defCols[$key] = $value;
			}
		}
		
		//Tickets Fields
		if(in_array('events-manager/events-manager.php', $this->get_active_plugins()) && $type === 'ticket'){
			$customarray = array(	
				'Ticket_name' => 'ticket_name',
                                'Ticket_description' => 'ticket_description',
                                'Ticket_price' => 'ticket_price',
                                'Ticket_start_date' => 'ticket_start_date',
                                'Ticket_end_date' => 'ticket_end_date',
                                'Ticket_start_time' => 'ticket_start_time',
                                'Ticket_end_time' => 'ticket_end_time',
                                'Ticket_min' => 'ticket_min',
                                'Ticket_max' => 'ticket_max',
                                'Ticket_spaces' => 'ticket_spaces',
                                'Ticket_members' => 'ticket_members',
                                'Ticket_members_roles' =>'ticket_members_roles',
                                'Ticket_guests' => 'ticket_guests',
                                'Ticket_required' => 'ticket_required',
			);
			foreach($customarray as $key => $value){
                                $defCols[$key] = $value;
                        }
		}			

		// Core fields for Taxonomies
		if($type === 'Taxonomies') {
			$defCols = array(
				'Taxonomy Name' => 'name',
				'Taxonomy Slug' => 'slug',
				'Taxonomy Description' => 'description',
				'SEO Title' => 'wpseo_title',
				'SEO Description' => 'wpseo_desc',
				'Canonical' => 'wpseo_canonical',
				'Noindex this category' => 'wpseo_noindex',
				'Include in sitemap?' => 'wpseo_sitemap_include',
				'Term ID' => 'TERMID',
			);
			if (in_array('sitepress-multilingual-cms/sitepress.php', $this->get_active_plugins())) {
				$defCols['Language Code'] = 'language_code';
				$defCols['Translated Post Title'] = 'translated_post_title';
			}
		}
		if($type === 'Categories') {
			$defCols = array(
				'Category Name' => 'name',
				'Category Slug' => 'slug',
				'Category Description' => 'description',
				'SEO Title' => 'wpseo_title',
				'SEO Description' => 'wpseo_desc',
				'Canonical' => 'wpseo_canonical',
				'Noindex this category' => 'wpseo_noindex',
				'Include in sitemap?' => 'wpseo_sitemap_include',
				'Term ID' => 'TERMID',
			);
			if (in_array('sitepress-multilingual-cms/sitepress.php', $this->get_active_plugins())) {
				$defCols['Language Code'] = 'language_code';
				$defCols['Translated Post Title'] = 'translated_post_title';
			}
		}
		if($type === 'Tags') {
			$defCols = array(
				'Tag Name' => 'name',
				'Tag Slug' => 'slug',
				'Tag Description' => 'description',
				'SEO Title' => 'wpseo_title',
				'SEO Description' => 'wpseo_desc',
				'Canonical' => 'wpseo_canonical',
				'Noindex this category' => 'wpseo_noindex',
				'Include in sitemap?' => 'wpseo_sitemap_include',
				'Term ID' => 'TERMID',
			);
			if (in_array('sitepress-multilingual-cms/sitepress.php', $this->get_active_plugins())) {
				$defCols['Language Code'] = 'language_code';
				$defCols['Translated Post Title'] = 'translated_post_title';
			}
		}
		// Core fields for CustomerReviews
		if($type === 'CustomerReviews') {
			if(in_array('wp-customer-reviews/wp-customer-reviews-3.php', $this->get_active_plugins()) || in_array('wp-customer-reviews/wp-customer-reviews.php', $this->get_active_plugins())) {
				$defCols = array(
					'Review Date Time' => 'date_time',
					'Reviewer Name' => 'reviewer_name',
					'Reviewer Email' => 'reviewer_email',
					'Reviewer IP' => 'reviewer_ip',
					'Review Format' => 'review_format',
					'Review Title' => 'review_title',
					'Review Text' => 'review_text',
					'Review Response' => 'review_response',
					'Review Status' => 'status',
					'Review Rating' => 'review_rating',
					'Review URL' => 'reviewer_url',
					'Review to Post/Page Id' => 'page_id',
					'Custom Field #1' => 'custom_field1',
					'Custom Field #2' => 'custom_field2',
					'Custom Field #3' => 'custom_field3',
					'Review ID' => 'review_id',
				);
			}
		}
		if($type === 'Comments') {
			$defCols = array(
				'Comment Post Id' => 'comment_post_ID',
				'Comment Author' => 'comment_author',
				'Comment Author Email' => 'comment_author_email',
				'Comment Author URL' => 'comment_author_url',
				'Comment Content' => 'comment_content',
				'Comment Author IP' => 'comment_author_IP',
				'Comment Date' => 'comment_date',
				'Comment Approved' => 'comment_approved',
			);
		}
		if($type === 'WooCommerceVariations'){
			$defCols = array(
				'Product Id' => 'PRODUCTID',
				'Parent Sku' => 'PARENTSKU',
				'Variation Sku' => 'VARIATIONSKU',
				'Variation ID' => 'VARIATIONID');
		}
		if($type === 'WooCommerceCoupons'){
			$defCols = array(
				'Coupon Code' => 'coupon_code',
				'Description' => 'description',
				'Date' => 'coupon_date',
				'Status' => 'coupon_status',
				'Coupon Id' =>'COUPONID');
		}
		if($type === 'WooCommerceOrders'){
			$defCols = array(
				'Customer Note' => 'customer_note',
				'Order Status' => 'order_status',
				'Order Date' => 'order_date',
				'Order Id' => 'ORDERID');
		}
		if($type === 'WooCommerceRefunds'){
			$defCols = array(
				'Post Parent' => 'post_parent',
				'Post Excerpt' => 'post_excerpt',
				'Refund Id' => 'REFUNDID');
		}
	
		if($type == 'WPeCommerceCoupons') {
			$defCols = array(
				'Coupon Code' => 'coupon_code',
				'Description' => 'description',
				'Status' => 'coupon_status',
				'Discount' => 'discount',
				'Discount Type' => 'discount_type',
				'Start' => 'start',
				'Expiry' => 'expiry',
				'Use Once' => 'use_once',
				'Apply On All Products' => 'apply_on_all_products',
				'Conditions' => 'conditions'
			);
		}

		if($type === 'MarketPressVariations'){
			$defCols['Product Id'] = 'PRODUCTID';
			$defCols['Variation ID'] = 'VARIATIONID';
		}
		if($type === 'MarketPress' || $type == 'WooCommerce' || $type == 'WPeCommerce' || $type == 'eShop'){
                        $defCols['PRODUCT SKU'] = 'PRODUCTSKU';
		}
		foreach ($defCols as $key => $val) {
			$coreFields['CORE'][$key]['label'] = $key;
			$coreFields['CORE'][$key]['name'] = $val;
		}
		// Unset the Product Author for MarketPress / WooCommerce
		if($type == 'MarketPress' || $type == 'WooCommerceRefunds' || $type == 'WooCommerceOrders' || $type == 'WooCommerceCoupons' || $type == 'WooCommerceVariations' || $type == 'WooCommerce') {
			unset($coreFields['CORE']['Author']);
		}
		return $coreFields;
	}

	public function import_post_types($import_type, $importAs = null) {
		$module = array('Posts' => 'post', 'Pages' => 'page', 'Users' => 'users', 'Comments' => 'comments', 'Taxonomies' => $importAs, 'CustomerReviews' =>'wpcr3_review', 'Categories' => 'categories', 'Tags' => 'tags', 'eShop' => 'post', 'WooCommerce' => 'product', 'WPeCommerce' => 'wpsc-product','WPeCommerceCoupons' => 'wpsc-product', 'MarketPress' => 'product', 'MarketPressVariations' => 'mp_product_variation','WooCommerceVariations' => 'product', 'WooCommerceOrders' => 'product', 'WooCommerceCoupons' => 'product', 'WooCommerceRefunds' => 'product', 'CustomPosts' => $importAs);
		foreach (get_taxonomies() as $key => $taxonomy) {
			$module[$taxonomy] = $taxonomy;
		}
		if(array_key_exists($import_type, $module)) {
			return $module[$import_type];
		}
		else {
			return $import_type;
		}
	}

	public function get_import_post_types(){

		$custom_array = array('post', 'page');
		$other_posttypes = array('attachment','revision','nav_menu_item','wpsc-product-file','mp_order','shop_webhook');
		$importas = array(
			'Posts' => 'Posts',
			'Pages' => 'Pages',
			'Comments' => 'Comments'
		);

		$active_plugins = get_option( "active_plugins" );
		if(in_array('import-users/index.php', $active_plugins)){
			$importas['Users'] = 'Users';
		}

		if(in_array('woocommerce/woocommerce.php', $active_plugins) && in_array('import-woocommerce/index.php', $active_plugins)){
			$importas['WooCommerce'] = 'WooCommerce';
		}

		$all_post_types = get_post_types();
		foreach($other_posttypes as $ptkey => $ptvalue) {
			if (in_array($ptvalue, $all_post_types)) {
				unset($all_post_types[$ptvalue]);
			}
		}
		foreach($all_post_types as $key => $value) {
			if(!in_array($value, $custom_array)) {
				$importas[$value] = $value;
			}
		}

		
		# Added support for Customer Reviews plugin
		if(in_array('wp-customer-reviews/wp-customer-reviews-3.php', $this->get_active_plugins()) ||  in_array('wp-customer-reviews/wp-customer-reviews.php', $this->get_active_plugins())) {
			$importas['Customer Reviews'] = 'CustomerReviews';
			if(isset($importas['wpcr3_review'])) {
                               unset($importas['wpcr3_review']);
                        }

		}

		return $importas;
	}

	public function get_import_custom_post_types(){
		$custompost = array();
		$custom_array = array('post', 'page', 'product', 'wpsc-product', 'product_variation', 'shop_order', 'shop_coupon', 'shop_order_refund','mp_product_variation');
		$other_posttypes = array('attachment','revision','nav_menu_item','wpsc-product-file','mp_order','shop_webhook');
		$all_post_types = get_post_types();
		foreach($other_posttypes as $ptkey => $ptvalue) {
			if (in_array($ptvalue, $all_post_types)) {
				unset($all_post_types[$ptvalue]);
			}
		}
		foreach($all_post_types as $key => $value){
			if(!in_array($value,$custom_array)){
				$custompost[$value] = $value;
			}
		}

		return $custompost;
	}

	public function WPCustomFields($import_type, $importAs, $mode = null) {
		global $wpdb;
		if($mode == 'export') :
			$import_type = $importAs;
		endif;
		$module = $this->import_post_types($import_type);
		$commonMetaFields = array();
		if($module != 'users') {
			$keys = $wpdb->get_col( "SELECT pm.meta_key FROM $wpdb->posts p
									JOIN $wpdb->postmeta pm
									ON p.ID = pm.post_id
									WHERE p.post_type = '{$module}'
									GROUP BY meta_key
									HAVING meta_key NOT LIKE '\_%' and meta_key NOT LIKE 'field_%' and meta_key NOT LIKE 'wpcf-%'
									ORDER BY meta_key" );
		} else {
			$keys = $wpdb->get_col( "SELECT um.meta_key FROM $wpdb->users u
									JOIN $wpdb->usermeta um
									ON u.ID = um.user_id
									GROUP BY meta_key
									HAVING meta_key NOT LIKE '\_%' and meta_key NOT LIKE 'field_%' and meta_key NOT LIKE 'wpcf-%'
									ORDER BY meta_key" );
		}
		foreach ($keys as $val) {
			$commonMetaFields['CORECUSTFIELDS'][$val]['label'] = $val;
			$commonMetaFields['CORECUSTFIELDS'][$val]['name'] = $val;
		}
		
		if(in_array('advanced-custom-fields-pro/acf.php',$this->get_active_plugins())){
         $commonMetaFields = array();
         return $commonMetaFields;
        }
        if(in_array('advanced-custom-fields/acf.php',$this->get_active_plugins())){
         $commonMetaFields = array();
         return $commonMetaFields;
        }
		return $commonMetaFields;
	}

	public function ecommerceMetaFields($module) {
		$ecommerceMetaFields = array();
		$MetaFields = array();
		if($module === 'eShop') {
			$MetaFields = array(
				'SKU' => 'sku',
				'Product Options' => 'products_option',
				'Sale Price' => 'sale_price',
				'Regular Price' => 'regular_price',
				'Description' => 'description',
				'Shipping Rate' => 'shiprate',
				'Featured Product' => 'featured_product',
				'Product in sale' => 'product_in_sale',
				'Stock Available' => 'stock_available',
				'Show Options as' => 'cart_option'
			);
		}
		if($module === 'WPeCommerce') {
			$MetaFields = array(
				'Stock' => 'stock',
				'Price' => 'price',
				'Sale Price' => 'sale_price',
				'SKU' => 'sku',
				'Notify Stock Runs Out' => 'notify_when_none_left',
				'UnPublish If Stock Runs' => 'unpublish_when_none_left',
				'Taxable Amount' => 'taxable_amount',
				'Is Taxable' => 'is_taxable',
				'External Link' => 'external_link',
				'External Link Text' => 'external_link_text',
				'External Link Target' => 'external_link_target',
				'Can Have Uploaded Image' => 'can_have_uploaded_image',
				'Engraved' => 'engraved',
				'No Shipping' => 'no_shipping',
				'Weight' => 'weight',
				'Weight Unit' => 'weight_unit',
				'Height' => 'height',
				'Height Unit' => 'height_unit',
				'Width' => 'width',
				'Width Unit' => 'width_unit',
				'Length' => 'length',
				'Length Unit'  => 'length_unit',
				'Dimension Unit' => 'dimension_unit',
				'Shipping' => 'shipping',
				'Custom Name' => 'custom_name',
				'Custom Description' => 'custom_desc',
				'Custom Meta' => 'custom_meta',
				'Merchant Notes' => 'merchant_notes',
				'Enable Comments' => 'enable_comments',
				'Quantity Limited' => 'quantity_limited',
				'Special' => 'special',
				'Display Weight As' => 'display_weight_as',
				'State' => 'state',
				'Quantity' => 'quantity',
				'Table Price' => 'table_price',
				'Alternative Currencies and Price' => 'alternative_currencies_and_price',
				'Google Prohibited' => 'google_prohibited',
				'Discussion' => 'discussion',
				'Comments' => 'comments',
				'Attributes' => 'attributes',
				'Taxes' => 'taxes',
				'Image Gallery' => 'image_gallery',
				'Short Description' => 'short_description',
				'Meta Data' => 'meta_data',
				'Variations' => 'variations'
			);
		}
		if($module === 'WPeCommerceCoupons') {
			$MetaFields = array(
				'Discount' => 'discount',
				'Discount Type' => 'discount_type',
				'Start' => 'start',
				'End' => 'end',
				'Active' => 'active',
				'Use Once' => 'use_once',
				'Apply On All Products' => 'apply_on_all_products',
				'Conditions' => 'conditions'
			);
		}
		if($module === 'WooCommerce' && in_array('import-woocommerce/index.php', $this->getActivePlugins())) {
			global $wcomUci_admin;
			$MetaFields = $wcomUci_admin->getMetaFieldsOfWcom();
		}
		if($module === 'WooCommerceVariations'){
			$MetaFields = array(
				'Product Attribute Name' => 'product_attribute_name',
				'Product Attribute Value' => 'product_attribute_value',
				'Product Attribute Visible' => 'product_attribute_visible',
				'Product Attribute Variation' => 'product_attribute_variation',
				'Product Attribute Position' => 'product_attribute_position',
				'Featured' => 'featured',
				'Downloadable Files' => 'downloadable_files',
				'Download Limit' => 'download_limit',
				'Download Expiry' => 'download_expiry',
				'Price' => 'price',
				'Sales Price Date From' => 'sale_price_dates_from',
				'Sales Price Date To' => 'sale_price_dates_to',
				'Regular Price' => 'regular_price',
				'Sale Price' => 'sale_price',
				'Purchase Note' => 'purchase_note',
				'Default Attributes' => 'default_attributes',
				'Custom Attributes' => 'custom_attributes',
				'Weight' => 'weight',
				'Length' => 'length',
				'Width' => 'width',
				'Height' => 'height',
				'Downloadable' => 'downloadable',
				'Virtual' => 'virtual',
				'Stock' => 'stock',
				'Stock Status' => 'stock_status',
				'Stock Quantity' => 'stock_qty',
				'Sold Individually' => 'sold_individually',
				'Manage Stock' => 'manage_stock',
				'Backorders' => 'backorders',
				'SKU' => 'sku',
				'Thumbnail Id' => 'thumbnail_id',
			);
		}
		if($module === 'WooCommerceOrders') {
			$MetaFields = array(
				'Recorded Sales'          => 'recorded_sales',
				'Payment Method Title'    => 'payment_method_title',
				'Payment Method'          => 'payment_method',
				'Transaction Id'          => 'transaction_id',
				'Billing First Name'      => 'billing_first_name',
				'Billing Last Name'       => 'billing_last_name',
				'Billing Company'         => 'billing_company',
				'Billing Address1'        => 'billing_address_1',
				'Billing Address2'        => 'billing_address_2',
				'Billing City'            => 'billing_city',
				'Billing PostCode'        => 'billing_postcode',
				'Billing State'           => 'billing_state',
				'Billing Country'         => 'billing_country',
				'Billing Phone'           => 'billing_phone',
				'Billing Email'           => 'billing_email',
				'Shipping First Name'     => 'shipping_first_name',
				'Shipping Last Name'      => 'shipping_last_name',
				'Shipping Company'        => 'shipping_company',
				'Shipping Address1'       => 'shipping_address_1',
				'Shipping Address2'       => 'shipping_address_2',
				'Shipping City'           => 'shipping_city',
				'Shipping PostCode'       => 'shipping_postcode',
				'Shipping State'          => 'shipping_state',
				'Shipping Country'        => 'shipping_country',
				'Customer User'           => 'customer_user',
				'Order Key'               => 'order_key',
				'Order Currency'          => 'order_currency',
				'Order Shipping Tax'      => 'order_shipping_tax',
				'Order Tax'               => 'order_tax',
				'Order Total'             => 'order_total',
				'Cart Discount Tax'       => 'cart_discount_tax',
				'Cart Discount'           => 'cart_discount',
				'Order Shipping'          => 'order_shipping',
				'ITEM: name'              => 'item_name',
				'ITEM: type'              => 'item_type',
				'ITEM: variation_id'      => 'item_variation_id',
				'ITEM: product_id'        => 'item_product_id',
				'ITEM: line_subtotal'     => 'item_line_subtotal',
				'ITEM: line_subtotal_tax' => 'item_line_subtotal_tax',
				'ITEM: line_total'        => 'item_line_total',
				'ITEM: line_tax'          => 'item_line_tax',
				'ITEM: line_tax_data'     => 'item_line_tax_data',
				'ITEM: tax_class'         => 'item_tax_class',
				'ITEM: qty'               => 'item_qty',
				'FEE: name'               => 'fee_name',
				'FEE: type'               => 'fee_type',
				'FEE: tax_class'          => 'fee_tax_class',
				'FEE: line_total'         => 'fee_line_total',
				'FEE: line_tax'           => 'fee_line_tax',
				'FEE: line_tax_data'      => 'fee_line_tax_data',
				'FEE: line_subtotal'      => 'fee_line_subtotal',
				'FEE: line_subtotal_tax'  => 'fee_line_subtotal_tax',
				'SHIPMENT: name'          => 'shipment_name',
				'SHIPMENT: method_id'     => 'shipment_method_id',
				'SHIPMENT: cost'          => 'shipment_cost',
				'SHIPMENT: taxes'         => 'shipment_taxes',
			);
		}
		if($module === 'WooCommerceCoupons') {
			$MetaFields = array(
				'Discount Type' => 'discount_type',
				'Coupon Amount' => 'coupon_amount',
				'Individual Use' => 'individual_use',
				'Product Ids' => 'product_ids',
				'Exclude Product Ids' => 'exclude_product_ids',
				'Usage Limit' => 'usage_limit',
				'Usage Limit Per User' => 'usage_limit_per_user',
				'Limit Usage' => 'limit_usage_to_x_items',
				'Expiry Date' => 'expiry_date',
				'Free Shipping' => 'free_shipping',
				'Exclude Sale Items' => 'exclude_sale_items',
				'Product Categories' => 'product_categories',
				'Exclude Product Categories' => 'exclude_product_categories',
				'Minimum Amount' => 'minimum_amount',
				'Maximum Amount' => 'maximum_amount',
				'Customer Email' => 'customer_email'
			);
		}
		if($module === 'WooCommerceRefunds') {
			$MetaFields = array(
				'Recorded Sales' => 'recorded_sales',
				'Refund Amount' => 'refund_amount',
				'Order Shipping Tax' => 'order_shipping_tax',
				'Order Tax' => 'order_tax',
				'Order Shipping' => 'order_shipping',
				'Cart Discount' => 'cart_discount',
				'Cart Discount Tax' => 'cart_discount_tax',
				'Order Total' => 'order_total',
				'Customer User' =>'customer_user'
			);
		}
		if($module === 'MarketPress') {
			if(in_array('wordpress-ecommerce/marketpress.php', $this->get_active_plugins())){
				$MetaFields = array(
					'Variation' => 'variation',
					'SKU' => 'sku',
					'Regular Price' => 'regular_price',
					'Is Sale' => 'is_sale',
					'Sale Price' => 'sale_price',
					'Track Inventory' => 'track_inventory',
					'Inventory' => 'inventory',
					'Track Limit' => 'track_limit',
					'Limit Per Order' => 'limit_per_order',
					'Product Link' => 'product_link',
					'Is Special Tax' => 'is_special_tax',
					'Special Tax' => 'special_tax',
					'Sales Count' => 'sales_count',
					'Extra Shipping Cost' => 'extra_shipping_cost',
					'File URL' => 'file_url',
				);
			}
			if(in_array('marketpress/marketpress.php', $this->get_active_plugins())){
				$MetaFields = array(
					'Product Type' => 'product_type',
					'SKU' => 'sku',
					'Thumbnail Id' => 'thumbnail_id',
					'Sale Price' => 'sale_price',
					'Regular Price' => 'regular_price',
					'Per Order Limit' => 'per_order_limit',
					'Has Sale' => 'has_sale',
					'Sale Price Start Date' => 'sale_price_start_date',
					'Sale Price End Date' => 'sale_price_end_date',
					'File URL' => 'file_url',
					'External URL' => 'external_url',
					'Special Tax Rate' => 'special_tax_rate',
					'Charge Tax' => 'charge_tax',
					'Charge Shipping' => 'charge_shipping',
					'Weight Pounds' => 'weight_pounds',
					'Weight Ounces' => 'weight_ounces',
					'Weight Extra Shipping Cost' => 'weight_extra_shipping_cost',
					'Inventory Tracking' => 'inventory_tracking',
					'Quantity' => 'quantity',
					'Inv Out_Of Stock Purchase' => 'inv_out_of_stock_purchase',
					'Related Products' => 'related_products',
					'Product Images' => 'product_images',
				);
			}
		}
		if($module === 'MarketPressVariations') {
			if(in_array('marketpress/marketpress.php', $this->get_active_plugins())){
                                $MetaFields = array(
                                        'SKU' => 'sku',
                                        'Variation Image' => 'mp_variation_image',
                                        'Sale Price' => 'sale_price',
                                        'Regular Price' => 'regular_price',
                                        'Per Order Limit' => 'per_order_limit',
                                        'Has Sale' => 'has_sale',
                                        'Sale Price Start Date' => 'sale_price_start_date',
                                        'Sale Price End Date' => 'sale_price_end_date',
                                        'File URL' => 'file_url',
                                        'Special Tax Rate' => 'special_tax_rate',
                                        'Charge Tax' => 'charge_tax',
                                        'Charge Shipping' => 'charge_shipping',
                                        'Weight Pounds' => 'weight_pounds',
                                        'Weight Ounces' => 'weight_ounces',
                                        'Weight Extra Shipping Cost' => 'weight_extra_shipping_cost',
                                        'Inventory Tracking' => 'inventory_tracking',
                                        'Quantity' => 'quantity',
                                        'Inv Out_Of Stock Purchase' => 'inv_out_of_stock_purchase',
                                        'Variation Name' => 'mp_variation_name',
                                        'Variation Value' => 'mp_variation_value',
                                        'Has Variation Content' => 'has_variation_content',
                                        'Variation Content Type' => 'variation_content_type',
                                        'Variation Content Desc' => 'variation_content_desc',
                                );
                        }	
		}
		if(!empty($MetaFields)){
			foreach($MetaFields as $key => $val) {
				$ecommerceMetaFields['ECOMMETA'][$val]['label'] = $key;
				$ecommerceMetaFields['ECOMMETA'][$val]['name'] = $val;
			}
		}
		return $ecommerceMetaFields;
	}

	public function WooCommerceChainedProductFields() {
		$ecommerceMetaFields = array();
		$chain_product = array(
			'Chained Product Detail' => 'chained_product_detail',
			'Chained Product Manage Stock' => 'chained_product_manage_stock',
		);
		foreach($chain_product as $key => $val) {
			$ecommerceMetaFields[$val]['label'] = $key;
			$ecommerceMetaFields[$val]['name'] = $val;
		}
		return $ecommerceMetaFields;
	}

	public function WooCommerceProductRetailerFields() {
		$ecommerceMetaFields = array();
		$retailers = array(
			'Retailers Only Purchase' => 'wc_product_retailers_retailer_only_purchase',
			'Retailers Use Buttons' => 'wc_product_retailers_use_buttons',
			'Retailers Product Button Text' => 'wc_product_retailers_product_button_text',
			'Retailers Catalog Button Text' => 'wc_product_retailers_catalog_button_text',
			'Retailers Id' => 'wc_product_retailers_id',
			'Retailers Price' => 'wc_product_retailers_price',
			'Retailers URL' => 'wc_product_retailers_url',
		);
		foreach($retailers as $key => $val) {
			$ecommerceMetaFields[$val]['label'] = $key;
			$ecommerceMetaFields[$val]['name'] = $val;
		}
		return $ecommerceMetaFields;
	}

	public function WooCommerceProductAddOnsFields() {
		$ecommerceMetaFields = array();
		$product_Addons = array(
			'Product Addons Exclude Global' => 'product_addons_exclude_global',
			'Product Addons Group Name' => 'product_addons_group_name',
			'Product Addons Group Description' => 'product_addons_group_description',
			'Product Addons Type' => 'product_addons_type',
			'Product Addons Position' => 'product_addons_position',
			'Product Addons Required' => 'product_addons_required',
			'Product Addons Label Name' => 'product_addons_label_name',
			'Product Addons Price' => 'product_addons_price',
			'Product Addons Minimum' => 'product_addons_minimum',
			'Product Addons Maximum' => 'product_addons_maximum',
		);
		foreach($product_Addons as $key => $val) {
			$ecommerceMetaFields[$val]['label'] = $key;
			$ecommerceMetaFields[$val]['name'] = $val;
		}
		return $ecommerceMetaFields;
	}

	public function WooCommerceWarrantyFields() {
		$ecommerceMetaFields = array();
		$warranty = array(
			'Warranty Label' => 'warranty_label',
			'Warranty Type' => 'warranty_type',
			'Warranty Length' => 'warranty_length',
			'Warranty Value' => 'warranty_value',
			'Warranty Duration' => 'warranty_duration',
			'Warranty Addons Amount' => 'warranty_addons_amount',
			'Warranty Addons Value' => 'warranty_addons_value',
			'Warranty Addons Duration' => 'warranty_addons_duration',
			'No Warranty Option' => 'no_warranty_option',
		);
		foreach($warranty as $key => $val) {
			$ecommerceMetaFields[$val]['label'] = $key;
			$ecommerceMetaFields[$val]['name'] = $val;
		}
		return $ecommerceMetaFields;
	}

	public function WooCommercePreOrderFields() {
		$ecommerceMetaFields = array();
		$pre_orders = array(
			'Pre-Orders Enabled' => 'preorders_enabled',
			'Pre-Orders Fee' => 'preorders_fee',
			'Pre-Orders When to Charge' => 'preorders_when_to_charge',
			'Pre-Orders Availabilty Datetime' => 'preorders_availability_datetime'
		);
		foreach($pre_orders as $key => $val) {
			$ecommerceMetaFields[$val]['label'] = $key;
			$ecommerceMetaFields[$val]['name'] = $val;
		}
		return $ecommerceMetaFields;
	}

	public function WPeCommerceCustomFields() {
		$WPeComCustomFields = array();
		$get_wpecom_custom_fields = get_option('wpsc_cf_data');
		$wpecom_custom_fields = maybe_unserialize($get_wpecom_custom_fields);
		if(!empty($wpecom_custom_fields)) {
			foreach($wpecom_custom_fields as $key => $val) {
				$WPeComCustomFields['WPECOMMETA'][$val['slug']]['label'] = $val['name'];
				$WPeComCustomFields['WPECOMMETA'][$val['slug']]['name'] = $val['slug'];
			}
		}
		return $WPeComCustomFields;
	}

	public function ACFProCustomFields($import_type, $importAs, $mode) {
		global $wpdb;
		global $uci_admin;
		$repeater_field_arr = $flexible_field_arr = "";
		$group_id_arr = $customFields = $rep_customFields = array();
		#TODO
		$get_acf_groups     = $wpdb->get_results("select ID, post_content from $wpdb->posts where post_type = 'acf-field-group'");
		#TODO
		#$get_acf_groups     = $wpdb->get_col( $wpdb->prepare( "select ID from $wpdb->posts where post_type = %s", 'acf-field-group' ) );

		// Get available ACF group id
		foreach ( $get_acf_groups as $item => $group_rules ) {
			$rule = maybe_unserialize($group_rules->post_content);
			if(!empty($rule)) {
				foreach($rule['location'] as $key => $value) {
					if($value[0]['operator'] == '==' && $value[0]['value'] == $this->import_post_types($import_type, $importAs)) {
						$group_id_arr[] = $group_rules->ID; #. ',';
					}
				}
			}
		}

		#TODO
		/*foreach ( $get_acf_groups as $groupID ) {
			$post_content = unserialize($groupID->post_content);
			if($post_content['location'][0][0]['value'] == $post_type && $post_content['location'][0][0]['operator'] == '==') {
				$group_id_arr .= $groupID->ID . ',';
			}
		}*/
		#TODO
		if ( !empty($group_id_arr) ) {
			foreach($group_id_arr as $groupId) {
				$get_acf_fields = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title, post_content, post_excerpt, post_name FROM $wpdb->posts where post_parent in (%d)", $groupId ) );
				if ( ! empty( $get_acf_fields ) ) {
					foreach ( $get_acf_fields as $acf_pro_fields ) {
						$get_field_content = unserialize( $acf_pro_fields->post_content );
						if ( $get_field_content['type'] == 'repeater' ) {
							$repeater_field_arr .= $acf_pro_fields->ID . ",";
							//multi sup repeater
							$repeater_field = substr( $repeater_field_arr, 0, - 1 );
							$get_sub_fields = $wpdb->get_results( $wpdb->prepare( "SELECT ID, post_title, post_content, post_excerpt, post_name FROM $wpdb->posts where post_parent in (%d)", $repeater_field ) );
							foreach ( $get_sub_fields as $get_sub_key ) {
								$get_sub_field_content = unserialize( $get_sub_key->post_content );
								if ( $get_sub_field_content['type'] == 'repeater' ) {
									$repeater_field_arr .= $get_sub_key->ID . ",";
								}
							}
							//multi sup repeater
						} else if ( $get_field_content['type'] == 'flexible_content' ) {
							$flexible_field_arr .= $acf_pro_fields->ID . ",";
						} else if ( $get_field_content['type'] == 'message' || $get_field_content['type'] == 'tab' ) {
							$customFields["ACF"][ $acf_pro_fields->post_name ]['label'] = $acf_pro_fields->post_title;
							$customFields["ACF"][ $acf_pro_fields->post_name ]['name']  = $acf_pro_fields->post_name;
						} else {
							if ( $acf_pro_fields->post_excerpt != null || $acf_pro_fields->post_excerpt != '' ) {
								$customFields["ACF"][ $acf_pro_fields->post_excerpt ]['label'] = $acf_pro_fields->post_title;
								$customFields["ACF"][ $acf_pro_fields->post_excerpt ]['name']  = $acf_pro_fields->post_excerpt;
							}
						}
					}
				}
			}
			$repeater_field_arr = substr($repeater_field_arr, 0, -1);
			$flexible_field_arr = substr($flexible_field_arr, 0, -1);

			if(!empty($repeater_field_arr)) {
				$get_acf_repeater_fields = $wpdb->get_results($wpdb->prepare("SELECT ID, post_title, post_content, post_excerpt FROM $wpdb->posts where post_parent in (%d)", $repeater_field_arr));
			}
			if(!empty($get_acf_repeater_fields)) {
				foreach($get_acf_repeater_fields as $acf_pro_repeater_fields) {
					$rep_customFields[$acf_pro_repeater_fields->post_title] = $acf_pro_repeater_fields->post_excerpt;
					$check_exist_key = "ACF: " . $acf_pro_repeater_fields->post_title;
					if(array_key_exists($check_exist_key, $customFields)) {
						unset($customFields[$check_exist_key]);
					}
					$customFields["RF"][$acf_pro_repeater_fields->post_excerpt]['label'] = $acf_pro_repeater_fields->post_title;
					$customFields["RF"][$acf_pro_repeater_fields->post_excerpt]['name'] = $acf_pro_repeater_fields->post_excerpt;
				}
			}
			if(!empty($flexible_field_arr)) {
				$get_acf_flexible_content_fields = $wpdb->get_results($wpdb->prepare("SELECT ID, post_title, post_content, post_excerpt FROM $wpdb->posts where post_parent in (%d)",$flexible_field_arr));
			}
			if(!empty($get_acf_flexible_content_fields)) {
				foreach($get_acf_flexible_content_fields as $acf_pro_fc_fields) {
					$fc_customFields[$acf_pro_fc_fields->post_title] = $acf_pro_fc_fields->post_excerpt;
					$check_exist_key = "ACF: " . $acf_pro_fc_fields->post_title;
					if(array_key_exists($check_exist_key, $customFields)) {
						unset($customFields[$check_exist_key]);
					}
					$customFields["RF"][$acf_pro_fc_fields->post_excerpt]['label'] = $acf_pro_fc_fields->post_title;
					$customFields["RF"][$acf_pro_fc_fields->post_excerpt]['name'] = $acf_pro_fc_fields->post_excerpt;
				}
			}
		}
		return $customFields;
	}

	public function ACFCustomFields($import_type, $importAs, $mode) {
		global $wpdb;
		$get_acf_fields = $customFields = array();

		$get_acf_groups     = $wpdb->get_results( $wpdb->prepare( "select p.ID, pm.meta_value from $wpdb->posts p join $wpdb->postmeta pm on p.ID = pm.post_id where p.post_type = %s and pm.meta_key = %s", 'acf', 'rule' ) );
		$group_id_arr       = $repeater_field_arr = $flexible_field_arr = "";

		// Get available ACF group id
		foreach ( $get_acf_groups as $item => $group_rules ) {
			$rule = maybe_unserialize($group_rules->meta_value);
			if(!empty($rule) && $rule['operator'] == '==' && $rule['value'] == $this->import_post_types($import_type)) {
				$group_id_arr .= $group_rules->ID . ',';
			}
		}
		if($group_id_arr != '') {
			$group_id_arr = substr( $group_id_arr, 0, - 1 );

			// Get available ACF fields based on the import type and group id
			$get_acf_fields = $wpdb->get_col( "SELECT meta_value FROM $wpdb->postmeta
														WHERE post_id IN ($group_id_arr)
                                                        GROUP BY meta_key
                                                        HAVING meta_key LIKE 'field_%'
                                                        ORDER BY meta_key" );
		}
		// Available ACF fields
		if (!empty($get_acf_fields)) {
			foreach ($get_acf_fields as $key => $value) {
				$get_acf_field = @unserialize($value);
				$customFields["ACF"][$get_acf_field['name']]['label'] = $get_acf_field['label'];
				$customFields["ACF"][$get_acf_field['name']]['name'] = $get_acf_field['name'];
				$acf_field[] = $get_acf_field['name'];
			}
		}
		return $customFields;
	}

	public function ACFRepeaterFields($import_type, $importAs, $mode) {
		global $wpdb;
		$get_repeater_fields = $customFields = array();

		$get_acf_groups     = $wpdb->get_results( $wpdb->prepare( "select p.ID, pm.meta_value from $wpdb->posts p join $wpdb->postmeta pm on p.ID = pm.post_id where p.post_type = %s and pm.meta_key = %s", 'acf', 'rule' ) );
		$group_id_arr       = $repeater_field_arr = $flexible_field_arr = "";

		// Get available ACF group id
		foreach ( $get_acf_groups as $item => $group_rules ) {
			$rule = maybe_unserialize($group_rules->meta_value);
			if(!empty($rule) && $rule['operator'] == '==' && $rule['value'] == $this->import_post_types($import_type, $importAs)) {
				$group_id_arr .= $group_rules->ID . ',';
			}
		}
		if($group_id_arr != '') {
			$group_id_arr = substr( $group_id_arr, 0, - 1 );

			// Get available ACF fields based on the import type and group id
			$get_repeater_fields = $wpdb->get_col( "SELECT meta_value FROM $wpdb->postmeta
														WHERE post_id IN ($group_id_arr)
                                                        GROUP BY meta_key
                                                        HAVING meta_key LIKE 'field_%'
                                                        ORDER BY meta_key" );
		}
		#$get_repeater_fields = $wpdb->get_col("SELECT ID FROM $wpdb->posts where post_name like 'acf_%' and post_type = 'acf'");
		#foreach( $get_repeater_fields as $fieldGroupId ) {
			#$get_field_details = $wpdb->get_col("select meta_value from $wpdb->postmeta where post_id = $fieldGroupId and meta_key like 'field_%'");
		if (!empty($get_repeater_fields)) {
			foreach( $get_repeater_fields as $repFieldDet ) {
				$repeaterFields = unserialize($repFieldDet);
				foreach( $repeaterFields as $fieldKey => $fieldVal ) {
					if($fieldKey == 'sub_fields') {
						for($a=0; $a<count($fieldVal); $a++) {
							$customFields['RF'][$fieldVal[$a]['name']]['label'] = $fieldVal[$a]['label'];
							$customFields['RF'][$fieldVal[$a]['name']]['name']  = $fieldVal[$a]['name'];
						}
					}
				}
			}
		}
		return $customFields;
	}

	public function PODSCustomFields($import_type, $importAs, $mode) {
		global $wpdb;
		$podsFields = array();
		$import_type = $this->import_post_types($import_type, $importAs);
		$post_id = $wpdb->get_results($wpdb->prepare("select ID from $wpdb->posts where post_name= %s and post_type = %s", $import_type, '_pods_pod'));
		if(!empty($post_id)) {
			$lastId          = $post_id[0]->ID;
			$get_pods_fields = $wpdb->get_results( $wpdb->prepare( "SELECT post_title, post_name FROM $wpdb->posts where post_parent = %d AND post_type = %s", $lastId, '_pods_field' ) );
			if ( ! empty( $get_pods_fields ) ) :
				foreach ( $get_pods_fields as $pods_field ) {
					$podsFields["PODS"][ $pods_field->post_name ]['label'] = $pods_field->post_title;
					$podsFields["PODS"][ $pods_field->post_name ]['name']  = $pods_field->post_name;
				}
			endif;
		}
		return $podsFields;
	}

	public function TypesCustomFields($import_type, $importAs, $mode) {
		global $wpdb;
		$typesFields = array();
		if($import_type == 'Users') {
			$getUserMetaFields = get_option('wpcf-usermeta');
			if(is_array($getUserMetaFields)) {
				foreach ($getUserMetaFields as $optKey => $optVal) {
					$typesFields["TYPES"][$optVal['slug']]['label'] = $optVal['name'];
					$typesFields["TYPES"][$optVal['slug']]['name'] = $optVal['slug'];
				}
			}
		} else {
			$import_type = $this->import_post_types($import_type, $importAs);
			#$post_types = "Types Group for " . $import_type;
			#$post_id = $wpdb->get_results($wpdb->prepare("select ID from $wpdb->posts where post_content = %s and post_title = %s", 'Groups Description', $post_types));
			$get_groups = $wpdb->get_results($wpdb->prepare("select ID from $wpdb->posts where post_type = %s", 'wp-types-group'));
			if(!empty($get_groups)) {
				foreach($get_groups as $item => $group) {
					$lastId       = $group->ID;
					$rule_groups  = get_post_meta( $lastId, '_wp_types_group_post_types', true );
					$rules = explode(',', $rule_groups);
					if(in_array($import_type, $rules)) {
						$fields       = get_post_meta( $lastId, '_wp_types_group_fields', true );
						$trim         = substr( $fields, 1, - 1 );
						$types_fields = explode( ',', $trim );
						$count        = count( $types_fields );
						if ( is_array( $types_fields ) ) {
							for ( $i = 0; $i < $count; $i ++ ) {
								foreach ( $types_fields as $key => $value ) {
									$typesFields['TYPES'][ $value ]['name']  = $value;
									$typesFields['TYPES'][ $value ]['slug']  = $value;
									$typesFields['TYPES'][ $value ]['label'] = $value;
								}
							}
						}
					}
				}
			/*$types_fields = get_option('wpcf-fields');
				echo "<pre>";
			if(is_array($types_fields)) {
				foreach ($types_fields as $optKey => $optVal) {
					$typesFields["TYPES"][$optVal['slug']]['label'] = $optVal['name'];
					$typesFields["TYPES"][$optVal['slug']]['name'] = $optVal['slug'];
				}
			}*/
			} else {
				$typesFields ='';
			}

		}

		return $typesFields;
	}

	public function CCTMCustomFields() {
		$cctmFields = array();
		$getOptions = get_option('cctm_data');
		$get_cctm_fields = $getOptions['custom_field_defs'];
		if(!empty($get_cctm_fields)) :
			foreach ($get_cctm_fields as $optKey => $optVal) {
				$cctmFields["CCTM"][$optVal['name']]['label'] = $optVal['label'];
				$cctmFields["CCTM"][$optVal['name']]['name'] = $optVal['name'];
			}
		endif;
		return $cctmFields;
	}

	public function AIOSEOFields() {
		$aioseoFields = array();
		$seoFields = array('Keywords' => 'keywords',
		                   'Description' => 'description',
		                   'Title' => 'title',
		                   'NOINDEX' => 'noindex',
		                   'NOFOLLOW' => 'nofollow',
				   'Canonical URL' => 'custom_link',
		                   'Title Atr' => 'titleatr',
		                   'Menu Label' => 'menulabel',
		                   'Disable' => 'disable',
		                   'Disable Analytics' => 'disable_analytics',
		                   'NOODP' => 'noodp',
		                   'NOYDIR' => 'noydir'
		);
		foreach ($seoFields as $key => $val) {
			$aioseoFields['AIOSEO'][$val]['label'] = $key;
			$aioseoFields['AIOSEO'][$val]['name'] = $val;
		}
		return $aioseoFields;
	}

	public function YoastSEOFields() {
		$yoastseoFields = array();
		$seoFields = array('SEO Title' => 'title',
		                   'Meta Description' => 'meta_desc',
		                   'Meta Robots Index' => 'meta-robots-noindex',
		                   'Meta Robots Follow' => 'meta-robots-nofollow',
		                   'Meta Robots Advanced' => 'meta-robots-adv',
				   'Breadcrumbs Title'  => 'bread-crumbs-title',
		                   'Include in Sitemap' => 'sitemap-include',
		                   'Sitemap Priority' => 'sitemap-prio',
		                   'Canonical URL' => 'canonical',
		                   '301 Redirect' => 'redirect',
		                   'Facebook Title' => 'opengraph-title',
		                   'Facebook Description' => 'opengraph-description',
		                   'Facebook Image' => 'opengraph-image',
		                   'Twitter Title' => 'twitter-title',
		                   'Twitter Description' => 'twitter-description',
		                   'Twitter Image' => 'twitter-image',
		                   'Google+ Title' => 'google-plus-title',
		                   'Google+ Description' => 'google-plus-description',
		                   'Google+ Image' => 'google-plus-image',
		                   'Focus Keyword' => 'focus_keyword'
		);
		foreach ($seoFields as $key => $val) {
			$yoastseoFields['YOASTSEO'][$val]['label'] = $key;
			$yoastseoFields['YOASTSEO'][$val]['name'] = $val;
		}
		return $yoastseoFields;
	}

	public function custom_fields_by_wp_members () {
		$WPMemberFields = array();
		$get_WPMembers_fields = get_option('wpmembers_fields');
		if (is_array($get_WPMembers_fields) && !empty($get_WPMembers_fields)) {
			foreach ($get_WPMembers_fields as $get_fields) {
				$WPMemberFields['WPMEMBERS'][$get_fields[2]]['label'] = $get_fields[1];
				$WPMemberFields['WPMEMBERS'][$get_fields[2]]['name'] = $get_fields[2];
			}
		}
		return $WPMemberFields;
	}

	// public function billing_information_for_users () {
	// 	$billing_and_shipping_info = array();
	// 	if(in_array( 'marketpress/marketpress.php', $this->get_active_plugins() ) || in_array( 'wordpress-ecommerce/marketpress.php', $this->get_active_plugins() )) {
	// 		foreach($this->def_mpCols as $mp_key => $mp_val) {
	// 			$billing_and_shipping_info['BSI'][$mp_val]['label'] = $mp_key;
	// 			$billing_and_shipping_info['BSI'][$mp_val]['name'] = $mp_val;
	// 		}
	// 	}
	// 	if(in_array( 'woocommerce/woocommerce.php', $this->get_active_plugins() )) {
	// 		foreach($this->def_wcCols as $woo_key => $woo_val) {
	// 			$billing_and_shipping_info['BSI'][$woo_val]['label'] = $woo_key;
	// 			$billing_and_shipping_info['BSI'][$woo_val]['name'] = $woo_val;
	// 		}
	// 	}
	// 	return $billing_and_shipping_info;
	// }

	public function terms_and_taxonomies($type, $optionalType = null, $mode = null) {
		$term_taxonomies = array();
		$importas = $this->import_post_types($type);
		$taxonomies = get_object_taxonomies( $importas, 'names' );
		/*$term_taxonomies = array();
		if ($type == 'Posts' || $type == 'eShop') {
			$term_taxonomies['TERMS']['post_category']['label'] = 'Categories';
			$term_taxonomies['TERMS']['post_category']['name'] = 'post_category';
			$term_taxonomies['TERMS']['post_tag']['label'] = 'Tags';
			$term_taxonomies['TERMS']['post_tag']['name'] = 'post_tag';
		}
		/* if ($type == 'WooCommerce') {
			$term_taxonomies['TERMS']['product_category']['label'] = 'Categories';
			$term_taxonomies['TERMS']['product_category']['name'] = 'product_category';
			$term_taxonomies['TERMS']['product_tag']['label'] = 'Tags';
			$term_taxonomies['TERMS']['product_tag']['name'] = 'product_tag';
		}
		$taxonomies = get_taxonomies();
		foreach ($taxonomies as $key => $value) {
			if ($key != 'category' && $key != 'link_category' && $key != 'post_tag' && $key != 'nav_menu' &&
			    $key != 'post_format' && $key != 'product_type' && $key != 'event-tags' && $key != 'event-categories') {
				if (!array_key_exists($key, $term_taxonomies)) {
					$get_taxonomy_label = get_taxonomy($key);
					$taxonomy_label = $get_taxonomy_label->labels->singular_name;
					$term_taxonomies['TERMS'][$key]['label'] = $taxonomy_label;
					$term_taxonomies['TERMS'][$key]['name'] = $key;
				}
			}
		}*/
		if(!empty($taxonomies)) {
			foreach ($taxonomies as $key => $value) {
				$get_taxonomy_label = get_taxonomy($value);
				$taxonomy_label = $get_taxonomy_label->labels->singular_name;
				if($value == 'wpsc_product_category' || $value == 'product_cat'){
					$value = 'product_category';
				}elseif($value == 'category'){
					$value = 'post_category';
				}
				$term_taxonomies['TERMS'][$key]['label'] = $taxonomy_label;
				$term_taxonomies['TERMS'][$key]['name'] = $value;
			}
		}
		return $term_taxonomies;
	}

	public function convert_string2hash_key($value) {
		$file_name = hash_hmac('md5', "$value", 'secret');
		return $file_name;
	}

	/**
	 * Function to get the upload directory
	 * @param string $check
	 * @param $parserObj
	 *
	 * @return string
	 */
	public function getUploadDirectory($parserObj, $check = 'plugin_uploads') {
		$upload_dir = wp_upload_dir(); // WordPress upload directory
		if ($check == 'plugin_uploads') {
			return $upload_dir ['basedir'] . "/" . $parserObj->uploadDir;
		} else if ($check == 'ftpdownload') {
			return $upload_dir ['basedir'] . "/ultimate_importer_ftpfiles";
		}
		else if ($check == 'Export_csv_log') {
			return $upload_dir ['basedir'] . "/" . $parserObj->exportDir;
		}
		else if ($check == 'zip_uploads') {
			return $upload_dir ['basedir'] . "/" . $parserObj->zipDir;
		}
		else {
			return $upload_dir ['basedir'];
		}
	}

	/**
	 * Function to retrieve the mapping for specific event
	 * @param $eventKey
	 *
	 * @return array
	 */
	public function getEventMapping($eventKey) {
		$parserObj = new SmackCSVParser();
		$screen_info_file = $this->getUploadDirectory($parserObj, 'plugin_uploads') . "/" . $parserObj->screenDataDir . '/' . $eventKey; #ToDo: Need to create the ScreenData directory and save all post values in the event file
		$get_screen_data = fopen($screen_info_file, 'r');
		$get_screen_data = fread($get_screen_data, filesize($screen_info_file));
		@fclose($get_screen_data);
		$screen_data = unserialize($get_screen_data);
		return $screen_data['mapping_config'];
	}

	/**
	 * @param null $eventKey
	 * @param null $values
	 */
	public function SetPostValues($eventKey = null, $values = null){
		#$uploadpath = SM_UCI_SCREENS_DATA;
		$uploadPath = SM_UCI_IMPORT_DIR . '/' . $eventKey;
		if (!is_dir($uploadPath)) {
			wp_mkdir_p($uploadPath);
		}
		//$post_values = array();
		$filename = $uploadPath . '/screenInfo.txt';
		//$myfile = fopen($filename, "w") or die("Unable to open file!");
		//$post_values[$eventkey] = $values;
		//$post_values = serialize($post_values);
        chmod($uploadPath,0700);
		$myfile = fopen($filename, "w") or die("Unable to open file!");
		$post_values[$eventKey] = $values;
		$post_values = serialize($post_values);
		fwrite($myfile, $post_values);
		fclose($myfile);
		//file_put_contents($filename, $post_values,FILE_APPEND);
		$_SESSION[$eventKey] = $values;
	}

	public function GetPostValues($eventKey){
		#$uploadpath = SM_UCI_SCREENS_DATA;
		$uploadPath = SM_UCI_IMPORT_DIR . '/' . $eventKey;
		$screen_info_file = $uploadPath.'/screenInfo.txt';
		$screen_data = array();
		if(file_exists($screen_info_file)) {
			$get_screen_data = fopen( $screen_info_file, 'r' );
			$get_screen_data = fread( $get_screen_data, filesize( $screen_info_file ) );
			@fclose( $get_screen_data );
			$screen_data = unserialize( $get_screen_data );
		}
		return $screen_data;
	}

	public function importData($eventKey, $importType, $importMethod, $mode, $data, $currentLimit, $eventMapping, $affectedRecords, $mediaConfig, $importConfig) {
		global $wpdb, $uci_admin;
		$uci_admin->setImportAs($uci_admin->import_post_types($importType));
		$uci_admin->setEventInformation('csv_row_data', $data);
		$uci_admin->processing_row_id = $currentLimit;
		$customPosts = $uci_admin->get_import_custom_post_types();
		if (in_array($importType, get_taxonomies())) {
			if($importType == 'category' || $importType == 'product_category' || $importType == 'product_cat' || $importType == 'wpsc_product_category' || $importType == 'event-categories'):
				$importType = 'Categories';
			elseif($importType == 'product_tag' || $importType == 'event-tags' || $importType == 'post_tag'):
				$importType = 'Tags';
			else:
				$importType = 'Taxonomies';
			endif;
		}
		if (in_array($importType, $customPosts)) {
			$importType = 'CustomPosts';
		}

		//Assigning the data array to the global variable (based on groups)
		$result = array();
		$available_groups_type = $uci_admin->available_widgets($importType, $this->event_information['import_as']);
		#$this->groupMapping = $this->generateDataArrayBasedOnGroups($available_groups_type, $eventMapping, $data);
                $mapping_config = $uci_admin->getMappingConfiguration();
                $mapping_method = $mapping_config['smack_uci_mapping_method'];
$currentMapping = $this->generateDataArrayBasedOnGroups( $available_groups_type, $eventMapping, $data, $mapping_method);
                if(empty($currentMapping)) {
                        $uci_admin->setRowMapping( $data );
                } else {
                        $uci_admin->setRowMapping($this->generateDataArrayBasedOnGroups($available_groups_type, $eventMapping, $data, $mapping_method));
                }
		$event_information = $uci_admin->getEventInformation();
		if(empty($event_information)) {
			$screen_data = $uci_admin->GetPostValues( $eventKey );
		} else {
			$screen_data = $uci_admin->getEventInformation();
		}
		$duplicateHandling = array(
			'is_duplicate_handle' => isset($screen_data['import_config']['duplicate']) ? $screen_data['import_config']['duplicate'] : '',
			'conditions' => !empty($screen_data['import_config']['duplicate_conditions']) ? $screen_data['import_config']['duplicate_conditions'] : array(),
			'action' => isset($screen_data['import_config']['handle_duplicate']) ? $screen_data['import_config']['handle_duplicate'] : '',
			'media_handling' => isset($mediaConfig) ? $mediaConfig : ''
		);
		$active_plugins = get_option("active_plugins");
		// print_r($uci_admin->getRowMapping());
		// die();
		foreach ($uci_admin->getRowMapping() as $groupName => $groupValue) {
			#$this->groupName = $groupName;
			//$this->groupMapping = $groupMapping;
			//$this->data = !empty($import_dataArr) ? $import_dataArr: '';
			switch ($groupName) {
				case 'CORE':
					$result = $this->importDataForCoreFields($groupValue, $importType, $mode, $eventKey, $duplicateHandling, $mediaConfig, $importConfig);
					#if(!$result)
					#	break;
					#Todo: Assign the last imported record id.
					$last_import_id = isset($result['ID']) ? $result['ID'] : '';
					$this->setLastImportId($last_import_id);
					$mode_of_affect = isset($result['MODE']) ? $result['MODE'] : '';
					$assign_author = isset($result['AUTHOR']) ? $result['AUTHOR'] : '';
					$error_msg = isset($result['ERROR_MSG']) ? $result['ERROR_MSG'] : '';
					if($importType == 'Taxonomies' || $importType == 'Categories' || $importType == 'Tags'|| $importType == 'Comments')
						$importType = 'Term';
					if ($this->getLastImportId() != '') {
						// Push event logs in database
						$wpdb->insert('wp_ultimate_csv_importer_log_values', array(
								'eventKey' => $eventKey,
								'recordId' => $this->getLastImportId(),
								'module' => $importType,
								'mode_of_import' => $mode
							), array('%s', '%d', '%s', '%s')
						);
						#$this->detailed_log[$currentLimit]['RECORD'] = "<b>$mode_of_affect $importType id:</b> #" . $this->getLastImportId() . $assign_author;
						if($mode != 'Schedule') {
							if ( $importType == 'WooCommerceCoupons' || $importType == 'WooCommerceRefunds' || $importType == 'WooCommerceOrders' || $importType == 'WooCommerceVariations' || $importType == 'WooCommerce' ) {
								$this->detailed_log[ $currentLimit ]['VERIFY'] = "<b> Click here to verify</b> - <a href='" . get_edit_post_link( $this->getLastImportId(), true ) . "'target='_blank' title='" . esc_attr( 'Edit this item' ) . "'>Admin View</a>";
							}elseif( $importType == 'Users'){
								$this->detailed_log[$currentLimit]['VERIFY'] = "<b> Click here to verify</b> - <a href='" . get_edit_user_link( $this->getLastImportId() , true ) . "' target='_blank' title='" . esc_attr( 'Edit this item' ) . "'> User Profile </a>";

							} elseif ( $importType == 'Posts' || $importType == 'CustomPosts' || $importType == 'Pages' || $importType == 'WooCommerceVariations' || $importType == 'MarketPress' || $importType == 'eShop' || $importType == 'WPeCommerce' || $importType == 'ticket') {
								if ( ! isset( $groupValue['post_title'] ) ) {
									$groupValue['post_title'] = '';
								}
								$this->detailed_log[ $currentLimit ]['VERIFY'] = "<b> Click here to verify</b> - <a href='" . get_permalink( $this->getLastImportId() ) . "' target='_blank' title='" . esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $groupValue['post_title'] ) ) . "'rel='permalink'>Web View</a> | <a href='" . get_edit_post_link( $this->getLastImportId(), true ) . "'target='_blank' title='" . esc_attr( 'Edit this item' ) . "'>Admin View</a>";
							}
						}
					} else {
						#$this->detailed_log[$currentLimit]['RECORD'] = "Can't " . $mode_of_affect . " Record";
						#if($error_msg != '')
						#	$this->detailed_log[$currentLimit]['RECORD'] .= ' - ' . $error_msg;
					}
					break;
				case 'ECOMMETA':
					if(in_array('import-woocommerce/index.php', $active_plugins)){
						global $wcomUci_admin;
						$wcomUci_admin->importDataOfWcomMeta($groupValue, $this->getLastImportId());
					}
					break;
				case 'CORECUSTFIELDS':
					$this->importDataForWPMetaFields($groupValue, $this->getLastImportId(), $importType);
					break;
				case 'ACF':
					# Note: Removed data import for ACF fields
					break;
				case 'RF':
					# Note: Removed data import for ACF Repeater fields
					break;
				case 'TYPES':
					# Note: Removed data import for Toolset Types fields
					break;
				case 'PODS':
					# Note: Removed data import for PODS fields
					break;
				case 'CCTM':
					# Note: Removed data import for CCTM fields
					break;
				case 'AIOSEO':
					$aio_seo_row_mapping = $this->getRowMapping('AIOSEO');
					if(empty($aio_seo_row_mapping) || !file_exists(SM_UCI_PRO_DIR . 'includes/class-uci-aioseo-data-import.php'))
						break;
					require_once "class-uci-aioseo-data-import.php";
					break;
				case 'YOASTSEO':
					# Note: Removed data import for WordPress Yoast SEO fields
					break;
				case 'CFS':
					$cfs_row_mapping = $this->getRowMapping('CFS');
					if(empty($cfs_row_mapping) || !file_exists(SM_UCI_PRO_DIR . 'includes/class-uci-cfs-data-import.php')) {
						break;
					}
					require_once "class-uci-cfs-data-import.php";
					global $cfsHelper;
					$cfsHelper->push_cfs_data();
				break;
				case 'TERMS':
					$terms_row_mapping = $this->getRowMapping('TERMS');
					if(empty($terms_row_mapping))
						break;
					$this->importTermsAndTaxonomies($groupValue, $this->getLastImportId(), $importType);
					break;
				case 'WPMEMBERS':
					if(in_array('import-users/index.php', $active_plugins)){
						global $userUci_admin;
						$returnArr = $userUci_admin->importDataOfusersWPMem($groupValue, $this->getLastImportId());
					}
					break;
				case 'BSI':
					if(in_array('import-users/index.php', $active_plugins)){
						global $userUci_admin;
						$returnArr = $userUci_admin->importDataOfusersBS($groupValue, $this->getLastImportId());
					}
					break;
			}
		}
	}

	public function importDataForCoreFields ($data_array, $importType, $mode, $eventKey, $duplicateHandling, $mediaConfig, $importConfig) {
		$returnArr = array();
		global $wpdb, $uci_admin;
		$mode_of_affect = 'Inserted';

		if(!$data_array['post_format'])
		{
			if($data_array['post_format_option'])
				$data_array['post_format']=$data_array['post_format_option'];
		}
		#TODO: Check the mode & conditions based on import configuration values.

		$event_info = $uci_admin->getEventInformation();
		// Import the core fields based on the import type
		if($importType != 'ticket') {
			switch ($importType) {
				case 'Users':
					$active_plugins = get_option("active_plugins");
					if(in_array('import-users/index.php', $active_plugins)){
						global $userUci_admin;
						$returnArr = $userUci_admin->importDataOfusers($data_array, $mode, $eventKey, $duplicateHandling);
					}
					break;
				case 'CustomerReviews':
					require_once "class-uci-customer-reviews-data-import.php";
					$reviewsObj = new SmackUCICustomerReviews();
					$returnArr = $reviewsObj->importDataForCustomerReviews($data_array, $mode, $eventKey, $duplicateHandling);
					break;
				case 'Tags':
				case 'Categories':
				case 'Taxonomies':
					#NOTE: Removed the bulk import for Terms & Taxonomies
					break;
				case 'Comments':
					$array = $uci_admin->getRowMapping();
					$data_array = $array['CORE'];
					$commentid = '';
					$post_id = $data_array['comment_post_ID'];
					$post_exists = $wpdb->get_row("SELECT * FROM $wpdb->posts WHERE id = '" . $post_id . "' and post_status in ('publish','draft','future','private','pending')", 'ARRAY_A');
					$valid_status = array('1', '0', 'spam');
					if(empty($data_array['comment_approved'])) {
						$data_array['comment_approved'] = 0;
					}
					if(!in_array($data_array['comment_approved'], $valid_status)) {
						$data_array['comment_approved'] = 0;
					}
					$data_array['comment_approved'] = trim($data_array['comment_approved']);
					if ($post_exists) {
						$retID = wp_insert_comment($data_array);
						$uci_admin->setInsertedRowCount($uci_admin->getInsertedRowCount() + 1);
						$uci_admin->detailed_log[$uci_admin->processing_row_id]['Message'] = 'Inserted Comment ID: ' . $retID;
						$mode_of_affect = 'Inserted';
					} else {
						$retID = $commentid;
						$uci_admin->setSkippedRowCount($uci_admin->getSkippedRowCount() + 1);
						$uci_admin->detailed_log[$uci_admin->processing_row_id]['Message'] = "Skipped, Due to unknown post ID.";
					}
					$returnArr['ID'] = $retID;
					$returnArr['MODE'] = $mode_of_affect;
					return $returnArr;
					break;
				case 'WooCommerce':
					if(in_array('import-woocommerce/index.php', $this->getActivePlugins() ) ){
						global $wcomUci_admin;
						$returnArr = $wcomUci_admin->importDataOfWcommerceProduct($data_array, $importType, $mode, $eventKey, $duplicateHandling);
					}
				break;
				case 'WooCommerceVariations':
				case 'WooCommerceOrders':
				case 'WooCommerceCoupons':
				case 'WooCommerceRefunds':
					# Note: Removed data import for WooCommerce fields
					break;
				case 'WPeCommerce':
				case 'WPeCommerceCoupons':
					# Note: Removed data import for WP-eCommerce fields
					break;
				case 'MarketPress':
					# Note: Removed data import for MarketPress fields
					break;
				case 'MarketPressVariations':
					# Note: Removed data import for MarketPress Variation fields
					break;
				default:
					$conditions = $duplicateHandling['conditions'];
					if(isset($duplicateHandling['is_duplicate_handle']) && $duplicateHandling['is_duplicate_handle'] == 'on') {
						$duplicateHandling['action'] = 'Skip';
					}
					$duplicate_action = $duplicateHandling['action'];
					// Assign post type
					$data_array['post_type'] = $this->import_post_types( $importType, $this->event_information['import_as'] );
					if($duplicate_action == 'Update' || $duplicate_action == 'Skip'):
						$mode = 'Update';
					endif;
					$is_update = false;
					if($mode != 'Insert' && !empty($conditions)):
						if( !empty($conditions[0]) ) {
							$whereCondition = " " . $conditions[0] . " = '{$data_array[$conditions[0]]}'";
							$duplicate_check_query = "select ID from $wpdb->posts where ($whereCondition) and (post_type = '{$data_array['post_type']}') and (post_status != 'trash') order by ID DESC";
							$is_update = true;
						}
					endif;
					/* Post date options */
					if(!isset( $data_array['post_date'] )) {
						$data_array['post_date'] = current_time('Y-m-d H:i:s');
					} else {
						if(strtotime( $data_array['post_date'] )) {
							$data_array['post_date'] = date( 'Y-m-d H:i:s', strtotime( $data_array['post_date'] ) );
						} else {
							$data_array['post_date'] = current_time('Y-m-d H:i:s');
						}
					}

					/* Post author options */
					if(!isset($data_array['post_author'])) {
						$data_array['post_author'] = 1;
					} else {
						if(isset( $data_array['post_author'] )) {
							$user_records = $this->get_from_user_details( $data_array['post_author'] );
							$data_array['post_author'] = $user_records['user_id'];
							$assigned_author = $user_records['message'];
						}
					}
					$post_format_array=array('post-format-aside','post-format-image','post-format-video','post-format-audio','post-format-quote','post-format-link','post-format-gallery','aside','image','video','audio','quote','link','gallery');
					/* Post Format Options */
					if ( ! empty( $data_array['post_format'] ) ) {
						if ( ! is_numeric( $data_array['post_format'] ) ) {
							if(in_array(trim($data_array['post_format']),$post_format_array)){
								$post_format = $data_array['post_format'];
							}
							else
								unset($data_array['post_format']);
						} else {
							switch ( $data_array ['post_format'] ) {
								case 1 :
									$post_format = 'post-format-aside';
									break;
								case 2 :
									$post_format = 'post-format-image';
									break;
								case 3 :
									$post_format = 'post-format-video';
									break;
								case 4 :
									$post_format = 'post-format-audio';
									break;
								case 5 :
									$post_format = 'post-format-quote';
									break;
								case 6 :
									$post_format = 'post-format-link';
									break;
								case 7 :
									$post_format = 'post-format-gallery';
									break;
							}
						}
						$data_array['post_format'] = $post_format;
					}
					/* Post Status Options */
					if ( !empty($data_array['post_date']) ) {
						$data_array = $this->assign_post_status( $data_array );
					}
					// Assign post type
					//$data_array['post_type'] = $this->import_post_types( $importType );

					// Import data with fast mode added by Fredrick Marks
					foreach (array('transition_post_status', 'save_post', 'pre_post_update', 'add_attachment', 'edit_attachment', 'edit_post', 'post_updated', 'wp_insert_post') as $act) {
						remove_all_actions($act);
					}

					// Initiate the action to insert / update the record
					if ($mode == 'Insert') {
						unset($data_array['ID']);
                                                 foreach($data_array as $data)
                                              {
                                                   if($data_array['post_slug']) {
                                                      $data_array['post_name']=$data_array['post_slug'];
                                                         unset($data_array['post_slug']);
                                                        }
                                                    }
                                             if(!intval($data_array['post_parent'])){
					$pquery = "select * from $wpdb->posts where post_title = '{$data_array['post_parent']}' and post_status != 'trash'";	
						
						
						$post = $wpdb->get_results($pquery,ARRAY_A);

						$data_array['post_parent']=$post[0]['ID'];
					}
						$retID = wp_insert_post($data_array); // Insert the core fields for the specific post type.

						if(is_wp_error($retID) || $retID == '') {
							$this->setSkippedRowCount($this->getSkippedRowCount() + 1);
							if(is_wp_error($retID)) {
								$uci_admin->detailed_log[$uci_admin->processing_row_id]['Message'] = "Can't insert this " . $data_array['post_type'] . ". " . $retID->get_error_message();
								return array('MODE' => $mode, 'ERROR_MSG' => $retID->get_error_message());
							} else {
								$uci_admin->detailed_log[ $uci_admin->processing_row_id ]['Message'] = "Can't insert this " . $data_array['post_type'];
								return array( 'MODE' => $mode, 'ERROR_MSG' => "Can't insert this " . $data_array['post_type'] );
							}
						} else {
							// WPML support on post types
							global $sitepress;
							if($sitepress != null) {
								$this->UCI_WPML_Supported_Posts($data_array, $retID);
							}
						}
						$_SESSION[$eventKey]['summary']['inserted'][] = $retID;
						$this->setInsertedRowCount($this->getInsertedRowCount() + 1);
						$uci_admin->detailed_log[$uci_admin->processing_row_id]['Message'] = 'Inserted ' . $data_array['post_type'] . ' ID: ' . $retID . ', ' . $assigned_author;
					}
					else {
						if ( ($mode == 'Update' || $mode == 'Schedule') && $is_update == true ) {
							$ID_result = $wpdb->get_results($duplicate_check_query);
							if (is_array($ID_result) && !empty($ID_result)) {
								$retID = $ID_result[0]->ID;
								$mode_of_affect = 'Skipped';
								$_SESSION[$eventKey]['summary']['skipped'][] = $retID;
								$this->setSkippedRowCount($this->getSkippedRowCount() + 1);
								$uci_admin->detailed_log[$uci_admin->processing_row_id]['Message'] = 'Skipped ' . $data_array['post_type'] . ' ID: ' . $retID . ', Due to the duplicate found!';
								return array( 'MODE' => $mode, 'ERROR_MSG' => 'Skipped ' . $data_array['post_type'] . ' ID: ' . $retID . ', Due to the duplicate found!' );
							} else {
								$retID = wp_insert_post($data_array);
								if(is_wp_error($retID) || $retID == '') {
									$this->setSkippedRowCount($this->getSkippedRowCount() + 1);
									if(is_wp_error($retID)) {
										$uci_admin->detailed_log[$uci_admin->processing_row_id]['Message'] = "Can't insert this " . $data_array['post_type'] . ". " . $retID->get_error_message();
										return array('MODE' => $mode, 'ERROR_MSG' => $retID->get_error_message());
									} else {
										$uci_admin->detailed_log[ $uci_admin->processing_row_id ]['Message'] = "Can't insert this " . $data_array['post_type'];
										return array( 'MODE' => $mode, 'ERROR_MSG' => "Can't insert this " . $data_array['post_type'] );
									}
								}
								$_SESSION[$eventKey]['summary']['inserted'][] = $retID;
								$this->setInsertedRowCount($this->getInsertedRowCount() + 1);
								$uci_admin->detailed_log[$uci_admin->processing_row_id]['Message'] = 'Inserted ' . $data_array['post_type'] . ' ID: ' . $retID . ', ' . $assigned_author;
							}
						} else {
							$retID = wp_insert_post($data_array);
							$_SESSION[$eventKey]['summary']['inserted'][] = $retID;
							$this->setInsertedRowCount($this->getInsertedRowCount() + 1);
							$uci_admin->detailed_log[$uci_admin->processing_row_id]['Message'] = 'Inserted ' . $data_array['post_type'] . ' ID: ' . $retID . ', ' . $assigned_author;
						}
					}

					if (!empty($post_format)) {
						wp_set_object_terms($retID, $post_format, 'post_format');
					}
					$shortcodes = array();
					$media_handle = isset($duplicateHandling['media_handling']) ? $duplicateHandling['media_handling'] : array();
					/* Page template */
					if($data_array['post_type'] == 'page') {
						if(isset($data_array['wp_page_template'])) {
							$page_template = $data_array['wp_page_template'];
						}
						else {
							$page_template = "";
						}
						update_post_meta($retID, '_wp_page_template', $page_template);
					}

					#TODO: Need to import the media for scheduler
					/* Set Featured Image */
					if(isset($data_array['featured_image'])) {
						if ( preg_match_all( '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)[-A-Z0-9+&@#\/%=~_|$?!:,.]*[A-Z0-9+&@#\/%=~_|$]/i', $data_array['featured_image'], $matchedlist, PREG_PATTERN_ORDER ) ) {
							$nextGenInfo           = array();
							$media_settings = $this->parse_media_settings($mediaConfig, $data_array);
							$featured_image_info = array(
								'value'           => trim($data_array['featured_image']),
								//'rename_image' => $renameimage,
								'nextgen_gallery' => $nextGenInfo,
								'media_settings'  => $media_settings
							);
							update_option( 'smack_featured_' . $retID, $featured_image_info );
						}
					}
					$uci_admin->detailed_log[$uci_admin->processing_row_id]['Status'] = $data_array['post_status'];
					$returnArr['ID'] = $retID;
					$returnArr['MODE'] = $mode_of_affect;
					if (!empty($data_array['post_author'])) {
						$returnArr['AUTHOR'] = isset($assigned_author) ? $assigned_author : '';
					}
					break;
			}
		}
		//Import type Not a ticket
		if($importType == 'ticket') {
			$data_array['post_type'] = 'ticket';
			$retID = $data_array['ID'];
			$returnArr['ID'] = $retID;
			$returnArr['MODE'] = $mode_of_affect;
		}

		#NOTE: Removed feature to import events manager data
		return $returnArr;
	}

	public function importDataForWPMetaFields ($data_array, $pID, $importType) {
		$createdFields = array();
		if(!empty($data_array)) {
			foreach ($data_array as $custom_key => $custom_value) {
				$createdFields[] = $custom_key;
				if($importType != 'Users') {
					// Modified by Fredrick Marks - Serialized value support added
					if(is_serialized($custom_value)) {
						update_post_meta($pID, $custom_key, unserialize($custom_value));
					} else {
						update_post_meta($pID, $custom_key, $custom_value);
					}
				} else {
					update_user_meta($pID, $custom_key, $custom_value);
				}
			}
		}
		return $createdFields;

	}

	public function CFSFields(){
		global $wpdb;
		$customFields = $cfs_field = array();
		$get_cfs_groups = $wpdb->get_results($wpdb->prepare("select ID from $wpdb->posts where post_type = %s", 'cfs'),ARRAY_A);
		$group_id_arr = '';
		foreach ( $get_cfs_groups as $item => $group_rules ) {
			$group_id_arr .= $group_rules['ID'] . ',';
		}
		if($group_id_arr != '') {
			$group_id_arr = substr( $group_id_arr, 0, - 1 );
			// Get available CFS fields based on the import type and group id
			$get_cfs_fields = $wpdb->get_results( $wpdb->prepare("SELECT meta_value FROM $wpdb->postmeta WHERE post_id IN (%s) and meta_key =%s ",$group_id_arr,'cfs_fields'), ARRAY_A);
		}
		// Available CFS fields
		if (!empty($get_cfs_fields)) {
			foreach ($get_cfs_fields as $key => $value) {
				$get_cfs_field = @unserialize($value['meta_value']);
				foreach($get_cfs_field as $fk => $fv){
					$customFields["CFS"][$fv['name']]['label'] = $fv['label'];
					$customFields["CFS"][$fv['name']]['name'] = $fv['name'];
					$customFields["CFS"][$fv['name']]['type'] = $fv['type'];
					$customFields["CFS"][$fv['name']]['fieldid'] = $fv['id'];
					$cfs_field[] = $fv['name'];
				}
			}
		}
		return $customFields;
	}


	public function assignTermsAndTaxonomies($categories, $category_name, $pID) {
		$get_category_list = $category_list = array();
		// Create / Assign categories to the post types
		if (!empty($categories)) {
			foreach ( $categories as $cat_key => $cat_value ) {
				if (strpos($cat_value, '|') !== false) {
					$get_category_list = explode('|', $cat_value);
				} elseif (strpos($cat_value, ',') !== false) {
					$get_category_list = explode(',', $cat_value);
				} else {
					$get_category_list[] = $cat_value;
				}
			}
		}
		if(!empty($get_category_list)) {
			$i = 0;
			foreach($get_category_list as $key => $value) {
				if (strpos($value, '->') !== false) {
					$split_line = explode('->', $value);
					if(is_array($split_line)) {
						foreach($split_line as $category) {
							$category_list[$i][] = $category;
						}
					}
				} else {
					$category_list[$i][] = $value;
				}
				$i++;
			}
		}
		foreach($category_list as $index => $category_set) {
			foreach ( $category_set as $item => $category_value ) {
				$term_children_options= get_option( "$category_name" . "_children" );
				$parentTerm           = $item;
				$termName             = trim( $category_value );
				$_name                = (string) $termName;
				$_slug                = preg_replace( '/\s\s+/', '-', strtolower( $_name ) );
				$checkAvailable       = array();
				$checkSuperParent     = $checkParent1 = $checkParent2 = null;
				$super_parent_term_id = $parent_term_id1 = $parent_term_id2 = 0;
				#$get_term_id          = term_exists( "$_name", "$category_name" );
				if ( $parentTerm != 0 ) {
					if ( isset( $category_set[ $item - 1 ] ) ) {
						$checkParent1 = trim( $category_set[ $item - 1 ] );
						$checkParent1 = (string) $checkParent1;
						$parent_term  = term_exists( "$checkParent1", "$category_name" );
						if ( isset( $parent_term['term_id'] ) ) {
							$parent_term_id1 = $parent_term['term_id'];
						}
					}
					if ( isset( $category_set[ $item - 2 ] ) ) {
						$parent_term_id1   = 0;
						$checkSuperParent  = trim( $category_set[ $item - 2 ] );
						$checkSuperParent  = (string) $checkSuperParent;
						$super_parent_term = term_exists( "$checkSuperParent", "$category_name" );
						if ( isset( $super_parent_term['term_id'] ) ) {
							$super_parent_term_id = $super_parent_term['term_id'];
						}
						$checkParent2 = trim( $category_set[ $item - 1 ] );
						$checkParent2 = (string) $checkParent2;
						$parent_term  = term_exists( "$checkParent2", "$category_name", $super_parent_term_id );
						if ( isset( $parent_term['term_id'] ) ) {
							$parent_term_id2 = $parent_term['term_id'];
						}
					}
				}
				if ( $super_parent_term_id != 0 ) {
					if ( $parent_term_id2 == 0 ) {
						$checkAvailable = term_exists( "$checkParent2", "$category_name" );
						if ( ! is_array( $checkAvailable ) ) {
							$taxonomyID          = wp_insert_term( "$checkParent2", "$category_name", array(
								'description' => '',
								'slug'        => $_slug,
								'parent'      => $super_parent_term_id
							) );
							$parent_term_id2 = $retID = $taxonomyID['term_id'];
							wp_set_object_terms( $pID, $retID, $category_name, true );
						} else {
							$exist_term_id = array( $checkAvailable['term_id'] );
							$exist_term_id = array_map( 'intval', $exist_term_id );
							$exist_term_id = array_unique( $exist_term_id );
							$parent_term_id2 = $checkAvailable['term_id'];
							wp_set_object_terms( $pID, $exist_term_id, $category_name, true );
						}
					}
					unset( $checkAvailable );
					$checkAvailable = term_exists( "$_name", "$category_name", $parent_term_id2 );
					if ( ! is_array( $checkAvailable ) ) {
						$taxonomyID = wp_insert_term( "$_name", "$category_name", array(
							'description' => '',
							'slug'        => $_slug,
							'parent'      => $parent_term_id2
						) );
						$retID  = $taxonomyID['term_id'];
						wp_set_object_terms( $pID, $retID, $category_name, true );
					} else {
						$exist_term_id = array( $checkAvailable['term_id'] );
						$exist_term_id = array_map( 'intval', $exist_term_id );
						$exist_term_id = array_unique( $exist_term_id );
						wp_set_object_terms( $pID, $exist_term_id, $category_name, true );
					}
					unset( $checkAvailable );
				}
				elseif ( $parent_term_id1 != 0 ) {
					$checkAvailable = term_exists( "$_name", "$category_name", $parent_term_id1 );
					if ( ! is_array( $checkAvailable ) ) {
						$taxonomyID = wp_insert_term( "$_name", "$category_name", array(
							'description' => '',
							'slug'        => $_slug,
							'parent'      => $parent_term_id1
						) );
						$retID  = $taxonomyID['term_id'];
						wp_set_object_terms( $pID, $retID, $category_name, true );
					} else {
						$exist_term_id = array( $checkAvailable['term_id'] );
						$exist_term_id = array_map( 'intval', $exist_term_id );
						$exist_term_id = array_unique( $exist_term_id );
						wp_set_object_terms( $pID, $exist_term_id, $category_name, true );
					}
					unset( $checkAvailable );
				}
				elseif ( $super_parent_term_id == 0 && $parent_term_id2 == 0 && $parent_term_id1 == 0 ) {
					$checkAvailable = term_exists( "$_name", "$category_name" );
					if ( !is_array( $checkAvailable ) ) {
						$taxonomyID = wp_insert_term( "$_name", "$category_name", array(
							'description' => '',
							'slug'        => $_slug,
						) );
						$retID  = $taxonomyID['term_id'];
						wp_set_object_terms( $pID, $retID, $category_name, true );
					} else {
						$exist_term_id = array( $checkAvailable['term_id'] );
						$exist_term_id = array_map( 'intval', $exist_term_id );
						$exist_term_id = array_unique( $exist_term_id );
						wp_set_object_terms( $pID, $exist_term_id, $category_name, true );
					}
					unset( $checkAvailable );
				}
				#if ( ! is_wp_error( $retID ) ) {
					update_option( "$category_name" . "_children", $term_children_options );
					delete_option( $category_name . "_children" );
				#}
				$categoryData[] = (string) $category_value;
			}
		}
	}

	public function importTermsAndTaxonomies ($data_array, $pID, $type) {
		global $uci_admin;
		unset($data_array['post_format']);
		$categories = $tags = array();
		foreach ($data_array as $termKey => $termVal) {
			$smack_taxonomy = array();
			switch ($termKey) {
				case 'post_category' :
					$categories [$termKey] = $data_array [$termKey];
					$uci_admin->detailed_log[$uci_admin->processing_row_id]['Categories'] = $data_array[$termKey];
					$category_name = 'category';
					// Create / Assign categories to the post types
					if(isset($categories[$termKey]) && $categories[$termKey] != '')
						$this->assignTermsAndTaxonomies($categories, $category_name, $pID);
					//Get Default Category id
					$default_category_id = get_option('default_category');
					//Get Default Category Name
					$default_category_details = get_term_by('id', $default_category_id , 'category');
					//Remove Default Category
					$categories = wp_get_object_terms($pID, 'category');
					if (count($categories) > 1) {
						foreach ($categories as $key => $category) {
							if ($category->name == $default_category_details->name ) {
								wp_remove_object_terms($pID, $default_category_details->name , 'category');
							}
						}
					}
					break;
				case 'post_tag' :
					$tags [$termKey] = $data_array [$termKey];
					$uci_admin->detailed_log[$uci_admin->processing_row_id]['Tags'] = $data_array[$termKey];
					$tag_name = 'post_tag';
					break;
				case 'product_tag':
					$tags [$termKey] = $data_array [$termKey];
					$uci_admin->detailed_log[$uci_admin->processing_row_id]['Tags'] = $data_array[$termKey];
					$tag_name = 'product_tag';
					break;
				case 'product_category':
					if($type === 'MarketPress')
						$category_name = 'product_category';
					if($type == 'WooCommerce')
						$category_name = 'product_cat';
					if($type == 'WPeCommerce')
						$category_name = 'wpsc_product_category';
					$categories [$termKey] = $data_array [$termKey];
					$uci_admin->detailed_log[$uci_admin->processing_row_id]['Categories'] = $data_array[$termKey];
					// Create / Assign categories to the post types
					if(isset($categories[$termKey]) && $categories[$termKey] != '')
						$this->assignTermsAndTaxonomies($categories, $category_name, $pID);
					break;
				case 'event_tags':
					$eventtags [$termKey] = $data_array [$termKey];
					if(!empty($eventtags)){
						$uci_admin->detailed_log[$uci_admin->processing_row_id]['Tags'] = $data_array[$termKey];
						foreach($eventtags as $e_key => $e_value){
							if(!empty($e_value)){
								if (strpos($e_value, '|') !== false) {
									$split_etag = explode('|', $e_value);
								} elseif (strpos($e_value, ',') !== false) {
									$split_etag = explode(',', $e_value);
								} else {
									$split_etag = $e_value;
								}
								if(is_array($split_etag)) {
									foreach($split_etag as $item) {
										$etagData[] = (string)$item;
									}
								} else {
									$etagData = (string)$split_etag;
								}
								#$split_line = explode('|', $ec_value);
								wp_set_object_terms($pID, $etagData,'event-tags');
							}
						}
					}
					break;
				case 'event_categories':
					$event_categories [$termKey] = $data_array [$termKey];
					if(!empty($event_categories)) {
						$uci_admin->detailed_log[$uci_admin->processing_row_id]['Categories'] = $data_array[$termKey];
						foreach($event_categories as $ec_key => $ec_value){
							if(!empty($ec_value)) {
								if (strpos($ec_value, '|') !== false) {
									$split_ecat = explode('|', $ec_value);
								} elseif (strpos($ec_value, ',') !== false) {
									$split_ecat = explode(',', $ec_value);
								} else {
									$split_ecat = $ec_value;
								}
								if(is_array($split_ecat)) {
									foreach($split_ecat as $item) {
										$ecatData[] = (string)$item;
									}
								} else {
									$ecatData = (string)$split_ecat;
								}
								#$split_line = explode('|', $ec_value);
								wp_set_object_terms($pID, $ecatData,'event-categories');
							}
						}
					}
					break;
				default :
					$smack_taxonomy[$termKey] = $data_array[$termKey];

					if($termKey != 'post_format')
						$uci_admin->detailed_log[$uci_admin->processing_row_id][$termKey] = $data_array[$termKey];

					$taxonomy_name = $termKey;

					// Create / Assign taxonomies to the post types
					if(isset($smack_taxonomy[$termKey]) && $smack_taxonomy[$termKey] != '')
						$this->assignTermsAndTaxonomies($smack_taxonomy, $taxonomy_name, $pID);
					break;
			}
		}

		// Create / Assign tags to the post types
		if (!empty ($tags)) {
			foreach ($tags as $tag_key => $tag_value) {
				if (!empty($tag_value)) {
					if (strpos($tag_value, '|') !== false) {
						$split_tag = explode('|', $tag_value);
					} elseif (strpos($tag_value, ',') !== false) {
						$split_tag = explode(',', $tag_value);
					} else {
						$split_tag = $tag_value;
					}
					if(is_array($split_tag)) {
						foreach($split_tag as $item) {
							$tag_list[] = $item;
						}
					} else {
						$tag_list = $split_tag;
					}
					#wp_set_post_tags($pID, $posttag);
					wp_set_object_terms($pID, $tag_list, $tag_name);
				}
			}
		}

	}

	/**
	 * @param $available_groups_type
	 * @param $mapping_records
	 * @param $data_rows
	 * @return mixed
	 */
	public function generateDataArrayBasedOnGroups($available_groups_type = null, $mapping_records = null, $data_rows = null, $method = 'advanced'){
		//new mapped array
		$import_dataArr = $current_mapped_check_serialize = array();
		if(!empty($available_groups_type)) {
			if($method == 'normal' || $method == ''):
			foreach ($available_groups_type as $groupname => $groupvalue) {
				if(!empty($mapping_records)) {
					foreach ( $mapping_records as $mapping_key => $mapping_value ) {
						$current_mapped_group_mapkey = explode( $groupvalue . '__mapping', $mapping_key );
						$current_mapped_group_key    = explode( $groupvalue . '__fieldname', $mapping_key );
						//Serialize check
                                                if( $groupvalue == 'CORECUSTFIELDS' ){
                                                        $current_mapped_check_serialize = explode( $groupvalue . '__SerializeVal', $mapping_key );
                                                }
						$current_static_group_key    = explode( $groupvalue . '_statictext_mapping', $mapping_key );
						$current_formula_group_key   = explode( $groupvalue . '_formulatext_mapping', $mapping_key );
						if ( is_array( $current_mapped_group_mapkey ) && count( $current_mapped_group_mapkey ) == 2 ) {
							$set_mapping_groups[ $groupvalue ][] = $mapping_value;
						}
						if ( is_array( $current_mapped_group_key ) && count( $current_mapped_group_key ) == 2 ) {
							$set_fields_group[ $groupvalue ][] = $mapping_value;
							$current_row_val                   = $mapping_value;
						}
						//serialize
                                                if ( is_array( $current_mapped_check_serialize ) && count( $current_mapped_check_serialize ) == 2 ) {
                                                        $serialize_index = substr( $mapping_key , -1);
                                                        $set_fields_serialize[ 'SerializeVal' ][$serialize_index] = $mapping_value;
                                                        $current_row_val                   = $mapping_value;
                                                }
						//static and formula features
						if ( is_array( $current_static_group_key ) && count( $current_static_group_key ) == 2 ) {
							$set_static_group[ $groupvalue ][ $current_row_val ] = $mapping_value;
						}
						if ( is_array( $current_formula_group_key ) && count( $current_formula_group_key ) == 2 ) {
							$set_formula_group[ $groupvalue ][ $current_row_val ] = $mapping_value;
						}

					}
				}
				if (!empty($set_fields_group[$groupvalue]) && !empty($set_mapping_groups[$groupvalue])) {
					$new_mapped_array[$groupvalue] = array_combine($set_fields_group[$groupvalue], $set_mapping_groups[$groupvalue]);
				}
				//static and formula features
				if(!empty($set_static_group[$groupvalue] )) {
					foreach ($set_static_group[$groupvalue] as $grp => $val) {
						if (!empty($new_mapped_array[$groupvalue]) && array_key_exists($grp, $new_mapped_array[$groupvalue])) {
							$new_mapped_array[$groupvalue][$grp] = $val;
						}
					}
				}
				if(!empty($set_formula_group[$groupvalue] )) {
					foreach ($set_formula_group[$groupvalue] as $grp => $val) {
						if (!empty($new_mapped_array[$groupvalue]) && array_key_exists($grp, $new_mapped_array[$groupvalue])) {
							$new_mapped_array[$groupvalue][$grp] = $val;
						}
					}
				}
				//serialized
                                if(!empty($set_fields_serialize['SerializeVal'] )) {
                                        foreach ($set_fields_serialize['SerializeVal'] as $grp => $val) {
                                                        $new_mapped_array['SerializeVal'][$set_fields_group['CORECUSTFIELDS'][$grp]] = $val;
                                        }
                                        $available_groups_type['SerializeVal'] = 'SerializeVal';
                                }
			}
			elseif($method == 'advanced'):
				foreach ($available_groups_type as $groupname => $groupvalue) {
					if(!empty($mapping_records)) {
						foreach ( $mapping_records as $mapping_key => $mapping_value ) {
							$current_mapping_field = explode($groupvalue . '__', $mapping_key);
							if (is_array($current_mapping_field) && count($current_mapping_field) == 2) {
								$new_mapped_array[ $groupvalue ][ $current_mapping_field[1] ] = $mapping_value;
								$set_static_group[ $groupvalue ][ $current_mapping_field[1] ] = $mapping_value;
							}
						}
					}
				}
			endif;
			//Empty rows checking
			foreach ($available_groups_type as $groupname => $groupvalue) {
				if (!empty($new_mapped_array[$groupvalue])) {
					foreach ($new_mapped_array[$groupvalue] as $mpkey => $mpval) {
						if ($mpval != '-- Select --' && $mpval != 'select') {
							if(isset($set_static_group[$groupvalue]) && in_array($mpval, $set_static_group[$groupvalue])) {
								$pattern = "/({([a-z A-Z 0-9 | , _ -]+)(.*?)(}))/";
								preg_match_all($pattern, $mpval, $results, PREG_PATTERN_ORDER);
								for($i=0; $i<count($results[2]); $i++) {
									$oldWord = $results[0][$i];
									$get_val = $results[2][$i];
									//TODO xml
									if(isset($data_rows[$get_val])) {
										$newWord = $data_rows[$get_val];
									} else {
										$newWord = $get_val;
									}
									$mpval = str_replace($oldWord, ' ' . $newWord, $mpval);
								}
								//$mpval = str_replace('+',' ', $mpval);
								if(trim($mpval) != '')
									$import_dataArr[$groupvalue][$mpkey] = $mpval;
							} elseif(isset($set_formula_group[$groupvalue]) && in_array($mpval, $set_formula_group[$groupvalue])) {
								$pattern = "/({([\w]+)(.*?)(}))/";
								preg_match_all($pattern, $mpval, $results, PREG_PATTERN_ORDER);
								for($i=0; $i<count($results[2]); $i++) {
									$oldWord = $results[0][$i];
									$get_val = $results[2][$i];
									//TODO xml
									if(isset($data_rows[$get_val])) {
										$newWord = $data_rows[$get_val];
									} else {
										$newWord = $get_val;
									}
									$mpval = str_replace($oldWord, $newWord, $mpval);
								}
								if(!empty($mpval) && $mpval != '' && $mpval != null) {
									$result = $this->evalmath($mpval);
								} else {
									$result = 0;
								}
								if($result == "false"){
									$result = $mpval;
								}
								if($result != 0)
									$import_dataArr[$groupvalue][$mpkey] = $result;
							} else {
								if ($mpval == 'publish') {
									$result = 'publish';
								} else{
									if($groupvalue == 'SerializeVal'){
										$import_dataArr['SerializeVal'][$mpkey] = $mpval;
										$result = '';
									} else{
										if (!empty($data_rows) && array_key_exists($mpval, $data_rows)) {
											$result = $data_rows[$mpval];
										} else {
											$result = '';
										}
									}
								}
								if($result != '')
									$import_dataArr[$groupvalue][$mpkey] = $result;
							}

						}
					}
				}
			}
		}
		return $import_dataArr;
	}

	public function saveAdvancedTemplate($uci_admin, $templateName = null) {
		#NOTE: Removed save mapping template feature
	}

	/**
	 * @param $uci_admin
	 * @param null $templateName
	 */
	public function saveTemplate ($uci_admin, $templateName = null) {
		#NOTE: Removed save mapping template feature
	}

	public function get_mapping_screendata($module,$post_values){
		global $uci_admin;
		$available_group = $uci_admin->available_widgets($module, '');
		foreach ($available_group as $groupname => $groupvalue) {
			foreach ($post_values as $mapping_key => $mapping_value) {
				$current_mapped_group_mapkey = explode($groupvalue . '__mapping', $mapping_key);
				$current_mapped_group_key = explode($groupvalue . '__fieldname', $mapping_key);
				$current_static_group_key = explode($groupvalue . '_statictext_mapping', $mapping_key);
				$current_formula_group_key = explode($groupvalue . '_formulatext_mapping', $mapping_key);
				if (is_array($current_mapped_group_mapkey) && count($current_mapped_group_mapkey) == 2) {
					$set_mapping_groups[$groupvalue][] = $mapping_value;
				}
				if (is_array($current_mapped_group_key) && count($current_mapped_group_key) == 2) {
					$set_fields_group[$groupvalue][] = $mapping_value;
					$current_row_val = $mapping_value;
				}
				//static and formula features
				if(is_array($current_static_group_key) && count($current_static_group_key) == 2){
					$set_static_group[$groupvalue][$current_row_val] = $mapping_value;
				}
				if(is_array($current_formula_group_key) && count($current_formula_group_key) == 2){
					$set_formula_group[$groupvalue][$current_row_val] = $mapping_value;
				}

			}
			if (!empty($set_fields_group[$groupvalue]) && !empty($set_mapping_groups[$groupvalue])) {
				$new_mapped_array[$groupvalue] = array_combine($set_fields_group[$groupvalue], $set_mapping_groups[$groupvalue]);
			}
			//static and formula features
			if(!empty($set_static_group[$groupvalue] )) {
				foreach ($set_static_group[$groupvalue] as $grp => $val) {
					if (!empty($new_mapped_array) && array_key_exists($grp, $new_mapped_array[$groupvalue])) {
						$new_mapped_array[$groupvalue][$grp] = $val;
					}
				}
			}
			if(!empty($set_formula_group[$groupvalue] )) {
				foreach ($set_formula_group[$groupvalue] as $grp => $val) {
					if (!empty($new_mapped_array) && array_key_exists($grp, $new_mapped_array[$groupvalue])) {
						$new_mapped_array[$groupvalue][$grp] = $val;
					}
				}
			}
		}
		return $new_mapped_array;
	}

	/**
	 * @param $filename
	 * @param $version
	 * @return string
	 */
	public function get_realname($filename,$version) {
		$extension = explode('.',$filename);
		$extension = $extension[count($extension) - 1];
		$filename = explode('-'.$version,$filename);
		return $filename[0] . '.' . $extension;
	}

	public function setPriority($filename = null, $eventkey = null, $uci_admin = null, $headers = array()) {
		#NOTE: Removed suggested template based on priority
	}

	public function filter_template($filterdata) {
		#NOTE: Removed search template from suggested priorities
	}

	public function get_from_user_details($request_user) {
		global $wpdb;
		// Author name/id update
		$authorLen = strlen($request_user);
		$checkpostuserid = intval($request_user);
		$postAuthorLen = strlen($checkpostuserid);
		if ($authorLen == $postAuthorLen) {
			$postauthor = $wpdb->get_results($wpdb->prepare("select ID,user_login from $wpdb->users where ID = %s",$request_user));
			if (empty($postauthor) || !$postauthor[0]->ID) { // If user name are numeric Ex: 1300001
				$postauthor = $wpdb->get_results($wpdb->prepare("select ID,user_login from $wpdb->users where user_login = %s",$request_user));
			}
		} else {
			$postauthor = $wpdb->get_results($wpdb->prepare("select ID,user_login from $wpdb->users where user_login = %s",$request_user));
		}

		if (empty($postauthor) || !$postauthor[0]->ID) {
			$request_user = 1;
			$admindet = $wpdb->get_results($wpdb->prepare("select ID,user_login from $wpdb->users where ID = %d",'1'));
			$message = " , <b>Author :- </b> not found (assigned to <b>" . $admindet[0]->user_login . "</b>)";
		} else {
			$request_user = $postauthor[0]->ID;
			$admindet = $wpdb->get_results($wpdb->prepare("select ID,user_login from $wpdb->users where ID = %s",$request_user));
			$message = " , <b>Author :- </b>" . $admindet[0]->user_login;
		}
		$userDetails['user_id'] = $request_user;
		$userDetails['user_login'] = $admindet[0]->user_login;
		$userDetails['message'] = $message;
		return $userDetails;
	}

	/**
	 * Function to get roles for users
	 * @param bool $capability
	 *
	 * @return array
	 */
	public function getRoles($capability = null) {
		global $wp_roles;
		$roles = array();
		if($capability != null) {
			foreach ( $wp_roles->roles as $rkey => $rval ) {
				$roles[ $rkey ] = '';
				for ( $cnt = 0; $cnt < count( $rval['capabilities'] ); $cnt ++ ) {
					$findval = "level_" . $cnt;
					if ( array_key_exists( $findval, $rval['capabilities'] ) ) {
						$roles[ $rkey ] = $roles[ $rkey ] . $cnt . ',';
					}
				}
			}
		} else {
			if ( ! isset( $wp_roles ) )
				$wp_roles = new WP_Roles();

			$roles = $wp_roles->get_names();
		}
		return $roles;
	}

	// function to get the google_map address
	public function get_latitude_longitude($address = null, $region = null) {
		$address = str_replace(" ", "+", $address);
		$json = @file_get_contents("http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false&region=$region");
		$json = json_decode($json);
		if(!empty($json->results)) {
			$lat  = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};
			$long = $json->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};
			return $lat . ',' . $long;
		} else {
			return false;
		}
	}

	public function get_requested_term_details ($post_id, $term) {
		$termLen = strlen($term);
		$checktermid = intval($term);
		$verifiedTermLen = strlen($checktermid);
		if($termLen == $verifiedTermLen) {
			return $term;
		} else {
			$reg_term_id = wp_set_object_terms($post_id, $term, 'category');
			$term_id = $reg_term_id[0];
			return $term_id;
		}
	}

	public function assign_post_status($data_array) {
		if (isset($data_array['is_post_status']) && $data_array['is_post_status'] != 'on') {
			$data_array ['post_status'] = $data_array['is_post_status'];
			unset($data_array['is_post_status']);
		}
		if (isset($data_array ['post_type']) && $data_array ['post_type'] == 'page') {
			$data_array ['post_status'] = 'publish';
		} else {
			if(isset($data_array['post_status']) || isset($data_array['coupon_status'])) {
				if(isset($data_array['post_status'])) {
					$data_array['post_status'] = strtolower( $data_array['post_status'] );
				} else {
					$data_array['post_status'] = strtolower( $data_array['coupon_status'] );
				}
				$data_array['post_status'] = trim($data_array['post_status']);
				if ($data_array['post_status'] != 'publish' && $data_array['post_status'] != 'private' && $data_array['post_status'] != 'draft' && $data_array['post_status'] != 'pending' && $data_array['post_status'] != 'sticky') {
					$stripPSF = strpos($data_array['post_status'], '{');
					if ($stripPSF === 0) {
						$poststatus = substr($data_array['post_status'], 1);
						$stripPSL = substr($poststatus, -1);
						if ($stripPSL == '}') {
							$postpwd = substr($poststatus, 0, -1);
							$data_array['post_status'] = 'publish';
							$data_array ['post_password'] = $postpwd;
						} else {
							$data_array['post_status'] = 'publish';
							$data_array ['post_password'] = $poststatus;
						}
					} else {
						$data_array['post_status'] = 'publish';
					}
				}
				if ($data_array['post_status'] == 'sticky') {
					$data_array['post_status'] = 'publish';
					$sticky = true;
				}
				else {
				}
			} else {
				$data_array['post_status'] = 'publish';
			}
		}
		return $data_array;
	}

	public function manage_records($manage_records, $fileInfo, $eventKey, $type_of_import, $mode, $eventInfo){
		global $wpdb;
		$uploadpath = SM_UCI_IMPORT_DIR;
		$imported_on = date('Y-m-d h:i:s');
		$file_name = $fileInfo['file_name'];
		$version = $fileInfo['revision'];
		$month = date("M", strtotime($imported_on));
		$year = date("Y", strtotime($imported_on));
		$versionFile = '/smack_uci_uploads/imports/' . $eventKey . '/' . $eventKey; //.'.txt';
		$module = $this->import_post_types($type_of_import, '');
		$dbrecords = $wpdb->get_results($wpdb->prepare("select *from smackuci_events where eventKey = %s", $eventKey ));
		if(count($dbrecords) == 0){
			$wpdb->insert('smackuci_events', array(
				'revision' => $fileInfo['revision'],
				'name' => "{$fileInfo['file_name']}",
				'original_file_name' => "{$fileInfo['original_file_name']}",
				'import_type' => "{$type_of_import}",
				'filetype' => "{$fileInfo['file_type']}",
				'filepath' => "{$versionFile}",
				'eventKey' => "{$eventKey}",
				'registered_on' => $imported_on,
				'processing' => 1,
				'count' => $eventInfo['count'],
				'processed' => $eventInfo['processed'],
				'created' => $eventInfo['inserted'],
				'updated' => $eventInfo['updated'],
				'skipped' => $eventInfo['skipped'],
				'last_activity' => $imported_on,
				'month' => $month,
				'year' => $year
			),
				array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%d', '%s','%s','%s')
			);
			$id = $wpdb->insert_id;
		} else {
			$id = $dbrecords[0]->id;
			$details = array(
				'processed' => $eventInfo['processed'],
				'created' => $eventInfo['inserted'],
				'updated' => $eventInfo['updated'],
				'skipped' => $eventInfo['skipped']
			);
			$condition = array('id' => $id);
			$wpdb->update('smackuci_events', $details, $condition, array('%d', '%d', '%d', '%d'), array('%d'));
		}
		#$this->saveMode($eventKey, $id, $type_of_import, $fileInfo);
		return $id;
	}

	/*public function duplicate_check($duplicate_header){
		global $wpdb;
		$duplicate_header = array(
			'post_title' => 'post1',
			'post_content' => 'sample description',
			'post_excerpt' => 'post-1',
			'post_type' => 'post',
		);
		$condition = implode(' and ', array_map(
			function ($record_val, $wp_fields) { return $wp_fields . '=' . "'".$record_val."'"; },
			$duplicate_header,
			array_keys($duplicate_header)
		));
		$post_exist = $wpdb->get_results("select ID from $wpdb->posts where $condition and post_status not in ('trash','auto-draft','inherit')");
		if(count($post_exist) == 0){
			return true;
		}
		else {
			return false;
		}
	}*/

	public function serverReq_data(){
		$record_arr = array();
		$split_range = array();
		$initial_recordcount = 1000;
		$minimum_record = 0;
		$filesize = 8000;
		//$tot_records = count($record_arr);
		$tot_records = 10;
		$executiontime = ini_get('max_execution_time');
		$memorysize = ini_get('memory_limit');
		if($tot_records < $initial_recordcount) {
			$current_processtime = ($tot_records / 20) * 60;
		}
		else {
			$current_processtime = (($tot_records / 1000) * 5) * 60;
		}
		if($executiontime < $current_processtime){
			$time_in_minute = round($executiontime / 60 , 3);
			if($executiontime > 300) {
				$server_req = 1000 * ($time_in_minute / 5); // 1000 record takes 5 minutes i.e 300s
			}
			else {
				$server_req = 150 * $time_in_minute; // 150 record takes 1 minute.
			}
			$server_req = $server_req / 2;
		}
		else {
			$server_req = $tot_records / 2;
		}
		if($server_req != 0){
			// convert to neareast 10.
			$quotient_val = (int)$server_req / 10;
			$remainder_val = (int)$server_req % 10;
			if($remainder_val != 0){
				$server_req = (intval($quotient_val) + 1) * 10;
			}
			// Split the range
			$count = 0;
			for($i = $server_req; $count < 10; $i++) {
				if($i > 20) {
					$split_range[] = $i - 20;
					$i -= 20;
				}
				else {
					$split_range[] = $i;
					break;
				}
				$count++;
			}
		}
		return $split_range;
	}

	public function saveMode($eventkey, $id, $type_of_import, $fileInfo) {
		global $wpdb, $uci_admin;
		$summary = $uci_admin->getEventInformation();
                $inserted = $summary['inserted'];
                $updated = $summary['updated'];
                $skipped = $summary['skipped'];
		$event_info = array();
		$filename = $fileInfo['original_file_name'];
		$event_info['eventkey'] = $eventkey;
		$event_info['inserted'] = $inserted;
		$event_info['updated'] = $updated;
		$event_info['skipped'] = $skipped;
		$event_info['eventid'] = $id;
		$event_info['filename'] = $filename;
		$event_info['module'] = $type_of_import;
	}

	public function convert_slug($Name =null){
		$label = trim($Name);
		$slug = strtolower($label);
		$slug = preg_replace("/[^a-zA-Z0-9._\s]/", "", $slug);
		$slug = preg_replace('/\s/', '-', $slug);
		return $slug;
	}

	public function capture_shortcodes($post_content,$pID,$mode,$media_handle){
		global $wpdb;
		if($mode == 'Inline') {
			$pattern = "/([WPIMPINLINE:([\w]+)(.*?)(])/";
			$shortcode_prefix = "[WPIMPINLINE:";
		} else if($mode == 'Featured') {
			$pattern = "/([WPIMPFEATURED:([\w]+)(.*?)(])/";
			$shortcode_prefix = "[WPIMPFEATURED:";
		}
		$post_content = str_replace("\n", "<br />", $post_content);
		preg_match_all($pattern, $post_content, $results, PREG_PATTERN_ORDER);
		$inlineimg_shortcodes = array();
		$shortcodelist = array();
		$inline_shortcode_count = 0;
		if($mode == 'Inline') {
			for($i=0; $i<count($results[0]); $i++){
				$get_shortcode_pos = strpos($results[0][$i], $shortcode_prefix);
				$inlineimg_shortcodes[] = substr($results[0][$i], $get_shortcode_pos);
			}
		}elseif($mode == 'Featured'){
			$inline_shortcode_count = count($results[0]);
			if($inline_shortcode_count == 1) {
				$inlineimg_shortcodes[] = $results[0][0];
			}
		}
		if(!empty($inlineimg_shortcodes)) {
			foreach($inlineimg_shortcodes as $shortkey => $shortcode) {
				 $shortcodelist[$pID][$shortkey] = $shortcode;
			}
		}
		return $shortcodelist;
	}
	
	public function scanDirectories($rootDir, $allData=array()){
	      // set filenames invisible if you want
		$invisibleFileNames = array(".", "..", ".htaccess", ".htpasswd");
		// run through content of root directory
		if(!is_dir($rootDir))
			 return false;
		       $dirContent = scandir($rootDir);
		 foreach($dirContent as $key => $content) {
			// filter all files not accessible
			$path = $rootDir.'/'.$content;
			if(!in_array($content, $invisibleFileNames)) {
				// if content is file & readable, add to array
				if(is_file($path) && is_readable($path)) {
					// save file name with path
					$allData[] = $path;
					// if content is a directory and readable, add path and name
				}elseif(is_dir($path) && is_readable($path)) {
					// recursive callback to open new directory
					$allData = $this->scanDirectories($path, $allData);
				}
			}
		}
		return $allData;
	}

	/**
	 * @param $f_img
	 * @param $post_id
	 * @param null $media_handle
	 * @return integer
	 */
	public function set_featureimage($f_img, $post_id, $media_handle = null){
		/* Image available in media */
		$useexistingimages = 'false';
		if(!empty($media_handle['imageprocess']) && $media_handle['imageprocess'] == 'duplicateimageoption'){
			$useexistingimages = 'true';
		}
		$dir = wp_upload_dir();
		$get_media_settings = get_option('uploads_use_yearmonth_folders');
		if ($get_media_settings == 1) {
			$dirname = date('Y') . '/' . date('m');
			$uploaddir_path = $dir ['basedir'] . '/' . $dirname;
			$uploaddir_url = $dir ['baseurl'] . '/' . $dirname;
		} else {
			$uploaddir_path = $dir ['basedir'];
			$uploaddir_url = $dir ['baseurl'];
		}
		$fimg_name = @basename($f_img);
		$fimg_name = str_replace(' ', '-', trim($fimg_name));
		$fimg_name = preg_replace('/[^a-zA-Z0-9._\-\s]/', '', $fimg_name);
		$fimg_name = urlencode($fimg_name);
		//$parseURL = parse_url($f_img);
		$path_parts = pathinfo($f_img);
		if (!isset($path_parts['extension'])) {
			$fimg_name = $fimg_name . '.jpg';
		}
		//$f_img_slug = '';
		$featured_image = trim($path_parts['filename']);
		/*$f_img_slug = strtolower(str_replace(' ', '-', trim($f_img_slug)));
		$f_img_slug = preg_replace('/[^a-zA-Z0-9._\-\s]/', '', $f_img_slug);
		$post_slug_value = strtolower($f_img_slug);*/
		#if (array_key_exists('extension', $path_parts)) {
		if ($useexistingimages == 'false') {
			$fimg_name = wp_unique_filename($uploaddir_path, trim($fimg_name));
		}
		$this->get_images_from_url($f_img, $uploaddir_path, $fimg_name);
		$filepath = $uploaddir_path . "/" . $fimg_name;
		$attach_id = '';
		if (@getimagesize($filepath)) {
			$file ['guid'] = $uploaddir_url . "/" . $fimg_name;
			$file ['post_title'] = $featured_image;
			$file ['post_content'] = '';
			$file ['post_status']  = 'inherit';
			$file ['post_type'] = 'attachment';
		} else {
			$file = false;
		}
		if (!empty ($file)) {
			global $wpdb;
			$attachment = array('guid' => $file ['guid'], 'post_mime_type' => 'image/jpeg', 'post_title' => preg_replace('/\.[^.]+$/', '', @basename($file ['guid'])), 'post_content' => '', 'post_status' => 'inherit');
			if ($get_media_settings == 1) {
				$generate_attachment = $dirname . '/' . $fimg_name;
			} else {
				$generate_attachment = $fimg_name;
			}
			$uploadedImage = $dir['path'] . '/' . $fimg_name;
			$existing_attachment = array();
			$query = $wpdb->get_results($wpdb->prepare("select post_title from $wpdb->posts where post_type = %s and post_mime_type = %s",'attachment','image/jpeg'));
			if(!empty($query)) {
				foreach ( $query as $key ) {
					$existing_attachment[] = $key->post_title;
				}
			}
			if(!empty($media_handle['imageprocess']) && $media_handle['imageprocess'] == 'duplicateimageoption') {
				if ( ! in_array( $attachment['post_title'], $existing_attachment ) ) {
					$attach_id   = wp_insert_attachment( $attachment, $generate_attachment, $post_id );
					$attach_data = wp_generate_attachment_metadata( $attach_id, $uploadedImage );
					wp_update_attachment_metadata( $attach_id, $attach_data );
				} else {
					$query2 = $wpdb->get_results( $wpdb->prepare( "select ID from $wpdb->posts where post_title = %s  and post_type = %s", $attachment['post_title'], 'attachment' ) );
					foreach ( $query2 as $key2 ) {
						$attach_id = $key2->ID;
					}
				}
			}
			elseif(!empty($media_handle['imageprocess']) && $media_handle['imageprocess'] == 'imagenamechangeoption'){
				$renamevalue = $media_handle['renamevalue'];
				$query1 = $wpdb->get_results($wpdb->prepare("select {$renamevalue} from $wpdb->posts where ID = %d",$post_id));
				foreach($query1 as $key){
					//$rename_file = preg_replace("/\s\,/", "-", $key->post_title);
					if(!empty($key->post_title)) {
						$rename_file = preg_replace('/[^ \w]+/', "", $key->post_title);
					}
					else {
						$rename_file = $key->post_name;
					}
					$rename_file = preg_replace('/\s/', '-',$rename_file);
				}
				if(!in_array($rename_file ,$existing_attachment)){
					$attach_id = wp_insert_attachment($attachment, $generate_attachment, $post_id);
					$post = get_post($post_id);
					$file = get_attached_file($attach_id);
					$path = pathinfo($file);
					$newfile = $dir['path'] . '/'.$rename_file.".".$path['extension'];
					rename($file, $newfile);
					update_attached_file( $attach_id, $newfile );
					$updateguid = $uploaddir_url . "/" .$rename_file.".".$path['extension'];
					//$newattachment = array('ID' => $attach_id,'post_content' => '', 'post_title' => preg_replace('/\.[^.]+$/', '', @basename($updateguid)),'post_status' => 'inherit','guid' => $updateguid, 'post_mime_type' => 'image/jpeg','post_type' => 'attachment');
					$wpdb->update($wpdb->posts,array('post_content' => '', 'post_title' => preg_replace('/\.[^.]+$/', '', @basename($updateguid)),'post_status' => 'inherit','guid' => $updateguid, 'post_mime_type' => 'image/jpeg','post_type' => 'attachment'),array('ID'=>$attach_id));
					$attach_data = wp_generate_attachment_metadata($attach_id, $newfile);
					wp_update_attachment_metadata($attach_id, $attach_data);
				}else{
					//$query2 = $wpdb->get_results("select ID from $wpdb->posts where post_title = '$rename_file' and post_type = 'attachment'");
					$query2 = $wpdb->get_results($wpdb->prepare("select ID from $wpdb->posts where post_title = %s and post_type = %s",$rename_file,'attachment'));
					foreach($query2 as $key2){
						$attach_id = $key2->ID;
					}

				}
			}
			else {
				$attach_id = wp_insert_attachment($attachment, $generate_attachment, $post_id);
				$attach_data = wp_generate_attachment_metadata($attach_id, $uploadedImage);
				wp_update_attachment_metadata($attach_id, $attach_data);
			}
			//set_post_thumbnail($post_id, $attach_id);
		}
		return $attach_id;
	}

	function get_images_from_url($f_img, $uploaddir_path, $fimg_name){
		$f_img = str_replace(" ","%20",$f_img);
		if ($uploaddir_path != "" && $uploaddir_path) {
			$uploaddir_path = $uploaddir_path . "/" . $fimg_name;
		}
		$ch = curl_init($f_img);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		$rawdata = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ( $http_code != 200 && strpos( $rawdata, 'Not Found' ) != 0 ) {
			$rawdata = false;
		}
		if ($rawdata == false) {
			return null;
		} else {
			if (file_exists($uploaddir_path)) {
				unlink($uploaddir_path);
			}
			$fp = fopen($uploaddir_path, 'x');
			fwrite($fp, $rawdata);
			fclose($fp);
		}
		curl_close($ch);
	}

	public function evalmath($equation) {
		$result = 0;

		// sanitize imput
		$equation = preg_replace("/[^0-9+\-.*\/()%]/","",$equation);

		// convert percentages to decimal
		$equation = preg_replace("/([+-])([0-9]{1})(%)/","*(1\$1.0\$2)",$equation);
		$equation = preg_replace("/([+-])([0-9]+)(%)/","*(1\$1.\$2)",$equation);
		//$equation = preg_replace("/([0-9]+)(%)/",".\$1",$equation);

		if ( $equation != "" )
		{
			$result = @eval("return " . $equation . ";" );
		}

		if ($result === null)
		{
			throw new Exception("Unable to calculate equation");
		}
		if($result === FALSE){
			$result = 'false';
		}

		return $result;
	}

	public function getFileSize($file) {
		$fileSize = filesize($file);

		if ($fileSize > 1024 && $fileSize < (1024 * 1024)) {
			$fileSize = round(($fileSize / 1024), 2) . ' kb';
		}
		else if ($fileSize > (1024 * 1024)) {
			$fileSize = round(($fileSize / (1024 * 1024)), 2) . ' mb';
		}
		else {
			$fileSize = $fileSize . ' bytes';
		}

		return $fileSize;
	}

	/**
	 * @param $field_info
	 * Check the Mandatory Custom Fields
	 */
	public function Required_CF_Fields($field_info) {
		#NOTE: Removed check required fields for ACF, PODS & Toolset Types fields
	}

	// WPML support on posts
	public function UCI_WPML_Supported_Posts ($data_array, $pId) {
		#Note: Removed multilingual support on data import
	}

	public function getCSVHeader($field_name) {
		$eventKey = isset($_REQUEST['eventkey']) ? sanitize_key($_REQUEST['eventkey']) : '';
		$headers = array();
		$filePath = SM_UCI_IMPORT_DIR . '/' . $eventKey . '/' . $eventKey;

		if(file_exists($filePath)) {
			$parserObj = new SmackCSVParser();
			$parserObj->parseCSV( $filePath, 0, - 1 );
			$headers = $parserObj->get_CSVheaders();
			$headers = $headers[0];
			$prefix = 'img_seo_'; #$mapping_count = 0;

			$header_fields = "<select style='width:35%; height:47px;float: right;margin: -32px 22px;' class='search_dropdown_mapping selectpicker' id='" . $prefix . "mapping_for_" . $field_name . "' name='" . $prefix . "mapping_for_" . $field_name . "' disabled='disabled'>";
			$header_fields .= "<option> --Select-- </option>";
			foreach($headers as $hKey) {
				$header_fields .= "<option value='" . $hKey . "'>" . $hKey . "</option>";
			}
			$header_fields .= "</select>";
		}
		return $header_fields;
	}

	public function parse_media_settings($get_media_settings, $data_array) {

		$data_array = $this->getRowData();
		if ( isset( $data_array['nextgen-gallery'] ) && $get_media_settings['nextgen_media_handling'] == 'on' ) {
			$nextGenInfo = array(
				'status'    => 'enabled',
				'directory' => $data_array['nextgen-gallery'],
			);
		}
		$media_settings = array();

		if(isset($get_media_settings['imageprocess']) && $get_media_settings['imageprocess'] == 'use_existing_images') {
			$media_settings['media_process'] = 'use_existing_images';
		} elseif(isset($get_media_settings['imageprocess']) && $get_media_settings['imageprocess'] == 'overwrite_existing_images') {
			$media_settings['media_process'] = 'overwrite_existing_images';
		}
		if(isset($get_media_settings['media_seo_title']) && $get_media_settings['media_seo_title'] == 'on') {
			$media_settings['title'] = $data_array[$get_media_settings['img_seo_mapping_for_title']];
		}
		if(isset($get_media_settings['media_seo_caption']) && $get_media_settings['media_seo_caption'] == 'on') {
			$media_settings['caption'] = $data_array[$get_media_settings['img_seo_mapping_for_caption']];
		}
		if(isset($get_media_settings['media_seo_alttext']) && $get_media_settings['media_seo_alttext'] == 'on') {
			$media_settings['alttext'] = $data_array[$get_media_settings['img_seo_mapping_for_alttext']];
		}
		if(isset($get_media_settings['media_seo_description']) && $get_media_settings['media_seo_description'] == 'on') {
			$media_settings['description'] = $data_array[$get_media_settings['img_seo_mapping_for_description']];
		}
		if(isset($get_media_settings['change_media_file_name']) && $get_media_settings['change_media_file_name'] == 'on') {
			$media_settings['imageName'] = $data_array[$get_media_settings['img_seo_mapping_for_imageName']];
		}
		if(isset($get_media_settings['media_thumbnail_size']) && $get_media_settings['media_thumbnail_size'] == 'on') {
			$media_settings['thumbnail'] = 'on';
		}
		if(isset($get_media_settings['media_medium_size']) && $get_media_settings['media_medium_size'] == 'on') {
			$media_settings['medium'] = 'on';
		}
		if(isset($get_media_settings['media_medium_large_size']) && $get_media_settings['media_medium_large_size'] == 'on') {
			$media_settings['medium_large'] = 'on';
		}
		if(isset($get_media_settings['media_large_size']) && $get_media_settings['media_large_size'] == 'on') {
			$media_settings['large'] = 'on';
		}
		if(isset($get_media_settings['media_custom_width']) && isset($get_media_settings['media_custom_height'])) {
			$media_settings['custom_width'] = $get_media_settings['media_custom_width'];
			$media_settings['custom_height'] = $get_media_settings['media_custom_height'];
		}

		return $media_settings;
	}

	public function updateMaintenance($value)
	{
		$mode = array();
		$mode['enable_main_mode'] = $value;
		update_option('sm_uci_pro_settings', $mode);
	}

	// Push core fields data into database
	public function core_information_into_db ($mode, $data_array) {

	}

}
