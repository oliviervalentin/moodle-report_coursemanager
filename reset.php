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
 * Reset page, based on native reset course page. Elements are prechecked to automatically reset course from heaviest elements.
 *
 * @package    report_coursemanager
 * @copyright  2022 Olivier VALENTIN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once('reset_form.php');
require_once($CFG->libdir . '/enrollib.php');
global $COURSE, $DB, $USER, $CFG;

require_login();

$id = required_param('id', PARAM_INT);
$context = context_course::instance($id, MUST_EXIST);

require_capability('moodle/course:reset', $context);

if (!$course = $DB->get_record('course', ['id' => $id])) {
    throw new moodle_exception('invalidcourseid');
}

$strresetcourse = get_string('resetcourse');

// Get site infos.
$site = get_site();

// Page settings.
$PAGE = new moodle_page();
$PAGE->set_heading($site->fullname);

$PAGE->set_url('/report/coursemanager/reset.php', ['id' => $id]);
$PAGE->set_pagelayout('mycourses');
$PAGE->set_pagetype('report-coursemanager');

$PAGE->blocks->add_region('content');
$PAGE->set_title($site->fullname);

$mform = new report_coursemanager_reset_form(null, ['courseid' => $id]);

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot.'/report/coursemanager/view.php');
} else if ($data = $mform->get_data()) {

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strresetcourse);

    $data->reset_start_date_old = $course->startdate;
    $data->reset_end_date_old = $course->enddate;
    $status = reset_course_userdata($data);

    // Get enroll instances.
    $instances = enrol_get_instances($course->id, false);
    $plugins   = enrol_get_plugins(false);

    // Delete only cohort enrollment methods.
    foreach ($instances as $instance) {
        if ($instance->enrol == 'cohort') {
            $plugin = $plugins[$instance->enrol];
            $plugin->delete_instance($instance);
        }
    }

    $data = [];

    foreach ($status as $item) {
        $line = [];
        $line[] = $item['component'];
        $line[] = $item['item'];
        $line[] = ($item['error'] === false) ? get_string('ok') : '<div class="notifyproblem">'.$item['error'].'</div>';
        $data[] = $line;
    }

    echo html_writer::div(get_string('reset_result', 'report_coursemanager'), 'alert alert-success');

    echo $OUTPUT->continue_button('view.php');
    echo $OUTPUT->footer();

    // Add event for course resetting.
    $context = context_course::instance($course->id);
    $eventparams = ['context' => $context, 'courseid' => $course->id];
    $event = \report_coursemanager\event\course_global_reset::create($eventparams);
    $event->trigger();

    exit;
}

echo $OUTPUT->header();
echo $OUTPUT->heading($strresetcourse);
echo html_writer::div(get_string('reset_info', 'report_coursemanager'));
$mform->display();
echo $OUTPUT->footer();
