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
            $('#quiz-behaviour-cats-collapseall').bind('click', this.collapseall);
            $('#quiz-behaviour-cats-expandall').bind('click', this.expandall);
            $('#quiz-behaviour-cats-toggleempty').bind('click', this.toggleempty);

            log.debug("AMD Extended question bank initialized");
        },

        togglecategory: function(e) {

            e.stopPropagation();
            e.preventDefault();

            var that = $(this);

            var handleid = that.attr('id');
            var id = handleid.replace('question-category-handle-', '');
            var subs = 'question-category-sub-' + id;
            if ($('#' + subs).css('display') == 'block') {
                $('#' + subs).css('display', 'none');
                $('#' + handleid).attr('aria-expanded', false);
            } else {
                $('#' + subs).css('display', 'block');
                $('#' + handleid).attr('aria-expanded', true);
            }
        },

        collapseall: function() {
            $('.question-category.sub').css('display', 'none');
            $('.question-category-handle').attr('aria-expanded', false);
        },

        expandall: function() {
            $('.question-category.sub').css('display', 'block');
            $('.question-category-handle').attr('aria-expanded', true);
        },

        toggleempty: function() {

            var that = $(this);
            var parents;

            if ($('.question-category.is-not-empty').css('display') == 'block') {
                $('.question-category.is-not-empty').css('display', 'none');
                that.css('background-color', '#888');
                that.css('color', '#fff');
                // Reopen upgoing hierarchies to empty elements.
                $('.question-category.is-empty').parentsUntil('.questioncategories').css('display', 'block');
                parents = $('.question-category.is-empty').parentsUntil('.questioncategories');
                parents.children('.question-category-header').css('opacity', 0.5);
            } else {
                $('.question-category.is-not-empty').css('display', 'block');
                parents = $('.question-category.is-empty').parentsUntil('.questioncategories');
                parents.children('.question-category-header').css('opacity', 1);
                that.css('background-color', '#ccc');
                that.css('color', '#000');
            }
        },
    };

    return extendedquestionbank;
});