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
require_once($CFG->dirroot.'/blocks/quiz_behaviour/classeS/manager.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

$attemptid = required_param('attempt', PARAM_INT);

$url = new moodle_url('/blocks/quiz_behaviour/deadend.php', array('attempt' => $attemptid));
$PAGE->set_url($url);

$attemptobj = quiz_attempt::create($attemptid);

require_login($attemptobj->get_course(), $attemptobj->get_cm());

$coursecontext = context_course::instance($attemptobj->get_course()->id);

$PAGE->set_context($coursecontext);
$PAGE->set_heading(get_string('deadendpage', 'block_quiz_behaviour'));

$manager = \block_quiz_behaviour\manager::instance();

echo $OUTPUT->box_start('quiz-deadend-message');
echo $manager->get_deadend_message();
echo $OUTPUT->box_end();

echo $OUTPUT->continue_button($CFG->wwwroot);

echo $OUTPUT->header();



echo $OUTPUT->footer();