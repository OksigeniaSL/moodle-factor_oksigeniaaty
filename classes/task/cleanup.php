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

namespace factor_oksigeniaaty\task;

/**
 * Every hour, the requests and the codes older than a day go: nobody keeps the browser and the address of a sign-in
 * longer than that, also for whoever does not sign in again.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cleanup extends \core\task\scheduled_task {
    /**
     * The name of the task.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task:cleanup', 'factor_oksigeniaaty');
    }

    /**
     * Delete the requests older than 30 days and the codes to register a phone older than a day.
     */
    public function execute(): void {
        global $DB;
        $before = time() - \factor_oksigeniaaty\local\request::KEEP;
        $DB->delete_records_select(\factor_oksigeniaaty\local\request::TABLE, 'timecreated < ?', [$before]);
        $before = time() - \factor_oksigeniaaty\local\devices::ENROLKEEP;
        $DB->delete_records_select(\factor_oksigeniaaty\local\devices::ENROLTABLE, 'timecreated < ?', [$before]);
    }
}
