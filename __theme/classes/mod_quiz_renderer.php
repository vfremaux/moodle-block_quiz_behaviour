<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package    mod_quiz
 * @copyright  2016 Valery Fremaux
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/blocks/quiz_behaviour/xlib.php');

class theme_<themename>_mod_quiz_renderer extends mod_quiz_renderer {

    protected $attemptobj;

    /**
     * Attempt Page.
     * Bound to : quiz_behaviour
     * Reason : Change layout
     *
     * @param quiz_attempt $attemptobj Instance of quiz_attempt
     * @param int $page Current page number
     * @param quiz_access_manager $accessmanager Instance of quiz_access_manager
     * @param array $messages An array of messages
     * @param array $slots Contains an array of integers that relate to questions
     * @param int $id The ID of an attempt
     * @param int $nextpage The number of the next page
     */
    public function attempt_page($attemptobj, $page, $accessmanager, $messages, $slots, $id,
            $nextpage) {

        $course = $attemptobj->get_course();
        $manager = get_block_quiz_behaviour_manager();
        $qid = $attemptobj->get_quizid();

        if (!$manager || !$manager->has_behaviour($qid, 'alternateattemptpage')) {
            // Back to standard rendering.
            return parent::attempt_page($attemptobj, $page, $accessmanager, $messages, $slots, $id, $nextpage);
        }

        $this->attemptobj = $attemptobj;

        $template = new StdClass;
        $template->header = $this->header();
        $template->quiznotices = $this->quiz_notices($messages);
        $template->countdowntimer = $this->countdown_timer($attemptobj, time());

        $template->questionstr = get_string('question');
        $template->questionnum = $this->question_num($attemptobj);
        $template->quizprogressindicator = $this->quiz_progress_indicator($attemptobj);
        $template->questionrefs = $this->question_refs($slots, $attemptobj);

        $template->quizcountdown = $this->quiz_countdown($attemptobj);
        $template->attemptform = $this->attempt_form($attemptobj, $page, $slots, $id, $nextpage);
        $template->footer = $this->footer();
        return $this->output->render_from_template('block_quiz_behaviour/attemptpage', $template);
    }

    /*
     * Summary Page
     */

    /**
     * Display the prev/next buttons that go at the bottom of each page of the attempt.
     * Bound to : userquiz_monitor
     * Reason : change names of the button to fit the context use case
     *
     * @param int $page the page number. Starts at 0 for the first page.
     * @param bool $lastpage is this the last page in the quiz?
     * @return string HTML fragment.
     */
    protected function local_attempt_navigation_buttons($attemptobj, $page) {
        global $CFG;

        $lastpage = $attemptobj->is_last_page($page);
        $terminatebutton = '';

        if (is_dir($CFG->dirroot.'/blocks/userquiz_monitor')) {
            // Special optional hook.
            include_once($CFG->dirroot.'/blocks/userquiz_monitor/xlib.php');
            if ($attemptbuttons = block_userquiz_monitor_attempt_buttons($attemptobj, $page)) {
                return $attemptbuttons;
            }
        }

        if (!$lastpage) {
            $params = array('value' => get_string('endtest', 'quiz'), 'type' => 'button');
            $label = html_writer::empty_tag('input', $params);
            $attrs = array('class' => 'endtestlink mod_quiz-next-nav');
            $terminatebutton = html_writer::link($attemptobj->summary_url(), $label, $attrs);
        }

        $output = '';
        $output .= html_writer::start_tag('div', array('class' => 'submitbtns'));
        $navmethod = $attemptobj->get_quiz()->navmethod;
        if ($page > 0 && $navmethod == 'free') {
            $output .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'previous',
                    'value' => get_string('navigateprevious', 'quiz'), 'class' => 'mod_quiz-prev-nav'));
        }
        if ($lastpage) {
            $nextlabel = get_string('endtest', 'quiz');
        } else {
            $nextlabel = get_string('navigatenext', 'quiz');
        }
        $output .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'next',
                'value' => $nextlabel, 'class' => 'mod_quiz-next-nav'));
        $output .= $terminatebutton;
        $output .= html_writer::end_tag('div');

        return $output;

    }

    /**
     *
     */
    public function quiz_countdown($attemptobj) {
        $str = '';

        $str .= '<div id="quiz-countdown">';
        $str .= $this->countdown_timer($attemptobj, time());
        $str .= '</div>';

        return $str;
    }

    /**
     * Alter the summary layout
     * Bound to : quiz_behaviour
     *
     * @param quiz_attempt $attemptobj
     * @param mod_quiz_display_options $displayoptions
     */
    public function summary_page($attemptobj, $displayoptions) {

        $course = $attemptobj->get_course();
        $qid = $attemptobj->get_quizid();
        $manager = get_block_quiz_behaviour_manager();

        $output = '';
        $output .= $this->header();
        if (!$manager || !$manager->has_behaviour($qid, 'hidesummaryinfo')) {
            // Do NOT expose quiz identity in a enabled quizzes.
            $output .= $this->heading(format_string($attemptobj->get_quiz_name()));
        }
        $output .= $this->heading(get_string('finalvalidation', 'block_quiz_behaviour'), 3);
        $output .= $this->box(get_string('finalvalidation_desc', 'block_quiz_behaviour'));
        $output .= $this->summary_page_controls($attemptobj);
        $output .= $this->footer();
        return $output;
    }

    /**
     * Creates any controls a the page should have.
     *
     * @param quiz_attempt $attemptobj
     */
    public function summary_page_controls($attemptobj) {
        $output = '';

        $course = $attemptobj->get_course();
        $qid = $attemptobj->get_quizid();
        $manager = get_block_quiz_behaviour_manager();

        // CHANGE : Make it aware of no-backwards restriction.
        $navmethod = $attemptobj->get_quiz()->navmethod;
        if ($navmethod == "free") {
            // Return to place button.
            if ($attemptobj->get_state() == quiz_attempt::IN_PROGRESS) {
                $button = new single_button(
                        new moodle_url($attemptobj->attempt_url(null, $attemptobj->get_currentpage())),
                        get_string('returnattempt', 'quiz'));
                $output .= $this->container($this->container($this->render($button),
                        'controls'), 'submitbtns mdl-align');
            }
        }
        // CHANGE.

        // Finish attempt button.
        $options = array(
            'attempt' => $attemptobj->get_attemptid(),
            'finishattempt' => 1,
            'timeup' => 0,
            'slots' => '',
            'sesskey' => sesskey(),
        );

        $button = new single_button(
                new moodle_url($attemptobj->processattempt_url(), $options),
                get_string('submitallandfinish', 'quiz'));
        $button->id = 'responseform';

<<<<<<< HEAD
        if ($manager && $manager->has_behaviour($qid, 'alternateattemptpage'))) {
=======
        if ($manager && $manager->has_behaviour($qid, 'alternateattemptpage')) {
>>>>>>> MOODLE_35_STABLE
            if ($attemptobj->get_state() == quiz_attempt::IN_PROGRESS) {
                $button->add_action(new confirm_action(get_string('confirmclose', 'quiz'), null,
                    get_string('submitallandfinish', 'quiz')));
            }
        }

        $duedate = $attemptobj->get_due_date();
        $message = '';
        if ($attemptobj->get_state() == quiz_attempt::OVERDUE) {
            $message = get_string('overduemustbesubmittedby', 'quiz', userdate($duedate));

        } else if ($duedate) {
            $message = get_string('mustbesubmittedby', 'quiz', userdate($duedate));
        }

        $content = '';
        $content .= $message . $this->container($this->render($button), 'controls');
        $output .= $this->container($content, 'submitbtns mdl-align');

        return $output;
    }

    /**
     * Prints a graphic progress indicator
     * @param quiz_attempt &$attemptobj the current attempt
     */
    public function quiz_progress_indicator(&$attemptobj) {

        $page = optional_param('page', 0, PARAM_INT);
        if ($page < 0) {
            $page = 0;
        }

        $pagenum = $attemptobj->get_num_pages();

        $template = new StdClass;
        $template->ratio = ($pagenum) ? ($page + 1) / $pagenum * 100 : 0;

        return $this->output->render_from_template('block_quiz_behaviour/quizprogressindicator', $template);
    }

    /**
     * Ouputs the form for making an attempt
     *
     * @param quiz_attempt $attemptobj
     * @param int $page Current page number
     * @param array $slots Array of integers relating to questions
     * @param int $id ID of the attempt
     * @param int $nextpage Next page number
     */
    public function attempt_form($attemptobj, $page, $slots, $id, $nextpage) {
        global $DB;

        $output = '';

        // Start the form.
        $output .= html_writer::start_tag('form',
                array('action' => $attemptobj->processattempt_url(), 'method' => 'post',
                'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8',
                'id' => 'responseform'));
        $output .= html_writer::start_tag('div');

        // Print all the questions.
        foreach ($slots as $slot) {
            $output .= $attemptobj->render_question($slot, false, $this,
                    $attemptobj->attempt_url($slot, $page), $this);
        }

        $output .= $this->local_attempt_navigation_buttons($attemptobj, $page);

        // Some hidden fields to trach what is going on.
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'attempt',
                'value' => $attemptobj->get_attemptid()));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'thispage',
                'value' => $page, 'id' => 'followingpage'));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'nextpage',
                'value' => $nextpage));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'timeup',
                'value' => '0', 'id' => 'timeup'));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey',
                'value' => sesskey()));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'scrollpos',
                'value' => '', 'id' => 'scrollpos'));

        // Add a hidden field with questionids. Do this at the end of the form, so
        // if you navigate before the form has finished loading, it does not wipe all
        // the student's answers.
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'slots',
                'value' => implode(',', $attemptobj->get_active_slots($page))));

        // Finish the form.
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('form');

        $output .= $this->connection_warning();

        return $output;
    }

    public function question_refs($slots, &$attemptobj) {
        global $DB;

        $template = new StdClass;
        $template->questionreferencestr = get_string('questionreference', 'block_quiz_behaviour');
        $template->questioncategorystr = get_string('questioncategory', 'block_quiz_behaviour');

        foreach ($slots as $slot) {
            $questionreftpl = new StdClass;
            $qa = $attemptobj->get_question_attempt($slot);
            $qcatid = $DB->get_field('question', 'category', array('id' => $qa->get_question()->id));
            $questionreftpl->qcatname = format_string($DB->get_field('question_categories', 'name', array('id' => $qcatid)));
            $parts = explode('-', $attemptobj->get_question_name($slot));
            $questionreftpl->qname = format_string(array_shift($parts));
            $template->questionrefs[] = $questionreftpl;
        }

        return $this->output->render_from_template('block_quiz_behaviour/questionreferences', $template);
    }

    public function question_num(&$attemptobj) {
        global $DB;

        $page = optional_param('page', 0, PARAM_INT);
        if ($page < 0) {
            $page = 0;
        }

        $str = '';
        $pagenum = $attemptobj->get_num_pages();
        $str .= ' <span class="quiz-question-num">'.($page + 1).' / '.$pagenum.'</span>';

        return $str;
    }

    /**
     * Generates the view page
     *
     * @param int $course The id of the course
     * @param array $quiz Array conting quiz data
     * @param int $cm Course Module ID
     * @param int $context The page context ID
     * @param array $infomessages information about this quiz
     * @param mod_quiz_view_object $viewobj
     * @param string $buttontext text for the start/continue attempt button, if
     *      it should be shown.
     * @param array $infomessages further information about why the student cannot
     *      attempt this quiz now, if appicable this quiz
     */
    public function view_page($course, $quiz, $cm, $context, $viewobj) {
        $output = '';
        $output .= $this->view_information($quiz, $cm, $context, $viewobj->infomessages);
        $output .= $this->view_table($quiz, $context, $viewobj);
        $output .= $this->view_result_info($quiz, $context, $cm, $viewobj);
        $output .= $this->box($this->view_page_buttons($viewobj), 'quizattempt');
        return $output;
    }

    /**
     * Builds the review page
     *
     * @param quiz_attempt $attemptobj an instance of quiz_attempt.
     * @param array $slots an array of intgers relating to questions.
     * @param int $page the current page number
     * @param bool $showall whether to show entire attempt on one page.
     * @param bool $lastpage if true the current page is the last page.
     * @param mod_quiz_display_options $displayoptions instance of mod_quiz_display_options.
     * @param array $summarydata contains all table data
     * @return $output containing html data.
     */
    public function review_page(quiz_attempt $attemptobj, $slots, $page, $showall,
                                $lastpage, mod_quiz_display_options $displayoptions,
                                $summarydata) {

        $output = '';
        $output .= $this->header();
        $output .= $this->review_next_navigation($attemptobj, $page, $lastpage, $showall);
        $output .= $this->review_summary_table($summarydata, $page);
        $output .= $this->review_form($page, $showall, $displayoptions,
                $this->questions($attemptobj, true, $slots, $page, $showall, $displayoptions),
                $attemptobj);

        $output .= $this->review_next_navigation($attemptobj, $page, $lastpage, $showall);
        $output .= $this->footer();
        return $output;
    }

    public function set_attemptobj($attemptobj) {
        $this->attemptobj = $attemptobj;
    }

    /**
     * Creates the navigation buttons at the bottom of the review attempt page.
     *
     * Note, the name of this function is no longer accurate, but when the design
     * changed, it was decided to keep the old name for backwards compatibility.
     *
     * @param quiz_attempt $attemptobj instance of quiz_attempt
     * @param int $page the current page
     * @param bool $lastpage if true current page is the last page
     * @param bool|null $showall if true, the URL will be to review the entire attempt on one page,
     *      and $page will be ignored. If null, a sensible default will be chosen.
     *
     * @return string HTML fragment.
     */
    public function review_next_navigation(quiz_attempt $attemptobj, $page, $lastpage, $showall = null) {
        $nav = '';
        if ($page > 0) {
            $attrs = array('value' => get_string('navigateprevious', 'quiz'), 'type' => 'button');
            $nav .= link_arrow_left(html_writer::empty_tag('input', $attrs),
                    $attemptobj->review_url(null, $page - 1, $showall), false, 'mod_quiz-prev-nav');
        }
        if ($lastpage) {
            $nav .= $this->finish_review_link($attemptobj);
        } else {
            $attrs = array('value' => get_string('navigatenext', 'quiz'), 'type' => 'button');
            $nav .= link_arrow_right(html_writer::empty_tag('input', $attrs),
                    $attemptobj->review_url(null, $page + 1, $showall), false, 'mod_quiz-next-nav');
        }
        return html_writer::tag('div', $nav, array('class' => 'submitbtns'));
    }

    /**
     * Returns either a link or button
     *
     * @param quiz_attempt $attemptobj instance of quiz_attempt
     */
    public function finish_review_link(quiz_attempt $attemptobj) {
        $url = $attemptobj->view_url();

        $course = $attemptobj->get_course();
        $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));

        $manager = get_block_quiz_behaviour_manager();
        $qid = $attemptobj->get_quizid();

        if ($manager && $manager->has_behaviour($qid, 'directreturn')) {
            $backstr = get_string('backtocourse', 'block_quiz_behaviour');
            $button = html_writer::tag('input', '', array('type' => 'button', 'value' => $backstr));
            $link = html_writer::tag('a', $button, array('href' => $courseurl, 'class' => 'mod_quiz-next-nav'));
            return $link;
        }

        return $this->local_finish_review_link($attemptobj);
    }

    /**
     * Always returns a button
     *
     * @param quiz_attempt $attemptobj instance of quiz_attempt
     */
    public function local_finish_review_link(quiz_attempt $attemptobj) {
        $url = $attemptobj->view_url();

        if ($attemptobj->get_access_manager(time())->attempt_must_be_in_popup()) {
            $this->page->requires->js_init_call('M.mod_quiz.secure_window.init_close_button',
                    array($url), false, quiz_get_js_module());
            return html_writer::empty_tag('input', array('type' => 'button',
                    'value' => get_string('finishreview', 'quiz'),
                    'id' => 'secureclosebutton',
                    'class' => 'mod_quiz-next-nav'));

        } else {
            $attrs = array('value' => get_string('finishreview', 'quiz'), 'type' => 'button');
            return html_writer::link($url, html_writer::empty_tag('input', $attrs),
                    array('class' => 'mod_quiz-next-nav'));
        }
    }

    /**
     * Renders the main bit of the review page.
     *
     * @param array $summarydata contain row data for table
     * @param int $page current page number
     * @param mod_quiz_display_options $displayoptions instance of mod_quiz_display_options
     * @param $content contains each question
     * @param quiz_attempt $attemptobj instance of quiz_attempt
     * @param bool $showall if true display attempt on one page
     */
    public function review_form($page, $showall, $displayoptions, $content, $attemptobj) {

        $manager = get_block_quiz_behaviour_manager();
        $qid = $attemptobj->get_quizid();

        if ($displayoptions->flags != question_display_options::EDITABLE) {
            return $content;
        }

        $output = '';

        if (!$manager || !$manager->has_behaviour($qid, 'hideflags')) {
            $this->page->requires->js_init_call('M.mod_quiz.init_review_form', null, false,
                    quiz_get_js_module());

            $output .= html_writer::start_tag('form', array('action' => $attemptobj->review_url(null,
                    $page, $showall), 'method' => 'post', 'class' => 'questionflagsaveform'));
        }
        $output .= html_writer::start_tag('div');
        $output .= $content;
        if (!$manager || !$manager->has_behaviour($qid, 'hideflags')) {
            $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey',
                    'value' => sesskey()));
            $output .= html_writer::start_tag('div', array('class' => 'submitbtns'));
            $output .= html_writer::empty_tag('input', array('type' => 'submit',
                    'class' => 'questionflagsavebutton', 'name' => 'savingflags',
                    'value' => get_string('saveflags', 'question')));
            $output .= html_writer::end_tag('div');
        }
        $output .= html_writer::end_tag('div');
        if (!$manager || !$manager->has_behaviour($qid, 'hideflags')) {
            $output .= html_writer::end_tag('form');
        }

        return $output;
    }

    /**
     * Outputs the navigation block panel
     *
     * @param quiz_nav_panel_base $panel instance of quiz_nav_panel_base
     */
    public function navigation_panel(quiz_nav_panel_base $panel) {

        $output = '';
        $userpicture = $panel->user_picture();
        if ($userpicture) {
            $fullname = fullname($userpicture->user);
            if ($userpicture->size === true) {
                $fullname = html_writer::div($fullname);
            }
            $output .= html_writer::tag('div', $this->render($userpicture) . $fullname,
                    array('id' => 'user-picture', 'class' => 'clearfix'));
        }
        $output .= $panel->render_before_button_bits($this);

        $bcc = $panel->get_button_container_class();
        $output .= html_writer::start_tag('div', array('class' => "qn_buttons clearfix $bcc"));
        foreach ($panel->get_question_buttons() as $button) {
            $output .= $this->render($button);
        }
        $output .= html_writer::end_tag('div');

        // Override quiz_nav_panel_base::render_restart_preview_link()
        if (!$this->attemptobj->is_own_preview()) {
            $restartlink = '';
        }
        $restartlink =  $this->restart_preview_button(new moodle_url(
                $this->attemptobj->start_attempt_url(), array('forcenew' => true)));

<<<<<<< HEAD
        // CHANGE
=======
        // CHANGE+.
>>>>>>> MOODLE_35_STABLE
        $panelcontent = $this->countdown_timer($this->attemptobj, time()).$restartlink;

        $output .= html_writer::tag('div', $panelcontent, array('class' => 'othernav'));

        // $output .= html_writer::tag('div', $panel->render_end_bits($this),
        //         array('class' => 'othernav'));
<<<<<<< HEAD
        // /CHANGE
=======
        // CHANGE-.
>>>>>>> MOODLE_35_STABLE

        $this->page->requires->js_init_call('M.mod_quiz.nav.init', null, false,
                quiz_get_js_module());

        return $output;
    }
}