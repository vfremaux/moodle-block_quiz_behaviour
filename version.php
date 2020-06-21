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
 * Block Quiz behaviour teacher utility.
 *
 * @package    block_quiz_behaviour
 * @copyright  2018 Valery Fremaux
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2018040200;
$plugin->requires  = 2019111200;
$plugin->maturity = MATURITY_RC;
$plugin->release  = "3.8.0 (Build 2018040200)";
$plugin->component = 'block_quiz_behaviour';

// Non moodle attributes.
$plugin->codeincrement = '3.8.0000';
$plugin->privacy = 'public';
