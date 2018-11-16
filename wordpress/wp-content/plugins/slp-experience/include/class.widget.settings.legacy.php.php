<?php
if (! class_exists('SLPWidget_Legacy_Settings')) {
    /**
     * Class SLPWidget_Settings
     *
     * @package StoreLocatorPlus\Experience\Admin
     * @author Lance Cleveland <lance@charlestonsw.com>
     * @copyright 2015 Charleston Software Associates, LLC
     *
     * Text Domain: slp-experience
     *
     * @property-read mixed $settings_array This is the settings array (do not modify directly)
     * @property string     $maximum_result_set The default maximum number of results to return
     * @property     string     $default_title  The default title of the widget
     * @property    string  $radius_label   The radius field label.
     * @property     string     $search_label   The label of the search
     * @property     string    $button_label    The label of the button
     */
    class SLPWidget_Legacy_Settings extends SLPlus_BaseClass_Object {
        private $settings_array;
        public $maximum_result_set;
        public $default_title;
        public $radius_label;
        public $search_label;
        public $button_label;

        /**
         * Create a settings class
         */
        function initialize() {
            add_filter("slp_widget_default_options", array($this, "options"));
            add_filter("slp_widget_get_settings"    , array($this, "getSettings"));

            $this->settings_array = apply_filters("slp_widget_default_options", array());
            foreach ($this->settings_array as $setting => $setto) {
                $this->$setting = $setto;
            }
        }

        /**
         * Loads default options or gets them from the db if they already exist
         * @var mixed $default The default options to load
         * @return mixed The options from the db or the defaults
         */
        function options($default) {
            $default = array_merge(
                $default,
                array(
                    "maximum_result_set" => "3",
                    "default_title" => __("Find nearby locations", 'slp-experience'),
                    'radius_label' => __('Within', 'slp-experience'),
                    "search_label" => __("Zip Code", 'slp-experience'),
                    "button_label" => __("Go!", 'slp-experience'),
                )
            );

            return $default;
        }

        /**
         * @param $var
         */
        public function applySettings($var) {
            foreach ($var as $setting => $value) {
                if (isset($this->$setting))
                    $this->$setting = $value;
            }
        }

        /**
         * Reduces the class's settings to an array and returns them
         * @var mixed $settings Other settings to merge these with (for addons)
         * @return mixed The settings array
         */
        function getSettings($settings) {
            $this->reduceSettings();
            return array_merge($settings, $this->settings_array);
        }

        /**
         * Gets the plugin settings
         * @return array This plugin's settings class
         */
        public static function Settings() {
            $settings = apply_filters( 'slp_widget_settings' , array() );
            return $settings[0];
        }

        /**
         * Reduces all class vars into an array for saving or distribution
         */
        private function reduceSettings() {
            foreach ($this->settings_array as $setting => $setto) {
                $this->settings_array[$setting] = $this->$setting;
            }
        }

    }
}