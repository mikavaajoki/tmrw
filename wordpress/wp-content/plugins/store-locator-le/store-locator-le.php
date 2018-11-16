<?php
/*
Plugin Name: Store Locator Plus™
Plugin URI: https://www.storelocatorplus.com/
Description: Add a location finder or directory to your site in minutes. Extensive add-on library available!
Author: Store Locator Plus™
Author URI: https://www.storelocatorplus.com
License: GPL3

Text Domain: store-locator-le

Copyright 2012 - 2018  Charleston Software Associates (info@storelocatorplus.com)

Tested up to: 4.9.8
Version: 4.9.19
*/
defined( 'ABSPATH' ) || exit;
if ( defined( 'SLPLUS_VERSION' ) ) return;
defined( 'SLPLUS_VERSION'  ) || define( 'SLPLUS_VERSION'  , '4.9.19' );
defined( 'SLPLUS_NAME'     ) || define( 'SLPLUS_NAME'     , __( 'Store Locator Plus™', 'store-locator-le' ) );
defined( 'SLP_LOADER_FILE' ) || define( 'SLP_LOADER_FILE' , __FILE__ );

// Detect WP Heartbeat
defined( 'SLP_DETECTED_HEARTBEAT' ) || define( 'SLP_DETECTED_HEARTBEAT' , ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $_POST[ 'action' ] ) && ( $_POST[ 'action' ] === 'heartbeat' ) ) );

require_once( 'include/base/loader.php' );

if ( ! slp_passed_requirements() ) return;

slp_setup_environment();
require_once( 'include/SLPlus.php' );