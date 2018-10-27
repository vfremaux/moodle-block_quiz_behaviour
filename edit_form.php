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

class block_quiz_behaviour_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        global $DB, $COURSE;

        $mform->addElement('header', 'configheader', get_string('quizbehaviourconfig', 'block_quiz_behaviour'));

        $quizzes = $DB->get_records('quiz', array('course' => $COURSE->id));

        foreach ($quizzes as $q) {

            $qname = format_string($q->name);

            $mform->addElement('advcheckbox', 'config_alternateattemptpage'.$q->id, $qname, get_string('alternateattemptpage', 'block_quiz_behaviour'));

            $group = array();
            $group[] = $mform->createElement('advcheckbox', 'config_startnewever'.$q->id, '');
            $group[] = $mform->createElement('static', '', ''); // Add a last empty elm for labels.

            $labels = array('&nbsp;'.get_string('startnewever', 'block_quiz_behaviour').'&ensp;&ensp;&ensp;');

            $mform->addGroup($group, 'gr'.$q->id, get_string('beforeattempt', 'block_quiz_behaviour'), $labels, false);

            $group = array();
            $group[] = $mform->createElement('advcheckbox', 'config_protect'.$q->id, '');
            $group[] = $mform->createElement('advcheckbox', 'config_trapoutlinks'.$q->id, '');
            $group[] = $mform->createElement('advcheckbox', 'config_forceanswer'.$q->id, '');
            $group[] = $mform->createElement('advcheckbox', 'config_nobackwards'.$q->id, '');
            $group[] = $mform->createElement('static', '', ''); // Add a last empty elm for labels.

            $labels = array('&nbsp;'.get_string('screenprotect', 'block_quiz_behaviour').'&ensp;&ensp;&ensp;',
                            '&nbsp;'.get_string('trapoutlinks', 'block_quiz_behaviour').'&ensp;&ensp;&ensp;',
                            '&nbsp;'.get_string('forceanswer', 'block_quiz_behaviour').'&ensp;&ensp;&ensp;',
                            '&nbsp;'.get_string('nobackwards', 'block_quiz_behaviour')
            );

            $mform->addGroup($group, 'gr'.$q->id, get_string('duringattempt', 'block_quiz_behaviour'), $labels, false);

            $group = array();
            $group[] = $mform->createElement('advcheckbox', 'config_hidesummaryinfo'.$q->id, '');
            $group[] = $mform->createElement('advcheckbox', 'config_directreturn'.$q->id, '');
            $group[] = $mform->createElement('advcheckbox', 'config_deadend'.$q->id, '');
            $group[] = $mform->createElement('static', '', ''); // Add a last empty elm for labels.

            $labels = array('&nbsp;'.get_string('hidesummaryinfo', 'block_quiz_behaviour').'&ensp;&ensp;&ensp;',
                            '&nbsp;'.get_string('directreturn', 'block_quiz_behaviour').'&ensp;&ensp;&ensp;',
                            '&nbsp;'.get_string('deadend', 'block_quiz_behaviour')
            );

            $mform->addGroup($group, 'gr'.$q->id, get_string('afterattempt', 'block_quiz_behaviour'), $labels, false);

            $mform->addElement('advcheckbox', 'config_hideflags'.$q->id, '', get_string('hideflags', 'block_quiz_behaviour'));

            $attrs = array('size' => 255);
            $mform->addElement('text', 'config_deadendcaption'.$q->id, get_string('deadendcaption', 'block_quiz_behaviour'), $attrs);
            $mform->setType('config_deadendcaption'.$q->id, PARAM_CLEANHTML);

            $attrs = array('cols' => 80, 'rows' => 10);
            $mform->addElement('textarea', 'config_deadendmessage'.$q->id, get_string('deadendmessage', 'block_quiz_behaviour'), $attrs);
            $mform->setType('config_deadendmessage'.$q->id, PARAM_CLEANHTML);
            $mform->addHelpButton('config_deadendmessage'.$q->id, 'deadendmessage', 'block_quiz_behaviour');

            $targetoptions = array('course' => get_string('course'),
                                   'site' => get_string('site'));
            $mform->addElement('select', 'config_deadendtarget'.$q->id, get_string('deadendtarget', 'block_quiz_behaviour'), $targetoptions);
        }
    }
}
