<?php
defined( 'ABSPATH' ) || exit;

/**
 * Tagalong changes to the WP edit tags interface.
 *
 * @property        SLPPower     $addon
 */
class SLPPower_Admin_EditTags extends SLPlus_BaseClass_Object {
    public $addon;

    /**
     * Add the WordPress taxonomy editor filters.
     */
    public function add_wp_filters() {
        add_filter( 'term_updated_messages'                                       , array( $this->addon->stores_taxonomy , 'set_messages'      )            );
        add_filter( 'manage_edit-' . SLPlus::locationTaxonomy  . '_columns'       , array( $this->addon->stores_taxonomy , 'set_columns'       )            );
        add_filter( 'manage_'      . SLPlus::locationTaxonomy  . '_custom_column' , array( $this->addon->stores_taxonomy , 'set_column_data'   ) , 20 , 3   );
    }
}

