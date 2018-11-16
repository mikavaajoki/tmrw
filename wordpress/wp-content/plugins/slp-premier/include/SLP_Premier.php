<?php
defined( 'ABSPATH' ) || exit;
require_once( SLPLUS_PLUGINDIR . 'include/base_class.addon.php' );

/**
 * Class SLP_Premier
 *
 * @property    SLP_Premier_Admin            $admin
 * @property    array                        $options
 * @property    SLP_Premier_Options          $Options
 * @property    SLP_Premier_WooCommerce_Glue $WooCommerce_Glue
 */
class SLP_Premier extends SLP_BaseClass_Addon {
    public $admin;

    protected $class_prefix = 'SLP_Premier_';
    protected $objects      = array(
	    'WooCommerce_Glue' => array( 'subdir' => 'include/module/woocommerce/' ) ,
    );


    // NOTE: Do NOT extend this any longer (since 4.6.4) - use the ./include/module/options/SLP_Premier_Options module
    // to build smart options.   They are far more efficient.  The have admin panel rendering intelligence, checkbox boolean saving intelligence,
    // and are stored in the base plugin options_nojs (or option) array saving disk I/O on get_option.
    //
    public $options = array(
	    'category_name_separator'      => ', ' ,
	    'boundaries_influence_min_lat' => '0' ,
	    'boundaries_influence_min_lng' => '0' ,
	    'boundaries_influence_max_lat' => '0' ,
	    'boundaries_influence_max_lng' => '0' ,
	    'dropdown_autosubmit'          => '0' ,
	    'installed_version'            => '' ,
	    'pagination_label'             => '' ,
	    'region_influence_enabled'     => '0' ,
	    'show_address_guess'           => '0' ,
	    'show_location_on_order_email' => '1' ,
	    'woo_data_version'             => '0' ,
    );

    /**
     * Initialize a singleton of this object.
     *
     * @return SLP_Premier
     */
    public static function init() {
	    static $instance = false;
	    if ( ! $instance ) {
		    load_plugin_textdomain( 'slp-premier' , false , SLPPREMIER_REL_DIR . '/languages/' );
		    $instance = new SLP_Premier( array(
			                                 'version'         => SLP_PREMIER_VERSION ,
			                                 'min_slp_version' => SLP_PREMIER_MIN_SLP ,

			                                 'name'        => __( 'Premier' , 'slp-premier' ) ,
			                                 'option_name' => 'slp-premier-options' ,
			                                 'file'        => SLP_PREMIER_FILE ,

			                                 'activation_class_name'    => 'SLP_Premier_Activation' ,
			                                 'admin_class_name'         => 'SLP_Premier_Admin' ,
			                                 'ajax_class_name'          => 'SLP_Premier_AJAX' ,
			                                 'userinterface_class_name' => 'SLP_Premier_UI' ,
		                                 ) );
	    }

	    return $instance;
    }

    /**
     * Global hooks and filters that run after plugins_loaded.
     */
    function add_hooks_and_filters() {
	    add_action( 'slp_prepare_location_import' , array( $this , 'extend_location_import' ) );

	    // WooCommerce
	    if ( $this->is_woo_running() ) {

		    $this->instantiate( 'WooCommerce_Glue' );

		    add_filter( 'woocommerce_add_cart_item' , array(
			    $this->WooCommerce_Glue ,
			    'set_product_price_on_add_to_cart' ,
		    ) );  // Set price when adding to cart

		    add_filter( 'woocommerce_get_cart_item_from_session' , array(
			    $this->WooCommerce_Glue ,
			    'set_product_price_on_session' ,
		    ) , 20 , 3 );  // Set price in cart session


		    if ( version_compare( WC_VERSION , '2.7' , '<' ) ) {
			    add_action( 'woocommerce_order_add_product' , array( $this->WooCommerce_Glue , 'add_location_id_to_order_meta' , ) , 20 , 5 );  // add _location_id to order meta (shows on admin review of order)
		    } else {
			    add_action( 'woocommerce_new_order_item' , array( $this->WooCommerce_Glue , 'add_location_id_to_order_meta' , ) , 20 , 3 );  // add _location_id to order meta (shows on admin review of order)
		    }

		    if ( $this->slplus->is_CheckTrue( $this->options[ 'show_location_on_order_email' ] ) ) {
			    add_action( 'woocommerce_order_item_meta_end' , array(
				    $this->WooCommerce_Glue ,
				    'show_location_details_in_email' ,
			    ) , 20 , 3 );
		    }
	    }

	    // Boundary Influence Is 'Locations'
	    //
	    if ( $this->slplus->SmartOptions->boundaries_influence_type->value === 'locations' ) {
		    add_action( 'slp_deletelocation_starting' , array( $this , 'update_minmax_latlng_delete' ) );
	    }
    }

    /**
     * Create the category object if Power is active.
     *
     * @returns boolean
     */
    public function create_object_category() {
	    if ( ! $this->slplus->AddOns->get( 'slp-power' , 'active' ) ) {
		    return false;
	    }

	    SLP_Premier_Category::get_instance();

	    return true;
    }

    /**
     * Premier Features For Location Import
     */
    function extend_location_import() {
	    if ( $this->is_woo_running() ) {
		    $this->instantiate( 'WooCommerce_Glue' );
	    }
    }

    /**
     * Extend the SQL to get the min/max lat/long.
     *
     * @param string $command
     *
     * @return string
     */
    function extend_sql_get_location_bounds( $command ) {
	    if ( $command !== 'select_location_bounds' ) {
		    return $command;
	    }

	    return 'SELECT ' . 'min(cast(sl_latitude as decimal(10,6))) as min_lat, min(cast(sl_longitude as decimal(10,6))) as min_lng, ' . 'max(cast(sl_latitude as decimal(10,6))) as max_lat, max(cast(sl_longitude as decimal(10,6))) as max_lng  ' . 'FROM ' . $this->slplus->database->info[ 'table' ];

    }

    /**
     * Set the sw and ne corners of the bounding box that covers all our locations.
     *
     * Store in the add on pack options array.
     */
    public function find_minmax_latlng() {
	    add_filter( 'slp_extend_get_SQL' , array( $this , 'extend_sql_get_location_bounds' ) );
	    $locations_bounds = $this->slplus->database->get_Record( array(
		                                                             'select_location_bounds' ,
		                                                             'where_default_validlatlong' ,
		                                                             'where_not_private' ,
	                                                             ) );

	    $this->addon->options[ 'boundaries_influence_min_lat' ] = $locations_bounds[ 'min_lat' ];
	    $this->addon->options[ 'boundaries_influence_min_lng' ] = $locations_bounds[ 'min_lng' ];
	    $this->addon->options[ 'boundaries_influence_max_lat' ] = $locations_bounds[ 'max_lat' ];
	    $this->addon->options[ 'boundaries_influence_max_lng' ] = $locations_bounds[ 'max_lng' ];

	    update_option( $this->addon->option_name , $this->addon->options );
    }

    /**
     * Return true if any URL controls are in place.
     * @return bool
     */
    public function has_url_controls() {
	    return $this->slplus->AddOns->is_premier_subscription_valid() && ( $this->slplus->SmartOptions->allow_limit_in_url->is_true || $this->slplus->SmartOptions->allow_location_in_url->is_true );
    }

    /**
     * Set option defaults for this plugin.
     */
    function init_options() {
	    $this->option_defaults[ 'pagination_label' ] = __( 'More Locations: ' , 'slp-premier' );
	    parent::init_options();
    }

    /**
     * Return true of WooCommerce is running.
     *
     * @return bool
     */
    function is_woo_running() {
	    return ( class_exists( 'WooCommerce' ) && defined( 'WOOCOMMERCE_VERSION' ) && version_compare( WOOCOMMERCE_VERSION , '2.4.10' , '>=' ) );
    }

    /**
     * Our default object options.
     */
    protected function set_default_object_options() {
	    $this->objects[ 'Options' ][ 'options' ]          = array( 'addon' => $this );
	    $this->objects[ 'Category' ][ 'options' ]         = array( 'addon' => $this );
    }

    /**
     * Get the location search boundaries as saved in the admin panel.
     *
     * Use in both admin and UI.
     *
     * @return array
     */
    function set_location_search_boundaries() {
	    return array(
		    'min_lat' => $this->addon->options[ 'boundaries_influence_min_lat' ] ,
		    'min_lng' => $this->addon->options[ 'boundaries_influence_min_lng' ] ,
		    'max_lat' => $this->addon->options[ 'boundaries_influence_max_lat' ] ,
		    'max_lng' => $this->addon->options[ 'boundaries_influence_max_lng' ] ,
	    );
    }

    /**
     * Update the min/max latitude and longitude if we delete and outter boundary.
     */
    function update_minmax_latlng_delete() {
	    if ( ( floatval( $this->slplus->currentLocation->latitude ) === floatval( $this->options[ 'boundaries_influence_min_lat' ] ) ) || ( floatval( $this->slplus->currentLocation->longitude ) === floatval( $this->options[ 'boundaries_influence_min_lng' ] ) ) || ( floatval( $this->slplus->currentLocation->latitude ) === floatval( $this->options[ 'boundaries_influence_max_lat' ] ) ) || ( floatval( $this->slplus->currentLocation->longitude ) === floatval( $this->options[ 'boundaries_influence_max_lng' ] ) ) ) {
		    $this->find_minmax_latlng();
	    }
    }
}


