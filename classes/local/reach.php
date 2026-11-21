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
     * @param bool $toapp Whether the app counts: not when the sign-in is in the app's own window (in_app_window()).
     * @return bool
     */
    public static function any(int $userid, bool $toapp = true): bool {
        return ($toapp && self::app($userid)) || chats::available($userid);
    }

    /**
     * A link that opens this site's page in the app on this same phone, by the app's own scheme (the site's, or the
     * Moodle app's): for the setup page seen on the phone that registers itself.
     *
     * @param \moodle_url $url The page.
     * @return string
     */
    public static function app_link(\moodle_url $url): string {
        $scheme = trim((string) get_config('tool_mobile', 'forcedurlscheme'));
        $scheme = preg_match('/^[a-z][a-z0-9+.-]*$/i', $scheme) ? $scheme : 'moodlemobile';
        return $scheme . '://link=' . $url->out(false);
    }

    /**
     * Whether this sign-in happens in the window of the Moodle app (or of an app made from it). There, the app's own
     * notice would open the numbers under the window, out of sight: only the chats, which are other apps, can ask.
     *
     * @return bool
     */
    public static function in_app_window(): bool {
        return \core_useragent::is_moodle_app();
    }

    /**
     * Whether the person's phone gets the site's push notices for this one and can answer: the site sends them, the
     * person registered a phone for this factor and has the app on a phone, and has not turned these notices off.
     *
     * @param int $userid The person.
     * @return bool
     */
    public static function app(int $userid): bool {
        global $CFG, $DB;
        if (empty($CFG->airnotifieraccesskey) || !$DB->get_field('message_processors', 'enabled', ['name' => 'airnotifier'])) {
            return false;
        }
        // A phone registered for this factor, with its key: without it, nothing the app sends counts.
        if (!devices::active($userid)) {
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
     * @param bool $toapp Whether to send it to the app too, as a push notice.
     * @return bool Whether it went anywhere.
     */
    public static function send(\stdClass $request, bool $toapp = true): bool {
        $user = \core_user::get_user((int) $request->userid);
        if (!$user || $user->deleted || $user->suspended) {
            return false;
        }
        // In the person's language, wherever the request is made from.
        $language = force_current_language((string) $user->lang);
        try {
            return self::deliver($request, $user, $toapp);
        } finally {
            force_current_language($language);
        }
    }

    /**
     * Send a request, in the language already set.
     *
     * @param \stdClass $request The request.
     * @param \stdClass $user The person.
     * @param bool $toapp Whether to send it to the app too.
     * @return bool Whether it went anywhere.
     */
    private static function deliver(\stdClass $request, \stdClass $user, bool $toapp): bool {
        $where = get_string('where', 'factor_oksigeniaaty', (object) [
            'device' => $request->device,
            'time' => userdate(
                (int) $request->timecreated,
                get_string('strftimetime', 'langconfig'),
                \core_date::get_user_timezone($user)
            ),
        ]);
        $sent = false;
        if ($toapp && self::app((int) $request->userid)) {
            $url = new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/approve.php', ['r' => $request->id]);
            $site = format_string(get_site()->fullname, true, ['escape' => false]);
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
