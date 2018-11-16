<?php
defined( 'ABSPATH' ) || exit;

/**
 * Stores taxonomy metadata interface.
 *
 * SLP stores category metadata in the options table in a serialized string.
 *
 * The option name is a set prefix (addon->category_meta_option_base) plus the term ID.
 *
 * WordPress 4.4 is working toward adding a proper taxonomy metadata table.
 * When that goes into production this system should move to that implementation.
 * @see https://core.trac.wordpress.org/attachment/ticket/10142
 *
 * @property        SLPlus $slplus
 * @property        string $taxonomy   The 'stores' taxonomy name.
 */
class SLP_Power_Category_Stores_Taxonomy_Meta extends SLPlus_BaseClass_Object {
    public $taxonomy = SLPlus::locationTaxonomy;

    /**
     * Get the option name that stores meta for the given term id.
     *
     * @param   int $term_id
     *
     * @return  string
     */
    public function get_option_name( $term_id ) {
    	$addon = $this->slplus->addon( 'Power' );
        return $addon->category_meta_option_base . $term_id;
    }

    /**
     * Remove ALL metadata from a term.
     *
     * @param $term_id
     * @param null $meta_key
     * @param string $meta_value
     */
    public function delete_term_meta( $term_id, $meta_key = null, $meta_value = '' ) {
        $this->slplus->WPOption_Manager->delete_wp_option( $this->get_option_name( $term_id ) );
    }
}