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

namespace factor_oksigeniaaty;

use factor_oksigeniaaty\local\chats;
use factor_oksigeniaaty\local\devices;
use factor_oksigeniaaty\local\reach;
use factor_oksigeniaaty\local\request;

/**
 * The requests to approve a sign-in: the numbers, the answers, the time, the limits and who reaches them.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \factor_oksigeniaaty\local\request
 * @covers     \factor_oksigeniaaty\local\reach
 * @covers     \factor_oksigeniaaty\local\chats
 * @covers     \factor_oksigeniaaty\local\devices
 * @covers     \factor_oksigeniaaty\external\answer
 * @covers     \factor_oksigeniaaty\external\enrol
 */
final class request_test extends \advanced_testcase {
    /**
     * The factor on, as it is when it is used: Moodle sends the notices of a factor only while it is on.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        set_config('enabled', 1, 'tool_mfa');
        set_config('enabled', 1, 'factor_oksigeniaaty');
        \core_plugin_manager::reset_caches();
    }

    /**
     * Three different numbers, the right one among them, none that looks like another at a glance.
     */
    public function test_create_offers_three_numbers(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        for ($i = 0; $i < 20; $i++) {
            $request = request::create((int) $user->id, 's' . $i, 'Chrome on Windows', '10.0.0.1');
            $choices = request::choices($request);
            $this->assertCount(3, $choices);
            $this->assertContains((int) $request->code, $choices);
            foreach ($choices as $a) {
                $this->assertGreaterThanOrEqual(10, $a);
                $this->assertLessThanOrEqual(99, $a);
                foreach ($choices as $b) {
                    if ($a !== $b) {
                        $this->assertNotSame(intdiv($a, 10), intdiv($b, 10));
                        $this->assertNotSame($a % 10, $b % 10);
                    }
                }
            }
            $this->assertSame(request::PENDING, $request->status);
        }
    }

    /**
     * The right number approves, another number or «It was not me» stops it, and only the first answer counts.
     */
    public function test_answer(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $userid = (int) $user->id;

        $request = request::create($userid, 'a', 'Chrome on Windows', '10.0.0.1');
        $this->assertSame(request::APPROVED, request::answer((int) $request->id, $userid, (string) $request->code, 'app'));
        $this->assertSame(request::APPROVED, request::answer((int) $request->id, $userid, request::DENY, 'app'));

        $request = request::create($userid, 'b', 'Chrome on Windows', '10.0.0.1');
        $other = current(array_diff(request::choices($request), [(int) $request->code]));
        $this->assertSame(request::WRONG, request::answer((int) $request->id, $userid, (string) $other, 'app'));

        $request = request::create($userid, 'c', 'Chrome on Windows', '10.0.0.1');
        $this->assertSame(request::DENIED, request::answer((int) $request->id, $userid, request::DENY, 'web'));
        $this->assertSame(request::DENIED, request::get((int) $request->id, $userid)->status);
        $this->assertSame('web', request::get((int) $request->id, $userid)->via);
    }

    /**
     * Nobody else can answer a person's request, nor see it.
     */
    public function test_answer_only_by_the_person(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $intruder = $this->getDataGenerator()->create_user();
        $request = request::create((int) $user->id, 'a', 'Chrome on Windows', '10.0.0.1');

        $this->assertSame('', request::answer((int) $request->id, (int) $intruder->id, (string) $request->code, 'app'));
        $this->assertNull(request::get((int) $request->id, (int) $intruder->id));
        $this->assertSame(request::PENDING, request::get((int) $request->id, (int) $user->id)->status);
    }

    /**
     * After its time, a request is expired and takes no answer.
     */
    public function test_expiry(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $request = request::create((int) $user->id, 'a', 'Chrome on Windows', '10.0.0.1');
        $DB->set_field(request::TABLE, 'timeexpires', time() - 1, ['id' => $request->id]);

        $this->assertSame(request::EXPIRED, request::for_session((int) $user->id, 'a')->status);
        $status = request::answer((int) $request->id, (int) $user->id, (string) $request->code, 'app');
        $this->assertSame(request::EXPIRED, $status);
    }

    /**
     * A new request takes the place of the one waiting, in any browser: the phone only ever has one.
     */
    public function test_one_waiting(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $first = request::create((int) $user->id, 'a', 'Chrome on Windows', '10.0.0.1');
        $second = request::create((int) $user->id, 'b', 'Firefox on Linux', '10.0.0.2');

        $this->assertSame(request::EXPIRED, request::for_session((int) $user->id, 'a')->status);
        $this->assertSame(request::PENDING, request::for_session((int) $user->id, 'b')->status);
        $this->assertSame((int) $second->id, (int) request::latest((int) $user->id)->id);
        $this->assertNotSame((int) $first->id, (int) $second->id);
    }

    /**
     * A request belongs to the session that waits for it: another session of the same person does not see it.
     */
    public function test_tied_to_the_session(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        request::create((int) $user->id, 'mine', 'Chrome on Windows', '10.0.0.1');

        $this->assertNull(request::for_session((int) $user->id, 'other'));
        $this->assertNotNull(request::for_session((int) $user->id, 'mine'));
    }

    /**
     * No more than five requests in a quarter of an hour; the approved one is used only once.
     */
    public function test_limits(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $userid = (int) $user->id;
        for ($i = 0; $i < request::MAXRECENT; $i++) {
            $this->assertTrue(request::can_create($userid));
            $request = request::create($userid, 's' . $i, 'Chrome on Windows', '10.0.0.1');
        }
        $this->assertFalse(request::can_create($userid));

        request::answer((int) $request->id, $userid, (string) $request->code, 'app');
        request::mark_used((int) $request->id);
        $this->assertSame(request::USED, request::get((int) $request->id, $userid)->status);
        $this->assertSame(request::USED, request::answer((int) $request->id, $userid, (string) $request->code, 'app'));
    }

    /**
     * The phone with the app counts only with the site's push notices on and a registered device, and while the
     * person has not turned these notices off for it.
     */
    public function test_reach_app(): void {
        global $CFG, $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $userid = (int) $user->id;

        $CFG->airnotifieraccesskey = '';
        $this->assertFalse(reach::app($userid));

        $CFG->airnotifieraccesskey = 'key';
        $DB->set_field('message_processors', 'enabled', 1, ['name' => 'airnotifier']);
        $this->assertFalse(reach::app($userid));

        $DB->insert_record('user_devices', (object) [
            'userid' => $userid, 'appid' => 'com.oksigenia.aty', 'name' => 'Pixel', 'model' => 'Pixel 8',
            'platform' => 'Android', 'version' => '15', 'pushid' => 'p', 'uuid' => 'u', 'publickey' => null,
            'timecreated' => time(), 'timemodified' => time(),
        ]);
        // The app on a phone is not enough: the phone has to be registered for the factor, with its key.
        $this->assertFalse(reach::app($userid));
        [, $code] = devices::new_enrolment($userid);
        $this->assertNotNull(devices::complete($code, $userid, 'Pixel 8'));
        $this->assertTrue(reach::app($userid));
        $this->assertTrue(reach::any($userid));

        // In the app's own window, the app does not count: its notice would open under the window.
        $this->assertFalse(reach::any($userid, false));

        set_user_preference('message_provider_factor_oksigeniaaty_approval_enabled', 'popup', $userid);
        $this->assertFalse(reach::app($userid));
    }

    /**
     * The sign-in in the window of the app is told apart by the app's browser signature.
     */
    public function test_in_app_window(): void {
        \core_useragent::instance(true, 'Mozilla/5.0 (Linux; Android 10; K; wv) AppleWebKit/537.36 (KHTML, like Gecko) '
            . 'Version/4.0 Chrome/158.0.0.0 Mobile Safari/537.36 MoodleMobile 5.2.4 (52109)');
        $this->assertTrue(reach::in_app_window());
        \core_useragent::instance(true, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) '
            . 'Chrome/120.0 Safari/537.36');
        $this->assertFalse(reach::in_app_window());
        \core_useragent::instance(true);
    }

    /**
     * The request reaches the phone as a notice whose link opens it. Moodle sends the notices of a factor only while
     * it is on, which is when it is used.
     */
    public function test_send_to_app(): void {
        global $CFG, $DB;
        $user = $this->getDataGenerator()->create_user();
        $CFG->airnotifieraccesskey = 'key';
        $DB->set_field('message_processors', 'enabled', 1, ['name' => 'airnotifier']);
        $DB->insert_record('user_devices', (object) [
            'userid' => $user->id, 'appid' => 'com.oksigenia.aty', 'name' => 'Pixel', 'model' => 'Pixel 8',
            'platform' => 'Android', 'version' => '15', 'pushid' => 'p', 'uuid' => 'u', 'publickey' => null,
            'timecreated' => time(), 'timemodified' => time(),
        ]);
        [, $code] = devices::new_enrolment((int) $user->id);
        devices::complete($code, (int) $user->id, 'Pixel 8');
        $request = request::create((int) $user->id, 'a', 'Chrome on Windows', '10.0.0.1');
        $this->assertTrue(reach::app((int) $user->id));

        $sink = $this->redirectMessages();
        $sink->clear();
        $this->assertFalse(reach::send($request, false));
        $this->assertCount(0, $sink->get_messages());
        $this->assertTrue(reach::send($request));
        $messages = $sink->get_messages();
        $this->assertCount(1, $messages);
        $this->assertSame('factor_oksigeniaaty', $messages[0]->component);
        $this->assertSame('approval', $messages[0]->eventtype);
        $this->assertStringContainsString('approve.php?r=' . $request->id, $messages[0]->contexturl);
        $this->assertStringContainsString('Chrome on Windows', $messages[0]->smallmessage);
        $this->assertStringNotContainsString((string) $request->code, $messages[0]->smallmessage);
    }

    /**
     * A code registers one phone, once, for its own person, while it lasts; the phone gets its key then and only then.
     */
    public function test_enrolment(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        $userid = (int) $user->id;

        [$id, $code] = devices::new_enrolment($userid);
        $this->assertSame(devices::PENDING, devices::enrolment($id, $userid)->status);
        $this->assertNull(devices::enrolment($id, (int) $other->id));
        $this->assertNull(devices::complete($code, (int) $other->id, 'Intruder'));

        [$device, $secret] = devices::complete($code, $userid, 'Pixel 8');
        $this->assertSame(32, strlen(base64_decode($secret, true)));
        $this->assertSame(devices::DONE, devices::enrolment($id, $userid)->status);
        $this->assertSame($device, (int) devices::enrolment($id, $userid)->toolmfaid);
        $this->assertNull(devices::complete($code, $userid, 'Again'));
        $this->assertCount(1, devices::active($userid));
        $this->assertFalse($DB->record_exists(devices::ENROLTABLE, ['codehash' => $code]));

        [$id2, $code2] = devices::new_enrolment($userid);
        $DB->set_field(devices::ENROLTABLE, 'timeexpires', time() - 1, ['id' => $id2]);
        $this->assertNull(devices::complete($code2, $userid, 'Late'));
        $this->assertSame(devices::EXPIRED, devices::enrolment($id2, $userid)->status);
    }

    /**
     * Only the phone's own signature of «request:choice» counts; a revoked phone does not answer.
     */
    public function test_verify(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $userid = (int) $user->id;
        [, $code] = devices::new_enrolment($userid);
        [$device, $secret] = devices::complete($code, $userid, 'Pixel 8');
        $sign = fn(string $message, string $key) => base64_encode(hash_hmac('sha256', $message, base64_decode($key), true));

        $this->assertTrue(devices::verify($device, $userid, '7:42', $sign('7:42', $secret)));
        $this->assertFalse(devices::verify($device, $userid, '7:43', $sign('7:42', $secret)));
        $this->assertFalse(devices::verify($device, $userid, '7:42', $sign('7:42', base64_encode(random_bytes(32)))));
        $this->assertFalse(devices::verify($device, $userid, '7:42', 'not base64 !'));
        $other = $this->getDataGenerator()->create_user();
        $this->assertFalse(devices::verify($device, (int) $other->id, '7:42', $sign('7:42', $secret)));

        $DB->set_field('tool_mfa', 'revoked', 1, ['id' => $device]);
        $this->assertFalse(devices::verify($device, $userid, '7:42', $sign('7:42', $secret)));
    }

    /**
     * The app's answer counts only with the phone's signature: a token alone, which the password gives, does not.
     */
    public function test_answer_service_needs_the_key(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $userid = (int) $user->id;
        $this->setUser($user);
        [, $code] = devices::new_enrolment($userid);
        [$device, $secret] = devices::complete($code, $userid, 'Pixel 8');
        $request = request::create($userid, 'a', 'Chrome on Windows', '10.0.0.1');
        $choice = (string) $request->code;

        $result = \factor_oksigeniaaty\external\answer::execute((int) $request->id, $choice, $device, base64_encode('nothing'));
        $this->assertSame('notenrolled', $result['status']);
        $this->assertSame(request::PENDING, request::get((int) $request->id, $userid)->status);

        $proof = base64_encode(hash_hmac('sha256', $request->id . ':' . $choice, base64_decode($secret), true));
        $result = \factor_oksigeniaaty\external\answer::execute((int) $request->id, $choice, $device, $proof);
        $this->assertSame(request::APPROVED, $result['status']);
    }

    /**
     * A sign-in stopped from the phone holds back new requests for a while, and three in a day for the day.
     */
    public function test_held_back_after_stopped(): void {
        global $DB;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $userid = (int) $user->id;
        $this->assertSame('', request::held_back($userid));

        $request = request::create($userid, 'a', 'Chrome on Windows', '10.0.0.1');
        request::answer((int) $request->id, $userid, request::DENY, 'app');
        $this->assertSame('stopped', request::held_back($userid));
        $this->assertFalse(request::can_create($userid));

        $DB->set_field(request::TABLE, 'timeanswered', time() - request::COOLDOWN - 60, ['id' => $request->id]);
        $DB->set_field(request::TABLE, 'timecreated', time() - request::RECENT - 60, ['id' => $request->id]);
        $this->assertSame('', request::held_back($userid));

        foreach (['b', 'c'] as $sid) {
            $more = request::create($userid, $sid, 'Chrome on Windows', '10.0.0.1');
            request::answer((int) $more->id, $userid, request::DENY, 'app');
            $DB->set_field(request::TABLE, 'timeanswered', time() - request::COOLDOWN - 60, ['id' => $more->id]);
            $DB->set_field(request::TABLE, 'timecreated', time() - request::RECENT - 60, ['id' => $more->id]);
        }
        $this->assertSame('stopped', request::held_back($userid));
    }

    /**
     * What the chat answers for each state.
     */
    public function test_chat_replies(): void {
        $this->assertSame(get_string('approve:approved', 'factor_oksigeniaaty'), chats::reply(request::APPROVED));
        $this->assertSame(get_string('approve:approved', 'factor_oksigeniaaty'), chats::reply(request::USED));
        $this->assertSame(get_string('approve:denied', 'factor_oksigeniaaty'), chats::reply(request::DENIED));
        $this->assertSame(get_string('approve:none', 'factor_oksigeniaaty'), chats::reply(''));
    }

    /**
     * The browser and system, as the phone shows them.
     */
    public function test_describe(): void {
        $this->assertSame('Chrome on Windows', request::describe('Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
            . 'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36'));
        $this->assertSame('Edge on Windows', request::describe('Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 '
            . '(KHTML, like Gecko) Chrome/120.0 Safari/537.36 Edg/120.0'));
        $this->assertSame('Safari on iPhone', request::describe('Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) '
            . 'AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1'));
        $this->assertSame('Firefox on Linux', request::describe('Mozilla/5.0 (X11; Linux x86_64; rv:120.0) Gecko/20100101 '
            . 'Firefox/120.0'));
        $this->assertSame(get_string('device:unknown', 'factor_oksigeniaaty'), request::describe(''));
    }
}
