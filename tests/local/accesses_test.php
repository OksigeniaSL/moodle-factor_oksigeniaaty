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

namespace factor_oksigeniaaty\local;

/**
 * «Your accesses»: what it lists, who can take a phone away, and how long the sign-ins stay.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \factor_oksigeniaaty\local\accesses
 * @covers     \factor_oksigeniaaty\factor
 * @covers     \factor_oksigeniaaty\task\cleanup
 */
final class accesses_test extends \advanced_testcase {
    /**
     * The factor on, as it is when it is used.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        set_config('enabled', 1, 'tool_mfa');
        set_config('enabled', 1, 'factor_oksigeniaaty');
        \core_plugin_manager::reset_caches();
    }

    /**
     * A phone of the person, registered as the app does it.
     *
     * @param int $userid The person.
     * @param string $label The phone's name.
     * @return int Its row in tool_mfa.
     */
    private function phone(int $userid, string $label): int {
        [, $code] = devices::new_enrolment($userid);
        [$device] = devices::complete($code, $userid, $label);
        return $device;
    }

    /**
     * The person's phones and sign-ins, newest first, with what happened to each; nobody else's.
     */
    public function test_context(): void {
        global $DB;
        $user = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        $userid = (int) $user->id;
        $this->phone($userid, 'Pixel 8');
        $this->phone((int) $other->id, 'Not mine');

        $approved = request::create($userid, 's1', 'Chrome on Windows', '10.0.0.1');
        request::answer((int) $approved->id, $userid, (string) $approved->code, 'telegram');
        $denied = request::create($userid, 's2', 'Firefox on Linux', '10.0.0.2');
        request::answer((int) $denied->id, $userid, request::DENY, 'app');
        $DB->set_field(request::TABLE, 'timecreated', time() - 120, ['id' => $approved->id]);
        $DB->set_field(request::TABLE, 'timecreated', time() - 60, ['id' => $denied->id]);
        $late = request::create($userid, 's3', 'Safari on macOS', '10.0.0.3');
        $DB->set_field(request::TABLE, 'timeexpires', time() - 1, ['id' => $late->id]);
        request::create((int) $other->id, 's4', 'Edge on Windows', '10.0.0.4');

        $context = accesses::context($userid);
        $this->assertCount(1, $context['phones']);
        $this->assertSame('Pixel 8', $context['phones'][0]['name']);
        $this->assertSame('', $context['phones'][0]['lastused']);
        $this->assertSame(30, $context['days']);

        $this->assertCount(3, $context['history']);
        [$first, $second, $third] = $context['history'];
        $this->assertSame('Safari on macOS', $first['device']);
        $this->assertSame(get_string('accesses:expired', 'factor_oksigeniaaty'), $first['outcome']);
        $this->assertSame('', $first['via']);
        $this->assertTrue($second['stopped']);
        $this->assertSame(get_string('accesses:via:app', 'factor_oksigeniaaty'), $second['via']);
        $this->assertTrue($third['approved']);
        $this->assertSame(get_string('accesses:via:telegram', 'factor_oksigeniaaty'), $third['via']);
        $this->assertStringContainsString('/login/change_password.php', $context['passwordurl']);
    }

    /**
     * A sign-in waiting shows as waiting until its time is over.
     */
    public function test_outcome(): void {
        $record = (object) ['status' => request::PENDING, 'timeexpires' => time() + 60];
        $this->assertSame('pending', accesses::outcome($record));
        $record->timeexpires = time() - 1;
        $this->assertSame('expired', accesses::outcome($record));
        $this->assertSame('approved', accesses::outcome((object) ['status' => request::USED]));
        $this->assertSame('wrong', accesses::outcome((object) ['status' => request::WRONG]));
    }

    /**
     * Only the person signed in takes away a phone of theirs, and once.
     */
    public function test_remove_phone(): void {
        $user = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        $mine = $this->phone((int) $user->id, 'Pixel 8');
        $theirs = $this->phone((int) $other->id, 'Galaxy');

        $this->setUser($user);
        $this->assertFalse(accesses::remove_phone((int) $user->id, $theirs));
        $this->assertFalse(accesses::remove_phone((int) $other->id, $theirs));
        $this->assertCount(1, devices::active((int) $other->id));

        $this->assertTrue(accesses::remove_phone((int) $user->id, $mine));
        $this->assertCount(0, devices::active((int) $user->id));
        $this->assertFalse(accesses::remove_phone((int) $user->id, $mine));
    }

    /**
     * The card of the preferences says what approves and leads to «Your accesses».
     */
    public function test_manage_info(): void {
        $user = $this->getDataGenerator()->create_user();
        $device = $this->phone((int) $user->id, 'Pixel 8');
        $this->setUser($user);
        $info = \tool_mfa\plugininfo\factor::get_factor('oksigeniaaty')->get_manage_info($device);
        $this->assertStringContainsString(get_string('accesses:summaryonephone', 'factor_oksigeniaaty'), $info);
        $this->assertStringContainsString('/admin/tool/mfa/factor/oksigeniaaty/accesses.php', $info);
    }

    /**
     * The sign-ins stay 30 days; the codes to register a phone, a day.
     */
    public function test_cleanup(): void {
        global $DB;
        $user = $this->getDataGenerator()->create_user();
        $userid = (int) $user->id;
        $kept = request::create($userid, 's1', 'Chrome on Windows', '10.0.0.1');
        $gone = request::create($userid, 's2', 'Chrome on Windows', '10.0.0.1');
        $DB->set_field(request::TABLE, 'timecreated', time() - 29 * DAYSECS, ['id' => $kept->id]);
        $DB->set_field(request::TABLE, 'timecreated', time() - 31 * DAYSECS, ['id' => $gone->id]);
        [$enrol] = devices::new_enrolment($userid);
        $DB->set_field(devices::ENROLTABLE, 'timecreated', time() - 2 * DAYSECS, ['id' => $enrol]);

        (new \factor_oksigeniaaty\task\cleanup())->execute();
        $this->assertTrue($DB->record_exists(request::TABLE, ['id' => $kept->id]));
        $this->assertFalse($DB->record_exists(request::TABLE, ['id' => $gone->id]));
        $this->assertFalse($DB->record_exists(devices::ENROLTABLE, ['id' => $enrol]));
    }
}
