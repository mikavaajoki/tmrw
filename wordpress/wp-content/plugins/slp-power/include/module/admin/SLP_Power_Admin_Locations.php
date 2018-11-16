<?php
defined( 'ABSPATH' ) || exit;

/**
 * The things that modify the Admin / Locations UX.
 *
 * @property        SLPPower                          $addon
 * @property        SLP_Power_Admin_Locations_Import  $import_subtab
 * @property-read   SLP_Settings                      $settings
 */
class SLP_Power_Admin_Locations  extends SLPlus_BaseClass_Object {
	public  $addon;
	public  $import_subtab;
	private $settings;

	/**
	 * Start things for admin locations.
	 */
	public function initialize() {
		$this->addon = $this->slplus->addon( 'Power' );
		$this->create_object_locations_import_subtab();
		add_action( 'slp_manage_locations_action'           , array( $this ,'handle_actions' ) );
		if (current_user_can('manage_slp_admin') ) {
			add_action( 'slp_build_locations_panels'    , array( $this , 'add_location_tab_panels'       ) , 30 );
			add_action( 'slp_modify_location_add_form'  , array( $this , 'modify_add_form' ), 30 );
			add_filter( 'slp_modify_admin_locations_script_data' , array( $this, 'modify_script_data' ) );
			add_filter( 'slp_locations_manage_bulkactions', array( $this, 'extend_bulk_actions' ) );
		}
	}

	/**
	 * Add more actions to the Bulk Action drop down on the admin Locations/Manage Locations interface.
	 *
	 * @param array $dropdownItems
	 *
	 * @return array
	 */
	public function extend_bulk_actions( $dropdownItems ) {
		$power_actions = array();

		// Only add categorize if categories exist.
		// TODO: the category checklist should be pulled by AJAX
		//
		if ( $this->slplus->Power_Category_Manager->get_category_count() > 0 ) {
			$select_text = __( 'Select your categories: ', 'slp-power' );
			$selector = $this->slplus->Power_Category_Manager->get_check_list( 0 );

			$categorize_html = <<< SCRIPT
				<script type="text/javascript">
					var hiddenInputs=new Array();
					jQuery(document).ready( function(){
						jQuery('#slp_tagalong_fields input:checkbox').change(function(){
							var theCats="";
							var hiddenInputs=new Array();								
							jQuery("#slp_tagalong_fields li").find("input[type=checkbox]:checked" ).each( function(idx) {
								theCats += jQuery(this).parent().text() + ", ";
								hiddenInputs.push("<input type='hidden' name='tax_input[stores][]' value='"+jQuery(this).val()+"'/>");
							});
						});
					});
				</script>
				<div id="extra_categorize" class="bulk_extras">{$select_text}
					<div id="slp_tagalong_selector">
						{$selector}
					</div>
				</div>
SCRIPT;
			$power_actions[] = array( 'label'  => __( 'Categorize', 'slp-power' ), 'value'  => 'categorize', 'extras' => $categorize_html );
		}

		$power_actions[] = array( 'label' => __( 'Export, Download CSV' , 'slp-power' ), 'value' => 'export'        );
		$power_actions[] = array( 'label' => __( 'Export, Hosted CSV'   , 'slp-power' ), 'value' => 'export_local'  );
		$power_actions[] = array( 'label' => __( 'Geocode Selected'     , 'slp-power' ), 'value' => 'recode'        );
		$power_actions[] = array( 'label' => __( 'Geocode All Uncoded'  , 'slp-power' ), 'value' => 'recode_all'    );
		$power_actions[] = array( 'label' => __( 'Tag'                  , 'slp-power' ), 'value'  => 'add_tag',
			'extras' =>
				'<div id="extra_add_tag" class="bulk_extras">' .
				'<label for="sl_tags">' . __( 'Enter your comma-separated tags:', 'slp-power' ) . '</label>' .
				'<input name="sl_tags">' .
				'</div>',
		);
		$power_actions[] = array( 'label' => __( 'Tag, Remove'          , 'slp-power' ), 'value' => 'remove_tag'    );

		return array_merge( $dropdownItems, $power_actions );
	}

	/**
	 * Extend the edit location form to show store pages data.
	 */
	public function modify_add_form() {
		$vue = new SLP_Template_Vue( array( 'plugin_dir' => SLPPOWER_REL_DIR ) );

		/** @var  SLP_Power_Category_Manager $category_manager */
		$category_manager = SLP_Power_Category_Manager::get_instance();

		/** @var  SLP_Admin_Locations_Add $add_form */
		$add_form = SLP_Admin_Locations_Add::get_instance();

		$add_form->group_params[ 'header' ] = '';
		$add_form->group_params[ 'group_slug' ] = 'power';
		$add_form->group_params[ 'div_group' ]  = 'right_side flex xs4';
		$add_form->settings->add_group( $add_form->group_params );

		$add_form->settings->add_ItemToGroup( array(
			'group_params' => $add_form->group_params ,
			'show_label'   => false,
			'wrapper'      => false,
			'type'         => 'custom' ,
			'custom'       =>  $vue->get_content( 'locations_add_categories' ),
		) );

		// Only need to do this if some WP categories exist.
		//
		if ( $category_manager->get_category_count() > 0 ) {
			$categories_html = $category_manager->get_check_list( 0 );  // Categories for all posts
			if ( ! empty( $categories_html ) ) {
				$add_form->settings->add_ItemToGroup( array(
					'setting'    => 'slp_tagalong_fields',
					'custom'     => $categories_html,
					'type'       => 'custom',
					'show_label' => false,
					'wrapper'      => false,
					'group_params'=> $add_form->group_params
				) );
			}
		}
	}

	/**
	 * Modify the manage locations localized script data.
	 *
	 * @param array $script_data
	 *
	 * @return array
	 */
	public function modify_script_data( $script_data ) {
		$more_text = array();
		$slugs = array( 'categories' , 'power' );
		foreach ( $slugs as $slug ) {
			$more_text[ $slug ] = $this->slplus->Text->get_text_string( $slug );
		}
		$script_data[ 'text' ] = array_merge( $script_data[ 'text' ] , $more_text );

		/** @var  SLP_Power_Category_Manager $category_manager */
		$category_manager = SLP_Power_Category_Manager::get_instance();
		$script_data[ 'category_names' ] = $category_manager->get_category_names();
		$script_data[ 'has_categories' ] = ! empty( $script_data[ 'category_names' ] );

		return $script_data;
	}

	/**
	 * Add the panels to the location tab including the import subtab and filters on the list locations subtab.
	 *
	 * @used-by SLP_Admin_Locations::render_adminpage()
	 * @trigger slp_build_locations_panels
	 *
	 * @param  SLP_Settings    $settings
	 */
	public function add_location_tab_panels( $settings ) {
		$this->settings = $settings;
		require_once( SLPPOWER_REL_DIR . 'include/module/admin/SLP_Power_Admin_Location_Filters.php' );
		$this->addon->create_CSVLocationImporter();
		if ( is_object( $this->import_subtab ) ) $this->import_subtab->create_import_subtab( $this->settings );
	}

	/**
	 * Filter the manage locations list by property matches in the filter form.
	 *
	 * @param string $where
	 * @return string
	 */
	function action_ManageLocations_ByProperty($where) {
		// Get the SQL command and where params
		//
		list($sqlCommands, $sqlParams) = $this->slplus->Power_Admin_Location_Filters->create_LocationSQLCommand( $this->create_LocationFilterInputArray() );
		$whereclause = $this->slplus->database->get_SQL('where_default');
		if ( strpos( $whereclause, '%' ) !== false ) {
			$whereclause = $this->slplus->database->db->prepare( $whereclause , $sqlParams );
		}
		$whereclause = preg_replace('/^\s+WHERE /i','',$whereclause);

		return $whereclause;
	}

	/**
	 * Create an array of location filter inputs from $_REQUEST.
	 *
	 * @return string[] named array
	 */
	private function create_LocationFilterInputArray() {
		$formdata_array = array();
		$location_filter_inputs =
			array(
				'name'              ,
				'state_filter'      ,
				'state_joiner'      ,
				'zip_filter'        ,
				'zip_filter_joiner' ,
				'country_joiner'    ,
				'country_filter'
			);
		foreach ($location_filter_inputs as $input) {
			if ( ! empty( $_REQUEST[$input] ) ) {
				$formdata_array[$input] = $_REQUEST[$input];
			}
		}
		return $formdata_array;
	}

	/**
	 * Create and attach the admin locations import object.
	 */
	private function create_object_locations_import_subtab() {
		require( SLPLUS_PLUGINDIR . 'include/module/location/SLP_Location_Manager.php' );
		if ( $this->slplus->Location_Manager->has_max_locations() ) return;
		if ( ! isset( $this->import_subtab ) ) {
			require_once( SLPPOWER_REL_DIR . 'include/module/admin/SLP_Power_Admin_Locations_Import.php' );
			$this->import_subtab = new SLP_Power_Admin_Locations_Import();
		}
	}

	/**
	 * Additional location processing on manage locations admin page.
	 */
	public function handle_actions( $slp_action_handler ) {
		$action_processor = SLP_Power_Admin_Locations_Actions::get_instance();
		$action_processor->addon = $this->addon;
		$action_processor->location_handler = $this;
		$action_processor->slp_action_handler = $slp_action_handler;
		$action_processor->process( $_REQUEST['act'] );
	}
}