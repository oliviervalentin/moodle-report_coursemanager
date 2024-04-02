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
 * Admin interface for choosing tool to apply from Course Manager.
 *
 *
 * @package     report_coursemanager
 * @copyright   2022 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');

// Login and check capabilities.
require_login();
require_capability('report/coursemanager:admintools', context_system::instance());

global $PAGE, $DB, $USER, $CFG;

$site = get_site();

$PAGE = new moodle_page();
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('title', 'report_coursemanager'));
$PAGE->set_url('/report/coursemanager/admin_dashboard/index.php');
$PAGE->set_pagelayout('mycourses');

$PAGE->set_pagetype('report-coursemanager');
$PAGE->blocks->add_region('content');
$PAGE->set_title($site->fullname);

echo $OUTPUT->header();

echo html_writer::div(get_string('admin_course_managerinfo', 'report_coursemanager'));

$table = new html_table();
$table->attributes['class'] = 'table-striped';
$table->align = ['left', 'left'];
$table->head = [];

// Define headings for table.
$table->head[] = get_string('table_tool_name', 'report_coursemanager');
$table->head[] = get_string('table_tool_description', 'report_coursemanager');

// Link for orphan submissions tool.
$url = new moodle_url('/report/coursemanager/admin_dashboard/orphaned_submissions.php');
$row = new html_table_row([
    html_writer::link($url, get_string('title_admin_orphan_submissions', 'report_coursemanager')),
    html_writer::div(get_string('admin_orphan_submissions_info', 'report_coursemanager'), null),
]);
$row->attributes['class'] = 'align-top';
$table->data[] = $row;

// Link for courses without teachers tool.
$url = new moodle_url('/report/coursemanager/admin_dashboard/courses_without_teachers.php');
$row = new html_table_row([
    html_writer::link($url, get_string('title_admin_no_teacher_courses', 'report_coursemanager')),
    html_writer::div(get_string('admin_no_teacher_courses_info', 'report_coursemanager'), null),
]);
$row->attributes['class'] = 'align-top';
$table->data[] = $row;

// Link for files distribution in table mdl_files tool.
$url = new moodle_url('/report/coursemanager/admin_dashboard/files_distribution.php');
$row = new html_table_row([
    html_writer::link($url, get_string('title_admin_files_distribution', 'report_coursemanager')),
    html_writer::div(get_string('admin_files_distribution_info', 'report_coursemanager'), null),
]);
$row->attributes['class'] = 'align-top';
$table->data[] = $row;

// Link for stats page.
$url = new moodle_url('/report/coursemanager/admin_dashboard/stats.php');
$row = new html_table_row([
    html_writer::link($url, get_string('title_admin_stats', 'report_coursemanager')),
    html_writer::div(get_string('admin_stats_info', 'report_coursemanager'), null),
]);
$row->attributes['class'] = 'align-top';
$table->data[] = $row;

echo html_writer::table($table);

echo $OUTPUT->footer();
