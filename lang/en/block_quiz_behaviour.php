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
 * Block Quiz Behaviour language strings.
 *
 * @package    block_quiz_behaviour
 * @copyright  2018 Valery Fremaux
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['quiz_behaviour:addinstance'] = 'Add a Quiz Behaviour block';

// Privacy.
$string['privacy:metadata'] = 'The Quiz Behaviour block plugin does not store itself any personal data about the users.';

$string['collapseall'] = 'Collapse all';
$string['expandall'] = 'Expand all';
$string['showempty'] = 'Show empty categories';
$string['showall'] = 'Show all categories';

$string['afterattempt'] = 'After attempt';
$string['alternateattemptpage'] = 'Alternative layout of the attempt page';
$string['backtocourse'] = 'Back to course';
$string['beforeattempt'] = 'Before attempt';
$string['deadend'] = 'Dead end';
$string['deadendcaption'] = 'Dead end caption';
$string['deadendmessage'] = 'Dead end message';
$string['deadendtarget'] = 'Dead end outgoing target';
$string['directreturn'] = 'Direct return';
$string['deadendpage'] = 'Quiz exit';
$string['duringattempt'] = 'During attempt';
$string['finalvalidation'] = 'Final validation';
$string['finalvalidation_desc'] = 'Please confirm saving all your answers. You will not be allowed to go back any more after this action.';
$string['forceanswer'] = 'Force answer';
$string['help'] = 'Just edit this block to change behaviour of quizzes.';
$string['hideflags'] = 'Hide question flagging';
$string['hidesummaryinfo'] = 'Hide summary info';
$string['looseattemptsignal'] = 'You are quitting the test and will loose all results of the attempt. Do you want to continue?';
$string['nobackwards'] = 'No backwards';
$string['pluginname'] = 'Quiz Behaviour Modifier';
$string['questioncategory'] = 'Category';
$string['questionreference'] = 'Question Ref';
$string['quizbehaviourconfig'] = 'Quiz behaviour configuration';
$string['screenprotect'] = 'Screen protect';
$string['startnewever'] = 'Always restart a new attempt';
$string['trapoutlinks'] = 'Trap outgoing links';
$string['youhave'] = 'You have {$a} quizzes in this course';

$string['deadendmessage_help'] = 'Dead end message accepts simple HTML and the following placeholders : %%FIRSTNAME%%, %%LASTNAME%%, %%QUIZNAME%% ';
