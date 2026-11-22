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

use stdClass;

/**
 * The phones that can approve a person's sign-ins: each one registered from a session that already passed another
 * factor, with a key of its own that only the phone and the site know. A phone answers by signing «request:choice»
 * with that key; without it, an answer does not count. Whoever only has the password can get a token of the app, but
 * never a key: the code to register a phone only shows on a page that waits for the factor.
 *
 * Each phone is a row of tool_mfa, as the codes app does with its secrets: the multi-factor pages list them, and
 * revoke them, as any other.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class devices {
    /** @var string The factor, in tool_mfa. */
    public const FACTOR = 'oksigeniaaty';

    /** @var string The codes to register a phone. */
    public const ENROLTABLE = 'factor_oksigeniaaty_enrol';

    /** @var int How long a code to register a phone lasts. */
    public const ENROLLIFE = 10 * MINSECS;

    /** @var int How long a code to register a phone is kept, used or not. */
    public const ENROLKEEP = DAYSECS;

    /** @var string Waiting for the app. */
    public const PENDING = 'pending';
    /** @var string The app registered the phone. */
    public const DONE = 'done';
    /** @var string Nobody used it in time, or a newer one took its place. */
    public const EXPIRED = 'expired';

    /**
     * A new code to register a phone, for a session that passed the factor; the one the person had waiting stops
     * counting.
     *
     * @param int $userid The person.
     * @return array [id, code]: the code goes only in the QR and the link of that page.
     */
    public static function new_enrolment(int $userid): array {
        global $DB;
        $now = time();
        $DB->set_field(self::ENROLTABLE, 'status', self::EXPIRED, ['userid' => $userid, 'status' => self::PENDING]);
        $DB->delete_records_select(self::ENROLTABLE, 'timecreated < ?', [$now - self::ENROLKEEP]);
        $code = bin2hex(random_bytes(16));
        $id = $DB->insert_record(self::ENROLTABLE, (object) [
            'userid' => $userid,
            'codehash' => hash('sha256', $code),
            'status' => self::PENDING,
            'toolmfaid' => 0,
            'timecreated' => $now,
            'timeexpires' => $now + self::ENROLLIFE,
        ]);
        return [(int) $id, $code];
    }

    /**
     * A code of a person, with its state up to date.
     *
     * @param int $id The code's row.
     * @param int $userid The person: anyone else's is not found.
     * @return stdClass|null
     */
    public static function enrolment(int $id, int $userid): ?stdClass {
        global $DB;
        $enrol = $DB->get_record(self::ENROLTABLE, ['id' => $id, 'userid' => $userid]);
        if (!$enrol) {
            return null;
        }
        if ($enrol->status === self::PENDING && (int) $enrol->timeexpires <= time()) {
            $DB->set_field(self::ENROLTABLE, 'status', self::EXPIRED, ['id' => $enrol->id, 'status' => self::PENDING]);
            $enrol->status = self::EXPIRED;
        }
        return $enrol;
    }

    /**
     * The app registers the phone, as the person whose code it is: once, while it lasts.
     *
     * @param string $code The code of the QR or the link.
     * @param int $userid The person the app is signed in as.
     * @param string $label The phone, as the app names it («Pixel 8»).
     * @return array|null [device, secret], or null if the code is not good for this person now.
     */
    public static function complete(string $code, int $userid, string $label): ?array {
        global $DB;
        $hash = hash('sha256', $code);
        $lock = \core\lock\lock_config::get_lock_factory('factor_oksigeniaaty')->get_lock('enrol' . $hash, 5);
        if (!$lock) {
            return null;
        }
        try {
            $enrol = $DB->get_record(self::ENROLTABLE, ['codehash' => $hash, 'status' => self::PENDING]);
            if (!$enrol || (int) $enrol->userid !== $userid || (int) $enrol->timeexpires <= time()) {
                return null;
            }
            $secret = base64_encode(random_bytes(32));
            $label = trim(clean_param($label, PARAM_TEXT));
            $now = time();
            $deviceid = $DB->insert_record('tool_mfa', (object) [
                'userid' => $userid,
                'factor' => self::FACTOR,
                'secret' => $secret,
                'label' => \core_text::substr($label !== '' ? $label : get_string('label', 'factor_oksigeniaaty'), 0, 100),
                'timecreated' => $now,
                'createdfromip' => getremoteaddr(),
                'timemodified' => $now,
                'lastverified' => 0,
                'revoked' => 0,
            ]);
            $DB->update_record(self::ENROLTABLE, (object) [
                'id' => $enrol->id,
                'status' => self::DONE,
                'toolmfaid' => $deviceid,
            ]);
        } finally {
            $lock->release();
        }
        self::tell_new_phone($userid, (int) $deviceid);
        return [(int) $deviceid, $secret];
    }

    /**
     * The phones of a person that can approve now.
     *
     * @param int $userid The person.
     * @return stdClass[]
     */
    public static function active(int $userid): array {
        global $DB;
        return $DB->get_records_select(
            'tool_mfa',
            "userid = ? AND factor = ? AND revoked = 0 AND secret IS NOT NULL AND secret <> ''",
            [$userid, self::FACTOR]
        );
    }

    /**
     * Whether an answer comes from a registered phone of the person: the signature of «request:choice» with its key.
     *
     * @param int $deviceid The phone (its tool_mfa row).
     * @param int $userid The person answering.
     * @param string $message What was signed.
     * @param string $proof The signature, in base64.
     * @return bool
     */
    public static function verify(int $deviceid, int $userid, string $message, string $proof): bool {
        global $DB;
        $device = $DB->get_record('tool_mfa', ['id' => $deviceid, 'userid' => $userid, 'factor' => self::FACTOR, 'revoked' => 0]);
        $key = $device ? base64_decode((string) $device->secret, true) : false;
        $given = base64_decode($proof, true);
        if ($key === false || $key === '' || $given === false) {
            return false;
        }
        if (!hash_equals(hash_hmac('sha256', $message, $key, true), $given)) {
            return false;
        }
        $DB->update_record('tool_mfa', (object) ['id' => $device->id, 'lastverified' => time(), 'timemodified' => time()]);
        return true;
    }

    /**
     * A new phone can approve the person's sign-ins: they are told, on every channel they get notices on, so that a
     * phone registered by someone else does not go unnoticed.
     *
     * @param int $userid The person.
     * @param int $deviceid The phone.
     */
    private static function tell_new_phone(int $userid, int $deviceid): void {
        global $DB, $OUTPUT;
        $user = \core_user::get_user($userid);
        $device = $DB->get_record('tool_mfa', ['id' => $deviceid]);
        if (!$user || !$device) {
            return;
        }
        $language = force_current_language((string) $user->lang);
        try {
            $a = (object) [
                'device' => $device->label,
                'time' => userdate(
                    (int) $device->timecreated,
                    get_string('strftimedatetimeshort', 'langconfig'),
                    \core_date::get_user_timezone($user)
                ),
                'site' => format_string(get_site()->fullname, true, ['escape' => false]),
            ];
            $message = new \core\message\message();
            $message->component = 'factor_oksigeniaaty';
            $message->name = 'newphone';
            $message->userfrom = \core_user::get_noreply_user();
            $message->userto = $user;
            $message->notification = 1;
            $message->subject = get_string('newphone:subject', 'factor_oksigeniaaty', $a);
            $message->fullmessage = get_string('newphone:body', 'factor_oksigeniaaty', $a);
            $message->fullmessageformat = FORMAT_PLAIN;
            $message->fullmessagehtml = '';
            $message->smallmessage = $message->subject;
            $message->contexturl = (new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/accesses.php'))->out(false);
            $message->contexturlname = get_string('newphone:link', 'factor_oksigeniaaty');
            // The app draws its own icon only beside notices of activities; anyone else's, it takes from the notice.
            $message->customdata = ['notificationiconurl' => $OUTPUT->image_url('icon', 'factor_oksigeniaaty')->out(false)];
            message_send($message);
        } finally {
            force_current_language($language);
        }
    }
}
