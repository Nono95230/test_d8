(function($, Drupal, drupalSettings){
    'use strict';
    Drupal.behaviors.jsTestD8Theme = {
        attach: function (context, settings) {
            $(function(){

                $("a.test_d8-theme-select").on("click", function(e){
                    var theme = $(this).text(),
                        number_of_questions = drupalSettings.TestD8.number_of_questions,
                        time_to_complete_test = drupalSettings.TestD8.time_to_complete_test;
                    return confirm("Vous êtes sur le point de débuter le test " + theme + ".\n\
Vous avez " + (time_to_complete_test / 60) + " minutes pour répondre à " + number_of_questions + " questions.\n\
Le test est soumis automatiquement la fin du compte à rebours.\n\n\
/!\\ Pas d'annulation possible !\n\n\
Voulez-vous continuer ?");
                });

            });
        }
    };
})(jQuery, Drupal, drupalSettings);

