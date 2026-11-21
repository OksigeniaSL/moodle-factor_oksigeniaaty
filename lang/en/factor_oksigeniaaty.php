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
 * Strings for factor_oksigeniaaty, in English.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['approve:approved'] = 'Done: you are in. You can go back to the other screen.';
$string['approve:changepassword'] = 'Change my password';
$string['approve:denied'] = 'We have stopped that sign-in. If it was not you, change your password.';
$string['approve:deny'] = 'It was not me';
$string['approve:donetitle'] = 'Approved';
$string['approve:expired'] = 'This notice has expired. If you are still signing in, ask for another one on the screen.';
$string['approve:none'] = 'There is nothing to approve now.';
$string['approve:pick'] = 'If it is you, tap the number you see on the sign-in screen.';
$string['approve:title'] = 'Is it you?';
$string['approve:who'] = 'Someone is signing in to {$a} with your account.';
$string['approve:wrong'] = 'That is not the number on the screen, so we have stopped the sign-in. If it was you, try again.';
$string['device'] = '{$a->browser} on {$a->os}';
$string['device:app'] = 'The app';
$string['device:unknown'] = 'An unknown browser';
$string['error:notapproved'] = 'It is not approved yet. Open the notice on your phone and tap the number.';
$string['info'] = 'Each sign-in is approved from the notice that reaches the phone, in the app or on a chat.';
$string['label'] = 'Phone';
$string['logindesc'] = 'We send a notice to your phone to approve this sign-in.';
$string['loginoption'] = 'Approve from the phone';
$string['loginsubmit'] = 'I have approved it';
$string['logintitle'] = 'Approve the sign-in from your phone';
$string['marandu:label'] = 'sign-in';
$string['marandu:login_check'] = 'Approve a sign-in';
$string['marandu:login_check_button'] = 'Open';
$string['marandu:login_check_desc'] = 'When someone signs in with the account, to approve it with the number on the screen';
$string['marandu:login_check_option_deny'] = 'It was not me';
$string['marandu:login_check_template'] = 'Is it you? Signing in to {{1}}: {{2}}. If it is you, tap the number you see on the screen: {{3}}, {{4}} or {{5}}.';
$string['messageprovider:approval'] = 'Requests to approve a sign-in';
$string['mobile:title'] = 'Is it you?';
$string['notify:body'] = 'Is it you? Signing in with your account: {$a}. Tap here and choose the number on the screen.';
$string['notify:link'] = 'Approve or stop';
$string['notify:subject'] = 'Is it you? Signing in to {$a}';
$string['pluginname'] = 'Approve from the phone';
$string['privacy:metadata:factor_oksigeniaaty_req'] = 'Each request to approve a sign-in, kept for a day.';
$string['privacy:metadata:factor_oksigeniaaty_req:device'] = 'The browser and system of the sign-in.';
$string['privacy:metadata:factor_oksigeniaaty_req:ip'] = 'The address the sign-in came from.';
$string['privacy:metadata:factor_oksigeniaaty_req:status'] = 'What happened to the request.';
$string['privacy:metadata:factor_oksigeniaaty_req:timeanswered'] = 'When it was answered.';
$string['privacy:metadata:factor_oksigeniaaty_req:timecreated'] = 'When it was made.';
$string['privacy:metadata:factor_oksigeniaaty_req:userid'] = 'The person signing in.';
$string['privacy:metadata:factor_oksigeniaaty_req:via'] = 'Where it was answered: the app, the web or a chat.';
$string['settings:description'] = 'People approve each sign-in from a notice on their phone: the sign-in screen shows a number, and the phone offers three numbers and «It was not me». Tapping the one on the screen lets the sign-in go on; any other answer stops it, so a hurried tap lets nobody in. It works for whoever has the app on a phone that gets the site\'s notices, and on Telegram and WhatsApp with Oksigenia Marandu.';
$string['settings:shortdescription'] = 'Approve each sign-in from a notice on the phone, by tapping the number on the screen.';
$string['settings:validity'] = 'Time to answer';
$string['settings:validity_help'] = 'How long the phone has to answer a request. After that, the sign-in screen offers to send another one.';
$string['summarycondition'] = 'has the app on a phone that gets notices, or a chat for them';
$string['wait'] = 'Waiting for your phone…';
$string['wait:again'] = 'Send another notice';
$string['wait:codearia'] = 'The number to tap on your phone: {$a}';
$string['wait:expired'] = 'The notice has expired.';
$string['wait:pick'] = 'Open it and tap this number:';
$string['wait:toomany'] = 'Too many notices in a short time. Wait a few minutes, or choose another way to sign in.';
$string['wait:unsent'] = 'We could not send the notice to your phone. Choose another way to sign in.';
$string['where'] = '{$a->device}, at {$a->time}';
