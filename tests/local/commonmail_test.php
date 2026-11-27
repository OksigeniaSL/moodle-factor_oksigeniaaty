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
 * The notices of the security of an account in the common look of the organization, by the way of Oksigenia Marandu
 * when it is there.
 *
 * @package    factor_oksigeniaaty
 * @category   test
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \factor_oksigeniaaty\local\commonmail
 * @covers     \factor_oksigeniaaty\local\recovery
 */
final class commonmail_test extends \advanced_testcase {
    /**
     * A notice as pieces of the mail: the greeting, a paragraph for each block of its text, a button to its page and
     * why it arrives.
     */
    public function test_security(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user(['firstname' => 'Ana']);
        $message = new \core\message\message();
        $message->subject = 'Asunto';
        $message->fullmessage = "Uno.\n\nDos.";
        $message->contexturl = 'https://example.org/accesos';
        $message->contexturlname = 'Tus accesos';

        $pieces = commonmail::security($message, $user, 'Colegio');

        $this->assertSame([
            ['paragraph' => 'Uno.'],
            ['paragraph' => 'Dos.'],
            ['action' => ['actionurl' => 'https://example.org/accesos', 'actiontext' => 'Tus accesos']],
        ], $pieces['blocks']);
        $this->assertSame(
            ['Asunto', 'Asunto', get_string('mail:hello', 'factor_oksigeniaaty', 'Ana'),
                get_string('mail:reason', 'factor_oksigeniaaty', 'Colegio')],
            [$pieces['subject'], $pieces['heading'], $pieces['hello'], $pieces['reason']]
        );
    }

    /**
     * With Oksigenia Marandu, the mail of the notice that recovery codes were made goes in the common look, with a
     * text version and the button to «Your accesses».
     */
    public function test_recovery_in_the_common_look(): void {
        if (!commonmail::available()) {
            $this->markTestSkipped('Oksigenia Marandu 0.70 or later is not installed.');
        }
        $this->resetAfterTest();
        set_config('enabled', 1, 'tool_mfa');
        set_config('enabled', 1, 'factor_oksigeniaaty');
        \core_plugin_manager::reset_caches();
        $user = $this->getDataGenerator()->create_user(['firstname' => 'Ana']);
        $sink = $this->redirectEmails();

        recovery::create((int) $user->id);

        $mails = array_values(array_filter($sink->get_messages(), fn($mail) => $mail->to === $user->email));
        $this->assertCount(1, $mails);
        $this->assertStringContainsString('Auto-Submitted: auto-generated', $mails[0]->header);
        $raw = quoted_printable_decode($mails[0]->body);
        $this->assertStringContainsString('text/plain', $raw);
        $this->assertStringContainsString('factor/oksigeniaaty/accesses.php', $raw);
        $this->assertStringContainsString('Ana', $raw);
    }
}
