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
 * Callbacks of the factor for other plugins.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The question Oksigenia Marandu sends to Telegram and WhatsApp: «Is it you?», with the three numbers and «It was not
 * me». A number's button takes its label from the values of the question; WhatsApp only gets it inside the 24 hours of
 * a conversation, never as a template, whose buttons could not carry the numbers.
 *
 * @return array
 */
function factor_oksigeniaaty_oksigeniamarandu_notices(): array {
    return [
        'login_check' => [
            'examples' => ['Colegio Ejemplo', 'Chrome en Windows, a las 9:41', '42', '17', '85'],
            'options' => ['a', 'b', 'c', 'deny'],
            'optionlabels' => ['a' => 3, 'b' => 4, 'c' => 5],
            'freeonly' => true,
            'uncapped' => true,
            'channels' => ['telegram', 'whatsapp'],
            'perweek' => 1,
        ],
    ];
}
