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
require_once($CFG->dirroot.'/config.php');

class mailing_task extends \core\task\scheduled_task {
    public function get_name() {
        // Shown in admin screens.
        return get_string('mailingtask', 'report_coursemanager');
    }

    public function execute() {
        mtrace("... Start coursemanager teachers mailing for reports.");
                
        // If mailing is enabled in settings, start process.
        if(get_config('report_coursemanager', 'enable_mailing') == 1) {
            global $CFG, $DB, $USER;

            // Define $from for mailing.
            $from = new \stdClass;
            $from->lastname = $CFG->supportname;
            $from->email = $CFG->supportname;
            $from->maildisplay = false;
            $from->mailformat = 1;

            $table = 'coursemanager';
            $teacher_role = get_config('report_coursemanager', 'teacher_role_dashboard');

            // List all users that have a teacher role (as defined in params) in at least one course.
            $sqllistteacher = 'SELECT DISTINCT(tra.userid) AS idteacher
                FROM {course} AS c
                LEFT JOIN {context} AS ctx ON c.id = ctx.instanceid
                JOIN {role_assignments} AS lra ON lra.contextid = ctx.id
                JOIN {role_assignments} AS tra ON tra.contextid = ctx.id
                JOIN {user} AS u ON u.id=tra.userid
                WHERE tra.roleid= ?';

            $paramsdblistteacher = array($teacher_role);
            $dbresultlistteacher = $DB->get_records_sql($sqllistteacher, $paramsdblistteacher);

            foreach($dbresultlistteacher as $teacher) {
                // We have list of teacher. Now retrieve all courses for each one.

                $sqllistcoursesforteacher = 'SELECT DISTINCT(c.id) AS courseid, c.fullname AS coursename
                FROM {course} AS c
                LEFT JOIN {context} AS ctx ON c.id = ctx.instanceid
                JOIN {role_assignments} AS lra ON lra.contextid = ctx.id
                JOIN {role_assignments} AS tra ON tra.contextid = ctx.id
                JOIN {user} AS u ON u.id=tra.userid
                WHERE tra.roleid = '.$teacher_role .'
                AND u.id = '.$teacher->idteacher.'
                ';
    
                $dbresultlistcoursesforteacher = $DB->get_records_sql($sqllistcoursesforteacher);
    
                $mailcontent = '';
                
                // Define list of reports and their titles in an object for loop.
                $allreports = new \stdClass();
                $allreports = array(
                    array('report' => 'empty', 
                        'string' => get_string('no_content', 'report_coursemanager'), 
                        'desc' => get_string('mailingddescreportempty', 'report_coursemanager')
                    ),
                    array('report' => 'no_visit_student',
                        'string' => get_string('no_visit_student', 'report_coursemanager'),
                        'desc' => get_string('mailingddescreportnovisitstudent', 'report_coursemanager')
                ),
                    array('report' => 'no_student',
                        'string' => get_string('no_student', 'report_coursemanager'),
                        'desc' => get_string('mailingddescreportnostudent', 'report_coursemanager')
                ),
                    array('report' => 'no_visit_teacher',
                        'string' => get_string('no_visit_teacher', 'report_coursemanager'),
                        'desc' => get_string('mailingddescreportnovisitteacher', 'report_coursemanager')
                ),
                    array('report' => 'heavy',
                        'string' => get_string('heavy_course', 'report_coursemanager'),
                        'desc' => get_string('mailingddescreportheavy', 'report_coursemanager')
                ),
                    array('report' => 'orphan_submissions',
                        'string' => get_string('orphan_submissions_button', 'report_coursemanager'),
                        'desc' => get_string('mailingddescreportorphansubmissions', 'report_coursemanager')
                    )
                );
                foreach($allreports as $report) {
                    // For each report, we test each course for a teacher.
                    foreach($dbresultlistcoursesforteacher as $listcourse) {
                        // If a report exists for a course, add course name to the list with direct link.
                        $checkreport = $DB->get_record('coursemanager', array('course' => $listcourse->courseid, 'report' => $report['report']));
                        if(!empty($checkreport)) {
                            // Heavy report leads to the specific page about course files.
                            if($report['report'] == 'heavy') {
                                $reportresult .= '- <a href="'.$CFG->wwwroot.'/report/coursemanager/course_files.php?courseid='.$listcourse->courseid.'">'.$listcourse->coursename.'</a><br />';
                            } else {
                                $reportresult .= '- <a href="'.$CFG->wwwroot.'/course/view.php?id='.$listcourse->courseid.'">'.$listcourse->coursename.'</a><br />';
                            }
                        }
                    }
                    // If courses are concerned by this report, add report as title and concat course list.
                    if(!empty($reportresult)) {
                        // For each final report, add title, description .
                        $mailcontent .= '<h3>'.$report['string'].' </h3>';
                        $mailcontent .= '<p>'.$report['desc'].'</p>';
                        $mailcontent .= $reportresult;
                        unset($reportresult);
                    }
                }
                // End loop.

                // Mail is sent only if there are reports for a teacher.
                if(!empty($mailcontent)){
                    $a = new \stdClass;
                    $a->coursemanagerlink = $CFG->wwwroot.'/report/coursemanager/view.php';
                    $a->no_student_time = get_config('report_coursemanager', 'last_access_student');
                    $a->no_teacher_time = get_config('report_coursemanager', 'last_access_teacher');

                    $finalcontent .= get_string('mailingintro', 'report_coursemanager', $a);
                    $finalcontent .= $mailcontent;
                    $finalcontent .= get_string('mailingoutro', 'report_coursemanager');

                    $teacheruserinfo = \core_user::get_user($teacher->idteacher);
                    $send = email_to_user($teacheruserinfo, $from, get_string('mailingtitle', 'report_coursemanager'), $finalcontent);

                    mtrace('Mail sent to user '.$teacher->idteacher);
                } 
                // Unset mail content for next teacher.
                unset($finalcontent);
            }
            // End of courses list.
        } else {
            // Mailing is not enabled in plugin settings, nothing happens.
            mtrace("...... Coursemanager mailing for teacher is not enabled !");
        }
        mtrace("... End coursemanager mailing.");
    }
}
