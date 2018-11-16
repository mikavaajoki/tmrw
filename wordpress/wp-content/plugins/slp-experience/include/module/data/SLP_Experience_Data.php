<?php
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'SLP_Experience_Data' ) ) {
    /**
     * Class SLP_Experience_Data
     */
	class SLP_Experience_Data extends SLPlus_BaseClass_Object {

        /**
         * Things we do at the start.
         */
	    public function initialize() {
            add_filter( 'slp_extend_get_SQL' , array( $this , 'lookup_sql_by_slug' ) );
        }

        /**
         * More SLP data queries.
         *
         * @param $slug
         *
         * @return string
         */
		public function lookup_sql_by_slug( $slug ) {
			switch ( $slug ) {
				case 'select_city_state':
					return "SELECT CONCAT(TRIM(sl_city), ', ', TRIM(sl_state)) as city_state" . $this->slplus->database->from_slp_table;

                case 'select_location_zips':
                    return
                        'SELECT sl_zip' . $this->slplus->database->from_slp_table .
                        "WHERE sl_zip LIKE '%%%s%%' " .
                        'GROUP BY sl_zip ' .
                        'ORDER BY sl_zip ASC '
                        ;

				case 'group_by_city_state':
					return 'GROUP BY city_state ';

				case 'order_by_city_state':
					return 'ORDER BY city_state ASC';

				case 'where_valid_city':
					return $this->slplus->database->add_where_clause("(sl_city<>'') AND (sl_city IS NOT NULL)");

				default:
					return $slug;
			}
		}
	}

    global $slplus;
    if ( is_a( $slplus, 'SLPlus' ) ) {
        $slplus->add_object( new SLP_Experience_Data() );
    }
}