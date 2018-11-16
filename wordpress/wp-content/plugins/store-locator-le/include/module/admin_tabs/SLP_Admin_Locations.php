<?php
defined( 'ABSPATH'     ) || exit;
if ( ! class_exists( 'SLP_Admin_Locations' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

	/**
	 * Store Locator Plus manage locations admin user interface.
	 *
	 * @property        boolean                 $addingLocation                Adding a location? (default: false)
	 * @property        array                   $active_columns                Active extended data columns.
	 *                                               metatable['records'][] from the SLPlus_Data_Extension class
	 * @property        string                  $baseAdminURL
	 * @property-read   array                   $buttons                        The action buttons array.
	 * @property-read   string                  $cleanAdminURl
	 * @property-read   string                  $cleanURL                      The current request URL without order by or sorting parameters.
	 * @property        array                   $columns                       The Manage Locations interface column names. key is the field name, value is the column title
	 * @property        string                  $current_action                The current action as determined by the incoming $_REQUEST['act'] string.
	 * @property-read   string                  $db_orderbyfield               Order by field for the order by clause.
	 * @property-read   array                   $empty_columns                 A list of empty column field IDs. key is the field name, value is the column title
	 * @property-read   string                  $extra_location_filters        Extra database where clause filters.
	 *                                                This is leftover from legacy code where this admin ui locations class
	 *                                                had its own custom data handler for managing where clauses and order by
	 *                                                vs. the current data class standard sql methods.
	 * @property        string                  $hangoverURL                   The manage locations URL with params we like to keep such as page number and sort order.
	 * @property-read   array                   $script_data                   Things to be localized for the data tables js.
	 * @property        SLP_Settings            $settings
	 * @property-read   SLPlus                  $slplus
	 * @property-read   string                  $sort_order                    Requested sort order.
	 * @property-read   int                     $start                         Start listing locations from this record offset.
	 * @property-read   int                     $total_locations_shown         Total locations on list.
	 */
	class SLP_Admin_Locations extends WP_List_Table {
		public  $addingLocation         = false;
		public  $active_columns;
		public  $baseAdminURL           = '';
		private $buttons;
		private $cleanAdminURL          = '';
		private $cleanURL;
		public  $columns                = array();
		public  $current_action;
		private $db_orderbyfield        = 'sl_store';
		private $empty_columns          = array();
		private $extra_location_filters = '';
		public  $hangoverURL            = '';
		private $screen_id;
		private $script_data            = array(
		        'locations' => array(),
		        'notices' => array()
        );
		public  $settings;
		private $slplus;
		private $sort_order             = 'asc';
		private $start                  = 0;
		private $total_locations_shown  = 0;
		private $wp_screen;

		/**
		 * SLP_Admin_Locations constructor.
		 *
		 * @param array $args
		 */
		function __construct( $args = array() ) {
			global $slplus_plugin;
			$this->slplus = $slplus_plugin;

			$this->set_CurrentAction();

			// If there are ANY extended data fields...
			//
			if ( $this->slplus->database->is_Extended() ) {
				add_filter( 'slp_manage_location_columns'  , array( $this, 'add_extended_data_to_active_columns'   ) );
			}

			$this->set_urls();

			$this->create_object_settings();

			$screen = $this->get_wp_screen();

			$this->screen_id = is_a( $screen , 'WP_Screen' ) ? $screen->id : apply_filters( 'slp_locations_screen_id' , '' );
			if ( ! empty( $this->screen_id ) ) {
				add_filter( 'manage_' . $this->screen_id . '_columns' , array( $this , 'manage_columns' ) );
			}


		}

		/**
		 * Return an instance of the object which is also registered to the slplus global less the SLP_ part.
		 *
		 * @param mixed     $options         object init params
		 *
		 * @return SLPlus_BaseClass_Object
		 */
		public static function get_instance( $options=array() ) {
			static $instance;
			if ( ! isset( $instance ) ) {
				$class = get_called_class();
				$instance = new $class( $options );
			}
			return $instance;
		}

		/**
		 * Adds the extended data columns.
		 *
		 * @param array $current_cols The current columns
		 *
		 * @return array
		 */
		public function add_extended_data_to_active_columns( $current_cols ) {
			$this->set_active_columns();
			$this->filter_active_columns();
			if ( empty( $this->active_columns ) ) {
				return $current_cols;
			}

			foreach ( $this->active_columns as $col ) {
				if ( $this->slplus->database->extension->get_option( $col->slug, 'display_type' ) === 'none' ) {
					continue;
				}
				$current_cols[ $col->slug ] = $col->label;
			}

			return $current_cols;
		}

		/**
		 * Add the private class to locations marked private.
		 *
		 * @param $class the current class string for the manage locations location entry.
		 *
		 * @return string the modified CSS class with private attached if warranted.
		 */
		public function add_private_location_css( $class ) {
			if ( $this->slplus->currentLocation->private ) {
				$class .= ' private ';
			}

			return $class;
		}

		/**
		 * Add screen options.
		 */
		public function add_screen_options() {
			add_screen_option( 'per_page', array( 'option' => 'locations_per_page' , 'label' => __( 'Locations Per Page' , 'store-locator-le' ) , 'default' => 50 ) );
		}

		/**
		 * Customize the data displayed to the user in the location list.
		 *
		 * @used-by create_string_manage_locations_table
		 * @trigger slp_column_data
		 *
		 * @param string $data
		 * @param string $slug
		 * @param string $label
		 *
		 * @return string
		 */
		public function customize_location_list_displayed_data( $data , $slug, $label ) {
			switch ( $slug ) {
				case 'sl_initial_distance':
					return sprintf( "%0.2f" , $data ) . ' '  . ( ( $this->slplus->SmartOptions->distance_unit->value === 'miles' ) ? __('miles','store-locator-le'):__('km','store-locator-le') );
				case 'sl_store':
					return $this->create_string_coordinate_link( $data );
			}


			// Custom Display Types
			$location_property = $this->slplus->currentLocation->get_property_name( $slug );
			switch ( $this->slplus->currentLocation->get_display_type( $location_property ) ) {
				case 'checkbox':
					return $this->slplus->is_CheckTrue( $data ) ? '<span class="dashicons dashicons-yes"></span>' : '';

				case 'image':
				case 'icon' :
					return $this->create_string_image_html( $data, $label );

				default:
					return empty( $data ) ? '' : $data;
			}

		}

		/**
		 * Create the location details box.
		 */
		private function create_location_details_box() {
			return <<<HTML
<div class="location-card hidden">
			<div class="primary">
				<header class="card-header">
					<h3 class="location-name" contenteditable="true" data-field="sl_store"></h3>
					<p class="location-address"><span class="one-liner" data-field="sl_address"></span>					
					<span data-field="sl_city"></span> 
					<span data-field="sl_state"></span>
					<span data-field="sl_zip"></span>
					<span data-field="sl_country" class="one-liner"></span>
					</p>				
				</header>
				<div class="card-body">
					<div class="location-information card-info-grid">					
					</div>				
				</div>
				<footer class="card-footer">
					<span data-field="sl_id" class="hidden"></span>
	                <div id='slp_form_buttons'>
	                <input type='button' class='button' value='{$this->slplus->Text->get_text_string( 'cancel' ) }' alt='{$this->slplus->Text->get_text_string( 'cancel' )}' title='{$this->slplus->Text->get_text_string( 'cancel' )}'  />
	                </div>					
					<span data-field="sl_linked_postid"></span>
				</footer>
			</div>			
			<div class="secondary">
				<div id="location_map" class='location_map'></div>
				<div class="location-coordinates">
					<span class="coordinates"><span contenteditable="true" data-field="sl_latitude"></span><span>, </span><span contenteditable="true" data-field="sl_longitude"></span></span>
					<a href="@" class="website" contenteditable="true" data-field="sl_url"></a>									
				</div>
			</div>
</div>
HTML;


		}

		/**
		 * Create and attach settings.
		 */
		private function create_object_settings() {
			if ( ! isset( $this->settings ) ) {
				$this->settings = new SLP_Settings( array(
					'name'              => SLPLUS_NAME . __( ' - Locations' , 'store-locator-le' ) ,
					'form_action'       => $this->baseAdminURL ,
					'form_name'         => 'locationForm' ,
					'form_enctype'      => 'multipart/form-data',
					'save_text'         => '' ,
					'show_help_sidebar' => false ,
				) );
			}
		}

		/**
		 * Create the display drop down for the top-of-table navigation.
		 *
		 */
		private function create_string_apply() {
			$button_label = __( 'Apply', 'store-locator-le' );
			return "<div id='do_action_apply' class='button action left_half'>{$button_label}</div>";
		}

		/**
		 * Create the display drop down for the top-of-table navigation.
		 *
		 */
		private function create_string_apply_to_all() {
			$button_label = __( 'To All', 'store-locator-le' );
			return "<div id='do_action_apply_to_all' class='button action right_half'>{$button_label}</div>";
		}

		/**
		 * Add the lat/long under the store name.
		 *
		 * @param string $field_value
		 *
		 * @return string
		 */
		private function create_string_coordinate_link( $field_value ) {
			$commaOrSpace = ( $this->slplus->currentLocation->latitude . $this->slplus->currentLocation->longitude !== '' ) ? ',' : ' ';

			if ( $commaOrSpace != ' ' ) {
				$the_url  =
					sprintf( 'https://%s?saddr=%f,%f',
						$this->slplus->options['map_domain'],
						$this->slplus->currentLocation->latitude,
						$this->slplus->currentLocation->longitude
					);
				$the_text =
					sprintf( '<a href="%s" target="csa_map">@ <span data-field="sl_latitude" data-value="%f">%f</span> %s <span data-field="sl_longitude" data-value="%f">%f</span></a></span>',
						$the_url,
						$this->slplus->currentLocation->latitude,
						$this->slplus->currentLocation->latitude,
						$commaOrSpace,
						$this->slplus->currentLocation->longitude,
						$this->slplus->currentLocation->longitude
					);
			} else {
				$the_text = __( 'Inactive. Geocode to activate.', 'store-locator-le' );
			}

			return
				sprintf( '<span class="store_name" data-field="sl_store" data-value="%s">%s</span>' .
				         '<span class="store_latlong">%s</span>',
					$field_value,
					$field_value,
					$the_text
				);
		}

		/**
		 * Create an HTML image.
		 *
		 * @param    string $image_source The image source.
		 * @param    string $title        The title for the image.
		 *
		 * @return  string
		 */
		private function create_string_image_html( $image_source, $title = '' ) {
			if ( empty( $image_source ) ) {
				return '';
			}
			if ( empty( $title ) ) {
				$title = __( 'image ', 'store-locator-le' );
			}

			$title = $this->slplus->currentLocation->store . ' ' . $title;

			$image_html =
				sprintf( '<img src="%s" alt="%s" title="%s" class="location_image manage_locations" />',
					$image_source,
					$title,
					$title
				);

			$link_html =
				sprintf(
					"<a href='%s' target='blank'>%s</a>",
					$image_source,
					$image_html
				);

			return $link_html;
		}

		/**
		 * Build the action buttons HTML string on the first column of the manage locations panel.
		 *
		 * @return string
		 */
		private function create_action_buttons_html() {
			$button_html = array();
			foreach ( $this->buttons as $action => $attributes ) {
				$button_html[] = sprintf(
					'<a class="dashicons %s slp-no-box-shadow" title="%s" href="#" data-action="%s" data-id="%s"></a>',
					$attributes[ 'class' ] , $attributes[ 'title' ] , $action , $this->slplus->currentLocation->id );
			}

			return join( $button_html );
		}

		/**
		 * Create the bulk actions drop down for the top-of-table navigation.
		 *
		 */
		public function createstring_BulkActionsBlock() {

			// Setup the properties array for our drop down.
			//
			$dropdownItems = array(
				array(
					'label'    => __( 'Bulk Actions', 'store-locator-le' ),
					'value'    => '-1',
					'selected' => true,
				),
				array(
					'label' => __( 'Delete Permanently', 'store-locator-le' ),
					'value' => 'delete',
				),
			);

			/**
			 * Filter to add a menu entry to the bulk actions drop down on the locations manager.
			 *
			 * @filter  slp_locations_manage_bulkactions
			 *
			 * @params  array   $dropdownitems
			 */
			$dropdownItems = apply_filters( 'slp_locations_manage_bulkactions', $dropdownItems );

			// Loop through the action boxes content array
			//
			$baExtras = '';
			foreach ( $dropdownItems as $item ) {
				if ( isset( $item['extras'] ) && ! empty( $item['extras'] ) ) {
					$baExtras .= $item['extras'];
				}
			}

			// Create the box div string.
			//
			$morebox             = "'#extra_'+jQuery('#actionType').val()";
			$filter_dialog_title = __( 'Options', 'store-locator-le' );
			$dialog_options      =
				"appendTo: '#locationForm'      , " .
				"minHeight: 50                  , " .
				"minWidth: 450                  , " .
				"title: jQuery('#actionType option:selected').text()+' $filter_dialog_title'  , " .
				"position: { my: 'left top', at: 'left bottom', of: '#actionType' } ";

			return
				'<div class="alignleft actions">' .
				$this->slplus->Helper->createstring_DropDownMenu(
					array(
						'id'          => 'actionType',
						'name'        => 'action',
						'items'       => $dropdownItems,
						'onchange'    => "jQuery({$morebox}).dialog({ $dialog_options });",
					)
				) .
				$this->create_string_apply() .
				$this->create_string_apply_to_all() .
				'</div>' .
				$baExtras
				;
		}

		/**
		 * Create the filters drop down for the top-of-table navigation.
		 *
		 */
		private function createstring_FiltersBlock() {
			$dropdownItems = array(
				array(
					'label'    => __( 'Show All', 'store-locator-le' ),
					'value'    => 'show_all',
					'selected' => true,
				),
			);

			/**
			 * Filter to add entries to the filters drop down menu on the locations manager.
			 *
			 * @filter slp_locations_manage_filters
			 *
			 * @params
			 */
			$dropdownItems = apply_filters( 'slp_locations_manage_filters', $dropdownItems );

			// Do not show if only "Show All" is an option.
			//
			if ( count( $dropdownItems ) <= 1 ) {
				return '';
			}

			// Loop through the action boxes content array
			//
			$baExtras = '';
			foreach ( $dropdownItems as $item ) {
				if ( isset( $item['extras'] ) && ! empty( $item['extras'] ) ) {
					$baExtras .= $item['extras'];
				}
			}

			// Create the box div string.
			//
			$morebox             = "'#extra_'+jQuery('#filterType').val()";
			$filter_dialog_title = __( 'Filter Locations By', 'store-locator-le' );
			$dialog_options      =
				"appendTo: '#locationForm'      , " .
				"minWidth: 550                  , " .
				"title: '$filter_dialog_title'  , " .
				"position: { my: 'left top', at: 'left bottom', of: '#filterType' } ";

			return
				$this->slplus->Helper->createstring_DropDownMenuWithButton(
					array(
						'id'          => 'filterType',
						'name'        => 'filter',
						'items'       => $dropdownItems,
						'onchange'    => "jQuery({$morebox}).dialog({ $dialog_options });",
						'class'       => 'dashicons',
						'buttonlabel' => '',
						'onclick'     => 'AdminUI.doAction(jQuery(\'#filterType\').val());',
					)
				) .
				$baExtras;
		}

		/**
		 * Create the manage locations pagination block
		 *
		 * @param int $totalLocations
		 * @param int $num_per_page
		 * @param int $start
		 * @param string $location_slug
		 *
		 * @return string
		 */
		private function createstring_PaginationBlock( $totalLocations = 0, $num_per_page = 10, $start = 0 , $location_slug ) {

			// Variable Init
			$pos          = 0;
			$prev         = min( max( 0, $start - $num_per_page ), $totalLocations );
			$next         = min( max( 0, $start + $num_per_page ), $totalLocations );
			$num_per_page = max( 1, $num_per_page );
			$qry          = isset( $_GET['q'] ) ? $_GET['q'] : '';
			$cleared      = preg_replace( '/q=$qry/', '', $this->hangoverURL );

			$extra_text = ( trim( $qry ) != '' ) ?
				__( "for your search of", 'store-locator-le' ) .
				" <strong>\"$qry\"</strong>&nbsp;|&nbsp;<a href='$cleared'>" .
				__( "Clear&nbsp;Results", 'store-locator-le' ) . "</a>" :
				"";

			// URL Regex Replace
			//
			if ( preg_match( '#&start=' . $start . '#', $this->hangoverURL ) ) {
				$prev_page = str_replace( "&start=$start", "&start=$prev", $this->hangoverURL );
				$next_page = str_replace( "&start=$start", "&start=$next", $this->hangoverURL );
			} else {
				$prev_page = $this->hangoverURL . "&start=$prev";
				$next_page = $this->hangoverURL . "&start=$next";
			}

			// Pages String
			//
			$pagesString                        = '';
			$this->script_data['all_displayed'] = ( $totalLocations <= $num_per_page );
			if ( ! $this->script_data['all_displayed'] ) {
				if ( ( ( $start / $num_per_page ) + 1 ) - 5 < 1 ) {
					$beginning_link = 1;
				} else {
					$beginning_link = ( ( $start / $num_per_page ) + 1 ) - 5;
				}
				if ( ( ( $start / $num_per_page ) + 1 ) + 5 > ( ( $totalLocations / $num_per_page ) + 1 ) ) {
					$end_link = ( ( $totalLocations / $num_per_page ) + 1 );
				} else {
					$end_link = ( ( $start / $num_per_page ) + 1 ) + 5;
				}
				$pos = ( $beginning_link - 1 ) * $num_per_page;
				for ( $k = $beginning_link; $k < $end_link; $k ++ ) {
					if ( preg_match( '#&start=' . $start . '#', $_SERVER['QUERY_STRING'] ) ) {
						$curr_page = str_replace( "&start=$start", "&start=$pos", $_SERVER['QUERY_STRING'] );
					} else {
						$curr_page = $_SERVER['QUERY_STRING'] . "&start=$pos";
					}
					if ( ( $start - ( $k - 1 ) * $num_per_page ) < 0 || ( $start - ( $k - 1 ) * $num_per_page ) >= $num_per_page ) {
						$pagesString .= "<a class='page-button' href=\"{$this->hangoverURL}&$curr_page\" >";
					} else {
						$pagesString .= "<a class='page-button thispage' href='#'>";
					}

					$pagesString .= "$k</a>";
					$pos = $pos + $num_per_page;
				}
			}

			$prevpages =
				"<a class='prev-page page-button" .
				( ( ( $start - $num_per_page ) >= 0 ) ? '' : ' disabled' ) .
				"' href='" .
				( ( ( $start - $num_per_page ) >= 0 ) ? $prev_page : '#' ) .
				"'>‹</a>";
			$nextpages =
				"<a class='next-page page-button" .
				( ( ( $start + $num_per_page ) < $totalLocations ) ? '' : ' disabled' ) .
				"' href='" .
				( ( ( $start + $num_per_page ) < $totalLocations ) ? $next_page : '#' ) .
				"'>›</a>";

			$pagesString =
				$prevpages .
				$pagesString .
				$nextpages;

			return
				"<div id='slp_pagination_pages_{$location_slug}' class='tablenav-pages'>" .
				'<span class="displaying-num">' .
				"<span id='total_locations_{$location_slug}'>{$totalLocations}</span>" .
				' ' . __( 'locations', 'store-locator-le' ) .
				'</span>' .
				'<span class="pagination-links">' .
				$pagesString .
				'</span>' .
				'</div>' .
				$extra_text;
		}

		/**
		 * Attach the HTML for the manage locations panel to the settings object as a new section.
		 *
		 * This will be rendered via the render_adminpage method via the standard wpCSL Settings object display method.
		 */
		public function create_settings_section_Manage() {
			$group_params = array( 'section_slug' => 'list' , 'group_slug' => 'location_table', 'header' => ''   );

			$this->settings->add_section( array(
				'section_slug' => $group_params['section_slug'],
				'div_id'      => 'current_locations',
				'auto'        => true,
				'innerdiv'    => true,
			) );

			$this->settings->add_group( $group_params );

			$this->settings->add_ItemToGroup( array( 'group_params' => $group_params , 'type' => 'custom' , 'wrapper' => false, 'id' => 'location_table_start' , 'custom' => $this->create_string_location_table_start() ) );


			// no locations - bail early
			if ( $this->total_locations_shown < 1 ) {
				if ( $this->slplus->database->had_where_clause ) {
					$this->settings->add_ItemToGroup( array( 'group_params' => $group_params , 'type' => 'custom' , 'wrapper' => false, 'id' => 'location_table_search_empty' , 'custom' => $this->slplus->Helper->create_string_wp_setting_error_box( __( "Location search or filter returned no matches.", 'store-locator-le' ) ) ) );
				}
				return;
			}

			$this->settings->add_ItemToGroup( array(
				'group_params' => $group_params,
				'type'         => 'custom',
				'wrapper'      => false,
				'id'           => 'location_notices',
				'custom'       => $this->slplus->notifications->get_html()
			) );

			$this->settings->add_ItemToGroup( array(
				'group_params' => $group_params,
				'type'         => 'manage_locations_table',
			) );

			$this->settings->add_ItemToGroup( array(
				'group_params' => $group_params,
				'type'         => 'custom',
				'wrapper'      => false,
				'id'           => 'location_table_end',
				'custom'       => $this->create_string_location_table_end()
			) );
		}

		/**
		 * Returns the string that is the Location Info Form guts.
		 */
		public function create_settings_section_Add() {
			SLP_Admin_Locations_Add::get_instance()->build_interface();
		}

		/**
		 * Import panel only if Power not active.
		 */
		public function create_settings_section_Import() {
			if ( $this->slplus->Location_Manager->get_location_count( true ) >= $this->slplus->Location_Manager->location_limit ) return;
			$section_params['name'] = __( 'Import', 'store-locator-le' );
			$section_params['slug'] = 'import';
			$this->settings->add_section( $section_params );

			$group_params['header'      ] = __( 'Upload A File', 'store-locator-le' );
			$group_params['group_slug'  ] = 'upload_a_file';
			$group_params['section_slug'] = $section_params['slug'];
			$group_params['div_group']    = 'left_side';
			$group_params['plugin'      ] = $this->slplus;
			$this->settings->add_group(  $group_params );

			$import_messages = array();
			$import_messages[] = $this->slplus->Text->get_web_link( 'import_provided_by', 'slp-power' );

			$this->settings->add_ItemToGroup(array(
				'group_params'  => $group_params,
				'type'          => 'subheader'  ,
				'label'         => ''           ,
				'description'   => join( $import_messages )
			));

		}

		/**
		 * Start location table.
		 */
		private function create_string_location_table_start() {
			$this->set_location_query();

			// No locations exist.
			//
			if ( ( $this->total_locations_shown < 1 ) && ! $this->slplus->database->had_where_clause ) {
				return
					wp_nonce_field( 'screen-options-nonce', 'screenoptionnonce', false ) .
					$this->slplus->Helper->create_string_wp_setting_error_box( __( "No locations have been created yet.", 'store-locator-le' ) );
			}

			$start_at     = $this->slplus->clean['start'];
			$hidden_start = "<input type='hidden' name='start' id='start' value='{$start_at}' />";

			// We have locations, show them.
			//
			return
				wp_nonce_field( 'screen-options-nonce', 'screenoptionnonce', false ) .
				$hidden_start .
				$this->createstring_PanelManageTableTopActions() .
				'<div class="manage_locations_table_outside">';
		}

		/**
		 * Close out location table.
		 *
		 * @return string
		 */
		private function create_string_location_table_end() {
			return
				'</div>' .
				'<div class="tablenav bottom">' .
				$this->createstring_PanelManageTablePagination( 'bottom' ) .
				'</div>';
		}

		/**
		 * Build the content of the manage locations table.
		 *
		 * @uses \SLP_Admin_Locations::add_private_location_css via SLP Filter slp_locations_manage_cssclass
		 * @uses \SLP_Admin_Locations::filter_InvalidHighlight  via SLP Filter slp_locations_manage_cssclass
		 */
		public function display_manage_locations_table() {
			?>
			<table id='manage_locations_table' class='slplus wp-list-table widefat posts display' cellspacing=0>
				<thead>
				<tr >
					<th id='top_of_checkbox_column'>
						<input type="checkbox" onclick=" jQuery('.slp_checkbox').prop( 'checked' , jQuery(this).prop('checked') ); " >
					</th>
					<?php $this->display_column_headers(); ?>
				</tr>
				</thead>

				<?php
				// Formatting
				//
				$colorClass = '';
				add_filter( 'slp_locations_manage_cssclass', array( $this, 'filter_InvalidHighlight' ), 10 );
				add_filter( 'slp_locations_manage_cssclass', array( $this, 'add_private_location_css' ), 15 );
				add_filter( 'slp_column_data' , array( $this, 'customize_location_list_displayed_data' ) , 10 , 3 );

				// Setup Data Query
				//
				$this->slplus->database->reset_clauses();
				$this->slplus->database->order_by_array = array();
				$sqlCommand                             = array(
					'selectall',
					'where_default',
					'orderby_default',
					'limit_one',
					'manual_offset',
				);

				// Start at the desired starting position in the list (for secondary pages)
				//
				$offset    = $this->start;
				$sqlParams = array( $offset );

				$max_to_show = min( $this->get_screen_option_per_page() , $this->slplus->Location_Manager->location_limit );

				// Setup action buttons
				//
				$this->setup_action_buttons();

				// Tell the WP Engine to get one record at a time
				// Until we reached how many we want per page.
				//
				$this->empty_columns = $this->columns;
				while (
					( ( $offset - $this->start ) < $max_to_show ) &&
					( $location = $this->slplus->database->get_Record( $sqlCommand, $sqlParams ) )
				) {
					$this->slplus->currentLocation->set_PropertiesViaArray( $location , 'load' );
					$location = array_map( "trim", $location );

					// Custom Filters to set the links on special data like URLs and Email
					//
					$location['sl_url'] = esc_url( $location['sl_url'] );
					$location['sl_url'] = empty( $location['sl_url'] ) ? '' :
						sprintf( '<a href="%s" target="blank" alt="%s" title="%s">%s</a>',
							$location['sl_url'],
							$location['sl_url'],
							$location['sl_url'],
							$this->slplus->Text->get_text( 'label_website' , null ,false)
						);

					$location['sl_email'] = empty( $location['sl_email'] ) ? '' :
						sprintf( '<a href="mailto:%s" target="blank" title="%s">%s</a>',
							$location['sl_email'],
							$location['sl_email'],
							$this->slplus->Text->get_text( 'label_email', null ,false )
						);

					$location['sl_description'] = empty( $location['sl_description'] ) ? '' :
						sprintf('<a onclick="alert(\'%s\');" href="#">%s</a>' ,
							esc_js( $location['sl_description'] ),
							__( 'View', 'store-locator-le' )
						);

					$cleanName = urlencode( $this->slplus->currentLocation->store );

					// Location Row Start
					//
					$location_string = __( 'Location # ', 'store-locator-le' ) . $this->slplus->currentLocation->id;

					// Row color
					//
					$colorClass = ( ( $colorClass === 'alternate' ) ? '' : 'alternate' );

					/**
					 * Filter to add manage locations css classes.
					 *
					 * @filter  slp_locations_manage_cssclass
					 *
					 * @params string
					 *
					 * @return string
					 */
					$extraCSSClasses = apply_filters( 'slp_locations_manage_cssclass', '' );
					?>

					<tr id='location-<?= $this->slplus->currentLocation->id ?>' name='<?= $cleanName ?>' class='slp_managelocations_row <?= $colorClass ?><?= $extraCSSClasses ?>' data-id='<?= $this->slplus->currentLocation->id ?>' data-type='base' >
						<td class='th_checkbox slp_th slp_checkbox'>
							<input type='hidden' name='linked_postid' data-field='linked_postid' data-value='<?= $this->slplus->currentLocation->linked_postid ?>' value='<?= $this->slplus->currentLocation->linked_postid ?>' />
							<input type='checkbox' class='slp_checkbox' name='sl_id[]' value='<?= $this->slplus->currentLocation->id ?>' alt='<?= $location_string ?>' title='<?= $location_string ?>'>
							<span class='location_id' data-field='sl_id'><?= $this->slplus->currentLocation->id ?></span>
						</td>
						<td class='actions'><div class='action_buttons'><?= $this->create_action_buttons_html() ?></div></td>

						<?php
						// Data Columns
						//
						list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();
						foreach ( $columns as $column_key => $column_display_name ) {
							if ( $column_key === 'sl_id' ) {
								continue;
							}

							$class   = array( 'slp_manage_locations_cell' );
							$clean_name = sanitize_title( $column_display_name );
							$class[] = $clean_name;
							$class[] = 'column-' . $column_key;
							if ( is_array( $hidden ) && in_array( $column_key, $hidden ) ) {
								$class[] = 'hidden';
							}

							if ( ! isset( $location[ $column_key ] ) ) {
								$location[ $column_key ] = '';
							}

							/**
							 * FILTER: slp_column_data
							 *
							 * Modify the data that is rendered for this column
							 *
							 * @filter  slp_column_data
							 *
							 * @params  string    column key without slashes
							 * @params  string    column key
							 * @params  string    column display name
							 *
							 * @return    string      the string to output
							 */
							$column_data = apply_filters( 'slp_column_data', stripslashes( $location[ $column_key ] ), $column_key, $column_display_name );

							if ( ! empty( $column_data ) ) {
								unset( $this->empty_columns[ $column_key ] );
							}

							$class      = "class='" . join( ' ', $class ) . "'";
							$data_field = ( $column_key !== 'sl_store' ) ? "data-field='{$column_key}'" : "data-field='sl_store_complex'";
							$data_colname = "data-colname='{$column_display_name}'";
							$value = ! empty( $this->slplus->currentLocation->locationData[$column_key] ) ? esc_attr( $this->slplus->currentLocation->locationData[$column_key] ) : '';
							$data_value = 'data-value="' . $value . '" ';
							?>
							<td <?= $class ?> <?= $data_field ?> <?= $data_colname ?> <?= $data_value ?>><?= $column_data ?></td>
							<?php
						}
						?>
					</tr>
					<?php
					$sqlParams = array( ++ $offset );
				}

				// Did we hit a limitation?
				//
				if ( ( $offset - $this->start ) == $this->slplus->Location_Manager->location_limit ) {
					$this->slplus->notifications->add_notice( 'information' , sprintf( __('Your manage locations display is limited to %s locations per your level of service.' ,'store-locator-le' ) , $this->slplus->Location_Manager->location_limit ) );
				}
				?>
			</table>
			<?php
		}

		/**
		 * Create the pagination string for the manage locations table.
		 *
		 * @param string $location_slug
		 *
		 * @return string
		 */
		private function createstring_PanelManageTablePagination( $location_slug ) {
			if ( $this->total_locations_shown > 0 ) {
				return $this->createstring_PaginationBlock(
					$this->total_locations_shown,
					$this->get_screen_option_per_page(),
					$this->start ,
					$location_slug
				);
			} else {
				return '';
			}
		}

		/**
		 * Build the HTML for the top-of-table navigation interface.
		 *
		 * @return string
		 */
		private function createstring_PanelManageTableTopActions() {
			$HTML =
				$this->createstring_BulkActionsBlock() .
				$this->createstring_FiltersBlock() .
				$this->createstring_SearchBlock() .
				$this->createstring_PanelManageTablePagination( 'top' );

			// Add profiler
			if ( $this->slplus->SmartOptions->enable_wp_debug->is_true ) {
			    $HTML .= '<input type="hidden" name="XDEBUG_PROFILE" value="1" >';
            }

			/**
			 * Filter to add stuff to the manage locations action bar.
			 *
			 * @filter  slp_manage_locations_actionbar_ui
			 *
			 * @params  string  HTML
			 */

			return
				'<div class="tablenav top">' .
				apply_filters( 'slp_manage_locations_actionbar_ui', $HTML ) .
				'</div>';
		}

		/**
		 * Create the display drop down for the top-of-table navigation.
		 *
		 */
		private function createstring_SearchBlock() {
			$currentSearch = ( ( isset( $_REQUEST['searchfor'] ) && ! empty( $_REQUEST['searchfor'] ) ) ? $_REQUEST['searchfor'] : '' );

			if ( ! empty( $currentSearch ) ) {
				$currentSearch =
					htmlentities(
						stripslashes_deep( $currentSearch ),
						ENT_QUOTES
					);
			}

			$placeholder = __( 'Search' , 'store-locator-le' );

			return
				"<input id='searchfor' value='{$currentSearch}' type='text' placeholder='{$placeholder} ...' name='searchfor' " .
				' onkeypress=\'if (event.keyCode == 13) { event.preventDefault();AdminUI.doAction("search"); } \' ' .
				' />' .
				"<input id='doaction_search' class='button action submit' type='submit' " .
				"value='' " .
				'onClick="AdminUI.doAction(\'search\');" ' .
				' />'
				;
		}

		/**
		 * Create the column headers string.
		 *
		 * @param boolean $with_id Show ID on column header. (default: true)
		 */
		private function display_column_headers( $with_id = true ) {
			$this->set_active_columns();
			$this->filter_active_columns();

			list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();
			$base_address = array( 'sl_store', 'sl_address', 'sl_address2', 'sl_city', 'sl_state', 'sl_zip', 'sl_country' );

			foreach ( $columns as $column_key => $column_display_name ) {
				$class = array( 'manage-column', "column-{$column_key}" );

				if ( in_array( $column_key, $hidden ) ) {
					$class[] = 'hidden';
				}
				if ( in_array( $column_key, $base_address ) ) {
					$class[] = 'base';
				}

				if ( ! $this->script_data['all_displayed'] && ( $this->db_orderbyfield === $column_display_name ) ) {
					$class[] = 'sorted ';
					$class[] = $this->sort_order;
				}

				// Sortable Header If Partial Location List
				//
				if ( ! $this->script_data['all_displayed'] ) {
					$newDir       = ( $this->sort_order === 'asc' ) ? 'desc' : 'asc';
					$cell_content =
						"<a href='{$this->cleanURL}&orderBy=$column_key&sortorder=$newDir' alt='{$column_display_name}' title='{$column_display_name}'>" .
						"<span>{$column_display_name}</span>" .
						"<span class='sorting-indicator'></span>" .
						"</a>";

					// All locations shown, use JavaScript UI manager DataTables.
					//
				} else {
					$cell_content = "<a href='#'>{$column_display_name}</a>";
				}

				$tag        = ( 'cb' === $column_key ) ? 'td' : 'th';
				$scope      = ( 'th' === $tag ) ? 'scope="col"' : '';
				?>
				<<?= $tag ?> id='<?= $column_key?>' <?= $scope ?> class="<?= join( ' ', $class ) ?>" data-field="<?= $column_key ?>" ><?= $cell_content ?></<?= $tag ?>>
				<?php
			}
		}

		/**
		 * Enqueue the dataTables JS.
		 *
		 * @see https://github.com/DataTables/DataTables
		 */
		private function enqueue_scripts() {
			wp_enqueue_style( 'dashicons' );
			wp_enqueue_style( 'vuetify', SLPLUS_PLUGINURL . '/css/vuetify.min.css' );

			if ( file_exists( SLPLUS_PLUGINDIR . 'js/jquery.dataTables.min.js' ) ) {
				wp_enqueue_script( 'jquery-ui-dialog'    );
				wp_enqueue_script( 'jquery-ui-draggable' );
				wp_enqueue_script( 'jquery-ui-droppable' );
				wp_enqueue_script( 'slp_datatables', SLPLUS_PLUGINURL . '/js/jquery.dataTables.min.js' );

				if ( file_exists( SLPLUS_PLUGINDIR . 'css/admin/jquery.dataTables.min.css' ) ) {
					wp_enqueue_style( 'slp_admin_locations_manager', SLPLUS_PLUGINURL . '/css/admin/jquery.dataTables.min.css' );
				}
			}

			wp_enqueue_script( 'vue', SLPLUS_PLUGINURL . '/js/vue.min.js' );
			wp_enqueue_script( 'vuetify', SLPLUS_PLUGINURL . '/js/vuetify.min.js' );


			$more_script_data = array(
                'default_marker'        => $this->slplus->SmartOptions->map_end_icon->value,
                'add_text'              => __( 'Add Location' , 'store-locator-le' ),
                'edit_text'             => __( 'Edit Location' , 'store-locator-le' ),
			    'site_import_url'       => '',
                'rest_url'              => site_url( 'wp-json/store-locator-plus/v2/' ),
                'rest_nonce'            => wp_create_nonce( 'wp_rest' ),
                'boolean'               => array(
	                'loading_locations' => false,
	                'site_import_protocol' => true,
                )
            );

			$this->script_data = array_merge( $more_script_data , $this->script_data );


			$more_text = array(
			        'form_title'        => $this->slplus->Text->get_text_string( 'add_location' ),
                    'latlng_placeholder'=> $this->slplus->Text->get_text_string( 'auto_geocode' ),
                    'sl_latitude'       => $this->slplus->Text->get_text_string( 'latitude'     ),
			        'sl_longitude'      => $this->slplus->Text->get_text_string( 'longitude'    ),
                    'triggers_geocode'  => $this->slplus->Text->get_text_string( 'triggers_geocode' ),

			        'save'              => $this->slplus->Text->get_text_string( 'save'         ),
			        'cancel'            => $this->slplus->Text->get_text_string( 'cancel'       ),
			        'load'              => $this->slplus->Text->get_text_string( 'load'         ),
			        'site_import_url'   => $this->slplus->Text->get_text_string( 'site_import_url'  ),
			        'site_import_url_hint'   => $this->slplus->Text->get_text_string( 'site_import_url_hint'  ),
            );
			$this->get_columns();
			$this->script_data[ 'text' ] = array_merge( $this->columns , $more_text );

			$this->script_data = apply_filters( 'slp_modify_admin_locations_script_data' , $this->script_data );

			wp_localize_script( 'slp_manage_locations', 'location_manager', $this->script_data );
		}

		/**
         * Add a notification for JavaScript.
         *
		 * @param string $message
		 */
		public function add_notice( $message ) {
		    $this->script_data[ 'notices' ][] = $message;
        }

		/**
		 * Set the invalid highlighting class.
		 *
		 * @param string $class
		 *
		 * @return string the new class name for invalid rows
		 */
		public function filter_InvalidHighlight( $class ) {

			if ( ( $this->slplus->currentLocation->latitude == '' ) ||
			     ( $this->slplus->currentLocation->longitude == '' )
			) {
				$class .= ' invalid ';
			}

			/**
			 * Filter to add classes to the manage locations class for invalid location entries.
			 *
			 * @filter      slp_invalid_highlight
			 *
			 * @params      string  $class  existing class names
			 */

			return apply_filters( 'slp_invalid_highlight', $class );
		}

		/**
		 * Filter the active columns.
		 */
		public function filter_active_columns() {
			/**
			 * FILTER: slp_edit_location_change_extended_data_info
			 *
			 * Filter to set the active columns in the locations tab.
			 *
			 * @filter  slp_edit_location_change_extended_data_info
			 *
			 * @params  array   $this->active_columns
			 *
			 * @return    array    modified SLPlus_Data_Extension active column array (record objects from wpdb)
			 */
			$this->active_columns = apply_filters( 'slp_edit_location_change_extended_data_info', $this->active_columns );

		}

		/**
		 * Set the columns we will render on the manage locations page.
		 *
		 * @uses \SLP_Admin_Locations::add_extended_data_to_active_columns via SLP Filter slp_manage_location_columns

		 */
		public function get_columns() {
			$this->script_data['user_id'] = get_current_user_id();

			// For all views
			//
			$this->columns = array(
				'sl_id'               => $this->slplus->Text->get_text_string( 'actions'     ),
				'sl_store'            => $this->slplus->Text->get_text_string( 'name'        ),
				'sl_address'          => $this->slplus->Text->get_text_string( 'address'     ),
				'sl_address2'         => $this->slplus->Text->get_text_string( 'address2'    ),
				'sl_city'             => $this->slplus->Text->get_text_string( 'city'        ),
				'sl_state'            => $this->slplus->Text->get_text_string( 'state'       ),
				'sl_zip'              => $this->slplus->Text->get_text_string( 'zip'         ),
				'sl_country'          => $this->slplus->Text->get_text_string( 'country'     ),
				'sl_initial_distance' => $this->slplus->Text->get_text_string( 'distance'    ),
				'sl_description'      => $this->slplus->Text->get_text_string( 'description' ),
				'sl_email'            => $this->slplus->Text->get_text( 'label_email'   , null ,false),
				'sl_url'              => $this->slplus->Text->get_text( 'label_website' , null ,false),
				'sl_hours'            => $this->slplus->Text->get_text( 'label_hours'   , null ,false),
				'sl_phone'            => $this->slplus->Text->get_text( 'label_phone'   , null ,false),
				'sl_fax'              => $this->slplus->Text->get_text( 'label_fax'     , null ,false),
				'sl_image'            => $this->slplus->Text->get_text( 'label_image'   , null ,false),
				'sl_private'          => $this->slplus->Text->get_text_string( 'private'     ),
			);

			/**
			 * Filter to add columns to expanded view on manage locations
			 *
			 * @filter     slp_manage_expanded_location_columns
			 *
			 * @params     array    $columns
			 *
			 * @deprecated use slp_manage_locations filter
			 */
			$this->columns = apply_filters( 'slp_manage_expanded_location_columns', $this->columns );

			/**
			 * Filter to add columns to expanded view on manage locations
			 *
			 * @filter slp_manage_location_columns
			 *
			 * @params array    $columns
			 */
			$this->columns = apply_filters( 'slp_manage_location_columns', $this->columns );

			return $this->columns;
		}

		/**
		 * Get a list of all, hidden and sortable columns, with filter applied
		 *
		 * @return array
		 */
		public function get_column_info() {
			if ( ! isset( $this->_column_headers ) ) {
				$hidden_columns = get_user_option( 'manage' . $this->screen_id . 'columnshidden' );
				if ( empty( $hidden_columns ) ) $hidden_columns = array();

				$this->_column_headers = array(
					$this->get_columns(),
					$hidden_columns ,
					array(),
					'sl_id',
				);
			}

			return $this->_column_headers;
		}

		/**
		 * Get the screen option per_page.
		 * @return int
		 */
		private function get_screen_option_per_page() {
			$this->get_wp_screen();
			$option = $this->wp_screen->get_option( 'per_page', 'option' );
			if ( ! $option ) {
				$option = str_replace( '-', '_', "{$this->screen_id}_per_page" );
			}

			$per_page = (int) get_user_option( $option );
			if ( empty( $per_page ) || $per_page < 1 ) {
				$per_page = $this->wp_screen->get_option( 'per_page', 'default' );
				if ( ! $per_page ) {
					$per_page = 20;
				}
			}
			return $per_page;
		}

		/**
		 * Get the wp_screen property.
		 */
		private function get_wp_screen() {
			if ( empty( $this->wp_screen ) ) {
				$this->wp_screen = get_current_screen();
			}
			return $this->wp_screen;
		}

		/**
		 * Set up our screen columns.
		 *
		 * Impacts screen options column list.
		 *
		 * @param   array   columns     the existing columns
		 * @return  array               key = field slug, value = title
		 */
		public function manage_columns( $columns ) {
			$this->get_column_info();
			return $this->_column_headers[0];
		}

		/**
		 * Output the manage locations admin page.
		 */
		public function display() {
			$this->slplus->set_php_timeout();
			require_once( SLPLUS_PLUGINDIR . 'include/module/location/SLP_Location_Manager.php' );

			// Action handler if we are processing an action.
			//
			if ( ! empty( $this->current_action ) ) {
			    $location_actions = SLP_Admin_Locations_Actions::get_instance();
				$location_actions->screen = $this;
				$location_actions->process_actions();
			}

			// CHANGE UPDATER
			//
			if ( isset( $_GET['changeUpdater'] ) && ( $_GET['changeUpdater'] == 1 ) ) {
				if ( get_option( 'sl_location_updater_type' ) == "Tagging" ) {
					update_option( 'sl_location_updater_type', 'Multiple Fields' );
					$updaterTypeText = "Multiple Fields";
				} else {
					update_option( 'sl_location_updater_type', 'Tagging' );
					$updaterTypeText = "Tagging";
				}
				$_SERVER['REQUEST_URI'] = preg_replace( '/&changeUpdater=1/', '', $_SERVER['REQUEST_URI'] );
				print "<script>location.replace('" . $_SERVER['REQUEST_URI'] . "');</script>";
			}

			// Create Location Panels
			//
			add_action( 'slp_build_locations_panels' , array( $this, 'create_settings_section_Manage'   ), 10 );

			add_action( 'slp_build_locations_panels' , array( $this, 'create_settings_section_Add'      ), 20 );

			SLP_Admin_Locations_Load::get_instance();

			// Setup Navigation Bar
			//
			$this->settings->add_section(
				array(
					'name'        => 'Navigation',
					'div_id'      => 'navbar_wrapper',
					'description' => $this->create_location_details_box() . SLP_Admin_UI::get_instance()->create_Navbar(),
					'innerdiv'    => false,
					'is_topmenu'  => true,
					'auto'        => false,
				)
			);

			/**
			 * HOOK: slp_build_locations_panels
			 *
			 * @action slp_build_locations_panels
			 *
			 * @param  SLP_Settings $settings
			 *
			 */
			do_action( 'slp_build_locations_panels', $this->settings );

			// No import section on locations tab?  Add one.
			//
			if ( ! $this->settings->has_section( 'import' ) ) {
				$this->create_settings_section_Import();
			}

			$this->enqueue_scripts();

			$this->settings->render_settings_page();
		}

		/**
		 * Set the current action being executed by the plugin.
		 */
		private function set_CurrentAction() {

			// Assume we are to take no action
			// unless we get the 'act' parameter from a page request AND can verify the nonce
			// then the action can be set to the sanitized value, key is ok since our valid actions are always lowercase "keylike" values
			//
			$this->current_action = '';

			if ( ! empty( $this->slplus->clean[ 'act' ] ) && ! empty( $_POST[ 'screenoptionnonce' ] ) && wp_verify_nonce( $_POST[ 'screenoptionnonce' ] ,'screen-options-nonce'  ) ) {
				$this->current_action = $this->slplus->clean['act'];
			}

			// Set the sort order.
			//
			if ( ! empty( $this->slplus->clean[ 'sortorder' ] ) ) {
				$this->sort_order = $this->slplus->clean[ 'sortorder' ];
			}

			// If we have an action go do the processing elsewhere
			//
			if ( ! empty( $this->current_action ) ) {
				return;
			}

			// If action is empty and POST mode and CONTENT LENGTH is blank could be an import file upload issue.
			//
			if ( empty( $this->current_action ) ) {
				if (
					!empty( $_SERVER['REQUEST_METHOD'] ) &&
					( $_SERVER['REQUEST_METHOD'] === 'POST' ) &&
					!empty( $_SERVER['CONTENT_LENGTH'] ) &&
					( empty( $_POST ) )
				) {
					$max_post_size  = ini_get( 'post_max_size' );
					$content_length = (int) $_SERVER['CONTENT_LENGTH'] / 1024 / 1024;
					if ( $content_length > $max_post_size ) {
						print "<div class='updated fade'>" .
						      sprintf(
							      __( 'It appears you tried to upload %d MiB of data but the PHP post_max_size is %d MiB.', 'store-locator-le' ),
							      $content_length,
							      $max_post_size
						      ) .
						      '<br/>' .
						      __( 'Try increasing the post_max_size setting in your php.ini file.', 'store-locator-le' ) .
						      '</div>';
					}
				}
			}
		}

		/**
		 * Add the location filter.
		 *
		 * @param $where
		 *
		 * @return string
		 */
		public function set_location_filter( $where ) {

			// Support the legacy where clause filters added by the
			// slp_manage_location_where filter
			//
			if ( ! empty( $this->extra_location_filters ) ) {
				$this->slplus->database->reset_clauses();
				$where = $this->slplus->database->extend_Where( '', $this->extra_location_filters );
			}

			// Add any filters from the search box.
			//
			$search_filter = $this->set_search_filter();
			if ( ! empty( $search_filter ) ) {
				$where = $this->slplus->database->extend_Where( $where, $search_filter );
			}

			return $where;
		}

		/**
		 * Set the locations table order by SQL command.
		 *
		 * @param $current_order_array
		 */
		public function set_location_order( $current_order_array ) {

			// Sort Direction
			//
			$this->db_orderbyfield =
				( ! empty( $this->slplus->clean[ 'orderBy' ] ) ) ?
					$this->slplus->clean[ 'orderBy' ] :
					'sl_store';

			$this->slplus->database->extend_order_array( "{$this->db_orderbyfield} {$this->sort_order}" );
		}

		/**
		 * Set all the properties that manage the location query.
		 *
		 * @uses \SLP_Admin_Locations::set_location_order   via SLP Filter  slp_orderby_default
		 * @uses \SLP_Admin_Locations::set_location_filter  via SLP Filter  slp_location_where
		 */
		private function set_location_query() {
			$this->slplus->database->reset_clauses();
			$this->slplus->database->order_by_array = array();

			/**
			 * Filter to filter out locations on the locations manager page.
			 *
			 * @filter slp_manage_location_where
			 *
			 * @params string  ''   current filters
			 */
			$this->extra_location_filters = apply_filters( 'slp_manage_location_where', '' );

			add_filter( 'slp_location_where', array( $this, 'set_location_filter' ) );
			add_action( 'slp_orderby_default', array( $this, 'set_location_order' ) );

			// Get the sort order and direction out of our URL
			//
			$this->cleanURL = preg_replace( '/&orderBy=\w*&sortorder=\w*/i', '', $_SERVER['REQUEST_URI'] );

			$dataQuery                   = $this->slplus->database->get_SQL( array( 'selectall', 'where_default' ) );
			$dataQuery                   = str_replace( '*', 'count(sl_id)', $dataQuery );
			$this->total_locations_shown = $this->slplus->db->get_var( $dataQuery );

			// Starting Location (Page)
			//
			// Search Filter, no actions, start from beginning
			//
			if ( isset( $_POST['searchfor'] ) && ! empty( $_POST['searchfor'] ) && ( $this->current_action === '' ) ) {
				$this->start = 0;

				// Set start to selected page..
				// Adjust start if past end of location count.
				//
			} else {
				$this->start = $this->slplus->clean[ 'start' ];
				if ( $this->start > ( $this->total_locations_shown - 1 ) ) {
					$this->start       = max( $this->total_locations_shown - 1, 0 );
					$this->hangoverURL = str_replace( '&start=', '&prevstart=', $this->hangoverURL );
				}
			}
		}

		/**
		 * Set the search filter (where clause) if the searchfor field comes in via the form post.
		 * @return string
		 */
		private function set_search_filter() {
			if ( isset( $_POST['searchfor'] ) ) {
				$clean_search_for = stripslashes_deep( trim( $_POST['searchfor'] ) );
				if ( ! empty ( $clean_search_for ) ) {
					$clean_search_for = '%%' . esc_sql( $this->slplus->db->esc_like( $clean_search_for ) ) . '%%';

					return
						sprintf(
							" CONCAT_WS(';',sl_store,sl_address,sl_address2,sl_city,sl_state,sl_zip,sl_country,sl_tags) LIKE '%s'",
							$clean_search_for
						);
				}
			}

			return '';
		}

		/**
		 * Save screen options.
		 *
		 * @param $status
		 * @param $option
		 * @param $value
		 * @return mixedf
		 */
		public function save_screen_options( $status, $option, $value) {
			$valid_options = array( 'locations_per_page' );
			if ( in_array( $option , $valid_options ) ) return $value;
			return $status;
		}

		/**
		 * Get the extended columns meta data and remember them within this class.
		 */
		public function set_active_columns() {
			if ( ! isset( $this->active_columns ) ) {
				$this->active_columns = $this->slplus->database->extension->get_active_cols();
			}
		}

		/**
		 * Set our URL properties.
		 */
		private function set_urls() {
			if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
				return;
			}

			$this->cleanAdminURL =
				isset( $_SERVER['QUERY_STRING'] ) ?
					str_replace( '?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI'] ) :
					$_SERVER['REQUEST_URI'];

			$queryParams = array();

			// Base Admin URL = must have params
			//
			if ( ! empty( $this->slplus->clean[ 'page' ] ) ) {
				$queryParams['page'] = $this->slplus->clean[ 'page' ];
			}
			$this->baseAdminURL = $this->cleanAdminURL . '?' . build_query( $queryParams );

			// Hangover URL = params we like to carry around sometimes
			//
			if ( $this->current_action === 'show_all' ) {
				$_REQUEST['searchfor'] = '';
			}
			if ( ! empty( $_REQUEST['searchfor'] ) ) {
				$queryParams['searchfor'] = $_REQUEST['searchfor'];
			}
			if ( $this->slplus->clean[ 'start' ] > 0 ) {
				$queryParams['start'] = $this->slplus->clean[ 'start' ];
			}
			if ( ! empty( $this->slplus->clean[ 'orderBy' ] ) ) {
				$queryParams['orderBy'] = $this->slplus->clean[ 'orderBy' ];
			}
			if ( ! empty( $this->slplus->clean[ 'sortorder' ] ) ) {
				$queryParams['sortorder'] = $this->sort_order;
			}

			$this->hangoverURL = $this->cleanAdminURL . '?' . build_query( $queryParams );
		}

		/**
		 * Setup the action buttons.
		 */
		private function setup_action_buttons() {

			// slug = the action, array is the meta data
			$this->buttons = array(
				'edit'  => array(
					'title' => __( 'Edit' , 'store-locator-le' ),
					'class' => 'dashicons-welcome-write-blog',
				),
				'delete' => array(
					'title' => __( 'Delete' , 'store-locator-le' ),
					'class' => 'dashicons-trash' ,
				),
			);

			/**
			 * Filter to Build the action buttons HTML string on the first column of the manage locations panel.
			 *
			 * @filter      slp_manage_locations_buttons
			 *
			 * @used-by     \SLP_Power_Pages_Admin::add_action_buttons
			 *
			 * @params      array   buttons
			 */
			$this->buttons = apply_filters( 'slp_manage_locations_buttons', $this->buttons );
		}

	}

	/**
	 * @var SLPlus $slplus
	 */
	global $slplus;
	if ( is_a( $slplus, 'SLPlus' ) ) {
		$slplus->add_object( SLP_Admin_Locations::get_instance() );
	}

	/**
	 * Graft the manage locations table output onto an SLP Setting
	 */
	class SLP_Settings_manage_locations_table extends SLP_Setting {

		/**
		 * Generate the output.  Overrides the typical display method.
		 */
		public function display() {
			$this->slplus->Admin_Locations->display_manage_locations_table();
		}
	}


}
