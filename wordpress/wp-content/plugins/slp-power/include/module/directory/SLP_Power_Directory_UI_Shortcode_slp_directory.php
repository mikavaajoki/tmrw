<?php
defined( 'ABSPATH' ) || exit;
/**
 * The slp_directory shortcode processor.
 *
 * [slp_directory]
 *
 * [slp_directory by="city" style="list"]
 *
 * [slp_directory by="city" style="list_with_count" locator_page="/locations/"]
 * [slp_directory by="city" style="list_with_count" landing_page="/locations/"]
 *
 * [slp_directory by="city" locator_page="/locations" locator_data="sl_id"]
 *
 * [slp_directory by="city" filter_by="<where clause>"]
 *
 * [slp_directory style="landing_page"]
 *
 * [slp_directory style="title"]
 *
 * Attributes:
 * o by = a store property to group data by
 * o filter_by = an SQL clause to include/exclude locations i.e. filter_by="sl_country='USA'"
 * o if_blank_use = alternate data field if the group by field is blank
 * o locator_page = a URL for the locator page on the site
 * o style = list, list_with_count, landing_page, title
 *
 * By attribute:
 * Takes a location property in the form of the field name. sl_store, sl_city, etc.
 *      * The following field names are aliased for ease-of-use:
 * o store, sl_store, name = the store name field (sl_store)
 * o city, sl_city = the city field (sl_city)
 *
 * Style attribute:
 * Determines the output format of the sl_directory shortcode.
 * o list = a list of the "by attribute" values from the current location database, for example a list of cities, for example "Charleston"
 * o list_with_count = same as list but with the count of locations with that property, for example "Charleston (2)"
 *
 *
 * @property        SLPPower        $addon
 * @property-read   string          $alternate_groupby_field    The alternate group by field if the initial grouping field is blank.
 * @property        string[]        $attributes                 The attributes as passed into the shortcode as name=>value pairs.
 * @property-read   string[]                        $attribute_defaults         The default attribute values - landing_page is an alias for locator_page
 * @property        string                          $content                    The content for the shortcode, the part between matching open/close shortcode pairs.
 * @property-read   string                          $filter_by
 * @property-read   string                          $group_by
 * @property        string                          $name                       The name used to call the shortcode.
 * @property-read   SLP_Power_Directory_Landing_Page $landing_page   The landing page processor.
 */
class SLP_Power_Directory_UI_Shortcode_slp_directory extends SLPlus_BaseClass_Object {
    public  $addon;
    private $alternate_groupby_field = '';
    public $attributes = array();
    private $attribute_defaults = array(
        'by' => 'sl_state',
        'if_blank_use' => '',
        'filter_by' => '',
        'landing_page' => '',
        'locator_page' => '',
        'locator_data' => '',
        'only_with_category' => '',
        'style' => 'list',
    );
    private $group_by = 'sl_state';
    private $filter_by;
    private $landing_page;
    private $only_with_category;
    public $content;
    public $name;

    /**
     * Things to do at start.
     */
    function initialize()  {
        add_filter('slp_extend_get_SQL', array($this, 'filter_SQLStatements'));
        do_action( 'slp_directory_shortcode_init' );
    }

    /**
     * Remove the SQL filter as needed.
     *
     * This is called from the parent whenever the specific shortcode processing is no longer needed.
     * Allows for multiple shortcodes with different attributes to appear on the same page.
     */
    function remove_sql_filter() {
        remove_filter('slp_extend_get_SQL', array($this, 'filter_SQLStatements'         ));
        remove_filter('slp_ajaxsql_where' , array($this, 'sql_AddFilterBy'              ));
        remove_filter('slp_ajaxsql_where' , array($this, 'sql_add_only_with_category'   ));
    }

    /**
     * Create the landing page HTML.
     *
     * @return string $HTML
     */
    private function createstring_LandingPage() {
	    $landing_page = SLP_Power_Directory_Landing_Page::get_instance();

	    add_shortcode( 'slp_addon', array( SLP_UI::get_instance() , 'remove_slp_addon_shortcodes' ) );
        $landing_page->set_html_FromResultsLayout();
	    remove_shortcode( 'slp_addon' );

        return $landing_page->html;
    }

    /**
     * Create the list HTML for this shortcode.
     *
     * @return string HTML
     */
    private function createstring_List() {
        $HTML = "<div class='slp_directory_list slp_directory_style_{$this->attributes['style']}' >";

        // Alternate Field If Group Field Is Blank
        // use_if_blank attribute
        //
        if (!empty($this->attributes['if_blank_use'])) {
            $this->alternate_groupby_field = $this->translate_field_alias($this->attributes['if_blank_use']);
        } else {
            $this->alternate_groupby_field = $this->group_by;
        }

        $offset = 0;
        while ($location = $this->slplus->database->get_Record(array('select_for_slp_directory_by_group', 'where_default_validlatlong', 'group_by'), array(), $offset++)) {

            $sanitized_property_data = sanitize_title($location['grouping_field']);

            // Make sure the URL links to the right field name from our complext MySQL construct
            //
            if (empty($location[$this->group_by]) && !empty($this->alternate_groupby_field)) {
                $field_shown = $this->alternate_groupby_field;
            } else {
                $field_shown = $this->group_by;
            }

            // listing entry if locator_page is set otherwise make it plain text output
            //
            if  ( ! empty($this->attributes[ 'locator_page' ] ) ) {

                if ( empty( $this->attributes[ 'locator_data' ] ) || ( ! isset( $location[ $this->attributes[ 'locator_data' ] ] ) ) ) {
                    $listing_entry = $this->createstring_ListLink($location['grouping_field'], $field_shown);
                } else {
                    $listing_entry = $this->createstring_ListLink( $location['grouping_field'], $field_shown , $location[ $this->attributes[ 'locator_data' ] ], $this->attributes[ 'locator_data' ] );
                }

            } else {
                $listing_entry = $location[$field_shown];
            }

            $HTML .=
                "<div class='slp_directory_entry slp_directory_entry_{$sanitized_property_data}'>" .

                // The listing entry
                //
                $listing_entry .

                // The listing count
                //
                (
                (strcasecmp($this->attributes['style'], 'list_with_count') === 0) ?
                    sprintf('<span class="slp_directory_entry_count">(%d)</span>', $location['group_count'])
                    : ''
                ) .
                '</div>';
        }
        $HTML .= '</div>';


        return $HTML;
    }

    /**
     * Create an anchor text link for a directory entry.
     *
     * @param $text the text to show on the link
     * @return string the HTML output string
     */
    private function createstring_ListLink($text, $field_name, $url_data = null , $url_field = null ) {
        $text = apply_filters( 'slp_directory_entry_text' , $text , $this->attributes );

        if ( is_null( $url_data ) ) {
            $url_data = $text;
        }
        if ( is_null( $url_field ) ) {
            $url_field = $field_name;
        }
        $url = sprintf('%s?%s=%s', $this->attributes['locator_page'], $url_field, $url_data);
        if ( $this->slplus->SmartOptions->use_nonces->is_true ) {
            $url = wp_nonce_url($url, 'show_locations', 'directory_nonce');
        }

        if (!empty($url)) {
            $url = sprintf('<a href="%s" class="slp_directory_link slp_directory_link_%s">%s</a>', $url, sanitize_title( $text ),  $text);
        }

        return $url;
    }

    /**
     * Group the directory output by a specific location property.
     */
    private function set_location_grouping() {
        if (empty ($this->attributes['by'])) {
            return;
        }
        $group_by_field = $this->translate_field_alias($this->attributes['by']);

        // Check group by is a valid location attribute
        //
        switch ($group_by_field) {
            case 'name':
            case 'store':
            case 'sl_store':
                $this->group_by = 'sl_store';
                break;

            default:
                $this->group_by = $group_by_field;
                break;
        }
    }

    /**
     * Process the slp_directory shortcode.
     *
     * Attributes:
     * o by = a store property to group data by
     * o style = list, list_with_count, details
     *
     * @return string $HTML
     */
    public function createstring_ShortcodeOutput() {
        $this->attributes = array_merge($this->attribute_defaults, $this->attributes);

        // Make landing_page an alias for locator_page
        //
        if (
            empty($this->attributes['locator_page']) &&
            !empty($this->attributes['landing_page'])
        ) {
            $this->attributes['locator_page'] = $this->attributes['landing_page'];
        }

        // Filter By Value
        //
        if (isset($this->attributes['filter_by'])) {
            $this->filter_by = $this->attributes['filter_by'];
        }

        // Only With Category
        if ( ! empty( $this->attributes[ 'only_with_category' ] ) ) {
            $this->only_with_category = $this->attributes[ 'only_with_category' ];
        }

        // Process Style Output
        //
        switch ($this->attributes['style']) {

            // Landing Page
            //
            case 'landing_page':
                $HTML = $this->createstring_LandingPage();
                break;

            // Title
            //
            case 'title':
	            $power_ui = SLP_Power_UI::get_instance();
                $HTML = '';
                if ($power_ui->is_a_valid_directory_redirect()) {
                    if (isset($_REQUEST[$power_ui->location_property])) {
                        $HTML .= $_REQUEST[$power_ui->location_property];
                    }
                }
                break;

            // List (default)
            // List With Count
            //
            case 'list':
            case 'list_with_count':
            default:
                if (!empty($this->attributes['by'])) {
                    $this->set_location_grouping();
                }
                $HTML = $this->createstring_List();
                break;
        }

        return $HTML;
    }

    // Map aliases to the proper field name.
    // i.e. city to sl_city
    //
    private function translate_field_alias($field_name) {
        $lowercase_field = strtolower($field_name);
        $alias_array = array_flip($this->addon->location_fields);
        if (isset($alias_array[$lowercase_field])) {
            $lowercase_field = $alias_array[$lowercase_field];
        }
        return $lowercase_field;
    }

    /**
     * Custom SQL command statements for the shortcode processor.
     *
     * @param string $command
     * @return string
     */
    public function filter_SQLStatements($command) {
        $location_table = $this->slplus->database->info['table'];

        if ( ! empty( $this->filter_by ) ) {
            add_filter('slp_ajaxsql_where', array($this, 'sql_AddFilterBy'));
        }

        if ( ! empty( $this->only_with_category ) ) {
            add_filter('slp_ajaxsql_where', array($this, 'sql_add_only_with_category'));
        }

        $sql_statement = $command;
        switch ($command) {

            case 'select_for_slp_directory_by_group':
                // FILTER: slp_extend_get_SQL_selectall
                $sql_statement = apply_filters(
                    'slp_extend_get_SQL_selectall',
                    'SELECT *, ' .
                    '(CASE ' .
                    "WHEN {$this->group_by} IS NULL OR {$this->group_by} = '' THEN {$this->alternate_groupby_field} " .
                    "WHEN {$this->group_by} IS NOT NULL                       THEN {$this->group_by} " .
                    'END) ' .
                    'AS grouping_field, ' .
                    'count(*) as group_count ' .
                    "FROM {$location_table} "
                );
                break;

            case 'group_by':
                if (!empty($this->group_by)) {
                    $sql_statement = ' GROUP BY grouping_field ';
                }
                break;

            default:
                break;
        }

        return $sql_statement;
    }

    /**
     * Add the filter_by to the WHERE selection clause.
     *
     * @param $where
     * @return string
     */
    public function sql_AddFilterBy($where) {
        return $this->slplus->database->extend_Where($where, " {$this->filter_by} ");
    }

    /**
     * Add the only_with_category to the WHERE selection clause.
     *
     * @param $where
     * @return string
     */
    public function sql_add_only_with_category( $where ) {
        require_once( SLPPOWER_REL_DIR . 'include/module/category/SLP_Power_Category_Manager.php' );
        $category_id_list = $this->slplus->Power_Category_Manager->convert_category_name_list_to_id_list( $this->only_with_category );

        if ( empty( $category_id_list ) ) {
            return $where;
        }
        return $this->slplus->Power_Category_Data->add_where_location_has_category( $where , $category_id_list );
    }

}