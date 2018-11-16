<?php
defined( 'ABSPATH' ) || exit;

/**
 * System-wide Pages functionality for Power add on.
 *
 * @property        SLPPower             $addon                      The add on.
 */
class SLP_Power_Pages extends SLPlus_BaseClass_Object {
    public $addon;
    private $global;

    /**
     * Things we do when invoked.
     */
    public function initialize() {
    	$this->addon = $this->slplus->addon( 'Power' );
        $this->add_hooks_and_filters();
    }

    /**
     * Add WP and SLP hooks and filters.
     *
     * @uses    \SLP_Power_Pages::modify_storepage_attributes
     * @uses    \SLP_Power_Pages::do_when_slp_ready
     */
    private function add_hooks_and_filters() {
        add_filter( 'slp_storepage_attributes'  , array( $this , 'modify_storepage_attributes' )      ); // slp_storepages_attributes filter called on WP 'init' action.
        add_action( 'slp_init_complete'         , array( $this , 'do_when_slp_ready'           ) , 11 ); // 11 ensures the base slp_init has run for the addon
    }

    /**
     * Things to run after SLP has been loaded and is ready.
     */
    public function do_when_slp_ready() {
        if ( ! $this->addon->using_pages ) { return; }
	    $this->global = SLP_Power_Pages_Global::get_instance();
    }

    /**
     * Modify the default store pages attributes, essentially turning on/off store pages.
     *
     * @param array $attributes
     *
     * @return array
     */
    public function modify_storepage_attributes( $attributes ) {
        $this->addon->init_options();
        if ( ! $this->addon->using_pages ) { return $attributes; }

        return array_merge( $attributes, array(
            'hierarchical'  => true,                // Allow parent pages to be specified.  Must be true for functions like wp_list_pages
            'public'        => true,
            'rewrite' =>
                array(
                    'slug'       => $this->addon->options['permalink_starts_with'],
                    'with_front' => $this->slplus->is_CheckTrue( $this->addon->options['prepend_permalink_blog'] ),
                ),
        ) );
    }
}