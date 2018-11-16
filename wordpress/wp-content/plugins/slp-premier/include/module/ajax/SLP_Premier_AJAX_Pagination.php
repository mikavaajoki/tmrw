<?php
require_once(SLPLUS_PLUGINDIR.'/include/base_class.ajax.php');

/**
 * Holds the ajax-only code.
 *
 * This allows the main plugin to only include this file in AJAX mode
 * via the slp_init when DOING_AJAX is true.

 * @property        SLP_Premier      $addon
 */
class SLP_Premier_AJAX_Pagination extends SLPlus_BaseClass_Object{
    public $addon;

	/**
	 * Initialize
	 */
    final function initialize() {
	    $this->addon = $this->slplus->AddOns->instances['slp-premier'];
    }

    /**
     * Create the pagination block.
     *
     * @param $response_array
     *
     * @return string
     */
    private function create_pagination_block( $response_array ) {
        $primary_query = $this->get_primary_query( $response_array[ 'data_queries' ] );

        // Previous Button
        // If SQL query has OFFSET , show this
        $previous_button = '';
        if ( ! empty( $primary_query )  && ( strpos( $primary_query , ' OFFSET ' ) !== FALSE ) ) {
            $link_text = __('Previous Page' , 'slp-premier');
            $previous_button =
                '<a id="previous-locations-page" ' .
                    'href="javascript:SLPPREMIER.location_list.get_previous_page();" ' .
                    "title='{$link_text}' " .
                    '>'.
                '<span class="dashicons dashicons-arrow-left-alt2"></span>'.
                '</a>'
                ;
        }

        // Next Button
        // If count matches SQL query LIMIT , show this
        $next_button = '';
        if (
            ( (int) $response_array['count'] > 0 ) &&
            ( ! empty ( $primary_query ) ) &&
            ( strpos( $primary_query , sprintf(' LIMIT %d' , $response_array['count'] ) ) !== FALSE )
        ) {
            $link_text = __('Next Page' , 'slp-premier');
            $next_button =
                '<a id="next-locations-page" ' .
                    'href="javascript:SLPPREMIER.location_list.get_next_page();" ' .
                    "title='{$link_text}' " .
                    '>'.
                    '<span class="dashicons dashicons-arrow-right-alt2"></span>' .
                '</a>'
                ;
        }

        // Setup the pagination output
        // if prev/next buttons exist.
        //
        $HTML = '';
        if ( ! empty( $next_button ) || ! empty( $previous_button ) ) {
            $HTML =
                '<div class="results_pagination">' .
                sprintf(
                    '<span class="pagination_label">%s</span>' ,
                    $this->addon->options['pagination_label']
                ).
                $previous_button .
                $next_button .
                '</div>'
            ;
        }

        return $HTML;
    }

    /**
     * Get the primary data query from the AJAX query array.
     *
     * @param $data_queries
     *
     * @return string || null
     */
    private function get_primary_query( $data_queries ) {
        foreach ( $data_queries as $query => $meta ) {
            if ( ! empty( $meta[ 'query' ] ) ) return $meta[ 'query' ];
        }
        return null;
    }

    /**
     * Modify the AJAX response.
     *
     * @param $response_array
     * @return mixed
     */
     function modify_ajax_response( $response_array ) {

         $response_array['premier']['pagination_block']     = $this->create_pagination_block( $response_array );
         $response_array['premier']['search_coordinates']   = $_POST['lat'] . ',' . $_POST['lng'];
         $response_array['premier']['options']  = $this->addon->options;

         return $response_array;
    }

    /**
     * Modify the SQL query to handle pagination.
     *
     * @param $sql_query
     * @return string
     */
    function modify_sql_for_pagination( $sql_query ) {

        if ( isset($_POST['page']) && ( (int) $_POST['page'] > 0 ) ) {
            add_filter( 'slp_ajaxsql_queryparams' , array( $this , 'add_sql_param_for_pagination')  );
            $sql_query  .= ' OFFSET %d';
        }


        return $sql_query;
    }

    /**
     * Add the SQL offset parameter for pagination.
     *
     * @param $params
     * @return array
     */
    function add_sql_param_for_pagination( $params ) {
        $params[] = (int) $_POST['page'] * $this->slplus->AJAX->query_limit;
        return $params;
    }
}