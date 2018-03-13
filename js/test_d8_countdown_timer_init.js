(function ($, Drupal, drupalSettings) {
    "use strict";
    /**
     * Attaches the JS countdown behavior
     */
    Drupal.behaviors.jsCountdownTimer = {
        attach: function (context) {
            //console.log(context);
            var note = $('#test_d8-countdowntimer-note')/*,
                ts = new Date(drupalSettings.countdown.unixtimestamp * 1000)*/;

            $(context).find('#test_d8-countdowntimer').once('test_d8-countdowntimer').countdown({
                //timestamp: ts,
                timestamp: drupalSettings.TestD8.countdown *1000,
                callback: function (weeks, days, hours, minutes, seconds) {
                    var dateStrings = new Array();
                    dateStrings['@weeks'] = Drupal.formatPlural(weeks, '1 semaine', '@count semaines');
                    dateStrings['@days'] = Drupal.formatPlural(days, '1 jours', '@count jours');
                    dateStrings['@hours'] = Drupal.formatPlural(hours, '1 heure', '@count heures');
                    dateStrings['@minutes'] = Drupal.formatPlural(minutes, '1 minute', '@count minutes');
                    dateStrings['@seconds'] = Drupal.formatPlural(seconds, '1 seconde', '@count secondes');
                    var message = Drupal.t('@weeks, @days, @hours, @minutes, @seconds', dateStrings);
                    note.html(message);

                    // when timer stops
                    if (weeks == 0 && days == 0 &&
                        hours == 0 && minutes == 0 && seconds == 0){
                        $("#testd8_form").submit();
                    }
                }
            });
        }
    };
})(jQuery, Drupal, drupalSettings);
