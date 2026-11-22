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
 * New recovery codes, made by POST from «Your accesses» and shown once, to print them: only their hashes are kept.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../../../config.php');

use factor_oksigeniaaty\local\recovery;

$accesses = new moodle_url('/admin/tool/mfa/factor/oksigeniaaty/accesses.php');
$PAGE->set_url(new moodle_url('/admin/tool/mfa/factor/oksigeniaaty/codes.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');
require_login(null, false);
if (isguestuser()) {
    throw new require_login_exception('Guests have no recovery codes');
}
// Seen only right after they are made: a page opened again, or from the history, has nothing to show.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($accesses);
}
require_sesskey();

$codes = recovery::create((int) $USER->id);
$title = get_string('recovery:pagetitle', 'factor_oksigeniaaty');
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->requires->js_amd_inline("
    document.querySelectorAll('[data-oksigeniaaty-print]').forEach(function(button) {
        button.hidden = false;
        button.addEventListener('click', function() {
            window.print();
        });
    });
");

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('factor_oksigeniaaty/codes', [
    'codes' => array_map(fn($code) => ['code' => $code], $codes),
    'for' => get_string('recovery:pagefor', 'factor_oksigeniaaty', (object) [
        'name' => fullname($USER),
        'site' => format_string(get_site()->fullname, true, ['context' => context_system::instance()]),
        // in words and with its year: a sheet of paper is read long after «today»
        'date' => userdate(time(), get_string('accesses:dateformatyear', 'factor_oksigeniaaty')),
    ]),
    'backurl' => $accesses->out(false),
]);
echo $OUTPUT->footer();
