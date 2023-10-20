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
 * Form to ask course deletion.
 *
 *
 * @package     report_coursemanager
 * @copyright   2022 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
global $COURSE, $DB, $USER, $CFG;
require_login();

// $courseid = optional_param('courseid', 0, PARAM_INT);
// $confirm = optional_param('confirm', 0, PARAM_INT);
// $context = context_course::instance($courseid, MUST_EXIST);

// require_capability('moodle/course:update', $context);
// Get site infos.
$site = get_site();

// Page settings
$PAGE = new moodle_page();
// $PAGE->set_context($context);
$PAGE->set_heading($site->fullname);

$PAGE->set_url('/report/coursemanager/delete_course.php');
$PAGE->set_pagelayout('mycourses');
$PAGE->set_pagetype('teachertools');

$PAGE->blocks->add_region('content');
$PAGE->set_title($site->fullname);
// $PAGE->set_secondary_navigation(false);
// Force the add block out of the default area.

$list_courses = get_courses();
foreach ($list_courses as $course) {
    echo "ID DU COURS : ".$course->id."<br />";
    $sql = 'SELECT cm.instance
        FROM {course_modules} cm
        JOIN {course} c ON c.id = cm.course
        JOIN {modules} m ON m.id = cm.module
        WHERE m.name ="assign"
        AND c.id = ?';
    $paramsdb = array($course->id);
    $dbresult = $DB->get_records_sql($sql, $paramsdb);
    echo "total : ".count($dbresult)."<br /><br/>";
    // print_object($dbresult);
}