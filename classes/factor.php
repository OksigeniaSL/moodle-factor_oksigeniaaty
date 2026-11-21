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

namespace factor_oksigeniaaty;

use factor_oksigeniaaty\local\reach;
use factor_oksigeniaaty\local\request;
use stdClass;
use tool_mfa\local\factor\object_factor_base;
use tool_mfa\plugininfo\factor as states;

/**
 * «Approve from the phone»: the sign-in screen shows a number, the phone gets a notice with three numbers and «It was
 * not me», and the person taps the one on the screen. The screen goes on by itself as soon as the phone answers.
 *
 * Nothing to set up: the factor offers itself to whoever has the app on a phone that gets the site's notices, or a
 * chat of Oksigenia Marandu where it can ask them.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class factor extends object_factor_base {
    /** @var string The icon of the factor. */
    protected $icon = 'fa-mobile-screen-button';

    /** @var bool[] Whether each person can get a request, for this request of the browser. */
    private static $reachable = [];

    /**
     * Whether the person can get a request somewhere, once per page.
     *
     * @param int $userid The person.
     * @return bool
     */
    private static function ready(int $userid): bool {
        if (!array_key_exists($userid, self::$reachable)) {
            self::$reachable[$userid] = $userid > 0 && reach::any($userid);
        }
        return self::$reachable[$userid];
    }

    /**
     * The form of the sign-in: what it shows depends on the request, so it comes in login_form_definition_after_data().
     *
     * @param \MoodleQuickForm $mform The form.
     * @return \MoodleQuickForm
     */
    public function login_form_definition(\MoodleQuickForm $mform): \MoodleQuickForm {
        return $mform;
    }

    /**
     * The number to tap on the phone, and the wait for the answer. A new request is sent when this browser has none,
     * or when the person asks for another after the last one expired. If the phone stopped the sign-in, the factor
     * fails and the session ends.
     *
     * @param \MoodleQuickForm $mform The form.
     * @return \MoodleQuickForm
     */
    public function login_form_definition_after_data(\MoodleQuickForm $mform): \MoodleQuickForm {
        global $OUTPUT, $PAGE, $USER;

        $userid = (int) $USER->id;
        $sid = request::session_hash();
        $request = request::for_session($userid, $sid);
        if ($request && in_array($request->status, [request::DENIED, request::WRONG], true)) {
            $this->set_state(states::STATE_FAIL);
            redirect(new \moodle_url('/admin/tool/mfa/auth.php'));
        }

        $problem = '';
        $again = optional_param('oksigeniaatyagain', 0, PARAM_BOOL) && confirm_sesskey();
        if (!$request || $request->status === request::USED || ($request->status === request::EXPIRED && $again)) {
            if (request::can_create($userid)) {
                $device = request::describe((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
                $request = request::create($userid, $sid, $device, getremoteaddr());
                if (!reach::send($request)) {
                    $problem = get_string('wait:unsent', 'factor_oksigeniaaty');
                }
            } else {
                $problem = get_string('wait:toomany', 'factor_oksigeniaaty');
                $request = $request && $request->status !== request::USED ? $request : null;
            }
        }

        $waiting = $request && in_array($request->status, [request::PENDING, request::APPROVED], true) && $problem === '';
        // Sent before the phone answered: the form does not go on, and says why here, where the number is.
        $early = $waiting && $request->status === request::PENDING && $mform->isSubmitted();
        $checkurl = new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/check.php', [
            'r' => $request ? $request->id : 0,
            'sesskey' => sesskey(),
        ]);
        $againurl = new \moodle_url('/admin/tool/mfa/auth.php', [
            'factorname' => 'oksigeniaaty',
            'oksigeniaatyagain' => 1,
            'sesskey' => sesskey(),
        ]);
        $context = [
            'code' => $waiting ? (int) $request->code : null,
            'waiting' => $waiting,
            'checkurl' => $waiting ? $checkurl->out(false) : '',
            'expired' => !$waiting && $problem === '' && $request && $request->status === request::EXPIRED,
            'againurl' => $againurl->out(false),
            'problem' => $problem,
            'early' => $early ? get_string('error:notapproved', 'factor_oksigeniaaty') : '',
        ];
        // Raw, not a static element: the form would put it in a narrow column of a row.
        $mform->addElement('html', $OUTPUT->render_from_template('factor_oksigeniaaty/login', $context));
        if ($waiting) {
            $script = new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/wait.js', [
                'v' => get_config('factor_oksigeniaaty', 'version'),
            ]);
            $PAGE->requires->js($script);
        }
        return $mform;
    }

    /**
     * The browser goes on only when the phone chose the right number for this session.
     *
     * @param array $data The form.
     * @return array Errors.
     */
    public function login_form_validation(array $data): array {
        global $USER;
        $request = request::for_session((int) $USER->id, request::session_hash());
        if ($request && $request->status === request::APPROVED) {
            return [];
        }
        return ['oksigeniaatywait' => get_string('error:notapproved', 'factor_oksigeniaaty')];
    }

    /**
     * The approved request cannot be used again.
     */
    public function post_pass_state(): void {
        global $USER;
        $request = request::for_session((int) $USER->id, request::session_hash());
        if ($request && $request->status === request::APPROVED) {
            request::mark_used((int) $request->id);
        }
        parent::post_pass_state();
    }

    /**
     * One record of the factor for each person, made the first time, as the email factor does: there is nothing to
     * set up, the phone is the one where the app is.
     *
     * @param stdClass $user The person.
     * @return array
     */
    public function get_all_user_factors(stdClass $user): array {
        global $DB;
        $records = $DB->get_records('tool_mfa', ['userid' => $user->id, 'factor' => $this->name]);
        if ($records) {
            return $records;
        }
        $record = (object) [
            'userid' => $user->id,
            'factor' => $this->name,
            'label' => get_string('label', 'factor_oksigeniaaty'),
            'createdfromip' => $user->lastip ?? '',
            'timecreated' => time(),
            'revoked' => 0,
        ];
        $record->id = $DB->insert_record('tool_mfa', $record);
        return [$record];
    }

    /**
     * Whether the person has to answer something: only who can get the request.
     *
     * @return bool
     */
    public function has_input(): bool {
        global $USER;
        return self::ready((int) $USER->id);
    }

    /**
     * Neutral for whoever cannot get the request: the factor neither helps nor stops them.
     *
     * @return string
     */
    public function get_state(): string {
        global $USER;
        if (!self::ready((int) $USER->id)) {
            return states::STATE_NEUTRAL;
        }
        return parent::get_state();
    }

    /**
     * The states the factor can have for a person.
     *
     * @param stdClass $user The person.
     * @return array
     */
    public function possible_states(stdClass $user): array {
        if (!self::ready((int) $user->id)) {
            return [states::STATE_NEUTRAL];
        }
        return [states::STATE_FAIL, states::STATE_PASS, states::STATE_NEUTRAL, states::STATE_UNKNOWN];
    }

    /**
     * The page the waiting browser asks about the answer, which must work before the factor is passed.
     *
     * @return array
     */
    public function get_no_redirect_urls(): array {
        return [new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/check.php')];
    }
}
