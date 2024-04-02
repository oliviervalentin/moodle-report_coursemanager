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
 * Statistics about reports and Course Manager features.
 *
 * @package     report_coursemanager
 * @copyright   2022 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

// Login and check capabilities.
require_login();
require_capability('report/coursemanager:admintools', context_system::instance());

global $PAGE, $DB, $CFG;

$site = get_site();

$PAGE = new moodle_page();
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('title', 'report_coursemanager'));
$PAGE->set_url('/report/coursemanager/admin_dashboard/stats.php');
$PAGE->set_pagelayout('mycourses');

$PAGE->set_pagetype('report-coursemanager');
$PAGE->blocks->add_region('content');
$PAGE->set_title($site->fullname);

echo $OUTPUT->header();

echo html_writer::div(get_string('title_admin_stats', 'report_coursemanager'));
echo html_writer::div(get_string('admin_stats_info', 'report_coursemanager'));

// Retrieve some settings values.
$a = new stdClass();
$a->categorytrash = get_config('report_coursemanager', 'category_bin');
$a->totalfilesizethreshold = get_config('report_coursemanager', 'total_filesize_threshold');
$a->lastaccessteacher = get_config('report_coursemanager', 'last_access_teacher');
$a->lastaccessstudent = get_config('report_coursemanager', 'last_access_student');

// Total number of courses on Moodle.
$totalcountcourses = $DB->count_records('course');

// Number of courses in trash category.
$sqltotalcoursesintrash = "SELECT COUNT(*)
    FROM {course} c
    JOIN {course_categories} cc ON cc.id = c.category
    WHERE c.category = ?
";
$totalcoursesintrash = $DB->count_records_sql($sqltotalcoursesintrash, [$a->categorytrash]);

// Files weight for courses in trash category.
$sqlfilestrash = "SELECT ROUND(SUM(filesize)/1024/1024)
    FROM {files}
    WHERE contextid IN
        (SELECT id FROM {context} WHERE contextlevel = 70 AND instanceid IN
            (SELECT id FROM {course_modules} WHERE course IN
                (SELECT c.id
                FROM {course} c
                JOIN {course_categories} cc ON cc.id = c.category
                WHERE c.category = ?
                )
            )
        )";
$totalfilestrash = $DB->get_field_sql($sqlfilestrash, [$a->categorytrash]);

// Count heavy courses in Course Manager table.
$countheavycourses = $DB->count_records('report_coursemanager_reports', ['report' => 'heavy']);

// Heaviest course.
$sqlheaviestcourse = "SELECT course, detail AS weight
    FROM {report_coursemanager_reports}
    WHERE detail = (SELECT MAX(detail) FROM {report_coursemanager_reports})
    ";
$heaviestcourse = $DB->get_record_sql($sqlheaviestcourse);
if (!empty($heaviestcourse)) {
    $infoheaviest = get_course($heaviestcourse->course);
}

// Count empty courses in Course Manager table.
$countemptycourses = $DB->count_records('report_coursemanager_reports', ['report' => 'empty']);

// Count courses with orphan submissions in Course Manager table.
$countorphansubmissionscourses = $DB->count_records('report_coursemanager_reports', ['report' => 'orphan_submissions']);

// Sum filesize in Mo for orphan submissions.
if (!empty($countorphansubmissionscourses)) {
    $sqltotalorphans = "SELECT ROUND(SUM(detail)/1024/1024)
        FROM {report_coursemanager_reports}
        WHERE report = 'orphan_submissions'
        ";
    $totalorphans = $DB->get_field_sql($sqltotalorphans);
}

// Count courses without teachers in Course Manager table.
$countnoteachers = $DB->count_records('report_coursemanager_reports', ['report' => 'no_teacher_in_course']);

// Count courses without teachers visits  in Course Manager table.
$countnovisitteachers = $DB->count_records('report_coursemanager_reports', ['report' => 'no_visit_teacher']);

// Count courses without students in Course Manager table.
$countnostudents = $DB->count_records('report_coursemanager_reports', ['report' => 'no_student']);

// Count courses without students visits in Course Manager table.
$countnovisitstudents = $DB->count_records('report_coursemanager_reports', ['report' => 'no_visit_student']);

$content = '
    <div class="container">
        <h1 class="display-4">'.get_string('stats_title_courses', 'report_coursemanager').'</h1>
        <div class="row">
            <div class="card text-center m-2" style="width: 18rem;">
                <div class="card-body">
                <h5 class="card-title">'.get_string('stats_count_courses', 'report_coursemanager').'</h5>
                <small class="card-subtitle mb-2 text-muted">'
                .get_string('stats_count_courses_desc', 'report_coursemanager').'</small>
                <p class="card-text display-4"><i class="fa fa-graduation-cap"></i>  '.$totalcountcourses.'</p>
                </div>
            </div>
            <div class="card text-center m-2" style="width: 18rem;">
                <div class="card-body">
                <h5 class="card-title">'.get_string('stats_count_courses_trash', 'report_coursemanager').'</h5>
                <small class="card-subtitle mb-2 text-muted">'
                .get_string('stats_count_courses_trash_desc', 'report_coursemanager').'</small>
                <p class="card-text display-4"><i class="fa fa-trash"></i>  '.$totalcoursesintrash.'</p>
                </div>
            </div>
            <div class="card text-center m-2" style="width: 18rem;">
                <div class="card-body">
                <h5 class="card-title">'.get_string('stats_weight_courses_trash', 'report_coursemanager').'</h5>
                <small class="card-subtitle mb-2 text-muted">'
                .get_string('stats_weight_courses_trash_desc', 'report_coursemanager').'</small>
                <p class="card-text display-4"><i class="fa fa-thermometer-three-quarters"></i>  '.$totalfilestrash.' Mo</p>
                </div>
            </div>
        </div>
        <h1 class="display-4">'.get_string('stats_title_contents', 'report_coursemanager').'</h1>
        <div class="row">
            <div class="card text-center m-2" style="width: 18rem;">
                <div class="card-body">
                <h5 class="card-title">'.get_string('stats_heavy_courses', 'report_coursemanager').'</h5>
                <small class="card-subtitle mb-2 text-muted">'
                .get_string('stats_heavy_courses_desc', 'report_coursemanager', $a).'</small>
                <p class="card-text display-4"><i class="fa fa-thermometer-three-quarters"></i>  '.$countheavycourses.'</p>
                </div>
            </div>
';

if (!empty($heaviestcourse)) {
    $content .= '
    <div class="card text-center m-2" style="width: 18rem;">
                <div class="card-body">
                <h5 class="card-title">'.get_string('stats_heaviest_course', 'report_coursemanager').'</h5>
                <small class="card-subtitle mb-2 text-muted">'
                .get_string('stats_heaviest_course_desc', 'report_coursemanager').'</small>
                <p class="card-text display-4"><i class="fa fa-trophy"></i>  '.$heaviestcourse->weight.' Mo</p>
                <p class="card-text text-muted"><i class="fa fa-globe"></i> <a href="'.$CFG->wwwroot.'/course/view.php?id='
                .$heaviestcourse->course.'">'.$infoheaviest->fullname.'</a></p>
                </div>
            </div>
    ';
}

$content .= '
            <div class="card text-center m-2" style="width: 18rem;">
                <div class="card-body">
                <h5 class="card-title">'.get_string('stats_empty_courses', 'report_coursemanager').'</h5>
                <small class="card-subtitle mb-2 text-muted">'
                .get_string('stats_empty_courses_desc', 'report_coursemanager').'</small>
                <p class="card-text display-4"><i class="fa fa-battery-empty"></i>  '.$countemptycourses.'</p>
                </div>
            </div>
            <div class="card text-center m-2" style="width: 18rem;">
                <div class="card-body">
                <h5 class="card-title">'.get_string('stats_courses_orphan_submissions', 'report_coursemanager').'</h5>
                <small class="card-subtitle mb-2 text-muted">'
                .get_string('stats_courses_orphan_submissions_desc', 'report_coursemanager').'</small>
                <p class="card-text display-4"><i class="fa fa fa-files-o"></i>  '.$countorphansubmissionscourses.'</p>
                </div>
            </div>
 ';
if (!empty($countorphansubmissionscourses)) {
    $content .= '
            <div class="card text-center m-2" style="width: 18rem;">
                <div class="card-body">
                <h5 class="card-title">'.get_string('stats_weight_courses_orphan_submissions', 'report_coursemanager').'</h5>
                <small class="card-subtitle mb-2 text-muted">'
                .get_string('stats_weight_courses_orphan_submissions_desc', 'report_coursemanager').'</small>
                <p class="card-text display-4"><i class="fa fa-files-o"></i>  '.$totalorphans.' Mo</p>
                </div>
            </div>
    ';
}

 $content .= '
        </div>
        <h1 class="display-4">'.get_string('stats_title_enrolls_visits', 'report_coursemanager').'</h1>
        <div class="row">
            <div class="card text-center m-2" style="width: 18rem;">
                <div class="card-body">
                <h5 class="card-title">'.get_string('stats_count_courses_without_teachers', 'report_coursemanager').'</h5>
                <small class="card-subtitle mb-2 text-muted">'
                .get_string('stats_count_courses_without_teachers_desc', 'report_coursemanager').'</small>
                <p class="card-text display-4"><i class="fa fa-graduation-cap"></i>  '.$countnoteachers.'</p>
                </div>
            </div>
            <div class="card text-center m-2" style="width: 18rem;">
                <div class="card-body">
                <h5 class="card-title">'.get_string('stats_count_courses_without_visit_teachers', 'report_coursemanager').'</h5>
                <small class="card-subtitle mb-2 text-muted">'
                .get_string('stats_count_courses_without_visit_teachers_desc', 'report_coursemanager', $a).'</small>
                <p class="card-text display-4"><i class="fa fa-graduation-cap"></i>  '.$countnovisitteachers.'</p>
                </div>
            </div>
            <div class="card text-center m-2" style="width: 18rem;">
                <div class="card-body">
                <h5 class="card-title">'.get_string('stats_count_courses_without_students', 'report_coursemanager').'</h5>
                <small class="card-subtitle mb-2 text-muted">'
                .get_string('stats_count_courses_without_students_desc', 'report_coursemanager').'</small>
                <p class="card-text display-4"><i class="fa fa-user-o"></i>  '.$countnostudents.'</p>
                </div>
            </div>
            <div class="card text-center m-2" style="width: 18rem;">
                <div class="card-body">
                <h5 class="card-title">'.get_string('stats_count_courses_without_visit_students', 'report_coursemanager').'</h5>
                <small class="card-subtitle mb-2 text-muted">'
                .get_string('stats_count_courses_without_visit_students_desc', 'report_coursemanager', $a).'</small>
                <p class="card-text display-4"><i class="fa fa-group"></i>  '.$countnovisitstudents.'</p>
                </div>
            </div>
        </div>
    </div>
';

// Print the whole table.
echo html_writer::div($content);
echo $OUTPUT->footer();
