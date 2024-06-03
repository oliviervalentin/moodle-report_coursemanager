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
    if ($oldversion < 2024040803) {
        // CSS and orphan submissions task - V3.1.1.
        $table = new xmldb_table('report_coursemanager_orphans');

        // Adding fields to table report_coursemanager_orphans.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('contextid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('weight', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('files', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table report_coursemanager_orphans.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for report_coursemanager_orphans.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $reporttodelete = 'orphan_submissions';
        $DB->delete_records_select('report_coursemanager_reports', "report = ?", [$reporttodelete]);

        upgrade_plugin_savepoint(true, 2024040803, 'report', 'coursemanager');
    }
    if ($oldversion < 2024050301) {
        // Finalized new task for orphan submissions reports - V3.2.0.
        upgrade_plugin_savepoint(true, 2024050301, 'report', 'coursemanager');
    }
    if ($oldversion < 2024050302) {
        // Separating each report in a different task.
        upgrade_plugin_savepoint(true, 2024050302, 'report', 'coursemanager');
    }
    if ($oldversion < 2024050304) {
        // Redesign orphan submissions admin page needs to rename report_coursemanager_orphans field in database.
        $table = new xmldb_table('report_coursemanager_orphans');
        $field = new xmldb_field('contextid');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, 'course');

        if ($dbman->table_exists($table) && $dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'cmid', $continue = true, $feedback = true);
        }
        // Must empty report_coursemanager_orphans table and rerun task for recording new values.
        $DB->delete_records('report_coursemanager_orphans');
        upgrade_plugin_savepoint(true, 2024050304, 'report', 'coursemanager');
    }
    if ($oldversion < 2024050305) {
        // HOTFIX : error in courses without teachers admin page.
        upgrade_plugin_savepoint(true, 2024050305, 'report', 'coursemanager');
    }
    if ($oldversion < 2024060301) {
        // Version 3.2.2.
        // Add cleaning task and fixes several bugs.
        upgrade_plugin_savepoint(true, 2024060301, 'report', 'coursemanager');
    }
    return true;
}
