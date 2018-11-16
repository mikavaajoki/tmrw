<?php
require_once('base_class.wp_widget_slp.php');

/**
 * Class SLPWidget_search_and_results extends WP_Widget
 *
 * Display an address search and show a list of locations in a widget area.
 */
class SLPWidget_search_and_results extends WP_Widget_SLPExp {

    /**
     * Configure the settings for this particular widget.
     */
    protected function configure_widget_settings() {

       // Turn off the map url full setting
       //
       $this->settings['map_url_full']->admin_input = 'none';

       $this->settings['immediate'] = new SLP_Widget_Setting( array(
            'slug'          => 'immediate' ,
            'admin_input'   => 'checkbox' ,
            'admin_label'   => __( 'Immediate Search:' , 'slp-experience' ),
            'default_value' => '0' ,
            ));


        $this->settings['immediate_address'] = new SLP_Widget_Setting( array(
            'slug'          => 'immediate_address' ,
            'admin_input'   => 'input' ,
            'admin_label'   => __( 'Immediate Address:'          , 'slp-experience' ),
            'default_value' => '' ,
            ));

        $this->settings['max_results'] = new SLP_Widget_Setting( array(
            'slug'          => 'max_results' ,
            'admin_input'   => 'input' ,
            'admin_label'   => __( 'Maximum Results to display:' , 'slp-experience' ),
            'default_value' => '3' ,
            ));

        $this->settings['search_label'] = new SLP_Widget_Setting( array(
            'slug'          => 'search_label' ,
            'admin_input'   => 'input' ,
            'admin_label'   => __( 'Search Label:'               , 'slp-experience' ),
            'default_value' => __( 'Address:' , 'slp-experience' ) ,
            ));

        $this->settings['use_placeholder'] = new SLP_Widget_Setting( array(
            'slug'          => 'use_placeholder' ,
            'admin_input'   => 'checkbox' ,
            'admin_label'   => __( 'Search Label As Placeholder:', 'slp-experience' ),
            'default_value' => '0' ,
            ));

        $this->settings['radius_label'] = new SLP_Widget_Setting( array(
            'slug'          => 'radius_label' ,
            'admin_input'   => 'input' ,
            'admin_label'   => __( 'Radius Label:'               , 'slp-experience' ),
            'default_value' => __( 'Within:' , 'slp-experience') ,
            ));

        $this->settings['radius'] = new SLP_Widget_Setting( array(
            'slug'          => 'radius' ,
            'admin_input'   => 'input' ,
            'admin_label'   => __( 'Default Radius:'              , 'slp-experience' ),
            'default_value' => '100' ,
            ));

        $this->settings['none_found'] = new SLP_Widget_Setting( array(
            'slug'          => 'none_found' ,
            'admin_input'   => 'input' ,
            'admin_label'   => __( 'Nothing Found Text:'          , 'slp-experience' ),
            'default_value' => __( 'No locations found.'          , 'slp-experience' ) ,
            ));

        $this->settings['simple_output'] = new SLP_Widget_Setting( array(
            'slug'          => 'simple_output' ,
            'admin_input'   => 'checkbox' ,
            'admin_label'   => __( 'Use Simple Output:'           , 'slp-experience' ),
            'default_value' => '0' ,
            ));
    }

    /**
     * Set the properties for this widget.
     */
    public function configure_widget_properties()  {
        $this->slug                   = 'search_and_results';
        $this->description_admin  = __('The Store Locator Plus search form, with results appearing below the form.', 'slp-experience');
        $this->title_admin        = __('SLP Search and Results' , 'slp-experience');
        $this->title_ui           = __('Find Nearby Locations'  , 'slp-experience');
    }

}