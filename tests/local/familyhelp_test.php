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
 * The help of the family: who can give the code, the code that only works in the browser that asked, «They did not
 * ask me», and the limits.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \factor_oksigeniaaty\local\familyhelp
 * @covers     \factor_oksigeniaaty\local\accesses
 */
final class familyhelp_test extends \advanced_testcase {
    /** @var string Oksigenia Family's API. */
    private const FAMILY = '\local_oksigeniafamily\api';

    /**
     * The factor on, and Oksigenia Family, without which there is no help of the family.
     */
    protected function setUp(): void {
        parent::setUp();
        if (!class_exists(self::FAMILY)) {
            $this->markTestSkipped('Oksigenia Family is not installed.');
        }
        $this->resetAfterTest();
        set_config('enabled', 1, 'tool_mfa');
        set_config('enabled', 1, 'factor_oksigeniaaty');
        \core_plugin_manager::reset_caches();
    }

    /**
     * A household with two adults with an account and one without, and a student.
     *
     * @return array [person, helper, other family's adult]
     */
    private function household(): array {
        $family = self::FAMILY;
        $generator = $this->getDataGenerator();
        $ana = $generator->create_user(['firstname' => 'Ana', 'lastname' => 'Gil']);
        $luis = $generator->create_user(['firstname' => 'Luis', 'lastname' => 'Gil']);
        $stranger = $generator->create_user();
        $student = $generator->create_user();
        $familyid = $family::create_family('manual');
        $family::add_adult($familyid, ['firstname' => 'Ana', 'lastname' => 'Gil', 'userid' => $ana->id]);
        $family::add_adult($familyid, ['firstname' => 'Luis', 'lastname' => 'Gil', 'userid' => $luis->id]);
        $family::add_adult($familyid, ['firstname' => 'Eva', 'lastname' => 'Gil', 'email' => 'eva@example.com']);
        $family::add_child($familyid, (int) $student->id);
        $other = $family::create_family('manual');
        $family::add_adult($other, ['firstname' => 'Otro', 'lastname' => 'Adulto', 'userid' => $stranger->id]);
        return [$ana, $luis, $stranger];
    }

    /**
     * Only the other adults of the household with an account help; nobody when the setting is off.
     */
    public function test_helpers(): void {
        [$ana, $luis, $stranger] = $this->household();
        $this->assertSame([(int) $luis->id], familyhelp::helpers((int) $ana->id));
        $this->assertSame([], familyhelp::helpers((int) $stranger->id));
        set_config('familyhelp', 0, 'factor_oksigeniaaty');
        $this->assertSame([], familyhelp::helpers((int) $ana->id));
    }

    /**
     * The adult is told and sees the code; it passes once, and only in the browser that asked.
     */
    public function test_ask_and_spend(): void {
        [$ana, $luis, $stranger] = $this->household();
        $sink = $this->redirectMessages();
        $help = familyhelp::ask((int) $ana->id, 'browser-a');
        $this->assertNotNull($help);
        $messages = $sink->get_messages();
        $this->assertCount(2, $messages);
        $tohelper = array_values(array_filter($messages, fn($m) => (int) $m->useridto === (int) $luis->id));
        $this->assertCount(1, $tohelper);
        $this->assertSame('familyhelp', $tohelper[0]->eventtype);
        $this->assertStringNotContainsString($help->code, $tohelper[0]->fullmessage);

        $this->assertNull(familyhelp::for_helper((int) $help->id, (int) $stranger->id));
        $seen = familyhelp::for_helper((int) $help->id, (int) $luis->id);
        $this->assertSame($help->code, $seen->code);

        $this->assertFalse(familyhelp::spend((int) $ana->id, 'browser-b', $help->code));
        $this->assertFalse(familyhelp::spend((int) $ana->id, 'browser-a', '000000' === $help->code ? '111111' : '000000'));
        $typed = substr($help->code, 0, 3) . ' ' . substr($help->code, 3);
        $this->assertTrue(familyhelp::spend((int) $ana->id, 'browser-a', $typed));
        $this->assertFalse(familyhelp::spend((int) $ana->id, 'browser-a', $help->code));
        $this->assertSame('', (string) familyhelp::for_session((int) $ana->id, 'browser-a')->code);
    }

    /**
     * «They did not ask me» stops it, and nobody can ask again for a day.
     */
    public function test_refuse(): void {
        [$ana, $luis, $stranger] = $this->household();
        $help = familyhelp::ask((int) $ana->id, 'browser-a');
        $this->assertFalse(familyhelp::refuse((int) $help->id, (int) $stranger->id));
        $this->assertTrue(familyhelp::refuse((int) $help->id, (int) $luis->id));
        $this->assertFalse(familyhelp::spend((int) $ana->id, 'browser-a', $help->code));
        $this->assertFalse(familyhelp::can_ask((int) $ana->id));
        $this->assertNull(familyhelp::ask((int) $ana->id, 'browser-b'));
    }

    /**
     * No more than three a day; an expired one does not pass; a new one replaces the one waiting.
     */
    public function test_limits(): void {
        global $DB;
        [$ana] = $this->household();
        $first = familyhelp::ask((int) $ana->id, 'browser-a');
        $second = familyhelp::ask((int) $ana->id, 'browser-a');
        $helper = familyhelp::helpers((int) $ana->id)[0];
        $this->assertSame(familyhelp::EXPIRED, familyhelp::for_helper((int) $first->id, $helper)->status);
        $DB->set_field(familyhelp::TABLE, 'timeexpires', time() - 1, ['id' => $second->id]);
        $this->assertFalse(familyhelp::spend((int) $ana->id, 'browser-a', $second->code));
        $this->assertNotNull(familyhelp::ask((int) $ana->id, 'browser-a'));
        $this->assertNull(familyhelp::ask((int) $ana->id, 'browser-a'));
    }

    /**
     * «Your accesses» names who can help.
     */
    public function test_accesses(): void {
        [$ana] = $this->household();
        $this->setUser($ana);
        $this->assertStringContainsString('Luis Gil', accesses::context((int) $ana->id)['recovery']['helperstext']);
    }
}
