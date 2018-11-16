<?php
defined( 'ABSPATH'     ) || exit;
if (!class_exists( 'SLP_Power_Admin_Location_Filters' )) {

    /**
     * Admin Filters for Power
     *
     * text-domain: slp-power
     *
     * @property    SLPPower    $addon
     * @property    boolean     $reset          If true, reset the filters.
     * @property    boolean     $filter_active  True if a filter is active.
     */
    class SLP_Power_Admin_Location_Filters extends SLPlus_BaseClass_Object {
        public  $addon;
        public  $reset = false;
        public  $filter_active = false;

	    /**
	     * Initialize.
	     */
        public function initialize() {
        	$this->addon = $this->slplus->addon( 'power' );
        }

        /**
         *
         * @param string $HTML
         * @return string
         */
        public function createstring_FilterDisplay( $HTML ) {
            if ( $this->filter_active ) {
	            $power_admin_locations = SLP_Power_Admin_Locations::get_instance();

                $HTML .=
                   '<div class="location_filters">' .
                        '<span class="location_filter_label">' .
                            __('Location Filter: ' , 'slp-power' ) .
                        '</span>' .
                        $power_admin_locations->action_ManageLocations_ByProperty('') .
                   '</div>';
            }
            return $HTML;
        }

        /**
         * Create an input field on the export locations filter page with a div wrapper.
         *
         * @param string $field_name field name and ID
         * @param string $placeholder placeholder text, defaults to ''
         * @param string $label
         * @param bool $joiner
         * @return string HTML for the input field and wrapping div
         */
        function createstring_FilterInput($field_name,$placeholder='', $label='', $joiner = true) {

            $value = ( ! $this->reset && isset( $_REQUEST[$field_name] ) ) ? $_REQUEST[$field_name] : '';

            return
                '<div class="form_entry">' .
                    ( ! empty ($label) ? "<label for='{$field_name}'>{$label}</label>"              : '' ) .
                    ( $joiner          ? $this->createstring_FilterJoinWith("{$field_name}_joiner") : '' ) .
                    "<input id='{$field_name}' name='{$field_name}' class='postform' type='text' value='{$value}' placeholder='{$placeholder}' />" .
                '</div>'
                ;
        }

        /**
         * Create the AND/OR logic joiner for export filters.
         *
         * @param string $field_name the field name and ID
         * @return string HTML for the filter field "joiner" selector
         */
        function createstring_FilterJoinWith($field_name) {
            $value = ( ! $this->reset && isset( $_REQUEST[$field_name] ) ) ? $_REQUEST[$field_name] : '';
            $and_selected = ( $value === 'AND' ) ? ' selected ' : '';
            $or_selected  = ( $value === 'OR'  ) ? ' selected ' : '';

            return
                "<select id='$field_name' name='$field_name' class='postform'>".
                    "<option value='AND' {$and_selected}>".__('and','slp-power').'</option>'.
                    "<option value='OR'  {$or_selected} >".__('or' ,'slp-power').'</option>'.
                '</select>'
                ;
        }

        /**
         * Create the location filter form for export filters.
         */
        function createstring_LocationFilterForm() {

        	$category_checklist = $this->slplus->Power_Category_Manager->get_check_list( 0);
	        if ( !empty( $category_checklist ) ) {
	        	$category_checklist = sprintf( '%s<br/>%s' , __('Category','slp-power'), $category_checklist );
	        }

           $HTML =
                $this->createstring_FilterInput('name',__('Store Name My Place or My* or *Place','slp-power'),__('Name','slp-power'), false) .
                $this->createstring_LocationPropertyDropdown( 'state' , true ) .
                $this->createstring_FilterInput('zip_filter',__('Zip Code 29464 or 294* or *464','slp-power'),__('Zip','slp-power')) .
                $this->createstring_LocationPropertyDropdown( 'country' , true ) .
                $category_checklist
                ;

	        $HTML = apply_filters( 'slp-tag_locations_filter_ui', $HTML );
	        $HTML = apply_filters( 'slp-pro_locations_filter_ui', $HTML );
	        $HTML = apply_filters( 'slp-power_locations_filter_ui', $HTML );

           return "<div id='csa-slp-power-location-filters'>{$HTML}</div>";
        }

        /**
         * Create the HTML string for a state selection drop down from the data tables.
         *
         * @param string $location_property - which location property to use to build the drop down options
         * @return string the HTML for the drop down menu.
         */
        function createstring_LocationPropertyDropdown( $location_property = 'state' , $joiner = true ) {

            $input_id   = $location_property . '_filter';
            switch ( $location_property ) {
                case 'country':
                    $label      = __('Country','slp-power');
                    $all_option = __('All Countries','slp-power');
                    break;

                case 'state':
                default:
                    $label      = __('State','slp-power');
                    $all_option = __('All States','slp-power');
                    break;
            }

            return

                '<div class="form_entry">' .
                    "<label for='{$input_id}'>{$label}</label>" .
                    ( $joiner ? $this->createstring_FilterJoinWith( $location_property . "_joiner") : '' ) .
                    "<select id='{$input_id}' name='{$input_id}' class='postform'>".
                        "<option value=''>{$all_option}</option>".
                    '</select>'.
					"<div id='{$input_id}_spinner' class='spinner'></div>" .
                '</div>'
                ;
        }

        /**
         * Create the location selector SQL command with where clause parameters.
         *
         * @param array $request_data
         * @return mixed[] string = sql command, string[] = where clause parameters
         */
        public function create_LocationSQLCommand( $request_data ) {

            // Formdata Parsing
            //
            $this->formdata = array(
                'name'              => '',
                'state_filter'      => '',
                'state_joiner'      => 'AND',
                'zip_filter'        => '',
                'zip_joiner'        => 'AND',
                'country_filter'    => '',
                'country_joiner'    => 'AND',
            );
            $this->formdata = wp_parse_args($request_data,$this->formdata);

            // Which Records?
            //
            $sqlCommand = array('selectall','where_default');
            $sqlParams = array();

            // Export Name Pattern Matches
            //
            if ( ! empty( $this->formdata['name'] ) ) {
                add_filter('slp_ajaxsql_where',array($this,'filter_ExtendGetSQLWhere_Name'));
                $sqlParams[]  = $this->modifystring_WildCardToSQLLike($this->formdata['name']);
                $this->filter_active = true;
            }


            // State Filter
            //
            if ( ! empty( $this->formdata['state_filter'] ) ) {
                add_filter('slp_ajaxsql_where',array($this,'filter_ExtendGetSQLWhere_State'));
                $sqlParams[]  = sanitize_text_field( $this->formdata['state_filter'] );
                $this->filter_active = true;
            }

            // Export Zip Pattern Matches
            // Use * as a wild card beginning or ending 294* or *464 or *94*.
            //
            if ( ! empty( $this->formdata['zip_filter'] ) ) {
                add_filter('slp_ajaxsql_where',array($this,'filter_ExtendGetSQLWhere_Zip'));
                $sqlParams[]  = $this->modifystring_WildCardToSQLLike($this->formdata['zip_filter']);
                $this->filter_active = true;
            }

            // Country Filter
            //
            if ( ! empty( $this->formdata['country_filter'] ) ) {
                add_filter('slp_ajaxsql_where',array($this,'filter_ExtendGetSQLWhere_Country'));
                $sqlParams[]  = sanitize_text_field( $this->formdata['country_filter'] );
                $this->filter_active = true;
            }

            return array($sqlCommand , $sqlParams);
        }

       /**
         * Add name filters to the SQL where clause.
         *
         * @param string $where current where clause
         * @return string
         */
        public function filter_ExtendGetSQLWhere_Name($where) {
            return $this->slplus->database->extend_Where($where,"sl_store LIKE '%s' ");
        }

        /**
         * Add country filters to the SQL where clause.
         *
         * @param string $where current where clause
         * @return string
         */
        public function filter_ExtendGetSQLWhere_Country($where) {
            return $this->slplus->database->extend_Where($where,'trim(sl_country) = %s ',$this->formdata['country_joiner']);
        }

        /**
         * Add state filters to the SQL where clause.
         *
         * @param string $where current where clause
         * @return string
         */
        public function filter_ExtendGetSQLWhere_State($where) {
            return $this->slplus->database->extend_Where($where,'trim(sl_state) = %s ',$this->formdata['state_joiner']);
        }

        /**
         * Add zip filters to the SQL where clause.
         *
         * @param string $where current where clause
         * @return string
         */
        public function filter_ExtendGetSQLWhere_Zip($where) {
            return $this->slplus->database->extend_Where($where,"sl_zip LIKE '%s' ",$this->formdata['zip_filter_joiner']);
        }

        /**
         * Extend the current where clause to also filter by the categories selected.
         *
         * @param $where
         * @return string
         */
        public function filter_locations_by_category( $where ) {
            $this->addon->create_object_category_data();
            return $this->slplus->Power_Category_Data->add_where_location_has_category( $where , implode( ',' , $_REQUEST['tax_input']['stores'] ) );
        }

        /**
         * Change wildcard strings to SQL Like Statements.
         *
         * Replace * with % in the string.
         *
         * @param string $wildcard_string
         * @return string
         */
        public function modifystring_WildCardToSQLLike($wildcard_string) {
            return str_replace('*','%',sanitize_text_field( $wildcard_string ));
        }

        /**
         * Reset the filter variables.
         */
        public function reset() {
            $this->reset = true;
        }
    }
	global $slplus;
	if ( is_a( $slplus, 'SLPlus' ) ) {
		$slplus->add_object( new SLP_Power_Admin_Location_Filters( array( 'addon' => $slplus->add_ons->instances[ 'slp-power' ] ) ) );
	}
}
