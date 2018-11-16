<?php
/**
 * Class SLP_Premier_Admin_Experience
 *
 * @property        SLP_Premier                          $addon
 * @property        SLP_Premier_Text_Admin_Experience    $Admin_Experience_Text
 * @property-read   array                               $group_params
 * @property-read   SLP_Settings                        $Settings
 */
class SLP_Premier_Admin_Experience extends SLPlus_BaseClass_Object {
    public $addon;
    protected $class_prefix = 'SLP_Premier_';
    private $group_params;
    private $Settings;


    /**
     * Instantiate the admin experience object.
     */
    function initialize() {
    	SLP_Premier_Text_Admin_Experience::get_instance();

        parent::initialize();

        $this->slplus->enqueue_google_maps_script();

        $this->group_params = array( 'plugin' => $this->addon, 'section_slug' => null, 'group_slug' => null );
        add_action( 'slp_build_map_settings_panels', array( $this, 'modify_tab' ), 99 );

        add_filter( 'slp_radius_behavior_description' , array( $this , 'extend_radius_behavior_description' ) );
        add_filter( 'slp_radius_behavior_selections'  , array( $this , 'extend_radius_behavior_selections'  ) );
    }

    /**
     * Modify entries on the tab.
     *
     * @param SLPlus_Settings $Settings
     */
    public function modify_tab( $Settings ) {
	    $this->Settings = $Settings;
	    $this->modify_map();
	    $this->modify_results();
	    $this->modify_search();
    }

    /**
     * Map
     */
    private function modify_map() {
	    $this->group_params['section_slug'] = 'map';
	    $this->group_params['group_slug'] = 'at_startup';
	    $this->Settings->add_ItemToGroup( array(
		    'group_params' => $this->group_params,
		    'custom'      => $this->create_map_center_div(),
		    'description' =>
			    __( 'The current position of Center Lat/Long Fallback.', 'slp-premier' ) .
			    __( 'This what will be used to display the initial locations when Google Geocoding is offline.', 'slp-premier' ) .
			    __( 'Only locations within the Results / Radius To Search Initially setting will be shown.', 'slp-premier' ),
		    'show_label'  => false,
		    'type'        => 'custom',
	    ) );

    }

    /**
     * Results
     */
    private function modify_results() {
	    $this->group_params['section_slug'] = 'results';
	    $this->group_params['group_slug'] = 'appearance';

	    $this->Settings->add_ItemToGroup( array(
		    'group_params' => $this->group_params,
		    'label'        => __( 'Category Name Separator', 'slp-premier' ),
		    'description'  =>
			    __( 'The string to be used to separate Tagalong category names in the map results.  ', 'slp-premier' ) .
			    __( 'Utilized with Taglong and the [slp_location category_names] shortcode. ', 'slp-premier' ) .
			    __( 'Can include HTML such as <br>. ', 'slp-premier' )
	    ,
		    'option'       => 'category_name_separator',
	    ) );

	    $this->Settings->add_ItemToGroup( array(
		    'group_params' => $this->group_params,
		    'label'        => __( 'Pagination Label', 'slp-premier' ),
		    'description'  => __( 'Enter a label to appear in front of the previous/next page icons in the results.', 'slp-premier' ),
		    'option'       => 'pagination_label',
	    ) );
    }

    /**
     * Search
     */
    private function modify_search() {
	    $this->group_params['section_slug'] = 'search';
	    $this->group_params['group_slug'] = 'functionality';

	    $this->Settings->add_ItemToGroup( array(
		    'group_params' => $this->group_params,
		    'option'      => 'dropdown_autosubmit',
		    'type'         => 'checkbox',
		    'label'        => __( 'Dropdown Autosubmit', 'slp-premier' ),
		    'description'  => __( 'If checked, automatically submit searches when a dropdown changes.', 'slp-premier' )
	    ) );

	    $this->Settings->add_ItemToGroup( array(
		    'group_params' => $this->group_params,
		    'type'         => 'subheader',
		    'label'        => __( 'Search Form Address Processing', 'slp-premier' ),
	    ) );

	    $this->Settings->add_ItemToGroup( array(
		    'group_params' => $this->group_params,
		    'option'      => 'show_address_guess',
		    'type'         => 'checkbox',
		    'label'        => __( 'Show Address Guess', 'slp-premier' ),
		    'description'  => __( 'If checked, replace the address the user typed into the address box with what Google thought they meant.', 'slp-premier' )
	    ) );

	    $this->Settings->add_ItemToGroup( array(
		    'group_params' => $this->group_params,
		    'option'      => 'region_influence_enabled',
		    'type'         => 'checkbox',
		    'label'        => __( 'Country Influences Guess', 'slp-premier' ),
		    'description'  => __( 'When checked us the selected Map Domain to influence how Google guesses what the user meant when entering a search address.', 'slp-premier' )
	    ) );

	    // The details and map of the boundary influence.
	    $this->Settings->add_ItemToGroup( array(
	        'group_params'  => $this->group_params,
		    'description' => $this->create_string_boundaries_influence(),
		    'type'        => 'details',
	    ) );
    }

    /**
     * Create the boundaries influence description.
     */
    private function create_map_center_div() {
        return
            '<div class="input-group">'.
            '<label for="center_map">' . __( 'Map Center Fallback', 'slp-premier' ) . '</label>' .
            '<div id="center_map" name="center_map"></div>'.
            '</div>'
            ;
    }

    /**
     * Create the boundaries influence description.
     */
    private function create_string_boundaries_influence() {
        $ne_coordinates = sprintf(
            '<span id="ne_boundary">(%s,%s)</span>',
            $this->addon->options[ 'boundaries_influence_max_lat' ],
            $this->addon->options[ 'boundaries_influence_max_lng' ]
        );
        $sw_coordinates = sprintf(
            '<span id="sw_boundary">(%s,%s)</span>',
            $this->addon->options[ 'boundaries_influence_min_lat' ],
            $this->addon->options[ 'boundaries_influence_min_lng' ]

        );

        return
            "<div id='boundaries_map_wrapper' style='width:100%; height: 100%; display: block;'>" .

            "<input type='hidden' id='{$this->addon->option_name}[boundaries_influence_min_lat]' name='{$this->addon->option_name}[boundaries_influence_min_lat]' value='{$this->addon->options['boundaries_influence_min_lat']}' /> " .
            "<input type='hidden' id='{$this->addon->option_name}[boundaries_influence_max_lat]' name='{$this->addon->option_name}[boundaries_influence_max_lat]' value='{$this->addon->options['boundaries_influence_max_lat']}' /> " .
            "<input type='hidden' id='{$this->addon->option_name}[boundaries_influence_min_lng]' name='{$this->addon->option_name}[boundaries_influence_min_lng]' value='{$this->addon->options['boundaries_influence_min_lng']}' /> " .
            "<input type='hidden' id='{$this->addon->option_name}[boundaries_influence_max_lng]' name='{$this->addon->option_name}[boundaries_influence_max_lng]' value='{$this->addon->options['boundaries_influence_max_lng']}' /> " .

            "<span id='current_boundaries'>" .
            sprintf(
                __( 'Boundaries are currently set to %s southwest and %s northeast.', 'slp-premier' ),
                $sw_coordinates,
                $ne_coordinates
            ) .
            '</span>' .

            '<div id="boundaries_map"></div>' .
            '</div>';
    }

    /**
     * Extend Radius Behavior Selections
     * @param string $description
     * @return array
     */
    public function extend_radius_behavior_description( $description ) {
        if ( $this->slplus->SmartOptions->use_territory_bounds->is_false ) {
            return $description;
        }

        $description .= __( '<strong>Always Use, address entered must be in territory</strong> - location must be within radius AND address entered but be in the location territory.<br/>' , 'slp-premier' );

        $description .= __( '<strong>Use for locations without a territory, Do not use for territories</strong> - show locations within radius if they have no territory as well as locations with a territory that covers the entered address.'  , 'slp-premier' );

        return $description;
    }

    /**
     * Extend Radius Behavior Selections
     * @param array $selections
     * @return array
     */
    public function extend_radius_behavior_selections( $selections ) {
        if ( $this->slplus->SmartOptions->use_territory_bounds->is_false ) {
            return $selections;
        }
        $selections[] = array( 'label' => __( 'Always Use, address entered must be in territory' , 'slp-premier' ), 'value' => 'in_radius_and_in_territory' );
        $selections[] = array( 'label' => __( 'Use for locations without a territory, Do not use for territories'  , 'slp-premier' ), 'value' => 'in_radius_no_terr_set_or_in_territory' );
        return $selections;
    }
}
