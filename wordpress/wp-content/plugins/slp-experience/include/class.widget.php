<?php
defined( 'ABSPATH' ) || exit;

/**
 * Holds the admin-only code.
 *
 * This allows the main plugin to only include this file in admin mode
 * via the admin_menu call.   Reduces the front-end footprint.
 *
 * @property        SLP_Experience      $addon                  the parent addon
 * @property-read   boolean             $initial_widget_request is this the initial widget request
 * @property        array               $js_settings            JavaScript settings to be localized
 * @property        SLPWidget_Settings  $settings               our settings array
 * @property        string              $settings_class_name    the settings class name
 * @property-read   boolean             $widget_referral        True if the incoming requeset is from a widget.
 *
 *
 */
class SLP_Experience_Widget extends SLPlus_BaseClass_Object {
    public      $addon;
    private     $initial_widget_request = null;
    public      $settings;
    public      $settings_class_name;
    private     $widget_referral        = null;
    protected   $js_settings = array();

    /**
     * Run these things during invocation. (called from base object in __construct)
     */
    protected function initialize() {
	    add_action( 'widgets_init' , array( $this , 'register_widgets' ) );
	    $this->add_hooks_and_filters();
    }

    /**
     * Add our hooks and filters.
     */
    protected function add_hooks_and_filters() {
        add_filter( 'slp_widget_settings'       , array( $this , 'get_settings'                 ), 200, 0   );
        add_filter( 'slp_search_default_address', array( $this , 'set_SearchAddressFromRequest' )           );
        add_filter( 'slp_map_center'            , array( $this , 'set_SearchCenter'             )           );
        add_action( 'wp_enqueue_scripts'        , array( $this , 'enqueue_javascript'           )           );
    }

    /**
     * Create the settings object.
     */
    function create_object_settings() {
        if (!isset($this->settings)) {
            require_once($this->addon->dir . 'include/class.widget.settings.php');
            $this->settings = new SLPWidget_Legacy_Settings( array( 'addon' => $this->addon ) );
        }
    }

    /**
     * Enqueue JavaScript needed for widgets.
     */
    function enqueue_javascript() {

        // TODO : only if needed for cities drop down
        //
        if ( file_exists( $this->addon->dir . 'include/widgets/slp_widgets.js' ) ) {
            $this->js_settings['ajaxurl'] = admin_url( 'admin-ajax.php' );
            wp_enqueue_script( $this->addon->slug . '_widgets' , $this->addon->url . '/include/widgets/slp_widgets.js' , array( 'jquery' ) );
            wp_localize_script( $this->addon->slug . '_widgets' , 'slp_experience' , $this->js_settings );
        }
    }

    /**
     * Returns a pointer to the settings object
     */
    public function get_settings() {
        $this->create_object_settings();
        return array($this->settings);
    }

    /**
     * Is this the initial widget search?
     *
     * @param   array $var_array The form variables presented via REQUEST.
     *
     * @return bool
     */
    public function is_initial_widget_search( $var_array ) {
        if ( is_null( $this->initial_widget_request ) ) {
            $this->initial_widget_request = $this->is_widget_referral( $var_array );
        }
        return $this->initial_widget_request;
    }

    /**
     * Is this a widget referral?
     *
     * @param   array $var_array The form variables presented via REQUEST.
     *
     * @return bool
     */

    public function is_widget_referral( $var_array ) {
        if ( is_null( $this->widget_referral ) ) {
            $this->widget_referral = isset( $var_array['slp_widget'] );
        }
        return $this->widget_referral;
    }

    /**
     * Register our widgets with WordPress
     *
     * The files must be in ./include/widgets/class.<slug>.php.
     * The classes must be named SLPWidget_<slug> and extend WP_Widget.
     */
    function register_widgets() {
	    if ( ! is_dir( $this->addon->dir . 'include/widgets/' ) ) { return; }
	    $widget_files = scandir( $this->addon->dir . 'include/widgets/' );
	    $widget_slug = array();

	    foreach ( $widget_files as $widget_file ) {
		    if ( preg_match( '/^class\.([^.]*?)\.php/' , $widget_file , $widget_slug ) === 1 ) {
			    require_once( $this->addon->dir . 'include/widgets/' . $widget_file );
			    register_widget( 'SLPWidget_' . $widget_slug[1] );
		    }
	    }
    }


    /**
     * Sets the current address based on the widget address
     *
     * @param mixed $currentVal
     *
     * @return mixed
     */
    function set_SearchAddressFromRequest($currentVal) {
        if (($currentVal === '') && (isset($_REQUEST['widget_address']))) {
            return stripslashes_deep($_REQUEST['widget_address']);
        }

        return $currentVal;
    }

    /**
     * Sets the search center based on the address
     *
     * @param mixed $currentVal
     *
     * @return mixed
     */
    function set_SearchCenter($currentVal) {
        if ((isset($_REQUEST['widget_address']))) {
            return stripslashes_deep($_REQUEST['widget_address']);
        }

        return $currentVal;
    }


}

