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
$PAGE->set_context(context_system::instance());
$PAGE->set_heading($site->fullname);
$PAGE->requires->js('/report/coursemanager/test.js', false);
$PAGE->requires->js_init_call('filterSelection');

$PAGE->set_url('/coursemanager/view.php');
$PAGE->set_pagelayout('mycourses');
$PAGE->set_secondary_navigation(false);

$PAGE->set_pagetype('teachertools');
$PAGE->blocks->add_region('content');
$PAGE->set_title($site->fullname);
$PAGE->set_heading($site->fullname);
// Force the add block out of the default area.
$PAGE->theme->addblockposition  = BLOCK_ADDBLOCK_POSITION_CUSTOM;

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
echo html_writer::div('
<input type="text" id="myInput" onkeyup="myFunction()" placeholder="'.get_string('text_filter', 'report_coursemanager').'">

<div id="filtercontainer">
  <button class="btn btn-outline-secondary" onclick="filterSelection(\'all\')"><i class=\'fa fa-lg fa-list\'></i> '.get_string('all_courses', 'report_coursemanager').'</button>
  <button class="btn btn-outline-secondary" onclick="filterSelection(\'no-content\')"><i class=\'fa fa-lg fa-battery-empty text-danger\'></i> '.get_string('no_content', 'report_coursemanager').'</button>
  <button class="btn btn-outline-secondary" onclick="filterSelection(\'no-visit-student\')"><i class=\'fa fa-lg fa-group text-info\'></i> '.get_string('no_visit_student', 'report_coursemanager').'</button>
  <button class="btn btn-outline-secondary" onclick="filterSelection(\'no-visit-teacher\')"><i class=\'fa fa-lg fa-graduation-cap\'></i> '.get_string('no_visit_teacher', 'report_coursemanager').'</button>
  <button class="btn btn-outline-secondary" onclick="filterSelection(\'no-student\')"><i class=\'fa fa-lg fa-user-o text-info\'></i> '.get_string('no_student', 'report_coursemanager').'</button>
  <button class="btn btn-outline-secondary" onclick="filterSelection(\'heavy-course\')"><i class=\'fa fa-lg fa-thermometer-three-quarters text-danger\'></i> '.get_string('heavy_course', 'report_coursemanager').'</button>
</div>
');

////////////////////////////////////////////////////////////////////////


// First, retrieve all courses where user is enrolled.
$list_user_courses = enrol_get_users_courses($USER->id, false, '' , 'fullname ASC');

// If empty : user is not enrolled as teacher in any course. Show warning.
if(count($list_user_courses) == 0) {
    echo html_writer::div('<h2>Pas de cours</h3>
        Vous n\'êtes inscrit à aucun cours en tant qu\'enseignant.', 'alert alert-primary');

// If user is enrolled in at least one course as teacher, let's start !
} else {
    // Add a new table to display courses information.
    $table = new html_table();
	$all_row_classes = '';
    $table->id = 'courses';

    $table->attributes['class'] = 'admintable generaltable';
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
        // $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
		$coursecontext = context_course::instance($course->id);
		$infocourse = $DB->get_record('course', array('id' => $course->id), 'category');
	    $is_teacher = get_user_roles($coursecontext, $USER->id, false);
	    $role = key($is_teacher);
		

        // If user is enrolled as teacher in course.
	    if ($is_teacher[$role]->roleid == 3) {
			
//////////////////			
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
            $all_teachers = get_role_users(3, $coursecontext);
			$all_students = get_role_users(5, $coursecontext);	

            // Create a new line for table.
			// $row[] = new html_table_row();
            $row = array ();
            // Course name and direct link.
	        $row[] = html_writer::link("/course/view.php?id=".$course->id, $course->fullname);

            // If course is in trash : informations are hidden.
			// Action menu has only one possibility : contact support to re-establish course.
            if($infocourse->category == get_config('report_coursemanager', 'category_bin')) {
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
				$sumup = '';
				$icons_sumup = '';
				$string_key = '';
				// If course is not in trash, let's show all information.

				// Visible or hidden course ?.
	            if ($course->visible == 1) {
	                $row[] = html_writer::div('<i class="fa fa-eye"></i> '.get_string('course_state_visible', 'report_coursemanager'), 'course_visible');
	            } else if ($course->visible == 0) {
		            $row[] = html_writer::div('<i class="fa fa-eye-slash"></i> '.get_string('course_state_hidden', 'report_coursemanager'), 'course_hidden');
	            }

				// Query for total files size in course.			
				$sql = 'SELECT SUM(filesize)
					FROM {files}
					WHERE contextid 
					IN (SELECT id FROM {context} WHERE contextlevel = 70 AND instanceid IN 
					(SELECT id FROM {course_modules} WHERE course = ?)) ';
				$paramsdb = array($course->id);
				$dbresult = $DB->get_field_sql($sql, $paramsdb);
				// Rounded files size in Mo.
				$filesize = number_format(ceil($dbresult / 1048576));

				// Test with config variable "total_filesize_threshold".
				// if total size is null, blue color.
				if ($filesize == 0) {
					$icon_size = 'fa fa-thermometer-empty';
					$progress = 'text-info';
//////////////////////////////////////////////////////////////////////////////////////
////////////////   A MODIFIER !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
////////////////////////////////////////////////////////////////////////////////////////
				} else if ($filesize <= (get_config('report_coursemanager', 'total_filesize_threshold'))) {
					// Si la taille totale est inférieure à la moitié de la limite définie dans les paramètres, affichage vert.
					$icon_size = 'fa fa-thermometer-quarter';
					$progress = 'text-success';
				// } else if ($filesize > 10 && $filesize < 50) {
					// $icon_size = 'fa fa-thermometer-half';
					// $progress = 'text-warning';
				} else if ($filesize > (get_config('report_coursemanager', 'total_filesize_threshold'))) {
					// Au-delà de la moitié, on affiche une alerte.
					$icon_size = 'fa fa-thermometer-three-quarters';
					$progress = 'text-danger';
				}

				// Create table line to show files size.
				$row[] = html_writer::link("course_files.php?courseid=".$course->id, 
				    '<i class="'.$icon_size.' fa-lg"></i>&nbsp;&nbsp;'.$filesize.' Mo', array('class' => $progress));
				
				// Table line for number of cohorts.
				$row[] = html_writer::label($count_cohort, null);
				// Table line for number of students.
				$row[] = html_writer::label(count($all_students), null);
				// Table line for number of teachers.
				$row[] = html_writer::label(count($all_teachers), null);

				///////////////////////////////////////////////////
				/////  SPECIAL TESTS FOR RECOMMENDATIONS      /////
				///////////////////////////////////////////////////

				// 1- TEST FOR TOTAL COURSE SIZE.
				// If total_course_size exceeds limit, add warning.
				
				if ($filesize >= get_config('report_coursemanager', 'total_filesize_threshold')) {
					$info = new stdClass();
					$info->courseid = $course->id;
					$sumup .= "<li>".get_string('total_filesize_alert', 'report_coursemanager', $info)."</li><br />";
					$icons_sumup .= "<i class='fa fa-lg fa-thermometer-three-quarters text-danger'></i>&nbsp;";
					$all_row_classes .= 'heavy-course ';
					// $data_key[] = "heavycourse";
					// $string_key .= "heavycourse";
				}
					
				// 2- TEST FOR EMPTY COURSE.
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
				
				// If no result, course only contains announcment forum. Add a warning.
				if($dbresultemptycourse < 1) {
					$sumup .= "<li>".get_string('empty_course_alert', 'report_coursemanager')."</li><br />";
					$icons_sumup .= "<i class='fa fa-lg fa-battery-empty text-danger'></i>&nbsp;";
					$all_row_classes .= "no-content ";
					// $data_key[] = "nocontent";
					// $string_key .= "no-content";
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
					$info = new stdClass();
					$info->date = userdate($lastaccess);
					$info->limit_visit = get_config('report_coursemanager', 'last_access_teacher');
					// If there are more than one teach in course, add special message.
					if (count($all_teachers) > 1) {	
						$sumup .= "<li>".get_string('last_access_multiple_teacher_alert', 'report_coursemanager', $info)."</li><br />";
					} else {
						// If user is the only teacher in course, add message and last access.
						$sumup .= "<li>".get_string('last_access_unique_teacher_alert', 'report_coursemanager', $info).".</li><br />";
					}
					$icons_sumup .= "<i class='fa fa-lg fa-graduation-cap'></i>&nbsp;";
					$all_row_classes .= "no-visit-teacher ";
					// $data_key[] = "novisitteacher";
					// $string_key .= "novisitteacher ";
				}
				
				// 4- TEST FOR STUDENTS VISITS
				$count_student_visit = array();
				// If at least one student enrolled.
				if (count($all_students) > 0) {
					// For each student, retrieve last access in course.
					foreach($all_students as $student){
						$lastaccess = $DB->get_field('user_lastaccess', 'timeaccess', array('courseid' => $course->id, 'userid' => $student->id));
						// Difference between now and last access.
						$diff = $now - $lastaccess;
						// Calculate number of days without connection in course (86 400 equals number of seconds per day).
						$time_student = floor($diff/86400);
						// Si limit is under last_access_student, student has visited course.
						if ($time_student <= get_config('report_coursemanager', 'last_access_student')) {
							// Let's count a visit.
							array_push($count_student_visit, 'visited_student');
						}
					}
					$res_count_student_visit = array_count_values($count_student_visit);
					// If result is empty, no student has visited course.
					// if ($res_count_student_visit['visited_student'] == 0) {
					if (!isset($res_count_student_visit['visited_student'])) {
						$info = new stdClass();
						$info->limit_visit = floor(get_config('report_coursemanager', 'last_access_student')/30);					
						// Add warning about no visit for students.
						$sumup .= "<li>".get_string('last_access_student_alert', 'report_coursemanager', $info).".</li>";
						$icons_sumup .= "<i class='fa fa-lg fa-group text-info'></i>&nbsp;";
						$all_row_classes .= "no-visit-student ";
						// $data_key[] = "novisitstudent";
						// $string_key .= "novisitstudent ";
					}
				} else {
					// If no students enrolled, add a specific warning
					$sumup .= "<li>".get_string('empty_student_alert', 'report_coursemanager').".</li>";
					$icons_sumup .= "<i class='fa fa-lg fa-user-o text-info'></i>&nbsp;";
					$all_row_classes .= "no-student ";
					// $data_key[] = "nostudent";
					// $string_key .= "nostudent ";
				}

				////////////////////////////////////////////			
				///// END FOR RECOMMENDATIONS TEST
				////////////////////////////////////////////			

                // If no specific recommendations, add a specific message.
				if (empty($sumup)) {
				    $sumup = "<p class='course_visible'><i class='fa fa-check'></i> ".get_string('no_advices', 'report_coursemanager')."</p>";
					$icons_sumup .= "<i class='fa fa-lg fa-thumbs-up text-success'></i>";
					$all_row_classes .= "ok ";
					// $data_key[] = "ok";
					// $string_key .= "ok ";
				}
				
			////////////////////////////////////////////////// créer des classes rows
				
				// $data_keys = (array)$data_key;
				// print_object($data_key);
				// $row->attributes['data-key'][] = 'abc';
				// $tagada[] = $i;
				// $all_row_classes .= $i++;
				
				
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

				// Create actions menu.
				$menu = '
					<div class="dropdown show">
						<a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="icon fa fa-ellipsis-v fa-fw " ></i>
						</a>
						<div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
							<a class="dropdown-item" href="delete_course.php?courseid='.$course->id.'">Mettre à la corbeille</a>
							<a class="dropdown-item" href="course_files.php?courseid='.$course->id.'">Voir les fichiers</a>
							<a class="dropdown-item" href="reset.php?id='.$course->id.'">Réinitialiser</a>
							<a class="dropdown-item" href="/enrol/scolarite/manage.php?id='.$course->id.'">Ajouter des cohortes</a>
							<a class="dropdown-item" href="delete_cohort.php?id='.$course->id.'">Désinscrire des cohortes</a>
							<a class="dropdown-item" href="/course/edit.php?id='.$course->id.'">Paramètres</a>
						</div>
					</div>
				';
				$row[] = html_writer::div($menu, null);
            }

/////////////////// DES TEST ! A SUPPRIMER SI INUTILE
// <a class="dropdown-item" href="#" data-toggle="modal" data-target="#exampleModal">Test Modal</a>
// <a class="dropdown-item" href="view.php?courseid='.$course->id.'&confirm=1">TEST CORB</a>

            // All infos are set. Add line to table.
			$table->rowclasses[] = "filterrow show ".$all_row_classes;
			// $table->attributes['data-key'] = $data_keys;
	        $table->data[] = $row;
	    }
    }
    // End : show all table.
    echo html_writer::table($table);
}

// Trigger event for viewing the Teacher Courses Dashboard.
$context = context_user::instance($USER->id);
$eventparams = array('context' => $context);
$event = \report_coursemanager\event\course_dashboard_viewed::create($eventparams);
$event->trigger();

unset($_GET);

echo $OUTPUT->footer();
