var SLP_REPORT = SLP_REPORT || {

    /**
     * Chart management object.
     */
    chart: function () {

        /**
         * Draw the chart.
         */
        this.drawChart = function() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Date');
            data.addColumn('number', 'Queries');
            data.addColumn('number', 'Results');

            // Add Rows
            //
            var data_entries = slp_power.count_dataset.length;
            for (var entrynum = 0 ; entrynum < data_entries ; entrynum++ ) {
                data.addRows([ [slp_power.count_dataset[entrynum].TheDate , parseInt(slp_power.count_dataset[entrynum].QueryCount) , parseInt(slp_power.count_dataset[entrynum].ResultCount)] ]);
            }
            var chart;
            if ( slp_power.chart_type === 'ColumnChart' ) {
                chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
            } else {
                chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
            }
            chart.draw(data, {width: 800, height: 400, pointSize: 4});
        }
    },

    /**
     * Message management object.
     */
    message: function() {

        /**
         * Show no data message.
         */
        this.show_no_data_message = function() {
            jQuery("#chart_div").html(
                '<p>' +
                    slp_power.message_nodata +
                    slp_power.message_chartaftersearch +
                    '</p>'
            );
        }
    }
};


// Document Is Ready...
//
jQuery(document).ready(
    function($) {

        // Make tables sortable
        //
        var tstts = $("#topsearches_table").tablesorter( {sortList: [[1,1]]} );

        var col_count = jQuery('#topresults_table tr td').length;
        var trtts = $("#topresults_table").tablesorter( {sortList: [[col_count,1]]} );

        // Export Results Click
        //
        jQuery('#export_results').click(
            function(e) {
               var data = {
                  action: 'slp_download_report_csv',
                  filename: 'topresults',
                  query: jQuery("[name=topresults]").val(),
                  sort: trtts[0].config.sortList.toString(),
                  all: jQuery("[name=export_all]").is(':checked')
               };
               var params = jQuery.param(data);
               jQuery('#secretIFrame').attr('src', ajaxurl + '?' + params );
            }
        );

        // Export Searches Button Click
        //
         jQuery('#export_searches').click(
            function(e) {
               var data = {
                  action: 'slp_download_report_csv',
                  filename: 'topsearches',
                  query: jQuery("[name=topsearches]").val(),
                  sort: tstts[0].config.sortList.toString(),
                  all: jQuery("[name=export_all]").is(':checked')
               };
                var params = jQuery.param(data);
                jQuery('#secretIFrame').attr('src', ajaxurl + '?' + params );
            }
         );

        if (slp_power.total_searches > 0 ) {
            var chart = new  SLP_REPORT.chart();
            google.load('visualization', '1.0', {'packages':['corechart'], 'callback': chart.drawChart });

        } else {
            var message = new SLP_REPORT.message();
            message.show_no_data_message();
        }
    }
);
