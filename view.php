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
 * View tab of all courses per teachers.
 *
 * @package     report_coursemanager
 * @copyright   2022 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require_once(__DIR__ . '/../../config.php');
global $DB, $USER;

require_login();

$courseid = optional_param('courseid', 0, PARAM_INT);
$done = optional_param('done', 0, PARAM_RAW);

$site = get_site();

$PAGE = new moodle_page();
$PAGE->requires->js( new moodle_url($CFG->wwwroot . '/report/coursemanager/test.js'));
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('title', 'report_coursemanager'));
$PAGE->set_url('/report/coursemanager/view.php');
$PAGE->set_pagelayout('mycourses');
// $PAGE->set_secondary_navigation(false);

$PAGE->set_pagetype('teachertools');
$PAGE->blocks->add_region('content');
$PAGE->set_title($site->fullname);

// Force the add block out of the default area.
// $PAGE->theme->addblockposition  = BLOCK_ADDBLOCK_POSITION_CUSTOM;

// Defines date and hour.
$now = time();

echo $OUTPUT->header();

// Defines message to show after an action.
if ($done != '0') {
    switch ($done) {
        case 'cohort_deleted':
            $title_done = get_string('confirm_cohort_unenrolled_title', 'report_coursemanager');
            $text_done = get_string('confirm_cohort_unenrolled_message', 'report_coursemanager');
            break;
        
        case 'course_deleted':
            $title_done = get_string('confirm_course_deleted_title', 'report_coursemanager');
            $text_done = get_string('confirm_course_deleted_message', 'report_coursemanager');
            break;
            
        case 'course_restored':
            $title_done = get_string('confirm_course_restored_title', 'report_coursemanager');
            $text_done = get_string('confirm_course_restored_message', 'report_coursemanager');
            break;
            
        default:
            break;
    } 
    echo html_writer::div('
        <div class="alert alert-success alert-dismissible fade show" role="alert">
        <h4 class="alert-heading">'.$title_done.'</h4>
        <p>'.$text_done.'</p>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
        </button>
        </div>
        ');
}
// Empty action message variable.
unset($done);

// Buttons to filter lines.
print('
<h3>'.get_string('title', 'report_coursemanager').'</h3>
<input type="text" id="myInput" onkeyup="myFunction()" placeholder="'.get_string('text_filter', 'report_coursemanager').'">

<input type="radio" class="tablefilter" name="course_filter" id="filterrow" checked />
<label for="filterrow" class="btn btn-outline-primary"><i class=\'fa fa-lg fa-list\'></i> '.get_string('all_courses', 'report_coursemanager').'</label>
<input type="radio" class="tablefilter" name="course_filter" id="heavy-course" />
<label for="heavy-course" class="btn btn-outline-primary"><i class=\'fa fa-lg fa-thermometer-three-quarters text-danger\'></i> '.get_string('heavy_course', 'report_coursemanager').'</label>
<input type="radio" class="tablefilter" name="course_filter" id="no-visit-student" />
<label for="no-visit-student" class="btn btn-outline-primary"><i class=\'fa fa-lg fa-group text-info\'></i> '.get_string('no_visit_student', 'report_coursemanager').'</label>
<input type="radio" class="tablefilter" name="course_filter" id="no-visit-teacher" />
<label for="no-visit-teacher" class="btn btn-outline-primary"><i class=\'fa fa-lg fa-graduation-cap\'></i> '.get_string('no_visit_teacher', 'report_coursemanager').'</label>
<input type="radio" class="tablefilter" name="course_filter" id="no-student" />
<label for="no-student" class="btn btn-outline-primary"><i class=\'fa fa-lg fa-user-o\'></i> '.get_string('no_student', 'report_coursemanager').'</label>
<input type="radio" class="tablefilter" name="course_filter" id="no-content" />
<label for="no-content" class="btn btn-outline-primary"><i class=\'fa fa-lg fa-battery-empty text-danger\'></i> '.get_string('no_content', 'report_coursemanager').'</label>
<input type="radio" class="tablefilter" name="course_filter" id="orphan-submissions" />
<label for="orphan-submissions" class="btn btn-outline-primary"><i class=\'fa fa-lg fa-files-o text-danger\'></i> '.get_string('orphan_submissions_button', 'report_coursemanager').'</label>
<input type="radio" class="tablefilter" name="course_filter" id="ok" />
<label for="ok" class="btn btn-outline-primary"><i class=\'fa fa-lg fa-check-circle text-success\'></i> '.get_string('ok', 'report_coursemanager').'</label>
');

// First, retrieve all courses where user is enrolled.
$list_user_courses = enrol_get_users_courses($USER->id, false, '' , 'fullname ASC');

// If empty : user is not enrolled as teacher in any course. Show warning.
if(count($list_user_courses) == 0) {
    echo html_writer::div(get_string('no_course_to_show', 'report_coursemanager'), 'alert alert-primary');

// If user is enrolled in at least one course as teacher, let's start !.
} else {
    // Add a new table to display courses information.
    $count_courses = 0;
    $table = new html_table();
    $all_row_classes = '';
    $table->id = 'courses';

    $table->attributes['class'] = 'admintable generaltable browse_courses';
    $table->align = array('left', 'left', 'left', 'left', 'left', 'left', 'center', 'left');
    $table->head = array ();

    // Define headings for table.
    $table->head[] = get_string('table_course_name', 'report_coursemanager');
    $table->head[] = get_string('table_course_state', 'report_coursemanager');
    $table->head[] = get_string('table_files_weight', 'report_coursemanager');
    $table->head[] = get_string('table_enrolled_cohorts', 'report_coursemanager');
    $table->head[] = get_string('table_enrolled_students', 'report_coursemanager');
    $table->head[] = get_string('table_enrolled_teachers', 'report_coursemanager');
    $table->head[] = get_string('table_recommendation', 'report_coursemanager');;
    $table->head[] = get_string('table_actions', 'report_coursemanager');

    // Retrieve all informations for each courses where user is enrolled as teacher.
    foreach ($list_user_courses as $course) {
        // Get context and course infos.
        $coursecontext = context_course::instance($course->id);
        $infocourse = $DB->get_record('course', array('id' => $course->id), 'category');
        $is_teacher = get_user_roles($coursecontext, $USER->id, false);
        $role = key($is_teacher);

        // If user is enrolled as teacher in course.
        if ($is_teacher[$role]->roleid == get_config('report_coursemanager', 'teacher_role_dashboard')) {
            $count_courses = $count_courses+1;
            $all_row_classes = '';
            $data_keys = array();
            $string_keys = '';
                
            // Get enrol methods information.
            $instances = enrol_get_instances($course->id, false);
            $plugins   = enrol_get_plugins(false);

            // Start to count cohorts..
            $count_cohort = 0;
            // Get informations about cohort methods.
            foreach($instances as $instance){
                if ($instance->enrol == 'cohort') {
                    $plugin = $plugins[$instance->enrol];
                    $count_cohort = $count_cohort+1;
                }
            }

            // Let's count teachers and students enrolled in course.
            $all_teachers = get_role_users(get_config('report_coursemanager', 'teacher_role_dashboard'), $coursecontext);
            $all_students = get_role_users(get_config('report_coursemanager', 'student_role_report'), $coursecontext);    

            // Create a new line for table.
            // $row[] = new html_table_row();
            $row = array ();
            // Course name and direct link.
            $row[] = html_writer::link("/course/view.php?id=".$course->id, $course->fullname);

            // If course is in trash : informations are hidden.
            // Action menu has only one possibility : re-establish course out of trash category.
            if($infocourse->category == get_config('report_coursemanager', 'category_bin')) {
                // All lines are empty, except last one that displays menu.
                $row[] = html_writer::div('<i class="fa fa-trash"></i> '.get_string('course_state_trash', 'report_coursemanager'), 'course_trash');
                $row[] = html_writer::label('', null);
                $row[] = html_writer::label('', null);
                $row[] = html_writer::label('', null);
                $row[] = html_writer::label('', null);
                $row[] = html_writer::label('', null);
                $menu = '
                    <div class="dropdown show">
                        <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon fa fa-ellipsis-v fa-fw " ></i>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                            <a class="dropdown-item" href="restore_course.php?courseid='.$course->id.'">Rétablir le cours</a>
                        </div>
                    </div>
                ';
                $row[] = html_writer::div($menu, null);
            } else {
                // Course is not in trash, let's show all information.

                // First, define variables for showing reports in table.
                $sumup = '';
                $icons_sumup = '';
                $string_key = '';

                // Add line : visible or hidden course ?.
                if ($course->visible == 1) {
                    $row[] = html_writer::div('<i class="fa fa-eye"></i> '.get_string('course_state_visible', 'report_coursemanager'), 'course_visible');
                } else if ($course->visible == 0) {
                    $row[] = html_writer::div('<i class="fa fa-eye-slash"></i> '.get_string('course_state_hidden', 'report_coursemanager'), 'course_hidden');
                }

                // Retrieve course weight in table.
                $weight = $DB->get_record('coursemanager', array('course' => $course->id, 'report' => 'weight'));

                // Test with config variable "total_filesize_threshold" to define icon and text color.
                // If total size is null or less than 1 Mo, consider it empty.
                if (!$weight || $weight->detail == 0) {
                    $icon_size = 'fa fa-thermometer-empty';
                    $progress = 'text-info';
                } else if ($weight->detail <= (get_config('report_coursemanager', 'total_filesize_threshold'))) {
                    // If total size doesn't exceed threshold, green color.
                    $icon_size = 'fa fa-thermometer-quarter';
                    $progress = 'text-success';
                } else if ($weight->detail > (get_config('report_coursemanager', 'total_filesize_threshold'))) {
                    // If total size exceeds limit threshold, red color.
                    $icon_size = 'fa fa-thermometer-three-quarters';
                    $progress = 'text-danger';
                }

                // Create table line to show files size.
                $row[] = html_writer::link("course_files.php?courseid=".$course->id, 
                    '<i class="'.$icon_size.' fa-lg"></i>&nbsp;&nbsp;'.(!$weight ? '':$weight->detail).' Mo', array('class' => $progress));
                
                // Table line for number of cohorts.
                $row[] = html_writer::label($count_cohort, null);
                // Table line for number of students.
                $row[] = html_writer::label(count($all_students), null);
                // Table line for number of teachers.
                $row[] = html_writer::label(count($all_teachers), null);

                // Get all reports for table coursemanager for recommandations.
                $reports = $DB->get_records('coursemanager', array('course' => $course->id));
                foreach ($reports as $report) {
                    $info = new stdClass();
                    // Analysis : depending on each report, add a specific text with information if necessary.
                    switch ($report->report) {
                        case 'heavy':
                            // Get course id for direct link in text.
                            $info->courseid = $course->id;
                            $sumup .= "<li>".get_string('total_filesize_alert', 'report_coursemanager', $info)."</li><br />";
                            $icons_sumup .= "<i class='fa fa-lg fa-thermometer-three-quarters text-danger'></i>&nbsp;";
                            $all_row_classes .= 'heavy-course ';
                            break;
                        case 'no_visit_teacher':
                            $info->limit_visit = floor(get_config('report_coursemanager', 'last_access_teacher')/30);
                            // If there are more than one teach in course, add special message.
                            if (count($all_teachers) > 1) {    
                                $sumup .= "<li>".get_string('last_access_multiple_teacher_alert', 'report_coursemanager', $info)."</li><br />";
                            } else {
                                // If user is the only teacher in course, add message and last access.
                                $sumup .= "<li>".get_string('last_access_unique_teacher_alert', 'report_coursemanager', $info).".</li><br />";
                            }
                            $icons_sumup .= "<i class='fa fa-lg fa-graduation-cap'></i>&nbsp;";
                            $all_row_classes .= "no-visit-teacher ";
                            break;
                        case 'no_visit_student':
                            $info->limit_visit = floor(get_config('report_coursemanager', 'last_access_student')/30);                    
                            // Add warning about no visit for students.
                            $sumup .= "<li>".get_string('last_access_student_alert', 'report_coursemanager', $info).".</li>";
                            $icons_sumup .= "<i class='fa fa-lg fa-group text-info'></i>&nbsp;";
                            break;
                        case 'no_student':
                            $sumup .= "<li>".get_string('empty_student_alert', 'report_coursemanager').".</li>";
                            $icons_sumup .= "<i class='fa fa-lg fa-user-o text-info'></i>&nbsp;";
                            $all_row_classes .= "no-student ";
                            break;
                        case 'empty':
                            $sumup .= "<li>".get_string('empty_course_alert', 'report_coursemanager')."</li><br />";
                            $icons_sumup .= "<i class='fa fa-lg fa-battery-empty text-danger'></i>&nbsp;";
                            $all_row_classes .= "no-content ";
                            break;
                        case 'orphan_submissions':
                            $sumup .= "<li>".get_string('orphan_submissions_alert', 'report_coursemanager')."</li><br />";
                            $icons_sumup .= "<i class='fa fa-lg fa-files-o text-danger'></i>&nbsp;";
                            $all_row_classes .= "orphan-submissions ";
                            break;
                    }
                }
                // End of reports analysis.
   
                // If no specific recommendations, add a specific message.
                if (empty($sumup)) {
                    $sumup = "<p class='course_visible'><i class='fa fa-check'></i> ".get_string('no_advices', 'report_coursemanager')."</p>";
                    $icons_sumup .= "<i class='fa fa-lg fa-thumbs-up text-success'></i>";
                    $all_row_classes .= "ok ";
                }

                // Create button to open Modal containing all recommandations.
                $row[] = html_writer::label($icons_sumup."<br /><a class='badge badge-pill badge-light' href='#' data-toggle='modal' data-target='#exampleModal".$course->id."'>".get_string('see_advices', 'report_coursemanager')."</a>", null);

                // Code for Modal.
                echo html_writer::div('
                <div class="modal fade" id="exampleModal'.$course->id.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">'.get_string('advices_for_course', 'report_coursemanager').$course->fullname.'</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                          <ul>
                        '.$sumup.'
                          </ul>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer la fenêtre</button>
                      </div>
                    </div>
                  </div>
                </div>
                ');
                $pluginmanager = core_plugin_manager::instance();
                $plugininfo = $pluginmanager->get_plugin_info('enrol_scolarite');
                $enrolscolline = '';
                if ($plugininfo && $plugininfo->is_enabled()){
                    $enrolscolline = '<a class="dropdown-item" href="/enrol/scolarite/manage.php?id='.$course->id.'">'.get_string('menuenrolcohorts', 'report_coursemanager').'</a>';
                }

                // Create actions menu.
                $menu = '
                    <div class="dropdown show">
                        <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon fa fa-ellipsis-v fa-fw " ></i>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                            <a class="dropdown-item" href="delete_course.php?courseid='.$course->id.'">'.get_string('menudeletecourse', 'report_coursemanager').'</a>
                            <a class="dropdown-item" href="course_files.php?courseid='.$course->id.'">'.get_string('menucoursefilesinfo', 'report_coursemanager').'</a>
                            <a class="dropdown-item" href="reset.php?id='.$course->id.'">'.get_string('menureset', 'report_coursemanager').'</a>'
                            .$enrolscolline.'
                            <a class="dropdown-item" href="delete_cohort.php?id='.$course->id.'">'.get_string('menuunenrolcohorts', 'report_coursemanager').'</a>
                            <a class="dropdown-item" href="/course/edit.php?id='.$course->id.'">'.get_string('menucourseparameters', 'report_coursemanager').'</a>
                        </div>
                    </div>
                ';
                $row[] = html_writer::div($menu, null);
            }

            // All infos are set. Add line to table.
            $table->rowclasses[] = "filterrow ".$all_row_classes;
            $table->data[] = $row;
        }
    }
    // End : show all table.
    if ($count_courses > 0) {
        echo html_writer::table($table);
    } else {
        echo html_writer::div(get_string('no_course_to_show', 'report_coursemanager'), 'alert alert-primary');
    }
}

// Trigger event for viewing the Teacher Courses Dashboard.
$context = context_user::instance($USER->id);
$eventparams = array('context' => $context);
$event = \report_coursemanager\event\course_dashboard_viewed::create($eventparams);
$event->trigger();

unset($_GET);

echo $OUTPUT->footer();
