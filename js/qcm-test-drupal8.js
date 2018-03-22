(function($, Drupal, drupalSettings){
    'use strict';
    Drupal.behaviors.jsTestD8 = {
        attach: function (context, settings) {

            //$(function(){

                //Set properties
                var countDownEnd = false;

                // get question IDs
                var questionIds = new Array();
                $(".test_d8-question").once('test_d8-question').each(function(index, el){
                    var id = $(this).attr("id").replace("test_d8-question", "");
                    questionIds[index] = id;
                });

                // init: display first question
                var currentQuestion = 0;
                change_question(currentQuestion);

                // init: circle color (after page refresh)
                for (var i = 0, len = questionIds.length; i < len; i++){
                    var qid = questionIds[i];
                    if ($("input[name='propositions"+ qid +"']").is(':checked')){
                        circle_color(qid);
                    }
                }

                // navigation
                $("#test_d8-navisual").once('test_d8-navisual').on("click", ".test_d8-navisual-item", function(e){
                    e.preventDefault();
                    var qid = $(this).data("qid"),
                        pos = $(this).data("pos");
                    change_question(pos);
                });
                $("#test_d8-nav").once('test_d8-nav').on("click", "#test_d8-question-prev", function(e){
                    e.preventDefault();
                    change_question('prev');
                });
                $("#test_d8-nav").once('test_d8-nav').on("click", "#test_d8-question-next", function(e){
                    e.preventDefault();
                    change_question('next');
                });

                $("#test_d8-submit").once('test_d8-submit').on("click", function(e){
                    var diff = (questionIds.length - $("input:radio:checked").length);
                    if (diff && !countDownEnd){
                        var plural = (diff > 1 ? "s" : "");
                        return confirm("Vous n'avez pas répondu à " + diff + " question" + plural + ".\n" +
                            "Êtes-vous sûr(e) de vouloir valider le test ?");
                    }
                });

                // apply color to circle when question is answered + update session
                $("input[name^=propositions]").once('input-propositions').on("click", function(){
                    var qid = $(this).attr("name").replace("propositions", "");
                    circle_color(qid);
                    update_session(qid);
                });

                function circle_color(qid){
                    $("span.test_d8-navisual-item[data-qid='" + qid + "']").addClass("answered");
                }

                function update_session(qid){
                    var answer = $('#test_d8-question'+ qid +' input:radio:checked').val().replace("p", "");
                    $.ajax({
                        type: "POST",
                        url: '/test-drupal8/update-session',
                        data: {qid: qid, answer: answer}
                    });
                }

                function update_timer(){
                    $.ajax({
                        type: "POST",
                        url: '/test-drupal8/update-timer'
                    });
                }
                setInterval(update_timer, 2000);

                function change_question(num){
                    if (num == 'prev'){
                        var prevQuestion = currentQuestion - 1;
                        if (questionIds[prevQuestion] !== undefined){
                            $("#test_d8-question" + questionIds[currentQuestion]).addClass("test_d8-hidden");
                            $("#test_d8-question" + questionIds[prevQuestion]).removeClass("test_d8-hidden");
                            currentQuestion = prevQuestion;
                            $("#test_d8-question-curr").text(currentQuestion + 1);
                        }
                    } else if (num == 'next'){
                        var nextQuestion = currentQuestion + 1;
                        if (questionIds[nextQuestion] !== undefined){
                            $("#test_d8-question" + questionIds[currentQuestion]).addClass("test_d8-hidden");
                            $("#test_d8-question" + questionIds[nextQuestion]).removeClass("test_d8-hidden");
                            currentQuestion = nextQuestion;
                            $("#test_d8-question-curr").text(currentQuestion + 1);
                        }
                    } else {
                        if (questionIds[num] !== undefined){
                            $("#test_d8-question" + questionIds[currentQuestion]).addClass("test_d8-hidden");
                            $("#test_d8-question" + questionIds[num]).removeClass("test_d8-hidden");
                            currentQuestion = num;
                            $("#test_d8-question-curr").text(currentQuestion + 1);
                        }
                    }

                    // disable prev/next button according to the current question
                    if (questionIds[currentQuestion - 1] === undefined){
                        $("#test_d8-question-prev").prop("disabled", true);
                    } else {
                        $("#test_d8-question-prev").prop("disabled", false);
                    }
                    if (questionIds[currentQuestion + 1] === undefined){
                        $("#test_d8-question-next").prop("disabled", true);
                    } else {
                        $("#test_d8-question-next").prop("disabled", false);
                    }

                    return false;
                }

                // countdown timer
                var note = $('#timer_qcm-note');//,
                    //ts = new Date(drupalSettings.countdown.unixtimestamp * 1000);
                $(context).find('#timer_qcm').once('timer_qcm').countdown({
                    //timestamp: ts,
                    timestamp: (drupalSettings.TestD8.countdown * 1000),
                    callback: function (weeks, days, hours, minutes, seconds) {
                        /*
                        var dateStrings = new Array();
                        dateStrings['@weeks'] = Drupal.formatPlural(weeks, '1 semaine', '@count semaines');
                        dateStrings['@days'] = Drupal.formatPlural(days, '1 jours', '@count jours');
                        dateStrings['@hours'] = Drupal.formatPlural(hours, '1 heure', '@count heures');
                        dateStrings['@minutes'] = Drupal.formatPlural(minutes, '1 minute', '@count minutes');
                        dateStrings['@seconds'] = Drupal.formatPlural(seconds, '1 seconde', '@count secondes');
                        var message = Drupal.t('@weeks, @days, @hours, @minutes, @seconds', dateStrings);
                        note.html(message);
                        */

                        // when timer stops
                        if (
                            weeks == 0 &&
                            days == 0 &&
                            hours == 0 &&
                            minutes == 0 &&
                            seconds == 0
                        ){
                            countDownEnd = true;
                            $("#test_d8-submit").click();
                        }
                    }
                });

            //});
        }
    };
})(jQuery, Drupal, drupalSettings);

