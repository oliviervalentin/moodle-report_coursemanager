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
 * List courses without teachers.
 *
 *
 * @package     report_coursemanager
 * @copyright   2022 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot.'/course/lib.php');

// Login and check capabilities.
require_login();
require_capability('report/coursemanager:admintools', context_system::instance());

global $PAGE, $DB, $USER, $CFG;

// Declare optional parameters.
$delete = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$instance  = optional_param('instance', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);

$site = get_site();

$PAGE = new moodle_page();
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('title', 'report_coursemanager'));
$PAGE->set_url('/report/coursemanager/admin_dashboard/courses_without_teachers.php');
$PAGE->set_pagelayout('mycourses');

$PAGE->set_pagetype('report-coursemanager');
$PAGE->blocks->add_region('content');
$PAGE->set_title($site->fullname);

if (!empty($delete)) {
    // User has confirmed deletion : move course in trash category.
    if (!empty($confirm) && confirm_sesskey()) {
        // If confirmed : course is moved in trash category.
        move_courses([$instance], get_config('report_coursemanager', 'category_bin'));

        // Course parameters updated : course is hidden.
        $datahide = new stdClass;
        $datahide->id = $instance;
        $datahide->visible = 0;
        $hide = $DB->update_record('course', $datahide);

        $purgereports = $DB->get_record('report_coursemanager_reports', ['course' => $instance]);
        if (!empty($purgereports)) {
            $res = $DB->delete_records('report_coursemanager_reports', ['course' => $instance]);
        }

        $returnurl = "courses_without_teachers.php";
        redirect($returnurl);
        exit();

        // TO DO : add event when course is deleted by admin.
    } else {
        // Shows form to confirm before delete.
        $PAGE->navbar->add(get_string('title_admin_no_teacher_courses', 'report_coursemanager'));
        $PAGE->set_heading(get_string('title', 'report_coursemanager'));

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('title_admin_no_teacher_courses', 'report_coursemanager'));
        echo $OUTPUT->confirm(get_string('deletecoursewithoutteachersconfirm', 'report_coursemanager'),
                "courses_without_teachers.php?delete=1&confirm=1&instance=".$instance,
                $CFG->wwwroot . '/report/coursemanager/admin_dashboard/courses_without_teachers.php');
    }
    echo $OUTPUT->footer();
    exit();
}

echo $OUTPUT->header();
$url = new moodle_url('index.php');
echo $OUTPUT->single_button($url, get_string('back'));

echo html_writer::div(get_string('admin_no_teacher_courses_info', 'report_coursemanager'));
echo html_writer::div(get_string('adminnoteachercoursesnote', 'report_coursemanager'));
// If course weight is enabled, add warning that some courses may not appear if weight hasn't been calculated.
if (get_config('report_coursemanager', 'enable_course_content_task') == 1) {
    echo html_writer::div(get_string('adminnoteachercoursesweight', 'report_coursemanager'));
}

// Check for entries in coursemanager table for courses without teachers.
// If course weight is enabled, retrieve concerned courses AND their weight.
if (get_config('report_coursemanager', 'enable_course_content_task') == 1) {
    $sqlnoteacherincourse = "SELECT r1.course AS course, r2.detail AS weight
        FROM {report_coursemanager_reports} r1
        JOIN {report_coursemanager_reports} r2 ON r1.course = r2.course
        WHERE r1.report = 'no_teacher_in_course' AND r2.report = 'weight'
        ORDER BY r2.detail DESC
    ";
} else {
    $sqlnoteacherincourse = "SELECT r1.course AS course
        FROM {report_coursemanager_reports} r1
        WHERE r1.report = 'no_teacher_in_course'
    ";
}
$paramsnoteacherincourse = [];
$existsnoteacherincourse = $DB->get_records_sql($sqlnoteacherincourse, $paramsnoteacherincourse);

if (count($existsnoteacherincourse) > 0) {
    $table = new html_table();
    $table->attributes['class'] = 'admintable generaltable';
    $table->align = ['left', 'left', 'left', 'left'];
    $table->head = [];

    // Define headings for table.
    $table->head[] = get_string('table_course_name', 'report_coursemanager');
    $table->head[] = get_string('tablecountenrolledstudents', 'report_coursemanager');
    $table->head[] = get_string('tablelastaccess', 'report_coursemanager');
    $table->head[] = get_string('tablecountmodules', 'report_coursemanager');
    // If course weight is enabled, add weight column.
    if (get_config('report_coursemanager', 'enable_course_content_task') == 1) {
        $table->head[] = get_string('tablecourseweight', 'report_coursemanager');
    }
    $table->head[] = get_string('tablelastteacherlog', 'report_coursemanager');
    $table->head[] = get_string('tablelastteacher', 'report_coursemanager');
    $table->head[] = get_string('table_actions', 'report_coursemanager');

    // For each course, retrieve informations for table.
    $selectnoteacherincourse = array_slice($existsnoteacherincourse, $page * $perpage, $perpage);
    foreach ($selectnoteacherincourse as $course) {
        if ($DB->record_exists('course', ['id' => $course->course])) {
            $coursecontext = \context_course::instance($course->course);
            // Retrieve course general information.
            $courseinfo = $DB->get_record('course', ['id' => $course->course]);
            // Count enrolled students.
            $allstudents = count(get_role_users(get_config('report_coursemanager', 'student_role_report'), $coursecontext));

            // Retrieve last user access to course.
            $sqllastaccess = 'SELECT MAX(timeaccess) AS lastaccess
                FROM {user_lastaccess}
                WHERE courseid = ?';
            $paramslastaccess  = [$course->course];
            $dbresultlastaccess  = $DB->get_record_sql($sqllastaccess, $paramslastaccess);
            if (!$dbresultlastaccess->lastaccess) {
                $lastaccess = get_string('unknown', 'report_coursemanager');
            } else {
                $lastaccess = userdate($dbresultlastaccess->lastaccess);
            }

            // Calculate number of activities.
            $sqlemptycourse = 'SELECT COUNT(mcm.id) AS count_modules
                FROM {course} mc
                INNER JOIN {course_modules} mcm ON (mc.id = mcm.course)
                INNER JOIN {modules} mm ON (mcm.module = mm.id)
                WHERE mc.id = ?
                AND mm.name <> \'forum\'
                ';
            $paramsemptycourse = [$course->course];
            $dbresultemptycourse = $DB->count_records_sql($sqlemptycourse, $paramsemptycourse);

            // Calculate last teacher log and retrieve name of the probable last teacher.
            // Information based on edulevel field of logstore table.
            // Ignore if the user is a site admin.
            $adminlist = array_keys(get_admins());
            list($notinsql, $notinparams) = $DB->get_in_or_equal($adminlist, SQL_PARAMS_NAMED, 'param', false);
            $sqllastteacherlog = 'SELECT id, userid AS teacher, timecreated AS lastlog
                        FROM {logstore_standard_log}
                        WHERE timecreated = (SELECT MAX(timecreated)
                            FROM {logstore_standard_log}
                            WHERE courseid = :courseid
                            AND edulevel = 1
                            AND userid '. $notinsql. ')';
            $paramslastteacherlog = ['courseid' => $course->course] + $notinparams;
            $dbresultlastteacherlog  = $DB->get_record_sql($sqllastteacherlog, $paramslastteacherlog);

            if ($dbresultlastteacherlog) {
                $lastteacher = $DB->get_record('user', ['id' => $dbresultlastteacherlog->teacher]);
                $namelastteacher = $lastteacher->lastname.' '.$lastteacher->firstname;
                $lastlog = userdate($dbresultlastteacherlog->lastlog);
            } else {
                $namelastteacher = get_string('unknown', 'report_coursemanager');
                $lastlog = get_string('unknown', 'report_coursemanager');
            }

            // Now start to build table rows.
            $row = [];
            $row[] = html_writer::link("/course/view.php?id=".$courseinfo->id, $courseinfo->fullname);
            $row[] = html_writer::label($allstudents, null);
            $row[] = html_writer::label(($lastaccess), null);
            $row[] = html_writer::label($dbresultemptycourse, null);
            // If course weight is enabled, add course weight.
            if (get_config('report_coursemanager', 'enable_course_content_task') == 1) {
                // Check course weight ; add - if no weight or not calculated yet.
                if (!$course->weight) {
                    $row[] = html_writer::label("-", null);
                } else {
                    $row[] = html_writer::label(display_size($course->weight), null);
                }
            }
            $row[] = html_writer::label($lastlog, null);
            $row[] = html_writer::label($namelastteacher, null);

            $deleteurl = new moodle_url('/report/coursemanager/admin_dashboard/courses_without_teachers.php',
                ['delete' => 1, 'instance' => $courseinfo->id]);
            $deletelink = "<a href='".$deleteurl."'>".get_string('text_link_delete', 'report_coursemanager')."</a>";
            $row[] = html_writer::label($deletelink, null);
            $table->data[] = $row;
        }
    }

    // Print the whole table.
    echo html_writer::table($table);

    $baseurl = new moodle_url('/report/coursemanager/admin_dashboard/courses_without_teachers.php', ['perpage' => $perpage]);
    echo $OUTPUT->paging_bar(count($existsnoteacherincourse), $page, $perpage, $baseurl);
} else {
    // If no course without teacher, add a message in place of table.
    echo html_writer::div(get_string('emptytablenoteacherincourses', 'report_coursemanager'), 'alert alert-success');
}
echo $OUTPUT->footer();
