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
 * Settings for plugin Course Manager.
 *
 * @package    report_coursemanager
 * @copyright  2022 Olivier VALENTIN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // Define which role is defined as teacher in courses to retrieve course list on dashboard.
    $rolesoptions = role_fix_names(get_all_roles(), null, ROLENAME_ORIGINALANDSHORT, true);
    $editingteachers = get_archetype_roles('editingteacher');
    $settings->add(
        new admin_setting_configselect('report_coursemanager/teacher_role_dashboard',
            get_string('teacherroledashboard', 'report_coursemanager'),
            get_string('teacherroledashboard_desc', 'report_coursemanager'),
            '3',
            $rolesoptions
        )
    );

    // Define which role is defined as student in courses to calculate reports.
    $rolesoptions = role_fix_names(get_all_roles(), null, ROLENAME_ORIGINALANDSHORT, true);
    $students = get_archetype_roles('student');
    $settings->add(
        new admin_setting_configselect('report_coursemanager/student_role_report',
            get_string('studentrolereport', 'report_coursemanager'),
            get_string('studentrolereport_desc', 'report_coursemanager'),
            '5',
            $rolesoptions
        )
    );

    // Define trash category.
    $displaylist = core_course_category::make_categories_list();
    $name = 'report_coursemanager/category_bin';
    $title = get_string('category_bin', 'report_coursemanager');
    $description = get_string('category_bin_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configselect($name, $title, $description, null, $displaylist));

    // Limit for total course files size before warning.
    $name = 'report_coursemanager/total_filesize_threshold';
    $title = get_string('total_filesize_threshold', 'report_coursemanager');
    $description = get_string('total_filesize_threshold_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configtext($name, $title, $description, null, PARAM_TEXT, '5'));

    // Limit for single file size  before warning.
    $name = 'report_coursemanager/unique_filesize_threshold';
    $title = get_string('unique_filesize_threshold', 'report_coursemanager');
    $description = get_string('unique_filesize_threshold_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configtext($name, $title, $description, null, PARAM_TEXT, '5'));

    // Number of days without teacher visit before warn.
    $name = 'report_coursemanager/last_access_teacher';
    $title = get_string('last_access_teacher', 'report_coursemanager');
    $description = get_string('last_access_teacher_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configtext($name, $title, $description, null, PARAM_TEXT, '5'));

    // Number of days without student visit before warn.
    $name = 'report_coursemanager/last_access_student';
    $title = get_string('last_access_student', 'report_coursemanager');
    $description = get_string('last_access_student_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configtext($name, $title, $description, null, PARAM_TEXT, '5'));

    // Information text when courses in trash category will be deleted.
    $name = 'report_coursemanager/delete_period';
    $title = get_string('delete_period', 'report_coursemanager');
    $description = get_string('delete_period_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configtext($name, $title, $description, null, PARAM_TEXT, '50'));

    // Roles that should receive the warning email when deleting a course.
    $settings->add(new admin_setting_configmultiselect('report_coursemanager/delete_send_mail',
            get_string('delete_send_mail', 'report_coursemanager'),
            get_string('delete_send_mail_desc', 'report_coursemanager'),
            ['3'], $rolesoptions)
    );

    // Checkbox for enabling reports mailing.
    $name = 'report_coursemanager/enable_mailing';
    $title = get_string('enablemailing', 'report_coursemanager');
    $description = get_string('enablemailing_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configcheckbox($name, $title, $description, 0));

    // Introduction for report mailing.
    $name = 'report_coursemanager/mailing_introduction';
    $title = get_string('mailingintro_setting', 'report_coursemanager');
    $description = get_string('mailingintro_setting_desc', 'report_coursemanager');
    $settings->add(
        new admin_setting_configtextarea(
            $name,
            $title,
            $description,
            get_string('mailingintro', 'report_coursemanager'),
            PARAM_RAW
        )
    );

    // Define if reports are displayed in courses and which place to use.
    $showreportincoursechoices = [
        get_string('show_report_in_course_choices_none', 'report_coursemanager'),
        get_string('show_report_in_course_choices_collapse', 'report_coursemanager'),
        get_string('show_report_in_course_choices_popover', 'report_coursemanager'),
    ];

    $name = 'report_coursemanager/show_report_in_course';
    $title = get_string('show_report_in_course', 'report_coursemanager');
    $description = get_string('show_report_in_course_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configselect($name, $title, $description, null, $showreportincoursechoices));
}

$ADMIN->add('reports', new admin_externalpage('report_coursemanager',
        get_string('pluginname', 'report_coursemanager'),
        new moodle_url('/report/coursemanager/admin_dashboard/index.php')));
