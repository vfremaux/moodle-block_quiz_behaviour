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
 * @package    block_quiz_behaviour
 * @category   blocks
 * @copyright  2018 Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/blocks/quiz_behaviour/classes/manager.php');

/**
 * Returns the quiz_behaviour instance for this course.
 */
function get_block_quiz_behaviour_manager() {
    return \block_quiz_behaviour\manager::instance();
}

/**
 * Adds Jquery form control for single question quizzes
 */
function block_quiz_behaviour_attempt_adds($attemptobj) {
    global $PAGE;

    $manager = get_block_quiz_behaviour_manager();
    if (!$manager) {
        // No additional behaviour required in this course.
        return;
    }

    $course = $attemptobj->get_course();
    $coursecontext = context_course::instance($course->id);

    $qid = $attemptobj->get_quizid();

    $PAGE->requires->jquery();

    if ($manager->has_behaviour($qid, 'forceanswer')) {
        $PAGE->requires->js_call_amd('block_quiz_behaviour/quizforceanswer', 'init');
        $PAGE->requires->css('/blocks/quiz_behaviour/forceanswer.css');
    }

    if ($manager->has_behaviour($qid, 'protect') &&
            !has_capability('moodle/course:manageactivities', $coursecontext)) {
        $PAGE->requires->js_call_amd('block_quiz_behaviour/quizprotectcopy', 'init');
        $PAGE->requires->css('/blocks/quiz_behaviour/lockselection.css');
    }

    if ($manager->has_behaviour($qid, 'trapoutlinks') && $PAGE->pagetype == 'mod-quiz-attempt') {
        $PAGE->requires->js_call_amd('block_quiz_behaviour/quiztrapoutlinks', 'init');
    }

    if ($manager->has_behaviour($qid, 'hideflags')) {
        $PAGE->requires->css('/blocks/quiz_behaviour/hideflags.css');
    }

    if ($manager->has_behaviour($qid, 'nobackwards')) {
        $PAGE->requires->css('/blocks/quiz_behaviour/nobackwards.css');
    }
}
