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
 * Library for Course Manager report.
 *
 * @package    report_coursemanager
 * @copyright  2022 Olivier VALENTIN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");

global $COURSE, $OUTPUT, $PAGE, $CFG;

/**
 * Get comments for assign files.
 *
 * @param int $courseid Course ID.
 * @return array.
 */
function report_coursemanager_get_assign_comment($courseid) {
    global $DB, $CFG, $OUTPUT;

    // Retrieve course information.
    $course = $DB->get_record('course', ['id' => $courseid]);
    $modinfo = get_fast_modinfo($course);

    // Check heavy files and orphans files in assign activity.

    // FIRST TEST : heavy files.
    // If there are assign in course, check files infos.
    foreach ($modinfo->get_instances_of('assign') as $assignid => $cminfo) {
        // Start count of total file sizes for an assign.
        $totalassignsize = 0;
        // Retrieve informations for this instance.
        $cm = get_coursemodule_from_instance('assign', $assignid);
        $context = context_module::instance($cm->id);
        // Retrieve all submissions for this instance.
        $fsdev = get_file_storage();
        $filesdev = $fsdev->get_area_files($context->id, 'assignsubmission_file', 'submission_files');

        // Start count of total files for an assign.
        $totalassigncountfiles = 0;
        // For each submission, check size to add it at total files size.
        foreach ($filesdev as $f) {
            if ($f->get_filesize() > 0) {
                $sizedev = $f->get_filesize();
                $totalassignsize += $sizedev;
                $totalassigncountfiles = $totalassigncountfiles + 1;
            }
        }

        // Define the return counts.
        $assigncountfilesreturn = $totalassigncountfiles;
        $assignfilessizereturn = $totalassignsize;
        // Total files size rounded in Mo.
        $roundedassignsize = number_format(ceil($totalassignsize / 1048576));

        // Create new object to stock heavy files information.
        $heavyassigns = [];

        // If file crosses limit, add information.
        if ($roundedassignsize > get_config('report_coursemanager', 'unique_filesize_threshold')) {
            $assigntoempty[] = (['weight' => $roundedassignsize, 'name' => $cm->name]);
        }
        $heavyassigns = (array)$assigntoempty;
    }
    $comment = '';

    // If at least one heavy file, show warning.
    if (count($heavyassigns) > 0) {
        $comment .= "<span class='text-danger'>".get_string('warn_heavy_assign', 'report_coursemanager')."</span>"
        .$OUTPUT->help_icon('warn_heavy_assign', 'report_coursemanager', '');
        $comment .= "<ul>";

        // For each heavy file, shox name and size.
        foreach ($heavyassigns as $heavyassign) {
            $comment .= "<li>". $heavyassign['name'] ." (".$heavyassign['weight']."Mo)</li>";
        }
        $comment .= "</ul>";
    }

    // SECOND TEST : orphan submissions.
    // This check for submissions where student is not enrolled in course.
    $sqlassignsorphans = 'SELECT DISTINCT(a.name) AS assign
    FROM
        {files} f,
        {assignsubmission_file} asf,
        {assign} a,
        {user} u,
        {course} c,
        {course_modules} cm
    WHERE
       component = "assignsubmission_file"
        AND asf.submission=f.itemid
        AND a.id = asf.assignment
        AND f.userid = u.id
        AND filename != \'.\'
        AND c.id = a.course
        AND c.id = ?
        AND a.id = cm.instance
        AND u.id  NOT IN
            (SELECT us.id
            FROM {course} course,
                {enrol} en,
                {user_enrolments} ue,
                {user} us
            WHERE c.id=course.id
                AND en.courseid = course.id
                AND ue.enrolid = en.id
                AND us.id = ue.userid
            )
    ';
    $paramsdbassignsorphans = [$courseid];
    $dbresultassignsorphans = $DB->get_records_sql($sqlassignsorphans, $paramsdbassignsorphans);

    // If at least one result, add warning and show orphan submissions.
    if (count($dbresultassignsorphans) > 0) {
        $comment .= "<span class='text-danger'>".get_string('warn_orphans', 'report_coursemanager')." </span>"
        .$OUTPUT->help_icon('warn_orphans', 'report_coursemanager', '');
        $comment .= "<ul>";
        foreach ($dbresultassignsorphans as $dbresultassignorphans) {
            $comment .= "<li>". $dbresultassignorphans->assign ."</li>";
        }
        $comment .= "</ul>";
    }

    // Return all comments, total files size and total number of files.
    $all = [$comment, $assignfilessizereturn, $assigncountfilesreturn];
    return $all;
}

/**
 * Get comments about heavy files for these specific activities :
 * label, forum, resource, folder.
 *
 * @param string $component The name of the component.
 * @param int $courseid Course ID.
 * @param string $filearea The name of the file area.
 * @return array.
 */
function report_coursemanager_get_files_comment($component, $courseid, $filearea) {
    global $DB, $CFG, $OUTPUT;

    // Check course information.
    $course = $DB->get_record('course', ['id' => $courseid]);
    $modinfo = get_fast_modinfo($course);

    // For each component, check files.
    foreach ($modinfo->get_instances_of($component) as $resourceid => $cminfo) {
        $cm = get_coursemodule_from_instance($component, $resourceid);
        $contextres = context_module::instance($cm->id);
        $fsres = get_file_storage();
        $filesrev = $fsres->get_area_files($contextres->id, 'mod_'.$component, $filearea);

        $heavyfiles = [];
        $videos = [];
        $heavyfile = [];
        $video = [];

        // For each file, check MIME type and size.
        foreach ($filesrev as $f) {

            // We remove files starting by "_s" and files with no size.
            if (substr($f->get_filename(), 0, 2) !== "s_" && $f->get_filesize() > 0) {
                // Size is rounded in Mo.
                $weight = number_format(ceil($f->get_filesize() / 1048576));

                if (strpos($f->get_mimetype(), 'video') !== false && $weight >=
                get_config('report_coursemanager', 'unique_filesize_threshold')) {
                    // If file is a video AND exceeds file limit, add warning about Web TV.
                    $video[] = (['weight' => $weight, 'name' => $f->get_filename()]);
                } else if ($weight > get_config('report_coursemanager', 'unique_filesize_threshold')) {
                    // If file is no video, just add warning about size.
                    $heavyfile[] = (['weight' => $weight, 'name' => $f->get_filename()]);
                }
                $videos = (array)$video;
                $heavyfiles = (array)$heavyfile;
            }
        }
    }

    // Trigger comments to return.
    // If there are videos, add warning.
    $comment = '';
    if (count($videos) > 0) {
        $comment .= "<span class='text-danger'>".get_string('warn_videos', 'report_coursemanager')." </span>"
        .$OUTPUT->help_icon('warn_videos', 'report_coursemanager', '');
        $comment .= "<ul>";

        // For each video, show name and size.
        foreach ($videos as $video) {
            $comment .= "<li>". $video['name'] ." (".$video['weight']."Mo)</li>";
        }
        $comment .= "</ul>";
    }

    // If heavy files detected, add warning.
    if (count($heavyfiles) > 0) {
        $comment .= "<span class='text-danger'>".get_string('warn_big_files', 'report_coursemanager')." </span>"
        .$OUTPUT->help_icon('warn_big_files', 'report_coursemanager', '');
        $comment .= "<ul>";

        // For each heavy file, show name and size.
        foreach ($heavyfiles as $heavyfile) {
            $comment .= "<li>". $heavyfile['name'] ." (".$heavyfile['weight']."Mo)</li>";
        }
        $comment .= "</ul>";
    }
    $all = [$comment];
    return $all;
}

/**
 * Push DIV zone in course home page to show reports.
 *
 * @return string.
 */
function report_coursemanager_before_standard_top_of_body_html() {
    global $DB, $PAGE, $USER;

    if ($PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)) {
        // If plugin param is set to show report, let's start.
        $coursecontext = context_course::instance($PAGE->course->id);
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
                $info = new stdClass();
                $info->courseid = $PAGE->course->id;
                $info->heavy_link = "<a href='/report/coursemanager/course_files.php?courseid=".$PAGE->course->id."' >".
                get_string('more_information', 'report_coursemanager')."</a>";
                $info->delete_link = "<a href='/report/coursemanager/delete_course.php?courseid=".$PAGE->course->id."' >".
                get_string('text_link_delete', 'report_coursemanager')."</a>";
                $info->reset_link = "<a href='/report/coursemanager/reset.php?id=".$PAGE->course->id."' >".
                get_string('text_link_reset', 'report_coursemanager')."</a>";
                $info->no_teacher_time = get_config('report_coursemanager', 'last_access_teacher');
                $info->no_student_time = get_config('report_coursemanager', 'last_access_student');

                // If reports are shown in course with collapse menu under admin nav.
                $output = '';
                if (!empty($allreports)) {
                    if (get_config('report_coursemanager', 'show_report_in_course') == 1) {
                        // For each report, create <li> with text and links.
                        foreach ($allreports as $report) {
                            switch($report->report) {
                                case $report->report = 'heavy':
                                    $info->size = $report->detail;
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
                            switch($report->report) {
                                case $report->report = 'heavy':
                                    $info->size = $report->detail;
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
