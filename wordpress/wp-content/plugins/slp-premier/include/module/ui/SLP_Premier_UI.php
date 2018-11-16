<?php
defined( 'ABSPATH' ) || exit;
require_once(SLPLUS_PLUGINDIR.'/include/base_class.userinterface.php');

/**
 * Holds the UI-only code.
 *
 * This allows the main plugin to only include this file in the front end
 * via the wp_enqueue_scripts call.   Reduces the back-end footprint.
 *
 * @property        SLP_Premier                  $addon
 */
class SLP_Premier_UI  extends SLP_BaseClass_UI  {
    public $addon;

	/**
	 * Do this at the start.
	 */
    final function at_startup() {
	    $this->addon = $this->slplus->AddOns->instances['slp-premier'];
	    $this->js_settings = $this->addon->options;

	    SLP_Premier_Category_UI::get_instance();

	    // URL Controls
	    if ( $this->slplus->AddOns->is_premier_subscription_valid() ) {
		    if ( $this->addon->has_url_controls() ) {
			    require_once( SLPPREMIER_REL_DIR . 'include/module/url/SLP_Premier_URL_Control.php' );
		    }
		    if ( $this->slplus->SmartOptions->allow_location_in_url->is_true ) {
			    $this->slplus->Premier_URL_Control->set_center_map_to_location();
		    }
		    if ( $this->slplus->SmartOptions->allow_limit_in_url->is_true ) {
			    $this->slplus->Premier_URL_Control->set_location_limits();
		    }
	    }

	    add_filter( 'shortcode_slp_option'          , array( SLP_UI_Shortcode_slp_option::get_instance() , 'modify'     )       );
	    add_action( 'slp_directory_shortcode_init' , array( $this , 'extend_slp_directory_shortcode' ) );
    }

    /**
     * Manage the UI CSS enqueue process.
     *
     */
    function enqueue_ui_css() {
	    if ( ! $this->slplus->AddOns->is_premier_subscription_valid() ) return;

        // Pagination or Woo
        //
        if (
	        ! empty( $this->slplus->SmartOptions->loading_indicator ) ||
	        $this->slplus->SmartOptions->pagination_enabled->is_true ||
            $this->addon->is_woo_running() ||
            isset( $this->slplus->SmartOptions->show_cats_on_search ) && ( $this->slplus->SmartOptions->show_cats_on_search->value !== 'none' )
        ) {
            parent::enqueue_ui_css();
            wp_enqueue_style('dashicons');
        }
    }

    /**
     * Manage the UI JavaScript enqueue process.
     *
     */
    function enqueue_ui_javascript() {

        // Extend js_settings to include bounds 'boundaries_influence_type'] !== 'none'
        //
        if ( $this->slplus->SmartOptions->boundaries_influence_type->value !== 'none' ) {
            $this->js_settings['bounds'] = $this->addon->set_location_search_boundaries();
        }

        // Extend js_settings to include region if 'boundaries_influence_type' !== 'none'
        //
        if ( $this->slplus->is_CheckTrue( $this->addon->options['region_influence_enabled'] ) ) {
            $this->js_settings['region'] = $this->set_geocoding_region();
        }
        $this->js_settings['url'] = $this->addon->url;

        parent::enqueue_ui_javascript();

        // Add cluster marker scripts if clusters enabled.
        //
        if ( $this->slplus->SmartOptions->clusters_enabled->is_true ) {
            wp_enqueue_script( 'markerclusterer', $this->addon->url . '/js/markerclusterer.min.js', $this->js_requirements , filemtime( $this->addon->dir . '/js/markerclusterer.min.js' ) );
	        wp_enqueue_script( 'slp-premier_marker_cluster', $this->addon->url . '/js/slp-premier_marker_cluster.min.js', ( 'markerclusterer' ) , filemtime( $this->addon->dir . '/js/slp-premier_marker_cluster.min.js' )  );
        }

        // jQuery Selectmenu
        //
        if ( $this->slplus->SmartOptions->dropdown_style !== 'none' ) {
            wp_enqueue_script('jquery-ui-selectmenu');
        }
    }

    /**
     * Things we do when the slp_directory shortcode processing is started by the Power add on.
     */
    public function extend_slp_directory_shortcode() {
		add_filter( 'slp_directory_entry_text' , array( $this, 'process_slp_directory_text_if_blank' ) , 10 , 2 );
    }

    /**
     * Add the text_if_blank shortcode attributet to slp_directory
     *
     * @param string $text
     * @param string[] $shortcode_attributes
     *
     * @return string
     */
    public function process_slp_directory_text_if_blank( $text, $shortcode_attributes ) {
        if ( empty( $text ) && ! empty( $shortcode_attributes[ 'text_if_blank' ] ) ) {
	        return $shortcode_attributes[ 'text_if_blank' ];
        }
        return $text;
    }

    /**
     * Set the geocoding region based on the selected Map Domain cctld.
     *
     * @return string
     */
    private function set_geocoding_region() {
	    require_once( SLPLUS_PLUGINDIR . 'include/module/i18n/SLP_Country_Manager.php' );
		$country = $this->slplus->Country_Manager->countries[ $this->slplus->options_nojs['default_country'] ];
	    return $country->cctld;
    }

}