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
 * List courses with orphans submissions and delete them.
 *
 * @package     report_coursemanager
 * @copyright   2022 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');

// Login and check capabilities.
require_login();
require_capability('report/coursemanager:admintools', context_system::instance());

global $PAGE, $DB, $USER, $CFG;

// Declare optional parameters.
$delete = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$instance  = optional_param('instance', 0, PARAM_INT);
$course  = optional_param('course', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);

$site = get_site();

$PAGE = new moodle_page();
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('title', 'report_coursemanager'));
$PAGE->set_url('/report/coursemanager/admin_dashboard/orphaned_submissions.php');
$PAGE->set_pagelayout('mycourses');

$PAGE->set_pagetype('report-coursemanager');
$PAGE->blocks->add_region('content');
$PAGE->set_title($site->fullname);

if (!empty($delete)) {
    // User has confirmed deletion : orphan submissions are deleted.
    if (!empty($confirm) && confirm_sesskey()) {
        $context = context_module::instance($instance);
        $targetassign = new assign($context, null, null);

        $sqllistusersorphansubmissions = "SELECT DISTINCT(u.id), asf.submission
            FROM
            {files} AS f,
            {assignsubmission_file} AS asf,
            {assign} AS a,
            {user} AS u,
            {course} AS c,
            {course_modules} AS cm
            WHERE
            component = 'assignsubmission_file'
            AND asf.submission=f.itemid
            AND a.id = asf.assignment
            AND f.userid = u.id
            AND filename != '.'
            AND c.id = a.course
            AND a.id = cm.instance
            AND cm.id = ?
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
        ";
        $paramslistusersorphansubmissions = [$instance];
        $dbresultlistusersorphansubmissions = $DB->get_records_sql($sqllistusersorphansubmissions,
        $paramslistusersorphansubmissions);

        foreach ($dbresultlistusersorphansubmissions as $userorphan) {
            $delete = $targetassign->remove_submission($userorphan->id);
        }
        // Now that files are deleted, delete report entry.
        $purgereport = $DB->delete_records('report_coursemanager_orphans', ['cmid' => $instance, 'course' => $course]);
        $returnurl = "orphaned_submissions.php";
        redirect($returnurl);
        exit();

        // TO DO : add event when orphan submissions are deleted.
    } else {
        // Shows form to confirm before delete.
        $PAGE->navbar->add(get_string('title_admin_orphan_submissions', 'report_coursemanager'));
        $PAGE->set_heading(get_string('title', 'report_coursemanager'));

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('title_admin_orphan_submissions', 'report_coursemanager'));

        $urlconfirmdelete = new moodle_url('orphaned_submissions.php',
            ['confirm' => 1, 'delete' => 1, 'instance' => $instance, 'course' => $course, 'sesskey' => sesskey()]);

        echo $OUTPUT->confirm(get_string('deleteorphansubmissionsconfirm', 'report_coursemanager'),
                $urlconfirmdelete,
                $CFG->wwwroot . '/report/coursemanager/admin_dashboard/orphaned_submissions.php');
    }
    echo $OUTPUT->footer();
    exit();
}

echo $OUTPUT->header();

echo html_writer::div(get_string('admin_orphan_submissions_info', 'report_coursemanager'));

$table = new html_table();
$table->attributes['class'] = 'admintable generaltable';
$table->align = ['left', 'left', 'left', 'left'];
$table->head = [];

// Define headings for table.
$table->head[] = get_string('table_course_name', 'report_coursemanager');
$table->head[] = get_string('table_assign_name', 'report_coursemanager');
$table->head[] = get_string('table_files_count', 'report_coursemanager');
$table->head[] = get_string('table_files_weight', 'report_coursemanager');
$table->head[] = get_string('table_actions', 'report_coursemanager');

// Let's retrieve all records in orphans table.
$listassigns = $DB->get_records('report_coursemanager_orphans', [], 'weight DESC');
$selectedassigns = array_slice($listassigns, $page * $perpage, $perpage);
foreach ($selectedassigns as $assign) {
    $cm = get_coursemodule_from_id('assign', $assign->cmid);
    $course = $DB->get_record('course', ['id' => $assign->course]);

    // If coursemodule ($cm) is found, assign still exists, let's add row.
    // If not, entry will be deleted by cleaning task.
    if ($cm) {
        $row = [];
        $row[] = html_writer::link("/course/view.php?id=".$assign->course, $course->fullname);
        $row[] = html_writer::link("/mod/assign/view.php?id=".$assign->cmid, $cm->name);
        $row[] = html_writer::label($assign->files, null);
        $row[] = html_writer::label(number_format(ceil($assign->weight / 1048576), 0, ',', '')." Mo", null);
        $content = "<a href='/report/coursemanager/admin_dashboard/orphaned_submissions.php?delete=1
        &instance=".$assign->cmid."&course=".$assign->course."'>".get_string('deleteorphans', 'report_coursemanager')."</a>";
        $row[] = html_writer::label($content, null);
        $table->data[] = $row;
    }
}

echo html_writer::table($table);

$baseurl = new moodle_url('/report/coursemanager/admin_dashboard/orphaned_submissions.php', ['perpage' => $perpage]);
echo $OUTPUT->paging_bar(count($listassigns), $page, $perpage, $baseurl);

echo $OUTPUT->footer();
