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

namespace factor_oksigeniaaty\local;

/**
 * Recovery codes: ten numbers of eight digits, on paper, for the day the phone is lost or out of battery. Each one
 * passes the factor once, in the place of the phone's answer. Only their hashes are kept: the person sees them once,
 * when they are made, to print them; new ones replace the old ones. Using one, or making new ones, is told to the
 * person on every channel, as a new phone is.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class recovery {
    /** @var string The table. */
    public const TABLE = 'factor_oksigeniaaty_codes';

    /** @var int How many codes a person gets. */
    public const COUNT = 10;

    /** @var int Digits of each code. */
    public const DIGITS = 8;

    /**
     * New codes for a person: the ones they had stop counting.
     *
     * @param int $userid The person.
     * @return string[] The codes, as they are printed («4821 7730»): this is the only time they exist in clear.
     */
    public static function create(int $userid): array {
        global $DB;
        $now = time();
        $codes = [];
        while (count($codes) < self::COUNT) {
            $digits = '';
            for ($i = 0; $i < self::DIGITS; $i++) {
                $digits .= (string) random_int(0, 9);
            }
            $codes[$digits] = true;
        }
        $transaction = $DB->start_delegated_transaction();
        $DB->delete_records(self::TABLE, ['userid' => $userid]);
        foreach (array_keys($codes) as $digits) {
            $DB->insert_record(self::TABLE, (object) [
                'userid' => $userid,
                'codehash' => self::hash($userid, (string) $digits),
                'timecreated' => $now,
                'timeused' => 0,
            ]);
        }
        $transaction->allow_commit();
        self::tell($userid, 'made', 0);
        return array_map(fn($digits) => self::show((string) $digits), array_keys($codes));
    }

    /**
     * How many codes a person has left.
     *
     * @param int $userid The person.
     * @return int
     */
    public static function remaining(int $userid): int {
        global $DB;
        return $userid > 0 ? $DB->count_records(self::TABLE, ['userid' => $userid, 'timeused' => 0]) : 0;
    }

    /**
     * When the codes a person has were made, used up or not.
     *
     * @param int $userid The person.
     * @return int The time, or 0 if they have none.
     */
    public static function made(int $userid): int {
        global $DB;
        return (int) $DB->get_field(self::TABLE, 'MAX(timecreated)', ['userid' => $userid]);
    }

    /**
     * A code typed on the sign-in screen: if it is one of the person's, still unused, it is spent and passes.
     *
     * @param int $userid The person signing in.
     * @param string $typed What they typed: spaces, dashes and dots do not count.
     * @return bool
     */
    public static function spend(int $userid, string $typed): bool {
        global $DB;
        $digits = preg_replace('/[\s.\-]+/u', '', $typed);
        if ($userid <= 0 || !preg_match('/^\d{' . self::DIGITS . '}$/', (string) $digits)) {
            return false;
        }
        // One at a time per person: the same code typed in two browsers at once passes only one of them.
        $lock = \core\lock\lock_config::get_lock_factory('factor_oksigeniaaty')->get_lock('recovery' . $userid, 5);
        if (!$lock) {
            return false;
        }
        try {
            $record = $DB->get_record(self::TABLE, [
                'userid' => $userid,
                'codehash' => self::hash($userid, $digits),
                'timeused' => 0,
            ]);
            if (!$record) {
                return false;
            }
            $DB->set_field(self::TABLE, 'timeused', time(), ['id' => $record->id]);
        } finally {
            $lock->release();
        }
        self::tell($userid, 'used', self::remaining($userid));
        return true;
    }

    /**
     * A person's codes are gone: the factor reset for them, or their data deleted.
     *
     * @param int $userid The person.
     */
    public static function delete(int $userid): void {
        global $DB;
        $DB->delete_records(self::TABLE, ['userid' => $userid]);
    }

    /**
     * A code as it is printed: two groups of four.
     *
     * @param string $digits The eight digits.
     * @return string
     */
    public static function show(string $digits): string {
        return substr($digits, 0, 4) . ' ' . substr($digits, 4);
    }

    /**
     * The stored hash of a code: tied to the person, so the same digits are a different row for someone else.
     *
     * @param int $userid The person.
     * @param string $digits The code's digits.
     * @return string
     */
    private static function hash(int $userid, string $digits): string {
        return hash_hmac('sha256', $userid . ':' . $digits, get_site_identifier());
    }

    /**
     * The person is told that their codes were made or one was used: whoever has their session cannot make codes
     * of their own unnoticed, nor use one.
     *
     * @param int $userid The person.
     * @param string $what made or used.
     * @param int $left Codes left, after one was used.
     */
    private static function tell(int $userid, string $what, int $left): void {
        global $OUTPUT;
        $user = \core_user::get_user($userid);
        if (!$user) {
            return;
        }
        $language = force_current_language((string) $user->lang);
        try {
            $a = (object) [
                'time' => userdate(
                    time(),
                    get_string('strftimedatetimeshort', 'langconfig'),
                    \core_date::get_user_timezone($user)
                ),
                'site' => format_string(get_site()->fullname, true, ['escape' => false]),
                'left' => $left,
            ];
            $message = new \core\message\message();
            $message->component = 'factor_oksigeniaaty';
            $message->name = 'recovery';
            $message->userfrom = \core_user::get_noreply_user();
            $message->userto = $user;
            $message->notification = 1;
            $message->subject = get_string('recovery:' . $what . 'subject', 'factor_oksigeniaaty', $a);
            $message->fullmessage = get_string('recovery:' . $what . 'body', 'factor_oksigeniaaty', $a);
            $message->fullmessageformat = FORMAT_PLAIN;
            $message->fullmessagehtml = '';
            $message->smallmessage = $message->subject;
            $message->contexturl = (new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/accesses.php'))->out(false);
            $message->contexturlname = get_string('accesses:title', 'factor_oksigeniaaty');
            $message->customdata = ['notificationiconurl' => $OUTPUT->image_url('icon', 'factor_oksigeniaaty')->out(false)];
            message_send($message);
        } finally {
            force_current_language($language);
        }
    }
}
