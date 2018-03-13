(function($, Drupal, drupalSettings){
    'use strict';
    Drupal.behaviors.jsTestD8Chart = {
        attach: function (context, settings) {
            //$(function(){

            //alert('ok!');
            google.charts.load('current', {'packages':['line']});
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {

                var data = new google.visualization.DataTable();
                data.addColumn('number', 'Day');
                data.addColumn('number', 'Guardians of the Galaxy');
                data.addColumn('number', 'The Avengers');
                data.addColumn('number', 'Transformers: Age of Extinction');

                data.addRows(drupalSettings.TestD8.chart);

                var options = {
                    chart: {
                        title: 'Box Office Earnings in First Two Weeks of Opening',
                        subtitle: 'in millions of dollars (USD)'
                    },
                    width: "100%",
                    height: 300,
                    vAxis: {
                      title: 'Scores'
                    }
                };

                var chart = new google.charts.Line(document.getElementById('chart_div'));
                chart.draw(data, google.charts.Line.convertOptions(options));
            }

            //});
        }
    };
})(jQuery, Drupal, drupalSettings);

