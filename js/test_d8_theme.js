(function($, Drupal, drupalSettings){
    'use strict';
    Drupal.behaviors.jsTestD8Theme = {
        attach: function (context, settings) {
            $(function(){

                // navigation
                $(".test_d8-theme-list").on("click", "a.test_d8-theme-select", function(e){
                    var theme = $(this).data("theme"),
                        numberofquestions = $(this).data("numberofquestions"),
                        timetocompletetest = $(this).data("timetocompletetest");
                    return confirm("Vous êtes sur le point de débuter le test " + theme + ".\n\
Vous avez " + (timetocompletetest / 60) + " minutes pour répondre à " + numberofquestions + " questions.\n\
Le test est soumis automatiquement la fin du compte à rebours.\n\n\
/!\\ Pas d'annulation possible !\n\n\
Voulez-vous continuer ?");
                });

            });
        }
    };
})(jQuery, Drupal, drupalSettings);

