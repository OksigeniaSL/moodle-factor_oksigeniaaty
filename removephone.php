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
 * Takes away a phone that can approve the person's sign-ins, from «Your accesses»: by POST, with the session key, and
 * only a phone of the person signed in, with the multi-factor authentication passed.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../../../config.php');

use factor_oksigeniaaty\local\accesses;

$PAGE->set_url(new moodle_url('/admin/tool/mfa/factor/oksigeniaaty/removephone.php'));
$PAGE->set_context(context_system::instance());
require_login(null, false);
require_sesskey();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    throw new moodle_exception('invalidrequest');
}

$done = accesses::remove_phone((int) $USER->id, required_param('id', PARAM_INT));
redirect(
    new moodle_url('/admin/tool/mfa/factor/oksigeniaaty/accesses.php'),
    $done ? get_string('accesses:removedphone', 'factor_oksigeniaaty') : '',
    null,
    \core\output\notification::NOTIFY_SUCCESS
);
