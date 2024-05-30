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
 * Calls Course Manager cron task for reports mailing.
 *
 * @package     report_coursemanager
 * @copyright   2022 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursemanager\task;

/**
 * Class for mailing task.
 *
 * @copyright  2022 Olivier VALENTIN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mailing_task extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        // Shown in admin screens.
        return get_string('mailingtask', 'report_coursemanager');
    }

    /**
     * Execute the scheduled task.
     */
    public function execute() {
        mtrace("... Start coursemanager teachers mailing for reports.");

        // If mailing is enabled in settings, start process.
        if (get_config('report_coursemanager', 'enable_mailing') == 1) {
            global $CFG, $DB, $USER;

            // Define $from for mailing.
            $from = new \stdClass;
            $from->firstname = '';
            $from->lastname = $CFG->supportname;
            $from->email = $CFG->supportname;
            $from->maildisplay = false;
            $from->mailformat = 1;
            $from->firstnamephonetic = '';
            $from->lastnamephonetic = '';
            $from->middlename = '';
            $from->alternatename = '';

            $table = 'coursemanager';
            $teacherrole = get_config('report_coursemanager', 'teacher_role_dashboard');

            // List all users that have a teacher role (as defined in params) in at least one course.
            $sqllistteacher = 'SELECT DISTINCT(tra.userid) AS idteacher
                FROM {course} c
                LEFT JOIN {context} ctx ON c.id = ctx.instanceid
                JOIN {role_assignments} lra ON lra.contextid = ctx.id
                JOIN {role_assignments} tra ON tra.contextid = ctx.id
                JOIN {user} u ON u.id=tra.userid
                WHERE tra.roleid= ?';

            $paramsdblistteacher = [$teacherrole];
            $dbresultlistteacher = $DB->get_records_sql($sqllistteacher, $paramsdblistteacher);

            foreach ($dbresultlistteacher as $teacher) {
                // We have list of teacher. Now retrieve all courses for each one.

                $sqllistcoursesforteacher = 'SELECT DISTINCT(c.id) AS courseid, c.fullname AS coursename
                FROM {course} c
                LEFT JOIN {context} ctx ON c.id = ctx.instanceid
                JOIN {role_assignments} lra ON lra.contextid = ctx.id
                JOIN {role_assignments} tra ON tra.contextid = ctx.id
                JOIN {user} u ON u.id=tra.userid
                WHERE tra.roleid = '.$teacherrole .'
                AND u.id = '.$teacher->idteacher.'
                ';
                $dbresultlistcoursesforteacher = $DB->get_records_sql($sqllistcoursesforteacher);

                $mailcontent = '';

                // Define list of reports and their titles in an object for loop.
                $allreports = new \stdClass();
                $allreports = [
                    ['report' => 'empty',
                        'string' => get_string('no_content', 'report_coursemanager'),
                        'desc' => get_string('mailingddescreportempty', 'report_coursemanager'),
                    ],
                    ['report' => 'no_visit_student',
                        'string' => get_string('no_visit_student', 'report_coursemanager'),
                        'desc' => get_string('mailingddescreportnovisitstudent', 'report_coursemanager'),
                    ],
                    ['report' => 'no_student',
                        'string' => get_string('no_student', 'report_coursemanager'),
                        'desc' => get_string('mailingddescreportnostudent', 'report_coursemanager'),
                    ],
                    ['report' => 'no_visit_teacher',
                        'string' => get_string('no_visit_teacher', 'report_coursemanager'),
                        'desc' => get_string('mailingddescreportnovisitteacher', 'report_coursemanager'),
                    ],
                    ['report' => 'heavy',
                        'string' => get_string('heavy_course', 'report_coursemanager'),
                        'desc' => get_string('mailingddescreportheavy', 'report_coursemanager'),
                    ],
                    ['report' => 'orphan_submissions',
                        'string' => get_string('orphan_submissions_button', 'report_coursemanager'),
                        'desc' => get_string('mailingddescreportorphansubmissions', 'report_coursemanager'),
                    ],
                ];
                foreach ($allreports as $report) {
                    // Initiallize report content.
                    $reportresult = '';
                    // For each report, we test each course for a teacher.
                    foreach ($dbresultlistcoursesforteacher as $listcourse) {
                        // If a report exists for a course, add course name to the list with direct link.
                        if ($report['report'] == 'orphan_submissions') {
                            $checkreport = $DB->get_records('report_coursemanager_orphans',
                            ['course' => $listcourse->courseid]);
                        } else {
                            $checkreport = $DB->get_record('report_coursemanager_reports',
                            ['course' => $listcourse->courseid, 'report' => $report['report']]);
                        }

                        if (!empty($checkreport)) {
                            // Heavy report leads to the specific page about course files.
                            if ($report['report'] == 'heavy') {
                                $reportresult .= '- <a href="'.$CFG->wwwroot
                                .'/report/coursemanager/course_files.php?courseid='
                                .$listcourse->courseid.'">'.$listcourse->coursename.'</a><br />';
                            } else {
                                $reportresult .= '- <a href="'.$CFG->wwwroot.'/course/view.php?id='
                                .$listcourse->courseid.'">'.$listcourse->coursename.'</a><br />';
                            }
                        }
                    }
                    // If courses are concerned by this report, add report as title and concat course list.
                    if (!empty($reportresult)) {
                        // For each final report, add title, description .
                        $mailcontent .= '<h3>'.$report['string'].' </h3>';
                        $mailcontent .= '<p>'.$report['desc'].'</p>';
                        $mailcontent .= $reportresult;
                        unset($reportresult);
                    }
                }
                // End loop.

                // Mail is sent only if there are reports for a teacher.
                if (!empty($mailcontent)) {
                    // Initialize final content.
                    $finalcontent = '';
                    $teacheruserinfo = \core_user::get_user($teacher->idteacher);
                    $a = new \stdClass;
                    $a->no_student_time = get_config('report_coursemanager', 'last_access_student');
                    $a->no_teacher_time = get_config('report_coursemanager', 'last_access_teacher');
                    $mailingintroduction = str_replace(
                        ['%coursemanagerlink%', '%userfirstname%', '%userlastname%'],
                        [$CFG->wwwroot.'/report/coursemanager/view.php', $teacheruserinfo->firstname, $teacheruserinfo->lastname],
                        get_config('report_coursemanager', 'mailing_introduction')
                    );

                    $finalcontent .= $mailingintroduction;
                    $finalcontent .= $mailcontent;
                    $finalcontent .= get_string('mailingoutro', 'report_coursemanager');

                    $send = email_to_user($teacheruserinfo, $from,
                    get_string('mailingtitle', 'report_coursemanager'), $finalcontent);

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
