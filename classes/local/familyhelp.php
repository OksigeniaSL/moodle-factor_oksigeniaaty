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
 * Help from the family, for someone without their phone and without their recovery codes: another adult of their
 * household (Oksigenia Family), signed in on their own account, sees a code of six digits and tells it to them, in
 * person or on the phone. As Apple's recovery contacts do: the code never travels to the person who asks, only to
 * someone who knows them.
 *
 * The code works once, for a few minutes, and only in the browser that asked for it. Every adult of the household with
 * an account is told, any of them can give it, and any of them can say «They did not ask me», which stops it and ends
 * that browser's session. The person is told on every channel when help is asked for and when it is used.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class familyhelp {
    /** @var string The table. */
    public const TABLE = 'factor_oksigeniaaty_help';

    /** @var string Waiting for the code. */
    public const PENDING = 'pending';
    /** @var string The code passed the factor. */
    public const USED = 'used';
    /** @var string An adult of the family said they had not been asked. */
    public const REFUSED = 'refused';
    /** @var string Nobody used it in time, or a newer one took its place. */
    public const EXPIRED = 'expired';

    /** @var int How long a code lasts. */
    public const LIFE = 10 * MINSECS;

    /** @var int Asked this many times in a day, no more until the day is over. */
    public const MAXDAY = 3;

    /** @var string Oksigenia Family's API. */
    private const FAMILY = '\local_oksigeniafamily\api';

    /**
     * Whether the site offers it: Oksigenia Family installed and on, and the setting not turned off.
     *
     * @return bool
     */
    public static function enabled(): bool {
        $family = self::FAMILY;
        if (get_config('factor_oksigeniaaty', 'familyhelp') === '0' || !class_exists($family)) {
            return false;
        }
        return !method_exists($family, 'is_available') || $family::is_available();
    }

    /**
     * The adults of the person's households who can help them: those with an account, active, other than the person.
     *
     * @param int $userid The person.
     * @return int[] Their accounts.
     */
    public static function helpers(int $userid): array {
        if ($userid <= 0 || !self::enabled()) {
            return [];
        }
        $family = self::FAMILY;
        $helpers = [];
        foreach ($family::families_of_user($userid) as $household) {
            foreach ($family::adult_users_of_family((int) $household->familyid) as $adult) {
                if ((int) $adult !== $userid) {
                    $helpers[(int) $adult] = (int) $adult;
                }
            }
        }
        return array_values($helpers);
    }

    /**
     * Whether the person can ask for help now: they have someone to ask, nobody said «They did not ask me» today, and
     * they have not asked too many times today.
     *
     * @param int $userid The person.
     * @return bool
     */
    public static function can_ask(int $userid): bool {
        global $DB;
        if (!self::helpers($userid)) {
            return false;
        }
        $since = time() - DAYSECS;
        $refused = $DB->record_exists_select(
            self::TABLE,
            'userid = ? AND status = ? AND timeanswered > ?',
            [$userid, self::REFUSED, $since]
        );
        if ($refused) {
            return false;
        }
        return $DB->count_records_select(self::TABLE, 'userid = ? AND timecreated > ?', [$userid, $since]) < self::MAXDAY;
    }

    /**
     * Help asked for, from the browser signing in: a new code, and a notice to each adult who can give it. The one the
     * person had waiting stops counting.
     *
     * @param int $userid The person.
     * @param string $sid The hash of the browser's session.
     * @return \stdClass|null The request, or null if they cannot ask now.
     */
    public static function ask(int $userid, string $sid): ?\stdClass {
        global $DB;
        if (!self::can_ask($userid)) {
            return null;
        }
        $now = time();
        self::expire_pending($userid);
        $DB->delete_records_select(self::TABLE, 'userid = ? AND timecreated < ?', [$userid, $now - request::KEEP]);
        $help = (object) [
            'userid' => $userid,
            'sid' => $sid,
            'code' => sprintf('%06d', random_int(0, 999999)),
            'status' => self::PENDING,
            'timecreated' => $now,
            'timeexpires' => $now + self::LIFE,
            'timeanswered' => 0,
        ];
        $help->id = $DB->insert_record(self::TABLE, $help);
        foreach (self::helpers($userid) as $helper) {
            self::tell_helper($help, $helper);
        }
        self::tell($userid, 'asked');
        return $help;
    }

    /**
     * The request the browser is waiting with, if any, brought up to date.
     *
     * @param int $userid The person.
     * @param string $sid The hash of the browser's session.
     * @return \stdClass|null
     */
    public static function for_session(int $userid, string $sid): ?\stdClass {
        global $DB;
        $records = $DB->get_records(self::TABLE, ['userid' => $userid, 'sid' => $sid], 'id DESC', '*', 0, 1);
        $help = reset($records);
        return $help ? self::fresh($help) : null;
    }

    /**
     * A code typed on the sign-in screen: if it is the one this browser is waiting for, it is spent and passes.
     *
     * @param int $userid The person.
     * @param string $sid The hash of the browser's session.
     * @param string $typed What they typed: spaces and dashes do not count.
     * @return bool
     */
    public static function spend(int $userid, string $sid, string $typed): bool {
        global $DB;
        $digits = preg_replace('/[\s.\-]+/u', '', $typed);
        if (!preg_match('/^\d{6}$/', (string) $digits)) {
            return false;
        }
        $lock = \core\lock\lock_config::get_lock_factory('factor_oksigeniaaty')->get_lock('help' . $userid, 5);
        if (!$lock) {
            return false;
        }
        try {
            $help = self::for_session($userid, $sid);
            if (!$help || $help->status !== self::PENDING || !hash_equals((string) $help->code, $digits)) {
                return false;
            }
            $DB->update_record(self::TABLE, (object) [
                'id' => $help->id,
                'status' => self::USED,
                'code' => '',
                'timeanswered' => time(),
            ]);
        } finally {
            $lock->release();
        }
        self::tell($userid, 'used');
        return true;
    }

    /**
     * A request as an adult of the family sees it, when they are one of those who can give the code.
     *
     * @param int $id The request.
     * @param int $helperid The adult, signed in.
     * @return \stdClass|null The request, brought up to date, or null if it is not theirs to see.
     */
    public static function for_helper(int $id, int $helperid): ?\stdClass {
        global $DB;
        $help = $DB->get_record(self::TABLE, ['id' => $id]);
        if (!$help || !in_array($helperid, self::helpers((int) $help->userid), true)) {
            return null;
        }
        return self::fresh($help);
    }

    /**
     * An adult of the family says they were not asked: the code stops counting, the browser that asked loses its
     * session, and the person is told.
     *
     * @param int $id The request.
     * @param int $helperid The adult, signed in.
     * @return bool Whether it was still waiting.
     */
    public static function refuse(int $id, int $helperid): bool {
        global $DB;
        $help = self::for_helper($id, $helperid);
        if (!$help || $help->status !== self::PENDING) {
            return false;
        }
        $DB->update_record(self::TABLE, (object) [
            'id' => $help->id,
            'status' => self::REFUSED,
            'code' => '',
            'timeanswered' => time(),
        ]);
        foreach ($DB->get_fieldset_select('sessions', 'sid', 'userid = ?', [$help->userid]) as $sid) {
            if (hash_equals((string) $help->sid, hash('sha256', (string) $sid))) {
                \core\session\manager::destroy($sid);
            }
        }
        self::tell((int) $help->userid, 'refused');
        return true;
    }

    /**
     * The person's requests still waiting stop counting.
     *
     * @param int $userid The person.
     */
    public static function expire_pending(int $userid): void {
        global $DB;
        $DB->execute(
            'UPDATE {' . self::TABLE . "} SET status = ?, code = '' WHERE userid = ? AND status = ?",
            [self::EXPIRED, $userid, self::PENDING]
        );
    }

    /**
     * A person's requests are gone: their data deleted.
     *
     * @param int $userid The person.
     */
    public static function delete(int $userid): void {
        global $DB;
        $DB->delete_records(self::TABLE, ['userid' => $userid]);
    }

    /**
     * A request with its state up to date: one still waiting after its time is expired, and its code wiped.
     *
     * @param \stdClass $help The request as read.
     * @return \stdClass
     */
    private static function fresh(\stdClass $help): \stdClass {
        global $DB;
        if ($help->status === self::PENDING && (int) $help->timeexpires <= time()) {
            $DB->update_record(self::TABLE, (object) ['id' => $help->id, 'status' => self::EXPIRED, 'code' => '']);
            $help->status = self::EXPIRED;
            $help->code = '';
        }
        return $help;
    }

    /**
     * An adult of the family is asked to give the code: the notice leads to the page that shows it, after they sign in.
     *
     * @param \stdClass $help The request.
     * @param int $helperid The adult.
     */
    private static function tell_helper(\stdClass $help, int $helperid): void {
        $person = \core_user::get_user((int) $help->userid);
        $helper = \core_user::get_user($helperid);
        if (!$person || !$helper) {
            return;
        }
        $a = (object) ['name' => fullname($person), 'minutes' => (int) (self::LIFE / MINSECS)];
        $url = new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/help.php', ['h' => $help->id]);
        self::send($helper, 'familyhelp', 'familyhelp:helper', $a, $url, 'familyhelp:open');
    }

    /**
     * The person is told that help was asked for, used, or refused by an adult of the family.
     *
     * @param int $userid The person.
     * @param string $what asked, used or refused.
     */
    private static function tell(int $userid, string $what): void {
        $user = \core_user::get_user($userid);
        if (!$user) {
            return;
        }
        $url = new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/accesses.php');
        self::send($user, 'recovery', 'familyhelp:' . $what, (object) [], $url, 'accesses:title');
    }

    /**
     * A notice, in the language of whoever gets it.
     *
     * @param \stdClass $user Who gets it.
     * @param string $provider The message provider.
     * @param string $key The strings: key . 'subject' and key . 'body'.
     * @param \stdClass $a What the strings take; the site's name and the time are added.
     * @param \moodle_url $url Where it leads.
     * @param string $urlname The string of the link.
     */
    private static function send(
        \stdClass $user,
        string $provider,
        string $key,
        \stdClass $a,
        \moodle_url $url,
        string $urlname
    ): void {
        global $OUTPUT;
        $language = force_current_language((string) $user->lang);
        try {
            $a->site = format_string(get_site()->fullname, true, ['escape' => false]);
            $a->time = userdate(time(), get_string('strftimetime', 'langconfig'), \core_date::get_user_timezone($user));
            $message = new \core\message\message();
            $message->component = 'factor_oksigeniaaty';
            $message->name = $provider;
            $message->userfrom = \core_user::get_noreply_user();
            $message->userto = $user;
            $message->notification = 1;
            $message->subject = get_string($key . 'subject', 'factor_oksigeniaaty', $a);
            $message->fullmessage = get_string($key . 'body', 'factor_oksigeniaaty', $a);
            $message->fullmessageformat = FORMAT_PLAIN;
            $message->fullmessagehtml = '';
            $message->smallmessage = $message->subject;
            $message->contexturl = $url->out(false);
            $message->contexturlname = get_string($urlname, 'factor_oksigeniaaty');
            $message->customdata = ['notificationiconurl' => $OUTPUT->image_url('icon', 'factor_oksigeniaaty')->out(false)];
            $pieces = commonmail::security($message, $user, $a->site);
            if ($provider === 'familyhelp') {
                // An adult asked to help, not the owner of the account.
                $pieces['reason'] = get_string('familyhelp:helperreason', 'factor_oksigeniaaty', $a);
            }
            commonmail::notice($message, $pieces);
            message_send($message);
        } finally {
            force_current_language($language);
        }
    }
}
