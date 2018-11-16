<?php
defined( 'ABSPATH' ) || exit;

/**
 * The things that modify the Admin / Locations / Import UX.
 *
 * @property        SLPPower                        $addon
 * @property-read   SLP_Settings                    $settings
 */
class SLP_Power_Admin_Locations_Import  extends SLP_Object_With_Objects {
	const subtab_slug = 'import';

	public  $addon;
	private $settings;

	/**
	 * Connect our addon.
	 */
	protected final function initialize() {
		$this->addon = $this->slplus->addon( 'Power' );
	}

	/**
	 * Add the bulk upload form to add locations.
	 *
	 * @param  SLP_Settings    $settings
	 */
	function create_import_subtab( $settings ) {
		$this->settings = $settings;
		$section_params['name'] = __( 'Import', 'slp-power' );
		$section_params['slug'] = self::subtab_slug;

		$this->settings->add_section( $section_params );

		$this->status_bar();
		$this->new_import_upload_csv_group    ( $section_params['slug'] );
		$this->import_remote_file_group       ( $section_params['slug'] );
		$this->import_schedule_group          ( $section_params['slug'] );
		$this->import_file_settings_group     ( $section_params['slug'] );
		$this->import_messages_group          ( $section_params['slug'] );

		$this->configure_javascript_variables();
	}

	/**
	 * Create the import status bar.
	 */
	private function status_bar() {
		require_once( $this->addon->dir . 'include/module/settings/SLP_Settings_import_stats.php' );

		$group_params = array(
			'plugin'        => $this->addon,
			'section_slug'  => self::subtab_slug,
			'group_slug'    => 'status_bar',
			'header'        => '',

		);
		$this->settings->add_group(  $group_params );

		$this->settings->add_ItemToGroup( array(
			'group_params' => $group_params,
			'type'         => 'import_stats',
		) );

	}

	/**
	 * Configure the variables to be passed into the JavaScript code.
	 */
	private function configure_javascript_variables() {
		$manage_locations_vars = array(
				'action'                => !empty( $_REQUEST[ 'action' ] ) ? $_REQUEST[ 'action' ] : '' ,
				'download_file_message' => sprintf( __( 'Download your %s', 'slp-power' ), sprintf( '<a href="%s">%s</a>', SLPLUS_UPLOADURL . 'csv/exported_slp_locations.csv', __( 'locations CSV file.', 'slp-power' ) ) ),
				'text_uploading'        => __( 'Uploading', 'slp-power' ),
				'text_loading'          => __( 'Loading locations from ', 'slp-power' ),
				'text_loaded'           => __( 'Finished loading ', 'slp-power' ),

				'cron_url'          => network_site_url('wp-cron.php'),
				'nonce'             => wp_create_nonce('media-form'),
				'rest_geocode_url'  => get_rest_url(null , SLP_REST_SLUG . '/v2/geocoding' ),
				'rest_imports_url'  => get_rest_url(null , SLP_REST_SLUG . '/v2/imports' ),
				'upload_url'        => admin_url('async-upload.php'),
				);

		wp_localize_script( 'slppower_manage_locations', 'location_import', $manage_locations_vars );

	}

	/**
	 * Get the cron schedule as a formatted HTML string.
	 *
	 * @return string
	 */
	private function create_string_cron_schedule() {
		if ( ! isset( $this->file_meta ) ) { return ''; }
		$html     = '';
		$schedule = wp_get_schedule( 'cron_csv_import', array( 'import_csv', $this->file_meta ) );
		if ( ! empty( $schedule ) ) {
			$html =
				sprintf( __( 'CSV file imports are currently scheduled to run %s.', 'slp-power' ), $schedule ) .
				'<br/><br/>';
		} else {
			$html = __( 'There are no scheduled imports.' , 'slp-power' );
		}
		return $html;
	}

	/**
	 * File Settings Group
	 *
	 * @param string $section_slug
	 */
	private function import_file_settings_group( $section_slug ) {
		$group_params['header'      ] = __( 'File Settings', 'slp-power' );
		$group_params['group_slug'  ] = 'file_settings';
		$group_params['section_slug'] = $section_slug;
		$group_params['intro'       ] = __( 'These settings apply to both Upload CSV File and Remote File Retrieval.' , 'slp-power' );
		$group_params['plugin'      ] = $this->addon;
		$this->settings->add_group(  $group_params );

		$this->settings->add_ItemToGroup( array(
			                                  'group_params'  => $group_params,
			                                  'type'          => 'checkbox',
			                                  'option'        => 'csv_skip_geocoding',
			                                  'label'         => __( 'Skip Geocoding', 'slp-power' ),
			                                  'description'   => __( 'Do not check with the Geocoding service to get latitude/longitude.  Locations without a latitude/longitude will NOT appear on map base searches.', 'slp-power' ),
			                                  'classes'       => array( 'quick_save' )
		));

		$this->settings->add_ItemToGroup( array(
			                                  'group_params'  => $group_params,
			                                  'type'          => 'checkbox',
			                                  'option'        => 'load_data',
			                                  'label'         => __( 'Load Data', 'slp-power' ),
			                                  'description'   => __( 'If checked use the faster MySQL Load Data method of file processing.', 'slp-power' ) . ' ' .
			                                                     sprintf( __( 'Only base plugin data can be loaded, see the <a href="%s">approved field name list</a>.', 'slp-power' ), $this->slplus->support_url ),
			                                  'classes'       => array( 'quick_save' )

		) );

		$this->settings->add_ItemToGroup( array(
			                                  'group_params'  => $group_params,
			                                  'type'          => 'checkbox',
			                                  'option'        => 'csv_clear_messages_on_import',
			                                  'label'         => __( 'Clear Messages', 'slp-power' ),
			                                  'description'   => __( 'Clear import messages at the start of each new import.', 'slp-power' ),
			                                  'classes'       => array( 'quick_save' )
		));

		$this->settings->add_ItemToGroup( array(
			                                  'group_params'  => $group_params,
			                                  'option'        => 'csv_duplicates_handling',
			                                  'type'          => 'dropdown',
			                                  'label'         => __( 'Duplicates Handling', 'slp-power' ),
			                                  'description'   => __( 'How should duplicates be handled? ', 'slp-power' ) .
			                                                     __( 'Duplicates are records that match on name and complete address with country. ', 'slp-power' ) .
			                                                     __( 'Add (default) will add new records when duplicates are encountered. ', 'slp-power' ) . '<br/>' .
			                                                     __( 'Skip will not process duplicate records. ', 'slp-power' ) . '<br/>' .
			                                                     __( 'Update will update duplicate records. ', 'slp-power' ) .
			                                                     __( 'To update name and address fields the CSV must have the ID column with the ID of the existing location.', 'slp-power' ),
			                                  'custom'        => array(
				                                  array( 'label' => __( 'Add', 'slp-power' ), 'value' => 'add' ),
				                                  array( 'label' => __( 'Skip', 'slp-power' ), 'value' => 'skip' ),
				                                  array( 'label' => __( 'Update', 'slp-power' ), 'value' => 'update' ),
			                                  ),
			                                  'classes'       => array( 'quick_save' )
		                                  ) );

	}

	/**
	 * Import Messages Group
	 *
	 * @param string $section_slug
	 */
	private function import_messages_group( $section_slug ) {
		if ( ! is_object( $this->addon->csvImporter ) ) return;

		$group_params['header'      ] = __( 'Import Messages', 'slp-power' );
		$group_params['group_slug'  ] = 'import_messages';
		$group_params['section_slug'] = $section_slug;
		$group_params['plugin'      ] = $this->addon;
		$this->settings->add_group(  $group_params );

		$this->settings->add_ItemToGroup( array(
			                                  'group_params'  => $group_params,
			                                  'type'          => 'details',
			                                  'label'         => 'Import Messages',
			                                  'custom'        => $this->addon->csvImporter->messages->get_message_string()
		                                  ));

		if ( $this->addon->csvImporter->messages->exist() ) {
			$clear_text = __( 'Clear import messages.', 'slp-power' );
			$this->settings->add_ItemToGroup( array(
				                                  'group_params'  => $group_params,
				                                  'type'         => 'hyperbutton',
				                                  'button_label'  => $clear_text,
				                                  'id'            => 'import_messages_clear',
				                                  'onClick'       => 'SLPPOWER_ADMIN_LOCATIONS.messages.clear_import_messages()'
			                                  )
			);
		}
	}

	/**
	 * Remote File Retrieval Group
	 *
	 * @param string    $section_slug
	 */
	private function import_remote_file_group( $section_slug ) {
		$group_params['header'      ] = __( 'Remote File Retrieval', 'slp-power' );
		$group_params['group_slug'  ] = 'remote_file';
		$group_params['section_slug'] = $section_slug;
		$group_params['plugin'      ] = $this->addon;
		$this->settings->add_group(  $group_params );


		$this->settings->add_ItemToGroup( array(
			                                  'group_params'  => $group_params,
			                                  'type'          => 'details',
			                                  'label'         => '' ,
			                                  'description'   =>  sprintf( __( 'The current WordPress time (GMT) is %s.', 'slp-power' ), current_time( 'mysql', true ) )
		                                  ));

		$this->settings->add_ItemToGroup( array(
			                                  'group_params'  => $group_params,
			                                  'option'        => 'csv_file_url',
			                                  'classes'       => array( 'quick_save' ) ,
			                                  'label'         => __( 'CSV File URL'                                       , 'slp-power' ),
			                                  'description'   => __( 'Enter a full URL for a CSV file you wish to import' , 'slp-power' ),
		                                  ) );

		$this->settings->add_ItemToGroup( array(
			                                  'group_params'  => $group_params,
			                                  'option'        => 'cron_import_recurrence',
			                                  'classes'       => array( 'quick_save' )				,
			                                  'type'          => 'dropdown',
			                                  'label'         => __( 'Import Schedule', 'slp-power' ),
			                                  'description'   => __( 'How often to fetch the file from the URL. '                             , 'slp-power' ) .
			                                                     __( 'None loads the remote file immediately with no background processing. ' , 'slp-power' ) .
			                                                     __( 'At loads the file one time on or after the time specified. '            , 'slp-power' ) .
			                                                     __( 'Set to none and leave the URL blank to clear the cron job.  '           , 'slp-power' )
			                                  ,
			                                  'custom'        => array(
				                                  array( 'label' => __( 'None'        , 'slp-power' ), 'value' => 'none'      ),
				                                  array( 'label' => __( 'At'          , 'slp-power' ), 'value' => 'at'        ),
				                                  array( 'label' => __( 'Hourly'      , 'slp-power' ), 'value' => 'hourly'    ),
				                                  array( 'label' => __( 'Twice Daily' , 'slp-power' ), 'value' => 'twicedaily'),
				                                  array( 'label' => __( 'Daily'       , 'slp-power' ), 'value' => 'daily'     ),
			                                  ))
		);

		$this->settings->add_ItemToGroup( array(
			                                  'group_params'  => $group_params,
			                                  'option'        => 'cron_import_timestamp',
			                                  'classes'       => array( 'quick_save' )				,
			                                  'label'         => __( 'Import Time'                                                                                , 'slp-power' ),
			                                  'description'   => __( 'What time to run the recurring import from this URL.  '                                     , 'slp-power' ) .
			                                                     __( 'WordPress cron is not exact, it executes the next time a visitor comes to your site.  '     , 'slp-power' ) .
			                                                     __( 'WordPress times are UTC/GMT time NOT local time.  '                                         , 'slp-power' ) .
			                                                     sprintf( '<a href="%s" target="slp">%s"</a>', 'http://php.net/manual/en/datetime.formats.php', __('PHP Date and Time formats', 'slp-power' ) ) .
			                                                     __( 'are acceptable, such as "now" or "14:00" or "sunday"' , 'slp-power')
		                                  ));


		$this->settings->add_ItemToGroup( array(
			                                  'group_params'  => $group_params,
			                                  'type'          => 'hyperbutton',
			                                  'id'            => 'import_button',
			                                  'button_label'  => __( 'Import Locations', 'slp-power' )
		                                  ));


	}

	/**
	 * Add the import messages group.
	 *
	 * @param string $section_slug
	 */
	private function import_schedule_group( $section_slug ) {
		$group_params['header'      ] = __( 'Scheduled Activity', 'slp-power' );
		$group_params['group_slug'  ] = 'schedule';
		$group_params['section_slug'] = $section_slug;
		$group_params['plugin'      ] = $this->addon;
		$this->settings->add_group(  $group_params );


		$this->settings->add_ItemToGroup( array(
			                                  'group_params'  => $group_params,
			                                  'type'          => 'details',
			                                  'description'   =>  $this->create_string_cron_schedule()
		                                  ));
	}

	/**
	 * Upload CSV Group
	 *
	 * @param   string   $section_slug
	 */
	private function new_import_upload_csv_group( $section_slug ) {
		SLP_Power_Text::get_instance();

		$group_params['header'      ] = __( 'Upload A File', 'slp-power' );
		$group_params['group_slug'  ] = 'upload_a_file';
		$group_params['section_slug'] = $section_slug;
		$group_params['plugin'      ] = $this->addon;
		$this->settings->add_group(  $group_params );

		$this->settings->add_ItemToGroup(array(
			                                 'group_params'  => $group_params,
			                                 'type'          => 'subheader'  ,
			                                 'label'         => ''           ,
			                                 'description'   => __( 'CSV files need a header row to define the columns. ' , 'slp-power' ) .
			                                                    __( 'Files should be in UTF-8 format with comma delimiters. ' , 'slp-power' ) .
			                                                    '<p>'.
			                                                    __( 'Imports are a 3-step process. ', 'slp-power' ).
			                                                    '<br/>'.
			                                                    __( '1) The file is uploaded to the WP Media Library. ', 'slp-power' ).
			                                                    '<br/>'.
			                                                    __( '2) A background process reads the CSV into the locations list. ', 'slp-power' ).
			                                                    __( 'It may run this process multiple times on slow servers or with lists over 2500 locations. ', 'slp-power' ).
			                                                    '<br/>'.
			                                                    __( '3) A background process geocodes the locations without a latitude or longitude. ', 'slp-power' ).
			                                                    __( 'It may run this process multiple times if the goecoding servers are slow or you have hit your daily limit on Google goecoding services. ', 'slp-power' ).
			                                                    '</p>'.
			                                                    '<p>'.
			                                                    __( 'Importing a file twice before the first file is done processing can result in duplicate entries. ' , 'slp-power' ) .
			                                                    __( 'You do not have to stay on this import page after the blue uploaded notification or the Import progress box appears. ' , 'slp-power' ) .
			                                                    __( 'Check progress by coming back to this page and checking messages at the bottom of this page or click on the CSV file in the media library. ' , 'slp-power' ) .
			                                                    __( 'Having issues with the import not doing what you expect? ' , 'slp-power' ) .
			                                                    __( 'Excel likes to mangle CSV files; Try using Google Sheets to create and maintain your CSV files. ' , 'slp-power' ) .
			                                                    '</p>'.
			                                                    '<p>'.
			                                                    $this->slplus->Text->get_web_link( 'docs_for_csv_import' ) .
			                                                    '</p>'
		                                 ));

		// Check Max Files
		//
		require( SLPLUS_PLUGINDIR . 'include/module/location/SLP_Location_Manager.php' );
		if ( $this->slplus->Location_Manager->has_max_locations() ) {

			$this->settings->add_ItemToGroup(array(
				                                 'group_params'  => $group_params,
				                                 'type'          => 'subheader',
				                                 'label'         => '',
				                                 'description'   => __( 'You have reached the maximum locations allowed for your plan.' , 'slp-power' )
			                                 ));

			return;
		}

		$this->settings->add_ItemToGroup(array(
			                                 'group_params'  => $group_params,
			                                 'type'          => 'file',
			                                 'id'            => 'csv_file',
			                                 'name'          => 'async-upload',
			                                 'button_text'   => __( 'Select Your CSV File' , 'slp-power' ),
			                                 'attributes'    => array(
			                                 	'accept'        => 'accept=".csv"'
			                                 )
		                                 ));
	}
}

global $slplus;
if ( is_a( $slplus, 'SLPlus' ) ) {
	$slplus->add_object( new SLP_Power_Admin_Locations_Import() );
}