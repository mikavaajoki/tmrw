<?php
defined( 'ABSPATH' ) || exit;
require_once('base_class.wp_widget_slp.php');

/**
 * A basic slp widget
 */
class SLPWidget_search extends WP_Widget_SLPExp {

    /**
     * Set the properties for this widget.
     */
    protected function configure_widget_properties() {
        $this->slug = 'search';
        $this->description_admin = __('Send the user to the map when doing a search.', 'slp-experience');
        $this->title_admin = __('SLP Search Form', 'slp-experience');
        $this->title_ui = __('Find Us', 'slp-experience');
    }

    /**
     * Configure the settings for this particular widget.
     */
    protected function configure_widget_settings() {

        $this->settings['search_label'] = new SLP_Widget_Setting(array(
            'slug' => 'search_label',
            'admin_input' => 'input',
            'admin_label' => __('Search Label', 'slp-experience'),
            'default_value' => __('Address', 'slp-experience'),
        ));

        $this->settings['use_placeholder'] = new SLP_Widget_Setting(array(
            'slug' => 'use_placeholder',
            'admin_input' => 'checkbox',
            'admin_label' => __('Use Placeholder', 'slp-experience'),
            'default_value' => '0',
        ));

        $this->settings['radius'] = new SLP_Widget_Setting(array(
            'slug' => 'radius',
            'admin_input' => 'input',
            'admin_label' => __('Radius', 'slp-experience'),
            'default_value' => '100',
        ));

    }

}
