<?php

require_once('base_class.wp_widget_slp.php');


if ( ! class_exists( 'SLPWidget_states' ) ) {

    /**
     * Class SLPWidget_states
     *
     * Display the Store Locator Plus states in a sidebar.
     *
     * @see http://codex.wordpress.org/Widgets_API
     */
    class SLPWidget_states extends WP_Widget_SLPExp {

        /**
         * Set the properties for this widget.
         */
        public function configure_widget_properties() {
            $this->slug = 'states';
            $this->description_admin = __('List the states for your locations and link to the map or listing page.', 'slp-experience');
            $this->title_admin = __('SLP States', 'slp-experience');
            $this->title_ui = __('We Are In These States', 'slp-experience');
        }
    }
}