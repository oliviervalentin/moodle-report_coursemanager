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
 * calls the offlinequiz cron task for evaluating uploaded files
 *
 * @package       report
 * @subpackage    AA
 * @author        BB
 * @copyright     CCC
 * @since         Moodle 3.1+
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// namespace mod_offlinequiz\task;

// defined('MOODLE_INTERNAL') || die();
require_once('../../../../config.php');
// global $CFG, $DB;
// $list_courses = get_courses();
// print_object($list_courses);
// exit();
// Defines date and hour.


function test() {
    global $CFG, $DB, $USER;
    // raise_memory_limit(MEMORY_EXTRA);
    $list_courses = get_courses();
    // print_object($list_courses);exit();
    
    $now = time();
foreach ($list_courses as $course) {
        if ($course->id > 1) {
            echo $course->id." ".$course->fullname."<br>";
            // Get context and course infos.
            $coursecontext = context_course::instance($course->id);
            // $infocourse = $DB->get_record('course', array('id' => $course->id), 'category');
            $is_teacher = get_user_roles($coursecontext, $USER->id, false);
                
            // Let's count teachers and students enrolled in course.
            $all_teachers = get_role_users(3, $coursecontext);
            $all_students = get_role_users(5, $coursecontext);    

            // If course is in trash : informations are hidden.
            // Action menu has only one possibility : contact support to re-establish course.
            if($course->category == get_config('report_coursemanager', 'category_bin')) {
                // mtrace("My task finished");
                print ('POUBELLE ! <br/>');
            } else {
                // If course is not in trash, let's calculate all information.

                // Query for total files size in course.            
                $sql = 'SELECT SUM(filesize)
                    FROM {files}
                    WHERE contextid 
                    IN (SELECT id FROM {context} WHERE contextlevel = 70 AND instanceid IN 
                    (SELECT id FROM {course_modules} WHERE course = ?)) ';
                $paramsdb = array($course->id);
                $strictness=IGNORE_MISSING;
                $dbresult = $DB->get_field_sql($sql, $paramsdb, $strictness);
                // print($dbresult);exit();
                // Rounded files size in Mo.
                $filesize = number_format(ceil($dbresult / 1048576), 0, ',', '');

                ///////////////////////////////////////////////////
                /////  SPECIAL TESTS FOR RECOMMENDATIONS      /////
                ///////////////////////////////////////////////////

                // 1- TEST FOR TOTAL COURSE SIZE.
                // If total_course_size exceeds limit, add warning.
                echo "TEST EXISTS<br />";
                $test = $DB->get_record('coursemanager', array('course'=>$course->id, 'report'=>'heavy'));
                print_object($test);

                if (!empty($test)) { echo "OK<br />"; } else { echo "FALSE<br />";}
                
                if ($filesize >= get_config('report_coursemanager', 'total_filesize_threshold')) {
                    $data = new stdClass();
                    $data->course = $course->id;
                    $data->report = 'heavy';
                    $data->detail = $filesize;
                    
                    $res = $DB->insert_record('coursemanager', $data);
                    unset($data);
                    print ('lourd <br/>');
                }
                unset($test);
                // 2- TEST FOR EMPTY COURSE.
                // Query to count number of activities in course.
                $sql_empty_course = 'SELECT COUNT(mcm.id) AS count_modules
                FROM {course} mc
                INNER JOIN {course_modules} mcm ON (mc.id = mcm.course)
                INNER JOIN {modules} mm ON (mcm.module = mm.id)
                WHERE mc.id = ?
                AND mm.name <> \'forum\'
                ';
                $paramsemptycourse = array($course->id);
                $dbresultemptycourse = $DB->count_records_sql($sql_empty_course, $paramsemptycourse);
                
                // If no result, course only contains announcment forum.
                if($dbresultemptycourse < 1) {
                    $data = new stdClass();
                    $data->course = $course->id;
                    $data->report = 'empty';
                    
                    $res = $DB->insert_record('coursemanager', $data);
                    unset($data);
                    print ('vide <br/>');
                }
                
                // 3- TEST FOR TEACHERS VISITS
                $count_teacher_visit = array();
                // For each enrolled teacher, check last visit in course.
                foreach($all_teachers as $teacher){
                    $lastaccess = $DB->get_field('user_lastaccess', 'timeaccess', array('courseid' => $course->id, 'userid' => $teacher->id));
                    // Difference between now and last access.
                    $diff = $now - $lastaccess;
                    // Calculate number of days without connection in course (86 400 equals number of seconds per day).
                    $time_teacher = floor($diff/86400);
                    // Si limit is under last_access_teacher, teacher has visited course.
                    if ($time_teacher <= get_config('report_coursemanager', 'last_access_teacher')) {
                        // Let's count a visit.
                        array_push($count_teacher_visit, 'visited_teacher');
                    }
                }
                $res_count_teacher_visit = array_count_values($count_teacher_visit);
                // If result is empty, no teacher has visited course.
                if (!isset($res_count_teacher_visit['visited_teacher'])) {
                    $data = new stdClass();
                    $data->course = $course->id;
                    $data->report = 'no_visit_teacher';
                    
                    $res = $DB->insert_record('coursemanager', $data);
                    unset($data);
                    print ('pas de visites ens <br/>');
                }
                
                // 4- TEST FOR STUDENTS VISITS
                $count_student_visit = array();
                echo "MAINTENANT : ".$now;
                $i=0;
                echo "A vide :";
                // print_object($all_students);
                // If at least one student enrolled.
                if (count($all_students) > 0) {
                    // For each student, retrieve last access in course.
                    foreach($all_students as $student){
                        $lastaccess = $DB->get_field('user_lastaccess', 'timeaccess', array('courseid' => $course->id, 'userid' => $student->id));
                        // Difference between now and last access.
                        $diff = $now - $lastaccess;
                        // Calculate number of days without connection in course (86 400 equals number of seconds per day).
                        $time_student = floor($diff/86400);
                        echo "TIMESTU ".$time_student."<br>";
                        // Si limit is under last_access_student, student has visited course.
                        if ($time_student <= get_config('report_coursemanager', 'last_access_student') && $time_student != 0) {
                            // Let's count a visit.
                            array_push($count_student_visit, 'visited_student');
                            $i++;
                            echo "NEW : ";
                            print_r($count_student_visit);
                        }
                    }
                    $res_count_student_visit = sizeof($count_student_visit);
                    echo "compte visites    ";
                    print_object($res_count_student_visit);
                    echo "iiiiiii : ".$i."<br><br>";
                    // If result is empty, no student has visited course.
                    if ($res_count_student_visit['visited_student'] > 1) {
                        echo "PROUT";
                        $data = new stdClass();
                        $data->course = $course->id;
                        $data->report = 'no_visit_student';
                        
                        $res = $DB->insert_record('coursemanager', $data);
                        unset($data);
                        print ('pas de visites etu <br/>');
                    }
                } else {
                    $data = new stdClass();
                    $data->course = $course->id;
                    $data->report = 'no_student';
                    
                    $res = $DB->insert_record('coursemanager', $data);
                    unset($data);
                    print ('vide etus <br/>');
                }

                ////////////////////////////////////////////            
                ///// END FOR RECOMMENDATIONS TEST
                ////////////////////////////////////////////            

            }
        }
    }
}
    
$essai = test();
// print_object($essai);
