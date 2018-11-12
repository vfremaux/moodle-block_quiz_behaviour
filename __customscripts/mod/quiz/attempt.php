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
 * This script displays a particular page of a quiz attempt that is in progress.
 *
 * @package   mod_quiz
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Customscript type : CUSTOMSCRIPT_REPLACE.

// require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

// Look for old-style URLs, such as may be in the logs, and redirect them to startattemtp.php.
if ($id = optional_param('id', 0, PARAM_INT)) {
    redirect($CFG->wwwroot . '/mod/quiz/startattempt.php?cmid=' . $id . '&sesskey=' . sesskey());
} else if ($qid = optional_param('q', 0, PARAM_INT)) {
    if (!$cm = get_coursemodule_from_instance('quiz', $qid)) {
        print_error('invalidquizid', 'quiz');
    }
    redirect(new moodle_url('/mod/quiz/startattempt.php',
            array('cmid' => $cm->id, 'sesskey' => sesskey())));
}

// Get submitted parameters.
$attemptid = required_param('attempt', PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);

$attemptobj = quiz_attempt::create($attemptid);
$page = $attemptobj->force_page_number_into_range($page);
$PAGE->set_url($attemptobj->attempt_url(null, $page));

// Check login.
require_login($attemptobj->get_course(), false, $attemptobj->get_cm());

// CHANGE+ : Check quiz_behaviour component after require_login to get course initialized.
<<<<<<< HEAD
if (is_dir($CFG->dirroot.'/blocks/quiz_behaviour')) {
    include_once($CFG->dirroot.'/blocks/quiz_behaviour/xlib.php');
=======
$manager = null;
if (is_dir($CFG->dirroot.'/blocks/quiz_behaviour')) {
    include_once($CFG->dirroot.'/blocks/quiz_behaviour/xlib.php');
    $manager = get_block_quiz_behaviour_manager();
>>>>>>> MOODLE_35_STABLE
    block_quiz_behaviour_attempt_adds($attemptobj);
}
// CHANGE-.

// Check that this attempt belongs to this user.
if ($attemptobj->get_userid() != $USER->id) {
    if ($attemptobj->has_capability('mod/quiz:viewreports')) {
        redirect($attemptobj->review_url(null, $page));
    } else {
        throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'notyourattempt');
    }
}

// Check capabilities and block settings.
if (!$attemptobj->is_preview_user()) {
    $attemptobj->require_capability('mod/quiz:attempt');
    if (empty($attemptobj->get_quiz()->showblocks)) {
        $PAGE->blocks->show_only_fake_blocks();
    }

} else {
    navigation_node::override_active_url($attemptobj->start_attempt_url());
}

// If the attempt is already closed, send them to the review page.
if ($attemptobj->is_finished()) {
    redirect($attemptobj->review_url(null, $page));
} else if ($attemptobj->get_state() == quiz_attempt::OVERDUE) {
    redirect($attemptobj->summary_url());
}

// Check the access rules.
$accessmanager = $attemptobj->get_access_manager(time());
$accessmanager->setup_attempt_page($PAGE);
$output = $PAGE->get_renderer('mod_quiz');
// CHANGE+ : Check quiz_behaviour component after require_login to get course initialized.
$output->set_attemptobj($attemptobj);
// CHANGE-.
$messages = $accessmanager->prevent_access();
if (!$attemptobj->is_preview_user() && $messages) {
    print_error('attempterror', 'quiz', $attemptobj->view_url(),
            $output->access_messages($messages));
}
if ($accessmanager->is_preflight_check_required($attemptobj->get_attemptid())) {
    redirect($attemptobj->start_attempt_url(null, $page));
}

// Set up auto-save if required.
$autosaveperiod = get_config('quiz', 'autosaveperiod');
if ($autosaveperiod) {
    $PAGE->requires->yui_module('moodle-mod_quiz-autosave',
            'M.mod_quiz.autosave.init', array($autosaveperiod));
}

// Log this page view.
$attemptobj->fire_attempt_viewed_event();

// Get the list of questions needed by this page.
$slots = $attemptobj->get_slots($page);

// Check.
if (empty($slots)) {
    throw new moodle_quiz_exception($attemptobj->get_quizobj(), 'noquestionsfound');
}

// Update attempt page, redirecting the user if $page is not valid.
if (!$attemptobj->set_currentpage($page)) {
    redirect($attemptobj->start_attempt_url(null, $attemptobj->get_currentpage()));
}

// Initialise the JavaScript.
$headtags = $attemptobj->get_html_head_contributions($page);
$PAGE->requires->js_init_call('M.mod_quiz.init_attempt_form', null, false, quiz_get_js_module());

// Arrange for the navigation to be displayed in the first region on the page.
// CHANGE+.
if ($manager && $manager->has_behaviour($attemptobj->get_quizid(), 'alternateattemptpage')) {
    // Standard quiz behaviour.
    $navbc = $attemptobj->get_navigation_panel($output, 'quiz_attempt_nav_panel', $page);
    $regions = $PAGE->blocks->get_regions();
    $PAGE->blocks->add_fake_block($navbc, reset($regions));
} else {
    // Alternate simplified behaviour.
    $bc = new block_contents();
    $bc->attributes['id'] = 'mod_quiz_questioninfo';
    $bc->attributes['role'] = 'info';
    $bc->attributes['aria-labelledby'] = 'mod_quiz_questioninfo_title';
    $bc->title = get_string('question').$output->question_num($attemptobj);
    $bc->content = $output->quiz_progress_indicator($attemptobj);
    $bc->content .= $output->question_refs($slots, $attemptobj);
    $regions = $PAGE->blocks->get_regions();
    $PAGE->blocks->add_fake_block($bc, reset($regions));
}
//CHANGE-.

$title = get_string('attempt', 'quiz', $attemptobj->get_attempt_number());
$headtags = $attemptobj->get_html_head_contributions($page);
$PAGE->set_title($attemptobj->get_quiz_name());
$PAGE->set_heading($attemptobj->get_course()->fullname);

if ($attemptobj->is_last_page($page)) {
    $nextpage = -1;
} else {
    $nextpage = $page + 1;
}

echo $output->attempt_page($attemptobj, $page, $accessmanager, $messages, $slots, $id, $nextpage);
// CHANGE+.
die;
// CHANGE-.