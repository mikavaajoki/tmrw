<?php
/*
Plugin Name: Store Locator Plus™ - Power
Plugin URI: https://wordpress.storelocatorplus.com/product/power/
Description: Adds power user features include location import, categorization, and SEO pages to Store Locator Plus™.
Author: Store Locator Plus™
Author URI: https://www.storelocatorplus.com

Text Domain: slp-power
Domain Path: /languages/

Copyright 2016 - 2018 Charleston Software Associates (support@storelocatorplus.com)

Tested up to: 4.9.8
Version: 4.9.20
*/
defined( 'ABSPATH' ) || exit;
if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $_POST[ 'action' ] ) && ( $_POST[ 'action' ] === 'heartbeat' ) ) {
	return;
}

function SLP_Power_loader() {
	defined( 'SLP_POWER_MIN_SLP' ) || define( 'SLP_POWER_MIN_SLP' , '4.9.17'  );
	defined( 'SLP_POWER_FILE'    ) || define( 'SLP_POWER_FILE'    , __FILE__ );
	require_once( 'include/base/loader.php' );
}

add_action( 'plugins_loaded' , 'SLP_Power_loader' );

