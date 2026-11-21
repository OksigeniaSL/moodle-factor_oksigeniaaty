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

namespace factor_oksigeniaaty\output;

use factor_oksigeniaaty\local\request;

/**
 * What the phone shows for a request, in the app and on the web: the sign-in and its three numbers while it waits, or
 * what happened to it.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class approval {
    /**
     * The context of the templates approve and mobile_approve.
     *
     * @param \stdClass|null $request The request, or null if there is none.
     * @param \stdClass $user Who looks at it.
     * @param bool $web For the web page, whose buttons send a form; the app's call the web service.
     * @return array
     */
    public static function context(?\stdClass $request, \stdClass $user, bool $web): array {
        $site = format_string(get_site()->fullname);
        if (!$request) {
            return [
                'pending' => false,
                'title' => get_string('approve:title', 'factor_oksigeniaaty'),
                'message' => get_string('approve:none', 'factor_oksigeniaaty'),
            ];
        }
        if ($request->status === request::PENDING) {
            $choices = [];
            foreach (request::choices($request) as $number) {
                $choices[] = ['value' => (string) $number];
            }
            return [
                'pending' => true,
                'requestid' => (int) $request->id,
                'who' => get_string('approve:who', 'factor_oksigeniaaty', $site),
                'where' => get_string('where', 'factor_oksigeniaaty', (object) [
                    'device' => $request->device,
                    'time' => userdate(
                        (int) $request->timecreated,
                        get_string('strftimetime', 'langconfig'),
                        \core_date::get_user_timezone($user)
                    ),
                ]),
                'choices' => $choices,
                'formurl' => $web ? (new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/approve.php'))->out(false) : '',
                'sesskey' => $web ? sesskey() : '',
            ];
        }
        $status = $request->status === request::USED ? request::APPROVED : $request->status;
        $changepassword = '';
        if ($status === request::DENIED) {
            $auth = get_auth_plugin($user->auth);
            if ($auth->can_change_password()) {
                $url = $auth->change_password_url();
                $changepassword = $url ? $url->out(false)
                    : (new \moodle_url('/login/change_password.php'))->out(false);
            }
        }
        $titles = [
            request::APPROVED => 'approve:donetitle',
            request::DENIED => 'approve:denytitle',
            request::WRONG => 'approve:wrongtitle',
            request::EXPIRED => 'approve:expiredtitle',
        ];
        return [
            'pending' => false,
            'title' => get_string($titles[$status] ?? 'approve:title', 'factor_oksigeniaaty'),
            'message' => get_string('approve:' . $status, 'factor_oksigeniaaty'),
            'changepassword' => $changepassword,
            'approved' => $status === request::APPROVED,
        ];
    }
}
