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
 * A request to approve a sign-in: the number the sign-in screen shows, the three the phone offers (the right one among
 * them), and what happened to it.
 *
 * The browser that signs in waits for the answer with its own session: the request is tied to a hash of it, so that
 * only that browser can go on with it. The phone answers as the person, with the app's token or a signed-in page, or
 * through a chat of theirs; one answer closes it, whatever it was. A wrong number stops the sign-in as «It was not me»
 * does: whoever taps without looking does not let anyone in.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class request {
    /** @var string The table. */
    public const TABLE = 'factor_oksigeniaaty_req';

    /** @var string Waiting for the phone. */
    public const PENDING = 'pending';
    /** @var string The phone chose the right number: the browser can go on. */
    public const APPROVED = 'approved';
    /** @var string «It was not me». */
    public const DENIED = 'denied';
    /** @var string The phone chose another number. */
    public const WRONG = 'wrong';
    /** @var string Nobody answered in time, or a newer request took its place. */
    public const EXPIRED = 'expired';
    /** @var string The browser went in with it. */
    public const USED = 'used';

    /** @var string The answer «It was not me». */
    public const DENY = 'deny';

    /** @var int Requests a person can get in RECENT seconds: more is someone pressing on. */
    public const MAXRECENT = 3;

    /** @var int After a sign-in stopped from the phone, no new request for this long. */
    public const COOLDOWN = 30 * MINSECS;

    /** @var int Stopped this many times in a day, no new request until the day is over. */
    public const MAXSTOPPED = 3;
    /** @var int No more than this many requests in an hour, so the phone is not filled with them. */
    public const MAXHOUR = 6;
    /** @var int The window of MAXRECENT. */
    public const RECENT = 15 * MINSECS;
    /** @var int How long a request is kept, answered or not. */
    public const KEEP = DAYSECS;

    /**
     * Seconds to answer, from the settings: between half a minute and a quarter of an hour.
     *
     * @return int
     */
    public static function validity(): int {
        $seconds = (int) get_config('factor_oksigeniaaty', 'validity');
        return $seconds > 0 ? max(30, min($seconds, 15 * MINSECS)) : 3 * MINSECS;
    }

    /**
     * The hash of this browser's session, which ties a request to it.
     *
     * @return string
     */
    public static function session_hash(): string {
        return hash('sha256', (string) session_id());
    }

    /**
     * The last request of a session, with its state brought up to date.
     *
     * @param int $userid The person signing in.
     * @param string $sid The hash of the session.
     * @return \stdClass|null
     */
    public static function for_session(int $userid, string $sid): ?\stdClass {
        global $DB;
        $records = $DB->get_records(self::TABLE, ['userid' => $userid, 'sid' => $sid], 'id DESC', '*', 0, 1);
        $request = reset($records);
        return $request ? self::fresh($request) : null;
    }

    /**
     * A request of a person, with its state brought up to date.
     *
     * @param int $id The request.
     * @param int $userid Its person: anyone else's is not found.
     * @return \stdClass|null
     */
    public static function get(int $id, int $userid): ?\stdClass {
        global $DB;
        $request = $DB->get_record(self::TABLE, ['id' => $id, 'userid' => $userid]);
        return $request ? self::fresh($request) : null;
    }

    /**
     * The request the phone should show when it opens with no request in hand: the one waiting, or else the last one,
     * if it is recent.
     *
     * @param int $userid The person.
     * @return \stdClass|null
     */
    public static function latest(int $userid): ?\stdClass {
        global $DB;
        $records = $DB->get_records_select(
            self::TABLE,
            'userid = ? AND timecreated > ?',
            [$userid, time() - 10 * MINSECS],
            'id DESC',
            '*',
            0,
            1
        );
        $request = reset($records);
        return $request ? self::fresh($request) : null;
    }

    /**
     * Whether a person can get another request now.
     *
     * @param int $userid The person.
     * @return bool
     */
    public static function can_create(int $userid): bool {
        return self::held_back($userid) === '';
    }

    /**
     * Why a person cannot get another request now, if they cannot: a sign-in stopped from the phone holds them back
     * for a while (a day, after three), and so do many requests in a row. Whoever has the password and tries again
     * and again does not get a new chance every few minutes.
     *
     * @param int $userid The person.
     * @return string stopped, toomany, or empty if they can.
     */
    public static function held_back(int $userid): string {
        global $DB;
        $now = time();
        [$insql, $params] = $DB->get_in_or_equal([self::DENIED, self::WRONG]);
        $stopped = $DB->get_records_select(
            self::TABLE,
            "userid = ? AND status $insql AND timeanswered > ?",
            array_merge([$userid], $params, [$now - DAYSECS]),
            'timeanswered DESC',
            'id, timeanswered'
        );
        $last = $stopped ? (int) reset($stopped)->timeanswered : 0;
        if ($last > $now - self::COOLDOWN || count($stopped) >= self::MAXSTOPPED) {
            return 'stopped';
        }
        $recent = $DB->count_records_select(self::TABLE, 'userid = ? AND timecreated > ?', [$userid, $now - self::RECENT]);
        $hour = $DB->count_records_select(self::TABLE, 'userid = ? AND timecreated > ?', [$userid, $now - HOURSECS]);
        return $recent < self::MAXRECENT && $hour < self::MAXHOUR ? '' : 'toomany';
    }

    /**
     * A request that could not be sent, or that another way of signing in made unnecessary, stops waiting.
     *
     * @param \stdClass $request The request.
     */
    public static function expire(\stdClass $request): void {
        global $DB;
        $DB->set_field(self::TABLE, 'status', self::EXPIRED, ['id' => $request->id, 'status' => self::PENDING]);
        $request->status = self::EXPIRED;
        chats::close($request, 'expired');
    }

    /**
     * A new request for a sign-in. The one a person still had waiting, in this browser or in another, stops counting:
     * the phone only ever has one to answer.
     *
     * @param int $userid The person signing in.
     * @param string $sid The hash of the session that waits.
     * @param string $device The browser and system, as the phone shows them.
     * @param string $ip The address of the sign-in.
     * @return \stdClass
     */
    public static function create(int $userid, string $sid, string $device, string $ip): \stdClass {
        global $DB;
        $now = time();
        $DB->set_field_select(self::TABLE, 'status', self::EXPIRED, 'userid = ? AND status = ?', [$userid, self::PENDING]);
        $DB->delete_records_select(self::TABLE, 'userid = ? AND timecreated < ?', [$userid, $now - self::KEEP]);

        $code = random_int(10, 99);
        $choices = [$code];
        while (count($choices) < 3) {
            $other = random_int(10, 99);
            // Not one that looks like the right one at a glance: another first digit and another second digit.
            $looksalike = false;
            foreach ($choices as $choice) {
                $looksalike = $looksalike || intdiv($other, 10) === intdiv($choice, 10) || $other % 10 === $choice % 10;
            }
            if (!$looksalike) {
                $choices[] = $other;
            }
        }
        shuffle($choices);

        $request = (object) [
            'userid' => $userid,
            'sid' => $sid,
            'code' => $code,
            'choices' => implode(',', $choices),
            'status' => self::PENDING,
            'device' => \core_text::substr($device, 0, 255),
            'ip' => \core_text::substr($ip, 0, 45),
            'via' => '',
            'timecreated' => $now,
            'timeexpires' => $now + self::validity(),
            'timeanswered' => 0,
        ];
        $request->id = $DB->insert_record(self::TABLE, $request);
        return $request;
    }

    /**
     * Whether this person types the number on the phone instead of choosing it among three: the site's administrators,
     * whose accounts can do the most. Three to choose from is guessed one time in three; typing it is the number matching
     * NIST SP 800-63B asks for.
     *
     * @param int $userid The person.
     * @return bool
     */
    public static function typed(int $userid): bool {
        return is_siteadmin($userid);
    }

    /**
     * The numbers the phone offers, in their order.
     *
     * @param \stdClass $request The request.
     * @return int[]
     */
    public static function choices(\stdClass $request): array {
        return array_map('intval', explode(',', (string) $request->choices));
    }

    /**
     * Answer a request from the phone. Only a request still waiting takes an answer, and only the first one.
     *
     * @param int $id The request.
     * @param int $userid The person answering: a request of anyone else is not found.
     * @param string $choice A number offered, or DENY.
     * @param string $via Where it was answered: app, web, telegram or whatsapp.
     * @return string The state the request is left in, or an empty string if it was not found.
     */
    public static function answer(int $id, int $userid, string $choice, string $via): string {
        global $DB;
        $lock = \core\lock\lock_config::get_lock_factory('factor_oksigeniaaty')->get_lock('request' . $id, 5);
        if (!$lock) {
            $request = self::get($id, $userid);
            return $request ? $request->status : '';
        }
        try {
            $request = self::get($id, $userid);
            if (!$request) {
                return '';
            }
            if ($request->status !== self::PENDING) {
                return $request->status;
            }
            if ($choice === self::DENY) {
                $status = self::DENIED;
            } else if (ctype_digit($choice) && (int) $choice === (int) $request->code) {
                $status = self::APPROVED;
            } else {
                $status = self::WRONG;
            }
            // Only while it still waits: a newer request may have expired it between the reading and now.
            $DB->execute(
                'UPDATE {' . self::TABLE . '} SET status = ?, via = ?, timeanswered = ? WHERE id = ? AND status = ?',
                [$status, \core_text::substr($via, 0, 20), time(), $request->id, self::PENDING]
            );
            $status = (string) $DB->get_field(self::TABLE, 'status', ['id' => $request->id]);
        } finally {
            $lock->release();
        }
        chats::close($request, 'answered');
        if ($status === self::DENIED || $status === self::WRONG) {
            self::end_session($request);
        }
        return $status;
    }

    /**
     * The browser of a stopped sign-in loses its session at once: whoever was signing in cannot try another factor
     * with it. The request keeps only a hash of the session, so it is found among the person's sessions.
     *
     * @param \stdClass $request The request.
     */
    private static function end_session(\stdClass $request): void {
        global $DB;
        foreach ($DB->get_fieldset_select('sessions', 'sid', 'userid = ?', [$request->userid]) as $sid) {
            if (hash_equals((string) $request->sid, hash('sha256', (string) $sid))) {
                \core\session\manager::destroy($sid);
            }
        }
    }

    /**
     * The browser went in with an approved request: it cannot be used again.
     *
     * @param int $id The request.
     */
    public static function mark_used(int $id): void {
        global $DB;
        $DB->set_field(self::TABLE, 'status', self::USED, ['id' => $id, 'status' => self::APPROVED]);
    }

    /**
     * A request with its state up to date: one still waiting after its time is expired.
     *
     * @param \stdClass $request The request as read.
     * @return \stdClass
     */
    private static function fresh(\stdClass $request): \stdClass {
        global $DB;
        if ($request->status === self::PENDING && (int) $request->timeexpires <= time()) {
            $DB->set_field(self::TABLE, 'status', self::EXPIRED, ['id' => $request->id, 'status' => self::PENDING]);
            $request->status = self::EXPIRED;
        }
        return $request;
    }

    /**
     * The browser and system of a sign-in, as the phone shows them: «Chrome on Windows».
     *
     * @param string $useragent The browser's user agent.
     * @return string
     */
    public static function describe(string $useragent): string {
        $browsers = [
            'Edg/' => 'Edge', 'OPR/' => 'Opera', 'SamsungBrowser/' => 'Samsung Internet', 'Firefox/' => 'Firefox',
            'MoodleMobile' => get_string('device:app', 'factor_oksigeniaaty'), 'Chrome/' => 'Chrome',
            'CriOS/' => 'Chrome', 'FxiOS/' => 'Firefox', 'Safari/' => 'Safari',
        ];
        $systems = [
            'CrOS' => 'ChromeOS', 'Android' => 'Android', 'iPhone' => 'iPhone', 'iPad' => 'iPad',
            'Macintosh' => 'macOS', 'Windows' => 'Windows', 'Linux' => 'Linux',
        ];
        $browser = '';
        foreach ($browsers as $needle => $name) {
            if (strpos($useragent, $needle) !== false) {
                $browser = $name;
                break;
            }
        }
        $system = '';
        foreach ($systems as $needle => $name) {
            if (strpos($useragent, $needle) !== false) {
                $system = $name;
                break;
            }
        }
        if ($browser === '' && $system === '') {
            return get_string('device:unknown', 'factor_oksigeniaaty');
        }
        if ($browser === '' || $system === '') {
            return $browser . $system;
        }
        return get_string('device', 'factor_oksigeniaaty', (object) ['browser' => $browser, 'os' => $system]);
    }
}
