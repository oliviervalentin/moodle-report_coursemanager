<?php
// This file is part of mod_offlinequiz for Moodle - http://moodle.org/
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
 * Calls Course Manager cron task for calculating reports.
 *
 * @package     report_coursemanager
 * @copyright   2022 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursemanager\task;

/**
 * Class for empty or heavy courses reports calculation.
 *
 * @copyright  2022 Olivier VALENTIN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class run_clean_reports_task extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        // Shown in admin screens.
        return get_string('runcleanreportstask', 'report_coursemanager');
    }

    /**
     * Execute the scheduled task.
     */
    public function execute() {
        mtrace("... Start cleaning coursemanager reports.");
        global $CFG, $DB;

        // 1- Check if reports are enabled in settings. If not, clean reports from tables.

        // If content reports task is disabled, delete empty course and heavy course reports, and course weight.
        if (get_config('report_coursemanager', 'enable_course_content_task') == 0) {
            $purgereports = $DB->delete_records('report_coursemanager_reports', ['report' => 'weight']);
            $purgereports = $DB->delete_records('report_coursemanager_reports', ['report' => 'empty']);
            $purgereports = $DB->delete_records('report_coursemanager_reports', ['report' => 'heavy']);
        }
        // If teacher reports task is disabled, delete course without teachers or without teachers visits.
        if (get_config('report_coursemanager', 'enable_teachers_task') == 0) {
            $purgereports = $DB->delete_records('report_coursemanager_reports', ['report' => 'no_teacher_in_course']);
            $purgereports = $DB->delete_records('report_coursemanager_reports', ['report' => 'no_visit_teacher']);
        }
        // If student reports task is disabled, delete course without students or without sutdents visits.
        if (get_config('report_coursemanager', 'enable_students_task') == 0) {
            $purgereports = $DB->delete_records('report_coursemanager_reports', ['report' => 'no_visit_student']);
            $purgereports = $DB->delete_records('report_coursemanager_reports', ['report' => 'no_student']);
        }
        // Check if orphaned submissions is disabled, clean whole table.
        if (get_config('report_coursemanager', 'enable_orphans_task') == 0) {
            $purgereports = $DB->delete_records('report_coursemanager_orphans', []);
        }

        // 2- Select all courses having reports in general reports table and check if course exists.
        $sqllistcourseswithreports = 'SELECT DISTINCT(course) AS courseid
            FROM {report_coursemanager_reports} c
        ';
        $listcourseswithreports = $DB->get_records_sql($sqllistcourseswithreports);

        foreach ($listcourseswithreports as $clean) {
            // If course doesn't exist (deleted by admin), delete all reports in tables.
            if (!$DB->record_exists('course', ['id' => $clean->courseid])) {
                $purgereports = $DB->delete_records('report_coursemanager_reports', ['course' => $clean->courseid]);
                $purgereports = $DB->delete_records('report_coursemanager_orphans', ['course' => $clean->courseid]);
            }
        }

        // 3- Specific check for orphaned submissions.
        if (get_config('report_coursemanager', 'enable_students_task') == 1) {
            // If orphaned submissions is enabled, retrieve all reports.
            $listassigns = $DB->get_records('report_coursemanager_orphans');
            foreach ($listassigns as $assign) {
                // For each report, check if coursemodule if exists.
                $cm = get_coursemodule_from_id('assign', $assign->cmid);
                if (!$cm) {
                    // If coursemodule not found, assign has probably been deleted. Delete report.
                    $purgereports = $DB->delete_records('report_coursemanager_orphans',
                    ['course' => $assign->course, 'cmid' => $assign->cmid]);
                }
            }
        }
        mtrace("... End cleaning coursemanager reports.");
    }
}
