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
use factor_oksigeniaaty\local\devices;

/**
 * The app registers this phone to approve sign-ins, with the code of the QR or link of the factor's setup page, which
 * only shows after another factor. It gets back the phone's key, once; the app keeps it and signs every answer.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol extends external_api {
    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'code' => new external_value(PARAM_ALPHANUM, 'The code of the setup page'),
            'label' => new external_value(PARAM_TEXT, 'The phone, as the app names it', VALUE_DEFAULT, ''),
        ]);
    }

    /**
     * Register the phone of the person the app is signed in as, if the code is theirs and still good.
     *
     * @param string $code The code.
     * @param string $label The phone's name.
     * @return array
     */
    public static function execute(string $code, string $label = ''): array {
        global $USER;
        $params = self::validate_parameters(self::execute_parameters(), ['code' => $code, 'label' => $label]);
        self::validate_context(\context_user::instance((int) $USER->id));
        if (isguestuser()) {
            return ['registered' => false, 'device' => 0, 'secret' => ''];
        }

        $done = devices::complete($params['code'], (int) $USER->id, $params['label']);
        if (!$done) {
            return ['registered' => false, 'device' => 0, 'secret' => ''];
        }
        [$device, $secret] = $done;
        return ['registered' => true, 'device' => $device, 'secret' => $secret];
    }

    /**
     * What comes back.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'registered' => new external_value(PARAM_BOOL, 'Whether the phone is registered now'),
            'device' => new external_value(PARAM_INT, 'The phone, to sign its answers with'),
            'secret' => new external_value(PARAM_RAW, 'The phone\'s key, in base64: only now, and only to this app'),
        ]);
    }
}
