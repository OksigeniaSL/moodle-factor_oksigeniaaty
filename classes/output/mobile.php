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

namespace factor_oksigeniaaty\output;

use factor_oksigeniaaty\local\request;

/**
 * The factor in the Moodle app.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mobile {
    /**
     * The link of the notice opens the page of the request instead of the site in a window. No entry in the menu.
     *
     * @param array $args What the app sends.
     * @return array
     */
    public static function mobile_init(array $args): array {
        return [
            'templates' => [],
            'javascript' => (string) file_get_contents(__DIR__ . '/../../app/links.js'),
            'otherdata' => '',
            'disabled' => true,
        ];
    }

    /**
     * The request: who is signing in, from where, the three numbers and «It was not me»; or what happened to it.
     *
     * @param array $args The request (r), from the notice's link; without it, the one waiting.
     * @return array
     */
    public static function mobile_approve_view(array $args): array {
        global $OUTPUT, $USER;
        $id = (int) ($args['r'] ?? 0);
        $request = $id ? request::get($id, (int) $USER->id) : request::latest((int) $USER->id);
        return [
            'templates' => [[
                'id' => 'main',
                'html' => $OUTPUT->render_from_template(
                    'factor_oksigeniaaty/mobile_approve',
                    approval::context($request, $USER, false)
                ),
            ]],
            'javascript' => '',
            'otherdata' => '',
        ];
    }
}
