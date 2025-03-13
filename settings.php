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
    // Settings for reports calculation.
    $settings->add(new admin_setting_heading('reportssettingsheading',
            get_string('reportssettingsheading', 'report_coursemanager'), ''));

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

    // Defines other teacher roles to consider when counting the number of teachers in a course.
    $rolesoptions = role_fix_names(get_all_roles(), null, ROLENAME_ORIGINALANDSHORT, true);
    $teachers = get_archetype_roles('teacher');
    $settings->add(
        new admin_setting_configmultiselect('report_coursemanager/other_teacher_role_dashboard',
            get_string('otherteacherroledashboard', 'report_coursemanager'),
            get_string('otherteacherroledashboard_desc', 'report_coursemanager'),
            [''],
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

    // Settings for teachers dashboard.
    $settings->add(new admin_setting_heading('dashboardsettingsheading',
    get_string('dashboardsettingsheading', 'report_coursemanager'), ''));

    // Checkbox for adding an icon link in navbar.
    $name = 'report_coursemanager/navbar_link';
    $title = get_string('navbarlink', 'report_coursemanager');
    $description = get_string('navbarlink_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configcheckbox($name, $title, $description, 0));

    // Checkbox for cohorts column.
    $name = 'report_coursemanager/enable_column_cohorts';
    $title = get_string('enablecolumncohorts', 'report_coursemanager');
    $description = get_string('enablecolumncohorts_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configcheckbox($name, $title, $description, 1));

    // Checkbox for students column.
    $name = 'report_coursemanager/enable_column_students';
    $title = get_string('enablecolumnstudents', 'report_coursemanager');
    $description = get_string('enablecolumnstudents_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configcheckbox($name, $title, $description, 1));

    // Checkbox for teachers column.
    $name = 'report_coursemanager/enable_column_teachers';
    $title = get_string('enablecolumnteachers', 'report_coursemanager');
    $description = get_string('enablecolumnteachers_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configcheckbox($name, $title, $description, 1));

    // Checkbox for course size column.
    $name = 'report_coursemanager/enable_column_coursesize';
    $title = get_string('enablecolumncoursesize', 'report_coursemanager');
    $description = get_string('enablecolumncoursesize_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configcheckbox($name, $title, $description, 1));

    // Checkbox for coursesize comparison column.
    $name = 'report_coursemanager/enable_column_comparison';
    $title = get_string('enablecolumncomparison', 'report_coursemanager');
    $description = get_string('enablecolumncomparison_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configcheckbox($name, $title, $description, 1));

    // Define agregation for course size comparison.
    $aggregationchoices = [
        get_string('aggregationaverage', 'report_coursemanager'),
        get_string('aggregationmedian', 'report_coursemanager'),
        get_string('aggregationboth', 'report_coursemanager'),
    ];
    $name = 'report_coursemanager/aggregation_choice';
    $title = get_string('aggregationchoice', 'report_coursemanager');
    $description = get_string('aggregationchoice_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configselect($name, $title, $description, null, $aggregationchoices));

    $settings->add(new admin_setting_description('report_coursemanager/actionsmenuinfo',
        get_string('configmenuactions', 'report_coursemanager'),
        get_string('configmenuactions_desc', 'report_coursemanager'))
    );

    // Checkbox for action - View coursefile page.
    $name = 'report_coursemanager/enable_action_coursefiles';
    $title = get_string('enableactioncoursefiles', 'report_coursemanager');
    $settings->add(new admin_setting_configcheckbox($name, $title, '', 1));

    // Checkbox for action - Reset page.
    $name = 'report_coursemanager/enable_action_reset';
    $title = get_string('enableactionreset', 'report_coursemanager');
    $settings->add(new admin_setting_configcheckbox($name, $title, '', 1));

    // Checkbox for action - Unenroll cohorts page.
    $name = 'report_coursemanager/enable_action_cohorts';
    $title = get_string('enableactioncohorts', 'report_coursemanager');
    $settings->add(new admin_setting_configcheckbox($name, $title, '', 1));

    // Checkbox for action - Link to course params page.
    $name = 'report_coursemanager/enable_action_params';
    $title = get_string('enableactionparams', 'report_coursemanager');
    $settings->add(new admin_setting_configcheckbox($name, $title, '', 1));

    // Settings for trash category.
    $settings->add(new admin_setting_heading('trashsettingsheading',
            get_string('trashsettingsheading', 'report_coursemanager'), ''));

    // Define trash category.
    $displaylist = core_course_category::make_categories_list();
    $name = 'report_coursemanager/category_bin';
    $title = get_string('category_bin', 'report_coursemanager');
    $description = get_string('category_bin_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configselect($name, $title, $description, null, $displaylist));

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

    // Reports displaying and availability.
    $settings->add(new admin_setting_heading('reportsheading',
            get_string('reportsheading', 'report_coursemanager'), ''));

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

    // Checkbox for enabling course content task (heavy and empty courses).
    $name = 'report_coursemanager/enable_course_content_task';
    $title = get_string('enablecoursecontenttask', 'report_coursemanager');
    $description = get_string('enablecoursecontenttask_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configcheckbox($name, $title, $description, 0));

    // Checkbox for enabling teachers task (courses without teachers or without teachers visits).
    $name = 'report_coursemanager/enable_teachers_task';
    $title = get_string('enableteacherstask', 'report_coursemanager');
    $description = get_string('enableteacherstask_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configcheckbox($name, $title, $description, 0));

    // Checkbox for enabling students task (courses without students or without students visits).
    $name = 'report_coursemanager/enable_students_task';
    $title = get_string('enablestudentstask', 'report_coursemanager');
    $description = get_string('enablestudentstask_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configcheckbox($name, $title, $description, 0));

    // Checkbox for enabling orphan submissions task.
    $name = 'report_coursemanager/enable_orphans_task';
    $title = get_string('enableorphanstask', 'report_coursemanager');
    $description = get_string('enableorphanstask_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configcheckbox($name, $title, $description, 0));

    // Settings for teachers mailing.
    $settings->add(new admin_setting_heading('mailingheading',
            get_string('mailingheading', 'report_coursemanager'), ''));

    // Checkbox for enabling reports mailing.
    $name = 'report_coursemanager/enable_mailing';
    $title = get_string('enablemailing', 'report_coursemanager');
    $description = get_string('enablemailing_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configcheckbox($name, $title, $description, 0));

    // Subject of the email for report mailing.
    $name = 'report_coursemanager/mailing_title';
    $title = get_string('mailingtitle_setting', 'report_coursemanager');
    $description = get_string('mailingtitle_setting_desc', 'report_coursemanager');
    $default = get_string('mailingtitle', 'report_coursemanager');
    $settings->add(new admin_setting_configtext($name, $title, $description, $default, PARAM_TEXT, '50'));

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

    // Outro for report mailing.
    $name = 'report_coursemanager/mailing_outro';
    $title = get_string('mailingoutro_setting', 'report_coursemanager');
    $description = get_string('mailingoutro_setting_desc', 'report_coursemanager');
    $settings->add(
        new admin_setting_configtextarea(
            $name,
            $title,
            $description,
            get_string('mailingoutro', 'report_coursemanager'),
            PARAM_RAW
        )
    );
}

$ADMIN->add('reports', new admin_externalpage('report_coursemanager',
        get_string('pluginname', 'report_coursemanager'),
        new moodle_url('/report/coursemanager/admin_dashboard/index.php')));
