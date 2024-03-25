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
class run_reports_task extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        // Shown in admin screens.
        return get_string('runreportstask', 'report_coursemanager');
    }

    /**
     * Execute the scheduled task.
     */
    public function execute() {
        mtrace("... Start coursemanager reports.");
        global $CFG, $DB, $USER;
        $table = 'report_coursemanager_reports';
        $now = time();

        $listcourses = get_courses();
        foreach ($listcourses as $course) {
            if ($course->id > 1) {
                $coursecontext = \context_course::instance($course->id);
                $isteacher = get_user_roles($coursecontext, $USER->id, false);

                // Let's count teachers and students enrolled in course.
                $allteachers = get_role_users(get_config('report_coursemanager', 'teacher_role_dashboard'), $coursecontext);
                $allstudents = get_role_users(get_config('report_coursemanager', 'student_role_report'), $coursecontext);

                // If course is in trash category, delete all reports.
                if ($course->category == get_config('report_coursemanager', 'category_bin')) {
                    $exists = $DB->get_record('report_coursemanager_reports', ['course' => $course->id]);
                    if (!empty($exists)) {
                        $res = $DB->delete_records($table, ['course' => $course->id]);
                    }
                } else {
                    // Start reports calculation.

                    // 0-A CALCULATE TOTAL COURSE SIZE.
                    // Query for total files size in course.
                    $sql = 'SELECT SUM(filesize)
                        FROM {files}
                        WHERE contextid
                        IN (SELECT id FROM {context} WHERE contextlevel = 70 AND instanceid IN
                        (SELECT id FROM {course_modules} WHERE course = ?)) ';
                    $paramsdb = [$course->id];
                    $dbresult = $DB->get_field_sql($sql, $paramsdb);
                    $filesize = number_format(ceil($dbresult / 1048576), 0, ',', '');

                    // Check if course weight information exist in database.
                    $existsweight = $DB->get_record('report_coursemanager_reports',
                        ['course' => $course->id, 'report' => 'weight']);
                    // Create or update weight general information.
                    $dataweight = new \stdClass();
                    $dataweight->course = $course->id;
                    $dataweight->report = 'weight';
                    $dataweight->detail = $filesize;
                    if (empty($existsweight)) {
                        $res = $DB->insert_record($table, $dataweight);
                    } else {
                        // If alert existe, update total filesize.
                        $dataweight->id = $existsweight->id;
                        $res = $DB->update_record($table, $dataweight);
                    }
                    unset($dataweight);
                    unset($existsweight);

                    // 0-B CHECK FOR ASSIGNS.
                    // Query to check if there are assigns, that will trigger orphaned submissions report.
                    $assignsql = 'SELECT cm.instance
                        FROM {course_modules} cm
                        JOIN {course} c ON c.id = cm.course
                        JOIN {modules} m ON m.id = cm.module
                        WHERE m.name =\'assign\'
                        AND c.id = ?';
                    $assignparamsdb = [$course->id];
                    $assigndbresult = $DB->get_records_sql($assignsql, $assignparamsdb);

                    // 1- TEST FOR TOTAL COURSE SIZE.
                    // If total_course_size exceeds limit, add warning.
                    // If total filesize is bigger than limit defined in parameters, create alert.

                    $existsheavy = $DB->get_record('report_coursemanager_reports', ['course' => $course->id, 'report' => 'heavy']);

                    if ($filesize >= get_config('report_coursemanager', 'total_filesize_threshold')) {
                        $data = new \stdClass();
                        $data->course = $course->id;
                        $data->report = 'heavy';

                        // If size alert doesn't exist for this course, create it in DB.
                        if (empty($existsheavy)) {
                            $res = $DB->insert_record($table, $data);
                        } else {
                            // If alert existe, possibily change total filesize.
                            $data->id = $existsheavy->id;
                            $res = $DB->update_record($table, $data);
                        }
                        unset($data);
                    } else if (!empty($existsheavy)) {
                        // In this case, filesize doesn't reach limit. If alert exists, delete it.
                        $res = $DB->delete_records($table, ['id' => $existsheavy->id]);
                        unset($data);
                    }
                    unset($existsheavy);

                    // 2- TEST FOR EMPTY COURSE.
                    // Check if course entry exists in database.
                    $existsempty = $DB->get_record('report_coursemanager_reports', ['course' => $course->id, 'report' => 'empty']);

                    // Query to count number of activities in course.
                    $sqlemptycourse = 'SELECT COUNT(mcm.id) AS count_modules
                    FROM {course} mc
                    INNER JOIN {course_modules} mcm ON (mc.id = mcm.course)
                    INNER JOIN {modules} mm ON (mcm.module = mm.id)
                    WHERE mc.id = ?
                    AND mm.name <> \'forum\'
                    ';
                    $paramsemptycourse = [$course->id];
                    $dbresultemptycourse = $DB->count_records_sql($sqlemptycourse, $paramsemptycourse);

                    // If no result, course only contains announcment forum.
                    if ($dbresultemptycourse < 1) {
                        $data = new \stdClass();
                        $data->course = $course->id;
                        $data->report = 'empty';

                        // If empty course alert doesn't exist for this course, create it in DB.
                        if (empty($existsempty)) {
                            $res = $DB->insert_record($table, $data);
                        }
                        unset($data);
                    } else if (!empty($existsempty)) {
                        // In this case, course is not empty. If alert exists, delete it.
                        $res = $DB->delete_records($table, ['id' => $existsempty->id]);
                        unset($data);
                    }
                    unset($existsempty);

                    // 3- TEST FOR TEACHERS - VISITS AND COURSES WITHOUT TEACHERS
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

                    // 4- TEST FOR STUDENTS - NO VISITS OR COURSES WITHOUT STUDENTS.
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

                    // 5- TEST FOR ORPHANS SUBMISSIONS.
                    // Check if assigns contain assignments uploaded by unenrolled users.
                    if (count($assigndbresult) > 0) {
                        $sqlassignsorphans = 'SELECT DISTINCT(f.filesize) AS filesize, a.name AS assign
                        FROM
                            {files} AS f,
                            {assignsubmission_file} AS asf,
                            {assign} AS a,
                            {user} AS u,
                            {course} AS c,
                            {course_modules} AS cm
                        WHERE
                        component = \'assignsubmission_file\'
                            AND asf.submission=f.itemid
                            AND a.id = asf.assignment
                            AND f.userid = u.id
                            AND filename != \'.\'
                            AND c.id = a.course
                            AND c.id = ?
                            AND a.id = cm.instance
                            AND u.id  NOT IN
                                (SELECT us.id
                                FROM
                                {course} AS course,
                                {enrol} AS en,
                                {user_enrolments} AS ue,
                                {user} AS us
                                WHERE c.id=course.id
                                    AND en.courseid = course.id
                                    AND ue.enrolid = en.id
                                    AND us.id = ue.userid
                                )
                            GROUP BY filesize, u.id
                        ';
                        $paramsdbassignsorphans = [$course->id];
                        $dbresultassignsorphans = $DB->get_records_sql($sqlassignsorphans, $paramsdbassignsorphans);

                        // If at least one result, add warning and show orphan submissions.
                        $existsorphans = $DB->get_record('report_coursemanager_reports',
                        ['course' => $course->id, 'report' => 'orphan_submissions']);
                        if (count($dbresultassignsorphans) > 0) {
                            // Calculate total filesize for each course.
                            $total = 0;
                            foreach ($dbresultassignsorphans as $filesize) {
                                $total += $filesize->filesize;
                            }
                            $data = new \stdClass();
                            $data->course = $course->id;
                            $data->report = 'orphan_submissions';
                            $data->detail = $total;

                            // If empty course alert doesn't exist for this course, create it in DB.
                            if (empty($existsorphans)) {
                                $res = $DB->insert_record($table, $data);
                            } else {
                                // If exists, update orphans submissions size.
                                $data->id = $existsorphans->id;
                                $res = $DB->update_record($table, $data);
                            }
                            unset($data);
                        } else if (!empty($existsorphans)) {
                            // In this case, course is not empty. If alert exists, delete it.
                            $res = $DB->delete_records($table, ['id' => $existsorphans->id]);
                            unset($data);
                        }
                        unset($existsorphans);
                    }
                }
                // Tests end.
            }
        }
        mtrace("... End coursemanager reports.");
    }
}
