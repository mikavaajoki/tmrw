<?php
if ( ! class_exists( 'SLP_Premier_Location_Add' ) ) {
    /**
     * The things that modify the Admin / Locations / Add UX
     *
     * @property SLP_Premier                 $addon
     * @property SLP_Premier_Admin_Locations $location
     */
    class SLP_Premier_Location_Add  extends SLPlus_BaseClass_Object {
        public $addon;
        public $location;

        /**
         * Add the interface element for showing the territory editing on add/edit locations.
         *
         * @param   SLP_Settings $settings           SLP Settings Interface reference SLPlus->ManageLocations->settings
         * @param   array[]      $group_params       The metadata needed to build a settings group.
         * @param   array[]      $data_field         The current extended data field meta.
         */
        public function add_territories_interface( $settings , $group_params, $data_field  ) {
            if ( $data_field->slug !== 'territory_bounds' ) { return; }

            if (
                $this->slplus->currentLocation->is_valid_lat() &&
                $this->slplus->currentLocation->is_valid_lng()
            )    {
                $html =
                    '<p class="plaintext">'.
                    "<span id='latitude_location' class='half_width'>{$this->slplus->currentLocation->latitude}</span> , " .
                    "<span id='longitude_location' class='half_width'>{$this->slplus->currentLocation->longitude}</span>"  .
                    '</p>'
                ;
                $settings->add_ItemToGroup(array(
                    'group_params'  => $group_params,
                    'type'          => 'custom' ,
                    'show_label'    => true,
                    'label'         => __( 'Location Coordinates' , 'slp-premier' ),
                    'custom'        => $html
                ));
            }

            $this->location->territory->load_points_from_current_location();

            if ( ! empty( $this->location->territory->points ) ) {
                foreach ( $this->location->territory->points as $point ) {
                    if ( ( $point->store !== 'sw' ) && ( $point->store !== 'ne' ) ) { continue; }
                    $html =
                        '<p class="plaintext">' .
                        "<span id='latitude_{$point->store}' class='half_width'>{$point->latitude}</span> , " .
                        "<span id='longitude_{$point->store}' class='half_width'>{$point->longitude}</span>" .
                        '</p>';
                    $settings->add_ItemToGroup( array(
                        'group_params' => $group_params,
                        'type'         => 'custom',
                        'show_label'   => true,
                        'label'        => sprintf( __( 'Territory %s Coordinate', 'slp-premier' ), $point->store ),
                        'custom'       => $html
                    ) );
                }
            }

        }
    }

}
