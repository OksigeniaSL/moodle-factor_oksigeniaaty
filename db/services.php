<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Web services of the factor.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'factor_oksigeniaaty_answer' => [
        'classname' => \factor_oksigeniaaty\external\answer::class,
        'description' => 'Answers, from the phone, a request to approve a sign-in.',
        'type' => 'write',
        'ajax' => false,
        'loginrequired' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
    'factor_oksigeniaaty_enrol' => [
        'classname' => \factor_oksigeniaaty\external\enrol::class,
        'description' => 'Registers this phone to approve sign-ins, with the code of a page that passed another factor.',
        'type' => 'write',
        'ajax' => false,
        'loginrequired' => true,
        'services' => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],
];
