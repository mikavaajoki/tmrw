<?php
defined( 'ABSPATH' ) || exit;
require_once( SLPLUS_PLUGINDIR . '/include/base_class.ajax.php' );


/**
 * Holds the ajax-only code.
 *
 * This allows the main plugin to only include this file in AJAX mode
 * via the slp_init when DOING_AJAX is true.
 *
 * @property        SLPPower                        $addon
 * @property        SLP_Power_Category_Data         $category_data   The data helper object.
 * @property-read   SLP_Power_AJAX_Location_Manager $location_manager
 *
 */
class SLP_Power_AJAX extends SLP_BaseClass_AJAX {
	public  $category_data;
	private $location_manager;
	public  $query_params_valid = array( 'options' );
	private $upload_transient   = 'slp_uploads_path';
	public  $valid_actions      = array(
		'csl_ajax_onload' ,
		'csl_ajax_search' ,
		'slp_background_location_download' ,
		'slp_change_option' ,
		'slp_clear_import_messages' ,
		'slp_create_page',
		'slp_download_report_csv' ,
		'slp_download_locations_csv' ,
		'slp_get_country_list' ,
		'slp_get_state_list' ,
	);

	/**
	 * Set up our environment.
	 *
	 * @uses \SLP_Power_AJAX::modify_formdata via SLP Filter slp_modify_ajax_formdata
	 */
	final function initialize() {
		$this->addon = $this->slplus->addon( 'Power' );
		add_filter( 'slp_modify_ajax_formdata' , array( $this , 'modify_formdata' ) );
		add_filter( 'wp_prepare_attachment_for_js'      , array( $this , 'modify_js_response' ) , 10 , 3 );
		parent::initialize();
	}

	/**
	 * Add our specific AJAX filters.
     *
     * @uses \SLP_Power_AJAX::filter_JSONP_SearchByCategory
	 */
	function add_ajax_hooks() {
		$this->addon->create_object_category_data();
		$this->category_data = $this->addon->category_data;

		add_filter( 'slp_ajaxsql_where' , array( $this , 'filter_JSONP_SearchFilters' ) , 20 );

		add_filter( 'slp_location_filters_for_AJAX' , array( $this , 'filter_JSONP_SearchByCategory' ) );
        add_filter( 'slp_location_filters_for_AJAX' , array( $this , 'createstring_TagSelectionWhereClause' ) );

		add_filter( 'slp_location_having_filters_for_AJAX' , array( $this , 'filter_AJAX_AddHavingClause' ) , 55 );
		add_action( 'slp_orderby_default' , array( $this , 'add_category_count_to_orderby' ) , 5 );

		add_action( 'wp_ajax_slp_background_location_download' , array( $this , 'location_download_in_background' ) );
		add_action( 'wp_ajax_slp_download_report_csv' , array( $this , 'download_report_csv' ) );
		add_action( 'wp_ajax_slp_download_locations_csv' , array( $this , 'download_locations_csv' ) );

		add_action( 'wp_ajax_slp_create_page' , array( $this , 'create_page' ) );

		add_action( 'wp_ajax_slp_get_country_list' , array( $this , 'get_country_list' ) );
		add_action( 'wp_ajax_slp_get_state_list' , array( $this , 'get_state_list' ) );

		if ( $this->slplus->SmartOptions->reporting_enabled->is_true ) {
			add_action( 'slp_report_query_result' , array( $this , 'log_search_queries_and_results' ) , 10 , 2 );
		}

		$this->add_global_hooks();
	}

	/**
	 * Global hooks, exposed possibly even outside AJAX.
	 */
	public function add_global_hooks() {
		add_filter( 'slp_results_marker_data' , array( $this , 'modify_ajax_markers' ) );
	}

	/**
	 * Change the results order.
	 *
	 * Precedence is given to the order by category count option over all other extensions that came before it.
	 * This is enacted by placing the special category count clause as the first parameter of extend_OrderBy,
	 * and by setting the filter to a high priority (run last).
	 *
	 */
	function add_category_count_to_orderby() {
		if ( empty( $this->slplus->SmartOptions->ajax_orderby_catcount->value ) ) {
			return;
		}
		$this->slplus->database->extend_order_array( '(' . $this->category_data->get_SQL( 'select_categorycount_for_location' ) . ') DESC ' );
	}

	/**
	 * Clean the $_REQUEST array of some things we don't want to track.
	 *
	 * @return array[string]string
	 */
	private function clean_request() {
		$clean_request = $_REQUEST;

		unset( $clean_request[ 'options' ][ 'bubblelayout' ] );

		// Remove all label* options
		foreach ( $clean_request[ 'options' ] as $key => $value ) {
			if ( strpos( $key , 'label' ) === 0 ) {
				unset( $clean_request[ 'options' ][ $key ] );
			}
		}

		return $clean_request;
	}

	/**
	 * Attach the location_manager object.
	 */
	private function create_location_manager() {
		if ( ! isset( $this->location_manager ) ) {
            $this->location_manager = new  SLP_Power_AJAX_Location_Manager();
		}
	}

	/**
	 * Create A Store Page
	 *
	 * @uses \SLP_Power_Admin_Locations_Actions::create_pages
	 */
	public function create_page() {
		$response = array( 'status' => 'failed' );
		if ( ! empty( $_POST[ 'screenoptionnonce' ] ) && wp_verify_nonce( $_POST[ 'screenoptionnonce' ] ,'screen-options-nonce'  ) ) {

			/**
			 * @var SLP_Power_Admin_Locations_Actions $action_processor
			 */
			$action_processor = SLP_Power_Admin_Locations_Actions::get_instance();
			$action_processor->addon = $this->addon;
			$result = $action_processor->create_pages();

			if ( ! is_wp_error( $result ) ) {
				$response[ 'status' ] = 'ok';

			} else {
				/**
				 * @var WP_Error $result
				 */
				$response[ 'status'  ] = $result->get_error_code();
				$response[ 'message' ] = $result->get_error_message();

			}
		}
		echo json_encode( $response );
		wp_die();

	}

	/**
	 * Add the tags condition to the MySQL statement used to fetch locations with JSONP.
	 *
	 * @param string[] $currentFilters
	 *
	 * @return string[]
	 */
	public function createstring_TagSelectionWhereClause( $currentFilters ) {
		if ( ! isset( $_POST[ 'tags' ] ) || ( $_POST[ 'tags' ] == '' ) ) {
			return $currentFilters;
		}

		$posted_tag = preg_replace( '/^\s+(.*?)/' , '$1' , $_POST[ 'tags' ] );
		$posted_tag = preg_replace( '/(.*?)\s+$/' , '$1' , $posted_tag );

		return array_merge( $currentFilters , array( " AND ( sl_tags LIKE '%%" . $posted_tag . "%%') " ) );
	}


	/**
	 * Process incoming AJAX request to download the CSV file.
	 * TODO: use locations extended class
	 */
	function download_locations_csv() {
		$this->slplus->set_php_timeout();


		$this->addon->create_CSVLocationExporter();
		$this->addon->csvExporter->do_SendFile();
	}


	/**
	 * Process incoming AJAX request to download the CSV file.
	 */
	function download_report_csv() {
		$this->slplus->set_php_timeout();


		// CSV Header
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=slplus_' . $_REQUEST[ 'filename' ] . '.csv' );
		header( 'Content-Type: application/csv;' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// Setup our processing vars
		//
		global $wpdb;
		$query = $_REQUEST[ 'query' ];

		// All records - revise query
		//
		if ( isset( $_REQUEST[ 'all' ] ) && ( $_REQUEST[ 'all' ] == 'true' ) ) {
			require_once( SLPLUS_PLUGINDIR . 'include/module/location/SLP_Location_Manager.php' );
			$query = preg_replace( '/\d?$/' , $this->slplus->Location_Manager->location_limit , $query );
		}

		$slpQueryTable     = $wpdb->prefix . 'slp_rep_query';
		$slpResultsTable   = $wpdb->prefix . 'slp_rep_query_results';
		$slpLocationsTable = $wpdb->prefix . 'store_locator';

		$expr  = "/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/";
		$parts = preg_split( $expr , trim( html_entity_decode( $query , ENT_QUOTES ) ) );
		$parts = preg_replace( "/^\"(.*)\"$/" , "$1" , $parts );

		// Return the address in CSV format from the reports
		// $query = "top,,2017-02-22,2017-03-24 23:59:59,10"
		// $parts[0] = type
		// $parts[1]
		// $parts[2] = start
		// $parts[3] = end
		// $parts[4] = limit
		//
		if ( $parts[ 0 ] === 'addr' ) {
			$slpReportStartDate = $parts[ 1 ];
			$slpReportEndDate   = $parts[ 2 ];

			// Only Digits Here Please
			//
			$slpReportLimit = preg_replace( '/[^0-9]/' , '' , $parts[ 3 ] );

			$query      = "SELECT slp_repq_address, count(*)  as QueryCount FROM $slpQueryTable " . "WHERE slp_repq_time > %s AND " . "      slp_repq_time <= %s " . "GROUP BY slp_repq_address " . "ORDER BY QueryCount DESC " . "LIMIT %d";
			$queryParms = array(
				$slpReportStartDate ,
				$slpReportEndDate ,
				$slpReportLimit ,
			);

			// Return the locations searches in CSV format from the reports
			//
		} else if ( $parts[ 0 ] === 'top' ) {
			$slpReportStartDate = $parts[ 2 ];
			$slpReportEndDate   = $parts[ 3 ];

			// Only Digits Here Please
			//
			$slpReportLimit = preg_replace( '/[^0-9]/' , '' , $parts[ 4 ] );

			$query      = "SELECT sl_store,sl_city,sl_state, sl_zip, sl_tags, count(*) as ResultCount " . "FROM $slpResultsTable res " . "LEFT JOIN $slpLocationsTable sl " . "ON (res.sl_id = sl.sl_id) " . "LEFT JOIN $slpQueryTable qry " . "ON (res.slp_repq_id = qry.slp_repq_id) " . "WHERE slp_repq_time > %s AND slp_repq_time <= %s " . "GROUP BY sl_store,sl_city,sl_state,sl_zip,sl_tags " . "ORDER BY ResultCount DESC " . "LIMIT %d";
			$queryParms = array(
				$slpReportStartDate ,
				$slpReportEndDate ,
				$slpReportLimit ,
			);

			// Not Locations (top) or addresses entered in search
			// short circuit...
			//
		} else {
			die( __( "Cheatin' huh!" , 'slp-power' ) );
		}

		// No parms array?  GTFO
		//
		if ( ! is_array( $queryParms ) ) {
			die( __( "Cheatin' huh!" , 'slp-power' ) );
		}


		// Run the query & output the data in a CSV
		$thisDataset = $wpdb->get_results( $wpdb->prepare( $query , $queryParms ) , ARRAY_N );


		// Sorting
		// The sort comes in based on the display table column order which
		// matches the query output column order listed here.
		//
		// It is a paired array, first number is the column number (zero offset)
		// second number is the sort order [0=ascending, 1=descending]
		//
		// The sort needs to happen AFTER the select.
		//

		// Get our sort array
		//
		$thisSort = explode( ',' , $_REQUEST[ 'sort' ] );

		// Build our array_multisort command and our sort index/sort order arrays
		// we will need this later for helping do a multi-dimensional sort
		//
		$sob            = 'sort';
		$amsstring      = '';
		$sortarrayindex = 0;
		foreach ( $thisSort as $sl_value ) {
			if ( $sob == 'sort' ) {
				$sort[]    = $sl_value;
				$amsstring .= '$s[' . $sortarrayindex ++ . '], ';
				$sob       = 'order';
			} else {
				$order[]   = $sl_value;
				$amsstring .= ( $sl_value == 0 ) ? 'SORT_ASC, ' : 'SORT_DESC, ';
				$sob       = 'sort';
			}
		}
		$amsstring .= '$thisDataset';

		// Now that we have our sort arrays and commands,
		// build the indexes that will be used to do the
		// multi-dimensional sort
		//
		foreach ( $thisDataset as $key => $row ) {
			$sortarrayindex = 0;
			foreach ( $sort as $column ) {
				$s[ $sortarrayindex ++ ][ $key ] = $row[ $column ];
			}
		}

		// Now do the multidimensional sort
		//
		// This will sort using the first array ($s[0] we built in the above 2 steps)
		// to determine what order to put the "records" (the outter array $thisDataSet)
		// into.
		//
		// If there are secondary arrays ($s[1..n] as built above) we then further
		// refine the sort using these secondary arrays.  Think of them as the 2nd
		// through nth columns in a multi-column sort on a spreadsheet.
		//
		// This exactly mimics the jQuery sorts that manage our tables on the HTML
		// page.
		//

		//array_multisort($amsstring);
		// Output the sorted CSV strings
		// This simply iterates through our newly sorted array of records we
		// got from the DB and writes them out in CSV format for download.
		//
		foreach ( $thisDataset as $thisDatapoint ) {
			print SLPPower::array_to_CSV( $thisDatapoint );
		}

		// Get outta here
		die();
	}

	/**
	 * Add to the AJAX having clause
	 *
	 * @param mixed[] having clause array
	 *
	 * @return mixed[]
	 */
	function filter_AJAX_AddHavingClause( $clauseArray ) {

		// Ignore Radius Is On
		//
		if ( $this->slplus->options[ 'ignore_radius' ] === '1' ) {
			array_push( $clauseArray , ' OR (sl_distance > 0) ' );
		}

		return $clauseArray;
	}

	/**
	 * Add the category condition to the MySQL statement used to fetch locations with JSONP.
	 *
     * @used-by \SLP_Power_AJAX::add_ajax_hooks
     *
	 * @param string $currentFilters
	 *
	 * @return string
	 */
	public function filter_JSONP_SearchByCategory( $currentFilters ) {
		if ( empty( $this->formdata ) ) {
			return $currentFilters;
		}
		if ( ! isset( $this->formdata[ 'cat' ] ) ) {
			return $currentFilters;
		}
		if ( ! is_array( $this->formdata[ 'cat' ] ) && ( $this->formdata[ 'cat' ] <= 0 ) ) {
			return $currentFilters;
		}

		$this->addon->create_object_category_data();

		// Single Category
		//
		if ( ! is_array( $this->formdata[ 'cat' ] ) ) {
			$sql_select_stores_in_cats = sprintf( 'AND ' . $this->addon->category_data->get_SQL( 'where_location_has_category' ) , $this->formdata[ 'cat' ] );

			// Array Of Categories
		} else {
			$sql_select_stores_in_cats = '';
			foreach ( $this->formdata[ 'cat' ] as $term_id ) {
				if ( $term_id > 0 ) {
					$sql_select_stores_in_cats .= sprintf( 'AND ' . $this->addon->category_data->get_SQL( 'where_location_has_category' ) , $term_id );
				}
			}
		}

		// Setup and clause to select stores by a specific category
		//
		return array_merge( $currentFilters , array( $sql_select_stores_in_cats ) );
	}

	/**
	 * Add the selected filters to the search results.
	 *
	 */
	function filter_JSONP_SearchFilters( $where ) {
		if ( ! isset( $this->slplus->AJAX ) ) {
			return $where;
		}

		$ajax_options = $this->addon->options;

		foreach ( $this->addon->location_fields as $field => $shorthand ) {
			if ( ! empty( $ajax_options[ $shorthand ] ) && ( $ajax_options[ $shorthand . '_selector' ] === 'hidden' ) ) {
				return $this->slplus->database->extend_WhereFieldMatches( $where , $field , $ajax_options[ $shorthand ] );
			}

		}

		return $where;
	}

	/**
	 * Return a list of current countries
	 */
	public function get_country_list() {
		$this->create_location_manager();
		$this->location_manager->get_country_list();
	}

	/**
	 * Return a list of current states
	 */
	public function get_state_list() {
		$this->create_location_manager();
		$this->location_manager->get_state_list();
	}

	/**
	 * Log the search query and results into the reporting tables.
	 *
	 * <code>
	 * $query_params['QUERY_STRING'] => 'the query string';
	 * $query_params['tags'] => 'tags,used,for,this,query';
	 * $query_params['address'] => 'address for, the search';
	 * $query_params['radius'] => 'radius_of_search';
	 * </code>
	 *
	 * @param          array [string]string Contain query sql, tags, address and radius
	 * @param string[] Query result row id (integers) array.
	 */
	function log_search_queries_and_results( $query_params , $results ) {
		$inserted_query_id = $this->log_search_query( $query_params );
		$this->log_search_results( $results , $inserted_query_id );
	}

	/**
	 * Log the search query that was used.
	 *
	 * <code>
	 * $query_params['QUERY_STRING'] => 'the query string';
	 * $query_params['tags'] => 'tags,used,for,this,query';
	 * $query_params['address'] => 'address for, the search';
	 * $query_params['radius'] => 'radius_of_search';
	 * </code>
	 *
	 * @param array [string]string Contain query sql, tags, address and radius
	 *
	 * @return int the insert ID for this record.
	 */
	private function log_search_query( $query_params ) {

		$this->slplus->db->insert( "{$this->slplus->db->prefix}slp_rep_query" , array(
			                                                                      'slp_repq_query'   => $query_params[ 'QUERY_STRING' ] ,
			                                                                      'slp_repq_tags'    => $query_params[ 'tags' ] ,
			                                                                      'slp_repq_address' => $query_params[ 'address' ] ,
			                                                                      'slp_repq_radius'  => $query_params[ 'radius' ] ,
			                                                                      'meta_value'       => serialize( array( 'REQUEST' => $this->clean_request() , 'SERVER' => $_SERVER ) ) ,
		                                                                      ) , '%s' );

		return $this->slplus->db->insert_id;
	}

	/**
	 * Log the search results that were returned.
	 *
	 * @param string[] $results
	 * @param int      $inserted_query_id
	 */
	private function log_search_results( $results , $inserted_query_id ) {
		foreach ( $results as $row_id ) {
			$this->slplus->db->insert( "{$this->slplus->db->prefix}slp_rep_query_results" , array(
				                                                                              'slp_repq_id' => $inserted_query_id ,
				                                                                              'sl_id'       => $row_id ,
			                                                                              ) , '%d' );
		}
	}

	/**
	 * Start the process to download the locations in the background.
	 */
	function location_download_in_background() {

		// TODO: Fire off the process to get the CSV written to disk.
		// Do this is an AJAX post from here?
		//

		// Tell the user the CSV creation process has started.
		//
		die( json_encode( array(
			                  'message' => __( 'Creating the location export CSV file.' , 'slp-power' ) ,
		                  ) ) );
	}

	/**
	 * Modify the marker data.
	 *
	 * @param array [string]string $marker the current marker data
	 *
	 * @return array[string]string
	 */
	function modify_ajax_markers( $marker ) {

		$marker = $this->set_contact_fields( $marker );

		$marker = $this->set_location_tags( $marker );

		$marker = $this->set_location_pages_properties( $marker );

		$marker = $this->set_location_category_properties( $marker );

		return $marker;
	}


	/**
	 * Format interesting meta into a string for the media library.
	 * @param $meta
	 *
	 * @return string
	 */
	private function format_meta_for_media_library( $meta ) {
		if ( empty( $meta[ 'data_type' ] ) ) return '';
		$status = $meta[ 'processed' ] ? __( 'Processing complete.' , 'slp-power' ) : __( 'Being processed.' , 'slp-power' );

		$process_string = '';

		if ( ! empty( $meta[ 'original_name'] ) ) {
			$process_string .= sprintf(
				'<div class="slp-power-meta record"><strong>%s</strong> %s</div>',
				__( 'Original Name:', 'slp-power' ),
				$meta['original_name']
			);
		}

		if ( ! empty( $meta[ 'fields'] ) ) {
			$process_string .= sprintf(
				'<div class="slp-power-meta offset"><strong>%s</strong> %s</div>',
				__( 'Fields:', 'slp-power' ),
				$meta['fields']
			);
		}

		if ( ! empty( $meta[ 'size'] ) ) {
			$process_string .= sprintf(
				'<div class="slp-power-meta offset"><strong>%s</strong> %s</div>',
				__( 'File Size:', 'slp-power' ),
				$meta['size']
			);
		}

		if ( ! $meta[ 'processed' ] ) {
			$process_string = sprintf(
				'<div class="slp-power-meta process_time"><strong>%s</strong> %s</div>' ,
				__( 'Next process time:' , 'slp-power' ),
				date( DATE_RFC822 , $meta[ 'next_process_time'] )
			);

			$process_string .= sprintf(
				'<div class="slp-power-meta record"><strong>%s</strong> %s</div>' ,
				__( 'Last Processed Record:' , 'slp-power' ),
				$meta[ 'record']
			);

			$process_string .= sprintf(
				'<div class="slp-power-meta offset"><strong>%s</strong> %s</div>' ,
				__( 'Reading Offset:' , 'slp-power' ),
				$meta[ 'offset']
			);
		} else {
			$process_string .= sprintf(
				'<div class="slp-power-meta record"><strong>%s</strong> %s</div>' ,
				__( 'Records Loaded:' , 'slp-power' ),
				$meta[ 'record']
			);
		}

		if ( ! empty( $meta[ 'size'] ) ) {
			$process_string .= sprintf(
				'<div class="slp-power-meta offset"><strong>%s</strong> %s</div>',
				__( 'File Size:', 'slp-power' ),
				$meta['size']
			);
		}

		/**
		 * @var SLP_Power_Locations_Import $importer
		 */
		$importer = SLP_Power_Locations_Import::get_instance();
		foreach ( $importer->update_codes as $update_code ) {
			if ( isset( $meta[ $update_code ] ) ) {
				$process_string .= sprintf('<div class="slp-power-meta update_code_%s">%d <strong>%s</strong></div>', $update_code, $meta[ $update_code ], $update_code );
			}
		}

			$meta_string = <<<STRING
<br/>		
<div class="slp-power-meta data_type"><strong>Data Type:</strong> {$meta[ 'data_type' ]}</div>
<div class="slp-power-meta status"><strong>Status:</strong> {$status}</div>
{$process_string}
STRING;

		return $meta_string;
	}

	/**
	 * Add the data_type to the JSON response meta.
	 *
	 * @param array      $response   Array of prepared attachment data.
	 * @param int|object $attachment Attachment ID or object.
	 * @param array      $meta       Array of attachment meta data.
	 *
	 * @return mixed
	 */
	public function modify_js_response( $response, $attachment, $meta ) {
		if ( empty( $meta['data_type' ] ) ) return $response;

		// Only allow if we are uploading an attachment via async-upload or querying attachments via the media center.
		if ( ( $_REQUEST[ 'action' ] !== 'upload-attachment' ) && ( $_REQUEST[ 'action' ] !== 'query-attachments' ))  return $response;

		$response[ 'meta' ] = array_merge( $meta , wp_get_attachment_metadata( $attachment->ID , true ) );

		// If we are uploading, set this stuff or the first time.
		if ( $_REQUEST[ 'action' ] === 'upload-attachment' ) {
			$importer = SLP_Power_Locations_Import::get_instance();
			$response[ 'meta' ][ 'next_process_time' ] = $importer->start_detached_import( $attachment );

			// For media review, get the schedule.
		} else {
			$response[ 'meta' ][ 'next_process_time' ] = wp_next_scheduled( SLP_Power_Locations_Import::hook , array( $attachment->ID ));
		}

		$response[ 'compat' ][ 'meta' ] = $this->format_meta_for_media_library( $response[ 'meta' ] );

		return $response;
	}

	/**
	 * Modify the AJAX received form data.
	 *
	 * @used-by \SLP_Power_AJAX::initialize via SLP Filter slp_modify_ajax_formdata
	 *
	 * @param array $data
	 *
	 * @return mixed
	 */
	public function modify_formdata( $data ) {

		// Checklist Of Categories
		// Convert category list from array to comma separated string which works like OR instead of default AND in \SLP_Power_AJAX::filter_JSONP_SearchByCategory
		if ( isset( $data[ 'tax_input' ] ) && ! empty( $data[ 'tax_input' ][ SLPlus::locationTaxonomy ] ) && ! isset( $data[ 'cat' ] ) ) {
			$data[ 'cat' ] = join( ',' , $data[ 'tax_input' ][ SLPlus::locationTaxonomy ] );
		}

		return $data;
	}

	/**
	 * Clear the import messages transient.
	 *
	 * Runs via do_ajax_startup() in the base class.
	 */
	function process_slp_clear_import_messages() {
		$this->addon->create_CSVLocationImporter();
		$this->addon->csvImporter->messages->clear_messages();
		die( 'ok' );
	}

	/**
	 * Set the special contact fields to be returned by AJAX for this location.
	 *
	 * @param  array $marker
	 *
	 * @return array
	 */
	private function set_contact_fields( $marker ) {
		if ( ! empty( $marker[ 'data' ][ 'contact_image' ] ) ) {
			$contact_image_html = sprintf( '<img src="%s" alt="%s">' , $marker[ 'data' ][ 'contact_image' ] , $marker[ 'data' ][ 'contact' ] );
		} else {
			$contact_image_html = '';
		}

		return array_merge( $marker , array(
			                            'contact_image_html' => $contact_image_html ,
		                            ) );
	}

	/**
	 * Set the location marker based on categories.
	 */
	public function get_location_marker() {
		$locationMarker = '';

		// Category Location Marker
		//
		if ( $this->slplus->SmartOptions->default_icons->is_false && ( count( $this->addon->current_location_categories ) > 0 ) ) {

			$best_rank = 999999;
			foreach ( $this->addon->current_location_categories as $term_id ) {
				$category_details = $this->addon->get_TermWithTagalongData( $term_id );
				$cat_rank         = ( $category_details[ 'rank' ] === '' ) ? 999998 : (int) $category_details[ 'rank' ];
				if ( ( $cat_rank < $best_rank ) && isset( $category_details[ 'map-marker' ] ) ) {
					$best_rank      = $cat_rank;
					$locationMarker = $category_details[ 'map-marker' ];
				}
			}
		}

		return $locationMarker;
	}

	/**
	 * Set the category properties to be returned by AJAX for this location.
	 *
	 * @param  array $marker
	 *
	 * @return array
	 */
	private function set_location_category_properties( $marker ) {
		if ( ! $this->slplus->currentLocation->isvalid_ID( $marker[ 'id' ] ) ) {
			return $marker;
		}

		$this->addon->set_LocationCategories();

		// If we are looking for a specific category,
		// check to see if it is assigned to this location
		// Category searched for not in array, Skip this one.
		//
		//
		$filterOut = isset( $_POST[ 'formflds' ] ) && isset( $_POST[ 'formflds' ][ 'cat' ] ) && ( $_POST[ 'formflds' ][ 'cat' ] > 0 );
		if ( $filterOut ) {
			$selectedCat = (int) $_POST[ 'formflds' ][ 'cat' ];
			if ( ! in_array( $selectedCat , $this->addon->current_location_categories ) ) {
				return array();
			}
		}

		// Category Count
		//
		$category_count = count( $this->addon->current_location_categories );

		// Category Details
		// If category details is enabled (on by default), return them in the AJAX string.
		//
		$category_names = '';
		if ( $category_count > 0 ) {
			$category_name_array = array();
			foreach ( $this->addon->current_location_categories as $term_id ) {
				$category_info         = $this->addon->get_TermWithTagalongData( $term_id );
				$category_name_array[] = isset( $category_info[ 'name' ] ) ? $category_info[ 'name' ] : '';
			}

			/**
			 * FILTER: slp_category_name_separator
			 * Change the default ', ' separator used between category names.
			 *
			 * @param   string  the character string to be used between each category on the category name results output
			 *
			 * @return string   the character string to be used between each category on the category name results output
			 */
			$category_name_separator = apply_filters( 'slp_category_name_separator' , ', ' );
			$category_names          = implode( $category_name_separator , $category_name_array );
		}

		// Return our modified array
		//
		return array_merge( $marker , array(
			                            'attributes'     => $this->slplus->currentLocation->attributes ,
			                            'categories'     => $this->addon->current_location_categories ,
			                            'category_count' => $category_count ,
			                            'category_names' => $category_names ,
			                            'icon'           => $this->get_location_marker() ,
			                            'iconarray'      => $this->addon->create_string_icon_array() ,
		                            ) );
	}

	/**
	 * Modify the marker if pages is active.
	 *
	 * @param   array $marker
	 *
	 * @return  array
	 */
	private function set_location_pages_properties( $marker ) {
		if ( $this->slplus->SmartOptions->use_pages->is_false ) {
			return $marker;
		}

		// Pages SEO processing
		//
		$use_pages_link  = $this->slplus->is_CheckTrue( $this->addon->options[ 'pages_replace_websites' ] );
		$use_same_window = $this->slplus->is_CheckTrue( $this->addon->options[ 'prevent_new_window' ] );

		$marker[ 'sl_pages_url' ] = $this->set_pages_url( $marker );

		// No using pages link, just update the target window and leave.
		//
		if ( ! $use_pages_link ) {
			if ( $use_same_window ) {
				$marker[ 'web_link' ] = str_replace( "target='_blank'" , "target='_self'" , $marker[ 'web_link' ] );
				$marker[ 'url_link' ] = str_replace( "target='_blank'" , "target='_self'" , $marker[ 'url_link' ] );
			}

			return $marker;
		}

		// Using Pages Link and a public page exists, create link.
		//
		if ( $use_pages_link && ( ! empty( $marker[ 'sl_pages_url' ] ) ) ) {
			$marker[ 'url' ]      = $marker[ 'sl_pages_url' ];
			$marker[ 'web_link' ] = sprintf( "<a href='%s' target='%s' class='storelocatorlink'>%s</a><br/>" , $marker[ 'url' ] , ( $use_same_window ) ? '_self' : '_blank' , $this->slplus->WPML->get_text( 'label_website' ) );
			$marker[ 'url_link' ] = sprintf( "<a href='%s' target='%s' class='storelocatorlink'>%s</a><br/>" , $marker[ 'url' ] , ( $use_same_window ) ? '_self' : '_blank' , $marker[ 'url' ] );

		} else {
			$marker[ 'url' ]      = '';
			$marker[ 'web_link' ] = '';
			$marker[ 'url_link' ] = '';
		}

		return $marker;
	}

	/**
	 * Set the tags property to be returned by AJAX for this location.
	 *
	 * @param array $marker
	 *
	 * @return array mixed
	 */
	private function set_location_tags( $marker ) {
		switch ( $this->addon->options[ 'tag_output_processing' ] ) {
			case 'hide':
				$marker[ 'tags' ] = '';
				break;

			case 'replace_with_br':
				$marker[ 'tags' ] = str_replace( ',' , '<br/>' , $marker[ 'tags' ] );
				$marker[ 'tags' ] = str_replace( '&#044;' , '<br/>' , $marker[ 'tags' ] );
				break;

			case 'as_entered':
			default:
				break;
		}

		return $marker;
	}

	/**
	 * Set the pages URL for the given location, only if the page status is PUBLISH.
	 *
	 * @param array [string]string $marker
	 *
	 * @return string
	 */
	private function set_pages_url( $marker ) {
		$url = '';

		if ( ! empty( $marker[ 'sl_pages_url' ] ) && ( get_post_status( $this->slplus->currentLocation->linked_postid ) === 'publish' ) ) {
			$url = $this->slplus->currentLocation->pages_url;
		}

		return apply_filters( 'slp_pages_url' , $url );
	}
}