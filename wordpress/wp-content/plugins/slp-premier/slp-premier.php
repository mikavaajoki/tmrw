<?php
/*
Plugin Name: Store Locator Plus™ - Premier
Plugin URI: https://wordpress.storelocatorplus.com/product/premier-subscription/
Description: Add Premier Subscription features to the Store Locator Plus™ plugin.
Author: Store Locator Plus™
Author URI: https://wordpress.storelocatorplus.com/

Text Domain: slp-premier
Domain Path: /languages/

Copyright 2016 - 2018 Charleston Software Associates (support@storelocatorplus.com)

Tested up to : 4.9.7
Version: 4.9.17
*/
defined( 'ABSPATH' ) or exit;
if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $_POST[ 'action' ] ) && ( $_POST[ 'action' ] === 'heartbeat' ) )
    return;

function SLP_Premier_loader() {
    defined( 'SLP_PREMIER_MIN_SLP' ) || define( 'SLP_PREMIER_MIN_SLP' , '4.9.17'  );
    defined( 'SLP_PREMIER_FILE'    ) || define( 'SLP_PREMIER_FILE'    , __FILE__ );
    require_once( 'include/base/loader.php' );
}

add_action( 'plugins_loaded' , 'SLP_Premier_loader' );

