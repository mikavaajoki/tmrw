<?php
defined( 'ABSPATH' ) || exit;

require_once( SLPLUS_PLUGINDIR . 'include/module/admin_tabs/SLP_BaseClass_Admin.php');

/**
 * Holds the admin-only code.
 *
 * This allows the main plugin to only include this file in admin mode
 * via the admin_menu call.   Reduces the front-end footprint.
 *
 * @property        SLPPower                          $addon
 * @property        SLP_Power_Admin_Location_Filters  $admin_location_filters     Admin Location Filters UI Object
 * @property-read   SLPPower_Admin_EditTags           $edit_tags                  The WP tag editor object.
 * @property-read   SLPPower_Admin_ExperienceSettings $experience
 * @property        SLP_Power_Pages_Admin             $pages                      The pages admin class.
 * @property        SLP_Power_Admin_Reports            $reports_tab                The reports tab.
 * @property        SLP_Settings                      $settings                   The tag list settings object.
 */
class SLP_Power_Admin extends SLP_BaseClass_Admin {
	public  $addon;
	public  $admin_location_filters;
	private $data;
	private $edit_tags;
	private $experience;
	public  $js_requirements = array( 'jquery' );
	public  $pages;
	public  $reports_tab;
	public  $settings;
	public  $settings_interface;
	public  $settings_pages  = array(
		'slp_experience'       => array(
			'tag_autosubmit',
			'tag_show_any',
		),
		'slp_manage_locations' => array(
			'csv_skip_geocoding',
			'csv_clear_messages_on_import',
			'load_data',
		),
		'slp-pages'            => array(
			'default_comments',
			'default_trackbacks',
			'pages_replace_websites',
			'prevent_new_window',
			'prepend_permalink_blog',
		),
	);

	/**
	 * Add our SLP hooks and Filters for Admin Mode
	 *
	 * @uses \SLP_Power_Admin::add_slp_settings_to_wp_edit_category for filter stores_edit_form
	 * @uses \SLP_Power_Admin::add_slp_settings_to_wp_add_category  for filter stores_add_form_fields
	 * @uses \SLP_Power_Admin::add_managed_pages
	 */
	public function add_hooks_and_filters() {
		$this->js_pages = array( $this->slplus->admin_page_prefix . 'slp_manage_locations' );
		parent::add_hooks_and_filters();

		// Load objects based on which admin page we are on.
		//
		if ( isset( $_REQUEST['page'] ) ) {
			switch ( $_REQUEST['page'] ) {
				case 'slp_experience':
					$this->create_object_experience();
					break;
				case 'slp_general':
					new SLP_Power_Admin_General_Text();
					break;
				case 'slp_info':
					$this->create_object_info();
					break;
				case 'slp_manage_locations':
					SLP_Power_Admin_Locations::get_instance();
					require_once( SLPPOWER_REL_DIR . 'include/module/category/SLP_Power_Category_Manager.php' );

					add_filter( 'slp_column_data', array( $this, 'customize_location_list_displayed_data' ), 20, 3 );
					add_filter( 'slp_locations_manage_filters', array( $this, 'filter_LocationsFilters' ) );
					add_filter( 'slp_manage_expanded_location_columns', array( $this, 'filter_AddFieldHeadersToManageLocations' ) );
					if ( $this->slplus->Power_Category_Manager->get_category_count() > 0 ) {
						add_filter( 'slp_manage_location_columns', array( $this, 'filter_AddCategoriesHeaderToManageLocations' ) );
					}
					break;

				case 'slp_reports':
					add_action( 'admin_enqueue_scripts', array( $this, 'setup_report_scripts' ) , 20 );
					break;
			}
		}

		add_filter ( 'slp_managed_admin_pages' , array( $this , 'add_managed_pages' ) );

		// WP Taxonomy Edit for SLPlus::locationTaxonomy
		add_action( 'stores_add_form_fields', array( $this, 'add_slp_settings_to_wp_add_category' ) );
		add_action( 'stores_edit_form', array( $this, 'add_slp_settings_to_wp_edit_category' ) );

		// Location Added
		//
		//add_action( 'slp_location_added', array( $this, 'assign_location_categories' ) );
		add_action( 'slp_location_saved', array( $this, 'assign_location_categories' ) );

		// Location Upload Meta
		add_filter( 'wp_generate_attachment_metadata'   , array( $this , 'add_upload_meta' ) , 20 , 2 );

		// Taxonomy Interface
		//
		if ( isset( $_REQUEST['taxonomy'] ) && ( $_REQUEST['taxonomy'] === SLPlus::locationTaxonomy ) ) {
			$this->create_object_admin_edit_tags();
			$this->edit_tags->add_wp_filters();
		}
	}

	/**
	 * Add category view page to those we want to allow SLP to manage (add JS, etc.)
	 * @param $page_array
	 *
	 * @return array
	 */
	public function add_managed_pages( $page_array ) {
		$page_array[] = 'term.php';         // The per-categoery edit page
		$page_array[] = 'edit-tags.php';    // The main WP categories list page
		return $page_array;
	}

	/**
	 * Add upload meta.
	 *
	 * @param array|bool $data          Array of meta data for the given attachment, or false
	 *                                  if the object does not exist.
	 * @param int        $attachment_id Attachment post ID.
	 *
	 * @return mixed
	 */
	public function add_upload_meta( $data, $attachment_id ) {
		$importer = SLP_Power_Locations_Import::get_instance();
		return $importer->add_upload_meta( $data, $attachment_id );
	}

	/**
	 * Render the extra tagalong category fields for the add form.
	 */
	public function add_slp_settings_to_wp_add_category() {
		require_once( SLPPOWER_REL_DIR . 'include/module/category/SLP_Power_Category_Manager.php' );
		$this->slplus->Power_Category_Manager->render_ExtraCategoryFields();
	}

	/**
	 * Render the extra tagalong category fields for the edit form.
	 *
	 * @param WP_Term $tag
	 */
	public function add_slp_settings_to_wp_edit_category( $tag ) {
		require_once( SLPPOWER_REL_DIR . 'include/module/category/SLP_Power_Category_Manager.php' );
		print '<div id="tagalong_editform" class="form-wrap">';
		$this->slplus->Power_Category_Manager->render_ExtraCategoryFields( $tag->term_id, $this->addon->get_TermWithTagalongData( $tag->term_id ) );
		print '</div>';
	}

	/**
	 * Assign categories to a given location via the data relation table.
	 *
	 */
	public function assign_location_categories() {
		require_once( SLPPOWER_REL_DIR . 'include/module/category/SLP_Power_Category_Manager.php' );
		$this->slplus->Power_Category_Manager->set_categories_from_input();
		$this->slplus->Power_Category_Manager->update_location_category();
	}


	/**
	 * Create the edit tags object.
	 */
	private function create_object_admin_edit_tags() {
		if ( ! isset( $this->edit_tags ) ) {
			require_once( SLPPOWER_REL_DIR . 'include/class.admin.edit-tags.php' );
			$this->edit_tags = new SLPPower_Admin_EditTags( array( 'addon' => $this->addon ) );
		}
	}

	/**
	 * Create and attach the admin experience object.
	 */
	private function create_object_experience() {
		if ( ! isset( $this->experience ) ) {
			require_once( SLPPOWER_REL_DIR . 'include/class.admin.experience.php' );
			$this->experience = new SLPPower_Admin_ExperienceSettings( array( 'addon' => $this->addon ) );
		}
	}

	/**
	 * Create and attach the admin info object.
	 */
	private function create_object_info() {
		if ( ! isset( $this->info ) ) {
			require_once( SLPPOWER_REL_DIR . 'include/class.admin.info.php' );
			$this->info = new SLPPower_Admin_Info( array( 'addon' => $this->addon ) );
		}
	}

	/**
	 * Create and attach the admin processing object.
	 */
	function create_object_pages() {
		if ( ! $this->addon->using_pages ) {
			return;
		}
		if ( isset( $this->pages ) ) {
			return;
		}
		$this->pages = SLP_Power_Pages_Admin::get_instance();
	}

	/**
	 * Create the reports interface object and attach to this->reports
	 */
	function create_object_reports_tab() {
		if ( ! isset( $this->reports_tab ) ) {
			$this->reports_tab = new SLP_Power_Admin_Reports( array( 'addon' => $this->addon ) );
		}
	}

	/**
	 * Create the filter by categories div.
	 *
	 * @return string
	 */
	function createstring_FilterByCategoriesDiv() {
		require_once( SLPPOWER_REL_DIR . 'include/module/admin/SLP_Power_Admin_Location_Filters.php' );
		$HTML =
			'<div id="extra_filter_by_category" class="filter_extras">' .
			$this->slplus->Power_Admin_Location_Filters->createstring_LocationFilterForm() .
			'</div>';

		return $HTML;
	}

	/**
	 * Create the filter by properties div.
	 *
	 * @return string
	 */
	function createstring_FilterByPropertiesDiv() {
		require_once( SLPPOWER_REL_DIR . 'include/module/admin/SLP_Power_Admin_Location_Filters.php' );
		$HTML =
			"<iframe id='power_csv_download' src='' style='display:none; visibility:hidden;'></iframe>" .
			'<div id="extra_filter_by_property" class="filter_extras">' .
			$this->slplus->Power_Admin_Location_Filters->createstring_LocationFilterForm() .
			'</div>' .
			'<div id="slp-power_message_board" class="popup_message_div ui-dialog ui-widget ui-widget-content ui-corner-all ui-front ui-draggable ui-resizable">' .
			'<div class="ui-dialog-titlebar ui-widget-header">' .
			__( 'Location Processing Info', 'slp-power' ) .
			'</div>' .
			'<div id="slp-power_messages" class="ui-dialog-content ui-widget-content"></div>' .
			'</div>';

		return $HTML;
	}

	/**
	 * Save the Pro Pack options to the database in serialized format.
	 *
	 * Make sure the options are valid first.
	 *
	 * @param string[] $cbArray The checkbox array need to handle for the null to false operation
	 */
	function data_SaveOptions( $cbArray ) {
		$_POST[ $this->addon->option_name ] = array();
		array_walk( $_REQUEST, array( $this, 'set_ValidOptions' ) );

		// AdminUI->save_SerializedOption stores the $_POST[<option_name>] values plus the checkbox options (3rd param)
		// in the persistent store of wp_options
		//
		SLP_Admin_UI::get_instance()->save_SerializedOption( $this->addon->option_name, $this->addon->options, $cbArray );

		$this->addon->init_options();
	}

	/**
	 * Deactivate any plugins that this add-on replaces.
	 */
	private function deactivate_replaced_addons() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$replaced_addons = array(
			'slp-pro',
			'slp-tagalong',
			'slp-pages',
			'slp-contact-extender',
			'slp-directory-builder',
		);
		foreach ( $replaced_addons as $addon_slug ) {
			if ( $this->slplus->AddOns->get( $addon_slug , 'active' ) ) {
				deactivate_plugins( $this->slplus->AddOns->instances[ $addon_slug ]->file );
				$this->slplus->Helper->add_wp_admin_notification(
					sprintf(
						__( 'The %s add-on deactivated the conflicting %s add-on. ', 'slp-power' ),
						$this->addon->name,
						$this->slplus->AddOns->instances[ $addon_slug ]->name
					)
				);
			}
		}
	}

	/**
	 * Execute some admin startup things for this add-on pack.
	 */
	function do_admin_startup() {
		$this->addon->create_object_category_data();
		$this->addon->create_object_import_messages();


		parent::do_admin_startup();

		$this->create_object_pages();
		$this->create_object_reports_tab();
	}

	/**
	 * Enqueue the admin scripts.
	 *
	 * @used-by \SLP_Power_Admin::add_hooks_and_filters     FILTER: admin_enqueue_scripts
	 *
	 * @param string $hook
	 */
	public function setup_report_scripts( $hook ) {
		if ( $hook !== $this->slplus->admin_page_prefix . 'slp_reports' ) return;

		$scriptData = array();
		$scriptData[ 'plugin_url'             ] = SLPLUS_PLUGINURL;
		$scriptData['message_nodata'          ] = __( 'No data recorded yet. ', 'slp-power' );
		$scriptData['message_chartaftersearch'] = __( 'Chart will be available after a Store Locator Plus search has been performed.', 'slp-power' );
		$scriptData['total_searches']           = $this->reports_tab->data->total_searches;
		$scriptData['count_dataset']            = $this->reports_tab->data->counts_dataset;
		$scriptData['chart_type']               = $this->reports_tab->data->google_chart_type;

		wp_enqueue_script( 'google_jsapi', 'https://www.google.com/jsapi' );
		wp_enqueue_script( 'jquery_tablesorter', $this->addon->url . '/js/jquery.tablesorter.js', array( 'jquery' ) );

		wp_enqueue_script( 'slp_reporting', $this->addon->url . '/js/reporting.js' );
		wp_localize_script( 'slp_reporting', 'slp_power', $scriptData );
	}

	/**
	 * Add the categories column header to the manage locations table.
	 *
	 * SLP Filter: slp_manage_location_columns
	 *
	 * @param mixed[] $currentCols column name + column label for existing items
	 *
	 * @return mixed[] column name + column labels, extended with our categories data
	 */
	function filter_AddCategoriesHeaderToManageLocations( $currentCols ) {
		return array_merge( $currentCols,
			array(
				'sl_option_value' => __( 'Categories', 'slp-power' ),
			)
		);

	}

	/**
	 * Render the categories column in the manage locations table.
	 *
	 * @used-by SLP_Admin_Locations::create_string_manage_locations_table()
	 * @trigger slp_column_data
	 *
	 * @param string $data  the option_value field data from the database
	 * @param string $field the name of the field from the database (should be sl_option_value)
	 * @param string $label the column label for this column (should be 'Categories')
	 *
	 * @return string
	 */
	public function customize_location_list_displayed_data( $data, $field, $label ) {
		if (
			( $field === 'sl_option_value' ) &&
			( $label === __( 'Categories', 'slp-power' ) ) &&
			( $this->slplus->Power_Category_Manager->get_category_count() > 0 )
		) {
			return
				$this->get_location_marker_html() .
				$this->addon->create_string_icon_array( array( 'show_label' => true , 'add_edit_link' => true ) );

		}

		if ( $field === 'sl_tags' ) {
			return ( $this->slplus->currentLocation->tags != '' ) ?
				$this->slplus->currentLocation->tags :
				"";
		}

		return $data;
	}

	/**
	 * @return string
	 */
	private function get_location_marker_html() {
		$this->addon->set_LocationCategories();

		$ajax = SLP_Power_AJAX::get_instance();
		$img_src = $ajax->get_location_marker();

		if ( empty( $img_src ) ) return '';
		$tool_tip = __( 'Category Marker' , 'slp-power' );
		return <<<HTML
			<img class="location_marker" alt="{$tool_tip}" data-field="category_marker" src="{$img_src}" />
HTML;
	}

	/**
	 * Add the images column header to the manage locations table.
	 *
	 * SLP Filter: slp_manage_location_columns
	 *
	 * @param mixed[] $currentCols column name + column label for existing items
	 *
	 * @return mixed[] column name + column labels, extended with our extra fields data
	 */
	function filter_AddFieldHeadersToManageLocations( $currentCols ) {
		return array_merge( $currentCols,
			array(
				'sl_tags' => __( 'Tags', 'slp-power' ),
			)
		);
	}

	/**
	 * Add our admin pages to the valid admin page slugs.
	 *
	 * @used-by SLP_Admin_UI->is_our_admin_page()
	 *
	 * @param string[] $slugs admin page slugs
	 *
	 * @return string[] modified list of admin page slugs
	 */
	function filter_AddOurAdminSlug( $slugs ) {
		$slugs = parent::filter_AddOurAdminSlug( $slugs );
		foreach ( $this->settings_pages as $slug => $checkbox_array ) {
			if ( ! in_array( $slug, $slugs ) ) {
				$slugs[] = $slug;
				$slugs[] = $this->slplus->admin_page_prefix . $slug;
			}
		}
		if ( isset( $_REQUEST['taxonomy'] ) && ( $_REQUEST['taxonomy'] === 'stores' ) ) {
			$slugs[] = 'edit-tags.php';
			$slugs[] = 'edit_tags-stores';
			$slugs[] = 'term.php';
		}
        $slugs[] = 'store-locator-plus_page_slp-categories';

		return $slugs;
	}

	/**
	 * Add more actions to the Bulk Action drop down on the admin Locations/Manage Locations interface.
	 *
	 * @param mixed[] $items
	 *
	 * @return mixed[]
	 */
	function filter_LocationsFilters( $items ) {
		$power_filters   = array();
		$power_filters[] = array(
			'label' => __( 'Show Uncoded', 'slp-power' ),
			'value' => 'show_uncoded',
		);
		$power_filters[] = array(
			'label'  => __( 'With These Properties', 'slp-power' ),
			'value'  => 'filter_by_property',
			'extras' => $this->createstring_FilterByPropertiesDiv(),
		);

		// Only add categorize if categories exist.
		//
		if ( $this->slplus->Power_Category_Manager->get_category_count() > 0 ) {
			$power_filters[] = array(
				'label'  => __( 'In These Categories', 'slp-power' ),
				'value'  => 'filter_by_category',
				'extras' => $this->createstring_FilterByCategoriesDiv(),
			);
		}

		return array_merge( $items, $power_filters );
	}

	/**
	 * Set the Tagalong categories for the new store page.
	 *
	 * SLP Filter: slp_location_page_attributes
	 *
	 * @param mixed[] $pageAttributes - the wp_insert_post page attributes
	 *
	 * @return mixed[] - pageAttributes with tax_input set
	 */
	function set_taxonomy_for_location( $pageAttributes ) {
		$this->slplus->Power_Category_Manager->set_categories_from_input();

		return $this->slplus->Power_Category_Manager->set_page_taxonomy( $pageAttributes );
	}

	/**
	 * Set valid options from the incoming REQUEST
	 *
	 * @param mixed  $val - the value of a form var
	 * @param string $key - the key for that form var
	 */
	function set_ValidOptions( $val, $key ) {
		$simpleKey = str_replace( SLPLUS_PREFIX . '-', '', $key );

		if ( array_key_exists( $simpleKey, $this->addon->options ) ) {
			$_POST[ $this->addon->option_name ][ $simpleKey ] = stripslashes_deep( $val );
		}
	}

	/**
	 * Deactivate the competing add-on packs.
	 */
	function update_install_info() {
		parent::update_install_info();
		$this->deactivate_replaced_addons();
	}
}

