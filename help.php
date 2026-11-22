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
 * The page an adult of the family opens from the notice: the code to tell the person who asked, in person or on the
 * phone, and «They did not ask me», which stops it. It waits for the adult's own sign-in and factor like any page.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../../../config.php');

use factor_oksigeniaaty\local\familyhelp;

$id = required_param('h', PARAM_INT);
$url = new moodle_url('/admin/tool/mfa/factor/oksigeniaaty/help.php', ['h' => $id]);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
require_login(null, false);
if (isguestuser()) {
    throw new require_login_exception('Guests cannot help anyone in');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && optional_param('refuse', 0, PARAM_BOOL)) {
    require_sesskey();
    $done = familyhelp::refuse($id, (int) $USER->id);
    $message = $done ? get_string('familyhelp:refuseddone', 'factor_oksigeniaaty') : '';
    redirect($url, $message, null, \core\output\notification::NOTIFY_SUCCESS);
}

$help = familyhelp::for_helper($id, (int) $USER->id);
$person = $help ? core_user::get_user((int) $help->userid) : null;
$name = $person ? fullname($person) : '';
$context = [
    'none' => !$help,
    'waiting' => $help && $help->status === familyhelp::PENDING,
    'used' => $help && $help->status === familyhelp::USED,
    'refused' => $help && $help->status === familyhelp::REFUSED,
    'expired' => $help && $help->status === familyhelp::EXPIRED,
    'name' => $name,
    'code' => '',
    'left' => '',
    'url' => $url->out(false),
    'sesskey' => sesskey(),
];
if ($context['waiting']) {
    $context['code'] = substr($help->code, 0, 3) . ' ' . substr($help->code, 3);
    $minutes = (int) ceil(((int) $help->timeexpires - time()) / MINSECS);
    $context['left'] = $minutes > 1 ? get_string('familyhelp:leftminutes', 'factor_oksigeniaaty', $minutes)
        : get_string('familyhelp:leftminute', 'factor_oksigeniaaty');
}

$title = get_string('familyhelp:title', 'factor_oksigeniaaty');
$PAGE->set_title($title);
$PAGE->set_heading($title);
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('factor_oksigeniaaty/help', $context);
echo $OUTPUT->footer();
