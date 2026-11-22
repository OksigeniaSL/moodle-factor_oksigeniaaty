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
 * Recovery codes: made, spent once, replaced, told to the person, and the sign-in screen that takes them.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \factor_oksigeniaaty\local\recovery
 * @covers     \factor_oksigeniaaty\factor
 */
final class recovery_test extends \advanced_testcase {
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
     * Ten different codes of eight digits, only their hashes kept, and the person told.
     */
    public function test_create(): void {
        global $DB;
        $user = $this->getDataGenerator()->create_user();
        $sink = $this->redirectMessages();
        $codes = recovery::create((int) $user->id);

        $this->assertCount(recovery::COUNT, $codes);
        $this->assertCount(recovery::COUNT, array_unique($codes));
        foreach ($codes as $code) {
            $this->assertMatchesRegularExpression('/^\d{4} \d{4}$/', $code);
            $this->assertFalse($DB->record_exists(recovery::TABLE, ['codehash' => str_replace(' ', '', $code)]));
        }
        $this->assertSame(recovery::COUNT, recovery::remaining((int) $user->id));
        $this->assertGreaterThan(0, recovery::made((int) $user->id));
        $messages = $sink->get_messages();
        $this->assertCount(1, $messages);
        $this->assertSame('recovery', $messages[0]->eventtype);
    }

    /**
     * A code passes once, typed with or without spaces, and only for its person.
     */
    public function test_spend(): void {
        $user = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        $codes = recovery::create((int) $user->id);
        $sink = $this->redirectMessages();

        $this->assertFalse(recovery::spend((int) $other->id, $codes[0]));
        $this->assertFalse(recovery::spend((int) $user->id, '1234'));
        $this->assertFalse(recovery::spend((int) $user->id, 'abcd efgh'));
        $this->assertTrue(recovery::spend((int) $user->id, ' ' . str_replace(' ', '-', $codes[0]) . ' '));
        $this->assertFalse(recovery::spend((int) $user->id, $codes[0]));
        $this->assertTrue(recovery::spend((int) $user->id, str_replace(' ', '', $codes[1])));
        $this->assertSame(recovery::COUNT - 2, recovery::remaining((int) $user->id));

        $messages = $sink->get_messages();
        $this->assertCount(2, $messages);
        $this->assertStringContainsString((string) (recovery::COUNT - 2), $messages[1]->fullmessage);
    }

    /**
     * New codes replace the old ones.
     */
    public function test_replace(): void {
        $user = $this->getDataGenerator()->create_user();
        $old = recovery::create((int) $user->id);
        $new = recovery::create((int) $user->id);
        $this->assertSame(recovery::COUNT, recovery::remaining((int) $user->id));
        foreach (array_diff($old, $new) as $code) {
            $this->assertFalse(recovery::spend((int) $user->id, $code));
        }
        $this->assertTrue(recovery::spend((int) $user->id, $new[0]));
    }

    /**
     * With codes and no phone, the factor still asks; resetting it for the person takes the codes too.
     */
    public function test_factor(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $factor = \tool_mfa\plugininfo\factor::get_factor('oksigeniaaty');
        $this->assertFalse($factor->has_input());

        $other = $this->getDataGenerator()->create_user();
        recovery::create((int) $other->id);
        $this->setUser($other);
        $this->assertTrue($factor->has_input());

        $factor->delete_factor_for_user($other);
        $this->assertSame(0, recovery::remaining((int) $other->id));
        $this->assertSame(0, recovery::made((int) $other->id));
    }

    /**
     * «Your accesses» says how many are left.
     */
    public function test_accesses(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $this->assertSame('', accesses::context((int) $user->id)['recovery']['made']);
        $codes = recovery::create((int) $user->id);
        recovery::spend((int) $user->id, $codes[0]);
        $recovery = accesses::context((int) $user->id)['recovery'];
        $this->assertStringContainsString((string) (recovery::COUNT - 1), $recovery['lefttext']);
        $this->assertNotSame('', $recovery['lastused']);
    }
}
