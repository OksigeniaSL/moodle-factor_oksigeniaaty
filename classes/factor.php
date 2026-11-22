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
use factor_oksigeniaaty\local\familyhelp;
use factor_oksigeniaaty\local\reach;
use factor_oksigeniaaty\local\recovery;
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

    /** @var bool[] Whether each person passed with a recovery code in this request of the browser. */
    private static $recovered = [];

    /**
     * Whether the person can get a request somewhere, once per page.
     *
     * @param int $userid The person.
     * @return bool
     */
    private static function reachable(int $userid): bool {
        if (!array_key_exists($userid, self::$reachable)) {
            self::$reachable[$userid] = $userid > 0 && reach::any($userid, !reach::in_app_window());
        }
        return self::$reachable[$userid];
    }

    /**
     * Whether the factor can be passed now: a request can reach the person, they have recovery codes left, or, for
     * someone with a phone registered, an adult of their family can give them a code.
     *
     * @param int $userid The person.
     * @return bool
     */
    private static function ready(int $userid): bool {
        return self::reachable($userid) || recovery::remaining($userid) > 0
            || (devices::active($userid) && familyhelp::helpers($userid));
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

        // Help asked of the family: by a link with the session key, not by the form, which would count a failure.
        if (optional_param('oksigeniaatyhelp', 0, PARAM_BOOL) && confirm_sesskey()) {
            familyhelp::ask($userid, $sid);
        }
        $help = familyhelp::for_session($userid, $sid);
        $helpwaiting = $help && $help->status === familyhelp::PENDING;

        // A recovery code (eight digits) or the family's (six), typed below the number: spent here, before the form is
        // validated, so that the screen can say what went wrong; a wrong one counts a failure there, as any other.
        $codeerror = '';
        if ($mform->isSubmitted() && optional_param('oksigeniaatyusecode', 0, PARAM_BOOL) && confirm_sesskey()) {
            $typed = optional_param('oksigeniaatycode', '', PARAM_RAW_TRIMMED);
            $family = strlen((string) preg_replace('/\D+/', '', $typed)) === 6;
            self::$recovered[$userid] = $typed !== ''
                && ($family ? familyhelp::spend($userid, $sid, $typed) : recovery::spend($userid, $typed));
            if (!self::$recovered[$userid]) {
                $wrong = $family ? 'familyhelp:wrong' : 'recovery:wrong';
                $codeerror = get_string($typed === '' ? 'recovery:empty' : $wrong, 'factor_oksigeniaaty');
            }
        }
        $reachable = self::reachable($userid);
        $codes = recovery::remaining($userid);

        $problem = '';
        $again = optional_param('oksigeniaatyagain', 0, PARAM_BOOL) && confirm_sesskey();
        // Another one when there is none, or when the person asks for it: the one waiting expired, or never arrived.
        $renew = $again && in_array($request->status ?? '', [request::EXPIRED, request::PENDING], true);
        // None when nothing can reach the person (only their codes are left), or when a code has just passed.
        $quiet = !$reachable || !empty(self::$recovered[$userid]);
        if (!$quiet && (!$request || $request->status === request::USED || $renew)) {
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
            'expired' => $reachable && !$waiting && $problem === '' && $request && $request->status === request::EXPIRED,
            'againurl' => $againurl->out(false),
            'minutes' => (int) ceil(request::validity() / MINSECS),
            'typed' => $waiting && request::typed($userid),
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
            'reachable' => $reachable,
            'codes' => $codes > 0 || $codeerror !== '' || $helpwaiting,
            'codeerror' => $codeerror,
        ];
        $context += self::family_context($userid, $reachable, $codes, $helpwaiting);
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
     * What the sign-in screen says about the help of the family: the link to ask for it, the wait for the code, and
     * what to do when nothing else can reach the person.
     *
     * @param int $userid The person.
     * @param bool $reachable Whether a request can reach them.
     * @param int $codes Their recovery codes left.
     * @param bool $helpwaiting Whether this browser waits for the family's code.
     * @return array
     */
    private static function family_context(int $userid, bool $reachable, int $codes, bool $helpwaiting): array {
        $canask = !$helpwaiting && familyhelp::can_ask($userid);
        $askurl = new \moodle_url('/admin/tool/mfa/auth.php', [
            'factorname' => 'oksigeniaaty',
            'oksigeniaatyhelp' => 1,
            'sesskey' => sesskey(),
        ]);
        $only = '';
        if (!$reachable) {
            $only = $codes > 0 ? 'recovery:only' : ($canask || $helpwaiting ? 'familyhelp:only' : '');
        }
        return [
            'helpurl' => $canask ? $askurl->out(false) : '',
            'helpwaiting' => $helpwaiting
                ? get_string('familyhelp:waiting', 'factor_oksigeniaaty', (int) (familyhelp::LIFE / MINSECS)) : '',
            'onlytext' => $only !== '' ? get_string($only, 'factor_oksigeniaaty') : '',
            'codelabel' => get_string($helpwaiting ? 'familyhelp:label' : 'recovery:label', 'factor_oksigeniaaty'),
            'codeplaceholder' => $helpwaiting ? '000 000' : '0000 0000',
            'codesummary' => get_string($helpwaiting ? 'familyhelp:summary' : 'recovery:summary', 'factor_oksigeniaaty'),
        ];
    }

    /**
     * The browser goes on only when the phone chose the right number for this session.
     *
     * @param array $data The form.
     * @return array Errors.
     */
    public function login_form_validation(array $data): array {
        global $USER;
        if (!empty(self::$recovered[(int) $USER->id])) {
            return [];
        }
        if (optional_param('oksigeniaatyusecode', 0, PARAM_BOOL)) {
            return ['oksigeniaatycode' => get_string('recovery:wrong', 'factor_oksigeniaaty')];
        }
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
        familyhelp::expire_pending((int) $USER->id);
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
     * What approves the person's sign-ins, on the card of the preferences, with the way to «Your accesses».
     *
     * @param int $factorid One of the person's phones.
     * @return string
     */
    public function get_manage_info(int $factorid): string {
        global $USER;
        $phones = count(devices::active((int) $USER->id));
        $chats = count(chats::own((int) $USER->id));
        $parts = [];
        if ($phones) {
            $parts[] = $phones === 1 ? get_string('accesses:summaryonephone', 'factor_oksigeniaaty')
                : get_string('accesses:summaryphones', 'factor_oksigeniaaty', $phones);
        }
        if ($chats) {
            $parts[] = $chats === 1 ? get_string('accesses:summaryonechat', 'factor_oksigeniaaty')
                : get_string('accesses:summarychats', 'factor_oksigeniaaty', $chats);
        }
        if (count($parts) === 2) {
            $both = (object) ['phones' => $parts[0], 'chats' => $parts[1]];
            $both = get_string('accesses:summaryboth', 'factor_oksigeniaaty', $both);
            $text = get_string('manageinfo', 'factor_oksigeniaaty', $both);
        } else if ($parts) {
            $text = get_string('manageinfo', 'factor_oksigeniaaty', $parts[0]);
        } else {
            $text = get_string('accesses:nophones', 'factor_oksigeniaaty');
        }
        $link = \html_writer::link(
            new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/accesses.php'),
            get_string('accesses:title', 'factor_oksigeniaaty')
        );
        return \html_writer::tag('p', s($text)) . \html_writer::tag('p', $link);
    }

    /**
     * The «last approved» of a phone is set by the phone itself, when its signed answer is checked: passing the factor
     * with a recovery code or a chat does not make every phone of the person look as if it had approved.
     *
     * @param int|null $factorid The phone, or every phone of the person.
     * @return bool
     */
    public function update_lastverified(?int $factorid = null): bool {
        return true;
    }

    /**
     * The factor reset for a person, by an administrator: their phones go, as tool_mfa does, and their recovery codes
     * too.
     *
     * @param stdClass $user The person.
     */
    public function delete_factor_for_user(stdClass $user): void {
        parent::delete_factor_for_user($user);
        recovery::delete((int) $user->id);
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
            'accessesurl' => (new \moodle_url('/admin/tool/mfa/factor/oksigeniaaty/accesses.php'))->out(false),
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
