(function($, Drupal, drupalSettings){
    'use strict';
    Drupal.behaviors.jsTestD8Chart = {
        attach: function (context, settings) {
            Highcharts.setOptions({
                lang: {
                    decimalPoint: ',',
                    thousandsSep: ' ',
                    months: ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'],
                    shortMonths: ['jan','fév','mars','avr','mai','juin','juil','août','sept','oct','nov','déc'],
                    weekdays: ['dimanche','lundi','mardi','mercredi','jeudi','vendredi','samedi'],
                    shortWeekdays: ['dim','lun','mar','mer','jeu','ven','sam'],
                    rangeSelectorFrom: 'Du',
                    rangeSelectorTo: 'au',
                }
            });
            Highcharts.stockChart('charts', {
                chart: { type: 'spline' },
                title: { text: null },
                subtitle: { text: null },
                credits: {
                    enabled: false
                },
                rangeSelector: {
                    selected: 4,
                    buttons: [
                        {type: 'month', count: 1, text: '1 mois'},
                        {type: 'month', count: 3, text: '3 mois'},
                        {type: 'month', count: 6, text: '6 mois'},
                        {type: 'year', count: 1, text: '1 an'},
                        {type: 'all', text: 'Tout'}
                    ],
                    buttonTheme: {
                        width: 50,
                    },
                    buttonSpacing: 5,
                    inputDateFormat: '%e %b %Y',
                    inputEditDateFormat: '%d-%m-%Y'
                },
                legend: {
                    enabled: true,
                    layout: "horizontal",
                    verticalAlign: "bottom",
                    align: "center"
                },
                xAxis: {
                    title: { text: null/*'Date'*/ },
                    type: 'datetime',
                    dateTimeLabelFormats: {
                        month: '%b %Y'/*'%e %b %Y'*/,
                        year: '%b'
                    }
                },
                yAxis: {
                    title: { text: null/*'Score'*/ },
                    min: 0,
                    max: 100,
                    plotLines: [{
                        value: 70,
                        color: '#66cc66',
                        dashStyle: 'shortdash',
                        width: 1,
                        label: {
                            text: '70 %',
                            style: {
                                color: '#00aa00',
                                fontWeight: 'normal'
                            }
                        }
                    }]
                },
                tooltip: {
                    headerFormat: '<b>{series.name}</b><br>',
                    pointFormat: '{point.x:%e %b %Y} : {point.y:.1f} %'
                },
                plotOptions: {
                    spline: {
                        marker: {
                            enabled: true,
                            symbol: 'circle'
                        }
                    },

                },
                series: drupalSettings.TestD8.chart.data
            });

        }
    };
})(jQuery, Drupal, drupalSettings);

