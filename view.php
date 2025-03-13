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
$done = optional_param('done', 0, PARAM_TEXT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);

$site = get_site();

$PAGE = new moodle_page();
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/report/coursemanager/search_courses.js'));
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('title', 'report_coursemanager'));
$PAGE->set_url('/report/coursemanager/view.php');
$PAGE->set_pagelayout('mycourses');

$PAGE->set_pagetype('report-coursemanager');
$PAGE->blocks->add_region('content');
$PAGE->set_title($site->fullname);

// Defines date and hour.
$now = time();

echo $OUTPUT->header();

// Defines message to show after an action.
if ($done != '0') {
    switch ($done) {
        case 'cohort_deleted':
            $titledone = get_string('confirm_cohort_unenrolled_title', 'report_coursemanager');
            $textdone = get_string('confirm_cohort_unenrolled_message', 'report_coursemanager');
            break;
        case 'course_deleted':
            $titledone = get_string('confirm_course_deleted_title', 'report_coursemanager');
            $textdone = get_string('confirm_course_deleted_message', 'report_coursemanager');
            break;
        case 'course_restored':
            $titledone = get_string('confirm_course_restored_title', 'report_coursemanager');
            $textdone = get_string('confirm_course_restored_message', 'report_coursemanager');
            break;
        default:
            break;
    }
    echo html_writer::div('
        <div class="alert alert-success alert-dismissible fade show" role="alert">
        <h4 class="alert-heading">'.$titledone.'</h4>
        <p>'.$textdone.'</p>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
        </button>
        </div>
        ');
}
// Empty action message variable.
unset($done);

if (!get_config('report_coursemanager', 'last_access_student') ||
    !get_config('report_coursemanager', 'last_access_teacher') ||
    !get_config('report_coursemanager', 'total_filesize_threshold') ||
    !get_config('report_coursemanager', 'unique_filesize_threshold')
) {
    echo html_writer::div(get_string('empty_settings', 'report_coursemanager'), 'alert alert-primary');
    echo $OUTPUT->footer();
    exit();
}

// Buttons to filter lines.<h3>'.get_string('title', 'report_coursemanager').'</h3>
print('
<input type="text" id="courseInput" onkeyup="searchCourses()" placeholder="'.get_string('text_filter', 'report_coursemanager').'">
<input type="radio" class="tablefilter" name="course_filter" id="filterrow" checked />
<label for="filterrow" class="btn btn-outline-primary"><i class=\'fa fa-lg fa-list\'></i>
'.get_string('all_courses', 'report_coursemanager').'</label>
<input type="radio" class="tablefilter" name="course_filter" id="heavy-course" />
<label for="heavy-course" class="btn btn-outline-primary"><i class=\'fa fa-lg fa-thermometer-three-quarters text-danger\'></i>
'.get_string('heavy_course', 'report_coursemanager').'</label>
<input type="radio" class="tablefilter" name="course_filter" id="no-visit-student" />
<label for="no-visit-student" class="btn btn-outline-primary"><i class=\'fa fa-lg fa-group text-info\'></i>
'.get_string('no_visit_student', 'report_coursemanager').'</label>
<input type="radio" class="tablefilter" name="course_filter" id="no-visit-teacher" />
<label for="no-visit-teacher" class="btn btn-outline-primary"><i class=\'fa fa-lg fa-graduation-cap\'></i>
'.get_string('no_visit_teacher', 'report_coursemanager').'</label>
<input type="radio" class="tablefilter" name="course_filter" id="no-student" />
<label for="no-student" class="btn btn-outline-primary"><i class=\'fa fa-lg fa-user-o\'></i>
'.get_string('no_student', 'report_coursemanager').'</label>
<input type="radio" class="tablefilter" name="course_filter" id="no-content" />
<label for="no-content" class="btn btn-outline-primary"><i class=\'fa fa-lg fa-battery-empty text-danger\'></i>
'.get_string('no_content', 'report_coursemanager').'</label>
<input type="radio" class="tablefilter" name="course_filter" id="orphan-submissions" />
<label for="orphan-submissions" class="btn btn-outline-primary"><i class=\'fa fa-lg fa-files-o text-danger\'></i>
'.get_string('orphan_submissions_button', 'report_coursemanager').'</label>
<input type="radio" class="tablefilter" name="course_filter" id="ok" />
<label for="ok" class="btn btn-outline-primary"><i class=\'fa fa-lg fa-check-circle text-success\'></i>
'.get_string('ok', 'report_coursemanager').'</label>
');

// First, retrieve all courses where user is enrolled.
$listusercourses = enrol_get_users_courses($USER->id, false, '' , 'fullname ASC');

// If empty : user is not enrolled as teacher in any course. Show warning.
if (count($listusercourses) == 0) {
    echo html_writer::div(get_string('no_course_to_show', 'report_coursemanager'), 'alert alert-primary');
} else {
    // If user is enrolled in at least one course as teacher, let's start !.
    // Add a new table to display courses information.
    $countcourses = 0;
    $table = new html_table();
    $allrowclasses = '';
    $table->id = 'courses';

    $table->attributes['class'] = 'admintable generaltable browse_courses';
    $table->align = ['left', 'left', 'left', 'left', 'left', 'left', 'center', 'left'];
    $table->head = [];

    // Calculates aggregation.
    $aggregations = calculate_aggregation_coursesize();
    $maxsize = max_size_course();

    // Define headings for table.
    $table->head[] = get_string('table_course_name', 'report_coursemanager');
    $table->head[] = get_string('table_course_state', 'report_coursemanager');
    if (get_config('report_coursemanager', 'enable_column_coursesize') == 1) {
        $table->head[] = get_string('table_files_weight', 'report_coursemanager');
    }
    if (get_config('report_coursemanager', 'enable_column_comparison') == 1) {
        if (get_config('report_coursemanager', 'aggregation_choice') == 1 || get_config('report_coursemanager', 'aggregation_choice') == 2) {
            $table->head[] = get_string('table_size_comparison_median', 'report_coursemanager').$OUTPUT->help_icon('head_median', 'report_coursemanager');
        }
        if (get_config('report_coursemanager', 'aggregation_choice') == 0 || get_config('report_coursemanager', 'aggregation_choice') == 2) {
            $table->head[] = get_string('table_size_comparison_average', 'report_coursemanager').$OUTPUT->help_icon('head_average', 'report_coursemanager');
        }
    }
    if (get_config('report_coursemanager', 'enable_column_cohorts') == 1) {
        $table->head[] = get_string('table_enrolled_cohorts', 'report_coursemanager');
    }
    if (get_config('report_coursemanager', 'enable_column_students') == 1) {
        $table->head[] = get_string('table_enrolled_students', 'report_coursemanager');
    }
    if (get_config('report_coursemanager', 'enable_column_teachers') == 1) {
        $table->head[] = get_string('table_enrolled_teachers', 'report_coursemanager');
    }
    $table->head[] = get_string('table_recommendation', 'report_coursemanager');
    $table->head[] = get_string('table_actions', 'report_coursemanager');

    // Retrieve all informations for each courses where user is enrolled as teacher.
    $selectedcourses = array_slice($listusercourses, $page * $perpage, $perpage);
    foreach ($selectedcourses as $course) {
        // Get context and course infos.
        $coursecontext = context_course::instance($course->id);
        $infocourse = $DB->get_record('course', ['id' => $course->id], 'category');
        $isteacher = get_user_roles($coursecontext, $USER->id, false);
        $role = key($isteacher);

        // If user is enrolled as teacher in course.
        if ($isteacher[$role]->roleid == get_config('report_coursemanager', 'teacher_role_dashboard')) {
            $countcourses = $countcourses + 1;
            $allrowclasses = '';

            // Get enrol methods information.
            $instances = enrol_get_instances($course->id, false);
            $plugins   = enrol_get_plugins(false);

            // Start to count cohorts..
            $countcohort = 0;
            // Get informations about cohort methods.
            foreach ($instances as $instance) {
                if ($instance->enrol == 'cohort') {
                    $plugin = $plugins[$instance->enrol];
                    $countcohort = $countcohort + 1;
                }
            }

            // Let's count teachers and students enrolled in course.
            $allteachers = get_role_users(get_config('report_coursemanager', 'teacher_role_dashboard'), $coursecontext);
            $otherteachersconfig = explode(',', get_config('report_coursemanager', 'other_teacher_role_dashboard'));
            $otherteachers = [];
            if (!empty(get_config('report_coursemanager', 'other_teacher_role_dashboard'))) {
                foreach ($otherteachersconfig as $teacher) {
                    $otherteachers = $otherteachers + get_role_users($teacher, $coursecontext);
                }
            }
            $allstudents = get_role_users(get_config('report_coursemanager', 'student_role_report'), $coursecontext);

            // Create a new line for table.
            $row = [];
            // Course name and direct link.
            $row[] = html_writer::link(
                new moodle_url('/course/view.php', ['id' => $course->id]),
                $course->fullname
            );

            // If course is in trash : informations are hidden.
            // Action menu has only one possibility : re-establish course out of trash category.
            if ($infocourse->category == get_config('report_coursemanager', 'category_bin')) {
                // All lines are empty, except last one that displays menu.
                $row[] = html_writer::div('<i class="fa fa-trash"></i> '.get_string('course_state_trash', 'report_coursemanager'),
                'course_trash');
                if (get_config('report_coursemanager', 'enable_column_coursesize') == 1) {
                    $row[] = html_writer::label('', null);
                }
                if (get_config('report_coursemanager', 'enable_column_comparison') == 1) {
                    if (get_config('report_coursemanager', 'aggregation_choice') == 1 || get_config('report_coursemanager', 'aggregation_choice') == 2) {
                        $row[] = html_writer::label('', null);
                    }
                    if (get_config('report_coursemanager', 'aggregation_choice') == 0 || get_config('report_coursemanager', 'aggregation_choice') == 2) {
                        $row[] = html_writer::label('', null);
                    }
                }
                if (get_config('report_coursemanager', 'enable_column_cohorts') == 1) {
                    $row[] = html_writer::label('', null);
                }
                if (get_config('report_coursemanager', 'enable_column_students') == 1) {
                    $row[] = html_writer::label('', null);
                }
                if (get_config('report_coursemanager', 'enable_column_teachers') == 1) {
                    $row[] = html_writer::label('', null);
                }
                $row[] = html_writer::label('', null);
                $menu = '
                    <div class="dropdown show">
                        <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon fa fa-ellipsis-v fa-fw " ></i>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                            <a class="dropdown-item" href="restore_course.php?courseid='.$course->id.'">'.
                            get_string('menurestorecourse', 'report_coursemanager').'</a>
                        </div>
                    </div>
                ';
                $row[] = html_writer::div($menu, null);
            } else {
                // Course is not in trash, let's show all information.

                // First, define variables for showing reports in table.
                $sumup = '';
                $iconssumup = '';

                // Add line : visible or hidden course ?.
                if ($course->visible == 1) {
                    $row[] = html_writer::div('<i class="fa fa-eye"></i> '.get_string('course_state_visible',
                    'report_coursemanager'), 'course_visible');
                } else if ($course->visible == 0) {
                    $row[] = html_writer::div('<i class="fa fa-eye-slash"></i> '.get_string('course_state_hidden',
                    'report_coursemanager'), 'course_hidden');
                }

                // Retrieve course weight in table.
                $weight = $DB->get_record('report_coursemanager_reports', ['course' => $course->id, 'report' => 'weight']);
                // To compare with course weight, thresold setting is converted in bytes.
                $threshold = get_config('report_coursemanager', 'total_filesize_threshold') * 1024 * 1024;

                // Test with config variable "total_filesize_threshold" to define icon and text color.
                // If total size is null or less than 1 Mo, consider it empty.
                if (!$weight) {
                    $iconsize = 'fa fa-question';
                    $weightclass = 'text-info';
                    $calculated = 0;
                } else if ($weight->detail == 0 || is_null($weight->detail)) {
                    $iconsize = 'fa fa-thermometer-empty';
                    $weightclass = 'text-info';
                    $calculated = 1;
                } else if ($weight->detail <= $threshold) {
                    // If total size doesn't exceed threshold, green color.
                    $iconsize = 'fa fa-thermometer-quarter';
                    $weightclass = 'text-success';
                    $calculated = 1;
                } else if ($weight->detail > $threshold) {
                    // If total size exceeds limit threshold, red color.
                    $iconsize = 'fa fa-thermometer-three-quarters';
                    $weightclass = 'text-danger';
                    $calculated = 1;
                }

                // Create table line to show files size.
                if (get_config('report_coursemanager', 'enable_column_coursesize') == 1) {
                    if ($calculated === 0) {
                        $row[] = html_writer::label('<i class="'.$iconsize.' fa-lg"></i>&nbsp;&nbsp;<i>'.get_string('weight_not_calculated',
                            'report_coursemanager').'</i>', null);
                    } else {
                        $row[] = html_writer::link("course_files.php?courseid=".$course->id,
                        '<i class="'.$iconsize.' fa-lg"></i>&nbsp;&nbsp;'.(!$weight ? '' : display_size($weight->detail, 0, 'MB')).' ',
                        ['class' => $weightclass]);
                    }
                }
                // Table line for course size comparisons.
                if (get_config('report_coursemanager', 'enable_column_comparison') == 1) {
                    if (get_config('report_coursemanager', 'aggregation_choice') == 1 || get_config('report_coursemanager', 'aggregation_choice') == 2) {
                        $row[] = html_writer::div(aggregation_median(intval($weight->detail), intval($aggregations->median)), null);
                    }
                    if (get_config('report_coursemanager', 'aggregation_choice') == 0 || get_config('report_coursemanager', 'aggregation_choice') == 2) {
                        $row[] = html_writer::div(aggregation_average(intval($weight->detail), intval($aggregations->average)), null);
                    }
                }
                // Table line for number of cohorts.
                if (get_config('report_coursemanager', 'enable_column_cohorts') == 1) {
                    $row[] = html_writer::label($countcohort, null);
                }
                // Table line for number of students.
                if (get_config('report_coursemanager', 'enable_column_students') == 1) {
                    $row[] = html_writer::label(count($allstudents), null);
                }
                // Table line for number of teachers.
                if (get_config('report_coursemanager', 'enable_column_teachers') == 1) {
                    $row[] = html_writer::label(count($allteachers + $otherteachers), null);
                }

                // Get all reports for table coursemanager_reports for recommandations.
                $reports = $DB->get_records('report_coursemanager_reports', ['course' => $course->id]);
                foreach ($reports as $report) {
                    $info = new stdClass();
                    // Analysis : depending on each report, add a specific text with information if necessary.
                    switch ($report->report) {
                        case 'heavy':
                            // Get course id for direct link in text.
                            $info->courseid = $course->id;
                            $sumup .= "<li>".get_string('total_filesize_alert', 'report_coursemanager', $info)."</li><br />";
                            $iconssumup .= "<i class='fa fa-lg fa-thermometer-three-quarters text-danger'></i>&nbsp;";
                            $allrowclasses .= 'heavy-course ';
                            break;
                        case 'no_visit_teacher':
                            $info->limit_visit = floor(get_config('report_coursemanager', 'last_access_teacher') / 30);
                            // If there are more than one teach in course, add special message.
                            if (count($allteachers + $otherteachers) > 1) {
                                $sumup .= "<li>".get_string('last_access_multiple_teacher_alert', 'report_coursemanager', $info)
                                ."</li><br />";
                            } else {
                                // If user is the only teacher in course, add message and last access.
                                $sumup .= "<li>".get_string('last_access_unique_teacher_alert', 'report_coursemanager', $info)
                                ."</li><br />";
                            }
                            $iconssumup .= "<i class='fa fa-lg fa-graduation-cap'></i>&nbsp;";
                            $allrowclasses .= "no-visit-teacher ";
                            break;
                        case 'no_visit_student':
                            $info->limit_visit = floor(get_config('report_coursemanager', 'last_access_student') / 30);
                            // Add warning about no visit for students.
                            $sumup .= "<li>".get_string('last_access_student_alert', 'report_coursemanager', $info).".</li>";
                            $iconssumup .= "<i class='fa fa-lg fa-group text-info'></i>&nbsp;";
                            $allrowclasses .= "no-visit-student ";
                            break;
                        case 'no_student':
                            $sumup .= "<li>".get_string('empty_student_alert', 'report_coursemanager').".</li>";
                            $iconssumup .= "<i class='fa fa-lg fa-user-o text-info'></i>&nbsp;";
                            $allrowclasses .= "no-student ";
                            break;
                        case 'empty':
                            $sumup .= "<li>".get_string('empty_course_alert', 'report_coursemanager')."</li><br />";
                            $iconssumup .= "<i class='fa fa-lg fa-battery-empty text-danger'></i>&nbsp;";
                            $allrowclasses .= "no-content ";
                            break;

                    }
                }
                // Get all reports for orphan submissions.
                $reportsorphans = $DB->get_records('report_coursemanager_orphans', ['course' => $course->id]);
                if (!empty($reportsorphans)) {
                    $info->filesize = 0;
                    $info->filescount = 0;
                    $info->assigns = 0;
                    foreach ($reportsorphans as $orphan) {
                        $info->assigns = $info->assigns + 1;
                        $info->filesize += number_format(ceil($orphan->weight / 1048576), 0, ',', '');
                        $info->filescount += $orphan->files;
                    }
                    $sumup .= "<li>".get_string('orphan_submissions_alert', 'report_coursemanager', $info)."</li><br />";
                    $iconssumup .= "<i class='fa fa-lg fa-files-o text-danger'></i>&nbsp;";
                    $allrowclasses .= "orphan-submissions ";
                }
                // End of reports analysis.

                // If no specific recommendations, add a specific message.
                if (empty($sumup)) {
                    $sumup = "<p class='course_visible'><i class='fa fa-check'></i> ".get_string('no_advices',
                    'report_coursemanager')."</p>";
                    $iconssumup .= "<i class='fa fa-lg fa-thumbs-up text-success'></i>";
                    $allrowclasses .= "ok ";
                }

                // Create button to open Modal containing all recommandations.
                $row[] = html_writer::label($iconssumup."<br /><a class='badge badge-pill badge-light' href='#'
                data-toggle='modal' data-target='#exampleModal".$course->id."'>".get_string('see_advices',
                'report_coursemanager')."</a>", null);

                // Code for Modal.
                echo html_writer::div('
                <div class="modal fade" id="exampleModal'.$course->id.'" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">'.get_string('advices_for_course',
                        'report_coursemanager').$course->fullname.'</h5>
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
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        '.get_string('closereportmodal', 'report_coursemanager').'</button>
                      </div>
                    </div>
                  </div>
                </div>
                ');

                // Create actions menu.
                $deletelink = new moodle_url('/report/coursemanager/delete_course.php', ['courseid' => $course->id]);
                $fileslink = new moodle_url('/report/coursemanager/course_files.php', ['id' => $course->id]);
                $resetlink = new moodle_url('/report/coursemanager/reset.php', ['id' => $course->id]);
                $deletecohortlink = new moodle_url('/report/coursemanager/delete_cohort.php', ['id' => $course->id]);
                $courseeditlink = new moodle_url('/course/edit.php', ['id' => $course->id]);

                // Create variable to list actions. By default, link to put course in trash is available.
                $listactions = '<a class="dropdown-item" href="'.$deletelink.'">
                    '.get_string('menudeletecourse', 'report_coursemanager').'</a>';

                // Now, check plugin config if other actions are enabled.
                if (get_config('report_coursemanager', 'enable_action_coursefiles')) {
                    $listactions .= '<a class="dropdown-item" href="'.$fileslink.'">
                        '.get_string('menucoursefilesinfo', 'report_coursemanager').'</a>';
                }
                if (get_config('report_coursemanager', 'enable_action_reset')) {
                    $listactions .= '<a class="dropdown-item" href="'.$resetlink.'">
                        '.get_string('menureset', 'report_coursemanager').'</a>';
                }
                if (get_config('report_coursemanager', 'enable_action_cohorts')) {
                    $listactions .= '<a class="dropdown-item" href="'.$deletecohortlink.'">
                        '.get_string('menuunenrolcohorts', 'report_coursemanager').'</a>';
                }
                if (get_config('report_coursemanager', 'enable_action_params')) {
                    $listactions .= '<a class="dropdown-item" href="'.$courseeditlink.'">
                        '.get_string('menucourseparameters', 'report_coursemanager').'</a>';
                }

                $menu = '
                    <div class="dropdown show">
                        <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon fa fa-ellipsis-v fa-fw " ></i>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                            '.$listactions.'
                        </div>
                    </div>
                ';
                $row[] = html_writer::div($menu, null);
            }

            // All infos are set. Add line to table.
            $table->rowclasses[] = "filterrow ".$allrowclasses;
            $table->data[] = $row;
        }
    }
    // End : show all table.
    if ($countcourses > 0) {
        echo html_writer::table($table);
        $baseurl = new moodle_url('/report/coursemanager/view.php', ['perpage' => $perpage]);
        echo $OUTPUT->paging_bar(count($listusercourses), $page, $perpage, $baseurl);

    } else {
        echo html_writer::div(get_string('no_course_to_show', 'report_coursemanager'), 'alert alert-primary');
    }
}
// echo report_coursemanager_median_coursesize();
// Trigger event for viewing the Teacher Courses Dashboard.
$context = context_user::instance($USER->id);
$eventparams = ['context' => $context];
$event = \report_coursemanager\event\course_dashboard_viewed::create($eventparams);
$event->trigger();

echo $OUTPUT->footer();
