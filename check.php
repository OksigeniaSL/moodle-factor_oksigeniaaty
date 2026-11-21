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
 * What the browser asks while it waits: the state of its request, before the factor is passed (the factor lists this
 * page among those that do not wait for it), only about the request of this same session; or, on the setup page,
 * whether the app has registered the phone with that page's code, only about the person's own codes.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../../../config.php');

use factor_oksigeniaaty\local\request;

$id = optional_param('r', 0, PARAM_INT);
$enrolid = optional_param('e', 0, PARAM_INT);

$PAGE->set_url(new moodle_url('/admin/tool/mfa/factor/oksigeniaaty/check.php'));
$PAGE->set_context(context_system::instance());
require_login(null, false);
require_sesskey();
// Nothing here writes to the session: other requests of this browser do not wait for this one.
\core\session\manager::write_close();

if ($enrolid) {
    // The setup page waits for the app to register the phone with its code.
    $enrol = \factor_oksigeniaaty\local\devices::enrolment($enrolid, (int) $USER->id);
    $status = $enrol ? $enrol->status : 'none';
} else {
    $request = request::for_session((int) $USER->id, request::session_hash());
    $status = $request && (int) $request->id === $id ? $request->status : 'none';
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
echo json_encode(['status' => $status]);
