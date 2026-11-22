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
 * «Your accesses»: the phones and chats that can approve the person's sign-ins, each with a way to take it away, and
 * the sign-ins that asked for approval lately. It waits for the factor like any other page.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../../../config.php');

use factor_oksigeniaaty\local\accesses;

$PAGE->set_url(new moodle_url('/admin/tool/mfa/factor/oksigeniaaty/accesses.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
require_login(null, false);
if (isguestuser()) {
    throw new require_login_exception('Guests have no accesses');
}

$title = get_string('accesses:title', 'factor_oksigeniaaty');
$PAGE->set_title($title);
$PAGE->set_heading($title);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('factor_oksigeniaaty/accesses', accesses::context((int) $USER->id));
echo $OUTPUT->footer();
