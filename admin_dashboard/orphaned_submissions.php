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

// require_login();

// $courseid = optional_param('courseid', 0, PARAM_INT);
// $confirm = optional_param('confirm', 0, PARAM_INT);
// $context = context_course::instance($courseid, MUST_EXIST);

// require_capability('moodle/course:update', $context);
// Get site infos.
// $site = get_site();

// // Page settings
// $PAGE = new moodle_page();
// // $PAGE->set_context($context);
// $PAGE->set_heading($site->fullname);

// // $PAGE->set_url('/report/coursemanager/delete_course.php');
// $PAGE->set_pagelayout('mycourses');
// $PAGE->set_pagetype('teachertools');

// $PAGE->blocks->add_region('content');
// $PAGE->set_title($site->fullname);
// // $PAGE->set_secondary_navigation(false);
// // Force the add block out of the default area.




$list_courses = get_courses();
foreach ($list_courses as $course) {
    echo "----------------------------<br/>";
    echo "COURS : ".$course->fullname." (id : ".$course->id.")<br />";
    $sql = 'SELECT cm.instance, a.name
        FROM {course_modules} cm
        JOIN {course} c ON c.id = cm.course
        JOIN {modules} m ON m.id = cm.module
        JOIN {assign} a ON a.id = cm.instance
        WHERE m.name =\'assign\'
        AND c.id = ?';
    $paramsdb = array($course->id);
    $dbresult = $DB->get_records_sql($sql, $paramsdb);
// print_object($dbresult);
    echo "-> Nombre de devoirs : ".count($dbresult)."<br />";

    if(count($dbresult) > 0) {
        echo "---> Il y a des devoirs, on teste les orphelins !<br />";      
        foreach($dbresult as $assigninstance) {
            echo "----> Résultats pour ".$assigninstance->name."<br />";  
            $sqlassignsorphans = "SELECT DISTINCT(f.filesize) AS filesize, u.id , u.firstname, u.lastname
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

            // print_object($dbresultassignsorphans);

            if($dbresultassignsorphans){
                echo "------> DEVOIRS ORPHELINS DÉTECTÉS ! <br/>";
                echo "Rappel de l'instance : ".$assigninstance->instance."<br/>";
                $total = 0;
                foreach($dbresultassignsorphans as $orphansubmission) {
                    $total += $orphansubmission->filesize;
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;->".$orphansubmission->firstname. " ". $orphansubmission->lastname." (id ".$orphansubmission->id." |poids : ".$orphansubmission->filesize.")n'est plus inscrit dans ce cours.<br />";
                }
                echo "TOTAL : ".$total."<br />";
                echo "<br />";
            }else {
                echo "------> pas d'orphelins dans ce devoir.<br/><br/>";
            }
        }
    }else {
        echo "-> Pas d'activité Devoir, on passe.<br /><br/>";
    }


    // print_object($dbresult);
}
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
