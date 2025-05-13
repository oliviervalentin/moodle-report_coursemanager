<?php
// This file is part of Moodle - http://moodle.org/
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
 * Hook callbacks for Course Manager
 *
 * @package     report_coursemanager
 * @copyright   2022 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursemanager\output;

defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->dirroot . '/report/coursemanager/lib.php');

// use mod_quiz\question\bank\qbank_helper;
use core\hook\output\before_standard_top_of_body_html_generation;
// use plagiarism_compilatio\compilatio\api;
// use plagiarism_compilatio\compilatio\csv_generator;
// use plagiarism_compilatio\output\statistics;
// use plagiarism_compilatio\output\icons;
// use plagiarism_compilatio\compilatio\analysis;
use moodle_url;

/**
 * display_reports class
 */
class display_reports {

    /**
     * Hook callback to insert a chunk of html at the start of the html document.
     * This allow us to display the Compilatio frame with statistics, alerts,
     * author search tool and buttons to launch all analyses and update submitted files status.
     *
     * @param before_standard_top_of_body_html_generation $hook
     */
    public static function before_standard_top_of_body_html_generation(before_standard_top_of_body_html_generation $hook): void {

        global $SESSION;

        $hook->add_html(self::get_frame());
    }

    /**
     * Display Course Manager reports in course
     * @return string Return the HTML formatted string.
     */
    public static function get_frame() {
        // $output = "<div>COUCOU</div>";
        // return $output;
        global $DB, $PAGE, $USER;

    if ($PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)) {
        // If plugin param is set to show report, let's start.
        $coursecontext = \context_course::instance($PAGE->course->id);
        $displayreportteacher = 0;
        $isteacher = get_user_roles($coursecontext, $USER->id, false);

        // If user has teacher role as defined in plugin settings, set variable to display reports.
        if ($isteacher) {
            $role = key($isteacher);
            if ($isteacher[$role]->roleid == get_config('report_coursemanager', 'teacher_role_dashboard')) {
                $displayreportteacher = 1;
            }
        }

        // Check if reports are enabled, and check if user has variable or capability (admin) to see reports.
        if (get_config('report_coursemanager', 'show_report_in_course') != 0
        && ($displayreportteacher == 1 || has_capability('report/coursemanager:viewreport', $coursecontext))
        ) {
            // If course is in trash category, add warning.
            if ($PAGE->category->id == get_config('report_coursemanager', 'category_bin')) {
                $output = '';
                // JS function to push a div under admin nav.
                $warntextcoursetrash = get_string('warntextcoursetrash', 'report_coursemanager');
                $js = 'function warnCourseInTrash() {
                    var container = document.getElementById("user-notifications");
                    var warndiv_coursemanager = document.createElement("div");
                    warndiv_coursemanager.id = "coursemanager_coursetrashwarn";
                    warndiv_coursemanager.className = "alert alert-warning";
                    warndiv_coursemanager.innerHTML = "'.$warntextcoursetrash.'";
                    container.appendChild(warndiv_coursemanager);
                }
                warnCourseInTrash();
                ';
                $output .= $PAGE->requires->js_amd_inline($js);
                return $output;
            } else {

                // First, retrieve all reports for course.
                $allreports = $DB->get_records('report_coursemanager_reports', ['course' => $PAGE->course->id]);
                // Create object to stock reports.
                $final = '';
                
                // Create an object for messages variables.
                $info = new \stdClass();
                $info->courseid = $PAGE->course->id;
                $heavyurl = new moodle_url('/report/coursemanager/course_files.php', ['courseid' => $PAGE->course->id]);
                $info->heavy_link = "<a href='".$heavyurl."' >".
                get_string('more_information', 'report_coursemanager')."</a>";
                $deleteurl = new moodle_url('/report/coursemanager/delete_course.php', ['courseid' => $PAGE->course->id]);
                $info->delete_link = "<a href='".$deleteurl."' >".
                get_string('text_link_delete', 'report_coursemanager')."</a>";
                $reserturl = new moodle_url('/report/coursemanager/reset.php', ['courseid' => $PAGE->course->id]);
                $info->reset_link = "<a href='".$reserturl."' >".
                get_string('text_link_reset', 'report_coursemanager')."</a>";
                $info->no_teacher_time = get_config('report_coursemanager', 'last_access_teacher');
                $info->no_student_time = get_config('report_coursemanager', 'last_access_student');

                // If reports are shown in course with collapse menu under admin nav.
                $output = '';
                if (!empty($allreports)) {
                    if (get_config('report_coursemanager', 'show_report_in_course') == 1) {
                        // For each report, create <li> with text and links.
                        foreach ($allreports as $report) {
                            if ($report->report === "weight") {
                                $courseweight = $report->detail;
                                //break; // On sort de la boucle dès qu'on trouve la valeur
                            }
                            switch($report->report) {
                                case $report->report = 'heavy':
                                    $info->size = display_size($courseweight, 0, 'MB');
                                    $final .= "<li><i class='fa fa-thermometer-three-quarters text-danger fa-lg'></i>  ".
                                    get_string('course_alert_heavy', 'report_coursemanager', $info)."</li>";
                                    break;
                                case $report->report = 'no_visit_teacher':
                                    $final .= "<li><i class='fa fa-graduation-cap text-info fa-lg'></i>  ".
                                    get_string('course_alert_no_visit_teacher', 'report_coursemanager', $info)."</li>";
                                    break;
                                case $report->report = 'no_visit_student':
                                    $final .= "<li><i class='fa fa-group text-info fa-lg'></i>  ".
                                    get_string('course_alert_no_visit_student', 'report_coursemanager', $info)."</li>";
                                    break;
                                case $report->report = 'no_student':
                                    $final .= "<li><i class='fa fa-user-o fa-lg text-warning'></i>  ".
                                    get_string('course_alert_no_student', 'report_coursemanager', $info)."</li>";
                                    break;
                                case $report->report = 'empty':
                                    $final .= "<li><i class='fa fa-battery-empty fa-lg text-dark'></i>  ".
                                    get_string('course_alert_empty', 'report_coursemanager', $info)."</li>";
                                    break;
                            }
                        }

                        $reportsorphans = $DB->get_records('report_coursemanager_orphans', ['course' => $PAGE->course->id]);
                        if (!empty($reportsorphans)) {
                            $final .= "<li><i class='fa fa-files-o fa-lg text-danger'></i>  ".
                            get_string('course_alert_orphan_submissions', 'report_coursemanager', $info)."</li>";
                        }

                        // Generate HTML for collapse button and create.
                        $button = '<button id=\"coursemanager_collapse_report\" class=\"btn btn-primary collasped\" '
                        .'data-toggle=\"collapse\" data-target=\"#coursemanager_reports_zone\">'
                        .get_string('collapse_show_report', 'report_coursemanager')
                        .'</button><div id=\"coursemanager_reports_zone\" class=\"collapse alert alert-warning\"><ul>'
                        .$final.'</ul></div>';

                        // JS function to push a div under admin nav.
                        $js = 'function reportZone() {
                            var container = document.getElementById("user-notifications");
                            var button = document.createElement("div");
                            button.id = "coursemanager_collapse";
                            button.class = "collapse";
                            button.innerHTML = "'.$button.'";
                            container.appendChild(button);
                            document.addEventListener("DOMContentLoaded", function() {
                                var bouton = document.querySelector("#coursemanager_collapse button");
                                var collapse = new bootstrap.Collapse(document.querySelector("#coursemanager_collapse"));

                                bouton.addEventListener("click", function() {
                                    collapse.toggle();
                                });
                            });
                        }
                        reportZone();
                        ';
                        $output .= $PAGE->requires->js_amd_inline($js);
                    } else if (get_config('report_coursemanager', 'show_report_in_course') == 2) {
                        // If reports are shown with popover icons next to course title.
                        foreach ($allreports as $report) {
                            if ($report->report === "weight") {
                                $courseweight = $report->detail;
                                //break; // On sort de la boucle dès qu'on trouve la valeur
                            }
                            switch($report->report) {
                                case $report->report = 'heavy':
                                    $info->size = display_size($courseweight, 0, 'MB');
                                    $final .= '<li><button type=\"button\" '
                                    .'class=\"report_coursemanager-reportbutton bg-danger heavy\" '
                                    .'data-html=\"true\" data-toggle=\"popover\" data-placement=\"bottom\" '
                                    .'title=\"'.get_string('heavy_course', 'report_coursemanager').'\" '
                                    .'data-content=\"'.get_string('course_alert_heavy', 'report_coursemanager', $info)
                                    .'\"><i class=\"fa fa-thermometer-three-quarters\"></i></button></li>';
                                    break;
                                case $report->report = 'no_visit_teacher':
                                    $final .= '<li><button type=\"button\" '
                                    .'class=\"report_coursemanager-reportbutton bg-info no_visit_teacher\" '
                                    .'data-html=\"true\" data-toggle=\"popover\" data-placement=\"bottom\" '
                                    .'title=\"'.get_string('no_visit_teacher', 'report_coursemanager')
                                    .'\" data-content=\"'.get_string('course_alert_no_visit_teacher', 'report_coursemanager', $info)
                                    .'\"><i class=\"fa fa-graduation-cap\"></i></button></li>';
                                    break;
                                case $report->report = 'no_visit_student':
                                    $final .= '<li><button type=\"button\" '
                                    .'class=\"report_coursemanager-reportbutton bg-info no_visit_student\" '
                                    .'data-html=\"true\" data-toggle=\"popover\" data-placement=\"bottom\" '
                                    .'title=\"'.get_string('no_visit_student', 'report_coursemanager')
                                    .'\" data-content=\"'.get_string('course_alert_no_visit_student', 'report_coursemanager', $info)
                                    .'\"><i class=\"fa fa-group\"></i></button></li>';
                                    break;
                                case $report->report = 'no_student':
                                    $final .= '<li><button type=\"button\" '
                                    .'class=\"report_coursemanager-reportbutton bg-warning no_student\" '
                                    .'data-html=\"true\" data-toggle=\"popover\" data-placement=\"bottom\" '
                                    .'title=\"'.get_string('no_student', 'report_coursemanager')
                                    .'\" data-content=\"'.get_string('course_alert_no_student', 'report_coursemanager', $info)
                                    .'\"><i class=\"fa fa-user-o\"></i></button></li>';
                                    break;
                                case $report->report = 'empty':
                                    $final .= '<li><button type=\"button\" '
                                    .'class=\"report_coursemanager-reportbutton bg-dark empty\" '
                                    .'data-html=\"true\" data-toggle=\"popover\" data-placement=\"bottom\" '
                                    .'title=\"'.get_string('no_content', 'report_coursemanager')
                                    .'\" data-content=\"'.get_string('course_alert_empty', 'report_coursemanager', $info)
                                    .'\"><i class=\"fa fa-battery-empty\"></i></button></li>';
                                    break;
                            }
                        }

                        $reportsorphans = $DB->get_records('report_coursemanager_orphans', ['course' => $PAGE->course->id]);
                        if (!empty($reportsorphans)) {
                            $final .= '<li><button type=\"button\" '
                                    .'class=\"report_coursemanager-reportbutton bg-danger orphan_submissions\" '
                                    .'data-html=\"true\" data-toggle=\"popover\" data-placement=\"bottom\" '
                                    .'title=\"'.get_string('orphan_submissions_button', 'report_coursemanager')
                                    .'\" data-content=\"'
                                    .get_string('course_alert_orphan_submissions', 'report_coursemanager', $info)
                                    .'\"><i class=\"fa fa-files-o\"></i></button></li>';
                        }

                        $js = 'function reportZone() {
                            var container = document.querySelector(".page-context-header");
                            var button = document.createElement("div");
                            button.id = "coursemanager_popover";
                            container.appendChild(button);
                            var list = document.createElement("ul");
                            list.innerHTML = "'.$final.'";
                            list.id = "coursemanagerbuttons";
                            button.appendChild(list);
                            document.addEventListener("DOMContentLoaded", function() {
                                var popoverTrigger = document.querySelector(\'[data-toggle="popover"]\');
                                var popover = new bootstrap.Popover(popoverTrigger);
                            });
                        }
                        reportZone();
                        ';
                        $output .= $PAGE->requires->js_amd_inline($js);
                    }
                }
                return $output;
            }
        }
    } else if ($PAGE->url->compare(new moodle_url('/course/index.php?categoryid='
    .get_config('report_coursemanager', 'category_bin')), URL_MATCH_EXACT)) {
        $output = '';
        $warntextcategorytrash = get_string('warntextcategorytrash', 'report_coursemanager');
        // JS function to push a div under admin nav.
        $js = 'function warnCategoryTrash() {
            var container = document.getElementById("user-notifications");
            var warndiv_coursemanager = document.createElement("div");
            warndiv_coursemanager.id = "coursemanager_trash_warn";
            warndiv_coursemanager.className = "alert alert-warning";
            warndiv_coursemanager.innerHTML = "'.$warntextcategorytrash.'";
            container.appendChild(warndiv_coursemanager);
        }
        warnCategoryTrash();
        ';
        $output .= $PAGE->requires->js_amd_inline($js);
        return $output;
    } else {
        return;
    }
    }
}
