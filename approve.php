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
 * The request on the web, for whoever opens the notice's link outside the app: in another browser where the person is
 * already in, with the factor passed. The browser that is signing in cannot answer here its own request, nor that of
 * any other browser still waiting for the factor: this page waits for the factor like any other.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../../../config.php');

use factor_oksigeniaaty\local\request;
use factor_oksigeniaaty\output\approval;

$id = optional_param('r', 0, PARAM_INT);
$choice = optional_param('choice', '', PARAM_ALPHANUM);

$url = new moodle_url('/admin/tool/mfa/factor/oksigeniaaty/approve.php', $id ? ['r' => $id] : []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
require_login(null, false);
if (isguestuser()) {
    throw new require_login_exception('Guests cannot approve a sign-in');
}

if ($choice !== '' && $id) {
    require_sesskey();
    request::answer($id, (int) $USER->id, $choice, 'web');
    redirect($url);
}

$request = $id ? request::get($id, (int) $USER->id) : request::latest((int) $USER->id);
$title = get_string('approve:title', 'factor_oksigeniaaty');
$PAGE->set_title($title);
$PAGE->set_heading($title);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('factor_oksigeniaaty/approve', approval::context($request, $USER, true));
echo $OUTPUT->footer();
