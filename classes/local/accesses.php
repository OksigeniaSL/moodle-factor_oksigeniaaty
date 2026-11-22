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

        $phones = [];
        $devices = array_values(devices::active($userid));
        usort($devices, fn($a, $b) => (int) $b->timecreated <=> (int) $a->timecreated);
        foreach ($devices as $device) {
            $name = format_string($device->label, true, ['context' => \context_user::instance($userid)]);
            $phones[] = [
                'id' => (int) $device->id,
                'name' => $name,
                'since' => self::words((int) $device->timecreated, false),
                'lastused' => $device->lastverified ? self::words((int) $device->lastverified) : '',
                'confirm' => get_string('accesses:removephoneconfirm', 'factor_oksigeniaaty', $name),
            ];
        }

        $history = [];
        $days = [];
        $approved = 0;
        $stopped = 0;
        $laststopped = 0;
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
            $time = (int) $record->timecreated;
            $line = [
                'when' => self::words($time),
                // the hour alone, under the heading of its day
                'time' => userdate($time, get_string('accesses:timeformat', 'factor_oksigeniaaty')),
                'device' => $record->device,
                'outcome' => get_string('accesses:' . $outcome, 'factor_oksigeniaaty'),
                'via' => $record->via !== '' && in_array($outcome, ['approved', 'denied', 'wrong'], true)
                    ? get_string('accesses:via:' . $record->via, 'factor_oksigeniaaty') : '',
                'approved' => $outcome === 'approved',
                'stopped' => $outcome === 'denied' || $outcome === 'wrong',
                'waiting' => $outcome === 'pending',
                'expired' => $outcome === 'expired',
            ];
            $history[] = $line;
            // Grouped by day, the latest first: «Today», «Yesterday», «21 November».
            $day = self::day($time);
            if (!$days || end($days)['label'] !== $day) {
                $days[] = ['label' => $day, 'entries' => []];
            }
            $days[count($days) - 1]['entries'][] = $line;
            $approved += $line['approved'] ? 1 : 0;
            if ($line['stopped']) {
                $stopped++;
                $laststopped = $laststopped ?: $time;
            }
        }

        $base = '/admin/tool/mfa/factor/oksigeniaaty/';
        $chats = chats::own($userid);
        $context = [
            'phones' => $phones,
            'chats' => $chats,
            'chatsupported' => chats::supported(),
            'history' => $history,
            'historydays' => $days,
            'days' => (int) (request::KEEP / DAYSECS),
            // The state at the top, in words: what approves, and whether any sign-in was stopped (calm when none).
            'approvers' => self::approvers(count($phones), count($chats)),
            'stoppedcount' => $stopped,
            'stoppednote' => self::stoppednote($stopped, $laststopped),
            'allgood' => $approved && !$stopped
                ? get_string('accesses:allgood', 'factor_oksigeniaaty', (int) (request::KEEP / DAYSECS)) : '',
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
        $left = (object) [
            'left' => recovery::remaining($userid),
            'total' => recovery::COUNT,
            'made' => $made ? self::words($made, false) : '',
        ];
        // The codes as a ticket of ten: the spent ones punched.
        $marks = [];
        for ($i = 0; $made && $i < $left->total; $i++) {
            $marks[] = ['used' => $i < $left->total - $left->left];
        }
        return [
            'made' => $left->made,
            'lefttext' => $made ? get_string('recovery:left', 'factor_oksigeniaaty', $left) : '',
            'lastused' => $used ? self::words($used) : '',
            'marks' => $marks,
            'createurl' => (new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/codes.php'))->out(false),
        ];
    }

    /**
     * The note on top when sign-ins were stopped: how many, and when the last one was.
     *
     * @param int $stopped How many.
     * @param int $last When the last one was.
     * @return string Empty when none was.
     */
    private static function stoppednote(int $stopped, int $last): string {
        if (!$stopped) {
            return '';
        }
        if ($stopped === 1) {
            return get_string('accesses:stoppedone', 'factor_oksigeniaaty', self::words($last));
        }
        $a = (object) ['count' => $stopped, 'when' => self::words($last)];
        return get_string('accesses:stoppedmany', 'factor_oksigeniaaty', $a);
    }

    /**
     * A moment in words, never in numbers: «hoy a las 8:14», «ayer a las 23:02», «21 de
     * noviembre a las 9:00»; the year only when it is not this one. Without the hour, the day alone.
     *
     * @param int $time The moment.
     * @param bool $hour Whether to say the hour.
     * @return string
     */
    public static function words(int $time, bool $hour = true): string {
        $day = \core_text::strtolower(self::day($time));
        // a date takes its article («el 21 de noviembre»); today and yesterday do not
        $recent = [userdate(time(), '%Y-%m-%d'), userdate(time() - DAYSECS, '%Y-%m-%d')];
        if (!in_array(userdate($time, '%Y-%m-%d'), $recent, true)) {
            $day = get_string('accesses:onday', 'factor_oksigeniaaty', self::day($time));
        }
        if (!$hour) {
            return $day;
        }
        return get_string('accesses:at', 'factor_oksigeniaaty', (object) [
            'day' => $day,
            'time' => userdate($time, get_string('accesses:timeformat', 'factor_oksigeniaaty')),
        ]);
    }

    /**
     * The day of a moment, as a heading: «Hoy», «Ayer», «21 de noviembre» (and the year when it is not this one).
     *
     * @param int $time The moment.
     * @return string
     */
    public static function day(int $time): string {
        $date = userdate($time, '%Y-%m-%d');
        if ($date === userdate(time(), '%Y-%m-%d')) {
            return get_string('accesses:today', 'factor_oksigeniaaty');
        }
        if ($date === userdate(time() - DAYSECS, '%Y-%m-%d')) {
            return get_string('accesses:yesterday', 'factor_oksigeniaaty');
        }
        $format = userdate($time, '%Y') === userdate(time(), '%Y') ? 'accesses:dateformat' : 'accesses:dateformatyear';
        return userdate($time, get_string($format, 'factor_oksigeniaaty'));
    }

    /**
     * What approves the person's sign-ins, in a line: «Aprueban tus entradas: 1 móvil y 1 chat.»
     *
     * @param int $phones How many phones.
     * @param int $chats How many chats.
     * @return string Empty when nothing does.
     */
    private static function approvers(int $phones, int $chats): string {
        $p = $phones === 1 ? get_string('accesses:summaryonephone', 'factor_oksigeniaaty')
            : get_string('accesses:summaryphones', 'factor_oksigeniaaty', $phones);
        $c = $chats === 1 ? get_string('accesses:summaryonechat', 'factor_oksigeniaaty')
            : get_string('accesses:summarychats', 'factor_oksigeniaaty', $chats);
        if (!$phones && !$chats) {
            return '';
        }
        $what = $phones ? $p : $c;
        if ($phones && $chats) {
            $what = get_string('accesses:summaryboth', 'factor_oksigeniaaty', (object) ['phones' => $p, 'chats' => $c]);
        }
        return get_string('accesses:approvers', 'factor_oksigeniaaty', $what);
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
