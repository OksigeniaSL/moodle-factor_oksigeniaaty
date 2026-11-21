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

namespace factor_oksigeniaaty\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use factor_oksigeniaaty\local\chats;
use factor_oksigeniaaty\local\request;

/**
 * The phone answers a request to approve a sign-in: one of the numbers it was offered, or «It was not me».
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class answer extends external_api {
    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'requestid' => new external_value(PARAM_INT, 'The request'),
            'choice' => new external_value(PARAM_ALPHANUM, 'One of the numbers offered, or deny'),
        ]);
    }

    /**
     * Answer as the person: a request of anyone else is not found.
     *
     * @param int $requestid The request.
     * @param string $choice The number, or deny.
     * @return array
     */
    public static function execute(int $requestid, string $choice): array {
        global $USER;
        ['requestid' => $requestid, 'choice' => $choice] = self::validate_parameters(
            self::execute_parameters(),
            ['requestid' => $requestid, 'choice' => $choice]
        );
        $context = \context_user::instance((int) $USER->id);
        self::validate_context($context);

        $status = request::answer($requestid, (int) $USER->id, $choice, 'app');
        return [
            'status' => $status === '' ? 'none' : $status,
            'message' => chats::reply($status),
        ];
    }

    /**
     * What comes back.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_ALPHA, 'approved, denied, wrong, expired, used or none'),
            'message' => new external_value(PARAM_TEXT, 'What to tell the person'),
        ]);
    }
}
