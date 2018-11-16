<?php
defined( 'ABSPATH' ) || exit;
if (! class_exists('SLPPower_Activation')) {
    require_once(SLPLUS_PLUGINDIR.'/include/base_class.activation.php');

    /**
     * Manage plugin activation.
     *
     * @property    SLPPower    $addon
     */
    class SLPPower_Activation  extends SLP_BaseClass_Activation {

        /**
         * Change these single get_option() settings into an slp-power serialized option.
         * key = old setting key , value = new options array key
         * @var array
         */
        public $legacy_options = array(
            'csl-slplus-enhanced_search_auto_submit'    => array( 'key' => 'tag_autosubmit'           , 'since' => '4.4.01'  ),
            'csl-slplus-enhanced_search_show_tag_radio' => array( 'key' => 'tag_selector'             , 'since' => '4.0.019'  , 'callback' =>  'SLPPower_Activation::set_tag_selector'   ) ,
            'csl-slplus-custom_css'                     => array( 'key' => 'custom_css '              , 'since' => '4.2.00'  ),
            'csl-slplus-reporting_enabled'              => array( 'key' => 'reporting_enabled '       , 'since' => '4.1.00'  ),
            'csl-slplus_search_tag_label'               => array( 'key' => 'tag_label '               , 'since' => '4.4.01'  ),
            'csl-slplus_show_tag_any'                   => array( 'key' => 'tag_show_any'             , 'since' => '4.4.01'  ),
            'csl-slplus_show_tag_search'                => array( 'key' => 'tag_selector'             , 'since' => '4.1.02'   , 'callback' => 'SLPPower_Activation::set_tag_selector_search' ),
            'csl-slplus_show_tags'                      => array( 'key' => 'tag_output_processing'    , 'since' => '4.0.014' ),
            'csl-slplus_tag_pulldown_first'             => array( 'key' => 'tag_dropdown_first_entry' , 'since' => '4.4.01'  ),
            'csl-slplus_tag_search_selections'          => array( 'key' => 'tag_selections'           , 'since' => '4.0.014' ),
            'csl-slplus_use_location_sensor'            => array( 'key' => 'use_sensor'               , 'since' => '4.4.01'  ),
        );

        protected $smart_options = array(
	        'ajax_orderby_catcount',
	        'default_icons',
	        'hide_empty',
	        'label_category',
	        'log_import_messages',
	        'log_schedule_messages',
	        'reporting_enabled',
	        'show_cats_on_search',
	        'show_icon_array',
	        'show_legend_text',
	        'show_option_all',
	        'use_contact_fields',
	        'use_pages',
	        'use_nonces',
	        'use_sensor',
        );

        public $obsolete_options = array(
        	'highlight_uncoded',
        );

        /**
         * Add the contact fields.
         */
        public function add_data_extensions() {
            $this->slplus->database->extension->add_field(
                __( 'Identifier'       ,'slp-power' ),
                'varchar',
                array(
                    'addon'         => $this->addon->short_slug ,
                    'slug'          => 'identifier'             ,
                    'display_type'  => 'text'                   ,
                    'help_text'     => __( 'The identifier field is meant to store a unique location record ID from an external data source. ' , 'slp-power' ) .
                                       __( 'During a CSV import, this field is used to match up incoming data with existing locations. '       , 'slp-power' )
                ));
            $this->slplus->database->extension->add_field( __( 'Contact'          ,'slp-power' ), 'varchar', array( 'slug' => 'contact'       , 'addon' => $this->addon->short_slug ) );
            $this->slplus->database->extension->add_field( __( 'First Name'       ,'slp-power' ), 'varchar', array( 'slug' => 'first_name'    , 'addon' => $this->addon->short_slug ) );
            $this->slplus->database->extension->add_field( __( 'Last Name'        ,'slp-power' ), 'varchar', array( 'slug' => 'last_name'     , 'addon' => $this->addon->short_slug ) );
            $this->slplus->database->extension->add_field( __( 'Title'            ,'slp-power' ), 'varchar', array( 'slug' => 'title'         , 'addon' => $this->addon->short_slug ) );
            $this->slplus->database->extension->add_field( __( 'Department'       ,'slp-power' ), 'varchar', array( 'slug' => 'department'    , 'addon' => $this->addon->short_slug ) );
            $this->slplus->database->extension->add_field( __( 'Training'         ,'slp-power' ), 'varchar', array( 'slug' => 'training'      , 'addon' => $this->addon->short_slug ) );
            $this->slplus->database->extension->add_field( __( 'Facility Type'    ,'slp-power' ), 'varchar', array( 'slug' => 'facility_type' , 'addon' => $this->addon->short_slug ) );
            $this->slplus->database->extension->add_field( __( 'Office Phone'     ,'slp-power' ), 'varchar', array( 'slug' => 'office_phone'  , 'addon' => $this->addon->short_slug ) );
            $this->slplus->database->extension->add_field( __( 'Mobile Phone'     ,'slp-power' ), 'varchar', array( 'slug' => 'mobile_phone'  , 'addon' => $this->addon->short_slug ) );
            $this->slplus->database->extension->add_field( __( 'Contact Fax'      ,'slp-power' ), 'varchar', array( 'slug' => 'contact_fax'   , 'addon' => $this->addon->short_slug ) );
            $this->slplus->database->extension->add_field( __( 'Contact Email'    ,'slp-power' ), 'varchar', array( 'slug' => 'contact_email' , 'addon' => $this->addon->short_slug ) );
            $this->slplus->database->extension->add_field( __( 'Office Hours'     ,'slp-power' ), 'text'   , array( 'slug' => 'office_hours'  , 'addon' => $this->addon->short_slug ) );
            $this->slplus->database->extension->add_field( __( 'Contact Address'  ,'slp-power' ), 'text'   , array( 'slug' => 'contact_address','addon' => $this->addon->short_slug ) );
            $this->slplus->database->extension->add_field( __( 'Notes'            ,'slp-power' ), 'text'   , array( 'slug' => 'notes'         , 'addon' => $this->addon->short_slug ) );
            $this->slplus->database->extension->add_field( __( 'Introduction'     ,'slp-power' ), 'text'   , array( 'slug' => 'introduction'  , 'addon' => $this->addon->short_slug ) );
            $this->slplus->database->extension->add_field( __( 'Year Established' ,'slp-power' ), 'varchar', array( 'slug' =>'year_established','addon' => $this->addon->short_slug ) );
            $this->slplus->database->extension->add_field( __( 'County'           ,'slp-power' ), 'varchar', array( 'slug' => 'county'        , 'addon' => $this->addon->short_slug ) );
            $this->slplus->database->extension->add_field( __( 'District'         ,'slp-power' ), 'varchar', array( 'slug' => 'district'      , 'addon' => $this->addon->short_slug ) );
            $this->slplus->database->extension->add_field( __( 'Region'           ,'slp-power' ), 'varchar', array( 'slug' => 'region'        , 'addon' => $this->addon->short_slug ) );
            $this->slplus->database->extension->add_field( __( 'Territory'        ,'slp-power' ), 'varchar', array( 'slug' => 'territory'     , 'addon' => $this->addon->short_slug ) );
	        $this->slplus->database->extension->add_field( __( 'Image'            ,'slp-power' ), 'varchar', array( 'slug' => 'contact_image' , 'addon' => $this->addon->short_slug ) );
            $this->slplus->database->extension->update_data_table();

        }

        /**
         * Clean out duplicate report table indexes.
         */
        private function clean_duplicate_indexes() {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $this->clean_duplicate_index_from_table( 'slp_rep_query'         , 'slp_repq_time' );
            $this->clean_duplicate_index_from_table( 'slp_rep_query_results' , 'slp_repq_id'   );
        }

        /**
         * Clean the duplicate index for each table.
         *
         * @param string $table
         * @param string $column
         */
        private function clean_duplicate_index_from_table( $table , $column ) {
            global $wpdb;

            $table = $wpdb->prefix . $table;
            $tableindices = $wpdb->get_results("SHOW INDEX FROM {$table};");

            $the_list = wp_list_filter( $tableindices, array( 'Column_name' => $column ) );

            if ( count( $the_list ) > 1 ) {
                $first = true;
                foreach ( $the_list as $list_item ) {
                    if ( $first ) { $first = false; continue; }
                    drop_index( $list_item->Table , $list_item->Key_name );
                }
            }
        }

        /**
         * Install or update the main table
         * @global object $wpdb
         */
        function create_MoreInfoTable() {
            $this->dbupdater(
                $this->addon->category_data->get_SQL( 'create_tagalong_helper' ),
                $this->addon->category_data->plugintable['name']
            );
        }

        /**
         * Set the tag selector value for the show_tag_radio from 4.0.019
         *
         * @param $legacy_value
         * @return string
         */
        static function set_tag_selector( $legacy_value ) {
            return ( $legacy_value === '1' ) ? 'radiobutton' : 'none';
        }

        /**
         * Set the tag selector value for show_tag_search from 4.1.02.
         *
         * @param $legacy_value
         * @return string
         */
        static function set_tag_selector_search( $legacy_value ) {
            if ( $legacy_value === false                            ) { return 'none';      }
            if ( ($legacy_value === '0'  )                          ) { return 'none';      }

            $options = get_option( 'csl-slplus-PRO-options' , array() );
            if ( isset( $options['tag_selections'] ) && ! empty( $options['tag_selections'] ) ) { return 'dropdown';  }

            return 'textinput';
        }

        /**
         * Update legacy settings.
         */
        function update() {

            if ( version_compare( $this->updating_from , '4.5' , '<=' ) ) {
                $this->migrate_legacy_options( 'csl-slplus-PRO-options' );
	            if ( $this->migrate_legacy_options( 'slp_storepages-options' ) ) {
		            $this->slplus->SmartOptions->use_pages->value = true;
		            $this->slplus->options_nojs['use_pages'] = '1';
	            }
	            $this->migrate_legacy_options( 'slp-directory-builder-options' );
	            $this->migrate_legacy_options( 'csl-slplus-TAGALONG-options' );
            }

            parent::update();

            $this->install_reporting_tables();

            $this->create_MoreInfoTable();

            if ( version_compare( $this->updating_from , '4.4' , '<=' ) ) {
                $this->clean_duplicate_indexes();
            }

            if ( $this->slplus->SmartOptions->use_contact_fields->is_true ) {
                $this->add_data_extensions();
            }
        }

        /*************************************
         * Install reporting tables
         *
         * Update the plugin version in config.php on every structure change.
         */
        private function install_reporting_tables() {
            global $wpdb;

            $charset_collate = '';
            if ( ! empty($wpdb->charset) )
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if ( ! empty($wpdb->collate) )
                $charset_collate .= " COLLATE $wpdb->collate";

            // Reporting: Queries
            //
            $table_name = $wpdb->prefix . "slp_rep_query";
            $sql = "CREATE TABLE $table_name (
                    slp_repq_id         bigint(20) unsigned NOT NULL auto_increment,
                    slp_repq_time       timestamp NOT NULL default current_timestamp,
                    slp_repq_query      varchar(255) NOT NULL,
                    slp_repq_tags       varchar(255),
                    slp_repq_address    varchar(255),
                    slp_repq_radius     varchar(5),
                    meta_value          longtext,
                    PRIMARY KEY  (slp_repq_id),
                    KEY slp_repq_time (slp_repq_time)
                    )
                    $charset_collate
                    ";
            $this->dbupdater($sql,$table_name);

            // Reporting: Query Results
            //
            $table_name = $wpdb->prefix . "slp_rep_query_results";
            $sql = "CREATE TABLE $table_name (
                    slp_repqr_id    bigint(20) unsigned NOT NULL auto_increment,
                    slp_repq_id     bigint(20) unsigned NOT NULL,
                    sl_id           mediumint(8) unsigned NOT NULL,
                    PRIMARY KEY  (slp_repqr_id),
                    KEY slp_repq_id (slp_repq_id)
                    )
                    $charset_collate
                    ";

            // Install or Update the slp_rep_query_results table
            //
            $this->dbupdater($sql,$table_name);
        }

        /**
         * Update the data structures on new db versions.
         *
         * @global object $wpdb
         * @param string $sql
         * @param string $table_name
         * @return string
         */
        private function dbupdater($sql,$table_name) {
            global $wpdb;
            $retval = ( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) ? 'new' : 'updated';

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            global $EZSQL_ERROR;
            $EZSQL_ERROR = array();

            return $retval;
        }

	    /**
	     * Setup Smart Options.
	     *
	     * @param $slug
	     * @param $value
	     */
	    protected function setup_smart_option( $slug , $value ) {
		    switch ( $slug ) {
			    case 'log_import_messages':
				    $this->addon->create_object_import_messages();
				    break;
			    case 'log_schedule_messages':
				    $this->addon->create_object_schedule_messages();
				    break;
		    }
	    }
    }
}