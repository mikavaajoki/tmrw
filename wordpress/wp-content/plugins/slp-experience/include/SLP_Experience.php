<?php
defined( 'ABSPATH' ) || exit;
    require_once( SLPLUS_PLUGINDIR . 'include/base_class.addon.php');

/**
 * Class SLP_Experience
 *
 * @property        array                 options             Settable options for this plugin.
 * @property        SLP_Experience_Admin  admin               The admin object.
 * @property        SLP_Experience_Widget widget
 *
 */
class SLP_Experience extends SLP_BaseClass_Addon {
    public $admin;
    protected $class_prefix = 'SLP_Experience_';

    public $options = array(
        'address_autocomplete'              => 'none'               ,
        'address_autocomplete_min'          => '3'                  ,
        'city'                              => ''                   ,
        'city_selector'                     => 'hidden'             ,
        'country'                           => ''                   ,
        'country_selector'                  => 'hidden'             ,
        'custom_css'                        => ''                   ,
        'disable_initial_directory'         => '0'                  ,
        'email_link_format'                 => 'label_link'         ,
        'featured_location_display_type'    => 'show_within_radius' ,
        'first_entry_for_city_selector'     => ''                   , // default set in init_options
        'first_entry_for_country_selector'  => ''                   , // default set in init_options
        'first_entry_for_radius_selector'   => ''                   , // default set in init_options
        'first_entry_for_state_selector'    => ''                   , // default set in init_options
        'hide_address_entry'                => '0'                  ,
        'hide_bubble'                       => '0'                  ,
        'hide_distance'                     => '0'                  ,
        'hide_map'                          => '0'                  ,
        'hide_radius_selector'              => '0'                  ,
        'ignore_radius'                     => '0'                  , // used only to read in old value from versions < 4.1.05
        'installed_version'                 => ''                   ,
        'label_for_city_selector'           => ''                   , // default set in init_options
        'label_for_country_selector'        => ''                   , // default set in init_options
        'label_for_find_button'             => ''                   , // default set in init_options
        'label_for_map_toggle'              => ''                   , // default set in init_options
        'label_for_name'                    => ''                   , // default set in init_options
        'label_for_state_selector'          => ''                   , // default set in init_options
        'map_options_scaleControl'          => '0'                  ,
        'map_options_scrollwheel'           => '0'                  ,
        'map_options_mapTypeControl'        => '0'                  ,
        'map_region'                        => ''                   ,
        'name_placeholder'                  => ''                   ,
        'orderby'                           => 'sl_distance ASC'    ,
        'popup_email_title'                 => ''                   ,
        'popup_email_from_placeholder'      => ''                   ,
        'popup_email_subject_placeholder'   => ''                   ,
        'popup_email_message_placeholder'   => ''                   ,
        'search_box_title'                  => ''                   , // default set in init_options
        'search_by_name'                    => '0'                  ,
        'searchnear'                        => 'world'              ,
        'selector_behavior'                 => 'use_both'           ,
        'show_country'                      => '1'                  ,
        'show_hours'                        => '1'                  ,
        'show_maptoggle'                    => '0'                  ,
        'state'                             => ''                   ,
        'state_selector'                    => 'hidden'             ,
    );

    /**
     * Initialize a singleton of this object.
     *
     * @return SLP_Experience
     */
    public static function init() {
        static  $instance = false;
        if ( ! $instance ) {
            load_plugin_textdomain( 'slp-experience' , false, SLP_EXPERIENCE_REL_DIR . '/languages/');
            $instance = new SLP_Experience(
                    array(
                        'version'           => SLP_EXPERIENCE_VERSION ,
                        'min_slp_version'   => SLP_EXPERIENCE_MIN_SLP ,

                        'name'              => __( 'Experience' , 'slp-experience' ) ,
                        'option_name'       => 'slp-experience' ,
                        'file'              => SLP_EXPERIENCE_FILE ,

                        'activation_class_name'     => 'SLP_Experience_Activation' ,
                        'admin_class_name'          => 'SLP_Experience_Admin' ,
                        'ajax_class_name'           => 'SLP_Experience_AJAX' ,
                        'userinterface_class_name'  => 'SLP_Experience_UI' ,
                        'widget_class_name'         => 'SLP_Experience_Widget'
                    )
                );
        }
        return $instance;
    }

    /**
     * Add cross-element hooks & filters.
     *
     * Haven't yet moved all items to the AJAX and UI classes.
     */
    function add_hooks_and_filters() {
        // ADMIN UI Locations
        add_filter('slp_column_data'                        , array( $this , 'filter_FieldDataToManageLocations'), 90 , 3   );

        // Pro Pack Export
        //
        add_filter('slp-pro-dbfields'                       , array( $this , 'filter_Locations_Export_Field'    ), 90       );
        add_filter('slp-pro-csvexport'                      , array( $this , 'filter_Locations_Export_Data'     ), 90       );
    }

    /**
     * Initialize the options properties from the WordPress database.
     */
    function init_options() {

        // Set the defaults for first-run
        // Especially useful for gettext stuff you cannot put in the property definitions.
        //
        $this->option_defaults = $this->options;
        $this->option_defaults['first_entry_for_city_selector'      ] = __( 'All Cities...'             , 'slp-experience' );
        $this->option_defaults['first_entry_for_country_selector'   ] = __( 'All Countries...'          , 'slp-experience' );
        $this->option_defaults['first_entry_for_state_selector'     ] = __( 'All States...'             , 'slp-experience' );
        $this->option_defaults['label_for_city_selector'            ] = __( 'City'                      , 'slp-experience' );
        $this->option_defaults['label_for_country_selector'         ] = __( 'Country'                   , 'slp-experience' );
        $this->option_defaults['label_for_find_button'              ] = __( 'Find Locations'            , 'slp-experience' );
        $this->option_defaults['label_for_name'                     ] = __( 'Name'                      , 'slp-experience' );
        $this->option_defaults['label_for_map_toggle'               ] = __( 'Map'                       , 'slp-experience' );
        $this->option_defaults['label_for_state_selector'           ] = __( 'State'                     , 'slp-experience' );
        $this->option_defaults['popup_email_title'                  ] = __( 'Send An Email'             , 'slp-experience' );
        $this->option_defaults['popup_email_from_placeholder'       ] = __( 'Your email address.' 		, 'slp-experience' );
        $this->option_defaults['popup_email_subject_placeholder'    ] = __( 'Email subject line.' 		, 'slp-experience' );
        $this->option_defaults['popup_email_message_placeholder'    ] = __( 'What do you want to say?' 	, 'slp-experience' );
        $this->option_defaults['search_box_title'                   ] = __( 'Find Our Locations'        , 'slp-experience' );

        parent::init_options();
    }

    /**
     * Change the export location row data.
     *
     * @param mixed[] $location A location data ih associated array
     * @return mixed[] Location data need to export
     */
    function filter_Locations_Export_Data($location) {
        $this->slplus->database->createobject_DatabaseExtension();
        $exData = $this->slplus->database->extension->get_data($location['sl_id']);
        $location['featured'] = isset($exData['featured']) && $this->slplus->is_CheckTrue($exData['featured']) ? '1' : '0';
        $location['rank'    ] = isset($exData['rank']) ? $exData['rank'] : '';

        return $location;
    }

    /**
     * Change the export location field.
     *
     * @param mixed[] $location Field array
     * @return mixed[] Fields need to export
     */
    function filter_Locations_Export_Field($dbFields) {
        array_push($dbFields, 'featured');
        array_push($dbFields, 'rank'    );

        return $dbFields;
    }

    /**
     * Render the extra fields on the manage location table.
     *
     * SLP Filter: slp_column_data
     *
     * @param string $theData  - the option_value field data from the database
     * @param string $theField - the name of the field from the database (should be sl_option_value)
     * @param string $theLabel - the column label for this column (should be 'Categories')
     * @return type
     */
    function filter_FieldDataToManageLocations($theData,$theField,$theLabel) {
        if (
            ($theField === 'featured') &&
            ($theData  === '0')
        ) {
            $theData = '';
        }
        return $theData;
    }

    /**
     * SQL to select all cities within the given state.
     *
     * TODO: This should go in the SLP_Experience_Data class and the calling method should use add_filter( 'slp_extend_get_SQL' , array( $this , 'add_data_queries' ) ); See class.userinterface.php
     *
     * @param   string  $command     The name of the command
     *
     * @return  string               The SQL
     */
    public function select_cities_in_state( $command ) {
        if ( $command !== 'select_cities_in_state'  ) { return $command; }
        return
            'SELECT sl_city' . $this->slplus->database->from_slp_table .
            "WHERE sl_state = '%s' " .
            'GROUP BY sl_city ' .
            'ORDER BY sl_city ASC '
            ;
    }

}
