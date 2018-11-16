<?php
defined( 'ABSPATH' ) || exit;

require_once( SLPLUS_PLUGINDIR . 'include/base_class.addon.php' );

/**
 * Class SLPPower
 *
 * @property        SLP_Power_Admin            $admin
 * @property        string[]                   $category_attributes                     Custom attributes for a category.
 *                                  Keys (custom):
 *                                      category_url
 *                                      map-marker
 *                                      medium-icon
 *                                      rank
 *                                      url_target
 *
 * @property        SLP_Power_Category_Data    $category_data                           The data helper object.
 * @property-read   mixed[]                    $category_details                        The detailed category term data from the WP taxonomy with custom data as well.
 * @property        SLP_Power_Category_Manager $category_manager
 * @property        string                     $category_meta_option_base               The base name for the category meta entry in the options table csl-slplus-TAGALONG-category_<term_id>
 * @property-read   int                        $categories_loaded_for                   Which location ID is the current_location_categories loaded with?
 * @property        SLP_Power_Cron             $cron
 * @property        SLP_Power_Locations_Export         $csvExporter
 * @property        SLP_Power_Locations_Import $csvImporter                             The CSV Location Importer
 * @property        int[]                      $current_location_categories             The array of current location category IDs.
 * @property        SLPPower                   $instance
 * @property        mixed                      $location_fields                         Location fields.
 *                                                              long field name => shorthand
 *                                                              This is used in the AJAX and the UI objects so we share them up here in the add on base class.
 * @property        array                      $options                                 Settable options for this plugin.
 * @property        SLP_Message_Manager[]       $messages                                The links to the SLP messages lists.
 * @property        SLP_Power_Pages             $pages
 * @property        SLP_Power_Category_Stores_Taxonomy   $stores_taxonomy
 * @property        SLP_Power_UI                $userinterface
 * @property        boolean                    $using_pages                             Using SLP Pages
 *
 */
class SLPPower extends SLP_BaseClass_Addon {
	protected $class_prefix = 'SLP_Power_';

	public        $options                     = array(
		'city'                         => '' ,      // Group By Fields by= shortcode attribute
		'city_selector'                => '' ,      // Group By Fields by= shortcode attribute
		'country'                      => '' ,      // Group By Fields by= shortcode attribute
		'country_selector'             => '' ,      // Group By Fields by= shortcode attribute
		'cron_import_timestamp'        => '' ,
		'cron_import_recurrence'       => 'none' ,
		'csv_clear_messages_on_import' => '1' ,
		'csv_file_url'                 => '' ,
		'csv_skip_geocoding'           => '0' ,
		'csv_duplicates_handling'      => 'update' ,
		'default_comments'             => '0' ,
		'default_page_status'          => 'draft' ,
		'default_trackbacks'           => '0' ,
		'load_data'                    => '0' ,
		'installed_version'            => '' ,
		'pages_read_more_text'         => '' ,
		'pages_replace_websites'       => '1' ,
		'page_template'                => '' ,
		'permalink_starts_with'        => 'store-page' ,
		'permalink_flush_needed'       => '0' ,
		'prevent_new_window'           => '1' ,
		'prepend_permalink_blog'       => '1' ,
		'state'                        => '' ,      // Group By Fields by= shortcode attribute
		'state_selector'               => '' ,      // Group By Fields by= shortcode attribute
		'tag_autosubmit'               => '0' ,
		'tag_dropdown_first_entry'     => '' ,
		'tag_label'                    => '' ,
		'tag_selector'                 => 'none' ,
		'tag_selections'               => '' ,
		'tag_show_any'                 => '1' ,
		'tag_output_processing'        => 'as_entered' ,
		'territory'                    => '' ,      // Group By Fields by= shortcode attribute
		'territory_selector'           => '' ,      // Group By Fields by= shortcode attribute
	);
	public        $admin;
	public        $category_attributes         = array(
		'category_url' => '' ,
		'map-marker'   => '' ,
		'medium-icon'  => '' ,
		'rank'         => '' ,
		'url_target'   => '' ,
	);
	public        $category_data;
	public        $category_manager;
	private       $category_details            = array();
	public        $category_meta_option_base;
	private       $categories_loaded_for       = null;
	public        $cron;
	public        $csvExporter;
	public        $csvImporter;
	public        $current_location_categories = array();
	public static $instance;
	public        $location_fields             = array(
		'sl_city'    => 'city' ,
		'sl_country' => 'country' ,
		'sl_state'   => 'state' ,
		'territory'  => 'territory' ,
	);
	public        $messages;
	public        $pages;
	public        $stores_taxonomy;
	public        $using_pages;

	/**
	 * Initialize a singleton of this object.
	 *
	 * @return SLPPower
	 */
	public static function init() {
		static $instance = false;
		if ( ! $instance ) {
			load_plugin_textdomain( 'slp-power' , false , SLPPOWER_REL_DIR . '/languages/' );
			$instance = new SLPPower( array(
				                          'version'         => SLP_POWER_VERSION ,
				                          'min_slp_version' => SLP_POWER_MIN_SLP ,

				                          'name'        => __( 'Power' , 'slp-power' ) ,
				                          'option_name' => 'slp-power' ,
				                          'file'        => SLP_POWER_FILE ,

				                          'activation_class_name'    => 'SLPPower_Activation' ,
				                          'admin_class_name'         => 'SLP_Power_Admin' ,
				                          'ajax_class_name'          => 'SLP_Power_AJAX' ,
				                          'userinterface_class_name' => 'SLP_Power_UI',
			                          ) );
		}

		return $instance;
	}

	/**
	 * Allows WordPress to process csv file types
	 *
	 * @used-by \get_allowed_mime_types
	 * @set-via \SLPPower::add_hooks_and_filters
	 *
	 * @param array $existing_mimes
	 *
	 * @return array
	 */
	public function add_csv_mime_type( $existing_mimes = array() ) {
		$existing_mimes[ 'csv' ] = 'text/csv';
		return $existing_mimes;
	}

	/**
	 * Delete current location categories from Tagalong categories table.
	 */
	function action_DeleteLocationCategories() {
		if ( $this->slplus->currentLocation->all ) {
			$this->category_data->db->query( $this->category_data->get_SQL( 'delete_entire_category_map' ) );
			remove_action( 'slp_deletelocation_starting' , array( $this , 'action_DeleteLocationCategories' ) );
		} else {
			$this->category_data->db->query( $this->category_data->db->prepare( $this->category_data->get_SQL( 'delete_category_by_id' ), $this->slplus->currentLocation->id ) );
		}
	}

	/**
	 * Add cross-element hooks & filters.
	 *
	 * Haven't yet moved all items to the AJAX and UI classes.
	 */
	function add_hooks_and_filters() {
		$this->create_object_category_data();
		$this->stores_taxonomy = SLP_Power_Category_Stores_Taxonomy::get_instance();

		// Extend Data
		add_filter( 'slp_extend_get_SQL' , array( $this , 'add_sql_commands' ) );

		// Add Icons
		add_filter( 'slp_icon_directories' , array( $this , 'add_icon_directory' ) , 10 );

		add_filter( 'wp_title' , array( $this , 'modify_page_title' ) , 20 , 3 );

		// REST + Admin
        if ( defined( 'SLP_REST_SLUG' ) ) {
            add_action( 'slp_setup_rest_endpoints' , array( $this , 'add_REST_endpoints' ) );
        }

        // REST or Admin Action
		if ( defined( 'SLP_REST_SLUG' ) || ! empty( $_REQUEST[ 'action' ] ) ) {
			add_action( 'slp_location_added', array( SLP_Power_Admin::get_instance() , 'assign_location_categories' ) );
		}

        if ( ! empty( $_REQUEST[ 'action' ] ) && ( $_REQUEST[ 'action' ] === 'delete' ) ) {
			add_action( 'slp_deletelocation_starting', array( $this, 'action_DeleteLocationCategories' ) );
		}

        // AJAX
		add_filter( 'upload_mimes', array( $this, 'add_csv_mime_type') );
	}

	/**
	 * Add our icon directory to the list used by SLP.
	 *
	 * @param mixed[] $directories - array of directories.
	 *
	 * @return mixed[]
	 */
	function add_icon_directory( $directories ) {
		$directories = array_merge( $directories , array(
			                                         array(
				                                         'dir' => plugin_dir_path( SLP_POWER_FILE ) . 'images/icons/' ,
				                                         'url' => plugins_url( '' , SLP_POWER_FILE ) . '/images/icons/' ,
			                                         ) ,
		                                         ) );

		return $directories;
	}

    /**
     * Add some new REST endpoints.
     */
    public function add_REST_endpoints() {
        SLP_Power_REST_Handler::get_instance();
    }

	/**
	 * Return an SQL command component based on the command key provided.
	 *
	 * @param string $command
	 *
	 * @return string
	 */
	public function add_sql_commands( $command ) {
		if ( $command == 'wherelinkedpostid' ) {
			return ' WHERE sl_linked_postid=%d ';
		}
		$sql_statement = $this->category_data->get_SQL( $command );

		return $sql_statement;
	}

	/**
	 * Convert an array to CSV.
	 *
	 * @param array[] $data
	 *
	 * @return string
	 */
	static function array_to_CSV( $data ) {
		$outstream = fopen( "php://temp" , 'r+' );
		fputcsv( $outstream , $data , ',' , '"' );
		rewind( $outstream );
		$csv = fgets( $outstream );
		fclose( $outstream );

		return $csv;
	}

	/**
	 * Things we do at the start.
	 */
	protected function at_startup() {
		$this->pages = SLP_Power_Pages::get_instance();
	}


	/**
	 * Create and attach the \CSVExportLocations object
	 */
	function create_CSVLocationExporter() {
		if ( ! isset( $this->csvExporter ) ) {
			$this->csvExporter = SLP_Power_Locations_Export::get_instance();
		}
	}

	/**
	 * Create and attach the \CSVImportLocations object
	 *
	 * @param  array $params
	 */
	function create_CSVLocationImporter( $params = array() ) {
		require( SLPLUS_PLUGINDIR . 'include/module/location/SLP_Location_Manager.php' );
		if ( $this->slplus->Location_Manager->has_max_locations() ) {
			return;
		}
		if ( ! isset( $this->csvImporter ) ) {
			$this->csvImporter = SLP_Power_Locations_Import::get_instance();
			if ( ! empty( $params ) ) {
				$this->csvImporter->set_properties( $params );
			}
		}
	}

	/**
	 * Create a category icon array.
	 *
	 * **$params values**
	 * - **show_label** if true put text under the icons (default: false)
	 * - **add_edit_link** if true wrap the output in a link to the category edit page (default: false)
	 *
	 * **Example**
	 * /---code php
	 * $this->create_LocationIcons($category_list, array('show_label'=>false, 'add_edit_link'=>false));
	 * \---
	 *
	 * @param mixed[] $categories array of category details
	 * @param mixed[] $params     the parameters
	 *
	 * @return string html of the icon array
	 */
	function create_LocationIcons( $categories , $params = array() ) {

		// Make sure all params have defaults
		//
		$params = array_merge( array(
			                       'show_label'    => false ,
			                       'add_edit_link' => false ,
		                       ) , $params );

		// Now build the image tags for each category
		//
		$locationIcons = '';
		ksort( $categories );
		foreach ( $categories as $category ) {
			$locationIcons .= $this->createstring_CategoryIconHTML( $category , $params );
		}

		return $locationIcons;
	}

	/**
	 * Setup the category data object.
	 */
	public function create_object_category_data() {
		require_once( SLPPOWER_REL_DIR . 'include/module/category/SLP_Power_Category_Data.php' );
		$this->category_data = $this->slplus->Power_Category_Data;
	}

	/**
	 * Attach a message stack to this import object.
	 */
	public function create_object_schedule_messages() {
		if ( ! isset( $this->messages[ 'schedule' ] ) ) {
			$this->messages[ 'schedule' ] = SLP_Message_Manager::get_instance( array( 'slug' => 'schedule' ) );
		}
	}

	/**
	 * Attach a message stack to this import object.
	 */
	public function create_object_import_messages() {
		if ( ! isset( $this->messages[ 'import' ] ) ) {
			$this->messages[ 'import' ] = SLP_Message_Manager::get_instance( array( 'slug' => 'import' ) );
		}
	}

	/**
	 * Create a link to the category editor if warranted.
	 *
	 * @param int    $category_id the category ID
	 * @param string $html        the HTML output to be wrapped
	 *
	 * @return string the HTML wrapped in a link to the category editor.
	 */
	function createstring_CategoryEditLink( $category_id , $html ) {
		return sprintf( "<a href='%s' class='category_edit_link' title='edit category' alt='edit category' data-value='%s'>%s</a>" , get_edit_term_link( $category_id , SLPlus::locationTaxonomy ) , $category_id , $html );
	}

	/**
	 * Create the category HTML output for admin and user interface with images and text.
	 *
	 * **$params values**
	 * - **show_label** if true put text under the icons (default: false)
	 * - **add_edit_link** if true wrap the output in a link to the category edit page (default: false)
	 *
	 * **Example**
	 * /---code php
	 * $this->createstring_CategoryIconHTML($category, array('show_label'=>false, 'add_edit_link'=>false));
	 * \---
	 *
	 * @param mixed[] $category a taxonomy array
	 * @param mixed[] $params   the parameters we accept
	 *
	 * @return string HTML for the category output on UI and admin panels
	 */
	function createstring_CategoryIconHTML( $category , $params ) {
		$HTML = $this->createstring_CategoryImageHTML( $category );
		if ( $params[ 'show_label' ] ) {
			$HTML .= $this->createstring_CategoryLegendText( $category );
		}
		if ( $params[ 'add_edit_link' ] ) {
			$HTML = $this->createstring_CategoryEditLink( $category[ 'term_id' ] , $HTML );
		}
		return $HTML;
	}

	/**
	 * Create the image string HTML
	 *
	 * @param mixed[] $category   a taxonomy array
	 * @param string  $field_name which category field to get the image from
	 *
	 * @return string HTML for presenting an image
	 */
	function createstring_CategoryImageHTML( $category , $field_name = 'medium-icon' ) {

		if ( empty( $category[ $field_name ] ) ) {
			return '';
		}
		$category[ 'name' ] = isset( $category[ 'name' ] ) ? $category[ 'name' ] : '';
		$category[ 'name' ] = isset( $category[ 'slug' ] ) ? $category[ 'slug' ] : '';

		$image_HTML = sprintf( '<img src="%s" alt="%s" width="32" height="32">' , $category[ $field_name ] , $category[ 'name' ] , $category[ 'name' ] );

		// Wrap the icon in an anchor tag (link) if category_url is specified.
		//
		if ( ( $field_name === 'medium-icon' ) && ! empty( $category[ 'category_url' ] ) ) {
			$image_HTML = sprintf( '<a href="%s" target="%s" title="%s" alt="%s" class="slp_tagalong_icon">%s</a>' , $category[ 'category_url' ] , $category[ 'url_target' ] , $category[ 'slug' ] , $category[ 'slug' ] , $image_HTML );

		}

		return $image_HTML;
	}

	/**
	 * Create the category title span HTML
	 *
	 * @param mixed[] $category a taxonomy array
	 *
	 * @return string HTML for putting category title in a span
	 */
	function createstring_CategoryLegendText( $category ) {
		return sprintf( '<span class="legend_text">%s</span>' , $category[ 'name' ] );
	}

	/**
	 * Create the icon array for a given location.
	 *
	 * $params array values:
	 *  'show_label' = if true show the labels under the icon strings
	 *
	 * @param mixed[] $params named array of settings
	 *
	 * @return string
	 */
	public function create_string_icon_array( $params = array() ) {
		$params = array_merge( array(
			                       'show_label' => false ,
		                       ) , $params );

		// Setup the location categories from the helper table
		//
		$this->set_LocationCategories();

		// If there are categories assigned to this location...
		//
		if ( count( $this->current_location_categories ) > 0 ) {
			$assigned_categories = array();
			foreach ( $this->current_location_categories as $category_id ) {
				$category_details                                   = $this->get_TermWithTagalongData( $category_id );
				$assigned_categories[ $category_details[ 'slug' ] ] = $category_details;
			}

			$icon_string = $this->create_LocationIcons( $assigned_categories , $params );

			// Make the icon string blank if there are no categories.
			//
		} else {
			$icon_string = '';
		}

		// Return the icon string
		//
		return $icon_string;

	}

	/**
	 * Set the admin menu items.
	 *
	 * @param mixed[] $menuItems
	 *
	 * @return mixed[]
	 */
	public function filter_AddMenuItems( $menuItems ) {
		$this->createobject_Admin();
		$this->admin_menu_entries   = array();
		$this->admin_menu_entries[] = array(
			'label'    => __( 'Reports' , 'slp-power' ) ,
			'slug'     => 'slp_reports' ,
			'class'    => $this->admin->reports_tab ,
			'function' => 'render' ,
		);
		$this->admin_menu_entries[] = array(
			'label'    => __( 'Categories' , 'slp-power' ) ,
			'slug'     => 'slp-categories' ,
			'url'      => sprintf( 'edit-tags.php?taxonomy=%s&post_type=%s', SLPlus::locationTaxonomy , SLPlus::locationPostType ) ,
		);

		return parent::filter_AddMenuItems( $menuItems );
	}

	/**
	 * Get all the crons for the specified hook.
	 *
	 * TODO: put in a new SLP_Cron class.
	 *
	 * @param $hook
	 *
	 * @return array
	 */
	public function get_all_crons_for_hook( $hook ) {
		$matches = array();

		$crons = _get_cron_array();
		foreach ( $crons as $timestamp => $cron ) {
			if ( isset( $cron[$hook] ) ) {
				foreach ( $cron[ $hook ] as $key => $meta ) {
					$matches[] = $meta;
				}
			}
		}

		return $matches;
	}

	/**
	 * Add extended tagalong data to the category array.
	 *
	 * @param int $term_id the category term id
	 *
	 * @return mixed[]|false named array of category attributes
	 */
	function get_TermWithTagalongData( $term_id ) {
		if ( ! isset( $this->category_details[ $term_id ] ) ) {

			// Get the WordPress base taxonomy info for this category ID
			//
			$category_array = get_term_by( 'id' , $term_id , SLPlus::locationTaxonomy , ARRAY_A );

			// Term ID does not exist...
			//
			if ( ! is_array( $category_array ) ) {
				$this->process_bad_category_id( $term_id );

				return false;
			}

			// Get Tagalong Custom Meta Info for this category ID
			//
			$category_options = $this->slplus->WPOption_Manager->get_wp_option( $this->category_meta_option_base . $term_id , array() );

			// Build the complete custom taxonomy category structure.
			//
			// array_merge : later entries take precedence
			//
			$this->category_details[ $term_id ] = array_merge( $this->category_attributes , $category_options , $category_array );
		}

		return $this->category_details[ $term_id ];
	}

	/**
	 * Initialize the options properties from the WordPress database.
	 */
	function init_options() {

		// Set the defaults for first-run
		// Especially useful for gettext stuff you cannot put in the property definitions.
		//
		$this->option_defaults = $this->options;
		//$this->option_defaults['first_entry_for_city_selector'      ] = __( 'All Cities...'             , 'slp-power' );

		parent::init_options();

		$this->category_meta_option_base         = SLPLUS_PREFIX . '-TAGALONG-category_';
		$this->category_attributes[ 'taxonomy' ] = SLPlus::locationTaxonomy;

		$this->create_cron_object();

		$this->init_using_pages();
	}

	/**
	 * Set using pages and look for incoming change on general tab.
	 * We need to catch this super early in the stack before action processing.
	 */
	private function init_using_pages() {
		$this->using_pages = property_exists( $this->slplus->SmartOptions , 'use_pages' ) && $this->slplus->SmartOptions->use_pages->is_true;

		if ( ! isset( $_REQUEST[ 'action' ] ) || ( $_REQUEST[ 'action' ] !== 'update' ) ) {
			return;
		}
		if ( ! isset( $_REQUEST[ 'page' ] ) || ( $_REQUEST[ 'page' ] !== 'slp_general' ) ) {
			return;
		}
		$this->slplus->SmartOptions->use_pages->value = isset( $_REQUEST[ 'options_nojs' ][ 'use_pages' ] );
		$this->using_pages                            = $this->slplus->SmartOptions->use_pages->is_true;
	}

	/**
	 * Modify the HTML <title> tag page title of the current page.
	 *
	 * @param string $title
	 * @param string $sep
	 * @param int    $sep_pos
	 *
	 * @return mixed
	 */
	public function modify_page_title( $title , $sep , $sep_pos ) {
		return SLP_Power_UI::get_instance()->modify_HTMLPageTitle( $title , $sep , $sep_pos );
	}

	/**
	 * Process a bad category ID.
	 *
	 * @param $id
	 */
	private function process_bad_category_id( $id ) {
		$this->stores_taxonomy->delete_category_from_locations( $id , null , null );
	}

	/**
	 * Create the cron object.
	 */
	public function create_cron_object() {

		if ( ! defined( 'DOING_CRON' ) || ! DOING_CRON ) {
			return;
		}
		if ( isset( $this->cron ) ) {
			return;
		}
		$this->cron = SLP_Power_Cron::get_instance();
		$this->cron->make_bot_babies();
	}

	/**
	 * Recode all the uncoded locations.
	 *
	 * @param    SLP_Message_Manager $messages
	 * @param   int $max_id set if called from geocode cron
	 */
	public function recode_all_uncoded_locations( $messages = null , $max_id = null ) {
		$this->slplus->notifications->delete_all_notices();
		$logging_enabled = ! is_null( $messages ) && defined( 'DOING_CRON' ) && $this->slplus->SmartOptions->log_schedule_messages->is_true;
		if ( $logging_enabled ) {
			$this->slplus->notifications->enable_logging();
		}

		add_filter( 'slp_location_where' , array( $this , 'set_where_not_valid_lat_long' ) );

		$offset = 0;
		do {
			$location = $this->slplus->database->get_Record( array( 'selectslid' , 'where_default' ) , array() , $offset );
			if ( ! empty ( $location[ 'sl_id' ] ) ) {
				$this->slplus->currentLocation->set_PropertiesViaDB( $location[ 'sl_id' ] );
				$this->slplus->currentLocation->do_geocoding();
				if ( $this->slplus->currentLocation->dataChanged ) {
					$this->slplus->currentLocation->MakePersistent();

					if ( ! is_null( $max_id ) ) {
						$this->slplus->SmartOptions->set( 'last_geocoded_location' ,  $this->slplus->currentLocation->id );
						$this->slplus->WPOption_Manager->update_wp_option( 'nojs' );
					}

				} else {
					$offset ++;
				}

				if ( $logging_enabled ) {
					$notifications = $this->slplus->notifications->get();
					foreach ( $notifications as $notice ) {
						$messages->add_message( $notice->content );
					}
					$this->slplus->notifications->delete_all_notices();
				}

			} else {
				if ( $logging_enabled ) {
					$messages->add_message( __( 'Geocoding location ID is empty.' , 'slp-power' ) );
				}

				if ( ! is_null( $max_id ) ) {
					$this->slplus->SmartOptions->set( 'last_geocoded_location' ,  $max_id );
					$this->slplus->WPOption_Manager->update_wp_option( 'nojs' );
				}

				$offset ++;
			}
		} while ( ! empty ( $location[ 'sl_id' ] ) );

		remove_filter( 'slp_location_where' , array( $this , 'set_where_not_valid_lat_long' ) );
		if ( $logging_enabled ) {
			$this->slplus->notifications->disable_logging();
		}
	}

	/**
	 * Fill the current_location_categories array with the category IDs assigned to the current location.
	 *
	 * Assumes slplus->currentLocation is loaded with the current location data.
	 */
	function set_LocationCategories() {
		if ( $this->categories_loaded_for == $this->slplus->currentLocation->id ) {
			return;
		}

		// Reset the current location categories
		//
		$this->current_location_categories = array();

		// Get the first record from tagalong helper table
		//
		$location_category = $this->slplus->database->get_Record( 'select_categories_for_location' , $this->slplus->currentLocation->id , 0 );

		// First record exists,
		// push category ID onto current_location_categories
		// and loop through other category records,
		// appending to array
		//
		if ( $location_category !== null ) {
			$this->current_location_categories[] = $location_category[ 'term_id' ];
			$offset                              = 1;
			while ( ( $location_category = $this->slplus->database->get_Record( 'select_categories_for_location' , $this->slplus->currentLocation->id , $offset ++ ) ) !== null ) {
				$this->current_location_categories[] = $location_category[ 'term_id' ];
			}
		}

		$this->categories_loaded_for = $this->slplus->currentLocation->id;
	}

	/**
	 * Set the NOT where valid lat long clause.
	 *
	 * @param string $where
	 *
	 * @return string
	 */
	function set_where_not_valid_lat_long( $where ) {
		$where_valid_lat_long = $this->slplus->database->filter_SetWhereValidLatLong( '' );

		return 'NOT ( ' . $where_valid_lat_long . ' ) OR sl_latitude IS NULL OR sl_longitude IS NULL';
	}
}
