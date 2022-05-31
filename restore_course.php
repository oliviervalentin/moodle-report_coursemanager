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
require_once($CFG->dirroot.'/course/lib.php');
require_once(__DIR__.'/lib.php');
global $COURSE, $DB, $USER, $CFG;

require_login();

$id = required_param('courseid', PARAM_INT);
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
$PAGE->set_context($context);
$PAGE->set_heading($site->fullname);

$PAGE->set_url('/report/coursemanager/reset.php', array('id'=>$id));
$PAGE->set_pagelayout('mycourses');
$PAGE->set_pagetype('teachertools');

$PAGE->blocks->add_region('content');
$PAGE->set_title($site->fullname);
$PAGE->set_secondary_navigation(false);

$infocourse = $DB->get_record('course', array('id' => $id));

$a = new stdClass();
$a->delete_period = get_config('report_coursemanager', 'delete_period');
$name_trash = $DB->get_record('course_categories', array("id" => get_config('report_coursemanager', 'category_bin')));
// print_object($name_trash);
$a->trash_category = $name_trash->name;

$post = new stdClass();
$post->courseid = $id;

$formarray = array(
	'post' => $post
);

$mform = new form_restore('restore_course.php', $formarray, 'post');

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot.'/report/coursemanager/view.php');

} else if ($data = $mform->get_data()) { // no magic quotes
    $moveit = \core_course\management\helper::move_courses_into_category($data->restore_category,
        array('id' => $data->courseid));

	// Add event for course resetting.
	$context = context_course::instance($data->courseid);
	$eventparams = array('context' => $context, 'courseid' => $data->courseid);
	$event = \report_coursemanager\event\course_restored::create($eventparams);
	$event->trigger();

	$url = new moodle_url('view.php', array('done' => 'course_restored'));
	redirect($url);

    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('title_restore_confirm', 'report_coursemanager')." ".$infocourse->fullname." V2");

if($infocourse->category != get_config('report_coursemanager', 'category_bin')) {
	echo html_writer::tag('h5', get_string('restore_already_moved', 'report_coursemanager'), array('class' => 'alert alert-warning'));
} else {
    echo html_writer::div(get_string('restore_confirm', 'report_coursemanager', $a));
    $mform->display();
}
echo $OUTPUT->footer();
