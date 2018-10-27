/*global uci_importer */
jQuery( document ).ready(function() {
    var get_page = uci_importer.requestpage;
    if(get_page == 'sm-uci-dashboard') {
        //piechart();
        //linechart();
        UCIPieChart();
        UCILineChart();
        UCIBarStackedChart();
    }
});

function UCILineChart() {
    //var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    var mon = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    var months = new Array();
    var today = new Date();
    var aMonth = today.getMonth();
    var i;
    for (i=0; i<12; i++) {
        aMonth++;
        if (aMonth > 11) {
            aMonth = 0;
        }
        months[i] = mon[aMonth];
    }
    var dataSets = [];
    var config = {};
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            'action'   : 'FetchLineChartData',
        },
        dataType: 'json',
        cache: false,
        success: function(result) {
            var colorNames = Object.keys(window.chartColors);
            var index = 0;
            jQuery.each(result, function(key, value) {
                index++;
                var colorName = colorNames[index % colorNames.length];
                var data = {};
                var plot_data = [];
                data.label = key;
                data.backgroundColor = colorName;
                data.borderColor = colorName;
                var value_set = value.split(',');
                jQuery.each(value_set, function(k, v) {
                   plot_data.push(parseInt(v));
                });
                data.data = plot_data;
                data.fill = false;
                dataSets.push(data);
            });
            config = {
                type: 'line',
                data: {
                    labels: months,
                    datasets: dataSets
                },
                options: {
                    responsive: true,
                    title:{
                        display:true,
                        text:'Reports of Last 12 Months'
                    },
                    tooltips: {
                        mode: 'index',
                        intersect: false,
                    },
                    hover: {
                        mode: 'nearest',
                        intersect: true
                    },
                    scales: {
                        xAxes: [{
                            display: true,
                            scaleLabel: {
                                display: true,
                                labelString: 'Month'
                            }
                        }],
                        yAxes: [{
                            display: true,
                            scaleLabel: {
                                display: true,
                                labelString: 'Value'
                            }
                        }]
                    }
                }
            };
            var ctx = document.getElementById("uci-line-chart").getContext("2d");
            new Chart(ctx, config);
        }
    });
}

function UCIPieChart() {
    var config = {};
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            'action'   : 'FetchPieChartData',
        },
        dataType: 'json',
        cache: false,
        success: function(data) {
            //console.log(data);
            //var val = JSON.parse(data);
            var plot_data = [];
            var plot_label = [];
            var backgroundColor = [];
            jQuery.each(data, function(key, value) {
                var colorNames = Object.keys(window.chartColors);
                var colorName = colorNames[key % colorNames.length];
                backgroundColor.push(colorName);
                jQuery.each(value, function(item, val) {
                    plot_label.push(item);
                    plot_data.push(parseInt(val));
                });
            });
            config = {
                //type: 'doughnut',
                data: {
                    datasets: [{
                        data: plot_data,
                        backgroundColor: backgroundColor,
                        label: 'My dataset' // for legend
                    }],
                    labels: plot_label
                },
                options: {
                    responsive: true,
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Total no of records based on module'
                    },
                    scale: {
                        ticks: {
                            beginAtZero: true
                        },
                        reverse: false
                    },
                    animation: {
                        animateRotate: false,
                        animateScale: true
                    }
                }
            };
            var ctx = document.getElementById("uci-pie-chart");
            window.myPolarArea = Chart.PolarArea(ctx, config);
            //var ctx = document.getElementById("chart-area").getContext("2d");
            //window.myPie = new Chart(ctx, config);

            /* if (val['label'] == 'No Imports Yet') {
                document.getElementById('pieStats').innerHTML = "<h2 style='color: red;text-align: center;padding-top: 100px;' >No Imports Yet</h2>";
                return false;
            }
            var jdata = jQuery.parseJSON(data);
            /* Morris.Donut({
                element: 'pieStats',
                data: jdata//[
                //{label: val[0][0], value: value[0][1]}
                //{label: "page", value: 30},
                //{label: "custompost", value: 20}
                //]
            }); */
        }
    });
}

function UCIBarStackedChart() {
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            'action'   : 'FetchBarStackedChartData',
        },
        dataType: 'json',
        cache: false,
        success: function(data) {
            var labels = [];
            var plot_created = [];
            var plot_updated = [];
            var plot_skipped = [];
            var plot_name;
            jQuery.each(data, function(key, value) {
                labels.push(key);
                jQuery.each(value, function(item, val) {
                    if(item == 'created') {
                        plot_created.push(parseInt(val));
                    }
                    if(item == 'updated') {
                        plot_updated.push(parseInt(val));
                    }
                    if(item == 'skipped') {
                        plot_skipped.push(parseInt(val));
                    }
                });
            });
            //console.log(plot_created);
            //console.log(plot_updated);
            //console.log(plot_skipped);
            var barChartData = {
                labels: labels,
                datasets: [{
                    label: 'Inserted',
                    backgroundColor: window.chartColors.red,
                    data: plot_created
                }, {
                    label: 'Updated',
                    backgroundColor: window.chartColors.blue,
                    data: plot_updated
                }, {
                    label: 'Skipped',
                    backgroundColor: window.chartColors.green,
                    data: plot_skipped
                }]

            };
            var ctx = document.getElementById("uci-bar-stacked-chart").getContext("2d");
            window.myBar = new Chart(ctx, {
                type: 'bar',
                data: barChartData,
                options: {
                    title:{
                        display:true,
                        text:"Module based event information"
                    },
                    tooltips: {
                        mode: 'index',
                        intersect: false
                    },
                    responsive: true,
                    scales: {
                        xAxes: [{
                            stacked: true,
                        }],
                        yAxes: [{
                            stacked: true
                        }]
                    }
                }
            });
        }
    });
}

function linechart() {
    jQuery.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            'action'   : 'FetchLineChartData',
            'postdata' : 'secondchartdata',
        },
        dataType: 'text',
        cache: false,
        success: function(result) {
            var labels = [];
            JSON.parse(result, function (key, value) {
                labels.push(key);

            });
            labels.shift();
            labels.pop();
            labels.pop();
            var jdata = jQuery.parseJSON(result);
            //var val = JSON.parse(result);
            var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            Morris.Line({
                element: 'lineStats',
                data   : jdata,
                xkey: 'year',
                //ykeys: ['Posts', 'Pages','Users','WooCommerce','WooCommerceVariations','WooCommerceCoupons','WooCommerceOrders','WooCommerceRefunds','MarketPress','MarketPressVariations','WPeCommerce','eShop','CustomerReviews'],
                //labels: ['Posts', 'Pages','Users','WooCommerce','WooCommerceVariations','WooCommerceCoupons','WooCommerceOrders','WooCommerceRefunds','MarketPress','MarketPressVariations','WPeCommerce','eShop','CustomerReviews'],
                ykeys: labels,
                labels: labels,
                //lineColors:['gray','red','blue','black','orange','green'],
                xLabelFormat: function(x) { // <--- x.getMonth() returns valid index
                    var month = months[x.getMonth()];
                    return month;
                },
                dateFormat: function(x) {
                    var month = months[new Date(x).getMonth()];
                    return month;
                },

            });
        }
    });
}
