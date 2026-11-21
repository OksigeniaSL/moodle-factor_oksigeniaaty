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
 * In the Moodle app (and in the apps made from it): the page where the phone approves a sign-in, opened by the link of
 * the notice. No entry in the main menu; the handler is there for its init, which registers the link.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$addons = [
    'factor_oksigeniaaty' => [
        'handlers' => [
            'approve' => [
                'delegate' => 'CoreMainMenuDelegate',
                'method' => 'mobile_approve_view',
                'init' => 'mobile_init',
                'displaydata' => [
                    'title' => 'mobile:title',
                    'icon' => 'fas-mobile-screen-button',
                ],
                'priority' => 0,
            ],
        ],
        'lang' => [
            ['mobile:title', 'factor_oksigeniaaty'],
        ],
    ],
];
