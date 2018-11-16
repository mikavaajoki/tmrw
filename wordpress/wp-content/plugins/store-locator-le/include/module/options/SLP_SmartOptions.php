<?php
if ( ! class_exists( 'SLP_SmartOptions' ) ) {

	/**
	 * Class SLP_SmartOptions
	 *
	 * The options management for the base plugin (replaces SLPlus->options / SLPlus->options_nojs)
	 *
	 * @property        array    $change_callbacks                       Stack (array) of callbacks in array ( _func_ , _params_ ) format.
	 * @property-read   string[] $current_checkboxes                     Array of smart option checkbox slugs for the current admin screen
	 * @property-read   boolean  $db_loading                             True only when processing the option values loaded from the db.
	 * @property-read   array    $page_layout                            The page layout array( pages slugs => array( section slugs => array( group_slugs => property slugs ) ) )
     * @property-read   string   dropdown_style                          The selected jQuery UI theme for the SLPLUS UI.
	 * @property-read   string   radii                                   The radii string
	 * @property-read   string[] $smart_properties                       Array of property names that are smart options.
	 * @property-read   int      $style_id                               The active style ID (hidden, works with "style" setting).
	 * @property        string[] $text_options                           An array of smart option slugs that are text options.
	 * @property        array    $time_callbacks                         Stack (array) of callbacks for cron jobs in array ( _func_ , _params_ ) format.
	 * @property-read  boolean   $initial_distance_already_calculated    only do this once per change set.
	 *
	 * SLP
	 * @property    SLP_Option      loading_indicator
	 * @property    string      map_end_icon
	 * @property    string      map_home_icon
	 *
	 * Experience
	 * @property    SLP_Option     add_tel_to_phone
	 * @property    SLP_Option     address_placeholder
	 * @property    SLP_Option     append_to_search
	 * @property    SLP_Option     google_map_style
	 * @property    SLP_Option     hide_search_form
	 * @property    SLP_Option     map_initial_display
	 * @property    SLP_Option     no_autozoom
	 * @property    SLP_Option     no_homeicon_at_start
	 * @property    SLP_Option     results_box_title
	 * @property    SLP_Option     starting_image
	 * @property    SLP_Option     url_allow_address
	 *
	 * Premier
	 * @property    SLP_Option  allow_tag_in_url
     * @property    SLP_Option  block_ip_limit
     * @property    SLP_Option  block_ip_period
     * @property    SLP_Option  block_ip_release_after
     * @property    SLP_Option  ip_whitelist
	 *
	 * Power
	 * @property    SLP_Option  ajax_orderby_catcount
	 * @property    SLP_Option  default_icons
	 * @property    SLP_Option  hide_empty
	 * @property    SLP_Option  highlight_uncoded
	 * @property    SLP_Option  label_category
	 * @property    SLP_Option  reporting_enabled
	 * @property    SLP_Option  show_cats_on_search
	 * @property    SLP_Option  show_icon_array
	 * @property    SLP_Option  show_legend_text
	 * @property    SLP_Option  show_option_all
	 * @property    SLP_Option  use_contact_fields
	 * @property    SLP_Option  use_nonces
	 * @property    SLP_Option  use_pages
	 * @property    SLP_Option  use_sensor
	 *
	 * TODO: Options for drop downs needs to be hooked to a load_dropdowns method - to offload this overhead so we don't carry around huge arrays for every SLPlus instantiation
	 * note: should be called only when rendering the admin page, the option values should go in the include/module/admin_tabs directory in an SLP_Admin_Experience_Dropdown class
	 */
	class SLP_SmartOptions extends SLPlus_BaseClass_Object {

		// The SLP User-Set Options
		public $admin_notice_dismissed;
		public $distance_unit;
		public $google_server_key;
		public $has_been_setup = false;
		public $initial_radius;
		public $initial_results_returned;
		public $invalid_query_message;
		public $instructions;
		public $label_directions;
		public $label_email;
		public $label_fax;
		public $label_hours;
		public $label_image;
		public $label_phone;
		public $label_radius;
		public $label_search;
		public $label_website;
		public $log_schedule_messages;
		public $map_center;
		public $map_center_lat;
		public $map_center_lng;
		public $message_no_results;
		public $remove_credits;
		public $style_id;
		public $zoom_tweak;

		// Things that help us manage the options.
		private $current_checkboxes;
		protected $change_callbacks = array();
		private $db_loading = false;
		private $initial_distance_already_calculated = false;
		private $initialized = false;
		public $page_layout;
		private $page_options;
		private $smart_properties;
		public $text_options;
		protected $time_callbacks = array();

		/**
		 * Get something for non-existent properties.
		 *
		 * @param $property
		 *
		 * @return SLP_Option|null
		 */
		public function __get( $property ) {
			if ( ! property_exists( $this, $property ) ) {
				return new SLP_Option(
					array(
						'slug' => $property ,
						'add_to_settings_tab' => false,
						'show_label' => false,
					)
				);
			}
		}

		/**
		 * Things we do at the start.
		 */
		public function initialize() {
			if ( $this->initialized ) return;
			require_once( SLPLUS_PLUGINDIR . 'include/unit/SLP_Option.php' );

			$this->create_system_wide_options();
			$this->create_experience_options();
			$this->create_general_options();

			$this->initialized = true;
		}

		/**
		 * Things we do when a new map center is set.
		 *
		 * TODO: look up the address and set the lat/long.
		 *
		 * @param $key
		 * @param $old_val
		 * @param $new_val
		 */
		public function change_map_center( $key, $old_val, $new_val ) {
			$this->map_center_lng->value             = null;
			$this->slplus->options['map_center_lng'] = null;

			$this->map_center_lat->value             = null;
			$this->slplus->options['map_center_lat'] = null;

			$this->slplus->recenter_map();

			$this->recalculate_initial_distance( $key, $old_val, $new_val );
		}

		/**
		 * Run this when the style ID changes.
		 *
		 * @param $key
		 * @param $old_val
		 * @param $new_val
		 */
		public function change_style_id( $key, $old_val, $new_val ) {
			SLP_Style_Manager::get_instance( true )->change_style( $old_val, $new_val );
		}

		/**
		 * System Wide Smart Options
		 */
		private function create_system_wide_options() {

			$smart_options['active_style_css'] = array(
				'default' => <<<ACTIVE_STYLE_CSS
div#map img {
    background-color: transparent;
    box-shadow: none;
    border: 0;
    max-width: none;
    opacity: 1.0
}

div#map div {
    overflow: visible
}

div#map .gm-style-cc > div {
    word-wrap: normal
}

div#map img[src='http://maps.gstatic.com/mapfiles/iws3.png'] {
    display: none
}

.slp_search_form .search_box {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: flex-start;
    align-content: stretch
}

.slp_search_form .search_box .search_item {
    flex: 1 1 auto;
    display: flex;
    align-items: flex-start;
    justify-content: stretch;
    margin-bottom: .25em
}

.slp_search_form .search_box .search_item label {
    text-align: right;
    min-width: 8em;
    margin-right: .25em
}

.slp_search_form .search_box .search_item div {
    flex: 1 1 auto;
    display: flex
}

.slp_search_form .search_box .search_item #radius_in_submit {
    text-align: right
}

.slp_search_form .search_box .search_item .slp_ui_button {
    margin: .25em 0
}

.store_locator_plus.tagline {
    font-size: .75em;
    text-align: right
}

.slp_results_container .results_wrapper {
    margin: .5em 0;
    padding: .5em;
    border: solid 1px lightgrey;
    border-radius: .5em
}

.slp_results_container .results_wrapper:hover {
    background-color: lightgrey;
    border: solid 1px grey
}

.slp_results_container .results_wrapper .location_name {
    font-size: 1.15em
}

.slp_results_container .results_wrapper .location_distance {
    float: right;
    vertical-align: text-top
}
ACTIVE_STYLE_CSS
			);
			$smart_options['active_style_date'] = array();

			$smart_options['admin_notice_dismissed'] = array( 'type' => 'checkbox', 'default' => '0' );

			$smart_options['invalid_query_message'] = array( 'is_text' => true, );

			$this->create_smart_options( $smart_options );

		}

		/**
		 * Experience
		 */
		private function create_experience_options() {
            $this->experience_search_functionality();
            $this->experience_search_appearance();
            $this->experience_search_labels();


            $this->experience_map_at_startup();
            $this->experience_map_functionality();
            $this->experience_map_appearance();
            $this->experience_map_markers();

            $this->experience_results_at_startup();
            $this->experience_results_after_search();
            $this->experience_results_appearance();
            $this->experience_results_labels();

			$this->experience_view_appearance();
		}

		/**
		 * General
		 */
		private function create_general_options() {
			$this->general_admin_messages();
			$this->general_admin_addons();
			$this->general_user_interface();
			$this->general_server_map_services();
			$this->general_server_web_app_settings();
		}

		/**
		 * Create smart option objects and set to default_if_empty values.
		 *
		 * @param array $smart_option_params    An array of smart option attributes.
         * @param array $defaults               If not empty, set this as the default for every smart_option_params entry.
		 */
		public function create_smart_options( $smart_option_params , $defaults=array() ) {

			foreach ( $smart_option_params as $slug => $option_params ) {
				$property = $slug;

				if ( property_exists( $this, $property ) && ! empty( $this->{$property} ) ) {
					continue;
				}

				if ( ! empty( $defaults ) ) {
				    $option_params = array_merge( $option_params , $defaults );
                }

				$option_params['slug'] = $slug;
				$this->{$property}     = new SLP_Option( $option_params );


				// Text Option
				if ( $this->$property->is_text ) {
					$this->text_options[] = $property;
					$this->$property->allow_empty = true;
					if ( empty( $this->$property->default ) ) {
						$this->$property->default =$this->get_string_default( $property );
					}
				}

				// JS / no JS
				if ( $this->$property->use_in_javascript ) {
					$this->slplus->options[ $property ] = $this->$property->default;
				} else {
					$this->slplus->options_nojs[ $property ] = $this->$property->default;
				}

				// Cron Job Registration
				if ( defined( 'DOING_CRON' ) && ! empty( $this->$property->call_when_time ) ) {
					$this->time_callbacks[] = array( $this->$property->call_when_time, $property );
				}

				// List of Smart Option Slugs
				//
				$this->smart_properties[] = $slug;

				// Page Layout
				//
				if ( ! empty( $this->$property->page ) ) {
					if ( empty( $this->$property->section ) ) {
						$this->$property->section = 'default';
					}
					if ( empty ( $this->$property->group ) ) {
						$this->$property->group = 'default';
					}

					$this->page_layout[ $this->$property->page ][ $this->$property->section ][ $this->$property->group ][] = $slug;
				}

			}
		}

		/**
		 * Execute the stack of change callbacks.
		 *
		 * Use this to run callbacks after all options have been updated.
		 */
		public function execute_change_callbacks() {
			if ( ! empty( $this->change_callbacks ) ) {
				foreach ( $this->change_callbacks as $callback_info ) {
					call_user_func_array( $callback_info[0], $callback_info[1] );
				}
				$this->change_callbacks = array();
			}
		}

		/**
		 * Execute the stack of time callbacks.
		 *
		 * Use this to run callbacks after all options have been updated.
		 */
		public function execute_time_callbacks() {
			if ( defined( 'DOING_CRON' ) && ! empty( $this->time_callbacks ) ) {
				foreach ( $this->time_callbacks as $callback_info ) {
					call_user_func( $callback_info[0], $callback_info[1] );
				}
				$this->time_callbacks = array();
			}
		}

		/**
		 * Does the specified slug exist as a smart option?
		 *
		 * @param string $property
		 *
		 * @return boolean
		 */
		public function exists( $property ) {
			return property_exists( $this, $property );
		}

        /**
         * Experience / Map / Appearance
         */
        private function experience_map_appearance() {
            $smart_options[ 'map_height' ]       = array(
                'default'    => '480' ,
                'related_to' => 'map_height_units' ,
            );
            $smart_options[ 'map_height_units' ] = array(
                'default'    => 'px' ,
                'related_to' => 'map_height' ,
                'type'       => 'dropdown' ,
                'options'    => array(
                    array( 'label' => '%' ) ,
                    array( 'label' => 'px' ) ,
                    array( 'label' => 'em' ) ,
                    array( 'label' => 'pt' ) ,
                    array( 'label' => __( 'CSS / inherit' , 'store-locator-le' ) , 'value' => ' ' ) ,
                ) ,
            );
            $smart_options[ 'map_width' ]        = array(
                'default'    => '100' ,
                'related_to' => 'map_width_units' ,
            );
            $smart_options[ 'map_width_units' ]  = array(
                'default'    => '%' ,
                'related_to' => 'map_width' ,
                'type'       => 'dropdown' ,
                'options'    => array(
                    array( 'label' => '%' ) ,
                    array( 'label' => 'px' ) ,
                    array( 'label' => 'em' ) ,
                    array( 'label' => 'pt' ) ,
                    array( 'label' => __( 'CSS / inherit' , 'store-locator-le' ) , 'value' => ' ' ) ,
                ) ,
            );
            $smart_options[ 'map_type' ]         = array(
                'default'           => 'roadmap' ,
                'type'              => 'dropdown' ,
                'use_in_javascript' => true ,
                'options'           => array(
                    array( 'label' => 'Roadmap' , 'value' => 'roadmap' ) ,
                    array( 'label' => 'Hybrid' , 'value' => 'hybrid' ) ,
                    array( 'label' => 'Satellite' , 'value' => 'satellite' ) ,
                    array( 'label' => 'Terrain' , 'value' => 'terrain' ) ,
                ) ,
            );
            $smart_options[ 'remove_credits' ]   = array(
                'type'    => 'checkbox' ,
                'default' => '0' ,
            );
            $smart_options[ 'maplayout' ]        = array(
                'use_in_javascript'   => false ,
                'type'                => 'textarea' ,
                'add_to_settings_tab' => false ,
                'default'             => '[slp_mapcontent][slp_maptagline]'
            );
            $smart_options[ 'bubblelayout' ]     = array(
                'use_in_javascript'   => true ,
                'type'                => 'textarea' ,
                'add_to_settings_tab' => false ,
                'default'             => <<<BUBBLELAYOUT
<div id="slp_info_bubble_[slp_location id]" class="slp_info_bubble [slp_location featured]">
    <span id="slp_bubble_name"><strong>[slp_location name  suffix  br]</strong></span>
    <span id="slp_bubble_address">[slp_location address       suffix  br]</span>
    <span id="slp_bubble_address2">[slp_location address2      suffix  br]</span>
    <span id="slp_bubble_city">[slp_location city          suffix  comma]</span>
    <span id="slp_bubble_state">[slp_location state suffix    space]</span>
    <span id="slp_bubble_zip">[slp_location zip suffix  br]</span>
    <span id="slp_bubble_country"><span id="slp_bubble_country">[slp_location country       suffix  br]</span></span>
    <span id="slp_bubble_directions">[html br ifset directions]
    [slp_option label_directions wrap directions]</span>
    <span id="slp_bubble_website">[html br ifset url][slp_location web_link][html br ifset url]</span>
    <span id="slp_bubble_email">[slp_location email         wrap    mailto ][slp_option label_email ifset email][html closing_anchor ifset email][html br ifset email]</span>
    <span id="slp_bubble_phone">[html br ifset phone]
    <span class="location_detail_label">[slp_option   label_phone   ifset   phone]</span>[slp_location phone         suffix    br]</span>
    <span id="slp_bubble_fax"><span class="location_detail_label">[slp_option   label_fax     ifset   fax  ]</span>[slp_location fax           suffix    br]<span>
    <span id="slp_bubble_description"><span id="slp_bubble_description">[html br ifset description]
    [slp_location description raw]</span>[html br ifset description]</span>
    <span id="slp_bubble_hours">[html br ifset hours]
    <span class="location_detail_label">[slp_option   label_hours   ifset   hours]</span>
    <span class="location_detail_hours">[slp_location hours         suffix    br]</span></span>
    <span id="slp_bubble_img">[html br ifset img]
    [slp_location image         wrap    img]</span>
    <span id="slp_tags">[slp_location tags]</span>
</div>
BUBBLELAYOUT
            );
            $this->create_smart_options( $smart_options , array( 'page' => 'slp_experience' , 'section' => 'map' , 'group' => 'appearance' ) );
        }

        /**
         * Experience / Map / At Startup
         */
		private function experience_map_at_startup() {
            $smart_options[ 'map_center' ]     = array(
                'type'              => 'textarea' ,
                'related_to'        => 'map_center_lat,map_center_lng' ,
                'use_in_javascript' => true ,
                'call_when_changed' => array( $this , 'change_map_center' ) ,
            );
            $smart_options[ 'map_center_lat' ] = array(
                'related_to'        => 'map_center,map_center_lng' ,
                'use_in_javascript' => true ,
                'call_when_changed' => array( $this , 'recalculate_initial_distance' ) ,
            );
            $smart_options[ 'map_center_lng' ] = array(
                'related_to'        => 'map_center,map_center_lat' ,
                'use_in_javascript' => true ,
                'call_when_changed' => array( $this , 'recalculate_initial_distance' ) ,
            );
            $this->create_smart_options( $smart_options , array( 'page' => 'slp_experience' , 'section' => 'map' , 'group' => 'at_startup' ) );
        }

        /**
         * Experience / Map / Functionality
         */
        private function experience_map_functionality() {
            $smart_options['zoom_level'] = array(
                'type'              => 'dropdown',
                'default'           => '12',
                'use_in_javascript' => true,
                'options'           => array(
                    array( 'label' => '0' ),
                    array( 'label' => '1' ),
                    array( 'label' => '2' ),
                    array( 'label' => '3' ),
                    array( 'label' => '4' ),
                    array( 'label' => '5' ),
                    array( 'label' => '6' ),
                    array( 'label' => '7' ),
                    array( 'label' => '8' ),
                    array( 'label' => '9' ),
                    array( 'label' => '10' ),
                    array( 'label' => '11' ),
                    array( 'label' => '12' ),
                    array( 'label' => '13' ),
                    array( 'label' => '14' ),
                    array( 'label' => '15' ),
                    array( 'label' => '16' ),
                    array( 'label' => '17' ),
                    array( 'label' => '18' ),
                    array( 'label' => '19' ),
                ),
            );
            $smart_options['zoom_tweak'] = array(
                'type'              => 'dropdown',
                'default'           => '0',
                'use_in_javascript' => true,
                'options'           => array(
                    array( 'label' => '-10' ),
                    array( 'label' => '-9' ),
                    array( 'label' => '-8' ),
                    array( 'label' => '-7' ),
                    array( 'label' => '-6' ),
                    array( 'label' => '-5' ),
                    array( 'label' => '-4' ),
                    array( 'label' => '-3' ),
                    array( 'label' => '-2' ),
                    array( 'label' => '-1' ),
                    array( 'label' => '0' ),
                    array( 'label' => '1' ),
                    array( 'label' => '2' ),
                    array( 'label' => '3' ),
                    array( 'label' => '4' ),
                    array( 'label' => '5' ),
                    array( 'label' => '6' ),
                    array( 'label' => '7' ),
                    array( 'label' => '8' ),
                    array( 'label' => '9' ),
                    array( 'label' => '10' ),
                    array( 'label' => '11' ),
                    array( 'label' => '12' ),
                    array( 'label' => '13' ),
                    array( 'label' => '14' ),
                    array( 'label' => '15' ),
                    array( 'label' => '16' ),
                    array( 'label' => '17' ),
                    array( 'label' => '18' ),
                    array( 'label' => '19' ),
                ),
            );
            $this->create_smart_options( $smart_options , array( 'page' => 'slp_experience' , 'section' => 'map' , 'group' => 'functionality' ) );
        }

        /**
         * Experience / Map / Markers
         */
        private function experience_map_markers() {
            $smart_options['map_home_icon'] = array(
                'page'              => 'slp_experience',
                'section'           => 'map',
                'group'             => 'markers',
                'type'              => 'icon',
                'use_in_javascript' => true,
                'default'           => SLPLUS_ICONURL . 'bulb_yellow.png',

            );
            $smart_options['map_end_icon']  = array(
                'page'              => 'slp_experience',
                'section'           => 'map',
                'group'             => 'markers',
                'type'              => 'icon',
                'use_in_javascript' => true,
                'default'           => SLPLUS_ICONURL . 'bulb_azure.png',
            );
            $this->create_smart_options( $smart_options , array( 'page' => 'slp_experience' , 'section' => 'map' , 'group' => 'markers' ) );
        }

        /**
         * Experience / Results / At Startup
         */
        private function experience_results_at_startup() {
            $smart_options[ 'immediately_show_locations' ] = array(
                'page'              => 'slp_experience' ,
                'section'           => 'results' ,
                'group'             => 'at_startup' ,
                'type'              => 'checkbox' ,
                'default'           => '1' ,
                'use_in_javascript' => true ,
            );
            $smart_options[ 'initial_radius' ]             = array(
                'page'              => 'slp_experience' ,
                'section'           => 'results' ,
                'group'             => 'at_startup' ,
                'default'           => '' ,
                'use_in_javascript' => true ,
            );
            $smart_options[ 'initial_results_returned' ]   = array(
                'page'              => 'slp_experience' ,
                'section'           => 'results' ,
                'group'             => 'at_startup' ,
                'default'           => '25' ,
                'use_in_javascript' => false ,
            );
            $this->create_smart_options( $smart_options , array( 'page' => 'slp_experience' , 'section' => 'results' , 'group' => 'at_startup' ) );
        }

        /**
         * Experience / Results / After Search
         */
        private function experience_results_after_search() {

            // After Search
            $smart_options[ 'max_results_returned' ] = array(
                'page'    => 'slp_experience' ,
                'section' => 'results' ,
                'group'   => 'after_search' ,
                'default' => '25' ,
            );
            $this->create_smart_options( $smart_options , array( 'page' => 'slp_experience' , 'section' => 'results' , 'group' => 'after_search' ) );
        }

        /**
         * Experience / Results / Appearance
         */
        private function experience_results_appearance() {
            $smart_options[ 'message_no_results' ] = array(
                'is_text'           => true ,
                'use_in_javascript' => true ,
            );

	        $smart_options[ 'message_bad_address' ] = array(
		        'is_text'           => true ,
		        'use_in_javascript' => true ,
		        'add_to_settings_tab' => false,
	        );

            $smart_options[ 'loading_indicator' ] = array(
            	'type'  => 'dropdown',
                'default'    => '' ,
                'use_in_javascript' => true,
	            'related_to' => 'loading_indicator,loading_indicator_location, loading_indicator_color' ,
	            'classes'       => array( 'quick_save' ),
                'options'    => array(
		            array( 'label' => 'None' , 'value' => '' ) ,
	            ) ,
            );

            $smart_options[ 'resultslayout' ]      = array(
                'page'                => 'slp_experience' ,
                'section'             => 'results' ,
                'group'               => 'appearance' ,
                'use_in_javascript'   => true,
                'type'                => 'textarea' ,
                'add_to_settings_tab' => false ,
                'default'             => <<<RESULTSLAYOUT
<div id="slp_results_[slp_location id]" class="results_entry location_primary [slp_location featured]">
    <div class="results_row_left_column"   id="slp_left_cell_[slp_location id]"   >
        [slp_addon section=primary position=first]
        <span class="location_name">[slp_location name] [slp_location uml_buttons] [slp_location gfi_buttons]</span>
        <span class="location_distance">[slp_location distance_1] [slp_location distance_unit]</span>
        [slp_addon section=primary position=last]
    </div>
    <div class="results_row_center_column location_secondary" id="slp_center_cell_[slp_location id]" >
        [slp_addon section=secondary position=first]
        <span class="slp_result_address slp_result_street">[slp_location address]</span>
        <span class="slp_result_address slp_result_street2">[slp_location address2]</span>
        <span class="slp_result_address slp_result_citystatezip">[slp_location city_state_zip]</span>
        <span class="slp_result_address slp_result_country">[slp_location country]</span>
        <span class="slp_result_address slp_result_phone">[slp_location phone]</span>
        <span class="slp_result_address slp_result_fax">[slp_location fax]</span>
        [slp_addon section=secondary position=last]
    </div>
    <div class="results_row_right_column location_tertiary"  id="slp_right_cell_[slp_location id]"  >
        [slp_addon section=tertiary position=first]
        <span class="slp_result_contact slp_result_website">[slp_location web_link]</span>
        <span class="slp_result_contact slp_result_email">[slp_location email_link]</span>
        <span class="slp_result_contact slp_result_directions"><a href="https://[slp_option map_domain]/maps?saddr=[slp_location search_address]&daddr=[slp_location location_address]" target="_blank" class="storelocatorlink">[slp_location directions_text]</a></span>
        <span class="slp_result_contact slp_result_hours">[slp_location hours]</span>
        [slp_location pro_tags]
        [slp_location iconarray wrap="fullspan"]
        [slp_location eventiconarray wrap="fullspan"]
        [slp_location socialiconarray wrap="fullspan"]
        [slp_addon section=tertiary position=last]
    </div>
</div>
RESULTSLAYOUT
            );
            $this->create_smart_options( $smart_options , array( 'page' => 'slp_experience' , 'section' => 'results' , 'group' => 'appearance' ) );
        }

        /**
         * Experience / Results / Labels
         */
        private function experience_results_labels() {

            $smart_options[ 'instructions' ]     = array(
                'page'    => 'slp_experience' ,
                'section' => 'results' ,
                'group'   => 'labels' ,
                'is_text' => true ,
            );
            $smart_options[ 'label_website' ]    = array(
                'page'              => 'slp_experience' ,
                'section'           => 'results' ,
                'group'             => 'labels' ,
                'is_text'           => true ,
                'use_in_javascript' => true ,
            );
            $smart_options[ 'label_directions' ] = array(
                'page'              => 'slp_experience' ,
                'section'           => 'results' ,
                'group'             => 'labels' ,
                'is_text'           => true ,
                'use_in_javascript' => true ,
            );
            $smart_options[ 'label_hours' ]      = array(
                'page'    => 'slp_experience' ,
                'section' => 'results' ,
                'group'   => 'labels' ,
                'is_text' => true ,
            );
            $smart_options[ 'label_email' ]      = array(
                'page'              => 'slp_experience' ,
                'section'           => 'results' ,
                'group'             => 'labels' ,
                'is_text'           => true ,
                'use_in_javascript' => true ,
            );
            $smart_options[ 'label_phone' ]      = array(
                'page'              => 'slp_experience' ,
                'section'           => 'results' ,
                'group'             => 'labels' ,
                'is_text'           => true ,
                'use_in_javascript' => true ,
            );
            $smart_options[ 'label_fax' ]        = array(
                'page'              => 'slp_experience' ,
                'section'           => 'results' ,
                'group'             => 'labels' ,
                'is_text'           => true ,
                'use_in_javascript' => true ,
            );
            $smart_options[ 'label_image' ]      = array(
                'page'    => 'slp_experience' ,
                'section' => 'results' ,
                'group'   => 'labels' ,
                'is_text' => true ,
            );
            $this->create_smart_options( $smart_options , array( 'page' => 'slp_experience' , 'section' => 'results' , 'group' => 'labels' ) );
        }

        /**
         * Experience / Search / Appearance
         */
        private function experience_search_appearance() {
            $smart_options['searchlayout'] = array(
                'use_in_javascript'  => false,
                'type'               => 'textarea',
                'add_to_settings_tab' => false,
                'default'           => <<<SEARCHLAYOUT
<div id="address_search" class="slp search_box">
    [slp_addon location="very_top"]
    [slp_search_element input_with_label="name"]
    [slp_search_element input_with_label="address"]
    [slp_search_element dropdown_with_label="city"]
    [slp_search_element dropdown_with_label="state"]
    [slp_search_element dropdown_with_label="country"]
    [slp_search_element selector_with_label="tag"]
    [slp_search_element dropdown_with_label="category"]
    [slp_search_element dropdown_with_label="gfl_form_id"]
    [slp_addon location="before_radius_submit"]
    <div class="search_item">
        [slp_search_element dropdown_with_label="radius"]
        [slp_search_element button="submit"]
    </div>
    [slp_addon location="after_radius_submit"]
    [slp_addon location="very_bottom"]
</div>
SEARCHLAYOUT
            );

            $smart_options[ 'dropdown_style' ] = array(
                'type'              => 'dropdown',
                'default'           => 'none',
                'use_in_javascript' => true,
                'options'           => array(
                    array( 'label' => __( 'None', 'store-locator-le' ), 'value' => 'none' ),
                    array( 'label' => __( 'Base', 'store-locator-le' ), 'value' => 'base' ),
                ),

            );
            $this->create_smart_options( $smart_options , array( 'page' => 'slp_experience' , 'section' => 'search' , 'group' => 'appearance' ) );
        }

        /**
         * Experience / Search / Functionality
         */
        private function experience_search_functionality() {
            $smart_options['distance_unit'] = array(
                'default'           => 'miles',
                'call_when_changed' => array( $this, 'recalculate_initial_distance' ),
                'use_in_javascript' => true,
                'type'              => 'dropdown',
                'options'           => array(
                    array( 'label' => __( 'Kilometers', 'store-locator-le' ), 'value' => 'km' ),
                    array( 'label' => __( 'Miles', 'store-locator-le' ), 'value' => 'miles' ),
                ),
            );
            $smart_options['radii']         = array(
                'default'           => '10,25,50,100,(200),500',
                'use_in_javascript' => false,
            );
            $this->create_smart_options( $smart_options , array( 'page' => 'slp_experience' , 'section' => 'search' , 'group' => 'functionality' ) );
        }

        /**
         * Experience / Search / Labels
         */
        private function experience_search_labels() {
            $smart_options['label_radius'] = array(
                'is_text' => true,
            );
            $smart_options['label_search'] = array(
                'is_text'    => true,
                'related_to' => 'address_placeholder,hide_address_entry',
                'classes'       => array( 'quick_save' )
            );
            $this->create_smart_options( $smart_options , array( 'page' => 'slp_experience' , 'section' => 'search' , 'group' => 'labels' ) );
        }

        /**
         * Experience / View / Appearance
         */
        private function experience_view_appearance() {
            $smart_options['style_id'] = array(
                'type'              => 'hidden',
                'classes'       => array( 'quick_save' ),
                'call_when_changed' => array( $this, 'change_style_id' ),
            );
            $smart_options['theme']    = array(
                'type'    => 'list',
                'get_items_callback' => array( $this , 'get_theme_items' ),
                'classes'       => array( 'quick_save' ),
                'default' => 'a_gallery_style',
            );
            $smart_options['layout'] = array(
                'type'              => 'textarea',
                'add_to_settings_tab' => false,
                'classes'       => array( 'quick_save' ),
                'default'           => '<div id="sl_div">[slp_search][slp_map][slp_results]</div>'
            );
	        $smart_options['style']    = array(
		        'type'    => 'style_vision_list',
	        );

	        $this->create_smart_options( $smart_options , array( 'page' => 'slp_experience' , 'section' => 'view' , 'group' => 'appearance' ) );
        }

		/**
		 * General > Admin > Addons
		 */
        private function general_admin_addons() {
	        $smart_options['slp_userid'] = array( 'call_when_changed' => array( $this , 'trim' ) );
	        $smart_options['slp_apikey'] = array( 'call_when_changed' => array( $this , 'trim' ) );

	        $this->create_smart_options( $smart_options , array( 'page' => 'slp_general' , 'section' => 'admin' , 'group' => 'add_on_packs' ) );
        }

        /**
         * General / Admin
         */
        private function general_admin_messages() {
	        $smart_options['enable_wp_debug'] = array( 'type'    => 'checkbox', 'default' => '0', );
            $smart_options['log_schedule_messages'] = array( 'type'    => 'checkbox', 'default' => '0', );
            $this->create_smart_options( $smart_options , array( 'page' => 'slp_general' , 'section' => 'admin' , 'group' => 'messages' ) );
        }

		/**
		 * General / Server / Map Services
		 */
        private function general_server_map_services() {
	        $smart_options['google_geocode_key'] = array( 'call_when_changed' => array( $this , 'trim' ) );         // Backend Server Key (geocoding)
	        $smart_options['google_server_key'] = array( 'call_when_changed' => array( $this , 'trim' ) );          // Front End Browser Key
	        $this->create_smart_options( $smart_options , array( 'page' => 'slp_general' , 'section' => 'server' , 'group' => 'map_services' ) );
        }

        /**
         * General / Server / Web App Settings
         */
        private function general_server_web_app_settings() {
            $smart_options['php_max_execution_time'] = array( 'default' => '600', );
            $this->create_smart_options( $smart_options , array( 'page' => 'slp_general' , 'section' => 'server' , 'group' => 'web_app_settings' ) );
        }

        private function general_user_interface() {
	        $new_options[ 'url_control_description' ] = array( 'type' => 'subheader' );
	        $this->create_smart_options( $new_options , array( 'page' => 'slp_general' , 'section' => 'user_interface' , 'group' => 'url_control' ) );
        }

		/**
		 * Return the property formatted option name.
		 *
		 * @param $property
		 *
		 * @return string
		 */
		public function get_option_name( $property ) {
			if ( property_exists( $this, $property ) ) {
				$base_setting = $this->$property->use_in_javascript ? 'options' : 'options_nojs';

				return "${base_setting}[{$property}]";
			}

			return $property;
		}

		/**
		 * Get a list of Smart Options
		 *
		 * @uses \SLP_SmartOptions::only_smart_options
		 */
		public function get_options() {
			return array_keys( array_filter(  get_object_vars( $this ) , 'self::only_smart_options' ) );
		}

		/**
		 * Get a list of options that reside on the specified admin page.
		 *
		 * @param string $admin_page
		 *
		 * @return array
		 */
		public function get_page_options( $admin_page ) {
			if ( ! isset( $this->page_options[ $admin_page ] ) ) {
				$this->page_options[ $admin_page ] = array();
				if ( empty( $this->page_layout[ $admin_page ] ) ) $this->page_layout[ $admin_page ] = array();
				foreach ( $this->page_layout[ $admin_page ] as $sections ) {
					foreach ( $sections as $groups ) {
						foreach ( $groups as $property ) {
							$this->page_options[ $admin_page ][] = $property;
						}
					}
				}
			}
			return $this->page_options[ $admin_page ];
		}

		/**
		 * Return true if the element is an SLP_Option -- call get_options()
		 *
		 * @used-by \SLP_SmartOptions::get_options
		 *
		 * @param mixed $element
		 *
		 * @return bool
		 */
		public static function only_smart_options( $element ) {
			return is_a( $element , 'SLP_Option' );
		}

		/**
		 * Remember the original value of a setting before we change it.
		 *
		 * @param $new_value
		 * @param $key
		 * @param $option_array
		 * @param $is_smart_option
		 * @param $valid_legacy_option
		 *
		 * @return mixed
		 */
		private function get_original_value( $new_value, $key, &$option_array, $is_smart_option, $valid_legacy_option ) {

			// Invalid Setting - null
			if ( ! $is_smart_option && ! $valid_legacy_option ) {
				return null;
			}

			// Loading from DB - use db value
			if ( $this->db_loading ) {
				return $new_value;
			}

			// Smart option - return value (it reads from options array or default as needed)
			if ( $is_smart_option ) {
				return $this->$key->value;
			}

			// Send back original value
			if ( $valid_legacy_option ) {
				return $option_array[ $key ];
			}

			return null;
		}

		/**
		 * Get the parameters needed for the SLP_Settings entry.
		 *
		 * @param array $params
		 *
		 * @return array
		 */
		public function get_setting_params( $params ) {
			$option = $this->{$params['option']};

			$property_params = array( 'get_items_callback' , 'classes' , 'description' , 'related_to', 'show_label' , 'type', 'value' );
			foreach ( $property_params as $param ) {
				$params[ $param ] = $option->{$param};
				if ( ( $param === 'description' ) && ! empty( $option->default ) ) {
					$params[ $param ] .= '<br/>' . sprintf( __( 'Default: %s' , 'store-locator-le' ) , $option->default  );
				}
			}

			$params['option_name'] = 'smart_option';
			$params['use_prefix']  = false;

			$params['selectedVal'] = $params['value'];

			$params['setting'] = $this->get_option_name( $params['option'] );
			$params['name']    = $params['setting'];

			if ( $params['show_label'] ) {
				$params['label'] = $option->label;
			}

			if ( $params['type'] === 'dropdown' ) {
				if ( ! empty( $option->options ) ) {
					foreach ( $option->options as $dropdown_option ) {
						if ( ! empty( $dropdown_option['description'] ) ) {
							$params['description'] .= sprintf( '<p class="selections"><span class="label">%s</span><span class="function">%s</span>', $dropdown_option['label'], $dropdown_option['description'] );
						}
					}
				}
			}

			$params['custom'] = $option->options;

			$params['empty_ok'] = true;

			unset( $params['option'] );
			unset( $params['plugin'] );

			return $params;
		}

		/**
		 * Get string defaults.
		 *
		 * @param string $key key name for string to translate
		 *
		 * @return string
		 */
		private function get_string_default( $key ) {
			$text = SLP_Text::get_instance();
			$text_to_return = $text->get_text_string( array( 'option_default', $key ) );
			if ( empty( $text_to_return ) ) {
				$text_to_return = apply_filters( 'slp_string_default', '', $key );
			}

			return $text_to_return;
		}

		/**
		 * Return a list of option slugs that are text options.
		 *
		 * @return string[]
		 */
		public function get_text_options() {
			if ( ! isset( $this->text_options ) ) {
				$smart_options = get_object_vars( $this );
				foreach ( $smart_options as $slug => $option ) {
					if ( $option->is_text ) {
						$this->text_options[] = $option->slug;
					}
				}
			}

			return $this->text_options;
		}

		/**
		 * Callback for getting theme (plugin style) item list.
		 */
		public function get_theme_items() {
			return SLP_Style::get_instance( true )->get_theme_list();
		}

		/**
		 * Things we do once after the plugins are loaded.
		 */
		public function initialize_after_plugins_loaded() {
			$this->set_text_string_defaults();
			$this->slp_specific_setup();
		}

		/**
		 * Recalculate the initial distance for a location from the map center.
		 *
		 * Called if 'distance_unit' changes.
         *
         * call_when_changed alway gets 3 params whether or not they are used...
         *
         * @param $key
         * @param $old_val
         * @param $new_val
         */
		public function recalculate_initial_distance( $key, $old_val, $new_val ) {
			if ( ! $this->initial_distance_already_calculated ) {
				require_once( SLPLUS_PLUGINDIR . 'include/module/location/SLP_Location_Manager.php' );
				$this->slplus->Location_Manager->recalculate_initial_distance();
				$this->initial_distance_already_calculated = true;
			}
		}


		/**
		 * Save the options for the given admin page.
		 *
		 * @param string $admin_page
		 */
		public function save( $admin_page = '' ) {
			if ( empty( $admin_page ) ) $admin_page = SLP_Settings::get_instance()->current_admin_page;

			$this->set_checkboxes( $admin_page );

			if ( ! empty( $_REQUEST[ 'options' ] ) ) {
				array_walk( $_REQUEST['options'], array( $this, 'set_valid_options' ) );
			}

			if ( ! empty( $_REQUEST[ 'options_nojs' ] ) ) {
				array_walk( $_REQUEST['options_nojs'], array( $this, 'set_valid_options_nojs' ) );
			}

			$this->execute_change_callbacks();       // Anything changed?  Execute their callbacks.
			$this->slplus->WPOption_Manager->update_wp_option( 'js' );        // Change callbacks may interact with JS or NOJS, make sure both are saved after ALL callbacks
			$this->slplus->WPOption_Manager->update_wp_option( 'nojs' );

		}

		/**
		 * Set the smart option value and the legacy options/options_nojs
		 *
		 * @param $property
		 * @param $value
		 */
		public function set( $property, $value ) {
			if ( property_exists( $this, $property ) ) {
				$this->$property->value = $value;

				if ( $this->$property->use_in_javascript ) {
					$this->set_valid_options( $value, $property );
				} else {
					$this->set_valid_options_nojs( $value, $property );
				}
			}
		}

		/**
		 * Set text string defaults.
		 */
		private function set_text_string_defaults() {
			foreach ( $this->get_text_options() as $key ) {

				if ( array_key_exists( $key, $this->slplus->options ) ) {
					$this->slplus->options[ $key ] = $this->get_string_default( $key );

				} elseif ( array_key_exists( $key, $this->slplus->options_nojs ) ) {
					$this->slplus->options_nojs[ $key ] = $this->get_string_default( $key );

				}
			}
		}

		/**
		 * Initialize the options properties from the WordPress database.
		 *
		 * Called by MySLP Dashboard.
		 */
		public function slp_specific_setup() {
			do_action( 'start_slp_specific_setup' );

			// Serialized Options from DB for JS parameters
			//
			$this->slplus->options_default = $this->slplus->options;
			$dbOptions                     = $this->slplus->WPOption_Manager->get_wp_option( 'js' );
			if ( is_array( $dbOptions ) ) {
				$this->db_loading = true;
				array_walk( $dbOptions, array( $this, 'set_valid_options' ) );
				$this->db_loading = false;
			}

			// Map Center Fallback
			//
			$this->slplus->recenter_map();

			// Load serialized options for noJS parameters
			//
			$this->slplus->options_nojs_default = $this->slplus->options_nojs;
			$dbOptions                          = $this->slplus->WPOption_Manager->get_wp_option( 'nojs' );
			if ( is_array( $dbOptions ) ) {
				$this->db_loading = true;
				array_walk( $dbOptions, array( $this, 'set_valid_options_nojs' ) );
				$this->db_loading = false;
			}
			$this->slplus->javascript_is_forced = $this->slplus->is_CheckTrue( $this->slplus->options_nojs['force_load_js'] );

			$this->has_been_setup = true;
			do_action( 'finish_slp_specific_setup' );
		}

		/**
		 * Set incoming REQUEST checkboxes for the current admin page.   Only run on admin pages.
		 *
		 * @param   string  $admin_page
		 */
		public function set_checkboxes( $admin_page ) {
			$this->set_current_checkboxes( $admin_page );
			if ( is_array( $this->current_checkboxes ) ) {
				foreach ( $this->current_checkboxes as $property ) {
					$which_option = $this->$property->use_in_javascript ? 'options' : 'options_nojs';
					if ( isset( $_REQUEST[ $which_option ][ $this->$property->slug ] ) ) {
						continue;
					}
					$_REQUEST[ $which_option ][ $this->$property->slug ] = '0';
				}
			}
		}

		/**
		 * Builds a list of checkboxes for the current admin settings page.
		 *
		 * @param   string  $admin_page
		 */
		private function set_current_checkboxes( $admin_page ) {
			if ( empty( $this->current_checkboxes ) ) {
				$this->get_page_options( $admin_page );
				foreach ( $this->page_options[ $admin_page ] as $property ) {
					if ( $this->$property->type === 'checkbox' ) {
						$this->current_checkboxes[] = $property;
					}
				}
			}
		}

		/**
		 * Set the value of a smart option & legacy option array copy
		 *
		 * @param $value
		 * @param $key
		 * @param $option_array
		 * @param $is_smart_option
		 * @param $valid_legacy_option
		 */
		private function set_the_val( $value, $key, &$option_array, $is_smart_option, $valid_legacy_option ) {
			if ( $is_smart_option && is_object( $this->{$key} ) ) {
				$this->$key->__set( 'value' , stripslashes( $value ) );    // Smart Option objects don't want slashes.

				if ( $this->{$key}->type === 'textarea' ) {
					$value = stripslashes( $value );
				} elseif ( $this->{$key}->type === 'checkbox' ) {
					$value = $this->{$key}->is_true ? '1' : '0';
				}

				$option_array[ $key ] = $value;                 // The options and options_nojs arrays are stored in the DB and need slashes.

			} elseif ( $valid_legacy_option ) {
				$option_array[ $key ] = $value;

			}
		}

		/**
		 * Set an option in an array only if the key already exists, for empty values set to default.
		 *
		 * External classes should use set_valid_options / set_valid_options_nojs.
		 *
		 * @param mixed  $val           the value of a form var
		 * @param string $key           the key for that form var
		 * @param string $which_option  which array to use
		 */
		private function set_valid_option( $val, $key, $which_option ) {
			$default_option = $which_option . '_default';

			$valid_legacy_option = array_key_exists( $key , $this->slplus->{$which_option} );

			$is_smart_option     = property_exists( $this, $key );
			if ( $is_smart_option ) {
				if ( $valid_legacy_option ) {
					$valid_legacy_option = $this->slplus->SmartOptions->{$key}->use_in_javascript ? ( $which_option === 'options' ) : ( $which_option === 'options_nojs' );
				}
				if ( ! $valid_legacy_option ) return;
			}

			$original_value = null;

			// Remember the original value for smart options when not loading from DB
			if ( ! $this->db_loading && $is_smart_option ) {
				$original_value = $this->get_original_value( $val, $key, $this->slplus->{$which_option}, $is_smart_option, $valid_legacy_option );
			}

			// Set the value
			$this->set_the_val( $val, $key, $this->slplus->{$which_option}, $is_smart_option, $valid_legacy_option );

			// Loading from DB - our work is done
			if ( $this->db_loading ) {
				return;
			}

			// Not a smart option or valid legacy option - no need for defaults or change callbacks
			if ( ! $is_smart_option && ! $valid_legacy_option ) {
				return;
			}

			// Setting an option?  Set defaults if it comes in empty.
			$value_is_empty = ! ( is_numeric( $val ) || is_bool( $val ) || ! empty( $val ) );
			if ( $value_is_empty ) {
				$default_value = $is_smart_option ? ( $this->$key->allow_empty ? $val : $this->$key->default ) : $this->slplus->{$default_option}[ $key ];
				$this->set_the_val( $default_value, $key, $this->slplus->{$which_option}, $is_smart_option, $valid_legacy_option );
			}

			// Set callbacks for option changes.
			if ( $is_smart_option ) {
				$this->setup_smart_callback( $key, $original_value );
			}
		}

		/**
		 * Set valid slplus->options and copy to smart_options
		 *
		 * @param $val
		 * @param $key
		 */
		public function set_valid_options( $val, $key ) {
			$this->set_valid_option( $val, $key, 'options' );
		}

		/**
		 * Set valid slplus->options_nojs and copy to smart_options
		 *
		 * @param $val
		 * @param $key
		 */
		public function set_valid_options_nojs( $val, $key ) {
			$this->set_valid_option( $val, $key, 'options_nojs' );
		}

		/**
		 * Set value change callback methods for smart options.
		 *
		 * That are defined as on this page (or the page is not defined)
		 * Whose original value from slplus->options or slplus->options_nojs DOES NOT match the new value (from the DB usually)
		 * ... reset the original value temp var to the smart option default value (if provided , null if not provided)
		 * ... and set the smart option to the new value if not empty or the smart option default if the new value was empty
		 *
		 * @param $key
		 * @param $original_value
		 */
		private function setup_smart_callback( $key, $original_value ) {
			if ( ! empty( $this->$key->call_when_changed ) && ( $this->$key->value !== $original_value ) ) {
				$this->change_callbacks[] = array(
					$this->$key->call_when_changed,
					array( $key, $original_value, $this->$key->value ),
				);
			}
		}

		/**
		 * Strip slashes from value if this is a text entry.
		 *
		 * @param string $val
		 * @param string $key
		 */
		public function strip_slashes_if_text( &$val , $key ) {
			if ( ! property_exists( $this, $key ) ) return;
			if ( ! $this->$key->is_text ) return;
			$val = stripslashes( $val );
		}

		/**
		 * Trim the string.
		 *
		 * @param string $key
		 * @param string $old_val
		 * @param string $new_val
		 */
		public function trim ( $key, $old_val, $new_val ) {
			$this->set( $key , trim( $new_val ) );
		}

	}

	/**
	 * Make use - creates as a singleton attached to slplus->object['SmartOptions']
	 *
	 * @var SLPlus $slplus
	 */
	global $slplus;
	if ( is_a( $slplus, 'SLPlus' ) ) {
		$slplus->add_object( SLP_SmartOptions::get_instance() );
		$slplus->smart_options = $slplus->SmartOptions; // TODO: remove this when all things refer to SmartOptions not smart_options
	}
}