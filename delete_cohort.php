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
 * Form to bulk unenroll cohorts.
 *
 * @package     report_coursemanager
 * @copyright   2022 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/enrollib.php');
global $COURSE, $DB, $USER, $CFG;

$courseid = optional_param('id', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login();
require_capability('enrol/cohort:config', $context);

// Get site infos.
$site = get_site();

// Page settings.
$PAGE = new moodle_page();
$PAGE->set_context($context);
$PAGE->set_heading($site->fullname);

$PAGE->set_url('/report/coursemanager/delete_cohort.php');
$PAGE->set_pagelayout('mycourses');
$PAGE->set_pagetype('report-coursemanager');

$PAGE->blocks->add_region('content');
$PAGE->set_title($site->fullname);

// First, retrieve all enrollment instances.
$instances = enrol_get_instances($course->id, false);
$plugins   = enrol_get_plugins(false);

// Start to count.
$count = 0;
// We count all cohort enrollments.
foreach ($instances as $instance) {
    if ($instance->enrol == 'cohort') {
        $plugin = $plugins[$instance->enrol];
        $count = $count + 1;
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('title_delete_cohort_confirm', 'report_coursemanager'));

// If no cohort enrolled, warning and back button.
if ($count == 0) {
    echo html_writer::tag('p', get_string('no_cohort', 'report_coursemanager'));
    print html_writer::div('
    <div class="btn btn-outline-info"><a href="view.php">
    <i class="fa fa-arrow-left"></i>  '.get_string('back').'</a></span></div><br /><br />
    ');
} else if (!$confirm) {
    // If cohort detected, check if unenrollment is confirmed.
    // If not confirmed : add explanations.
    print html_writer::div('
    <div class="btn btn-outline-info"><a href="view.php">
    <i class="fa fa-arrow-left"></i>  '.get_string('back').'</a></div><br /><br />
    ');
    echo $OUTPUT->box_start('generalbox', 'notice');
    echo html_writer::tag('p', get_string('delete_cohort_confirm', 'report_coursemanager'));

    $urlconfirmdelete = new moodle_url('delete_cohort.php', ['confirm' => 1, 'id' => $courseid, 'sesskey' => sesskey()]);
    echo html_writer::div(html_writer::link($urlconfirmdelete,
    get_string('button_delete_cohort_confirm', 'report_coursemanager'), ['class' => 'text-white']), 'btn btn-info') . " ";

    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
} else if ($confirm) {
    // Before delete, check sesskey.
    require_sesskey();
    // If confirmed, all cohort enrollment are deleted.
    foreach ($instances as $instance) {
        if ($instance->enrol == 'cohort') {
            $plugin->delete_instance($instance);
        }
    }

    // Add event for cohort unenrollment.
    $context = context_course::instance($course->id);
    $eventparams = ['context' => $context, 'courseid' => $courseid];
    $event = \report_coursemanager\event\course_cohort_unenrolled::create($eventparams);
    $event->trigger();

    $url = new moodle_url('view.php', ['done' => 'cohort_deleted']);
    redirect($url);
}
