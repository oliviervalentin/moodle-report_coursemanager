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
 * List coursew with orphans submissions and delete them.
 *
 *
 * @package     report_coursemanager
 * @copyright   2022 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $PAGE, $DB, $USER, $CFG;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');

require_login();

// Declare optional parameters.
$delete = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$instance  = optional_param('instance', 0, PARAM_INT);

$site = get_site();

$PAGE = new moodle_page();
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('title', 'report_coursemanager'));
$PAGE->set_url('/report/coursemanager/admin_dashboard/orphaned_submissions.php');
$PAGE->set_pagelayout('mycourses');
// $PAGE->set_secondary_navigation(false);

$PAGE->set_pagetype('teachertools');
$PAGE->blocks->add_region('content');
$PAGE->set_title($site->fullname);


if (!empty($delete)) {
    // User has confirmed deletion : orphan submissions are deleted.
    if (!empty($confirm) AND confirm_sesskey()) {
        $context = context_module::instance($instance);
        print_object($context);

        $targetassign = new assign($context,null,null);

        // $test = $prout->remove_submission(7);

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
        $paramslistusersorphansubmissions = array($instance);
        $dbresultlistusersorphansubmissions = $DB->get_records_sql($sqllistusersorphansubmissions,$paramslistusersorphansubmissions);
        // print_object($dbresultlistusersorphansubmissions);
        foreach($dbresultlistusersorphansubmissions as $userorphan) {
            // echo "supprimer pour étu ".$userorphan->id." le ou les dépôts ".$userorphan->submission."<br />";
            $delete = $targetassign->remove_submission($userorphan->id);
        }
        $returnurl = "orphaned_submissions.php";
        redirect($returnurl);
        exit();
        // delete_stickynote($note, $modulecontext);

        // // Trigger note deleted event.
        // $params = array(
        //     'context'  => $modulecontext,
        //     'objectid' => $note
        //     );
        // $event = \mod_stickynotes\event\note_deleted::create($params);
        // $event->trigger();
    } else {
        // Shows form to confirm before delete.
        // $modulecontext = context_module::instance($cm->id);
        // $coursecontext = context_course::instance($course->id);
        $PAGE->navbar->add(get_string('deletenote', 'stickynotes'));
        // $PAGE->set_title("TEST");
        $PAGE->set_heading(get_string('title', 'report_coursemanager'));

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('title_admin_orphan_submissions', 'report_coursemanager'));
        echo $OUTPUT->confirm(get_string('deleteorphansubmissionsconfirm', 'report_coursemanager'),
                "orphaned_submissions.php?delete=1&confirm=1&instance=".$instance,
                $CFG->wwwroot . '/report/coursemanager/admin_dashboard/orphaned_submissions.php');
    }
    echo $OUTPUT->footer();
    exit();
}



echo $OUTPUT->header();

echo html_writer::div(get_string('admin_orphan_submissions_info', 'report_coursemanager'));

$table = new html_table();
$table->attributes['class'] = 'admintable generaltable';
$table->align = array('left', 'left', 'left', 'left');
$table->head = array ();

// Define headings for table.
$table->head[] = get_string('table_course_name', 'report_coursemanager');
$table->head[] = get_string('table_assign_name', 'report_coursemanager');
$table->head[] = get_string('table_files_count', 'report_coursemanager');
$table->head[] = get_string('table_files_weight', 'report_coursemanager');
$table->head[] = get_string('table_actions', 'report_coursemanager');

$list_courses = get_courses();
foreach ($list_courses as $course) {

    $sql = 'SELECT cm.instance, a.name, cm.id
        FROM {course_modules} cm
        JOIN {course} c ON c.id = cm.course
        JOIN {modules} m ON m.id = cm.module
        JOIN {assign} a ON a.id = cm.instance
        WHERE m.name =\'assign\'
        AND c.id = ?';
    $paramsdb = array($course->id);
    $dbresult = $DB->get_records_sql($sql, $paramsdb);

    // echo "-> Nombre de devoirs : ".count($dbresult)."<br />";

    if(count($dbresult) > 0) {
        // echo "---> Il y a des devoirs, on teste les orphelins !<br />";
        foreach($dbresult as $assigninstance) {
            $sqlassignsorphans = "SELECT DISTINCT(f.filesize) AS filesize, u.id , u.firstname, u.lastname, f.id
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
                AND a.id = ?
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
            ";
            $paramsdbassignsorphans = array($assigninstance->instance);
            $dbresultassignsorphans = $DB->get_records_sql($sqlassignsorphans,$paramsdbassignsorphans);

            if($dbresultassignsorphans){
                $row = array ();
                // echo "----> Résultats pour ".$assigninstance->name."<br />";
                // echo "------> DEVOIRS ORPHELINS DÉTECTÉS ! <br/>";
                // echo "Rappel de l'instance : ".$assigninstance->instance."<br/>";
                $total_size = 0;
                $total_files = 0;
                foreach($dbresultassignsorphans as $orphansubmission) {
                    $total_size += $orphansubmission->filesize;
                    $total_files = $total_files+1;
                    // echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;->".$orphansubmission->firstname. " ". $orphansubmission->lastname." (id ".$orphansubmission->id." |poids : ".$orphansubmission->filesize.")n'est plus inscrit dans ce cours.<br />";
                }
                $row[] = html_writer::link("/course/view.php?id=".$course->id, $course->fullname);
                $row[] = html_writer::link("/mod/assign/view.php?id=".$assigninstance->id, $assigninstance->name);
                $row[] = html_writer::label($total_files, null);
                $row[] = html_writer::label(number_format(ceil($total_size / 1048576), 0, ',', '')." Mo", null);
                $content = "<a href='/report/coursemanager/admin_dashboard/orphaned_submissions.php?delete=1&instance=".$assigninstance->id."'>SUPPR</a>";
                $row[] = html_writer::label($content, null);
                $table->data[] = $row;

            }else {
                // echo "------> pas d'orphelins dans ce devoir.<br/><br/>";
            }

        }
        // print_object($row);
    }else {
        // echo "-> Pas d'activité Devoir, on passe.<br /><br/>";
    }
}

echo html_writer::table($table);
echo $OUTPUT->footer();



// $instance=new stdClass();
// $instance->instance='4';
// print_object($instance);
// $truc01 = assign_get_coursemodule_info($instance);
// print_object($truc01);
// $this->instance = '3';



////////////// A GARDER !!!!!!!!!!!!!!!!
// $cm = get_coursemodule_from_instance('assign', 3, 0, false, MUST_EXIST);
// $context = context_module::instance($cm->id);

// $prout = new assign($context,null,null);
// $module =  $prout->get_course_module();
// $test = $prout->remove_submission(7);
