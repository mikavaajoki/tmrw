<?php
defined( 'ABSPATH' ) || exit;
if (!class_exists('SLP_BaseClass_Addon')) {
    if (!defined('SLPLUS_PLUGINDIR')) {
        require_once( dirname(__FILE__) . '/../store-locator-le.php' );
    }
	/** @noinspection PhpIncludeInspection */
	require_once( SLPLUS_PLUGINDIR . 'include/base/SLP_Object_WithOptions.php');

    /**
     * A base class that consolidates common add-on pack methods.
     *
     * Add on packs should base based on and extend this class.
     *
     * Setting the following properties will activate hooks to various
     * classes that will be instantiated as objects when needed:
     *
     * o 'admin_class_name' => 'Your_SLP_Admin_Class'
     * o 'user_class_name'  => 'Your_SLP_UI_Class'
     *
     * The admin class definition needs to go in your add-on pack
     * under the ./include directory and be named 'class.admin.php'.
     * The name of the class needs to match the provided string.
     * The admin object will only be instantiated when WordPress is
     * rendering the admin interface.
     *
     * The user class definition needs to go in your add-on pack
     * under the ./include directory and be named 'class.userinterface.php'.
     * The name of the class needs to match the provided string.
     * The user object will only be instantiated when WordPress is
     * rendering the WordPress front end.
     *
     * This methodology provides a standard construct for coding admin-only
     * and user-interface-only elements of a WordPress add-on pack.   This
     * will mean less code is loaded into active ram, avoiding loading UI
     * only code when on the admin panel and vice versa.
     *
     * @property        SLP_BaseClass_Addon             $addon
     * @property        SLP_BaseClass_Admin             $admin                      The admin object.
     * @property        SLP_BaseClass_AJAX              $ajax                       The AJAX object.
     * @property        string                          $activation_class_name      The name of the activation class for this add on.
     * @property        string                          $admin_class_name           The name of the admin class for this add on.
     * @property        array                $admin_menu_entries         array of menu entries, should be in a key=>value array where key = the menu text and value = the function or PHP file to execute.
     * @property        string               $ajax_class_name            The name of the AJAX class for this add on.
     * @property        string               $dir                        The directory the add-on pack resides in.
     * @property        string               $file                       The add on loader file.
     * @property        SLP_AddOns_Meta     $meta
     * @property        string               $min_slp_version            Minimum version of SLP required to run this add-on pack in x.y.zz format.
     * @property        string               $name                       Text name for this add on pack.
     * @property-read   array                $objects                    A named array of our instantiated objects, the key is the class name the value is the object itself.
     * @property        string               $option_name                The name of the wp_option to store serialized add-on pack settings.
     * @property        array                $option_defaults            The default values for options.   Set this in init_options for any gettext elements.  $option_defaults['setting'] = __('string to translate', 'textdomain');
     * @property        mixed[]              $options                    Settable options for this plugin. (Does NOT go into main plugin JavaScript)
     * @property        array                           $options_defaults           Default options.
     * @property        string                          $short_slug                 The short slug name.
     * @property        string                          $slug                       The slug for this plugin, usually matches the plugin subdirectory name.
     * @property        SLP_AddOn_Updates              $Updates
     * @property        string                          $url                        The url for this plugin admin features.
     * @property        string                          $userinterface_class_name   The name of the user class for this add on.
     * @property        SLP_BaseClass_UI                $userinterface
     * @property        SLP_BaseClass_Widget            $widget                     The Widget object.
     * @property        string                          $widget_class_name          The name of the widget class for this add on.
     * @property        SLP_WPOption_Manager            WPOption_Manager            The option manager
     * @property        string                          $version                    Current version of this add-on pack in x.y.zz format.
     */
    class SLP_BaseClass_Addon extends SLP_Object_WithOptions {
	    protected $addon;
	    public    $admin;
	    public    $ajax;
	    public    $activation_class_name;
	    protected $admin_class_name;
	    public    $admin_menu_entries;
	    protected $ajax_class_name;
	    public    $dir;
	    public    $file;
	    public    $meta;
	    public    $min_slp_version;
	    public    $name;
	    protected $objects;
	    public    $option_name;
	    public    $option_defaults  = array();
	    public    $options          = array( 'installed_version' => '' );
	    public    $options_defaults = array();
	    public    $short_slug;
	    public    $slug;
	    public    $Updates;
	    public    $url;
	    public    $userinterface_class_name;
	    public    $userinterface;
	    public    $widget;
	    public    $widget_class_name;
	    public    $WPOption_Manager;
	    public    $version;

	    //  TODO: SLP 5.0 remove, drops support for legacy add-ons.
	    public $metadata;

	    /**
	     * Run these things during invocation. (called from base object in __construct)
	     */
	    protected function initialize() {

		    // Calculate file if not specified
		    //
		    if ( is_null( $this->file ) ) {
			    $matches = array();
			    preg_match( '/^.*?\/(.*?)\.php/' , $this->slug , $matches );
			    $slug_base  = ! empty( $matches ) ? $matches[ 1 ] : $this->slug;
			    $this->file = str_replace( $slug_base . '/' , '' , $this->dir ) . $this->slug;

			    // If file was specified, check to set slug, url, dir if necessary
			    //
		    } else {
			    if ( ! isset( $this->dir ) ) {
				    $this->dir = plugin_dir_path( $this->file );
			    }
			    if ( ! isset( $this->slug ) ) {
				    $this->slug = plugin_basename( $this->file );
			    }
			    if ( ! isset( $this->url ) ) {
				    $this->url = plugins_url( '' , $this->file );
			    }
		    }
		    $this->short_slug = $this->get_short_slug();

		    if ( ! $this->check_my_version_compatibility() ) {
			    return;
		    }

		    $this->create_object_option_manager();

		    // Object of Objects Init
		    parent::initialize();

		    // Widgets
		    // This needs to run before slp_init because it requires widgets_init
		    // widgets_init runs on WP init at level 1
		    // slp_init_complete runs on WP init at level 11
		    //
		    if ( ! empty( $this->widget_class_name ) ) {
			    $this->create_object_widget();
		    }

		    // A little earlier than slp_init for options
		    //
		    add_action( 'start_slp_specific_setup' , array( $this , 'load_options' ) );

		    // When SLP finished initializing do this
		    //
		    add_action( 'slp_init_complete' , array( $this , 'slp_init' ) );
	    }

	    /**
	     * Check the current version of this Add On works with the latest version of the SLP base plugin.
	     *
	     * @return boolean
	     */
	    private function check_my_version_compatibility() {
		    if ( isset( $this->slplus->min_add_on_versions[ $this->short_slug ] ) && version_compare( $this->version , $this->slplus->min_add_on_versions[ $this->short_slug ] , '<' ) ) {
			    if ( ! function_exists( 'deactivate_plugins' ) ) {
			    	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			    }
			    deactivate_plugins( $this->file );
			    $helper = SLP_Admin_Helper::get_instance();
			    $text = SLP_Text::get_instance();
			    $helper->add_wp_admin_notification( sprintf( __( '%s version %s has been disabled. ' , 'store-locator-le' ) , $this->name , $this->version ) . '<br/>' . sprintf( __( 'It is not compatible with version %s of Store Locator Plus™. ' , 'store-locator-le' ) , SLPLUS_VERSION ) . '<br/>' . $text->get_web_link( 'check_website_for_upgrades' ) . '<br/>' . $text->get_web_link( 'new_addon_versions' ) );

			    // Check for updates
			    /** @var SLP_AddOn_Updates $update_engine */
			    $update_engine = SLP_AddOn_Updates::get_instance();
			    $update_engine->configure( $this );
			    $update_engine->set_new_version_available();

			    return false;
		    }

		    return true;
	    }

	    /**
	     * Load the options class to help the SLP smart options if we have that feature.
	     */
	    function load_options() {
		    if ( ! empty( $this->objects[ 'Options' ] ) ) {
			    $this->instantiate( 'Options' );
		    }
	    }

	    /**
	     * Things to do once SLP is alive.
	     *
	     * @uses \SLP_BaseClass_Addon::createobject_UserInterface via WP Filter wp_enqueue_scripts
	     */
	    function slp_init() {
		    $this->create_object_Updates( false );

		    $this->addon = $this;
		    $this->slplus->AddOns->register( $this->slug , $this );

		    // Check the base plugin minimum version requirement.
		    //
		    $this->VersionCheck( array(
			                         'addon_name'           => $this->name ,
			                         'addon_slug'           => $this->slug ,
			                         'min_required_version' => $this->min_slp_version
		                         ) );

		    // Initialize The Options
		    //
		    $this->init_options();

		    // Add Hooks and Filters
		    //
		    $this->add_hooks_and_filters();

		    // Admin Interface?
		    //
		    if ( ! empty( $this->admin_class_name ) ) {
			    add_action( 'slp_admin_menu_starting' , array( $this , 'createobject_Admin' ) , 5 );
			    add_filter( 'slp_menu_items' , array( $this , 'filter_AddMenuItems' ) );

			    if ( method_exists( $this , 'admin_menu' ) ) {
				    add_action( 'slp_admin_menu_starting' , array( $this , 'admin_menu' ) , 10 );
			    }

			    if ( method_exists( $this , 'admin_init' ) ) {
				    add_action( 'admin_init' , array( $this , 'admin_init' ) , 25 );
			    }
		    }

		    // User Interface?
		    //
		    if ( ! empty( $this->userinterface_class_name ) ) {
			    add_action( 'wp_enqueue_scripts' , array( $this , 'createobject_UserInterface' ) );
		    }

		    // AJAX Processing
		    //
		    if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! empty( $this->ajax_class_name ) ) {
			    $this->createobject_AJAX();
		    }

		    $this->slplus->SmartOptions->execute_time_callbacks();
	    }

	    /**
	     * Add the items specified in the menu_entries property to the SLP menu.
	     *
	     * If you make the 'slug' property of the $admin_menu_entries array = $this->addon->short_slug
	     * you won't need to set this->addon->admin->admin_page_slug
	     *
	     * @param mixed[] $menuItems
	     *
	     * @return mixed[]
	     */
	    function filter_AddMenuItems( $menuItems ) {
		    if ( ! isset( $this->admin_menu_entries ) ) {
			    return $menuItems;
		    }

		    return array_merge( (array) $menuItems , $this->admin_menu_entries );
	    }

	    /**
	     * Add the plugin specific hooks and filter configurations here.
	     *
	     * The hooks & filters that go here are cross-interface element hooks/filters needed in 2+ locations:
	     * - AJAX
	     * - Admin Interface
	     * - User Interface
	     *
	     * For example, custom taxonomy hooks and filters.
	     *
	     * Should include WordPress and SLP specific hooks and filters.
	     */
	    function add_hooks_and_filters() {
		    // Add your hooks and filters in the class that extends this base class.
	    }

	    /**
	     * Instantiate our objects ONCE and refer to them in an object array.
	     * TODO: Remove and update Power compat to 4.9.1
	     *
	     * @param string $class
	     * @param string $subdir
	     *
	     * @return mixed
	     */
	    public function create_object( $class , $subdir = '.' ) {
		    if ( ! isset( $this->objects[ $class ] ) ) {
			    /** @noinspection PhpIncludeInspection */
			    include_once( $this->dir . $subdir . '/' . $class . '.php' );
			    if ( ! class_exists( $class ) ) {
				    return null;
			    }
			    $this->objects[ $class ][ 'subdir' ] = $subdir;
			    $this->objects[ $class ][ 'object' ] = new $class( array( 'addon' => $this ) );
		    }

		    return $this->objects[ $class ][ 'object' ];
	    }

	    /**
	     * Creates updates object AND checks for updates for this add-on.
	     *
	     * @param boolean $force
	     */
	    function create_object_Updates( $force ) {
		    if ( ! current_user_can( 'update_plugins' ) ) {
			    return;
		    }

		    // Do not check for updates on any page request that contains slp_ in the name.
		    if ( ! $force && ( !empty( $this->slplus->clean[ 'page' ] ) && ( strpos( $this->slplus->clean[ 'page' ] , 'slp_' ) !== false ) ) ) {
			    return;
		    }

		    if ( ! isset( $this->Updates ) ) {
			    $this->Updates = new SLP_AddOn_Updates(); // Get a new instance of SLP_Add_On_Updates for this add on.
			    if ( ! $force ) {
				    $this->Updates->configure( $this );
			    }
			    $this->Updates->add_hooks_and_filters();
		    }
	    }

	    /**
	     * Create the admin interface object and attach to this->admin
	     *
	     * Called on slp_admin_menu_starting.  If that menu is rendering, we are on an admin page.
	     */
	    function createobject_Admin() {
		    if ( ! isset( $this->admin ) ) {

			    // TODO: remove this when all class.admin.php files are gone from Add Ons
			    // Rename the class.admin.php files to be <classname.php> and put in include/module/admin/ directory.
			    // The classname should be SLP_<AddOnSlug>_Admin.php
			    //
		    	if ( is_readable( $this->dir . 'include/class.admin.php' ) ) {
				    /** @noinspection PhpIncludeInspection */
				    require_once( $this->dir . 'include/class.admin.php' );
				    $this->admin = new $this->admin_class_name( array( 'addon' => $this ));
					return;
			    }

			    // autoload version
			    if ( class_exists( $this->admin_class_name ) ) {
		    		$classname = $this->admin_class_name;
				    $this->admin = $classname::get_instance( false , array( 'addon' => $this ) );
			    } else {
				    new WP_Error( 'noclass' , sprintf ( __( 'Class %s is missing.' , 'store-locator-le' ) , $this->admin_class_name ) );
			    }
		    }
	    }

	    /**
	     * Create the AJAX processing object and attach to this->ajax
	     */
	    function createobject_AJAX() {
		    if ( ! isset( $this->ajax ) ) {

			    // TODO: remove this when all class.ajax.php files are gone from Add Ons
			    // Rename the class.ajax.php files to be <classname.php> and put in include/module/ajax/ directory.
			    // The classname should be SLP_<AddOnSlug>_AJAX.php
			    //
			    if ( is_readable( $this->dir . 'include/class.ajax.php' ) ) {
				    /** @noinspection PhpIncludeInspection */
				    require_once( $this->dir . 'include/class.ajax.php' );
				    $this->ajax = new $this->ajax_class_name( array( 'addon' => $this ));
				    return;
			    }

			    // autoload version
			    if ( class_exists( $this->ajax_class_name ) ) {
				    $classname = $this->ajax_class_name;
				    $this->ajax = $classname::get_instance( false , array( 'addon' => $this ) );
			    } else {
				    new WP_Error( 'noclass' , sprintf ( __( 'Class %s is missing.' , 'store-locator-le' ) , $this->ajax_class_name ) );
			    }
		    }
	    }


	    /**
	     * Create an option manager object for this addon.
	     *
	     * If an addon has ./include/options/<class_prefix>Options.php load it up.
	     */
	    private function create_object_option_manager() {
		    if ( ! isset( $this->WPOption_Manager ) ) {
			    /** @noinspection PhpIncludeInspection */
			    require_once( SLPLUS_PLUGINDIR . 'include/module/options/SLP_WPOption_Manager.php' );
			    $this->WPOption_Manager = new SLP_WPOption_Manager( array( 'option_slug' => $this->option_name ) );
		    }
		    if ( empty( $this->class_prefix ) ) {
		    	return;
		    }

		    // Need to be registered for autoload to work
		    //
		    SLP_AddOns::get_instance()->register( $this->slug , $this );

		    // Autoload the options
		    $smart_option_class = $this->class_prefix . 'Options';
		    if ( class_exists( $smart_option_class ) ) {
			    $this->add_object( new $smart_option_class() );
		    } else {
			    new WP_Error( 'noclass' , sprintf( __( 'Class %s is missing.' , 'store-locator-le' ) , $smart_option_class ) );
		    }
	    }

	    /**
	     * Create the user interface object and attach to this->UserInterface
	     *
	     * @used-by \MySLP_REST_API::get_options
	     * @used-by \SLP_BaseClass_Addon::slp_init      via WP Filter wp_enqueue_scripts
	     */
	    public function createobject_UserInterface() {
		    if ( ! isset( $this->userinterface ) ) {

			    // TODO: remove this when all class.userinterface.php files are gone from Add Ons
			    // Rename the class.userinterace.php files to be <classname.php> and put in include/module/ui/ directory.
			    // The classname should be SLP_<AddOnSlug>_UI.php
			    // The new class will need to set its own addon property slplus is auto-set if it extends the base class userinterface
			    //
			    if ( is_readable( $this->dir . 'include/class.userinterface.php' ) ) {
				    /** @noinspection PhpIncludeInspection */
				    require_once( $this->dir . 'include/class.userinterface.php' );
				    $this->userinterface = new $this->userinterface_class_name( array( 'addon' => $this , 'slplus' => $this->slplus ) );
				    return;
			    }

			    // autoload version
			    if ( class_exists( $this->userinterface_class_name ) ) {
				    $classname = $this->userinterface_class_name;
				    $this->userinterface = $classname::get_instance( false , array( 'addon' => $this ) );
			    } else {
				    new WP_Error( 'noclass' , sprintf ( __( 'Class %s is missing.' , 'store-locator-le' ) , $this->userinterface_class_name ) );
			    }
		    }
	    }

	    /**
	     * Create and attach the widget object as needed.
	     */
	    private function create_object_widget() {
		    if ( ! isset( $this->widget ) ) {
			    /** @noinspection PhpIncludeInspection */
			    require_once( $this->dir . 'include/class.widget.php' );
			    $this->widget = new $this->widget_class_name( array( 'addon' => $this ));
		    }
	    }

	    /**
	     * Get the add-on pack version.
	     *
	     * @return string
	     */
	    public function get_addon_version() {
		    return $this->get_meta( 'Version' );
	    }

	    /**
	     * Get the short slug, just the base/directory part of the fully qualified WP slug for this plugin.
	     *
	     * @return string
	     */
	    private function get_short_slug() {
		    $slug_parts = explode( '/' , $this->slug );

		    return str_replace( '.php' , '' , $slug_parts[ count( $slug_parts ) - 1 ] );
	    }

	    /**
	     * Compare current plugin version with minimum required.
	     *
	     * Set a notification message.
	     * Disable the requesting add-on pack if requirement is not met.
	     *
	     * $params['addon_name'] - the plain text name for the add-on pack.
	     * $params['addon_slug'] - the slug for the add-on pack.
	     * $params['min_required_version'] - the minimum required version of the base plugin.
	     *
	     * @param mixed[] $params
	     */
	    private function VersionCheck( $params ) {

		    // Minimum version requirement not met.
		    //
		    if ( version_compare( SLPLUS_VERSION , $params[ 'min_required_version' ] , '<' ) ) {
			    if ( is_admin() ) {
				    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				    deactivate_plugins( array( $params[ 'addon_slug' ] ) );
				    $this->slplus->Helper->add_wp_admin_notification( sprintf( __( '%s requires Store Locator Plus™ version %s. ' , 'store-locator-le' ) , $this->addon->name , $params[ 'min_required_version' ] ) . __( 'This add-on has been deactivated' , 'store-locator-le' ) );
			    }

			    return;
		    }
	    }

	    /**
	     * Get the add on metadata property as specified.
	     *
	     * @param string $property
	     *
	     * @return string
	     */
	    public function get_meta( $property ) {
		    if ( ! isset( $this->meta ) ) {
			    $this->meta = new SLP_AddOns_Meta( array( 'addon' => $this ) ); // Use new not get_instance() as the meta object must be AddOn specific.
		    }
		    return $this->meta->get_meta( $property );
	    }

	    /**
	     * Initialize the options properties from the WordPress database.
	     *
	     */
	    function init_options() {
		    if ( isset( $this->option_name ) ) {
			    $this->set_option_defaults();
			    $dbOptions = $this->slplus->WPOption_Manager->get_wp_option( $this->option_name );
			    if ( is_array( $dbOptions ) ) {
				    $this->options = array_merge( $this->options , $this->options_defaults );
				    $this->options = array_merge( $this->options , $dbOptions );
			    }
		    }
	    }

	    /**
	     * Set option defaults outside of hard-coded property values via an array.
	     *
	     * This allows for gettext() string translations of defaults.
	     *
	     * Only bring over items in default_value_array that have matching keys in $this->options already.
	     *
	     */
	    function set_option_defaults() {
		    $valid_options = array_intersect_key( $this->option_defaults , $this->options );
		    $this->options = array_merge( $this->options , $valid_options );

		    return;
	    }

	    /**
	     * Set valid options according to the addon options array.
	     *
	     * @param $val
	     * @param $key
	     */
	    function set_ValidOptions( $val , $key ) {
		    $simpleKey = str_replace( SLPLUS_PREFIX . '-' , '' , $key );
		    if ( array_key_exists( $simpleKey , $this->options ) ) {
			    $this->options[ $simpleKey ] = stripslashes_deep( $val );
		    }
	    }

	    /**
	     * Generate a proper setting name for the settings class.
	     *
	     * @param $setting
	     *
	     * @return string
	     */
	    function setting_name( $setting ) {
		    return $this->addon->option_name . '[' . $setting . ']';
	    }
    }
}