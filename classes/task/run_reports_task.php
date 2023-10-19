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
namespace report_coursemanager\task;

defined('MOODLE_INTERNAL') || die();
// require_once($CFG->wwwroot.'/config.php');
// require_once($CFG->dirroot . '/report/coursemanager/test.php');

class run_reports_task extends \core\task\scheduled_task {
    public function get_name() {
        // Shown in admin screens.
        return get_string('runreportstask', 'report_coursemanager');
    }

    public function execute() {
        mtrace("... Start coursemanager reports.");
        global $CFG, $DB;
        $table = 'coursemanager';
        $now = time();
        
        $list_courses = get_courses();
        foreach ($list_courses as $course) {
            if ($course->id > 1) {
                $coursecontext = \context_course::instance($course->id);
                $is_teacher = get_user_roles($coursecontext, $USER->id, false);
                    
                // Let's count teachers and students enrolled in course.
                $all_teachers = get_role_users(3, $coursecontext);
                $all_students = get_role_users(5, $coursecontext);    

                // If course is in trash category, delete all reports.            
                if($course->category == get_config('report_coursemanager', 'category_bin')) {
                    $exists = $DB->get_record('coursemanager', array('course'=>$course->id));
                    if(!empty($exists)) {
                        $res = $DB->delete_records($table, array('course' => $course->id));
                    }
                    
                } else {
                    // Start reports calculation.

                    // Query for total files size in course.            
                    $sql = 'SELECT SUM(filesize)
                        FROM {files}
                        WHERE contextid 
                        IN (SELECT id FROM {context} WHERE contextlevel = 70 AND instanceid IN 
                        (SELECT id FROM {course_modules} WHERE course = ?)) ';
                    $paramsdb = array($course->id);
                    $dbresult = $DB->get_field_sql($sql, $paramsdb);
                    $filesize = number_format(ceil($dbresult / 1048576), 0, ',', '');

                    // Check if course weight information exist in database.
                    $exists_weight = $DB->get_record('coursemanager', array('course'=>$course->id, 'report'=>'weight'));
                    // Create or update weight general information.
                    $data_weight = (object)$data_weight;
                    $data_weight->course = $course->id;
                    $data_weight->report = 'weight';
                    $data_weight->detail = $filesize;
                    if (empty($exists_weight)) {
                        $res = $DB->insert_record($table, $data_weight);
                    } else {
                        // If alert existe, update total filesize.
                        $data_weight->id = $exists_weight->id;
                        $res = $DB->update_record($table, $data_weight);
                    }
                    unset($data_weight);
                    unset($exists_weight);

                    // 1- TEST FOR TOTAL COURSE SIZE.
                    // Calculate course size. If total_course_size exceeds limit, add warning.
                    // If total filesize is bigger than limit defined in parameters, create alert.

                    $exists = $DB->get_record('coursemanager', array('course'=>$course->id, 'report'=>'heavy'));

                    if ($filesize >= get_config('report_coursemanager', 'total_filesize_threshold')) {
                        $data = (object)$data;
                        $data->course = $course->id;
                        $data->report = 'heavy';
                        
                        // If size alert doesn't exist for this course, create it in DB.
                        if (empty($exists)) {
                            $res = $DB->insert_record($table, $data);
                        } else {
                            // If alert existe, possibily change total filesize.
                            $data->id = $exists->id;
                            $res = $DB->update_record($table, $data);
                        }
                        unset($data);
                    } elseif(!empty($exists)) {
                        // In this case, filesize doesn't reach limit. If alert exists, delete it.
                        $res = $DB->delete_records($table, array('id' => $exists->id));
                        unset($data);
                    }
                    unset($exists);
                    
                    // 2- TEST FOR EMPTY COURSE.
                    // Check if course entry exists in database.
                    $exists = $DB->get_record('coursemanager', array('course'=>$course->id, 'report'=>'empty'));

                    // Query to count number of activities in course.
                    $sql_empty_course = 'SELECT COUNT(mcm.id) AS count_modules
                    FROM {course} mc
                    INNER JOIN {course_modules} mcm ON (mc.id = mcm.course)
                    INNER JOIN {modules} mm ON (mcm.module = mm.id)
                    WHERE mc.id = ?
                    AND mm.name <> "forum"
                    ';
                    $paramsemptycourse = array($course->id);
                    $dbresultemptycourse = $DB->count_records_sql($sql_empty_course, $paramsemptycourse);
                    
                    // If no result, course only contains announcment forum.
                    if($dbresultemptycourse < 1) {
                        $data = (object)$data;
                        $data->course = $course->id;
                        $data->report = 'empty';
                        
                        // If empty course alert doesn't exist for this course, create it in DB.
                        if (empty($exists)) {
                            $res = $DB->insert_record($table, $data);
                        } else {
                            // Alert already exist - nothing to do !
                        }
                        unset($data);
                    } elseif(!empty($exists)) {
                        // In this case, course is not empty. If alert exists, delete it.
                        $res = $DB->delete_records($table, array('id' => $exists->id));
                        unset($data);
                    }
                    unset($exists);

                    // 3- TEST FOR TEACHERS VISITS
                    // Check if course entry exists in database.
                    $exists = $DB->get_record('coursemanager', array('course'=>$course->id, 'report'=>'no_visit_teacher'));

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
                        $data = (object)$data;
                        $data->course = $course->id;
                        $data->report = 'no_visit_teacher';
                        
                        // If no teacher visit alert doesn't exist for this course, create it in DB.
                        if (empty($exists)) {
                            $res = $DB->insert_record($table, $data);
                        } else {                
                            // Alert already exist - nothing to do !
                        }
                        unset($data);
                    } elseif(!empty($exists)) {
                        // In this case, at least one teacher has visited course. If alert exists, delete it.
                        $res = $DB->delete_records($table, array('id' => $exists->id));
                        unset($data);
                    }
                    unset($exists);

                    // 4- TEST FOR STUDENTS VISITS
                    // Check if course entry exists in database.
                    $exists_no_visit_student = $DB->get_record('coursemanager', array('course'=>$course->id, 'report'=>'no_visit_student'));
                    $exists_no_student = $DB->get_record('coursemanager', array('course'=>$course->id, 'report'=>'no_student'));

                    // If at least one student enrolled.
                    if (count($all_students) > 0) {
                        $count_student_visit = array();
                        $i=0;
                        
                        // For each student, retrieve last access in course.
                        foreach($all_students as $student){
                            $lastaccess = $DB->get_field('user_lastaccess', 'timeaccess', array('courseid' => $course->id, 'userid' => $student->id));
                            // mtrace(print_r($lastaccess));
                            // Difference between now and last access.
                            $diff = $now - $lastaccess;
                            // Calculate number of days without connection in course (86 400 equals number of seconds per day).
                            $time_student = floor($diff/86400);
                            // Si limit is under last_access_student, student has visited course.
                            if ($time_student <= get_config('report_coursemanager', 'last_access_student')) {
                                // Let's count a visit.
                                array_push($count_student_visit, 'visit');
                                $i++;
                            }
                            unset($time_student);
                        }

                        $res_count_student_visit = array_count_values($count_student_visit);
                        // If res_count_student_visit is empty : no student has visited course.
                        if ($i==0) {                    
                            $data = (object)$data;
                            $data->course = $course->id;
                            $data->report = 'no_visit_student';
                            
                            // First, delete entry "zero student" for this course.
                            $res = $DB->delete_records($table, array('id' => $exists_no_student->id));
                                                
                            if (empty($exists_no_visit_student)) {
                                $res = $DB->insert_record($table, $data);
                            } else {
                                // $data->id = $exists->id;
                                // $res = $DB->update_record($table, $data);
                            }
                        } elseif(!empty($exists_no_visit_student)) {
                            $res = $DB->delete_records($table, array('id' => $exists_no_visit_student->id));
                        }
                        unset($data);
                    } else {
                        // In this case, no student enrolled in course.                
                        $data = (object)$data;
                        $data->course = $course->id;
                        $data->report = 'no_student';
                        
                        // First, delete entry "zero student" for this course.
                        $res = $DB->delete_records($table, array('id' => $exists_no_visit_student->id));
                        
                        if (empty($exists_no_student)) {
                                $res = $DB->insert_record($table, $data);
                        } else {
                            // Alert already exist - nothing to do !
                        }
                        unset($data);
                        unset($count_student_visit);
                    }
                    
                }
                // Tests end?
            }
        }
    mtrace("... End coursemanager reports.");

    // TO DO : list reports for deleted courses and delete them !!!
    }
}
