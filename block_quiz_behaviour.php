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
 * Block LP main file.
 *
 * @package    block_lp
 * @copyright  2018 Valery Fremaux
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/blocks/quiz_behaviour/classes/manager.php');

/**
 * Block Quiz Behaviour.
 *
 * @package    block_quiz_behaviour
 * @copyright  2018 Valery Fremaux
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_quiz_behaviour extends block_base {

    /**
     * Applicable formats.
     *
     * @return array
     */
    public function applicable_formats() {
        return array('site' => false, 'course' => true, 'my' => false);
    }

    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Init.
     *
     * @return void
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_quiz_behaviour');
    }

    /**
     * Get content.
     *
     * @return stdClass
     */
    public function get_content() {
        global $COURSE, $DB;

        if (isset($this->content)) {
            return $this->content;
        }
        $this->content = new stdClass();

        $coursecontext = context_course::instance($COURSE->id);
        if (!has_capability('moodle/course:manageactivities', $coursecontext)) {
            $this->content->text = '';
            $this->content->footer = '';
            return $this->content;
        }

        $this->content->text = get_string('help', 'block_quiz_behaviour');
        $quiznum = $DB->count_records('quiz', array('course' => $COURSE->id));
        $this->content->text .= get_string('youhave', 'block_quiz_behaviour', $quiznum);
        $this->content->footer = '';

        return $this->content;
    }

    /**
     * Screen protect copy should be setup in all possible screens in the course.
     * We require it in the block it self to lock the overriding course content if possible.
     * Thi is absolutely NOT a full secure solution, just ennoying dummy users.
     */
    public function get_required_javascript() {
        global $PAGE, $COURSE;

        $coursecontext = context_course::instance($COURSE->id);
        if (has_capability('moodle/course:manageactivities', $coursecontext)) {
            return;
        }

        $manager = \block_quiz_behaviour\manager::instance();

        $PAGE->requires->jquery();
        $quizzes = $manager->get_quizzes();
        foreach ($quizzes as $q) {
            // Just one is enough to activate.
            if ($manager->has_behaviour($q->id, 'protect')) {
                $PAGE->requires->js_call_amd('block_quiz_behaviour/quizprotectcopy', 'init');
                $PAGE->requires->css('/blocks/quiz_behaviour/lockselection.css');
                break;
            }
        }
    }
}
