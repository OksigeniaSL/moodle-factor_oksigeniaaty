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

    if ($oldversion < 2026112200) {
        // The codes to register a phone.
        $table = new xmldb_table('factor_oksigeniaaty_enrol');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('codehash', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, 'pending');
        $table->add_field('toolmfaid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timeexpires', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_index('codehash', XMLDB_INDEX_NOTUNIQUE, ['codehash']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        // Before 0.2 every person got an empty row of the factor without registering anything: a phone now has a key.
        $DB->delete_records_select('tool_mfa', "factor = ? AND (secret IS NULL OR secret = '')", ['oksigeniaaty']);
        // Requests made before 0.2 were answered without a key: none stays open.
        $DB->set_field('factor_oksigeniaaty_req', 'status', 'expired', ['status' => 'pending']);
        upgrade_plugin_savepoint(true, 2026112200, 'factor', 'oksigeniaaty');
    }

    if ($oldversion < 2026112209) {
        // The recovery codes.
        $table = new xmldb_table('factor_oksigeniaaty_codes');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('codehash', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timeused', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('userid', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
        $table->add_index('codehash', XMLDB_INDEX_NOTUNIQUE, ['codehash']);
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        upgrade_plugin_savepoint(true, 2026112209, 'factor', 'oksigeniaaty');
    }

    return true;
}
