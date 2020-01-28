/**
 * This will only work for quizzes having :
 * - being customscripted as required
 * - having single question per page
 */
// jshint undef:false, unused:false

define(['jquery', 'core/log'], function($, log) {

    var extendedquestionbank = {

        init: function() {

            $('.question-category-handle').bind('click', this.togglecategory);

            log.debug("AMD Extended question bank initialized");
        },

        togglecategory: function(e) {

            e.stopPropagation();
            e.preventDefault();

            var that = $(this);

            var handleid = that.attr('id');
            var id = handleid.replace('question-category-handle-', '');
            var subs = 'question-category-subs-' + id;
            if ($('#' + subs).css('display') == 'block') {
                $('#' + subs).css('display', 'none');
                $('#' + handleid).attr('aria-expanded', false);
            } else {
                $('#' + subs).css('display', 'block');
                $('#' + handleid).attr('aria-expanded', true);
            }
        }
    };

    return extendedquestionbank;
});