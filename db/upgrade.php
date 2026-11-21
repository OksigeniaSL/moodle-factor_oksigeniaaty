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
 * Upgrade steps of factor_oksigeniaaty.
 *
 * @package    factor_oksigeniaaty
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade the factor.
 *
 * @param int $oldversion The version installed.
 * @return bool
 */
function xmldb_factor_oksigeniaaty_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2026112104) {
        // The text columns lose their empty default, which Moodle does not take on a NOT NULL column.
        $table = new xmldb_table('factor_oksigeniaaty_req');
        foreach (['device' => 255, 'ip' => 45, 'via' => 20] as $name => $length) {
            $field = new xmldb_field($name, XMLDB_TYPE_CHAR, $length, null, XMLDB_NOTNULL, null, null);
            $dbman->change_field_default($table, $field);
        }
        upgrade_plugin_savepoint(true, 2026112104, 'factor', 'oksigeniaaty');
    }

    return true;
}
