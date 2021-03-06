<?php
/*
Plugin Name: Store Locator Plus™ - Experience
Plugin URI: https://wordpress.storelocatorplus.com/product/experience/
Description: Extends the Store Locator Plus™ plugin with features that allow site authors and developers to create a customized robust user experience.
Author: Store Locator Plus™
Author URI: https://wordpress.storelocatorplus.com

Text Domain: slp-experience
Domain Path: /languages/

Copyright 2015 - 2018  Charleston Software Associates (support@storelocatorplus.com)

Tested up to: 4.9.7
Version: 4.9.17
*/
defined( 'ABSPATH' ) or exit;
if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $_POST[ 'action' ] ) && ( $_POST[ 'action' ] === 'heartbeat' ) )
    return;

function SLP_Experience_loader() {
    defined( 'SLP_EXPERIENCE_MIN_SLP' ) || define( 'SLP_EXPERIENCE_MIN_SLP' , '4.9.17'  );
    defined( 'SLP_EXPERIENCE_FILE'    ) || define( 'SLP_EXPERIENCE_FILE'    , __FILE__ );
    require_once( 'include/base/loader.php' );
}

add_action( 'plugins_loaded' , 'SLP_Experience_loader' );
