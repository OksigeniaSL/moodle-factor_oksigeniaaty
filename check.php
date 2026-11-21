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
 * What the browser that signs in asks while it waits: the state of its request. It works before the factor is passed,
 * because the factor lists this page among those that do not wait for it; it only tells about the request of this
 * same session.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../../../config.php');

use factor_oksigeniaaty\local\request;

$id = required_param('r', PARAM_INT);

$PAGE->set_url(new moodle_url('/admin/tool/mfa/factor/oksigeniaaty/check.php'));
$PAGE->set_context(context_system::instance());
require_login(null, false);
require_sesskey();

$request = request::for_session((int) $USER->id, request::session_hash());
$status = $request && (int) $request->id === $id ? $request->status : 'none';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
echo json_encode(['status' => $status]);
