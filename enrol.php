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
 * Where the QR of the setup page leads when it is read with the phone's camera instead of with the app: a button that
 * opens it in the app, which registers the phone. The code does nothing here; the app uses it with its own session.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../../../config.php');

use factor_oksigeniaaty\local\reach;

$code = optional_param('c', '', PARAM_ALPHANUM);
$url = new moodle_url('/admin/tool/mfa/factor/oksigeniaaty/enrol.php', $code !== '' ? ['c' => $code] : []);
$PAGE->set_url(new moodle_url('/admin/tool/mfa/factor/oksigeniaaty/enrol.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login');
$title = get_string('enrolpage:title', 'factor_oksigeniaaty');
$PAGE->set_title($title);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('factor_oksigeniaaty/enrol', [
    'title' => $title,
    'applink' => $code !== '' ? reach::app_link($url) : '',
]);
echo $OUTPUT->footer();
