<?php
defined( 'ABSPATH' ) || exit;
if ( ! class_exists('SLP_Power_Category_Data') ) {


    /**
     * The data interface helper.
     *
     * @property        SLPPower    $addon
     * @property        wpdb        $db
     * @property        string[]    $plugintable    Properties of the plugin data table.
     * @property        string      $collate        The collate modifier for create table commands.
     * @property        string      $where_clause   The SQL where clause.
     *
     * $plugintable
     * 'name'   = table name
     * 'fields' = key/value pair key = field name, value = field format
     */
    class SLP_Power_Category_Data extends SLPlus_BaseClass_Object {
        public $addon;
        public $db;
        public $plugintable;
        public $collate;
        public $where_clause = '';

        /**
         * Startup things.
         */
        public function initialize() {
            $this->addon = $this->slplus->AddOns->instances[ 'slp-power' ];
            $this->db                    = $this->slplus->db;
            $this->plugintable['name']   = $this->db->prefix . 'slp_tagalong';
            $this->plugintable['fields'] = array(
                'sl_id'   => '%d',
                'term_id' => '%d'
            );
        }

        /**
         * Extend the current where clause to also filter by the categories selected.
         *
         * @param   string  $where              existing where clause
         * @param   string  $category_list      comma separated list of categories
         * @return  string
         */
        public function add_where_location_has_category( $where , $category_list ) {
            if ( empty( $category_list ) ) {
                return $where;
            }
            return $this->slplus->database->extend_Where( $where , sprintf ( $this->get_SQL( 'where_location_has_category' ) , $category_list ) );
        }

        /**
         * Return a record as an array based on a given SQL select statement keys and params list.
         *
         * @param string|string[] $commandList
         * @param mixed[] $params
         * @param int $offset
         *
         * @return array
         */
        function get_Record( $commandList, $params = array(), $offset = 0 ) {
            return
                $this->db->get_row(
                    $this->db->prepare(
                        $this->get_SQL( $commandList ),
                        $params
                    ),
                    ARRAY_A,
                    $offset
                );
        }

        /**
         * Set the database character set
         */
        function set_DB_charset() {
            $collate = '';
            if ( $this->db->has_cap( 'collation' ) ) {
                if ( ! empty( $this->db->charset ) ) {
                    $collate .= "DEFAULT CHARACTER SET {$this->db->charset}";
                }
                if ( ! empty( $this->db->collate ) ) {
                    $collate .= " COLLATE {$this->db->collate}";
                }
            }
            $this->collate = $collate;
        }

        /**
         * Get an SQL statement for this database.
         *
         * @param string|string[] $commandList
         *
         * @return string
         */
        function get_SQL( $commandList ) {
            $sqlStatement = '';
            if ( ! is_array( $commandList ) ) {
                $commandList = array( $commandList );
            }
            foreach ( $commandList as $command ) {
                switch ( $command ) {
                    case 'create_tagalong_helper':
                        $sqlStatement .=
                            "CREATE TABLE {$this->plugintable['name']} (
                        sl_id mediumint(8) unsigned NOT NULL,
                        term_id bigint(20) unsigned NOT NULL,
                        KEY sl_id (sl_id),
                        KEY term_id (term_id),
                        KEY sl_term_id (sl_id,term_id)
                        ) {$this->collate}";
                        break;

	                case 'delete_entire_category_map':
		                $sqlStatement .= 'DELETE FROM ' . $this->plugintable['name'];
		                break;

                    case 'delete_category_by_id':
                        $sqlStatement .=
                            'DELETE FROM ' . $this->plugintable['name'] .
                            ' WHERE sl_id = %d ';
                        break;

                    case 'delete_category_by_termid':
                        $sqlStatement .=
                            'DELETE FROM ' . $this->plugintable['name'] .
                            ' WHERE term_id = %d ';
                        break;

	                case 'select_count_for_termid':
		                $sqlStatement .= "SELECT count(*) as location_count FROM {$this->plugintable['name']} WHERE term_id = %d ";
						break;

                    case 'select_categorycount_for_location':
                        $sqlStatement .=
                            'SELECT count(sl_id) FROM ' . $this->plugintable['name'] . ' ' .
                            'WHERE ' . $this->slplus->database->info['table'] . '.sl_id=' . $this->plugintable['name'] . '.sl_id';
                        break;

                    case 'tagalong_selectall':
                        $sqlStatement .= 'SELECT * FROM ' . $this->plugintable['name'];
                        break;

                    case 'select_categories_for_location' :
                        $sqlStatement .=
                            'SELECT term_id FROM ' . $this->plugintable['name'] .
                            ' WHERE sl_id = %d';
                        break;

                    case 'select_by_keyandcat':
                        $sqlStatement .=
                            'SELECT * FROM ' . $this->plugintable['name'] .
                            ' WHERE sl_id = %d AND term_id = %d';
                        break;

                    case 'select_locations_with_categories':
                        $sqlStatement .=
                            'SELECT sl_id FROM ' . $this->plugintable['name'];
                        break;

                    case 'select_where_optionvalue_has_cats':
                        $sqlStatement .=
                            'SELECT sl_id, sl_option_value FROM ' . $this->slplus->database->info['table'] . ' ' .
                            "WHERE sl_option_value LIKE '%store_categories%'";
                        break;

                    case 'whereslid':
                        $sqlStatement .= ' WHERE sl_id = %d ';
                        break;

                    // Where location has at least one of the given categories
                    //
                    // Assumes a comma-separated list of category IDS is passed
                    //
                    // Location is returned if it is in ANY category (OR)
                    //
                    case 'where_location_has_category':
                        $sqlStatement .=
                            ' sl_id IN (' .
                            'SELECT sl_id FROM ' . $this->plugintable['name'] . ' WHERE term_id IN ( %s )' .
                            ') ';
                        break;

                    default:
                        return $command;
                        break;
                }
            }

            return $sqlStatement;
        }

        /**
         * Add a record to the plugin info table if it does not exist.
         *
         * @param string $location_id
         * @param string $category_id
         *
         * @return null
         */
        function add_RecordIfNeeded( $location_id = null, $category_id = null ) {
            if ( ! isset( $this->addon ) ) {
                return;
            }

            if ( ! $this->slplus->currentLocation->isvalid_ID() ) {
                return;
            }

            // Check if record exists
            //
            $matching_record = $this->get_Record( 'select_by_keyandcat', array( $location_id, $category_id ) );

            // Add record if it does not exist
            //
            if (
                ( $matching_record === null ) ||
                ( $matching_record['sl_id'] != $location_id )
            ) {
                $this->db->insert( $this->plugintable['name'],
                    array(
                        'sl_id'   => $location_id,
                        'term_id' => $category_id
                    )
                );
            }

            return;
        }
    }

    global $slplus;
    if (is_a($slplus, 'SLPlus')) {
        $slplus->add_object(new SLP_Power_Category_Data());
    }
}