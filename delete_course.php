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
 * Form to ask course deletion.
 *
 * @package     report_coursemanager
 * @copyright   2022 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/course/lib.php');

global $COURSE, $DB, $USER, $CFG;

require_login();

$courseid = optional_param('courseid', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$context = context_course::instance($courseid, MUST_EXIST);

require_capability('moodle/course:update', $context);
// Get site infos.
$site = get_site();

// Page settings.
$PAGE = new moodle_page();
$PAGE->set_context($context);
$PAGE->set_heading($site->fullname);

$PAGE->set_url('/report/coursemanager/delete_course.php');
$PAGE->set_pagelayout('mycourses');
$PAGE->set_pagetype('report-coursemanager');

$PAGE->blocks->add_region('content');
$PAGE->set_title($site->fullname);

$infocourse = $DB->get_record('course', ['id' => $courseid]);

$a = new stdClass();
$a->delete_period = get_config('report_coursemanager', 'delete_period');

// Get all users enrolled as teacher in course.
$allteachersconfig = explode(',', get_config('report_coursemanager', 'delete_send_mail'));
$allteachers = [];
if (!empty(get_config('report_coursemanager', 'delete_send_mail'))) {
    foreach ($allteachersconfig as $teacher) {
        $allteachers = $allteachers + get_role_users($teacher, $context);
    }
}

// If not yet confirm.
if (!$confirm) {
    echo $OUTPUT->header();
    // Add back button.
    print html_writer::div('
    <div class="btn btn-outline-info"><a href="view.php">
    <i class="fa fa-arrow-left"></i>  '.get_string('back').'</a></div><br /><br />
    ');

    echo $OUTPUT->heading(get_string('title_move_confirm', 'report_coursemanager')." ".$infocourse->fullname);
    if ($infocourse->category == get_config('report_coursemanager', 'category_bin')) {
        echo html_writer::tag('h5', get_string('delete_already_moved'), ['class' => 'alert alert-warning']);
        echo $OUTPUT->footer();
        exit();
    }

    // Text to inform about this function.
    echo html_writer::div(get_string('move_confirm', 'report_coursemanager', $a));
    if (count($allteachers) > 1) {
        $textwarnseveralteachers = get_string('delete_several_teachers', 'report_coursemanager');
        $textwarnseveralteachers .= "<ul>";

        $listteachers = '';
        foreach ($allteachers as $teacher) {
            $listteachers .= '<li>'.$teacher->firstname.' '.$teacher->lastname. '</li>';
        }
        $textwarnseveralteachers .= $listteachers;
        $textwarnseveralteachers .= "</ul>";
        echo html_writer::div($textwarnseveralteachers, 'alert alert-danger');
    }

    // Add choices : delete course, or direct links to save questions bank or full course.
    echo html_writer::tag('h5', get_string('delete_wish', 'report_coursemanager'),
    ['class' => 'alert alert-warning']);
    $urlconfirmdelete = new moodle_url('delete_course.php',
    ['confirm' => 1, 'courseid' => $courseid, 'sesskey' => sesskey()]);
    echo html_writer::div(html_writer::link($urlconfirmdelete, get_string('button_move_confirm', 'report_coursemanager'),
    ['class' => 'text-white']), 'btn btn-warning') . " ";
    $urlquestionbank = new moodle_url('/question/bank/exportquestions/export.php',
    ['courseid' => $courseid]);
    echo html_writer::div(html_writer::link($urlquestionbank, get_string('button_save_questionbank', 'report_coursemanager'),
    ['class' => 'text-white']), 'btn btn-info') . " ";
    $urlbackupcourse = new moodle_url('/backup/backup.php',
    ['id' => $courseid]);
    echo html_writer::div(html_writer::link($urlbackupcourse, get_string('button_save_course', 'report_coursemanager'),
    ['class' => 'text-white']), 'btn btn-info');

    echo $OUTPUT->footer();

} else if ($confirm) {
    // If confirmed : course is moved in trash category.
    require_sesskey();
    move_courses([$courseid], get_config('report_coursemanager', 'category_bin'));

    // Course parameters updated : course is hidden.
    $datahide = new stdClass;
    $datahide->id = $courseid;
    $datahide->visible = 0;
    $hide = $DB->update_record('course', $datahide);

    // Define informations for mail.
    $a = new stdClass;
    $a->course = $infocourse->fullname;
    $a->count_teacher = count($allteachers);
    $a->delete_period = get_config('report_coursemanager', 'delete_period');
    $subject = get_string('mail_subject_delete', 'report_coursemanager', $a);

    $from = new stdClass;
    $from->email = $CFG->supportname;
    $from->maildisplay = false;

    // Send a message to teacher(s).
    // If only one teacher : send mail for the only teacher in course.
    if (count($allteachers) == 1) {
        $message = get_string('mail_message_delete_oneteacher', 'report_coursemanager', $a);
        $send = email_to_user($USER, $from, $subject, $message);
    } else {
        // If multiple teachers : send 2 different mails.
        foreach ($allteachers as $teacher) {
            if ($teacher->email == $USER->email) {
                // Mail for teacher who deletes course.
                $message = get_string('mail_message_delete_main_teacher', 'report_coursemanager', $a);
                $send = email_to_user($USER, $from, $subject, $message);
            } else {
                // Mail for other teachers to warn them.
                $a->deleter = $USER->firstname." ".$USER->lastname;
                $message = get_string('mail_message_delete_other_teacher', 'report_coursemanager', $a);
                $send = email_to_user($teacher, $from, $subject, $message);
            }
        }
    }

    // Add event for deletion.
    $context = context_course::instance($courseid);
    $eventparams = ['context' => $context, 'courseid' => $courseid];
    $event = \report_coursemanager\event\course_trash_moved::create($eventparams);
    $event->trigger();

    $url = new moodle_url('view.php', ['done' => 'course_deleted']);
        redirect($url);
}
