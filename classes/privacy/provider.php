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


namespace factor_oksigeniaaty\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * The requests to approve a sign-in, in the user's context. They are kept for a day.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {

    /** @var string The table. */
    private const TABLE = 'factor_oksigeniaaty_req';

    /**
     * What is stored.
     *
     * @param collection $collection The collection.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $fields = [];
        foreach (['userid', 'device', 'ip', 'status', 'via', 'timecreated', 'timeanswered'] as $field) {
            $fields[$field] = 'privacy:metadata:' . self::TABLE . ':' . $field;
        }
        $collection->add_database_table(self::TABLE, $fields, 'privacy:metadata:' . self::TABLE);
        $collection->add_subsystem_link('core_message', [], 'messageprovider:approval');
        return $collection;
    }

    /**
     * The user's context, if they have requests.
     *
     * @param int $userid The user.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $sql = 'SELECT ctx.id
                  FROM {context} ctx
                  JOIN {' . self::TABLE . '} r ON r.userid = ctx.instanceid AND ctx.contextlevel = :level
                 WHERE r.userid = :userid';
        $contextlist->add_from_sql($sql, ['level' => CONTEXT_USER, 'userid' => $userid]);
        return $contextlist;
    }

    /**
     * The users with requests in a context: only the user of a user context.
     *
     * @param userlist $userlist The list.
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_USER) {
            return;
        }
        $userlist->add_from_sql('userid', 'SELECT userid FROM {' . self::TABLE . '} WHERE userid = :userid',
            ['userid' => $context->instanceid]);
    }

    /**
     * Export the user's requests.
     *
     * @param approved_contextlist $contextlist The contexts.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;
        $userid = (int) $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_USER || (int) $context->instanceid !== $userid) {
                continue;
            }
            $requests = [];
            foreach ($DB->get_records(self::TABLE, ['userid' => $userid], 'id') as $request) {
                $requests[] = (object) [
                    'device' => $request->device,
                    'ip' => $request->ip,
                    'status' => $request->status,
                    'via' => $request->via,
                    'timecreated' => transform::datetime($request->timecreated),
                    'timeanswered' => $request->timeanswered ? transform::datetime($request->timeanswered) : '',
                ];
            }
            if ($requests) {
                writer::with_context($context)->export_data([get_string('pluginname', 'factor_oksigeniaaty')],
                    (object) ['requests' => $requests]);
            }
        }
    }

    /**
     * Delete the requests of everyone in a context.
     *
     * @param \context $context The context.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;
        if ($context->contextlevel == CONTEXT_USER) {
            $DB->delete_records(self::TABLE, ['userid' => $context->instanceid]);
        }
    }

    /**
     * Delete the user's requests.
     *
     * @param approved_contextlist $contextlist The contexts.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;
        $userid = (int) $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_USER && (int) $context->instanceid === $userid) {
                $DB->delete_records(self::TABLE, ['userid' => $userid]);
            }
        }
    }

    /**
     * Delete the requests of the users of a list.
     *
     * @param approved_userlist $userlist The list.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;
        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_USER) {
            return;
        }
        foreach ($userlist->get_userids() as $userid) {
            if ((int) $userid === (int) $context->instanceid) {
                $DB->delete_records(self::TABLE, ['userid' => $userid]);
            }
        }
    }
}
