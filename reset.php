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
 * Reset page, created from the native reset course page. Elements
 * are prechecked to automatically reset course from heaviest elements.
 *
 * @copyright Mark Flach and moodle.com - Olivier VALENTIN
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package     report_coursemanager
 */

require_once(__DIR__ . '/../../config.php');
require_once('reset_form.php');
require_once($CFG->libdir . '/enrollib.php');
global $COURSE, $DB, $USER, $CFG;

require_login();

$id = required_param('id', PARAM_INT);
$context = context_course::instance($id, MUST_EXIST);

require_capability('moodle/course:reset', $context);

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error("invalidcourseid");
}

$strresetcourse = get_string('resetcourse');

// Get site infos.
$site = get_site();

// Page settings
$PAGE = new moodle_page();
// $PAGE->set_context($context);
$PAGE->set_heading($site->fullname);

$PAGE->set_url('/report/coursemanager/reset.php', array('id'=>$id));
$PAGE->set_pagelayout('mycourses');
$PAGE->set_pagetype('teachertools');

$PAGE->blocks->add_region('content');
$PAGE->set_title($site->fullname);
// $PAGE->set_secondary_navigation(false);

$mform = new course_reset_form(null, array('courseid' => $id));

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot.'/report/coursemanager/view.php');

} else if ($data = $mform->get_data()) { // no magic quotes

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strresetcourse);

    $data->reset_start_date_old = $course->startdate;
    $data->reset_end_date_old = $course->enddate;
    $status = reset_course_userdata($data);
    
    // Get enroll instances.
    $instances = enrol_get_instances($course->id, false);
    $plugins   = enrol_get_plugins(false);

    // Delete only cohort enrollment methods.
    foreach($instances as $instance){
        if ($instance->enrol == 'cohort') {
            $plugin = $plugins[$instance->enrol];
            $plugin->delete_instance($instance);
        }
    }
    
    $data = array();
    
    foreach ($status as $item) {
        $line = array();
        $line[] = $item['component'];
        $line[] = $item['item'];
        $line[] = ($item['error']===false) ? get_string('ok') : '<div class="notifyproblem">'.$item['error'].'</div>';
        $data[] = $line;
    }

    echo html_writer::div(get_string('reset_result', 'report_coursemanager'), 'alert alert-success');

    echo $OUTPUT->continue_button('view.php');  // Retour accueil.
    echo $OUTPUT->footer();

    // Add event for course resetting.
    $context = context_course::instance($course->id);
    $eventparams = array('context' => $context, 'courseid' => $course->id);
    $event = \report_coursemanager\event\course_global_reset::create($eventparams);
    $event->trigger();

    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading($strresetcourse);
echo html_writer::div(get_string('reset_info', 'report_coursemanager'));
$mform->display();
echo $OUTPUT->footer();
