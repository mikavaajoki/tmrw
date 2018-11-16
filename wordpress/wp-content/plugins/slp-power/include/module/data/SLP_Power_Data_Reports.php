<?php
defined( 'ABSPATH' ) || exit;

/**
 * Admin Report Data Interface for Power
 *
 * @property        mixed[]     $counts_dataset     The search and results count data.
 *                                  An array of data for each day in the date range.
 *                                  [0..n]['QueryCount'] = query count for the day
 *                                  [0..n]['ResultCount'] = result count for the day
 *                                  [0..n]['TheDate'] = the day represented by this array entry
 * @property        string      $google_chart_type  Google Chart Type
 * @property        string[]    $table              The SQL data table names.
 * @property        string[]    $top_results        Top results array.
 * @property        string[]    $top_searches    Top searches array.
 * @property        int         $total_results Total results.
 * @property        int         $total_searches Total queries.
 *
 */
class SLP_Power_Data_Reports  extends SLPlus_BaseClass_Object {
    public $counts_dataset;
    public $google_chart_type;
    public $table = array(
        'query'         =>  'slp_rep_query'         ,
        'results'       =>  'slp_rep_query_results' ,
        'locations'     =>  'store_locator'         ,
    );
    public $top_results;
    public $top_searches;
    public $total_results = 0;
    public $total_searches = 0;

    /**
     * Things to do at the start.
     */
    function initialize() {
        foreach ($this->table as $name => $value ) {
            $this->table[$name] = $this->slplus->db->prefix . $value;
        }
    }

	/**
	 * Delete all history entries before the specified date.
	 *
	 * @param $date
	 * @return int|boolean
	 */
    public function delete_history_before( $date ) {
    	global $wpdb;
    	$query = sprintf( 'DELETE q,r FROM %s q INNER JOIN %s r ON q.slp_repq_id = r.slp_repq_id ' , $this->table[ 'query' ] , $this->table['results'] ) .
	             'WHERE slp_repq_time < %s';
    	$prepped_query = $wpdb->prepare( $query , array( $date  ) );
    	return $wpdb->query( $prepped_query );

    }

    /**
     * Set the top_results dataset for the given date range.
     *
     * @param $start_date
     * @param $end_date
     * @param $limit
     */
    public function set_top_results( $start_date , $end_date , $limit = 10 ) {
        if ( isset( $this->top_results ) ) { return; }

        // SELECT sl_store,sl_city,sl_state, sl_zip, sl_tags, count(*) as ResultCount
        //      FROM wp_slp_rep_query_results res
        //          LEFT JOIN wp_store_locator sl
        //              ON (res.sl_id = sl.sl_id)
        //      WHERE slp_repq_time > '%s' AND slp_repq_time <= '%s'
        //      GROUP BY sl_store,sl_city,sl_state,sl_zip,sl_tags
        //      ORDER BY ResultCount DESC
        //      LIMIT %s
        //
        $query = sprintf(
            "SELECT sl_store,sl_city,sl_state, sl_zip, sl_tags, count(*) as ResultCount " .
                "FROM %s res ".
                "LEFT JOIN %s sl  ON (res.sl_id = sl.sl_id) ".
                "LEFT JOIN %s qry ON (res.slp_repq_id = qry.slp_repq_id) ".

            "WHERE slp_repq_time > '%s' AND slp_repq_time <= '%s' ".

            "GROUP BY sl_store,sl_city,sl_state,sl_zip,sl_tags ".

            "ORDER BY ResultCount DESC ".

            "LIMIT %s"
            ,
            $this->table['results'],
            $this->table['locations'],
            $this->table['query'],
            $start_date,
            $end_date,
            $limit
        );

        $this->top_results = $this->slplus->db->get_results($query);
    }


    /**
     * Set the top searches dataset for the given date range.
     *
     * @param $start_date
     * @param $end_date
     * @param $limit
     */
    public function set_top_searches( $start_date , $end_date , $limit = 10 ) {
        if ( isset( $this->top_searches ) ) { return; }

        // SELECT slp_repq_address,count(*) as QueryCount
        //      FROM wp_slp_rep_query
        //      WHERE slp_repq_time > '%s' AND slp_repq_time <= '%s'
        //      GROUP BY slp_repq_address
        //      ORDER BY QueryCount DESC;
        //
        $query = sprintf(
            'SELECT slp_repq_address, count(*)  as QueryCount FROM %s ' .
            "WHERE slp_repq_time > '%s' AND " .
            "      slp_repq_time <= '%s' " .
            "GROUP BY slp_repq_address ".
            "ORDER BY QueryCount DESC " .
            "LIMIT %s"
            ,
            $this->table['query'],
            $start_date,
            $end_date,
            $limit
        );

        $this->top_searches = $this->slplus->db->get_results($query);
    }

    /**
     * Put daily results into dataset property as a named array with count, sum count, date.
     *
     * dataset['thecount'] = count (*)
     * dataset['theresults'] = count of results
     * dataset['thedate'] = the date.
     *
     *
     * select
     * count(*) as TheCount,
     * sum((select count(*) from wp_slp_rep_query_results RES
     * where slp_repq_id = QRY2.slp_repq_id)) as TheResults,
     * DATE(slp_repq_time) as TheDate
     * from wp_slp_rep_query QRY2 group by TheDate;
     *
     * @param string $start_date
     * @param string $end_date
     */
    private function set_counts_dataset( $start_date , $end_date ) {
        if ( isset( $this->counts_dataset ) ) { return; }

        $query = sprintf(

            'SELECT '                       .
                'count(*) as QueryCount, '  .

                "sum((select count(*) from %s where slp_repq_id = qry2.slp_repq_id)) as ResultCount," .

                "DATE(slp_repq_time) as TheDate " .

            "FROM %s qry2 " .

            "WHERE slp_repq_time > '%s' AND " .
            "      slp_repq_time <= '%s' " .

            "GROUP BY TheDate",

            $this->table['results'  ] ,
            $this->table['query'    ] ,
            $start_date,
            $end_date
        );

        $this->counts_dataset = $this->slplus->db->get_results($query);
    }

    /**
     * Summarize the data, getting total result and query counts and setting the google chart data string.
     */
    public function summarize_data( $start_date , $end_date ) {
        $this->set_counts_dataset( $start_date , $end_date );

        $this->total_searches = 0;
        $this->total_results = 0;

        foreach ($this->counts_dataset as $data_point) {
            $this->total_searches += $data_point->QueryCount;
            $this->total_results += $data_point->ResultCount;
        }

        $this->google_chart_type =
            ( count($this->counts_dataset) < 2)   ?
                'ColumnChart'   :
                'AreaChart'     ;
    }

}
