<?php
require_once('base_class.wp_widget_slp.php');

if ( ! class_exists( 'SLPWidget_cities' ) ) {

    /**
     * Class SLPWidget_cities
     *
     * Display the Store Locator Plus state drop down followed by cities.
     * Forwards the user to a specified page that has the pslplus] shortcode on it.
     *
     * @see http://codex.wordpress.org/Widgets_API
     */
    class SLPWidget_cities extends WP_Widget_SLPExp {

        /**
         * Set the properties for this widget.
         */
        public function configure_widget_properties() {
            $this->slug = 'cities';
            $this->description_admin = __('List the cities for your locations and link to the map or listing page.', 'slp-experience');
            $this->title_admin = __('SLP Cities', 'slp-experience');
            $this->title_ui = __('We Are In These Cities', 'slp-experience');
        }

        /**
         * Configure the settings for this particular widget.
         */
        protected function configure_widget_settings() {
            $this->settings['all_cities_text'] = new SLP_Widget_Setting(array(
                'slug' => 'all_cities_text',
                'admin_input' => 'input',
                'admin_label' => __('All Cities Text', 'slp-experience'),
                'default_value' => __('All Cities', 'slp-experience'),
            ));

            $this->settings['hide_city_until_needed'] = new SLP_Widget_Setting(array(
                'slug' => 'hide_city_until_needed',
                'admin_input' => 'checkbox',
                'admin_label' => __('Hide Cities At Start', 'slp-experience'),
                'default_value' => '0',
            ));

        }
    }

}