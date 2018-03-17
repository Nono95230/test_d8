(function($, Drupal, drupalSettings){
    'use strict';
    Drupal.behaviors.jsTestD8Chart = {
        attach: function (context, settings) {

            function getQueryStringValue(key){
                return decodeURIComponent(window.location.search.replace(new RegExp("^(?:.*[&\\?]" + encodeURIComponent(key).replace(/[\.\+\*]/g, "\\$&") + "(?:\\=([^&]*))?)?.*$", "i"), "$1"));
            }

            var render = new Array(),
                index,
                id,
                rows = drupalSettings.TestD8.chart.rows,
                disabledTabs = new Array();

            for (index in rows){
                var tmp = rows[index];
                for (id in tmp){

                    // désactive les onglets sans données
                    if (tmp[id].length == 0){
                        disabledTabs.push(Number(index));
                    } else {


                    }
                }
            }

            // active l'onglet du theme du test fini
            var tabId = getQueryStringValue('tab'),
                activeTab = 0;
            if (tabId){
                dance:
                for (index in rows){
                    activeTab = index;
                    for (id in rows[index]){
                        if (id == tabId){
                            activeTab = index;
                            break dance;
                        }
                    }
                }
            }

            $(function(){
                $("#tabs").tabs({
                    active: activeTab,
                    disabled: disabledTabs,
                    activate: function(event, ui){
                        drawChart[ui.newTab.index()]();
                    }
                });
            });



            var drawChart = [];
            drawChart['0'] = function(){
                var data = new google.visualization.DataTable();
                data.addColumn('string');
                data.addColumn('number');
                data.addRows(drupalSettings.TestD8.chart.Webmaster);
                var options = {
                    legend: { position:'none' },
                    chart: { title: 'Évolution de vos résultats pour le thème Webmaster' },
                    width: "100%",
                    height: 300,
                    vAxis: { /*title: 'Scores',*/ viewWindow: {min:0, max:drupalSettings.TestD8.chart.number_of_questions} }
                };
                var chart = new google.charts.Line(document.getElementById('chart-1'));
                chart.draw(data, google.charts.Line.convertOptions(options));
            }
            drawChart['1'] = function(){
                var data = new google.visualization.DataTable();
                data.addColumn('string');
                data.addColumn('number');
                data.addRows(drupalSettings.TestD8.chart.Webmaster);
                var options = {
                    legend: { position:'none' },
                    chart: { title: 'Évolution de vos résultats pour le thème Themer' },
                    width: "100%",
                    height: 300,
                    vAxis: { /*title: 'Scores',*/ viewWindow: {min:0, max:drupalSettings.TestD8.chart.number_of_questions} }
                };
                var chart = new google.charts.Line(document.getElementById('chart-2'));
                chart.draw(data, google.charts.Line.convertOptions(options));
            }
            drawChart['2'] = function(){
                var data = new google.visualization.DataTable();
                data.addColumn('string');
                data.addColumn('number');
                data.addRows(drupalSettings.TestD8.chart.Webmaster);
                var options = {
                    legend: { position:'none' },
                    chart: { title: 'Évolution de vos résultats pour le thème Développement' },
                    width: "100%",
                    height: 300,
                    vAxis: { /*title: 'Scores',*/ viewWindow: {min:0, max:drupalSettings.TestD8.chart.number_of_questions} }
                };
                var chart = new google.charts.Line(document.getElementById('chart-3'));
                chart.draw(data, google.charts.Line.convertOptions(options));
            }
            drawChart['3'] = function(){
                var data = new google.visualization.DataTable();
                data.addColumn('string');
                data.addColumn('number');
                data.addRows([
                  ['10 déc 2014', 21],
                  ['10 déc 2014', 21]
                ]);
                var options = {
                    legend: { position:'none' },
                    chart: { title: 'Évolution de vos résultats pour le thème Expert' },
                    width: "100%",
                    height: 300,
                    vAxis: { /*title: 'Scores',*/ viewWindow: {min:0, max:drupalSettings.TestD8.chart.number_of_questions} }
                };
                var chart = new google.charts.Line(document.getElementById('chart-4'));
                chart.draw(data, google.charts.Line.convertOptions(options));
            }

            google.charts.load('current', {'packages':['line']});
            google.charts.setOnLoadCallback(drawChart[activeTab]);


        }
    };
})(jQuery, Drupal, drupalSettings);

