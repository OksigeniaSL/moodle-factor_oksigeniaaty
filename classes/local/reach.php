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
 * Where a request reaches the person: the phone with the app, through the site's push notices, and the chats of
 * Oksigenia Marandu.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reach {
    /**
     * Whether the person can get a request somewhere: without it, the factor does not offer itself.
     *
     * @param int $userid The person.
     * @return bool
     */
    public static function any(int $userid): bool {
        return self::app($userid) || chats::available($userid);
    }

    /**
     * Whether the person's phone gets the site's push notices for this one: the site sends them, the person has a
     * phone with the app registered, and has not turned these notices off on the phone.
     *
     * @param int $userid The person.
     * @return bool
     */
    public static function app(int $userid): bool {
        global $CFG, $DB;
        if (empty($CFG->airnotifieraccesskey) || !$DB->get_field('message_processors', 'enabled', ['name' => 'airnotifier'])) {
            return false;
        }
        if (!$DB->record_exists('user_devices', ['userid' => $userid])) {
            return false;
        }
        $chosen = get_user_preferences('message_provider_factor_oksigeniaaty_approval_enabled', null, $userid);
        return $chosen === null || in_array('airnotifier', explode(',', (string) $chosen), true);
    }

    /**
     * Send a request to the phone and the chats.
     *
     * @param \stdClass $request The request.
     * @return bool Whether it went anywhere.
     */
    public static function send(\stdClass $request): bool {
        $user = \core_user::get_user((int) $request->userid);
        if (!$user || $user->deleted || $user->suspended) {
            return false;
        }
        // In the person's language, wherever the request is made from.
        $language = force_current_language((string) $user->lang);
        try {
            return self::deliver($request, $user);
        } finally {
            force_current_language($language);
        }
    }

    /**
     * Send a request, in the language already set.
     *
     * @param \stdClass $request The request.
     * @param \stdClass $user The person.
     * @return bool Whether it went anywhere.
     */
    private static function deliver(\stdClass $request, \stdClass $user): bool {
        $where = get_string('where', 'factor_oksigeniaaty', (object) [
            'device' => $request->device,
            'time' => userdate((int) $request->timecreated, get_string('strftimetime', 'langconfig'),
                \core_date::get_user_timezone($user)),
        ]);
        $sent = false;
        if (self::app((int) $request->userid)) {
            $url = new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/approve.php', ['r' => $request->id]);
            $site = format_string(get_site()->fullname);
            $message = new \core\message\message();
            $message->component = 'factor_oksigeniaaty';
            $message->name = 'approval';
            $message->userfrom = \core_user::get_noreply_user();
            $message->userto = $user;
            $message->notification = 1;
            $message->subject = get_string('notify:subject', 'factor_oksigeniaaty', $site);
            $message->smallmessage = get_string('notify:body', 'factor_oksigeniaaty', $where);
            $message->fullmessage = $message->smallmessage;
            $message->fullmessageformat = FORMAT_PLAIN;
            $message->fullmessagehtml = '';
            $message->contexturl = $url->out(false);
            $message->contexturlname = get_string('notify:link', 'factor_oksigeniaaty');
            $sent = (bool) message_send($message);
        }
        return chats::ask($request, $where) > 0 || $sent;
    }
}
