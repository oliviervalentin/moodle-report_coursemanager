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
 * Class for students reports calculation.
 *
 * @copyright  2022 Olivier VALENTIN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class run_student_visit_report_task extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        // Shown in admin screens.
        return get_string('runstudentvisitreporttask', 'report_coursemanager');
    }

    /**
     * Execute the scheduled task.
     */
    public function execute() {
        mtrace("... Start coursemanager report for courses without students visits or without students enrolled.");

        // If students report is enabled in settings, start process.
        if (get_config('report_coursemanager', 'enable_students_task') == 1) {
            global $CFG, $DB, $USER;
            $table = 'report_coursemanager_reports';

            $listcourses = get_courses();
            foreach ($listcourses as $course) {
                if ($course->id > 1) {
                    $coursecontext = \context_course::instance($course->id);
                    $now = time();

                    // Let's count students enrolled in course.
                    $allstudents = get_role_users(get_config('report_coursemanager', 'student_role_report'), $coursecontext);

                    // If course is in trash category, delete all reports.
                    if ($course->category == get_config('report_coursemanager', 'category_bin')) {
                        $exists = $DB->get_records('report_coursemanager_reports', ['course' => $course->id]);
                        if (!empty($exists)) {
                            $res = $DB->delete_records($table, ['course' => $course->id]);
                        }
                    } else {
                        // Start reports calculation.
                        // REPORTS FOR STUDENTS - NO VISITS OR COURSES WITHOUT STUDENTS.
                        // Check if course entry exists in database.
                        $existsnovisitstudent = $DB->get_record('report_coursemanager_reports',
                        ['course' => $course->id, 'report' => 'no_visit_student']);
                        $existsnostudent = $DB->get_record('report_coursemanager_reports',
                        ['course' => $course->id, 'report' => 'no_student']);

                        // CASE 1 : at least one student enrolled.
                        if (count($allstudents) > 0) {
                            $rescountstudentvisit = 0;

                            // As there are enrolled students, first delete "no student" report if exists.
                            if ($existsnostudent) {
                                $res = $DB->delete_records($table, ['id' => $existsnostudent->id]);
                            }

                            // For each student, retrieve last access in course.
                            foreach ($allstudents as $student) {
                                $lastaccess = $DB->get_field('user_lastaccess', 'timeaccess',
                                ['courseid' => $course->id, 'userid' => $student->id]);
                                // Difference between now and last access.
                                $diff = $now - $lastaccess;
                                // Calculate number of days without connection in course (86 400 equals number of seconds per day).
                                $timestudent = floor($diff / 86400);
                                // Si limit is under last_access_student, student has visited course.
                                if ($timestudent <= get_config('report_coursemanager', 'last_access_student')) {
                                    // Let's count a visit.
                                    $rescountstudentvisit++;
                                }
                                unset($timestudent);
                            }

                            // If res_count_student_visit is empty : no student has visited course.
                            if ($rescountstudentvisit == 0) {
                                $data = new \stdClass();
                                $data->course = $course->id;
                                $data->report = 'no_visit_student';

                                if (empty($existsnovisitstudent)) {
                                    $res = $DB->insert_record($table, $data);
                                }
                            } else if (!empty($existsnovisitstudent)) {
                                $res = $DB->delete_records($table, ['id' => $existsnovisitstudent->id]);
                            }
                            unset($data);
                        } else {
                            // CASE 2 : no student enrolled in course.
                            $data = new \stdClass();
                            $data->course = $course->id;
                            $data->report = 'no_student';

                            // First, delete entry "zero student" for this course.
                            if ($existsnovisitstudent) {
                                $res = $DB->delete_records($table, ['id' => $existsnovisitstudent->id]);
                            }
                            if (empty($existsnostudent)) {
                                    $res = $DB->insert_record($table, $data);
                            }
                            unset($data);
                            unset($countstudentvisit);
                        }
                    }
                    // Tests end.
                }
            }
        } else {
            // Students visits and enrolments report is not enabled in plugin settings, nothing happens.
            mtrace("...... Reports for students is not enabled !");
        }
        mtrace("... End coursemanager students report.");
    }
}
