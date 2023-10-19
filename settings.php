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
 * Specific settings for Lyon 3 template.
 *
 * @package    report_coursemanager
 * @copyright  2022 Olivier VALENTIN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die();      
                                                                                    
if ($ADMIN->fulltree) {                                                                                                                                                                                                  
	// Show in navigation ?
    $name = 'report_coursemanager/teachertools';
    $title = get_string('enableteachertools', 'report_coursemanager');
    $description = get_string('enableteachertools_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configcheckbox($name, $title, $description, 'enableteachertools'));

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
	
    // Define if reports are displayed in courses and which place to use.
	$show_report_in_course_choices = array(
	    get_string('show_report_in_course_choices_none', 'report_coursemanager'),
		get_string('show_report_in_course_choices_collapse', 'report_coursemanager'),
		get_string('show_report_in_course_choices_popover', 'report_coursemanager')
	);
    $name = 'report_coursemanager/show_report_in_course';
    $title = get_string('show_report_in_course', 'report_coursemanager');
    $description = get_string('show_report_in_course_desc', 'report_coursemanager');
    $settings->add(new admin_setting_configselect($name, $title, $description, null, $show_report_in_course_choices));

}

// $ADMIN->add('reports', new admin_externalpage('report_coursemanager',
        // get_string('pluginname', 'report_coursemanager'),
        // new moodle_url('/report/coursemanager/view.php')));
