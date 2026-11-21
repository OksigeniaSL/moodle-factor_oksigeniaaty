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

/**
 * The sign-in stopped from the phone («It was not me», or another number): says so in plain words, ends the session
 * and offers to sign in again, instead of the general page of the multi-factor authentication. It works before the
 * factor is passed, as the factor lists it among the pages that do not wait for it, and only for a session whose
 * request was stopped.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../../../config.php');

use factor_oksigeniaaty\local\request;

$PAGE->set_url(new moodle_url('/admin/tool/mfa/factor/oksigeniaaty/stopped.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login');
require_login(null, false);

$request = request::for_session((int) $USER->id, request::session_hash());
if (!$request || !in_array($request->status, [request::DENIED, request::WRONG], true)) {
    redirect(new moodle_url('/admin/tool/mfa/auth.php'));
}

// As the general page does: the event of the failure, then out.
\tool_mfa\event\user_failed_mfa::user_failed_mfa_event($USER)->trigger();
\tool_mfa\manager::mfa_logout();

$title = get_string('stopped:title', 'factor_oksigeniaaty');
$PAGE->set_title($title);
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('factor_oksigeniaaty/stopped', [
    'title' => $title,
    'message' => get_string($request->status === request::WRONG ? 'stopped:wrong' : 'stopped:denied', 'factor_oksigeniaaty'),
    'loginurl' => (new moodle_url('/login/index.php'))->out(false),
]);
echo $OUTPUT->footer();
