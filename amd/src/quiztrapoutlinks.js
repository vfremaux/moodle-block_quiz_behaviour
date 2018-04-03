/**
 * This will only work for quizzes having :
 * - being customscripted as required
 * - having single question per page
 */
// jshint undef:false, unused:false
/* globals $ */


define(['jquery', 'core/str', 'core/log'], function($, str, log) {

    var outmessage = "You will loose all results of the attempt. Do you want to continue?";

    var f = function(e) {
        return outmessage || true;
    };

    var skipf = function(e) {
        if (e.localTarget) {
            if (e.localTarget.id == 'responseform') {
                // Trap only the quiz answer form, let trigger all other forms.
                window.onbeforeunload = null;
            }
        } else if (e.originTarget) {
            if (e.originTarget.id == 'responseform') {
                // Trap only the quiz answer form, let trigger all other forms.
                window.onbeforeunload = null;
            }
        } else if (e.target) {
            if (e.target.id == 'responseform') {
                // Trap only the quiz answer form, let trigger all other forms.
                window.onbeforeunload = null;
            }
        }
        return null;
    };

    return {
        init: function() {

            str.get_string('looseattemptsignal', 'block_quiz_behaviour').done(function(s) {
                outmessage = s;
            });
            window.onbeforeunload = f;
            window.onsubmit = skipf;

            log.debug('AMD Block_quiz_behaviour quiztrapoutlinks initialized');
        }
    };

});