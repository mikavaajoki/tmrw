<?php
defined('ABSPATH') || exit;

/**
 * The data interface helper.
 */
class SLP_Power_Category_Walker_Legend extends Walker_Category {

    /**
     * @see Walker_Category::start_el()
     *
     * @param string    $output     Passed by reference. Used to append additional content.
     * @param object    $category   Category data object.
     * @param int       $depth      Depth of category in reference to parents.
     * @param array     $args
     * @param int    $id
     */
    function start_el( &$output, $category, $depth = 0, $args = array(), $id = 0 ) {
        if ( intval( $category->term_id ) <= 0 ) {
	        return;
        }
	    global $slplus_plugin;
	    $this->slplus=$slplus_plugin;
	    $this->addon = $this->slplus->addon( 'Power' );
	    $output .=
            '<span class="tagalong_legend_icon" id="legend_' . $category->slug . '">' .
            $this->addon->create_LocationIcons(
                array( $this->create_CategoryDetails( $category->term_id ) ),
                array( 'show_label' => $this->slplus->SmartOptions->show_legend_text->is_true )
            ) .
            '</span>';

        return;
    }

    /**
     * Create extended category details array.
     *
     * @param int $category
     * @return mixed
     */
    private function create_CategoryDetails( $category ) {
        $categoryDetails = get_term( $category, 'stores', ARRAY_A );
        $categoryDetails = array_merge( $categoryDetails, $this->addon->get_TermWithTagalongData( $category ) );

        return $categoryDetails;
    }
}