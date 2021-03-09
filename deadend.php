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
 * @package     block_quiz_behaviour
 * @category    blocks
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require_once($CFG->dirroot.'/blocks/quiz_behaviour/classes/manager.php');
require_once($CFG->dirroot.'/mod/quiz/locallib.php');

$attemptid = required_param('attempt', PARAM_INT);
$qid = $attemptobj->get_quizid();

$url = new moodle_url('/blocks/quiz_behaviour/deadend.php', array('attempt' => $attemptid));
$PAGE->set_url($url);

$attemptobj = quiz_attempt::create($attemptid);

require_login($attemptobj->get_course(), $attemptobj->get_cm());

$coursecontext = context_course::instance($attemptobj->get_course()->id);

$PAGE->set_context($coursecontext);
$PAGE->set_heading(get_string('deadendpage', 'block_quiz_behaviour'));

$manager = \block_quiz_behaviour\manager::instance();

echo $OUTPUT->header();

echo $OUTPUT->heading($manager->get_deadend_caption($qid));

echo $OUTPUT->box_start('quiz-deadend-message');

$message = $manager->get_deadend_message($qid);

$qname = format_string($attemptobj->get_quiz()->name);

$message = str_replace('%%FIRSTNAME%%', $USER->firstname, $message);
$message = str_replace('%%LASTNAME%%', $USER->lastname, $message);
$message = str_replace('%%QUIZNAME%%', $qname, $message);

echo $message;
echo $OUTPUT->box_end();

if ($manager->get_deadend_target())
$buttonurl = new moodle_url('/course/view.php', array('id' => $course->id));
echo '<p>';
echo $OUTPUT->continue_button($buttonurl);
echo '</p>';

echo $OUTPUT->footer();