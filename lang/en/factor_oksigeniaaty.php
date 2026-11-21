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

$string['approve:approved'] = 'Done: you can go on on the other screen.';
$string['approve:changepassword'] = 'Change my password';
$string['approve:denied'] = 'Nobody got in. Change your password so that they do not try again.';
$string['approve:deny'] = 'Not me: stop the sign-in';
$string['approve:denytitle'] = 'Sign-in stopped';
$string['approve:donetitle'] = 'Approved';
$string['approve:expired'] = 'This notice has expired. If you are still signing in, ask for another one on the screen.';
$string['approve:expiredtitle'] = 'Notice expired';
$string['approve:failed'] = 'It could not be sent. Check the connection and try again.';
$string['approve:leftminute'] = '1 minute left to answer.';
$string['approve:leftminutes'] = '{$a} minutes left to answer.';
$string['approve:leftseconds'] = 'Less than a minute left to answer.';
$string['approve:nobody'] = 'Nobody at the centre will ask you to tap a number. If someone does, tap «Not me».';
$string['approve:none'] = 'There is nothing to approve now.';
$string['approve:notenrolled'] = 'This phone is not registered to approve sign-ins. Register it on the web, in your preferences: Multi-factor authentication, «Approve from the phone».';
$string['approve:pick'] = 'If it is you, tap the number you see on the other screen.';
$string['approve:pickhint'] = 'If you tap another one, we stop the sign-in.';
$string['approve:title'] = 'Is it you?';
$string['approve:who'] = 'There is a sign-in to {$a} with your account:';
$string['approve:wrong'] = 'That is not the number on the screen, so we have stopped the sign-in. If it was you, try again.';
$string['approve:wrongtitle'] = 'Sign-in stopped';
$string['chan:telegram'] = 'Telegram';
$string['chan:whatsapp'] = 'WhatsApp';
$string['device'] = '{$a->browser} on {$a->os}';
$string['device:app'] = 'The app';
$string['device:unknown'] = 'An unknown browser';
$string['enrol:done'] = 'Done: this phone approves your sign-ins.';
$string['enrol:donehint'] = 'You can go back to the other screen.';
$string['enrol:failed'] = 'This phone could not be registered: the code has been used or has expired, or it is for another account. Open the page on the web again.';
$string['enrol:title'] = 'Register this phone';
$string['enrol:working'] = 'Registering this phone…';
$string['enrolpage:intro'] = 'This code registers the phone in the app of the centre.';
$string['enrolpage:noapp'] = 'If nothing opens, install the app of the centre, sign in with your account and read the code again.';
$string['enrolpage:nocode'] = 'This link is not complete. Open the page on the web again.';
$string['enrolpage:open'] = 'Open in the app';
$string['enrolpage:title'] = 'Register this phone';
$string['error:notapproved'] = 'You have not approved it on your phone yet.';
$string['info'] = 'Each sign-in is approved from a registered phone, in the app or on a chat joined from these preferences.';
$string['label'] = 'Phone';
$string['logindesc'] = 'This sign-in is approved from your phone.';
$string['loginoption'] = 'Approve from the phone';
$string['loginsubmit'] = 'I have approved it';
$string['logintitle'] = 'Approve the sign-in from your phone';
$string['marandu:label'] = 'sign-in';
$string['marandu:login_check'] = 'Approve a sign-in';
$string['marandu:login_check_button'] = 'Open';
$string['marandu:login_check_desc'] = 'When someone signs in with the account, to approve it with the number on the screen';
$string['marandu:login_check_option_deny'] = 'It was not me';
$string['marandu:login_check_template'] = 'Is it you? Signing in to {{1}}: {{2}}. If it is you, tap the number you see on the screen: {{3}}, {{4}} or {{5}}. Nobody at the centre will ask you to tap one: if someone does, tap «It was not me».';
$string['messageprovider:approval'] = 'Requests to approve a sign-in';
$string['messageprovider:newphone'] = 'A new phone can approve your sign-ins';
$string['mobile:title'] = 'Is it you?';
$string['newphone:body'] = '{$a->device} was registered on {$a->time} to approve your sign-ins to {$a->site}. If it was not you, remove it in your preferences (Multi-factor authentication) and change your password.';
$string['newphone:link'] = 'My phones';
$string['newphone:subject'] = 'A new phone can approve your sign-ins to {$a->site}';
$string['notify:body'] = 'Is it you signing in? {$a}. Tap to approve or stop.';
$string['notify:link'] = 'Approve or stop';
$string['notify:subject'] = 'Is it you? Signing in to {$a}';
$string['pluginname'] = 'Approve from the phone';
$string['privacy:metadata:factor_oksigeniaaty_enrol'] = 'Each code to register a phone, kept for a day.';
$string['privacy:metadata:factor_oksigeniaaty_enrol:status'] = 'Whether it was used.';
$string['privacy:metadata:factor_oksigeniaaty_enrol:timecreated'] = 'When it was made.';
$string['privacy:metadata:factor_oksigeniaaty_enrol:userid'] = 'The person whose phone it registers.';
$string['privacy:metadata:factor_oksigeniaaty_req'] = 'Each request to approve a sign-in, kept for a day.';
$string['privacy:metadata:factor_oksigeniaaty_req:device'] = 'The browser and system of the sign-in.';
$string['privacy:metadata:factor_oksigeniaaty_req:ip'] = 'The address the sign-in came from.';
$string['privacy:metadata:factor_oksigeniaaty_req:status'] = 'What happened to the request.';
$string['privacy:metadata:factor_oksigeniaaty_req:timeanswered'] = 'When it was answered.';
$string['privacy:metadata:factor_oksigeniaaty_req:timecreated'] = 'When it was made.';
$string['privacy:metadata:factor_oksigeniaaty_req:userid'] = 'The person signing in.';
$string['privacy:metadata:factor_oksigeniaaty_req:via'] = 'Where it was answered: the app, the web or a chat.';
$string['settings:description'] = 'People approve each sign-in from a notice on a phone they registered beforehand, from the multi-factor preferences, after passing another factor: the app reads a QR there and gets a key of its own, and every answer goes signed with it. The sign-in screen shows a number, and the phone offers three numbers and «Not me»: tapping the one on the screen lets the sign-in go on; any other answer stops it and holds back new requests for a while. With Oksigenia Marandu, a Telegram or WhatsApp chat joined from that same page can answer too.';
$string['settings:shortdescription'] = 'Approve each sign-in from a notice on the phone, by tapping the number on the screen.';
$string['settings:validity'] = 'Time to answer';
$string['settings:validity_help'] = 'How long the phone has to answer a request. After that, the sign-in screen offers to send another one.';
$string['setup:again'] = 'The code of this page has been used or has expired. Open the page again for a new one.';
$string['setup:chats'] = 'Or approve on a chat (joining from here; a chat joined elsewhere does not approve):';
$string['setup:done'] = 'Done';
$string['setup:intro'] = 'This phone will approve your sign-ins with a key of its own.';
$string['setup:notyet'] = 'The phone is not registered yet: read the code with the app.';
$string['setup:ownchats'] = 'Chats that can approve your sign-ins (take away any that is not yours):';
$string['setup:qralt'] = 'Code to register the phone';
$string['setup:registered'] = 'Done: the phone is registered.';
$string['setup:remove'] = 'Take away';
$string['setup:samephone'] = 'Are you on that phone now?';
$string['setup:samephonelink'] = 'Register this phone';
$string['setup:since'] = 'since {$a}';
$string['setup:step1'] = 'Open the app of the centre on the phone, signed in with your account.';
$string['setup:step2'] = 'Read this code with the phone (with the app or with the camera), and open it in the app.';
$string['setup:waiting'] = 'Waiting for the app… The code lasts {$a} minutes.';
$string['stopped:again'] = 'Sign in again';
$string['stopped:denied'] = 'This sign-in was stopped from the phone. Nobody got in.';
$string['stopped:title'] = 'Sign-in stopped';
$string['stopped:wrong'] = 'Another number was tapped on the phone, so we have stopped this sign-in. Sign in again and tap the number you see here.';
$string['summarycondition'] = 'has registered a phone, with the app or a chat';
$string['task:cleanup'] = 'Delete old requests to approve a sign-in';
$string['unlinked'] = 'Done: that chat can no longer approve your sign-ins.';
$string['wait'] = 'Waiting for your phone…';
$string['wait:again'] = 'Send another notice';
$string['wait:codehidden'] = 'Number to tap on your phone:';
$string['wait:expired'] = 'The notice has expired (it lasts {$a} minutes).';
$string['wait:left'] = '{$a} left to approve it on your phone.';
$string['wait:pick'] = 'Your phone will show three numbers. Tap this one:';
$string['wait:resend'] = 'Send another notice';
$string['wait:resendhint'] = 'Not getting it? Look at the bell in the app, or in your Telegram or WhatsApp.';
$string['wait:sayhalf'] = 'Thirty seconds left to approve it on your phone.';
$string['wait:sayone'] = 'One minute left to approve it on your phone.';
$string['wait:saytwo'] = 'Two minutes left to approve it on your phone.';
$string['wait:sentapp'] = 'We have sent a notice to the app on your phone.';
$string['wait:sentboth'] = 'We have sent a notice to the app on your phone and a message on Telegram or WhatsApp.';
$string['wait:sentchat'] = 'We have sent you a message on Telegram or WhatsApp.';
$string['wait:stopped'] = 'A sign-in was stopped from your phone a moment ago. Wait a while, or sign in another way, below.';
$string['wait:toomany'] = 'You have asked for several notices in a row. Wait 15 minutes, or sign in another way, below.';
$string['wait:unsent'] = 'We could not send you the notice. Sign in another way, below.';
$string['wait:valid'] = 'The notice is valid for {$a} minutes.';
$string['where'] = '{$a->device}, at {$a->time}';
