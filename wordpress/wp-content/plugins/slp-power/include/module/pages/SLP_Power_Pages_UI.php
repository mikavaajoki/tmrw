<?php
defined( 'ABSPATH' ) || exit;

/**
 * UI pages stuff on loaded of using pages is enabled.
 *
 * @property    SLP_Power_UI                         $ui
 */
class SLP_Power_Pages_UI extends SLPlus_BaseClass_Object {
    public $ui;

    /**
     * Add WP Hooks and Filters.
     *
     * @uses \SLP_Power_Pages_UI_Shortcode_storepage::process_shortcode
     * @uses \SLP_Power_Pages_UI_Shortcode_slp_pages::process_shortcode
     * @uses \SLP_Power_Pages_UI::fetch_location_for_post
     */
    public function add_hooks_and_filters() {
        add_shortcode( 'storepage' , array( SLP_Power_Pages_UI_Shortcode_storepage::get_instance() , 'process_shortcode' ) );
        add_shortcode( 'slp_pages' , array( SLP_Power_Pages_UI_Shortcode_slp_pages::get_instance() , 'process_shortcode' ) );
	    SLP_UI_Shortcode_slp_location::get_instance();
        if ( $this->has_pages_content() ) {
            add_action( 'the_post' , array( $this, 'fetch_location_for_post' ) );
            $this->setup_css_and_js();
        }
    }

    /**
     * Set the location data for each run through the custom post types in a loop (or a single display).
     *
     * @used-by \SLP_Power_Pages_UI::add_hooks_and_filters
     * @param WP_Post $post_object
     */
    public function fetch_location_for_post( $post_object ) {
        $current_location_array = $this->slplus->database->get_Record( array( 'selectall' , 'wherelinkedpostid' ) , $post_object->ID );
        $this->slplus->currentLocation->set_PropertiesViaArray( $current_location_array );
    }

    /**
     * Returns true if current page has Pages content.
     */
    private function has_pages_content() {
        if ( get_post_type() === SLPlus::locationPostType ) { return true; }

        $post = get_post();
        if ( ! is_a( $post , 'WP_Post' ) ) { return false; }
        $content = $post->post_content;
        if ( has_shortcode( $content , 'slp_pages' ) ) { return true; }
        if ( has_shortcode( $content , 'storepage' ) ) { return true; }
	    if ( has_shortcode( $content , 'slp_location' ) ) { return true; }

        return false;
    }

    /**
     * Enqueue the CSS and JS as needed.
     */
    public function setup_css_and_js() {
    	$main_addons = array( 'Experience' , 'Premier' , 'Power' );
    	foreach ( $main_addons as $slug ) {
		    $addon = $this->slplus->addon( $slug );
		    if ( ! empty( $addon ) && ! empty( $addon->userinterface_class_name ) ) {
		    	if ( $slug === 'Power' ) {
				    $ui = $this->ui;
			    } else {
				    $ui_class = $addon->userinterface_class_name;
				    $ui       = $ui_class::get_instance();
			    }

			    $ui->js_settings            = array_merge( $this->slplus->options, $addon->options );
			    $ui->js_settings['ajaxurl'] = admin_url( 'admin-ajax.php' );
			    $ui->js_settings['resturl'] = rest_url( 'store-locator-plus/v1/' );
			    $ui->enqueue_ui_javascript();
			    $ui->enqueue_ui_css();
		    }
	    }

	    defined( 'SLPLUS_SCRIPTS_MANAGED' ) || define( 'SLPLUS_SCRIPTS_MANAGED' , true );
	    SLP_Actions::get_instance()->wp_enqueue_scripts();
	    SLP_UI::get_instance()->localize_script();
	    wp_enqueue_script( 'slp_core' );
    }
}
