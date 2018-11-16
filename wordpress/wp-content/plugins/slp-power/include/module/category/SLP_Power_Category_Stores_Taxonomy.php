<?php
defined('ABSPATH') || exit;

/**
 * Tagalong stores taxonomy interface.
 *
 * @property        SLPPower                            $addon
 * @property-read   SLP_Power_Category_Stores_Taxonomy_Meta       $meta                   Term Meta Data
 * @property        SLPlus                              $slplus
 * @property        string                              $taxonomy   The 'stores' taxonomy name.
 *
 * NOTE: get_term employs the WP Object Cache to avoid subsequent data I/O calls when fetching the same object ID.
 */
class SLP_Power_Category_Stores_Taxonomy extends SLPlus_BaseClass_Object {
    public $addon;
    private $meta;
    public $taxonomy = SLPlus::locationTaxonomy;

    /**
     * Startup things.
     */
    function initialize() {
    	$this->addon = $this->slplus->addon( 'Power' );
	    $this->meta = SLP_Power_Category_Stores_Taxonomy_Meta::get_instance();
        $this->add_wp_filters();
    }

    /**
     * Add WordPress filters for this taxonomy.
     * Custom Taxonomy Processing from the WP stores category page
     *
     * wp-includes/taxonomy/ in wp_update_term()
     *
     *  do_action("edited_$taxonomy", $term_id, $tt_id);
     *  do_action("create_$taxonomy", $term_id, $tt_id);
     *
     */
    function add_wp_filters() {
        add_action( 'edited_' . $this->taxonomy, array( $this, 'create_or_edited_stores'        ) , 10, 2 );
        add_action( 'create_' . $this->taxonomy, array( $this, 'create_or_edited_stores'        ) , 10, 2 );
        add_action( 'delete_' . $this->taxonomy, array( $this, 'delete_category_from_locations' ) , 10, 3 );
    }

    /**
     * Called after a store category is inserted or updated in the database.
     *
     * Creates an entry in the wp_options table with an option name
     * based on the category ID and a tagalong prefix like this:
     *
     * csl-slplus-TAGALONG-category_14
     *
     * @param int $term_id - the newly inserted category ID
     */
    function create_or_edited_stores( $term_id, $ttid ) {
        if ( $this->isset_category_attribute( $_POST ) ) {

            $TagalongData = $this->addon->category_attributes;
            foreach ( $TagalongData as $attribute => $default ) {
                if ( isset( $_POST[ $attribute ] ) ) {
                    $TagalongData[ $attribute ] = $_POST[ $attribute ];
                }
            }

            $this->slplus->WPOption_Manager->update_wp_option( $this->meta->get_option_name( $term_id ), $TagalongData );
        }
    }

    /**
     * Handle deleting terms IDS from locations when taxonomy deletes the category.
     *
     * @param   int $term_id
     * @param   int $tt_id
     * @param   mixed $deleted_term
     */
    function delete_category_from_locations( $term_id, $tt_id, $deleted_term ) {

        // Delete from the location -> term id mapping table (<prefix>_slp_tagalong )
        $this->addon->category_data->db->query(
            $this->addon->category_data->db->prepare(
                $this->addon->category_data->get_SQL( 'delete_category_by_termid' ),
                $term_id
            )
        );

        // Delete from the wp_options table.
        $this->meta->delete_term_meta( $term_id );
    }

    /**
     * Fetch an array of term IDs for all stores categories that are parents with children.
     *
     * $options  - do not pass 'fields' , 'parent', or 'childless' for base functionality.
     *
     * @params  array    $options  see WordPress get_terms()
     *
     * @returns int[]
     */
    public function get_parent_categories( $options = array() ) {
        $options = array_merge(
            $options,
            array(
                'hide_empty' => 0,      // Fetch empty terms.
                'fields'     => 'ids',  // Returns an array of integers.
                'parent'     => 0,      // Return all top-level terms.
                'childless'  => false,   // Return only items with children.
            )
        );

        return get_terms( $this->taxonomy, $options );
    }

    /**
     * Return the term name for a specific term.
     *
     * @param   int $term_id
     * @param   string $property the get_term property (default: 'name')
     *
     * @see https://developer.wordpress.org/reference/functions/get_term/
     *
     * @return  string
     */
    public function get_term( $term_id, $property = 'name' ) {
        $term = get_term( $term_id, $this->taxonomy );

        return $term->$property;
    }

    /**
     * Check if any of the special category attributes are present in the form post.
     * @return bool
     */
    function isset_category_attribute( $named_array ) {
        foreach ( $this->addon->category_attributes as $attribute => $default ) {
            if ( isset( $named_array[ $attribute ] ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set the Store taxonomy page columns.
     *
     * @param mixed[] $columns
     *
     * @return mixed
     */
    function set_columns( $columns ) {
        $new_columns =
            array(
            	'locations' => __( 'Locations'  , 'slp-power' ) ,
                'rank'      => __( 'Rank'       , 'slp-power' ),
                'icon'      => __( 'Icon'       , 'slp-power' ),
                'marker'    => __( 'Marker'     , 'slp-power' )
            );

        if ( ! empty( $columns[ 'posts' ] ) ) {
        	$columns[ 'posts' ] = __( 'Pages' , 'slp-power' );
        }

        return array_merge( $columns, $new_columns );
    }

    /**
     * Prepare the Store taxonomy custom column data.
     *
     * @param string $output
     * @param string $column_name
     * @param int $term_id
     *
     * @return string what we want to display as data for the column
     */
    function set_column_data( $output, $column_name = '', $term_id = null ) {

        switch ( $column_name ) {

	        case 'locations':
	        	/** @var  SLP_Power_Category_Manager $cat_man_do */
		        $cat_man_do = SLP_Power_Category_Manager::get_instance();
	            $output = $cat_man_do->get_locations_in_category( $term_id );
	        	break;

            case 'rank':
                $category = $this->addon->get_TermWithTagalongData( $term_id );
                $output   = $category['rank'];
                break;

            case 'icon':
                $category = $this->addon->get_TermWithTagalongData( $term_id );
                $output   = $this->addon->createstring_CategoryImageHTML( $category, 'medium-icon' );
                break;

            case 'marker':
                $category = $this->addon->get_TermWithTagalongData( $term_id );
                $output   = $this->addon->createstring_CategoryImageHTML( $category, 'map-marker' );
                break;

            default:
                break;

        }

        return $output;
    }

    /**
     * Tweak the edit categories messages;
     *
     * @param $messages
     *
     * @return mixed
     */
    function set_messages( $messages ) {
        foreach ( $messages['_item'] as $index => $text ) {
            $messages[ $this->taxonomy ][ $index ] =
                str_replace(
                    __( 'Item ', 'slp-power' ),
                    __( 'Store category ', 'slp-power' ),
                    $text
                );
        }

        return $messages;
    }
}


