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
 * Class for teachers reports calculation.
 *
 * @copyright  2022 Olivier VALENTIN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class run_teacher_visit_report_task extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        // Shown in admin screens.
        return get_string('runteachervisitreporttask', 'report_coursemanager');
    }

    /**
     * Execute the scheduled task.
     */
    public function execute() {
        mtrace("... Start coursemanager report for courses without teachers visits or without teachers enrolled.");

        // If teachers report is enabled in settings, start process.
        if (get_config('report_coursemanager', 'enable_teachers_task') == 1) {
            global $CFG, $DB, $USER;
            $table = 'report_coursemanager_reports';
            $now = time();

            $listcourses = get_courses();
            foreach ($listcourses as $course) {
                if ($course->id > 1) {
                    $coursecontext = \context_course::instance($course->id);

                    // Let's count teachers and students enrolled in course.
                    $allteachers = get_role_users(get_config('report_coursemanager', 'teacher_role_dashboard'), $coursecontext);

                    // If course is in trash category, delete all reports.
                    if ($course->category == get_config('report_coursemanager', 'category_bin')) {
                        $exists = $DB->get_records('report_coursemanager_reports', ['course' => $course->id]);
                        if (!empty($exists)) {
                            $res = $DB->delete_records($table, ['course' => $course->id]);
                        }
                    } else {
                        // Start reports calculation.
                        // REPORTS FOR TEACHERS - VISITS AND COURSES WITHOUT TEACHERS
                        // Check if teachers reports exist.
                        $existsnoteacherincourse = $DB->get_record('report_coursemanager_reports',
                        ['course' => $course->id, 'report' => 'no_teacher_in_course']);
                        $existsnovisitteacher = $DB->get_record('report_coursemanager_reports',
                        ['course' => $course->id, 'report' => 'no_visit_teacher']);

                        // CASE 1 : if teachers are enrolled in course, test for visit.
                        if (count($allteachers) > 0) {
                            // As there are teachers in course, first delete "no teacher in course" report if exists.
                            if ($existsnoteacherincourse) {
                                $res = $DB->delete_records($table, ['id' => $existsnoteacherincourse->id]);
                            }

                            // Now check for teachers visits.
                            $countteachervisit = [];
                            // For each enrolled teacher, check last visit in course.
                            foreach ($allteachers as $teacher) {
                                $lastaccess = $DB->get_field('user_lastaccess', 'timeaccess',
                                ['courseid' => $course->id, 'userid' => $teacher->id]);
                                // Difference between now and last access.
                                $diff = $now - $lastaccess;
                                // Calculate number of days without connection in course (86 400 equals number of seconds per day).
                                $timeteacher = floor($diff / 86400);
                                // If limit is under last_access_teacher, teacher has visited course.
                                if ($timeteacher <= get_config('report_coursemanager', 'last_access_teacher')) {
                                    // Let's count a visit.
                                    array_push($countteachervisit, 'visited_teacher');
                                }
                            }
                            $rescountteachervisit = array_count_values($countteachervisit);

                            // If result is empty, no teacher has visited course.
                            if (!isset($rescountteachervisit['visited_teacher'])) {
                                $data = new \stdClass();
                                $data->course = $course->id;
                                $data->report = 'no_visit_teacher';

                                // If no teacher visit alert doesn't exist for this course, create it in DB.
                                if (empty($existsnovisitteacher)) {
                                    $res = $DB->insert_record($table, $data);
                                }
                                unset($data);
                            } else if (!empty($existsnovisitteacher)) {
                                // In this case, at least one teacher has visited course. If alert exists, delete it.
                                $res = $DB->delete_records($table, ['id' => $existsnovisitteacher->id]);
                                unset($data);
                            }
                            unset($existsnovisitteacher);
                        } else {
                            // CASE 2 : if no teachers are enrolled in course, add this report.

                            // As there are no teacher in course, first delete "no visit teacher" report if exists.
                            if ($existsnovisitteacher) {
                                $res = $DB->delete_records($table, ['id' => $existsnovisitteacher->id]);
                            }

                            $data = new \stdClass();
                            $data->course = $course->id;
                            $data->report = 'no_teacher_in_course';

                            if (empty($existsnoteacherincourse)) {
                                $res = $DB->insert_record($table, $data);
                            }
                            unset($data);
                            unset($existsnoteacherincourse);
                        }
                    }
                    // Tests end.
                }
            }
        } else {
            // Teachers visits and enrolments report is not enabled in plugin settings, nothing happens.
            mtrace("...... Reports for teachers is not enabled !");
        }
        mtrace("... End coursemanager teachers reports.");
    }
}
