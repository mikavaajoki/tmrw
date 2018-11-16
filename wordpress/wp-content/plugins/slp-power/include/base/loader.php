<?php
if ( ! function_exists( 'get_plugins' ) ) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
$all_plugins = get_plugins();
$this_plugin = $all_plugins[ plugin_basename( SLP_POWER_FILE ) ];
$min_wp_version   = '4.4';

if ( ! defined( 'SLPLUS_PLUGINDIR' ) ) {
	add_action(
		'admin_notices',
		create_function(
			'',
			"echo '<div class=\"error\"><p>" .
			sprintf(
				__( '%s requires Store Locator Plus to function properly. ', 'slp-power' ),
				$this_plugin['Name']
			) . '<br/>' .
			__( 'This plugin has been deactivated.', 'slp-power' ) .
			__( 'Please install Store Locator Plus.', 'slp-power' ) .
			"</p></div>';"
		)
	);
	deactivate_plugins( plugin_basename( SLP_POWER_FILE ) );

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
				__( '%s requires WordPress %s to function properly. ', 'slp-power' ),
				$this_plugin['Name'],
				$min_wp_version
			) .
			__( 'This plugin has been deactivated.', 'slp-power' ) .
			__( 'Please upgrade WordPress.', 'slp-power' ) .
			"</p></div>';"
		)
	);
	deactivate_plugins( plugin_basename( SLP_POWER_FILE ) );

	return;
}


if ( ! defined( 'SLPPOWER_REL_DIR'  ) ) define( 'SLPPOWER_REL_DIR', plugin_dir_path( SLP_POWER_FILE ) );
if ( ! defined( 'SLP_POWER_VERSION' ) ) define( 'SLP_POWER_VERSION', $this_plugin[ 'Version'] );

// Go forth and sprout your tentacles...
// Get some Store Locator Plus sauce.
//
require_once( SLPPOWER_REL_DIR . 'include/SLPPower.php' );
SLPPower::init();