<?php
if ( ! function_exists( 'get_plugins' ) ) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
$all_plugins = get_plugins();
$this_plugin = $all_plugins[ plugin_basename( SLP_PREMIER_FILE ) ];
$min_wp_version   = '4.4';

if ( ! defined( 'SLPLUS_PLUGINDIR' ) ) {
	add_action(
		'admin_notices',
		create_function(
			'',
			"echo '<div class=\"error\"><p>" .
			sprintf(
				__( '%s requires Store Locator Plus to function properly. ', 'slp-premier' ),
				$this_plugin['Name']
			) . '<br/>' .
			__( 'This plugin has been deactivated.', 'slp-premier' ) .
			__( 'Please install Store Locator Plus.', 'slp-premier' ) .
			"</p></div>';"
		)
	);
	deactivate_plugins( plugin_basename( SLP_PREMIER_FILE ) );

	return;
}

global $wp_version;
if ( version_compare( $wp_version, $min_wp_version, '<' ) ) {
	add_action(
		'admin_notices',
		create_function(
			'',
			"echo '<div class=\"error\"><p>" .
			sprintf(
				__( '%s requires WordPress %s to function properly. ', 'slp-premier' ),
				$this_plugin['Name'],
				$min_wp_version
			) .
			__( 'This plugin has been deactivated.', 'slp-premier' ) .
			__( 'Please upgrade WordPress.', 'slp-premier' ) .
			"</p></div>';"
		)
	);
	deactivate_plugins( plugin_basename( SLP_PREMIER_FILE ) );

	return;
}

if ( ! defined( 'SLPPREMIER_REL_DIR'  ) ) define( 'SLPPREMIER_REL_DIR', plugin_dir_path( SLP_PREMIER_FILE ) );
if ( ! defined( 'SLP_PREMIER_VERSION' ) ) define( 'SLP_PREMIER_VERSION', $this_plugin[ 'Version'] );

// Go forth and sprout your tentacles...
// Get some Store Locator Plus sauce.
//
require_once( SLPPREMIER_REL_DIR . 'include/SLP_Premier.php' );
SLP_Premier::init();
