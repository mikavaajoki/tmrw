<?php
if ( !class_exists( 'SLP_Premier_Territory' ) ) {

    /**
     * Define a territory
     *
     * @package   StoreLocatorPlus\SLP_Premier\Admin\Locations
     * @author    Lance Cleveland <lance@storelocatorplus.com>
     * @copyright 2016 Charleston Software Associates, LLC
     *
     * Text Domain: slp-premier
     *
     * @property        SLP_Premier        $addon
     * @property        SLPlus_Location[] $points         An array of locations (lat/lng) that define the bounds of the territory.
     *
     */
    class SLP_Premier_Territory extends SLPlus_BaseClass_Object {
        public $points;

        /**
         * Add a new lat/lng point.
         *
         * @param string  $name    Name of the point.
         * @param string  $lat     Latitude coordinate.
         * @param string  $lng     Longitude coordinate.
         * @param boolean $private true if a temporary point
         */
        private function add_point( $name, $lat, $lng, $private = false ) {
            $slug = $this->create_point_slug( $name );
            if ( !isset( $this->points[ $slug ] ) ) {

                /**
                 * @var SLPlus_Location $location
                 */
                $location = new SLPlus_Location( array( 'store' => $name, 'latitude' => $lat, 'longitude' => $lng, 'private' => $private ) );

                if ( !$location->is_valid_lat() ) {
                    return;
                }
                if ( !$location->is_valid_lng() ) {
                    return;
                }

                $this->points[ $slug ] = $location;
            }
        }

        /**
         * Add the opposite corners to the rectangle polygon.
         *
         * SW/NE assumed, find NW, SE corners.
         */
        private function add_opposite_corners() {
            if (
                isset( $this->points[ 'ne' ] ) && isset( $this->points[ 'sw' ] ) &&
                ( !isset( $this->points[ 'nw' ] ) || !isset( $this->points[ 'se' ] ) )
            ) {
                $this->add_point( 'nw', $this->points[ 'ne' ]->latitude, $this->points[ 'sw' ]->longitude );
                $this->add_point( 'se', $this->points[ 'sw' ]->latitude, $this->points[ 'ne' ]->longitude );
            }
        }

        /**
         * Clear all points.
         */
        public function clear_points() {
            unset( $this->points );
        }

        /**
         * Create a valid key slug for the point.
         *
         * @param   string $point_label The plain text label for the point.
         *
         * @return string
         */
        private function create_point_slug( $point_label ) {
            return sanitize_key( $point_label );
        }

        /**
         * Delete private points.
         *
         * @param $name
         */
        public function delete_private_points( $name ) {
            if ( empty( $this->points ) ) {
                return;
            }
            foreach ( $this->points as $slug => $point ) {
                if ( $point->private ) {
                    unset( $this->points[ $slug ] );
                }
            }
        }

        /**
         * Extend the SQL query options.
         *
         * @param  string $sql_slug the SQL statement/snippet slug
         * @return string           the formal SQL command
         */
        public function extend_sql( $sql_slug ) {
            switch ( $sql_slug ) {
                case 'where_territory_is_not_set':
                    if ( ! $this->slplus->database->has_extended_data() ) { return; }
                    return $this->slplus->database->add_where_clause( '( (territory_bounds IS NULL)  OR (territory_distance_unit IS NULL) OR (territory_distance_unit = "") )' );

                case 'where_territory_is_set':
                    if ( $this->slplus->database->has_extended_data() ) {
                        return $this->slplus->database->add_where_clause( '( (territory_bounds IS NOT NULL)  AND (territory_distance_unit IS NOT NULL) AND (territory_distance_unit != "") )' );
                    } else {
                        return $this->slplus->database->add_where_clause( 'false' );
                    }

                default:
                    return $sql_slug;
            }
        }

        /**
         * Get a simplified list of points that includes only the name, lat, lng.
         *
         * @param boolean $all if true return all points, even private ones.
         *
         * @return array
         */
        public function get_points( $all = false ) {
            $simple_points = array();

            /**
             * @var SLPlus_Location $location
             */
            foreach ( $this->points as $location ) {
                if ( !$all && $location->private ) {
                    continue;
                }
                $simple_points[ $location->store ] = array(
                    'latitude'  => $location->latitude,
                    'longitude' => $location->longitude,
                );
            }

            return $simple_points;
        }

        /**
         * Set the bearing in degrees as a float.
         *
         * @param string $bearing 'ne'|'se'|'sw'|'nw'|string representation of a float/int.
         *
         * @return float|int
         */
        private function get_bearing_degrees( $bearing ) {
            switch ( $bearing ) {
                case 'n':
                    return 0;
                case 'ne':
                    return 45;
                case 'e':
                    return 90;
                case 'se':
                    return 135;
                case 's':
                    return 180;
                case 'sw':
                    return 225;
                case 'w':
                    return 270;
                case 'nw':
                    return 315;
                default:
                    return floatval( $bearing );
            }
        }

        /**
         * Set the bearing in degrees as a float.
         *
         * @param string $bearing 'ne'|'se'|'sw'|'nw'|string representation of a float/int.
         * @param array  $data    The extended data being added.
         *
         * @return float|int
         */
        private function get_bearing_distance( $bearing, $data ) {
            switch ( $bearing ) {
                case 's':
                    return $data[ 'territory_distance_south' ];
                case 'e':
                    return $data[ 'territory_distance_east' ];
                case 'w':
                    return $data[ 'territory_distance_west' ];


                // Default to distance due north.
                case 'n':
                default:
                    return $data[ 'territory_distance_north' ];
            }
        }

        /**
         * Return true if a given lat/long is inside the current location territory.
         *
         * Assumes the points array is already setup.
         *
         * Uses the crossing number algorithm or even-odd-rule algorithm.
         * This is faster than using the circular algorithms such as the winding number algorithm.
         * Circular algorithms use trigonometric functions such as sin, cos, etc. that are computationally expensive.
         *
         * @see  https://en.wikipedia.org/wiki/Even%E2%80%93odd_rule
         *
         * @param string $latitude
         * @param string $longitude
         *
         * @return bool
         */
        private function is_lat_long_in_territory( $latitude, $longitude ) {
            if ( empty( $this->points ) ) {
                return;
            }
            $this->add_opposite_corners();
            if ( count( $this->points ) < 3 ) {
                return false;
            }

            $latitude = floatval( $latitude );
            $longitude = floatval( $longitude );

            $keys = array( 'ne', 'se', 'sw', 'nw' );     // The points need to be in an order that draws a proper polygon.

            $c = 0;
            $p1 = $this->points[ 'ne' ];
            $n = count( $this->points );

            for ( $i = 1; $i <= $n; $i++ ) {
                $p2 = $this->points[ $keys[ $i % $n ] ];

                if (
                    $longitude > min( $p1->longitude, $p2->longitude ) &&
                    $longitude <= max( $p1->longitude, $p2->longitude ) &&
                    $latitude <= max( $p1->latitude, $p2->latitude ) &&
                    $p1->longitude != $p2->longitude
                ) {
                    $xinters = ( $longitude - $p1->longitude ) * ( $p2->latitude - $p1->latitude ) / ( $p2->longitude - $p1->longitude ) + $p1->latitude;

                    if ( $p1->latitude == $p2->latitude || $latitude <= $xinters ) {
                        $c++;
                    }
                }
                $p1 = $p2;
            }

            // if the number of edges we passed through is even, then it's not in the poly.
            return $c % 2 != 0;
        }

        /**
         * Setup the territory points from the current location.
         */
        public function load_points_from_current_location() {
            if ( !isset( $this->slplus->currentLocation->exdata ) ) {
                return;
            }
            if ( !isset( $this->slplus->currentLocation->exdata[ 'territory_bounds' ] ) ) {
                return;
            }
            $this->load_points_from_bounds( $this->slplus->currentLocation->exdata[ 'territory_bounds' ] );
        }

        /**
         * Setup territory points from a given serialized array of bounds.
         *
         * @param string $bounds serialized points (name, lat/lng per $points property here).
         */
        private function load_points_from_bounds( $bounds ) {
            $points_array = maybe_unserialize( $bounds );
            if ( empty( $points_array ) ) {
                return;
            }
            foreach ( $points_array as $slug => $coordinate ) {
                $this->add_point( $slug, $coordinate[ 'latitude' ], $coordinate[ 'longitude' ] );
            }
        }

        /**
         * Change the having clause to compare distance to the territory radius.
         *
         * @param string[] $having_clause prior having clause elements
         * @param string   $query_slug    the query slug
         *
         * @return string
         */
        public function modify_having_clause( $having_clause, $query_slug ) {
            if ( $query_slug !== 'territory_serves_address' ) {
                return $having_clause;
            }
            return array( '( sl_distance < territory_radius )' );
        }

        /**
         * Modify the selectall_with_distance SQL clause from SLP.
         *
         * @param string $sql
         *
         * @return string       The modified select all statement.
         */
        public function modify_select_all_with_distance( $sql ) {
            $as_distance_clause = ' AS sl_distance ';
            $radius_clause = ' SQRT( POW( GREATEST( territory_distance_north, territory_distance_south) ,2 )  + POW( GREATEST( territory_distance_east, territory_distance_west) , 2 ) ) as territory_radius ';
            return preg_replace( "/{$as_distance_clause}/" , $as_distance_clause . ',' . $radius_clause , $sql );
        }

        /**
         * Remove results that are not in the territory.
         *
         * @param   array $results
         *
         * @return  array $results
         */
        public function remove_results_not_in_territory( $results ) {
            $filtered_results = array();
            $lat = $this->slplus->AddOns->instances['slp-premier']->ajax->query_params['lat'];
            $lng = $this->slplus->AddOns->instances['slp-premier']->ajax->query_params['lng'];
            foreach ( $results as $result ) {
                $this->load_points_from_bounds( $result[ 'territory_bounds' ] );
                if ( $this->is_lat_long_in_territory( $lat , $lng ) ) {
                    $filtered_results[] = $result;
                }
                $this->clear_points();
            }

            return $filtered_results;
        }

        /**
         * Set a boundary point to define the territory polygon.  Moves the center to ensure a right-angle box.
         *
         * @param string $bearing Either a cardinal direction 'nw', 'ne' , 'se' , 'sw' or a bearing in degrees (0 to 360).
         * @param array  $data    The extended data being added.
         */
        public function set_boundary_point( $bearing, $data ) {
            switch ( $bearing ) {
                case 'ne':
                    $this->set_point( 'n', $data, $this->slplus->currentLocation->latitude, $this->slplus->currentLocation->longitude, true );
                    $this->set_point( 'e', $data, $this->slplus->currentLocation->latitude, $this->slplus->currentLocation->longitude, true );
                    $this->add_point( $bearing, $this->points[ 'n' ]->latitude, $this->points[ 'e' ]->longitude );
                    break;
                case 'sw':
                    $this->set_point( 's', $data, $this->slplus->currentLocation->latitude, $this->slplus->currentLocation->longitude, true );
                    $this->set_point( 'w', $data, $this->slplus->currentLocation->latitude, $this->slplus->currentLocation->longitude, true );
                    $this->add_point( $bearing, $this->points[ 's' ]->latitude, $this->points[ 'w' ]->longitude );
                    break;
            }
        }

        /**
         * Clear the custom AJAX execute_location_query settings.
         *
         * @param  string $query_slug
         */
        public function clear_custom_ajax_sql_settings( $query_slug ) {
            if ( $query_slug === 'territory_serves_address' ) {
                remove_filter( 'slp_location_having_filters_for_AJAX', array( $this, 'modify_having_clause' ), 999 );
                remove_filter( 'slp_extend_get_SQL_selectall', array( $this, 'modify_select_all_with_distance' ), 999 );
                remove_filter( 'slp_ajaxsql_results' , array( $this , 'remove_results_not_in_territory' ) ,10 );
                remove_filter( 'slp_ajaxsql_queryparams', array( $this , 'remove_distance_param' ) , 10 );
            }
        }

        /**
         * Remove the distance param when having clause is modified.
         *
         * @param $default_query_parameters
         * @param $query_slug
         * \
         * @return mixed
         */
        public function remove_distance_param( $default_query_parameters , $query_slug )  {
            if ( $query_slug !== 'territory_serves_address' ) {
                return $default_query_parameters;
            }

            $revised_parameters = $default_query_parameters;
            $limit_param = array_pop( $revised_parameters );
            $distance_param = array_pop( $revised_parameters );
            $revised_parameters[] = $limit_param;

            return $revised_parameters;
        }

        /**
         * Set the custom AJAX execute_location_query settings.
         *
         * @param  string $query_slug
         */
        public function set_custom_ajax_sql_settings( $query_slug ) {
            add_filter( 'slp_extend_get_SQL', array( $this, 'extend_sql' ) );

            if ( $query_slug === 'territory_serves_address' ) {
                add_filter( 'slp_location_having_filters_for_AJAX', array( $this, 'modify_having_clause' ), 999, 2 );
                add_filter( 'slp_extend_get_SQL_selectall', array( $this, 'modify_select_all_with_distance' ), 999 );
                add_filter( 'slp_ajaxsql_results' , array( $this , 'remove_results_not_in_territory' ) ,10 );
                add_filter( 'slp_ajaxsql_queryparams', array( $this , 'remove_distance_param' ) , 10 , 2 );
            }
        }

        /**
         * Add the address within territory query to the stack of AJAX load/search queries on locations.
         *
         * @param  array $queries the standard query
         *
         * @return array           the standard query PLUS the address within territory query
         */
        public function set_location_search_queries( $queries ) {
            add_action( 'slp_ajax_execute_location_query_start', array( $this, 'set_custom_ajax_sql_settings' ) );
            add_action( 'slp_ajax_execute_location_query_end', array( $this, 'clear_custom_ajax_sql_settings' ) );

            // Add where_territory_is_not_set to standard select where clause
            //
	        if ( isset( $queries['standard_location_search']) ) {
		        $queries['standard_location_search'][] = 'where_territory_is_not_set';
	        }
	        if ( isset( $queries['standard_location_load']) ) {
		        $queries['standard_location_load'][] = 'where_territory_is_not_set';
	        }

            // Build the territory serves address query list
            // If there is no extended data there are no territories.
            //
            if ( $this->slplus->database->has_extended_data() ) {
                $queries[ 'territory_serves_address' ] = array( 'selectall_with_distance', 'where_default_validlatlong', 'where_territory_is_set' );
            }

            return $queries;
        }

        /**
         * Add a marker property to note whether a location services the user's location.
         *
         * @param   array $marker
         * @return array $marker
         */
        public function set_marker_in_territory_property( $marker ) {
            $not_in_territory = array( 'in_territory' => '0' , 'in_territory_class' => '' );

            if ( ! isset ( $marker[ 'data' ] ) ) {
                $marker[ 'data' ] = $not_in_territory;
                return $marker;
            }

            $data = $marker[ 'data' ];
            if ( !isset ( $data[ 'territory_distance_unit' ] ) ) {
                $marker[ 'data' ] = array_merge( $marker[ 'data' ], $not_in_territory );
                return $marker;
            }

            if ( empty ( $data[ 'territory_distance_unit' ] ) ) {
                $marker[ 'data' ] = array_merge( $marker[ 'data' ], $not_in_territory );
                return $marker;
            }

            if ( !isset ( $data[ 'territory_bounds' ] ) ) {
                $marker[ 'data' ] = array_merge( $marker[ 'data' ], $not_in_territory );
                return $marker;
            }

            if ( empty ( $data[ 'territory_bounds' ] ) ) {
                $marker[ 'data' ] = array_merge( $marker[ 'data' ], $not_in_territory );
                return $marker;
            }

            if ( !isset ( $this->slplus->AJAX->query_params ) ) {
                $marker[ 'data' ] = array_merge( $marker[ 'data' ], $not_in_territory );
                return $marker;
            }

            if ( !isset ( $this->slplus->AJAX->query_params[ 'lat' ] ) ) {
                $marker[ 'data' ] = array_merge( $marker[ 'data' ], $not_in_territory );
                return $marker;
            }

            if ( !isset ( $this->slplus->AJAX->query_params[ 'lng' ] ) ) {
                $marker[ 'data' ] = array_merge( $marker[ 'data' ], $not_in_territory );
                return $marker;
            }

            $this->load_points_from_bounds( $data[ 'territory_bounds' ] );
            $marker[ 'data' ][ 'in_territory' ] = $this->is_lat_long_in_territory( $this->slplus->AJAX->query_params[ 'lat' ], $this->slplus->AJAX->query_params[ 'lng' ] ) ? '1' : '0';
            $marker[ 'data' ][ 'in_territory_class' ] = $this->slplus->is_CheckTrue( $marker[ 'data' ][ 'in_territory' ] ) ? 'in_territory' : '';

            return $marker;
        }

        /**
         * Set a boundary point to define the territory polygon by calcualting the hypotenuse creatin an irregular box.
         *
         * This ensures the location is in the center of the territory but the polygon prescribed is an AVERAGE distance from the location.
         *
         * @param string  $bearing       Either a cardinal direction or a bearing in degrees (0 to 360).
         * @param array   $data          The extended data being added.
         * @param string  $starting_lat  The starting point latitude.
         * @param string  $starting_long The starting point longitude.
         * @param boolean $private       Is this a private point?
         */
        public function set_point( $bearing, $data, $starting_lat, $starting_long, $private ) {
            $rad_per_degree = ( M_PI / 180 );

            $bearing_radians = $this->get_bearing_degrees( $bearing ) * $rad_per_degree;
            $current_lat_radians = floatval( $starting_lat ) * $rad_per_degree;
            $current_lng_radians = floatval( $starting_long ) * $rad_per_degree;

            $distance = $this->get_bearing_distance( $bearing, $data );

            $earth_radius = ( $data[ 'territory_distance_unit' ] === 'km' ) ? SLPlus::earth_radius_km : SLPlus::earth_radius_mi;

            $distance_per_radian = $distance / $earth_radius;


            $new_latitude =
                asin(
                    sin( $current_lat_radians ) * cos( $distance_per_radian ) +
                    ( cos( $current_lat_radians ) * sin( $distance_per_radian ) * cos( $bearing_radians ) )
                );


            $new_longitude =
                $current_lng_radians +
                atan2(
                    sin( $bearing_radians ) * sin( $distance_per_radian ) * cos( $current_lat_radians ),
                    cos( $distance_per_radian ) - sin( $current_lat_radians ) * sin( $new_latitude )
                );

            $new_longitude = $new_longitude * ( 180 / M_PI );

            $new_latitude = $new_latitude * ( 180 / M_PI );

            $this->add_point( $bearing, $new_latitude, $new_longitude, $private );
        }

        /**
         * Set new territory bounds based on the incoming data.
         *
         * @param   array $data The new data to be written.
         * @return  array $data The data with the new territory bounds.
         */
        public function set_territory_bounds( $data ) {

            // No distance unit = set bounds to nothing.
            //
            if ( empty( $data[ 'territory_distance_unit' ] ) ) {
                $data[ 'territory_bounds' ] = NULL;

                // Distance unit set to km or miles , set the corners.
                //
            } else {

                // Need at least 2 vectors that are 90-degrees part.
                if (
                    ( empty( $data[ 'territory_distance_north' ] ) && empty( $data[ 'territory_distance_south' ] ) ) ||
                    ( empty( $data[ 'territory_distance_east' ] ) && empty( $data[ 'territory_distance_west' ] ) )
                ) {
                    $data[ 'territory_bounds' ] = NULL;

                    // We have at least 2 90-degree vectors, figure out the math stuff...
                    //
                } else {
                    $this->set_boundary_point( 'ne', $data );
                    $this->set_boundary_point( 'sw', $data );

                    $data[ 'territory_bounds' ] = maybe_serialize( $this->get_points() );
                }
            }

            return $data;
        }

        /**
         * Add where clause to SQL when selecting locations.  Filter out locations without territories.
         *
         * @param string $where_clause
         *
         * @return string
         */
        public function sql_filter_where_territory_is_set( $where_clause ) {
            if ( !isset( $this->slplus->AJAX ) ) {
                return $where_clause;
            }

            $where_clause = $this->slplus->database->extend_Where( $where_clause, 'territory_distance_unit IS NOT NULL' );
            $where_clause = $this->slplus->database->extend_Where( $where_clause, 'territory_distance_unit != ""' );
            $where_clause = $this->slplus->database->extend_Where( $where_clause, 'territory_bounds IS NOT NULL' );

            return $where_clause;
        }
    }
}