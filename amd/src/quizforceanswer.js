/**
 * This will only work for quizzes having :
 * - being customscripted as required
 * - having single question per page
 */
// jshint undef:false, unused:false
<<<<<<< HEAD
/* globals $ */
=======
>>>>>>> MOODLE_35_STABLE

define(['jquery', 'core/log'], function($, log) {

    return {
        init: function() {

            // Disables end button.
<<<<<<< HEAD
            questions = $('.que.notyetanswered');
            feedbacks = $('.immediatefeedback');
=======
            var questions = $('.que.notyetanswered');
>>>>>>> MOODLE_35_STABLE
            if (questions.length > 0) {
                // If we do not have all answered, disable the next button.
                $('.mod_quiz-next-nav').attr('disabled', 'disabled');
                $('.mod_quiz-next-nav').css('visibility', 'visible');
                $('.im-controls').css('visibility', 'hidden');
                $('#responseform input[type=radio]').css('visibility', 'visible');
                $('#responseform input[type=radio] + label').css('pointer-events', 'auto');
            } else {
                // Show the nav button back.
                $('.mod_quiz-next-nav').css('visibility', 'visible');
                $('#responseform input[type=radio]').css('visibility', 'visible');
                $('#responseform input[type=radio] + label').css('pointer-events', 'auto');
            }

            // Add onchange observer on all question options.
            $('#responseform input').on('change', function() {
                $('.mod_quiz-next-nav').attr('disabled', null);
                $('.im-controls').css('visibility', 'visible');
            });

            log.debug('AMD block_quiz_behaviour quizforceanswer initialized');
        }
    };
});