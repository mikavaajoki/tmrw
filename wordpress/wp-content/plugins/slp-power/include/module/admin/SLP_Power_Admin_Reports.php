<?php
defined( 'ABSPATH'     ) || exit;

/**
 * Admin Report System for Power
 *
 * @property        SLPPower                $addon
 * @property        SLP_Power_Data_Reports  $data
 * @property-read   string                  $end_date       The ending report range date.
 * @property-read   int                     $report_limit   Limit of records to report.
 * @property-read   SLP_Settings            $settings
 * @property-read   string                  $start_date     The starting report range date.
 *
 */
class SLP_Power_Admin_Reports extends SLPlus_BaseClass_Object {
    public $addon;
    public $data;
    private $end_date;
    private $report_limit;
    private $settings;
    private $start_date;

    /**
     * Things we do at the start.
     */
    function initialize( ) {
    	$this->addon = $this->slplus->addon( 'Power' );
	    new SLP_Power_Admin_Reports_Text();

        $this->settings = new SLP_Settings(array(
            'name'              => SLPLUS_NAME . __(' - Reporting','slp-power'),
            'form_action'       => '',
            'save_text'         => __('Save Settings','slp-power')
            ));

        // Start of date range to report on
        // default: 30 days ago
        //
        $this->start_date =
            isset($_POST['start_date'])                     ?
                $_POST['start_date']                        :
                date('Y-m-d',time() - MONTH_IN_SECONDS )  ;

        // Start of date range to report on
        // default: today
        //
        $this->end_date =
            isset($_POST['end_date'])               ?
                $_POST['end_date']                  :
                date('Y-m-d',time()) . ' 23:59:59'  ;
        if ( ! preg_match( '/\d\d:\d\d$/' , $this->end_date ) ) {
            $this->end_date .= ' 23:59:59';
        }

	    $this->data = SLP_Power_Data_Reports::get_instance();

	    // Prepare Data Summary
        //
        $this->data->summarize_data( $this->start_date , $this->end_date );
    }

    /**
     * Add Graph to reports.
     *
     * @param $section_name
     */
    private function add_Graph( $section_name ) {
        $panel_name = __('Report Summary', 'slp-power');
        $this->settings->add_ItemToGroup(
            array(
                'section'       => $section_name                    ,
                'group'         => $panel_name                      ,
                'show_label'    => false                            ,
                'type'          => 'custom'                         ,
                'custom'        => '<div id="chart_div"></div>'
            )
        );
    }

    /**
     * Add the standard NavBar to the report page.
     */
    function add_NavBarToTab() {
        $this->settings->add_section(
            array(
                'name'          => 'Navigation',
                'div_id'        => 'navbar_wrapper',
                'description'   => SLP_Admin_UI::get_instance()->create_Navbar(),
                'innerdiv'      => false,
                'is_topmenu'    => true,
                'auto'          => false,
            )
        );
    }

    /**
     * Add the Report Section.
     */
    function add_ReportSection() {
        $section_name = __('Reports','slp-power');
        $this->settings->add_section(
            array(
                'name'          => $section_name,
                'auto'          => true
            )
        );

        $this->add_Graph( $section_name );
        $this->add_ReportSection_ParametersPanel( $section_name );
        $this->add_ReportSection_SearchReportPanel( $section_name );
        $this->add_ReportSection_ResultsReportPanel( $section_name );
        $this->add_ReportSection_DownloadPanel( $section_name );
    }

    /**
     * Add the download panel to the reports section.
     *
     * @param $section_name
     */
    function add_ReportSection_DownloadPanel( $section_name ) {
        $panel_name = __('Export To CSV' , 'slp-power');
        $this->settings->add_ItemToGroup(
            array(
                'section'       => $section_name                    ,
                'group'         => $panel_name                      ,
                'show_label'    => false                            ,
                'type'          => 'custom'                         ,
                'custom'        =>
                    '<div class="form_entry">'.
                        '<label for="export_all">'.__('Export all records','slp-power').'</label>' .
                        '<input id="export_all" type="checkbox"  name="export_all" value="1">'.
                    '</div>'.
                    '<div class="form_entry">' .
                        '<input id="export_searches" class="button-secondary button-export" type="button" value="'.__('Top Searches','slp-power').'"><br/>'.
                        '<input id="export_results"  class="button-secondary button-export" type="button" value="'.__('Top Results','slp-power').'">'.
                    '</div>' .
                    '<iframe id="secretIFrame" src="" style="display:none; visibility:hidden;"></iframe>'
            )
        );
    }

    /**
     * Add the Parameters Section to the Report Panel
     *
     * @param string $section_name
     */
    function add_ReportSection_ParametersPanel( $section_name ) {
        $panel_name = __('Report Parameters','slp-power');

        // Start Date Entry
        //
        $this->settings->add_ItemToGroup(
            array(
                'section'       => $section_name                    ,
                'group'         => $panel_name                      ,
                'label'         => __('Start Date','slp-power')   ,
                'setting'       => 'start_date'                     ,
                'use_prefix'    => false                            ,
                'type'          => 'text'                           ,
                'value'         => $this->start_date                ,
                'description'   =>
                    __('Only show data on or after this date.  YYYY-MM-DD.', 'slp-power')
            )
        );


        // End Date
        $this->settings->add_ItemToGroup(
            array(
                'section'       => $section_name                    ,
                'group'         => $panel_name                      ,
                'label'         => __('End Date','slp-power')     ,
                'setting'       => 'end_date'                       ,
                'use_prefix'    => false                            ,
                'type'          => 'text'                           ,
                'value'         => $this->end_date                  ,
                'description'   =>
                    __('Only show data on or before this date.  YYYY-MM-DD hh:mm:ss.', 'slp-power')
            )
        );

        // How many detail records to report back
        // default: 10
        //
        $this->report_limit =
            isset( $_POST['report_limit'] ) ?
                $_POST['report_limit']      :
                '10'                        ;
        $this->settings->add_ItemToGroup(
            array(
                'section'       => $section_name                    ,
                'group'         => $panel_name                      ,
                'label'         => __('Report Limit','slp-power') ,
                'setting'       => 'report_limit'                   ,
                'use_prefix'    => false                            ,
                'type'          => 'text'                           ,
                'value'         => $this->report_limit              ,
                'description'   =>
                    __('Limit the report to this many detail records.  Default: 10.  Recommended maximum: 500.', 'slp-power')
            )
        );

        // Counts
        //
        $this->settings->add_ItemToGroup(
            array(
                'section'       => $section_name                    ,
                'group'         => $panel_name                      ,
                'show_label'    => false                            ,
                'type'          => 'custom'                         ,
                'custom'   =>
                    sprintf(
                        '<div class="report_line total">' .
                        __('Total searches: <strong>%s</strong>', 'slp-power'). "<br/>" .
                        __('Total results: <strong>%s</strong>', 'slp-power').  "<br/>" .
                        __('Days with activity: <strong>%s</strong>', 'slp-power'). "<br/>" .
                        '</div>',
                        $this->data->total_searches,
                        $this->data->total_results,
                        count( $this->data->counts_dataset )
                    )
            )
        );
    }


    /**
     * Add the Results Panel To The Report Section
	 * @param $section_name
	 */
    private function add_ReportSection_ResultsReportPanel( $section_name ) {
        $panel_name = sprintf(__('Top %s Results Returned', 'slp-power') , $this->report_limit);

        $slpColumnHeaders = array(
            __('Store'  ,'slp-power'),
            __('City'   ,'slp-power'),
            __('State'  ,'slp-power'),
            __('Zip'    ,'slp-power'),
            __('Tags'   ,'slp-power'),
            __('Total'  ,'slp-power')
        );
        $slpDataLines = array(
            array('columnName' => 'sl_store',   'columnClass'=> ''            ),
            array('columnName' => 'sl_city',    'columnClass'=> ''            ),
            array('columnName' => 'sl_state',   'columnClass'=> ''            ),
            array('columnName' => 'sl_zip',     'columnClass'=> ''            ),
            array('columnName' => 'sl_tags',    'columnClass'=> ''            ),
            array('columnName' => 'ResultCount','columnClass'=> 'alignright'  ),
        );

        $this->data->set_top_results( $this->start_date , $this->end_date , $this->report_limit );

        $this->settings->add_ItemToGroup(
            array(
                'section'       => $section_name                    ,
                'group'         => $panel_name                      ,
                'show_label'    => false                            ,
                'type'          => 'custom'                         ,
                'custom'   =>
                    $this->createstring_DataTable(
                        "top,,{$this->start_date},{$this->end_date},{$this->report_limit}",
                        $this->data->top_results,
                        $slpColumnHeaders,
                        $slpDataLines,
                        __('topresults','slp-power')
                    )
            )
        );

    }

    /**
     * Add the Search Report Panel To The Report Section
	 * @param $section_name
	 */
    private function add_ReportSection_SearchReportPanel( $section_name ) {
        $panel_name = sprintf(__('Top %s Addresses Searched', 'slp-power'),$this->report_limit);

        $slpColumnHeaders = array(
            __('Address','slp-power'),
            __('Total','slp-power')
        );
        $slpDataLines = array(
            array('columnName' => 'slp_repq_address', 'columnClass'=> ''            ),
            array('columnName' => 'QueryCount',       'columnClass'=> 'alignright'  ),
        );

        $this->data->set_top_searches( $this->start_date , $this->end_date , $this->report_limit );

        $this->settings->add_ItemToGroup(
            array(
                'section'       => $section_name                    ,
                'group'         => $panel_name                      ,
                'show_label'    => false                            ,
                'type'          => 'custom'                         ,
                'custom'   =>
                    $this->createstring_DataTable(
                        "addr,{$this->start_date},{$this->end_date},{$this->report_limit}",
                        $this->data->top_searches,
                        $slpColumnHeaders,
                        $slpDataLines,
                        __('topsearches','slp-power')
                    )
            )
        );
    }

    /**
     * Add the Settings Section.
     */
    private function add_SettingsSection() {
        $section_name = __('Settings','slp-power');


        $this->settings->add_section(
            array(
                'name'          => $section_name,
                'auto'          => true ,
                'description'   =>
                    ( $this->slplus->SmartOptions->reporting_enabled->is_false ) ?
                    __('Enable reporting to start recording location search data. ' , 'slp-power') .
                    __('Once enabled the report section will appear on this tab.'   , 'slp-power') :
                    ''
            )
        );
    }



    /**
     * Create a data table.
     *
	 * @param $tag
	 * @param $thisDataset
	 * @param $columnHeaders
	 * @param $columnDataLines
	 * @param $Qryname
	 *
	 * @return string
	 */
    private function createstring_DataTable( $tag, $thisDataset, $columnHeaders, $columnDataLines, $Qryname) {

        $thisQryname = strtolower(preg_replace('/\s/','_',$Qryname));
        $thisQryvalue= htmlspecialchars($tag,ENT_QUOTES,'UTF-8');

        $thisSectionDesc =
            '<input type="hidden" name="'.$thisQryname.'" value="'.$thisQryvalue.'">' .
            '<table id="'.$Qryname.'_table" cellpadding="0" cellspacing="0">' .
            '<thead>' .
            '<tr>';

        foreach ($columnHeaders as $columnHeader) {
            $thisSectionDesc .= "<th>$columnHeader</th>";
        }

        $thisSectionDesc .=  '</tr>' .
            '</thead>' .
            '<tbody>';

        $slpReportRowClass = 'rowon';
        foreach ($thisDataset as $thisDatapoint) {
            $slpReportRowClass = ($slpReportRowClass === 'rowoff') ? 'rowon' : 'rowoff';
            $thisSectionDesc .= '<tr>';
            foreach ($columnDataLines as $columnDataLine) {
                $columnName = $columnDataLine['columnName'];
                $columnClass= $columnDataLine['columnClass'];
                $thisSectionDesc .= sprintf(
                    '<td class="%s %s">%s</td>',
                    $columnClass,
                    $slpReportRowClass,
                    $thisDatapoint->$columnName
                );
            }
            $thisSectionDesc .= '</tr>';
        }

        $thisSectionDesc .=
            '</tbody>' .
            '</table>'
        ;

        return $thisSectionDesc;
    }

	/**
	 * Delete older history records.
	 */
    private function delete_older_history() {
    	if ( empty( $_REQUEST[ 'options_nojs' ] ) || empty( $_REQUEST[ 'options_nojs' ][ 'delete_history_before_this_date' ] ) ) {
    		return;
	    }
	    $delete_date = esc_sql( $_REQUEST[ 'options_nojs' ][ 'delete_history_before_this_date' ] );
    	$total_deleted = $this->data->delete_history_before( $delete_date );
    	if ( $total_deleted !== false ) {
		    $this->slplus->notifications->add_notice( 'info', sprintf( __( 'Deleted %d report history records logged before %s.', 'slp-power' ), $total_deleted , $delete_date ) );
	    }
    }


    /**
     * Build the reports tab content.
     */
    function render() {
        $this->save_Settings();
        $this->add_NavBarToTab();

        // If reporting is enabled put the report section first.
        //
        if ( $this->slplus->SmartOptions->reporting_enabled->is_true ) {
            $this->add_ReportSection();
        }

        $this->add_SettingsSection();

        $this->settings->render_settings_page();
    }

    /**
     * Save settings when appropriate.
     */
    function save_Settings() {
	    if ( empty( $_REQUEST['action'] ) ) {
	    	return;
	    }
	    if (  $_REQUEST['action'] !== 'update' ) {
	    	return;
	    }
	    if ( empty( $_REQUEST['_wp_http_referer'] ) ) {
	    	return;
	    }
        if ( substr( $_REQUEST['_wp_http_referer'] , -strlen('page=slp_reports') ) !== 'page=slp_reports' ) {
            return;
        }

        $this->delete_older_history();

	    SLP_SmartOptions::get_instance()->save();
        $this->slplus->WPOption_Manager->update_wp_option( $this->addon->option_name , $this->addon->options );
    }

}
