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

use factor_oksigeniaaty\local\chats;
use factor_oksigeniaaty\local\devices;
use factor_oksigeniaaty\local\reach;
use factor_oksigeniaaty\local\request;
use stdClass;
use tool_mfa\local\factor\object_factor_base;
use tool_mfa\plugininfo\factor as states;

/**
 * «Approve from the phone»: the sign-in screen shows a number, the phone gets a notice with three numbers and «It was
 * not me», and the person taps the one on the screen. The screen goes on by itself as soon as the phone answers.
 *
 * The phone that approves is registered first, from the multi-factor preferences, which wait for another factor: a
 * QR that the app reads, and from which it gets a key of its own; or a chat of Oksigenia Marandu joined with a link of
 * that same page. Whoever only has the password can get a token of the app, but not that key nor that link.
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

    /** @var string[] The code of each registration made by this request, to show in its page: tool_mfa builds the
     * setup form with a new instance of the factor for each step, so it cannot live in the instance. */
    private static $enrolcodes = [];

    /**
     * Whether the person can get a request somewhere, once per page.
     *
     * @param int $userid The person.
     * @return bool
     */
    private static function ready(int $userid): bool {
        if (!array_key_exists($userid, self::$reachable)) {
            self::$reachable[$userid] = $userid > 0 && reach::any($userid, !reach::in_app_window());
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
            // The phone stopped it: the factor fails and counts a failure, and a page of its own says why and ends
            // the session.
            $this->set_state(states::STATE_FAIL);
            $this->increment_lock_counter();
            redirect(new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/stopped.php'));
        }

        $problem = '';
        $again = optional_param('oksigeniaatyagain', 0, PARAM_BOOL) && confirm_sesskey();
        // Another one when there is none, or when the person asks for it: the one waiting expired, or never arrived.
        $renew = $again && in_array($request->status ?? '', [request::EXPIRED, request::PENDING], true);
        if (!$request || $request->status === request::USED || $renew) {
            $held = request::held_back($userid);
            if ($held === '') {
                $device = request::describe((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));
                $request = request::create($userid, $sid, $device, getremoteaddr());
                if (!reach::send($request, !reach::in_app_window())) {
                    request::expire($request);
                    $problem = get_string('wait:unsent', 'factor_oksigeniaaty');
                }
            } else {
                $problem = get_string('wait:' . $held, 'factor_oksigeniaaty');
                $request = $request && $request->status !== request::USED ? $request : null;
            }
        }

        $waiting = $request && in_array($request->status, [request::PENDING, request::APPROVED], true) && $problem === '';
        // Where it went, as far as can be known now: the app does not get it in its own window.
        $toapp = !reach::in_app_window() && reach::app($userid);
        $tochat = chats::available($userid);
        $sent = $toapp && $tochat ? 'wait:sentboth' : ($toapp ? 'wait:sentapp' : 'wait:sentchat');
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
            'sent' => get_string($sent, 'factor_oksigeniaaty'),
            'expired' => !$waiting && $problem === '' && $request && $request->status === request::EXPIRED,
            'againurl' => $againurl->out(false),
            'minutes' => (int) ceil(request::validity() / MINSECS),
            // What is left, counted down on the screen and said to a screen reader at two minutes, one and thirty
            // seconds: relative to now, so that the computer's clock does not matter.
            'seconds' => $waiting ? max(0, (int) $request->timecreated + request::validity() - time()) : 0,
            'lefttext' => get_string('wait:left', 'factor_oksigeniaaty', '%s'),
            'says' => json_encode([
                2 * MINSECS => get_string('wait:saytwo', 'factor_oksigeniaaty'),
                MINSECS => get_string('wait:sayone', 'factor_oksigeniaaty'),
                30 => get_string('wait:sayhalf', 'factor_oksigeniaaty'),
            ]),
            'problem' => $problem,
            'early' => $early ? get_string('error:notapproved', 'factor_oksigeniaaty') : '',
        ];
        // Raw, not a static element: the form would put it in a narrow column of a row.
        $mform->addElement('html', $OUTPUT->render_from_template('factor_oksigeniaaty/login', $context));
        // Always: while waiting it asks and goes on by itself; otherwise it hides the form's button, which has nothing
        // to confirm.
        $script = new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/wait.js', [
            'v' => get_config('factor_oksigeniaaty', 'version'),
        ]);
        $PAGE->requires->js($script);
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
        $fresh = $request && (int) $request->timeanswered > time() - request::validity();
        if ($fresh && $request->status === request::APPROVED) {
            return [];
        }
        return ['oksigeniaatywait' => get_string('error:notapproved', 'factor_oksigeniaaty')];
    }

    /**
     * The approved request cannot be used again; one still waiting (the person went in another way) stops waiting,
     * on the phone and on the chats.
     */
    public function post_pass_state(): void {
        global $USER;
        $request = request::for_session((int) $USER->id, request::session_hash());
        if ($request && $request->status === request::APPROVED) {
            request::mark_used((int) $request->id);
        } else if ($request && $request->status === request::PENDING) {
            request::expire($request);
        }
        parent::post_pass_state();
    }

    /**
     * The registered phones of a person, as tool_mfa lists them in its preferences, revoked ones too.
     *
     * @param stdClass $user The person.
     * @return array
     */
    public function get_all_user_factors(stdClass $user): array {
        global $DB;
        return $DB->get_records_select(
            'tool_mfa',
            "userid = ? AND factor = ? AND secret IS NOT NULL AND secret <> ''",
            [$user->id, $this->name]
        );
    }

    /**
     * A phone is registered before it approves.
     *
     * @return bool
     */
    public function has_setup(): bool {
        return true;
    }

    /**
     * The button to register a phone, in the preferences.
     *
     * @return bool
     */
    public function show_setup_buttons(): bool {
        return true;
    }

    /**
     * A registered phone can be removed, for a lost or a changed one.
     *
     * @return bool
     */
    public function has_revoke(): bool {
        return true;
    }

    /**
     * The registration: a new code, which only lives in the QR and the link of this page.
     *
     * @param \MoodleQuickForm $mform The form.
     * @return \MoodleQuickForm
     */
    public function setup_factor_form_definition(\MoodleQuickForm $mform): \MoodleQuickForm {
        global $USER;
        $enrolid = optional_param('enrolid', 0, PARAM_INT);
        if (!$enrolid || !devices::enrolment($enrolid, (int) $USER->id)) {
            [$enrolid, $code] = devices::new_enrolment((int) $USER->id);
            self::$enrolcodes[$enrolid] = $code;
        }
        $mform->addElement('hidden', 'enrolid', $enrolid);
        $mform->setType('enrolid', PARAM_INT);
        return $mform;
    }

    /**
     * The QR to read with the app, the link for this same phone, the chats to join, and the wait until the app has
     * registered it.
     *
     * @param \MoodleQuickForm $mform The form.
     * @return \MoodleQuickForm
     */
    public function setup_factor_form_definition_after_data(\MoodleQuickForm $mform): \MoodleQuickForm {
        global $OUTPUT, $PAGE, $USER;
        $enrolid = (int) $mform->getElement('enrolid')->getValue();
        $context = [
            'enrolid' => $enrolid,
            'chats' => chats::join_links((int) $USER->id),
            'ownchats' => chats::own((int) $USER->id),
            'unlinkurl' => (new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/unlinkchat.php'))->out(false),
            'sesskey' => sesskey(),
        ];
        $code = self::$enrolcodes[$enrolid] ?? '';
        if ($code !== '') {
            $url = new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/enrol.php', ['c' => $code]);
            $qrcode = new \core_qrcode($url->out(false));
            $context += [
                'qr' => 'data:image/png;base64,' . base64_encode($qrcode->getBarcodePngData(6, 6)),
                'samephone' => reach::app_link($url),
                'checkurl' => (new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/check.php', [
                    'e' => $enrolid,
                    'sesskey' => sesskey(),
                ]))->out(false),
                'minutes' => (int) (devices::ENROLLIFE / MINSECS),
            ];
        }
        $mform->addElement('html', $OUTPUT->render_from_template('factor_oksigeniaaty/setup', $context));
        $PAGE->requires->js(new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/wait.js', [
            'v' => get_config('factor_oksigeniaaty', 'version'),
        ]));
        return $mform;
    }

    /**
     * The registration is done when the app registered the phone with this page's code.
     *
     * @param array $data The form.
     * @return array Errors.
     */
    public function setup_factor_form_validation(array $data): array {
        global $USER;
        $enrol = devices::enrolment((int) ($data['enrolid'] ?? 0), (int) $USER->id);
        if ($enrol && $enrol->status === devices::DONE && (int) $enrol->toolmfaid > 0) {
            return [];
        }
        return ['enrolid' => get_string('setup:notyet', 'factor_oksigeniaaty')];
    }

    /**
     * The phone the app registered with this page's code.
     *
     * @param stdClass $data The form.
     * @return stdClass|null
     */
    public function setup_user_factor(stdClass $data): stdClass|null {
        global $DB, $USER;
        $enrol = devices::enrolment((int) ($data->enrolid ?? 0), (int) $USER->id);
        if (!$enrol || $enrol->status !== devices::DONE) {
            return null;
        }
        $record = $DB->get_record('tool_mfa', ['id' => $enrol->toolmfaid, 'userid' => $USER->id, 'factor' => $this->name]);
        if ($record) {
            $this->create_event_after_factor_setup($USER);
        }
        return $record ?: null;
    }

    /**
     * The button of the setup page, once the app has registered the phone.
     *
     * @return string|null
     */
    public function setup_factor_form_submit_button_string(): ?string {
        return get_string('setup:done', 'factor_oksigeniaaty');
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
        // Stopped from the phone: it fails, on any page, before the person can try another factor with this session.
        $request = request::for_session((int) $USER->id, request::session_hash());
        if ($request && in_array($request->status, [request::DENIED, request::WRONG], true)) {
            return states::STATE_FAIL;
        }
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
        if (!self::ready((int) $user->id) && !devices::active((int) $user->id)) {
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
        return [
            new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/check.php'),
            new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/stopped.php'),
        ];
    }
}
