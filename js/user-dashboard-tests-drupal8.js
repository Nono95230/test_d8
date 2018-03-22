(function($, Drupal, drupalSettings){
    'use strict';
    Drupal.behaviors.jsTestD8Chart = {
        attach: function (context, settings) {

            function getQueryStringValue(key){
                return decodeURIComponent(window.location.search.replace(new RegExp("^(?:.*[&\\?]" + encodeURIComponent(key).replace(/[\.\+\*]/g, "\\$&") + "(?:\\=([^&]*))?)?.*$", "i"), "$1"));
            }

            var drupalsettings_chart = drupalSettings.TestD8.chart.data,
                num_questions = drupalSettings.TestD8.chart.number_of_questions,
                disabledTabs = [],
                tabId = getQueryStringValue('tab'),
                activeTab = 0,
                myChart = [],
                chartOptions = {
                    legend: {
                        display: false
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.yLabel;
                            }
                        }
                    },
                    responsive: true,
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                max: num_questions,
                                min: 0
                            }
                        }]
                    },
                    //title: { display: false, text: 'Résultats pour "Webmaster"' },
                    tooltips: { mode: 'index', intersect: false }
                    //hover: { mode: 'nearest', intersect: true }
                };

            for (var index in drupalsettings_chart){
                var value = drupalsettings_chart[index];

                // active l'onglet du theme du test fini
                if (tabId == value.id){ activeTab = index; }

                // disable tabs with no data
                if (value.num == 0){ disabledTabs.push(Number(index)); }

                // implements drawChart functions
                myChart[index] = new Chart($("#chart-" + value.id), {
                    type: (value.num < 2 ? 'bar' : 'line'),
                    data: {
                        labels: drupalsettings_chart[index].chartLabels,
                        datasets: [{
                            data: drupalsettings_chart[index].chartData,
                            lineTension: 0.2,
                            label: '',
                            fill: false,
                            borderColor: "rgba(0, 120, 164, 0.75)",
                            borderWidth: 2
                        }]
                    },
                    options: chartOptions
                });
            }

            $("#tabs").tabs({
                active: activeTab,
                disabled: disabledTabs
            });

        }
    };
})(jQuery, Drupal, drupalSettings);

