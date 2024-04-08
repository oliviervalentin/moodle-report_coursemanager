<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Upgrade file for Course manager.
 *
 * @package     report_coursemanager
 * @copyright   2022 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Execute report_coursemanager upgrade from the given old version.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_report_coursemanager_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2023110307) {
        // Delete unused setting.
        unset_config('teachertools', 'report_coursemanager');
        upgrade_plugin_savepoint(true, 2023110307, 'report', 'coursemanager');
    }
    if ($oldversion < 2023110308) {
        upgrade_plugin_savepoint(true, 2023110308, 'report', 'coursemanager');
    }
    if ($oldversion < 2024021601) {
        upgrade_plugin_savepoint(true, 2024021601, 'report', 'coursemanager');
    }
    if ($oldversion < 2024021606) {
        upgrade_plugin_savepoint(true, 2024021606, 'report', 'coursemanager');
    }
    if ($oldversion < 2024021608) {
        $table = new xmldb_table('coursemanager');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'report_coursemanager_reports');
        }
        upgrade_plugin_savepoint(true, 2024021608, 'report', 'coursemanager');
    }
    if ($oldversion < 2024021609) {
        // Moodle first release in plugins directory - V3.1.0.
        upgrade_plugin_savepoint(true, 2024021609, 'report', 'coursemanager');
    }
    if ($oldversion < 2024040801) {
        // Hotfixes after Moodle release - V3.1.1.
        upgrade_plugin_savepoint(true, 2024040801, 'report', 'coursemanager');
    }
    return true;
}
