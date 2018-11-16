<?php
defined( 'ABSPATH' ) || exit;

/**
 * The things that modify the Admin / Locations tab.
 */
class SLP_Experience_Admin_Locations extends SLPlus_BaseClass_Object {

	/**
	 * feature a location
	 *
	 * @param   string                          $action = add or remove
     * @param   SLP_Admin_Locations_Actions     $processor
	 */
	public function feature_locations( $action , $processor) {
        $id = $processor->get_next_location();
        while ( ! is_null( $id ) ) {
            $this->slplus->database->extension->update_data( $id, array('featured' => ( $action === 'add' ) ? '1' : '0' ) );
            $id = $processor->get_next_location();
        }
	}
}

global $slplus;
if ( is_a( $slplus, 'SLPlus' ) ) {
	$slplus->add_object( new SLP_Experience_Admin_Locations() );
}
