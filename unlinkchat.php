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
 * Takes away a chat that can approve the person's sign-ins, from the setup page's list: by POST, with the session key,
 * and only a chat of the person signed in, with the multi-factor authentication passed.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../../../config.php');

use factor_oksigeniaaty\local\chats;

$PAGE->set_url(new moodle_url('/admin/tool/mfa/factor/oksigeniaaty/unlinkchat.php'));
$PAGE->set_context(context_system::instance());
require_login(null, false);
require_sesskey();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    throw new moodle_exception('invalidrequest');
}

$done = chats::unlink((int) $USER->id, required_param('id', PARAM_INT));
redirect(
    new moodle_url('/admin/tool/mfa/action.php', ['action' => 'setup', 'factor' => 'oksigeniaaty']),
    $done ? get_string('unlinked', 'factor_oksigeniaaty') : '',
    null,
    \core\output\notification::NOTIFY_SUCCESS
);
