<?php

if ( ! class_exists('SLP_Widget_Setting') ) {
	require_once( SLPLUS_PLUGINDIR . 'include/base_class.object.php');

	/**
	 * Class SLP_Widget_Setting
	 *
	 * The properties that define a widget setting.
	 *
	 * @package StoreLocatorPlus\Experience\Widget\Setting
	 * @author Lance Cleveland <lance@charlestonsw.com>
	 * @copyright 2015 Charleston Software Associates, LLC
	 *
	 * @property string $admin_input		'none' || 'text' || 'checkbox'
	 * @property string $admin_label		label for the admin input
	 * @property string $default_value 		the default value if not set in the instance
	 * @property string $slug 				the slug
	 *
	 * @since 4.4.00
	 */
	class SLP_Widget_Setting extends SLPlus_BaseClass_Object {
		public $admin_input;
		public $admin_label;
		public $default_value;
		public $slug;
	}

}