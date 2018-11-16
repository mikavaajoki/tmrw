<?php


if ( ! class_exists( 'SLPJanitor_Admin_Functions' ) ) {
    /**
     * Admin functions.
     *
     * @package StoreLocatorPlus\SLPJanitor\Admin\Functions
     * @author Lance Cleveland <lance@storelocatorplus.com>
     * @copyright 2016 Charleston Software Associates, LLC
     *
     * @property        SLPJanitor      $addon
     *
     */
    class SLPJanitor_Admin_Functions  extends SLPlus_BaseClass_Object {
        public $addon;

        /**
         *  Clear ALL the SLP locations.
         */
        function clear_Locations() {
            $clear_messages = array();

            $count = 0;
            $data = $this->slplus->database->get_Record(array('selectslid'));
            while (( $data['sl_id'] > 0)) {
                $this->slplus->currentLocation->set_PropertiesViaDB($data['sl_id']);
                $this->slplus->currentLocation->delete();

                $count++;
                $data = $this->slplus->database->get_Record(array('selectslid'));
            }

            if ($count < 1) {
                $clear_messages[] = __('No locations were found.', 'slp-janitor');
            } else {
                $clear_messages[] = $count . __(' locations has been deleted.', 'slp-janitor');
            }

            return $clear_messages;
        }

        /**
         * Clear the plugin styles settings.
         *
         * @return array
         */
        function clear_plugin_styles() {
            $plugin_style_options = array(
                'csl-slplus-theme_array',
                'csl-slplus-theme_details',
                'csl-slplus-theme_lastupdated',
            );
            foreach ($plugin_style_options as $option_name) {
                $this->addon->admin->reset_single_setting( $option_name );
            }

            $plugin_style_serialized = array(
                'csl-slplus-options_nojs' => 'themes_last_updated'
            );
            foreach ($plugin_style_serialized as $option_name => $setting) {
                $this->addon->admin->reset_serial_Settings( $option_name , $setting );
            }

            return array();
        }

        /**
         * Clear out all of the metadata records in the slp_extendo_meta table, also clear out all of the extended location data.
         */
        function delete_Extend_datas() {
            global $wpdb;
            $meta_table_name = $wpdb->prefix . 'slp_extendo_meta';
            $data_table_name = $wpdb->prefix . 'slp_extendo';
            $del_messages = array();

            if ($wpdb->get_var("SHOW TABLES LIKE '$meta_table_name'") == $meta_table_name) {
                $wpdb->query("DELETE FROM $meta_table_name");
                $del_messages[] = __("Delete records of table $meta_table_name.", 'slp-janitor');
            }

            if ($wpdb->get_var("SHOW TABLES LIKE '$data_table_name'") == $data_table_name) {
                $wpdb->query("DROP TABLE $data_table_name");
                $del_messages[] = __("Drop table $data_table_name.", 'slp-janitor');
            }

            $slplus_options = get_option(SLPLUS_PREFIX . '-options_nojs', array());
            $slplus_options['next_field_id'] = 0;
            $slplus_options['next_field_ported'] = '';
            update_option(SLPLUS_PREFIX . '-options_nojs', $slplus_options);
            $del_messages[] = __("Reset extended data options.", 'slp-janitor');

            return $del_messages;
        }

        /**
         * Delete the tagalong helper data.
         */
        function delete_Tagalong_helpers() {
            $del_messages = array();
            $table_name = $this->slplus->database->db->prefix . 'slp_tagalong';
            $del_messages[] = $this->slplus->database->db->delete($table_name, array('1' => '1'));
            $del_messages[] = __('Tagalong helper data has been cleared.', 'slp-janitor');
            return $del_messages;
        }

        /**
         * Drop an index only if it exists.
         *
         * @global object $wpdb
         * @param string $idxName name of index to drop
         *
         * TODO: Need a hook from the UI to manually run index cleanup.
         */
        function drop_index($idxName) {
            global $wpdb;
            if ($wpdb->get_var('SELECT count(*) FROM information_schema.statistics '.
                               "WHERE table_name='".$this->slplus->database->info['table']."' " .
                               "AND index_name='{$idxName}'" ) > 0) {
                $wpdb->query("DROP INDEX {$idxName} ON " . $this->slplus->database->info['table']);
            }
        }

        /**
         * Drop Locations
         */
        function drop_locations() {
            $this->slplus->createobject_Activation();
            global $wpdb;
            $table_name = $wpdb->prefix . "store_locator";
            $extended_table_name = $wpdb->prefix . 'slp_extendo_meta';

            // Drop Tables
            //
            $this->slplus->db->query( "DROP TABLE $table_name" );
            $this->slplus->db->query( "DROP TABLE $extended_table_name" );

            // Install Tables
            //
            $this->slplus->Activation->install_main_table();
            $this->slplus->Activation->install_ExtendedDataTables();
            return array();
        }

        /**
         * Fix the descriptions fields.
         */
        function fix_Descriptions() {
            $fix_messages = array();

            $offset = 0;
            $data = $this->slplus->database->get_Record(array('selectall'));
            while (( $data['sl_id'] > 0)) {
                $new_sl_description = html_entity_decode($data['sl_description']);
                if ($new_sl_description !== $data['sl_description']) {
                    $data['sl_description'] = $new_sl_description;
                    $this->slplus->currentLocation->set_PropertiesViaArray($data);
                    $this->slplus->currentLocation->MakePersistent();
                    $fix_messages[] = sprintf(' Fixed location # %d, %s', $data['sl_id'], $data['sl_store']);
                }
                $offset++;
                $data = $this->slplus->database->get_Record(array('selectall'), array(), $offset);
            }

            if (count($fix_messages) < 1) {
                $fix_messages[] = __('No locations were found with encoded HTML strings in their description.', 'slp-janitor');
            } else {
                array_unshift($fix_messages, __('The following locations had HTML encoded strings stored in the database:', 'slp-janitor'));
            }

            return $fix_messages;
        }

        /**
         * Rebuild the extended data table.
         */
        function rebuild_Extended_Tables() {
            if ($this->slplus->database->is_Extended()) {
                $this->slplus->database->extension->update_data_table(array('mode' => 'force'));
            }
        }

        /**
         * Rebuild the tagalong helper data.
         */
        function rebuild_Tagalong_helpers() {
            $table_name = $this->slplus->database->db->prefix . 'slp_tagalong';

            $offset = 0;
            $locations_with_categories = 0;
            $categories_assigned = 0;
            while (( $location_record = $this->slplus->database->get_Record('selectall', array(), $offset++) ) !== null) {
                if ($location_record['sl_linked_postid'] > 0) {
                    $post_categories = wp_get_object_terms($location_record['sl_linked_postid'], SLPLUS::locationTaxonomy, array('fields' => 'ids'));
                    $locations_with_categories++;
                    foreach ($post_categories as $category_id) {
                        $categories_assigned++;
                        $this->slplus->database->db->insert(
                            $table_name, array(
                            'sl_id' => $location_record['sl_id'],
                            'term_id' => $category_id
                        ), array(
                                '%d',
                                '%d'
                            )
                        );
                    }
                }
            }
            $messages[] = sprintf(
                __("%s category assignments have been made to %s locations.", 'slp-janitor'), $categories_assigned, $locations_with_categories
            );
            return $messages;
        }

        /**
         * Reset the SLP settings.
         */
        function reset_Settings() {
            $resetInfo = array();

            //FILTER: slp_janitor_deleteoptions
            $slpOptions = apply_filters('slp_janitor_deleteoptions', $this->addon->admin->optionList);
            foreach ($slpOptions as $optionName) {
                $this->addon->admin->reset_single_setting( $optionName );
            }
            return $resetInfo;
        }
    }
}