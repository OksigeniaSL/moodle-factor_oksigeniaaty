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
 * «Your accesses»: what can approve a person's sign-ins (their phones and their chats), each with a way to take it
 * away, and the sign-ins that asked for approval lately. Each of those is someone who typed the person's password, so
 * one they do not recognise tells them to change it.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class accesses {
    /** @var int The sign-ins listed, at most: the latest ones. */
    public const MAXHISTORY = 30;

    /**
     * The page's context.
     *
     * @param int $userid The person, signed in.
     * @return array
     */
    public static function context(int $userid): array {
        global $DB;
        $user = \core_user::get_user($userid, '*', MUST_EXIST);
        $datetime = get_string('strftimedatetimeshort', 'langconfig');
        $date = get_string('strftimedatefullshort', 'langconfig');

        $phones = [];
        $devices = array_values(devices::active($userid));
        usort($devices, fn($a, $b) => (int) $b->timecreated <=> (int) $a->timecreated);
        foreach ($devices as $device) {
            $name = format_string($device->label, true, ['context' => \context_user::instance($userid)]);
            $phones[] = [
                'id' => (int) $device->id,
                'name' => $name,
                'since' => userdate((int) $device->timecreated, $date),
                'lastused' => $device->lastverified ? userdate((int) $device->lastverified, $datetime) : '',
                'confirm' => get_string('accesses:removephoneconfirm', 'factor_oksigeniaaty', $name),
            ];
        }

        $history = [];
        $requests = $DB->get_records(
            request::TABLE,
            ['userid' => $userid],
            'timecreated DESC, id DESC',
            '*',
            0,
            self::MAXHISTORY
        );
        foreach ($requests as $record) {
            $outcome = self::outcome($record);
            $history[] = [
                'when' => userdate((int) $record->timecreated, $datetime),
                'device' => $record->device,
                'outcome' => get_string('accesses:' . $outcome, 'factor_oksigeniaaty'),
                'via' => $record->via !== '' && in_array($outcome, ['approved', 'denied', 'wrong'], true)
                    ? get_string('accesses:via:' . $record->via, 'factor_oksigeniaaty') : '',
                'approved' => $outcome === 'approved',
                'stopped' => $outcome === 'denied' || $outcome === 'wrong',
                'waiting' => $outcome === 'pending',
            ];
        }

        $base = '/admin/tool/mfa/factor/oksigeniaaty/';
        $context = [
            'phones' => $phones,
            'chats' => chats::own($userid),
            'chatsupported' => chats::supported(),
            'history' => $history,
            'days' => (int) (request::KEEP / DAYSECS),
            'addurl' => (new \moodle_url('/admin/tool/mfa/action.php', ['action' => 'setup', 'factor' => 'oksigeniaaty']))
                ->out(false),
            'removephoneurl' => (new \moodle_url($base . 'removephone.php'))->out(false),
            'unlinkurl' => (new \moodle_url($base . 'unlinkchat.php'))->out(false),
            'sesskey' => sesskey(),
            'passwordurl' => '',
            'recovery' => self::recovery($userid),
        ];
        $auth = get_auth_plugin($user->auth);
        if ($auth->can_change_password()) {
            $url = $auth->change_password_url();
            $context['passwordurl'] = ($url ?: new \moodle_url('/login/change_password.php'))->out(false);
        }
        return $context;
    }

    /**
     * The person's recovery codes: how many are left, when they were made and when the last one was used.
     *
     * @param int $userid The person.
     * @return array
     */
    private static function recovery(int $userid): array {
        global $DB;
        $made = recovery::made($userid);
        $used = (int) $DB->get_field(recovery::TABLE, 'MAX(timeused)', ['userid' => $userid]);
        $date = get_string('strftimedatefullshort', 'langconfig');
        $left = (object) [
            'left' => recovery::remaining($userid),
            'total' => recovery::COUNT,
            'made' => $made ? userdate($made, $date) : '',
        ];
        return [
            'made' => $left->made,
            'lefttext' => $made ? get_string('recovery:left', 'factor_oksigeniaaty', $left) : '',
            'lastused' => $used ? userdate($used, get_string('strftimedatetimeshort', 'langconfig')) : '',
            'createurl' => (new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/codes.php'))->out(false),
        ];
    }

    /**
     * What happened with a sign-in, as the list says it.
     *
     * @param \stdClass $record The request.
     * @return string approved, denied, wrong, pending or expired.
     */
    public static function outcome(\stdClass $record): string {
        switch ($record->status) {
            case request::APPROVED:
            case request::USED:
                return 'approved';
            case request::DENIED:
            case request::WRONG:
                return $record->status;
            case request::PENDING:
                return (int) $record->timeexpires > time() ? 'pending' : 'expired';
            default:
                return 'expired';
        }
    }

    /**
     * Takes away a phone of the person: it no longer approves their sign-ins. As tool_mfa's own revoke does, with its
     * event, and only for a phone of this factor and of this person.
     *
     * @param int $userid The person, signed in.
     * @param int $deviceid The phone, its row in tool_mfa.
     * @return bool Whether there was one.
     */
    public static function remove_phone(int $userid, int $deviceid): bool {
        global $DB, $USER;
        if ((int) $USER->id !== $userid) {
            return false;
        }
        $mine = $DB->record_exists('tool_mfa', [
            'id' => $deviceid,
            'userid' => $userid,
            'factor' => devices::FACTOR,
            'revoked' => 0,
        ]);
        if (!$mine) {
            return false;
        }
        return \tool_mfa\plugininfo\factor::get_factor(devices::FACTOR)->revoke_user_factor($deviceid);
    }
}
