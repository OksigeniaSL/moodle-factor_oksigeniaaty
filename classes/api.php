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

namespace factor_oksigeniaaty;

use factor_oksigeniaaty\local\devices;
use factor_oksigeniaaty\local\request;

/**
 * What other plugins and the site's scripts may call.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api {
    /**
     * An account given to another person (a school gives the account of whoever left to someone else): the phones the
     * former owner registered can no longer approve its sign-ins, and nothing of theirs stays waiting.
     *
     * @param int $userid The account.
     */
    public static function account_reassigned(int $userid): void {
        global $DB;
        $DB->set_field('tool_mfa', 'revoked', 1, ['userid' => $userid, 'factor' => devices::FACTOR]);
        $DB->delete_records(request::TABLE, ['userid' => $userid]);
        $DB->delete_records(devices::ENROLTABLE, ['userid' => $userid]);
    }
}
