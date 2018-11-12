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
namespace block_quiz_behaviour;

use \context_course;
use \moodle_exception;

defined('MOODLE_INTERNAL') || die();

class manager {

    protected $blockinstance;

    /**
     * Protected constructor. Singleton behaviour.
     * Get an internally instanciate the quiz_behaviour block intance for the course.
     */
    protected function __construct() {
        global $COURSE, $DB;

        $coursecontext = context_course::instance($COURSE->id);
        $params = array('blockname' => 'quiz_behaviour', 'parentcontextid' => $coursecontext->id);
        $blockrec = $DB->get_record('block_instances', $params);

        if ($blockrec) {
            $this->blockinstance = block_instance('quiz_behaviour', $blockrec);
        }
        return null;
    }

    public static function instance() {
        static $manager;

        if (isset($manager)) {
            return $manager;
        }

        return new manager();
    }

    public function get_quizzes() {
        global $DB, $COURSE;

        return $DB->get_records('quiz', '', 'id,name', array('course' => $COURSE->id));
    }

    public function has_behaviour($qid, $behaviour) {
        global $DB;

        if (!$DB->record_exists('quiz', array('id' => $qid))) {
            if ($CFG->debug == DEBUG_DEVELOPER) {
                // TODO : auto cleanup of the deleted instances.
                throw moodle_exception("Invalid quiz. May be deleted");
            }
            return false;
        }

        if (empty($this->blockinstance)) {
            return false;
        }

        $key = $behaviour.$qid;
        if (!empty($this->blockinstance->config->$key)) {
            return true;
        }

        // silently answer false in any negative case.
    }

    public function get_deadend_message($qid) {
        $key = 'deadendmessage'.$qid;
        return ''.@$this->blockinstance->config->$key;
    }

    public function get_deadend_caption($qid) {
        $key = 'deadendcaption'.$qid;
        return ''.@$this->blockinstance->config->$key;
    }

    public function get_deadend_target($qid) {
        $key = 'deadendtarget'.$qid;
        return ''.@$this->blockinstance->config->$key;
    }
}
