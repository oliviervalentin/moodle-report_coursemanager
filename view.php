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

// $page, $perpage et search_courses.js deleted : DataTables now handles pagination and search.
$courseid = optional_param('courseid', 0, PARAM_INT);
$done     = optional_param('done', 0, PARAM_TEXT);

$site = get_site();

$PAGE = new moodle_page();
$PAGE->set_context(context_system::instance());
$PAGE->set_heading(get_string('title', 'report_coursemanager'));
$PAGE->set_url('/report/coursemanager/view.php');
$PAGE->set_pagelayout('mycourses');
$PAGE->set_pagetype('report-coursemanager');
$PAGE->blocks->add_region('content');
$PAGE->set_title($site->fullname);

// Load AMD module that initialises DataTables and filters.
$PAGE->requires->js_call_amd('report_coursemanager/coursetable', 'init', [
    'courses',
    [
        'lengthMenu'   => get_string('dt_lengthmenu', 'report_coursemanager'),
        'info'         => get_string('dt_info', 'report_coursemanager'),
        'infoEmpty'    => get_string('dt_infoempty', 'report_coursemanager'),
        'infoFiltered' => get_string('dt_infofiltered', 'report_coursemanager'),
        'zeroRecords'  => get_string('dt_zerorecords', 'report_coursemanager'),
        'first'        => get_string('dt_first', 'report_coursemanager'),
        'last'         => get_string('dt_last', 'report_coursemanager'),
        'next'         => get_string('dt_next', 'report_coursemanager'),
        'previous'     => get_string('dt_previous', 'report_coursemanager'),
    ]
]);

$now = time();

echo $OUTPUT->header();

// Message after action.
if ($done != '0') {
    switch ($done) {
        case 'cohort_deleted':
            $titledone = get_string('confirm_cohort_unenrolled_title', 'report_coursemanager');
            $textdone  = get_string('confirm_cohort_unenrolled_message', 'report_coursemanager');
            break;
        case 'course_deleted':
            $titledone = get_string('confirm_course_deleted_title', 'report_coursemanager');
            $textdone  = get_string('confirm_course_deleted_message', 'report_coursemanager');
            break;
        case 'course_restored':
            $titledone = get_string('confirm_course_restored_title', 'report_coursemanager');
            $textdone  = get_string('confirm_course_restored_message', 'report_coursemanager');
            break;
        default:
            break;
    }
    // BS5 : btn-close and data-bs-dismiss replace close button.
    echo html_writer::div('
        <div class="alert alert-success alert-dismissible fade show" role="alert">
        <h4 class="alert-heading">' . $titledone . '</h4>
        <p>' . $textdone . '</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    ');
}
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

// FILTERS.
// Now use data-filter for buttons and classList on <tr> for filtering
// (coursetable.js reads classList of each <tr>.
// Old filter system with <input type="radio"> + search_courses.js is removed.

// Text field (replaces #courseInput + onkeyup="searchCourses()").
echo html_writer::start_div('coursemanager-toolbar mb-3 d-flex flex-wrap gap-2 align-items-center');
echo html_writer::tag(
    'label',
    get_string('search') . ' : ',
    ['for' => 'coursemanager-search',
    'class' => 'h5']
);
echo html_writer::tag(
    'input', '', [
        'type'        => 'text',
        'id'          => 'coursemanager-search',
        'class'       => 'form-control w-50',
        'placeholder' => get_string('text_filter', 'report_coursemanager'),
    ]
);
echo html_writer::end_div();

// Filter buttons.
echo html_writer::start_div('coursemanager-toolbar mb-3 d-flex flex-wrap gap-2 align-items-center');
$filters = [
    'filterrow' => ['icon' => 'fa-list', 'class' => '', 'label' => get_string('all_courses', 'report_coursemanager')],
    'heavy-course' => ['icon' => 'fa-thermometer-three-quarters', 'class' => 'text-danger', 'label' => get_string('heavy_course', 'report_coursemanager')],
    'no-visit-student' => ['icon' => 'fa-group', 'class' => 'text-info','label' => get_string('no_visit_student', 'report_coursemanager')],
    'no-visit-teacher' => ['icon' => 'fa-graduation-cap', 'class' => '', 'label' => get_string('no_visit_teacher', 'report_coursemanager')],
    'no-student' => ['icon' => 'fa-user-o', 'class' => '', 'label' => get_string('no_student', 'report_coursemanager')],
    'no-content' => ['icon' => 'fa-battery-empty', 'class' => 'text-danger', 'label' => get_string('no_content', 'report_coursemanager')],
    'orphan-submissions' => ['icon' => 'fa-files-o', 'class' => 'text-danger', 'label' => get_string('orphan_submissions_button', 'report_coursemanager')],
    'ok' => ['icon' => 'fa-check-circle', 'class' => 'text-success', 'label' => get_string('ok', 'report_coursemanager')],
];

foreach ($filters as $token => $def) {
    $active = ($token === 'filterrow') ? ' active' : '';
    $icon   = html_writer::tag('i', '', ['class' => "fa fa-lg {$def['icon']} {$def['class']}"]);
    echo html_writer::tag('button', $icon . ' ' . $def['label'], [
        'type'        => 'button',
        'class'       => 'btn btn-outline-primary coursemanager-filter-btn' . $active,
        'data-filter' => $token,
    ]);
}

echo html_writer::end_div();

// NEW Courses table.
$listusercourses = enrol_get_users_courses($USER->id, false, '', 'fullname ASC');

if (count($listusercourses) == 0) {
    echo html_writer::div(get_string('no_course_to_show', 'report_coursemanager'), 'alert alert-primary');
} else {
    $countcourses = 0;
    $table        = new html_table();
    $table->id    = 'courses';

    // w-100 forces width to 100 %.
    $table->attributes['class'] = 'admintable generaltable browse_courses w-100';
    $table->align               = ['left', 'left', 'left', 'left', 'left', 'left', 'center', 'left'];
    $table->head                = [];

    $aggregations = calculate_aggregation_coursesize();
    $maxsize      = max_size_course();

    // Table headers.
    $table->head[] = get_string('table_course_name', 'report_coursemanager');
    $table->head[] = get_string('table_course_state', 'report_coursemanager');
    if (get_config('report_coursemanager', 'enable_column_coursesize') == 1) {
        $table->head[] = get_string('table_files_weight', 'report_coursemanager');
    }
    // TO DO : hide sortable icons for median and average columns.
    if (get_config('report_coursemanager', 'enable_column_comparison') == 1) {
        if (get_config('report_coursemanager', 'aggregation_choice') == 1 ||
            get_config('report_coursemanager', 'aggregation_choice') == 2) {
            $table->head[] = get_string('table_size_comparison_median', 'report_coursemanager') .
                 $OUTPUT->help_icon('head_median', 'report_coursemanager');
        }
        if (get_config('report_coursemanager', 'aggregation_choice') == 0 ||
            get_config('report_coursemanager', 'aggregation_choice') == 2) {
            $table->head[] = get_string('table_size_comparison_average', 'report_coursemanager') .
                $OUTPUT->help_icon('head_average', 'report_coursemanager');
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

    // Iteration for all courses - no more array_slice.
    foreach ($listusercourses as $course) {
        $coursecontext = context_course::instance($course->id);
        $infocourse    = $DB->get_record('course', ['id' => $course->id], 'category');
        $isteacher     = get_user_roles($coursecontext, $USER->id, false);
        $role          = key($isteacher);

        if ($isteacher[$role]->roleid == get_config('report_coursemanager', 'teacher_role_dashboard')) {
            $countcourses++;
            $allrowclasses = '';

            $instances = enrol_get_instances($course->id, false);
            $plugins   = enrol_get_plugins(false);

            $countcohort = 0;
            foreach ($instances as $instance) {
                if ($instance->enrol == 'cohort') {
                    $plugin = $plugins[$instance->enrol];
                    $countcohort++;
                }
            }

            $allteachers         = get_role_users(get_config('report_coursemanager', 'teacher_role_dashboard'), $coursecontext);
            $otherteachersconfig = explode(',', get_config('report_coursemanager', 'other_teacher_role_dashboard'));
            $otherteachers       = [];
            if (!empty(get_config('report_coursemanager', 'other_teacher_role_dashboard'))) {
                foreach ($otherteachersconfig as $teacher) {
                    $otherteachers = $otherteachers + get_role_users($teacher, $coursecontext);
                }
            }
            $allstudents = get_role_users(get_config('report_coursemanager', 'student_role_report'), $coursecontext);

            $row = [];

            // Course name column.
            $row[] = html_writer::link(
                new moodle_url('/course/view.php', ['id' => $course->id]),
                $course->fullname
            );

            // Courses in trash.
            if ($infocourse->category == get_config('report_coursemanager', 'category_bin')) {
                $allrowclasses = 'course-trash';

                $row[] = html_writer::div(
                    '<i class="fa fa-trash"></i> ' . get_string('course_state_trash', 'report_coursemanager'),
                    'course_trash'
                );
                if (get_config('report_coursemanager', 'enable_column_coursesize') == 1) {
                    $row[] = html_writer::label('', null);
                }
                if (get_config('report_coursemanager', 'enable_column_comparison') == 1) {
                    if (get_config('report_coursemanager', 'aggregation_choice') == 1 ||
                        get_config('report_coursemanager', 'aggregation_choice') == 2) {
                        $row[] = html_writer::label('', null);
                    }
                    if (get_config('report_coursemanager', 'aggregation_choice') == 0 ||
                        get_config('report_coursemanager', 'aggregation_choice') == 2) {
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

                // BS5 : data-bs-toggle replaces data-toggle.
                $menu  = '
                    <div class="dropdown">
                        <a class="btn btn-secondary dropdown-toggle" href="#" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="icon fa fa-ellipsis-v fa-fw"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="restore_course.php?courseid=' . $course->id . '">' .
                            get_string('menurestorecourse', 'report_coursemanager') . '</a>
                        </div>
                    </div>
                ';
                $row[] = html_writer::div($menu, null);

            } else {
                // Normal course.
                $sumup      = '';
                $iconssumup = '';

                if ($course->visible == 1) {
                    $row[] = html_writer::div(
                        '<i class="fa fa-eye"></i> ' . get_string('course_state_visible', 'report_coursemanager'),
                        'course_visible'
                    );
                } else {
                    $row[] = html_writer::div(
                        '<i class="fa fa-eye-slash"></i> ' . get_string('course_state_hidden', 'report_coursemanager'),
                        'course_hidden'
                    );
                }

                $weight    = $DB->get_record('report_coursemanager_reports', ['course' => $course->id, 'report' => 'weight']);
                $threshold = get_config('report_coursemanager', 'total_filesize_threshold') * 1024 * 1024;

                if (!$weight) {
                    $iconsize    = 'fa fa-question';
                    $weightclass = 'text-info';
                    $calculated  = 0;
                } else if ($weight->detail == 0 || is_null($weight->detail)) {
                    $iconsize    = 'fa fa-thermometer-empty';
                    $weightclass = 'text-info';
                    $calculated  = 1;
                } else if ($weight->detail <= $threshold) {
                    $iconsize    = 'fa fa-thermometer-quarter';
                    $weightclass = 'text-success';
                    $calculated  = 1;
                } else {
                    $iconsize    = 'fa fa-thermometer-three-quarters';
                    $weightclass = 'text-danger';
                    $calculated  = 1;
                }

                if (get_config('report_coursemanager', 'enable_column_coursesize') == 1) {
                    if ($calculated === 0) {
                        $row[] = html_writer::label(
                            '<i class="' . $iconsize . ' fa-lg"></i>&nbsp;&nbsp;<i>' .
                            get_string('weight_not_calculated', 'report_coursemanager') . '</i>',
                            null
                        );
                    } else {
                        $row[] = html_writer::link(
                            'course_files.php?courseid=' . $course->id,
                            '<i class="' . $iconsize . ' fa-lg"></i>&nbsp;&nbsp;' .
                            (!$weight ? '' : display_size($weight->detail, 0, 'MB')) . ' ',
                            ['class' => $weightclass]
                        );
                    }
                }

                if (get_config('report_coursemanager', 'enable_column_comparison') == 1) {

                    $weightdetail = (!empty($weight) && isset($weight->detail)) ? intval($weight->detail) : 0;

                    if (get_config('report_coursemanager', 'aggregation_choice') == 1 ||
                        get_config('report_coursemanager', 'aggregation_choice') == 2) {
                        $median = (!empty($aggregations) && isset($aggregations->median)) ? intval($aggregations->median) : 0;
                        $row[] = html_writer::div(
                            aggregation_median($weightdetail, $median), null
                        );
                    }
                    if (get_config('report_coursemanager', 'aggregation_choice') == 0 ||
                        get_config('report_coursemanager', 'aggregation_choice') == 2) {
                        $average = (!empty($aggregations) && isset($aggregations->average)) ? intval($aggregations->average) : 0;
                        $row[] = html_writer::div(
                            aggregation_average($weightdetail, $average), null
                        );
                    }
                }

                if (get_config('report_coursemanager', 'enable_column_cohorts') == 1) {
                    $row[] = html_writer::label($countcohort, null);
                }
                if (get_config('report_coursemanager', 'enable_column_students') == 1) {
                    $row[] = html_writer::label(count($allstudents), null);
                }
                if (get_config('report_coursemanager', 'enable_column_teachers') == 1) {
                    $row[] = html_writer::label(count($allteachers + $otherteachers), null);
                }

                $reports = $DB->get_records('report_coursemanager_reports', ['course' => $course->id]);
                foreach ($reports as $report) {
                    $info = new stdClass();
                    switch ($report->report) {
                        case 'heavy':
                            $info->courseid = $course->id;
                            $sumup         .= '<li>' . get_string('total_filesize_alert', 'report_coursemanager', $info) . '</li><br />';
                            $iconssumup    .= "<i class='fa fa-lg fa-thermometer-three-quarters text-danger'></i>&nbsp;";
                            $allrowclasses .= ' heavy-course';
                            break;
                        case 'no_visit_teacher':
                            $info->limit_visit = floor(get_config('report_coursemanager', 'last_access_teacher') / 30);
                            if (count($allteachers + $otherteachers) > 1) {
                                $sumup .= '<li>' . get_string('last_access_multiple_teacher_alert', 'report_coursemanager', $info) . '</li><br />';
                            } else {
                                $sumup .= '<li>' . get_string('last_access_unique_teacher_alert', 'report_coursemanager', $info) . '</li><br />';
                            }
                            $iconssumup    .= "<i class='fa fa-lg fa-graduation-cap'></i>&nbsp;";
                            $allrowclasses .= ' no-visit-teacher';
                            break;
                        case 'no_visit_student':
                            $info->limit_visit = floor(get_config('report_coursemanager', 'last_access_student') / 30);
                            $sumup            .= '<li>' . get_string('last_access_student_alert', 'report_coursemanager', $info) . '.</li>';
                            $iconssumup       .= "<i class='fa fa-lg fa-group text-info'></i>&nbsp;";
                            $allrowclasses    .= ' no-visit-student';
                            break;
                        case 'no_student':
                            $sumup         .= '<li>' . get_string('empty_student_alert', 'report_coursemanager') . '.</li>';
                            $iconssumup    .= "<i class='fa fa-lg fa-user-o text-info'></i>&nbsp;";
                            $allrowclasses .= ' no-student';
                            break;
                        case 'empty':
                            $sumup         .= '<li>' . get_string('empty_course_alert', 'report_coursemanager') . '</li><br />';
                            $iconssumup    .= "<i class='fa fa-lg fa-battery-empty text-danger'></i>&nbsp;";
                            $allrowclasses .= ' no-content';
                            break;
                    }
                }

                $reportsorphans = $DB->get_records('report_coursemanager_orphans', ['course' => $course->id]);
                if (!empty($reportsorphans)) {
                    $info           = new stdClass();
                    $info->filesize   = 0;
                    $info->filescount = 0;
                    $info->assigns    = 0;
                    foreach ($reportsorphans as $orphan) {
                        $info->assigns++;
                        $info->filesize   += number_format(ceil($orphan->weight / 1048576), 0, ',', '');
                        $info->filescount += $orphan->files;
                    }
                    $sumup         .= '<li>' . get_string('orphan_submissions_alert', 'report_coursemanager', $info) . '</li><br />';
                    $iconssumup    .= "<i class='fa fa-lg fa-files-o text-danger'></i>&nbsp;";
                    $allrowclasses .= ' orphan-submissions';
                }

                if (empty($sumup)) {
                    $sumup          = "<p class='course_visible'><i class='fa fa-check'></i> " .
                        get_string('no_advices', 'report_coursemanager') . '</p>';
                    $iconssumup    .= "<i class='fa fa-lg fa-thumbs-up text-success'></i>";
                    $allrowclasses .= ' ok';
                }

                // BS5 update on old badge-pill badge-light and data-toggle/data-target.
                $row[] = html_writer::label(
                    $iconssumup . "<br /><a class='badge rounded-pill bg-light text-dark' href='#'
                    data-bs-toggle='modal' data-bs-target='#exampleModal" . $course->id . "'>" .
                    get_string('see_advices', 'report_coursemanager') . '</a>',
                    null
                );

                // Modal recommandations.
                // BS5 update : data-dismiss replaces data-bs-dismiss.
                echo html_writer::div('
                <div class="modal fade" id="exampleModal' . $course->id . '" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">' .
                            get_string('advices_for_course', 'report_coursemanager') . $course->fullname . '</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                          <ul>' . $sumup . '</ul>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' .
                            get_string('closereportmodal', 'report_coursemanager') . '</button>
                      </div>
                    </div>
                  </div>
                </div>
                ');

                // Acxtions menu.
                $deletelink       = new moodle_url('/report/coursemanager/delete_course.php', ['courseid' => $course->id]);
                $fileslink        = new moodle_url('/report/coursemanager/course_files.php', ['courseid' => $course->id]);
                $resetlink        = new moodle_url('/report/coursemanager/reset.php', ['id' => $course->id]);
                $deletecohortlink = new moodle_url('/report/coursemanager/delete_cohort.php', ['id' => $course->id]);
                $courseeditlink   = new moodle_url('/course/edit.php', ['id' => $course->id]);

                $listactions = '<a class="dropdown-item" href="' . $deletelink . '">' .
                    get_string('menudeletecourse', 'report_coursemanager') . '</a>';

                if (get_config('report_coursemanager', 'enable_action_coursefiles')) {
                    $listactions .= '<a class="dropdown-item" href="' . $fileslink . '">' .
                        get_string('menucoursefilesinfo', 'report_coursemanager') . '</a>';
                }
                if (get_config('report_coursemanager', 'enable_action_reset')) {
                    $listactions .= '<a class="dropdown-item" href="' . $resetlink . '">' .
                        get_string('menureset', 'report_coursemanager') . '</a>';
                }
                if (get_config('report_coursemanager', 'enable_action_cohorts')) {
                    $listactions .= '<a class="dropdown-item" href="' . $deletecohortlink . '">' .
                        get_string('menuunenrolcohorts', 'report_coursemanager') . '</a>';
                }
                if (get_config('report_coursemanager', 'enable_action_params')) {
                    $listactions .= '<a class="dropdown-item" href="' . $courseeditlink . '">' .
                        get_string('menucourseparameters', 'report_coursemanager') . '</a>';
                }

                // BS5 update : delete "show" on .dropdown, data-bs-toggle replaces data-toggle.
                $menu  = '
                    <div class="dropdown">
                        <a class="btn btn-secondary dropdown-toggle" href="#" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="icon fa fa-ellipsis-v fa-fw"></i>
                        </a>
                        <div class="dropdown-menu">
                            ' . $listactions . '
                        </div>
                    </div>
                ';
                $row[] = html_writer::div($menu, null);
            }

            // Row classes are used for JS filtering (coursetable.js reads classList of <tr>).
            $table->rowclasses[] = 'filterrow ' . trim($allrowclasses);
            $table->data[]       = $row;
        }
    }

    if ($countcourses > 0) {
        // Now DataTables handles pagination : no more paging_bar() nor $perpage.
        echo html_writer::table($table);
    } else {
        echo html_writer::div(get_string('no_course_to_show', 'report_coursemanager'), 'alert alert-primary');
    }
}

// Trigger the event of viewing the teacher dashboard.
$context     = context_user::instance($USER->id);
$eventparams = ['context' => $context];
$event       = \report_coursemanager\event\course_dashboard_viewed::create($eventparams);
$event->trigger();

echo $OUTPUT->footer();