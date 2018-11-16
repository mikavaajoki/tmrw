<?php
if ( ! function_exists( 'get_plugins' ) ) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
$all_plugins = get_plugins();
$this_plugin = $all_plugins[ plugin_basename( SLP_EXPERIENCE_FILE ) ];
$min_wp_version   = '4.4';

if ( ! defined( 'SLPLUS_PLUGINDIR' ) ) {
	add_action(
		'admin_notices',
		create_function(
			'',
			"echo '<div class=\"error\"><p>" .
			sprintf(
				__( '%s requires Store Locator Plus to function properly. ', 'slp-experience' ),
				$this_plugin['Name']
			) . '<br/>' .
			__( 'This plugin has been deactivated.', 'slp-experience' ) .
			__( 'Please install Store Locator Plus.', 'slp-experience' ) .
			"</p></div>';"
		)
	);
	deactivate_plugins( plugin_basename( SLP_EXPERIENCE_FILE ) );

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
				__( '%s requires WordPress %s to function properly. ', 'slp-experience' ),
				$this_plugin['Name'],
				$min_wp_version
			) .
			__( 'This plugin has been deactivated.', 'slp-experience' ) .
			__( 'Please upgrade WordPress.', 'slp-experience' ) .
			"</p></div>';"
		)
	);
	deactivate_plugins( plugin_basename( SLP_EXPERIENCE_FILE ) );

	return;
}

if ( current_user_can( 'activate_plugins' ) ) {
    $slp_widget_slug = 'slp-widgets/slp-widgets.php';
    if (is_plugin_active($slp_widget_slug)) {
        deactivate_plugins($slp_widget_slug);

        add_action(
            'admin_notices',
            create_function(
                '',
                "echo '<div class=\"error\"><p>" .
                sprintf(
                    __('%s deactivated the conflicting SLP Widget Pack add-on. ', 'slp-experience'),
                    $this_plugin['Name']
                ) .
                "</p></div>';"
            )
        );
        return;
    }
}

if ( ! defined( 'SLP_EXPERIENCE_REL_DIR' ) ) define( 'SLP_EXPERIENCE_REL_DIR', plugin_dir_path( SLP_EXPERIENCE_FILE ) );
if ( ! defined( 'SLP_EXPERIENCE_VERSION' ) ) define( 'SLP_EXPERIENCE_VERSION', $this_plugin[ 'Version'] );

// Go forth and sprout your tentacles...
// Get some Store Locator Plus sauce.
//
require_once( SLP_EXPERIENCE_REL_DIR . 'include/SLP_Experience.php' );
SLP_Experience::init();
