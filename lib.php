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
 * Library for Lyon 3 template and Teacher Courses Dashboard.
 *
 * @package    report_coursemanager
 * @copyright  2022 Olivier VALENTIN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");

global $COURSE, $OUTPUT, $PAGE, $CFG;

function report_coursemanager_get_main_scss_content($theme) {
    global $CFG;

    $scss = '';
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;
    $fs = get_file_storage();

    $context = context_system::instance();
    if ($filename == 'default.scss') {
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    } else if ($filename == 'plain.scss') {
        // We still load the default preset files directly from the boost theme. No sense in duplicating them.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/plain.scss');
    } else if ($filename && ($presetfile = $fs->get_file($context->id, 'report_coursemanager', 'preset', 0, '/', $filename))) {
        // This preset file was fetched from the file area for report_coursemanager and not theme_boost (see the line above).
        $scss .= $presetfile->get_content();
    } else {
        // Safety fallback - maybe new installs etc.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    }
    // Pre CSS - this is loaded AFTER any prescss from the setting but before the main scss.
    $pre = file_get_contents($CFG->dirroot . '/theme/coursemanager/scss/pre.scss');
    // Post CSS - this is loaded AFTER the main scss but before the extra scss from the setting.
    $post = file_get_contents($CFG->dirroot . '/theme/coursemanager/scss/post.scss');

    // Combine them together.
    return $pre . "\n" . $scss . "\n" . $post;
}

function theme_navcoursemanager_extend_navigation(global_navigation $root) {
    $node = navigation_node::create(
        'Coucou',
        new moodle_url('/local/greetings/index.php'),
        navigation_node::TYPE_CUSTOM,
        null,
        null,
        new pix_icon('t/message', '')
    );
    $node->showinflatnavigation = true;

    $root->add_node($node);
}

/**
 * Get comments for assign files.
 *
 * @param int $courseid Course ID.
 * @return array.
 */

function report_coursemanager_get_assign_comment($courseid) {
    global $DB, $CFG, $OUTPUT;

    // Retrieve course information.
    $course = $DB->get_record('course', array('id' => $courseid));
    $modinfo = get_fast_modinfo($course);

    // Check heavy files and orphans files in assign.

    // FIRST TEST : heavy files.
    // $total_files_dev = 0;
    // $total_files_numer = 0;

    // If there are assign in course, check files infos..
    foreach ($modinfo->get_instances_of('assign') as $assign_id => $cm_info) {
    // Start count of total file sizes for an assign.
        $total_assign_size = 0;
        // Retrieve informations for this instance.
        $cm = get_coursemodule_from_instance('assign', $assign_id);
        $context = context_module::instance($cm->id);
        // retrieve all submissions for this instance.
        $fsdev = get_file_storage();
        $filesdev = $fsdev->get_area_files($context->id, 'assignsubmission_file', 'submission_files');
        
        // Start count of total files for an assign.
        $total_assign_count_files = 0;
        // For each submission, check size to add it at total files size.
        foreach ($filesdev as $f) {
            if ($f->get_filesize() > 0) {
                $sizedev =  $f->get_filesize();
                $total_assign_size += $sizedev;
                $total_assign_count_files = $total_assign_count_files+1;
            }
        }
        
        // Define the return counts.
        $assign_count_files_return += $total_assign_count_files;
        $assign_files_size_return += $total_assign_size;
        // Total files size rounded in Mo.
        $rounded_assign_size = number_format(ceil($total_assign_size / 1048576));

        // Create new object to stock heavy files information.
        $heavy_assigns = array();
        
        // If file crosses limit, add information.
        if ($rounded_assign_size > get_config('report_coursemanager', 'unique_filesize_threshold')) {
            $assign_to_empty[] = (array ('weight' => $rounded_assign_size, 'name' =>$cm->name));
        }
        $heavy_assigns = (array)$assign_to_empty;
        }
        $comment = '';
        // If at least one heavy file, show warning.
        if (count($heavy_assigns) > 0) {
            $comment .= "<span class='text-danger'>".get_string('warn_heavy_assign', 'report_coursemanager')."</span>"  .  $OUTPUT->help_icon('warn_heavy_assign', 'report_coursemanager', '');
            $comment .= "<ul>";
            
            // For each heavy file, shox name and size.
            foreach ($heavy_assigns as $heavy_assign){
                $comment .= "<li>". $heavy_assign['name'] ." (".$heavy_assign['weight']."Mo)</li>";
                }
            $comment .= "</ul>";
        }
    
    // SECOND TEST : orphan submissions.
    // This check for submissions where student is not enrolled in course. 
    $sql_assigns_orphans = 'SELECT DISTINCT(a.name) AS assign
    FROM
        {files} AS f, 
        {assignsubmission_file} AS asf, 
        {assign} AS a, 
        {user} AS u, 
        {course} AS c,
        {course_modules} AS cm
    WHERE 
       component = "assignsubmission_file"
        AND asf.submission=f.itemid
        AND a.id = asf.assignment
        AND f.userid = u.id
        AND filename != "."
        AND c.id = a.course
        AND c.id = ?
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
    ';
    $params_db_assigns_orphans = array($courseid);
    $db_result_assigns_orphans = $DB->get_records_sql($sql_assigns_orphans, $params_db_assigns_orphans);

    // If at least one result, add warning and show orphan submissions.
    if (count($db_result_assigns_orphans) > 0) {        
        $comment .= "<span class='text-danger'>".get_string('warn_orphans', 'report_coursemanager')." </span>" .  $OUTPUT->help_icon('warn_orphans', 'report_coursemanager', '');
        $comment .= "<ul>";
        foreach ($db_result_assigns_orphans as $db_result_assign_orphans){
            $comment .= "<li>". $db_result_assign_orphans->assign ."</li>";
            }
        $comment .= "</ul>";
    } 

    // Return all comments, total files size and total number of files. 
    $all = array($comment, $assign_files_size_return, $assign_count_files_return);
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
    $course = $DB->get_record('course', array('id' => $courseid));
    $modinfo = get_fast_modinfo($course);

    // For each component, check files.
    foreach ($modinfo->get_instances_of($component) as $resource_id => $cm_info) {
        $cm = get_coursemodule_from_instance($component, $resource_id);
        $contextres = context_module::instance($cm->id);
        $fsres = get_file_storage();
        $filesrev = $fsres->get_area_files($contextres->id, 'mod_'.$component, $filearea);

        $heavy_files = array();
        $videos = array();
        $heavy_file = array();
        $video = array();

        // For each file, check MIME type and size.
        foreach ($filesrev as $f) {

            // We remove files starting by "_s" and files with no size.
            if(substr($f->get_filename(), 0, 2) !== "s_" && $f->get_filesize() > 0) {
                // Size is rounded in Mo.
                $weight = number_format(ceil($f->get_filesize() / 1048576));

                // If file is a video AND exceeds file limit, add warning about Web TV. 
                if(strpos($f->get_mimetype(), 'video') !== false && $weight >= get_config('report_coursemanager', 'unique_filesize_threshold')) {
                    $video[] = (array ('weight' => $weight, 'name' =>$f->get_filename()));
                }
                // If file is no video, just add warning about size.
                else if ($weight > get_config('report_coursemanager', 'unique_filesize_threshold')) {
                    $heavy_file[] = (array ('weight' => $weight, 'name' =>$f->get_filename()));
                    }
                $videos = (array)$video;
                $heavy_files = (array)$heavy_file;
                }
            }
        }
    
    // Trigger comments to return.

    // If there are videos, add warning.
    $comment = '';
// echo "video ".count($videos);
    if (count($videos) > 0) {
        $comment .= "<span class='text-danger'>".get_string('warn_videos', 'report_coursemanager')." </span>" .  $OUTPUT->help_icon('warn_videos', 'report_coursemanager', '') ; 
        $comment .= "<ul>";
        
        // For each video, show name and size.
        foreach ($videos as $video){
            $comment .= "<li>". $video['name'] ." (".$video['weight']."Mo)</li>";
            }
        $comment .= "</ul>";
    }

    // If heavy files detected, add warning.
    if (count($heavy_files) > 0) {
        $comment .= "<span class='text-danger'>".get_string('warn_big_files', 'report_coursemanager')." </span>" .  $OUTPUT->help_icon('warn_big_files', 'report_coursemanager', '') ; 
        $comment .= "<ul>";
        
        // For each heavy file, show name and size.
        foreach ($heavy_files as $heavy_file){
            $comment .= "<li>". $heavy_file['name'] ." (".$heavy_file['weight']."Mo)</li>";
            }
        $comment .= "</ul>";
    }
    $all = array($comment);
    return $all;
}

class form_restore extends moodleform {
   /**
   * Form definition for course restore.
   *
   * @return void
   */
    public function definition() {
        global $CFG, $USER, $DB;
        $mform = $this->_form;
        $datas = $this->_customdata['post'];
        
        $displaylist = core_course_category::make_categories_list();
        $mform->addElement('select', 'restore_category', get_string('select_restore_category', 'report_coursemanager'), $displaylist);
        
        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setDefault('courseid', $datas->courseid);
        $this->add_action_buttons(true, get_string('button_restore_confirm', 'report_coursemanager'));
    }

    /**
     * Form validation.
     *
     * @param array $data  data from the form.
     * @param array $files files uplaoded.
     *
     * @return array of errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (empty($data['restore_category'])) {
            $errors['message'] = get_string('error_category', 'report_coursemanager');
        }
        return $errors;
    }
}

// function report_coursemanager_insert_report() {

    // global $CFG, $PAGE, $OUTPUT, $DB, $SESSION;
    
    // if (!$PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)) {
        // return;
    // }
    
    // $output = '';
    
    // $output .= "<script type='text/javascript'>
// var div1 = document.createElement('div');
      // div1.innerHTML = 'hi there';
      // div1.style.left = '600px';
      // div1.style.position = 'absolute';
      // document.body.appendChild(div1);
// </script>
// <body onload='myFunction()'>
// ";
    // return $output;
// }

function report_coursemanager_before_standard_top_of_body_html() {

    global $DB, $PAGE, $USER;
    
    if (!$PAGE->url->compare(new moodle_url('/course/view.php'), URL_MATCH_BASE)) {
        return;
    }
    
    // If plugin param is set to show report, let's start.
    $coursecontext = context_course::instance($PAGE->course->id);
    $is_teacher = get_user_roles($coursecontext, $USER->id, false);
    $role = key($is_teacher);

    // If reports are shown AND user is a teacher, start to retrieve and show reports.
    if(get_config('report_coursemanager', 'show_report_in_course') != 0 && $is_teacher[$role]->roleid == 3) {
        // First, retrieve all reports for course.
        $all_reports = $DB->get_records('coursemanager', array('course' => $PAGE->course->id));
        // Create object to stock reports.
        $final = '';
        
        // Create an object for messages variables.
        $info = new stdClass();
        $info->courseid = $PAGE->course->id;
        $info->heavy_link = "<a href='/report/coursemanager/course_files.php?courseid=".$PAGE->course->id."' >".get_string('more_information', 'report_coursemanager')."</a>";
        $info->delete_link = "<a href='/report/coursemanager/delete_course.php?courseid=".$PAGE->course->id."' >".get_string('text_link_delete', 'report_coursemanager')."</a>";
        $info->reset_link = "<a href='/report/coursemanager/reset.php?id=".$PAGE->course->id."' >".get_string('text_link_reset', 'report_coursemanager')."</a>";
        $info->no_teacher_time = get_config('report_coursemanager', 'last_access_teacher');
        $info->no_student_time = get_config('report_coursemanager', 'last_access_student');
        
        // If reports are shown in course with collapse menu under admin nav.
        $output = '';
        if(!empty($all_reports)){
            
            if(get_config('report_coursemanager', 'show_report_in_course') == 1) {
                // For each report, create <li> with text and links.
                foreach ($all_reports as $report){
                    switch($report->report) {
                        case $report->report = 'heavy':
                            $info->size = $report->detail;
                            $final .= "<li><i class='fa fa-thermometer-three-quarters text-danger fa-lg'></i>  ".get_string('course_alert_heavy', 'report_coursemanager', $info)."</li>";
                            break;
                        case $report->report = 'no_visit_teacher':
                            $final .= "<li><i class='fa fa-graduation-cap text-info fa-lg'></i>  ".get_string('course_alert_no_visit_teacher', 'report_coursemanager', $info)."</li>";
                            break;
                        case $report->report = 'no_visit_student':
                            $final .= "<li><i class='fa fa-group text-info fa-lg'></i>  ".get_string('course_alert_no_visit_student', 'report_coursemanager', $info)."</li>";
                            break;
                        case $report->report = 'no_student':
                            $final .= "<li><i class='fa fa-user-o fa-lg text-warning'></i>  ".get_string('course_alert_no_student', 'report_coursemanager', $info)."</li>";
                            break;
                        case $report->report = 'empty':
                            $final .= "<li><i class='fa fa-battery-empty fa-lg text-dark'></i>  ".get_string('course_alert_empty', 'report_coursemanager', $info)."</li>";
                            break;
                    }
                }
                
                // Generate HTML for collapse button and create
                $button = '<button id=\"collapse_report\" class=\"btn btn-primary collasped\" data-toggle=\"collapse\" data-target=\"#reports_zone\">'.get_string('collapse_show_report', 'report_coursemanager').'</button><div id=\"reports_zone\" class=\"collapse alert alert-warning\"><ul>'.$final.'</ul></div>';
                
                // JS function to push a div under admin nav with 
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
                $output .=  $PAGE->requires->js_amd_inline($js);
            } elseif(get_config('report_coursemanager', 'show_report_in_course') == 2) {
                // If reports are shown with popover icons next to course title.    
                foreach ($all_reports as $report){
                    switch($report->report) {
                        case $report->report = 'heavy':
                            $info->size = $report->detail;
                            $final .= '<li><button type=\"button\" class=\"reportbutton bg-danger heavy\" data-html=\"true\" data-toggle=\"popover\" data-placement=\"bottom\" title=\"'.get_string('heavy_course', 'report_coursemanager').'\" data-content=\"'.get_string('course_alert_heavy', 'report_coursemanager', $info).'\"><i class=\"fa fa-thermometer-three-quarters\"></i></button></li>';
                            break;
                        case $report->report = 'no_visit_teacher':
                            $final .= '<li><button type=\"button\" class=\"reportbutton bg-info no_visit_teacher\" data-html=\"true\" data-toggle=\"popover\" data-placement=\"bottom\" title=\"'.get_string('no_visit_teacher', 'report_coursemanager').'\" data-content=\"'.get_string('course_alert_no_visit_teacher', 'report_coursemanager', $info).'\"><i class=\"fa fa-graduation-cap\"></i></button></li>';
                            break;
                        case $report->report = 'no_visit_student':
                            $final .= '<li><button type=\"button\" class=\"reportbutton bg-info no_visit_student\" data-html=\"true\" data-toggle=\"popover\" data-placement=\"bottom\" title=\"'.get_string('no_visit_student', 'report_coursemanager').'\" data-content=\"'.get_string('course_alert_no_visit_student', 'report_coursemanager', $info).'\"><i class=\"fa fa-group\"></i></button></li>';
                            break;
                        case $report->report = 'no_student':
                            $final .= '<li><button type=\"button\" class=\"reportbutton bg-warning no_student\" data-html=\"true\" data-toggle=\"popover\" data-placement=\"bottom\" title=\"'.get_string('no_student', 'report_coursemanager').'\" data-content=\"'.get_string('course_alert_no_student', 'report_coursemanager', $info).'\"><i class=\"fa fa-user-o\"></i></button></li>';
                            break;
                        case $report->report = 'empty':
                            $final .= '<li><button type=\"button\" class=\"reportbutton bg-dark empty\" data-html=\"true\" data-toggle=\"popover\" data-placement=\"bottom\" title=\"'.get_string('no_content', 'report_coursemanager').'\" data-content=\"'.get_string('course_alert_empty', 'report_coursemanager', $info).'\"><i class=\"fa fa-battery-empty\"></i></button></li>';
                            break;
                    }
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
                $output .=  $PAGE->requires->js_amd_inline($js);
            }
        }
        return $output;
    }
}
