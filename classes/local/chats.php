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
 * Telegram and WhatsApp, through Oksigenia Marandu, when it is installed: the question with the three numbers and «It
 * was not me», and its answer. Without Marandu, or with a version that cannot ask it yet, all of this is quiet and the
 * phone gets the request in the app only.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class chats {
    /** @var string Marandu's API. */
    private const API = '\local_oksigeniamarandu\api';

    /** @var string The question, as lib.php declares it. */
    public const NOTICE = 'login_check';

    /** @var string[] The keys of the numbers' buttons, in the order of the choices. */
    public const KEYS = ['a', 'b', 'c'];

    /**
     * Whether Marandu can ask this person on a chat of theirs.
     *
     * @param int $userid The person.
     * @return bool
     */
    public static function available(int $userid): bool {
        $api = self::API;
        if (!class_exists($api) || !method_exists($api, 'reaches')) {
            return false;
        }
        try {
            return (bool) $api::reaches('factor_oksigeniaaty', self::NOTICE, $userid);
        } catch (\Throwable $e) {
            debugging('Approve from the phone, Marandu: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }

    /**
     * Ask on the person's chats. Nothing if Marandu cannot.
     *
     * @param \stdClass $request The request.
     * @param string $where The browser and the time, as the question says them.
     * @return int How many chats it went to.
     */
    public static function ask(\stdClass $request, string $where): int {
        $api = self::API;
        if (!class_exists($api) || !method_exists($api, 'ask')) {
            return 0;
        }
        $params = array_merge([format_string(get_site()->fullname), $where],
            array_map('strval', request::choices($request)));
        try {
            return (int) $api::ask('factor_oksigeniaaty', self::NOTICE, $params, self::ref($request), 0,
                (int) $request->userid, new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/approve.php',
                ['r' => $request->id]), request::validity(), '', true);
        } catch (\Throwable $e) {
            debugging('Approve from the phone, Marandu: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return 0;
        }
    }

    /**
     * Once answered anywhere, the question on the chats has no live buttons left.
     *
     * @param \stdClass $request The request.
     * @param string $how answered or expired.
     */
    public static function close(\stdClass $request, string $how): void {
        $api = self::API;
        if (!class_exists($api) || !method_exists($api, 'close_question')) {
            return;
        }
        try {
            $api::close_question('factor_oksigeniaaty', self::ref($request), $how);
        } catch (\Throwable $e) {
            debugging('Approve from the phone, Marandu: ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }

    /**
     * An answer from Telegram or WhatsApp. Marandu has checked it comes from the chat the question went to, which is
     * the person's.
     *
     * @param object $hook \local_oksigeniamarandu\hook\answer_received.
     */
    public static function answer_received($hook): void {
        global $DB;
        if ($hook->get_component() !== 'factor_oksigeniaaty' || $hook->get_notice() !== self::NOTICE) {
            return;
        }
        if (!preg_match('/^login:(\d+)$/', (string) $hook->get_ref(), $parts)) {
            return;
        }
        $request = $DB->get_record(request::TABLE, ['id' => (int) $parts[1]]);
        if (!$request) {
            $hook->set_reply(get_string('approve:none', 'factor_oksigeniaaty'));
            return;
        }
        $option = (string) $hook->get_option();
        $choices = request::choices($request);
        $index = array_search($option, self::KEYS, true);
        if ($option === request::DENY) {
            $choice = request::DENY;
        } else if ($index !== false && isset($choices[$index])) {
            $choice = (string) $choices[$index];
        } else {
            return;
        }
        $status = request::answer((int) $request->id, (int) $request->userid, $choice,
            \core_text::substr((string) $hook->get_channel(), 0, 20));
        $hook->set_reply(self::reply($status));
    }

    /**
     * What the chat answers, for the state the request was left in.
     *
     * @param string $status The state.
     * @return string
     */
    public static function reply(string $status): string {
        $known = [request::APPROVED, request::DENIED, request::WRONG, request::EXPIRED];
        if ($status === request::USED) {
            $status = request::APPROVED;
        }
        return get_string('approve:' . (in_array($status, $known, true) ? $status : 'none'), 'factor_oksigeniaaty');
    }

    /**
     * The reference of a request in Marandu.
     *
     * @param \stdClass $request The request.
     * @return string
     */
    private static function ref(\stdClass $request): string {
        return 'login:' . $request->id;
    }
}
