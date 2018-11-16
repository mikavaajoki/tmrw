<?php
defined( 'ABSPATH' ) || exit;

/**
 * Manage categories for admin, UI, CRON, AJAX, REST.
 *
 * @property        SLPPower                $addon
 * @property        SLP_Power_Category_Data $category_data          The category table database interface.
 * @property        string[]                $category_names         The category names, index is the term ID.
 * @property        array                   $location_categories    The current location category ID array.
 * @property        SLPlus                  $slplus
 * @property        WP_Term[]               $wp_categories
 * @property-read   int                     $wp                     -category-count      Count of active categories. Fetch with get_category_count()
 */
class SLP_Power_Category_Manager extends SLPlus_BaseClass_Object {
    public  $addon;
    public  $wp_categories;
    private $wp_category_count;
    private $category_names;
    public  $category_data;
    public  $location_categories;
    private $checklist_cache;

    /**
     * Things we do at the start.
     */
    public function initialize() {
        SLP_Power_Text::get_instance();
        $this->addon = $this->slplus->AddOns->instances[ 'slp-power' ];
        $this->addon->create_object_category_data();
        $this->category_data = $this->addon->category_data;
    }

    /**
     * Add location counts to terms returned by WP get_term
     *
     * @param array              $terms      Array of found terms.
     * @param array              $taxonomies An array of taxonomies.
     * @param array              $args       An array of get_terms() arguments.
     * @param WP_Term_Query|null $term_query The WP_Term_Query object.  (Only available in WP 4.6+, null otherwise)
     *
     * @return array
     */
    public function add_location_counts_to_terms( $terms , $taxonomies , $args , $term_query = null ) {
        foreach ( $terms as $key => $term ) {
            if ( ! is_a( $term , 'WP_Term' ) ) {
                continue;
            }
            $count_data                    = $this->addon->category_data->get_Record( 'select_count_for_termid' , $term->term_id );
            $terms[ $key ]->location_count = $count_data[ 'location_count' ];
        }

        return $terms;
    }

    /**
     * Add single quotes to a string.
     *
     * @used-by \SLP_Power_Category_Manager::delete_detached_categories
     *
     * @param string $string
     *
     * @return string
     */
    public function add_single_quotes( $string ) {
        return "'$string'";
    }

    /**
     * Additional processing of CSV records during an import.
     *
     * @param string[] $locationData
     *
     * @return string[]
     */
    public function create_categories_from_location_data( $locationData ) {
	    $this->location_categories = array();

        // Allow "category_slug" to be used if category is empty, or 'categories' as alias
        //
        if ( empty( $locationData[ 'category' ] ) ) {
	        if ( ! empty( $locationData[ 'category_slug' ] ) ) {
		        $locationData['category'] = trim( $locationData['category_slug'] );
	        } elseif ( ! empty( $locationData[ 'categories' ] ) ) {
	             $locationData['category'] = trim( $locationData['categories'] );
	        }
        } else {
            $locationData[ 'category' ] = trim( $locationData[ 'category' ] );
        }

        if ( empty( $locationData[ 'category' ] ) ) {
            return $locationData;
        }

        // Build the category list
        //
        $locationData[ 'category' ] = wp_kses_decode_entities( $locationData[ 'category' ] );
        $categories                 = explode( ',' , $locationData[ 'category' ] );
        foreach ( $categories as $categoryName ) {
            $this->create_store_category( array( 'category' => $categoryName ) );
        }

        return $locationData;
    }

    /**
     * Create the store categories from the CSV data.
     *
     * categoryData elements include:
     * - category <string> the category name with :: to separate parents and children
     * - description <string> description of the category
     * - slug <string> which slug to use
     * - medium_icon <string> the URL to the icon file
     * - map_marker <string> the URL for the map marker file
     *
     * @param string[] $categoryData
     *
     * @return null
     */
    private function create_store_category( $categoryData ) {
        if ( empty( $categoryData[ 'category' ] ) ) {
            return;
        }

        $category_name_list        = preg_split( '/::/' , $categoryData[ 'category' ] );
        $category_count            = count( $category_name_list );
        $new_category[ 'term_id' ] = 0;
        $current_category_level    = 1;
        foreach ( $category_name_list as $category_name ) {

            // Is the the last category in the list (no preceding ::)
            //
            $is_last_category = ( $current_category_level ++ === $category_count );

            // Setup extra category data like parent pointer, description, and slug
            //
            $extra_category_data = array();
            if ( $is_last_category && ! empty( $categoryData[ 'slug' ] ) ) {
                $extra_category_data[ 'slug' ] = $categoryData[ 'slug' ];
            }
            $extra_category_data[ 'parent' ]      = $new_category[ 'term_id' ];
            $extra_category_data[ 'description' ] = ( $is_last_category && ! empty( $categoryData[ 'description' ] ) ) ? $categoryData[ 'description' ] : $category_name;

            // Add the new category if it does not exist.
            //
            $new_category = term_exists( $category_name , 'stores' );
            if ( ! isset( $new_category[ 'term_id' ] ) || ( $new_category[ 'term_id' ] === 0 ) ) {
                $new_category = wp_insert_term( $category_name , 'stores' , $extra_category_data );
            }

            if ( ! is_wp_error( $new_category ) && ! empty( $new_category[ 'term_id' ] ) ) {
	            $this->location_categories[] = $new_category[ 'term_id' ];
            }
        }
        return;
    }

    /**
     * Remove any categories no longer attached to a location from the mapping table.
     *
     * @uses SLP_Power_Category_Manager::add_single_quotes()
     */
    private function delete_detached_categories() {
        $inTerms = join( ',' , array_map( array( $this , 'add_single_quotes' ) , $this->location_categories ) );
        $this->category_data->db->query( $this->category_data->db->prepare( $this->category_data->get_SQL( 'delete_category_by_id' ) , $this->slplus->currentLocation->id ) . ( ! empty( $inTerms ) ? 'AND TERM_ID NOT IN (' . $inTerms . ')' : '' ) );
    }

    /**
     * Get all of the SLPlus::locationTaxonomy terms available.
     */
    private function get_all_categories() {
        if ( ! isset( $this->wp_categories ) ) {
            add_filter( 'get_terms' , array( $this , 'add_location_counts_to_terms' ) , 10 , 4 );
            $this->wp_categories = get_categories( array( 'taxonomy' => SLPlus::locationTaxonomy , 'hide_empty' => false , ) );
            remove_filter( 'get_terms' , array( $this , 'add_location_counts_to_terms' ) , 10 );
        }
    }

    /**
     * Set the location categories based on the $_REQUEST['tax_input'] values.
     */
    public function get_categories_from_input() {
        $this->set_categories_from_input();

        return $this->location_categories;
    }

    /**
     * Return the number of WP categories available for locations.
     * @return int
     */
    public function get_category_count() {
        $this->get_all_categories();
        if ( ! isset( $this->wp_category_count ) ) {
            $this->wp_category_count = count( $this->wp_categories );
        }

        return $this->wp_category_count;
    }

	/**
     * Get category names.
     *
	 * @return SLPlus_BaseClass_Object
	 */
    public function get_category_names() {
        if ( ! isset( $this->category_names ) ) {
	        $this->get_all_categories();

	        /** @var WP_Term $wp_term */
	        $this->category_names = array();
	        foreach ( $this->wp_categories as $idx => $wp_term ) {
	            $id = $wp_term->term_id;
	            $name = $wp_term->name;
	            $this->category_names[ $wp_term->term_id ] = $wp_term->name;
            }
        }
        return $this->category_names;
    }

	/**
     * Get category by term id.
     *
	 * @param int $term_id
	 *
	 * @return WP_Term
	 */
    private function get_category_by_id( $term_id ) {
        if ( ! isset( $this->wp_categories_by_id[ $term_id ] ) ) {
            $this->wp_categories_by_id = array();

            /** @var WP_Term $wp_term */
	        foreach ( $this->wp_categories as $idx => $wp_term ) {
                $this->wp_categories_by_id[ $wp_term->term_id ] = $idx;
            }
        }

        return $this->wp_categories[ $this->wp_categories_by_id[ $term_id ] ];
    }

	/**
     * Return location counts in a category.
     *
	 * @param int $term_id
	 *
	 * @return WP_Term
	 */
    public function get_locations_in_category( $term_id ) {
        $this->get_all_categories();
        return $this->get_category_by_id( $term_id )->location_count;
    }

    /**
     * Generate the categories check list.
     *
     * @param int $post_id the ID for the store page related to the location.
     *
     * @return string HTML of the checklist.
     */
    public function get_check_list( $post_id ) {
        $post_id = intval( $post_id );
        if ( ! isset( $this->checklist_cache[ $post_id ] ) ) {
            $args                              = array(
                'checked_ontop' => false ,
                'taxonomy'      => SLPlus::locationTaxonomy ,
                'echo'          => false ,
            );
            $this->checklist_cache[ $post_id ] = '<div id="slp_tagalong_fields" class="slp_editform_section">' . '<ul>' . wp_terms_checklist( $post_id , $args ) . '</ul>' . '</div>';
        }

        return $this->checklist_cache[ $post_id ];
    }

    /**
     * Return the categories array in a SLP taxonomy notation for WordPress.
     * @return array
     */
    public function get_taxonomy() {
        return array( SLPlus::locationTaxonomy => $this->location_categories );
    }

    /**
     * Add the location_categories into a named array with key 'stores' for WP taxonomy processing.
     */
    public function get_taxonomy_from_input() {
        $this->set_categories_from_input();

        return $this->get_taxonomy();
    }

    /**
     * Map categories to their locations in the categories data table.
     *
     * Does NOT update the currentLocation (wp_store_locator table) attributes.
     */
    public function map_categories_to_locations() {
        if ( ! $this->slplus->currentLocation->isvalid_ID() ) {
            return;
        }
        foreach ( $this->location_categories as $category ) {
            $this->category_data->add_RecordIfNeeded( $this->slplus->currentLocation->id , $category );
        }
    }

    /**
     * Render the extended category fields on the WordPress add/edit category forms for store_page categories.
     *
     * @param   int     $term_id       the category id.
     * @param   mixed[] $category_data tagalong data for this category.
     */
    public function render_ExtraCategoryFields( $term_id = null , $category_data = null ) {
        ?>

        <div id="slp_extended_data">
            <h3><?= $this->slplus->Text->get_text_string( array( 'label' , 'slp_settings' ) ) ?></h3>

            <!-- Category Rank -->
            <div class="input-group">
                <div class="form-field short_field">
                    <label for="rank"><?= $this->slplus->Text->get_text_string( 'rank' ) ?></label>
                    <input type="text" id="rank" name="rank" value="<?= $category_data[ 'rank' ] ?>">
                    <p><?= $this->slplus->Text->get_text_string( array( 'description' , 'marker_rank' ) ) ?></p>
                </div>
            </div>

            <!-- Map Marker -->
            <?php
                $icon_maker = new SLP_Settings_icon( array( 'name' => 'map-marker' , 'label' => $this->slplus->Text->get_text_string( 'map_marker' ) , 'value' => $category_data[ 'map-marker' ] ) );
                $icon_maker->display();
            ?>
            <p><?= $this->slplus->Text->get_text_string( array( 'description' , 'map_marker' ) ) ?></p>

            <!-- Medium Icon -->
            <?php
            $icon_maker = new SLP_Settings_icon( array( 'name' => 'medium-icon' , 'label' => $this->slplus->Text->get_text_string( 'medium_icon' ) , 'value' => $category_data[ 'medium-icon' ] ) );
            $icon_maker->display();
            ?>
            <p><?= $this->slplus->Text->get_text_string( array( 'description' , 'medium_icon' ) ) ?></p>

            <!-- Category URL -->
            <div class="input-group">
                <div class="form-field">
                    <label for="category_url"><?= $this->slplus->Text->get_text_string( 'category_url' ); ?></label>
                    <input type="text" id="category_url" name="category_url" value="<?= $category_data[ 'category_url' ] ?>">
                    <p><?= $this->slplus->Text->get_text_string( array( 'description' , 'category_url' ) ) ?></p>
                </div>
            </div>

            <!-- URL Target -->
            <div class="input-group">
                <div class="form-field">
                    <label for="url_target">URL Target</label>
                    <input type="text" id="url_target" name="url_target" value="<?= $category_data[ 'url_target' ] ?>">
                    <p><?= $this->slplus->Text->get_text_string( array( 'description' , 'url_target' ) ) ?></p>
                </div>
            </div>

        </div>
        <?php
    }

    /**
     * Reset the current location store categories attributes.
     */
    private function reset_current_location_categories() {
        if ( ! empty ( $this->slplus->currentLocation->attributes[ 'store_categories' ] ) ) {
            if ( is_array( $this->slplus->currentLocation->attributes[ 'store_categories' ] ) ) {
                unset( $this->slplus->currentLocation->attributes[ 'store_categories' ] );
            }
            $this->slplus->currentLocation->update_Attributes( array() );
        }
    }

    /**
     * Set the array of location category IDs passed in from $_REQUEST['tax_input']['stores']
     */
    public function set_categories_from_input() {
        if ( ! isset( $this->location_categories ) ) {
            if ( isset( $_REQUEST[ 'tax_input' ] ) && isset( $_REQUEST[ 'tax_input' ][ SLPlus::locationTaxonomy ] ) && is_array( $_REQUEST[ 'tax_input' ][ SLPlus::locationTaxonomy ] ) ) {
                $this->location_categories = $_REQUEST[ 'tax_input' ][ SLPlus::locationTaxonomy ];
            } else {
                $this->location_categories = array();
            }
        }
    }

    /**
     * Return an comma-separated_string of category IDs by parsing a comma-separated string.
     *
     * This Cat, That Cat becomes 12,13
     *
     * @param   string $category_name_list A string of category names in plain text
     *
     * @return  string                      A string of category IDs that match those names
     */
    public function convert_category_name_list_to_id_list( $category_name_list ) {
        if ( empty( $category_name_list ) ) {
            return '';
        }
        $category_list  = array();
        $category_slugs = preg_split( '/,/' , $category_name_list );
        foreach ( $category_slugs as $slug ) {
            $category = get_term_by( 'slug' , sanitize_title( $slug ) , SLPlus::locationTaxonomy );
            if ( $category ) {
                $category_list[] = $category->term_id;
            }
        }

        return join( ',' , $category_list );

    }

    /**
     * Set the store page taxonomy.
     *
     * @param   array $page_attributes
     *
     * @return array
     */
    public function set_page_taxonomy( $page_attributes ) {
        return array_merge( $page_attributes , array( 'tax_input' => $this->get_taxonomy() ) );
    }

    /**
     * Attach our category data to the current location.
     */
    function update_location_category() {

        add_filter( 'slp_location_page_attributes' , array( $this , 'set_page_taxonomy' ) , 30 );

        $this->slplus->currentLocation->crupdate_Page( true );

        $this->delete_detached_categories();

        $this->reset_current_location_categories();

        $this->map_categories_to_locations();
    }

}

global $slplus;
if ( is_a( $slplus , 'SLPlus' ) ) {
    $slplus->add_object( new SLP_Power_Category_Manager() );
}
