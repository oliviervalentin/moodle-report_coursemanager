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
 * Class for reports calculation.
 *
 * @copyright  2022 Olivier VALENTIN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class run_orphan_submissions_report_task extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        // Shown in admin screens.
        return get_string('runorphansubmissionstask', 'report_coursemanager');
    }

    /**
     * Execute the scheduled task.
     */
    public function execute() {
        mtrace("... Start coursemanager report for orphaned submissions.");

        // If orphan submissions report is enabled in settings, start process.
        if (get_config('report_coursemanager', 'enable_orphans_task') == 1) {
            global $CFG, $DB, $USER;
            $table = 'report_coursemanager_orphans';

            $listassigns = $DB->get_records('assign', [], 'id ASC', 'id, course');
            foreach ($listassigns as $assign) {
                $sqlcontextid = 'SELECT id
                FROM {course_modules}
                WHERE module = 1
                AND instance = ?
                ';
                $dbresultcontextid = $DB->get_record_sql($sqlcontextid, [$assign->id]);
                $cm = get_coursemodule_from_id('assign', $dbresultcontextid->id);
                $context = \context_module::instance($cm->id);

                $sqlassignsorphans = 'SELECT SUM(f.filesize) AS total_size, COUNT(f.id) AS total_files
                FROM {files} f
                JOIN {user} u ON f.userid = u.id
                JOIN {assignsubmission_file} asf ON asf.submission=f.itemid
                JOIN {assign} a ON a.id = asf.assignment
                JOIN {course} course ON a.course = course.id
                WHERE f.component = \'assignsubmission_file\'
                AND filename != \'.\'
                AND contextid = :contextid
                AND u.id  NOT IN (
                    SELECT us.id
                    FROM {course} c
                    JOIN {enrol} en ON en.courseid = c.id
                    JOIN {user_enrolments} ue ON ue.enrolid = en.id
                    JOIN {user} us ON us.id = ue.userid
                    WHERE c.id = :courseid
                )';
                $paramsdbassignsorphans = [
                    'contextid' => $context->id,
                    'courseid' => $assign->course,
                ];
                $dbresultassignsorphans = new \stdClass;
                $dbresultassignsorphans = $DB->get_records_sql($sqlassignsorphans, $paramsdbassignsorphans);
                foreach ($dbresultassignsorphans as $orphan) {
                    // First check if this report exists.
                    $existsorphans = $DB->get_record('report_coursemanager_orphans',
                    ['course' => $assign->course, 'cmid' => $cm->id]);

                    if ($orphan->total_files > 0) {
                        // Orphaned submissions detected for this assign, create or update entry.
                        $data = new \stdClass();
                        $data->course = $assign->course;
                        $data->cmid = $cm->id;
                        $data->weight = $orphan->total_size;
                        $data->files = $orphan->total_files;
                        $data->timecreated = time();

                        // If empty course alert doesn't exist for this assign, create it in DB.
                        if (empty($existsorphans)) {
                            $res = $DB->insert_record($table, $data);
                        } else {
                            // If exists, update orphans submissions size and files count.
                            $data->id = $existsorphans->id;
                            $data->weight = $orphan->total_size;
                            $data->files = $orphan->total_files;
                            $res = $DB->update_record($table, $data);
                        }
                        unset($data);
                    } else if ($orphan->total_files == 0) {
                        // In this case, no orphan submissions detected. If alert exists, delete it.
                        if (!empty($existsorphans)) {
                            $res = $DB->delete_records($table, ['id' => $existsorphans->id]);
                            unset($data);
                        }
                    }
                    unset($existsorphans);
                }
            }
            mtrace("... End coursemanager report for orphaned submissions.");
        } else {
            // Orphan submissions report is not enabled in plugin settings, nothing happens.
            mtrace("...... Reports for orphan submissions is not enabled !");
        }
    }
}
