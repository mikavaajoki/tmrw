<?php
/*
Plugin Name: Store Locator Plus - Janitor
Plugin URI: https://www.storelocatorplus.com/
Description: A free add-on to assist in clean up of settings for the Store Locator Plus plugin.
Author: Store Locator Plus
Author URI: https://www.storelocatorplus.com
License: GPL3


Text Domain: slp-janitor
Domain Path: /languages/
Copyright 2012 - 2017  Charleston Software Associates (info@storelocatorplus.com)


Tested up to: 4.9
Version: 4.9
*/

function SLPJanitor_loader() {
    if (class_exists('SLPlus')) {
        require_once(SLPLUS_PLUGINDIR . 'include/base_class.addon.php');

        /**
         * Janitor
         *
         * @property    SLPJanitor          $addon
         * @property    SLPJanitor_Admin    $admin
         * @property    SLPJanitor          $instance   static for singleton model
         */
        class SLPJanitor extends SLP_BaseClass_Addon {
            public $addon;
            public static $instance;

            /**
             * Invoke the plugin as singleton.
             *
             * @static
             */
            public static function init() {
                static $instance = false;
                if (!$instance) {
                    load_plugin_textdomain('slp-janitor', false, dirname(plugin_basename(__FILE__)) . '/languages/');
                    $instance = new SLPJanitor(
                        array(
                            'version' => '4.9',
                            'min_slp_version' => '4.9',
                            'name' => __('Janitor', 'slp-janitor'),
                            'option_name' => 'csl-slplus-JANITOR-options',
                            'file' => __FILE__,
                            'admin_class_name' => 'SLPJanitor_Admin'
                        )
                    );
                }
                return $instance;
            }

            /**
             * Add the tabs/main menu items.
             *
             * @param mixed[] $menu_items
             * @return mixed[]
             */
            function filter_AddMenuItems($menu_items) {
                $this->admin_menu_entries = array(array(
                    'label' => $this->name,
                    'slug' => $this->addon->short_slug,
                    'class' => $this->admin,
                    'function' => 'render_AdminPage'
                ));
                return parent::filter_AddMenuItems($menu_items);
            }
        }

        SLPJanitor::init();
    }
}
add_action('plugins_loaded' , 'SLPJanitor_loader' );
