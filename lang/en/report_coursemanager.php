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
 * Course Manager plugin strings.
 *
 * @package     report_coursemanager
 * @copyright   2022 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Course manager';
$string['title'] = 'Course Manager for teachers';

// Settings page.
$string['configtitle'] = 'Course Manager settings';
$string['category_bin'] = 'Category for bin';
$string['teacherroledashboard'] = 'Teacher role in courses';
$string['teacherroledashboard_desc'] = 'Defines teacher role that can display course list in Course Manager dashabord. Default role is Moodle teacher.';
$string['studentrolereport'] = 'Student role in courses';
$string['studentrolereport_desc'] = 'Defines student role for reports calculation. Default role is Moodle student.';
$string['category_bin_desc'] = 'If teacher deletes a course through dashboard, it is moved in this category before real deletion by admin.';
$string['total_filesize_threshold'] = 'Max course size (Mo)';
$string['total_filesize_threshold_desc'] = 'If total files size exceeds this limit, a report will be calculated for concerned courses.';
$string['unique_filesize_threshold'] = 'Max file size (Mo)';
$string['unique_filesize_threshold_desc'] = 'If a file size exceeds this limit, it will be displayed in the list of heavy files in total course files report.';
$string['last_access_teacher'] = 'Limit for last teacher access (in days)';
$string['last_access_teacher_desc'] = 'Number of days since teacher didn\'t visit a course';
$string['last_access_student'] = 'Limit for last student access (in days)';
$string['last_access_student_desc'] = 'mber of days since student didn\'t visit a course';
$string['delete_period'] = 'Time indication for course deletion';
$string['delete_period_desc'] = 'This information gives an approximative period for course deletion from bin category.
 You can write something like "in the beginning of july" or "at the end of the year". This information will be displayed in the mail when teacher deletes a course and in course deletion page.';
$string['delete_send_mail'] = 'Roles to prevent when deleting a course';
$string['delete_send_mail_desc'] = 'Select the role(s) that will be notified by email when a course is deleted';
$string['show_report_in_course'] = 'Report display in course';
$string['show_report_in_course_desc'] = 'Defines place in course where reports are displayed';
$string['show_report_in_course_choices_none'] = 'Do not display reports in course';
$string['show_report_in_course_choices_collapse'] = 'Scrolling menu under course admin menu';
$string['show_report_in_course_choices_popover'] = 'Icons next to course title';
$string['enablemailing'] = 'Activate reports mailing';
$string['enablemailing_desc'] = 'If checked, activates automated task for reports mailing. Default periodicity is every 30 days.';
$string['enablecoursecontenttask'] = 'Activate empty or heavy courses reports';
$string['enablecoursecontenttask_desc'] = 'If checked, activates automated task to detect heavy courses or courses without contents.';
$string['enableteacherstask'] = 'Activate reports concerning teachers visits and enrolments';
$string['enableteacherstask_desc'] = 'If checked, activates automated task to detect courses without teachers or without recent teachers visits.';
$string['enablestudentstask'] = 'Activate reports concerning students visits and enrolments';
$string['enablestudentstask_desc'] = 'If checked, activates automated task to detect courses without students or without recent students visits.';
$string['enableorphanstask'] = 'Activate orphaned submissions report';
$string['enableorphanstask_desc'] = 'If checked, activates automated task to detect assigns with orphaned files.';
$string['mailingintro_setting'] = 'Email content';
$string['mailingintro_setting_desc'] = 'Introduction of the report mailing.<br>
You can use the following variables :<br>
%userfirstname% : User\'s first name<br>
%userlastname% : User\'s last name<br>
%coursemanagerlink% : Course Manager\'s link';

// Headings for settings page.
$string['reportssettingsheading'] = 'Settings for reports calculation';
$string['trashsettingsheading'] = 'Settings for trash category and courses deletion';
$string['reportsheading'] = 'Reports';
$string['mailingheading'] = 'Teachers mailing';

// Banner for courses in category bin.
$string['trash'] = 'This course is in the category for deleted courses.';

// Dashboard.
$string['table_course_name'] = 'Course name';
$string['table_course_state'] = 'Visibility';
$string['table_files_weight'] = 'Total files size';
$string['table_enrolled_cohorts'] = 'Cohorts';
$string['table_enrolled_students'] = 'Students';
$string['table_enrolled_teachers'] = 'Teachers';
$string['table_recommendation'] = 'Reports';
$string['table_actions'] = 'Actions';
$string['empty_settings'] = 'Some settings are missing for this plugin ; please contact your Moodle admin';

// Actions.
$string['menudeletecourse'] = 'Delete course';
$string['menucoursefilesinfo'] = 'See files in course';
$string['menureset'] = 'Reset course';
$string['menuenrolcohorts'] = 'Add cohorts';
$string['menuunenrolcohorts'] = 'Bulk unenroll cohorts';
$string['menucourseparameters'] = 'Course settings';
$string['menurestorecourse'] = 'Restore course';

// Alerts and reports on dashboard.
$string['course_state_visible'] = 'Visible';
$string['course_state_hidden'] = 'Hidden';
$string['course_state_trash'] = 'To delete';
$string['see_advices'] = 'Reports summary';
$string['advices_for_course'] = 'recommendations ';
$string['total_filesize_alert'] = '<b>This course is heavy.</b><br />See <a href="course_files.php?courseid={$a->courseid}">course files report</a> to see heaviest files.';
$string['empty_course_alert'] = '<b>Empty course.</b>This course only contains native forum. If no content will be added, think about deleting this course.';
$string['last_access_multiple_teacher_alert'] = '<b>No teacher visit since {$a->limit_visit} months.</b><br />No teacher has visited this course for a long time. If no teacher uses it, think about deleting it !';
$string['last_access_unique_teacher_alert'] = '<b>You didn\' visit this course since {$a->limit_visit} months.</b><br />You are the only teacher in this course. If you don\'t use it anymore, think about deleting it.';
$string['last_access_student_alert'] = '<b>No student visit since {$a->limit_visit} months.</b><br />No students have visited this course since a long time. If necessary, delete it.';
$string['empty_student_alert'] = '<b>No student enrolled</b><br />There are no users enrolled as student in this course. If it isn\'t used anymore, think about delete it !';
$string['orphan_submissions_alert'] = '<b>Orphan submissions</b><br />This course contains {$a->assigns} Assigns containing {$a->filescount} files submitted by now unenrolled students. Those files represent {$a->filesize} Mo. Please remind to reset or delete these activities.';
$string['no_advices'] = '<b>No sepecific reports.</b><br />Congratulations, this course seems ok !';
$string['no_course_to_show'] = '<h2>No courses</h2>You are not enrolled in courses with a teacher role.';
$string['closereportmodal'] = 'Close window';

// Page for course deletion.
$string['title_move_confirm'] = 'Course deletion';
$string['move_confirm'] = '
<div class="alert alert-danger"><h5>read carefully before deleting</h5></div>
<p><b>This page allows you to put in trash a course you don\'t use anymore.</b><br/>If confirmed, this course will be moved in a specific category and hidden to students. It will be then deleted by Moodle admin <b>{$a->delete_period}</b>.</p>
<p> Before deletion, you still can access this course, or restore it if needed. Use course restore action in Course Manager dashboard.</p>
<p> Before deletion, consider making a backup of important files or questions bank. Those data can\'t be retrieved after complete deletion.';
$string['delete_several_teachers'] = '<h4><i class="fa fa-exclamation-triangle"></i> WARNING : THERE ARE MULTIPLE TEACHERS IN THIS COURSE !</h4>
If these teachers are not informed, please do it now.<br/>Course deletion will send an <b>automatic mail alert for every teacher in this course</b> to warn them.<br /><br />
<h5>Other teachers in this course :</h5>';
$string['delete_wish'] = 'What do you want to do ?';
$string['button_move_confirm'] = 'Confirm deletion';
$string['button_save_questionbank'] = 'Backup questions bank';
$string['button_save_course'] = 'Backup course';
$string['mail_subject_delete'] = 'Course deleted - {$a->course}';
$string['mail_message_delete_oneteacher'] = 'Hello,<br />
Course {$a->course} has been moved in trash category before complete deletion in {$a->delete_period}. Before this,
you still can access this course if you wish to retrieve datas.<br />
If you want to restore this course, move it out of bin category in Course Manager dashboard.';
$string['mail_message_delete_main_teacher'] = 'hello,<br />
Course {$a->course} has been moved in trash category before complete deletion in {$a->delete_period}. Before this, you still can access this course if you wish to retrieve datas.<br />
If you want to restore this course, move it out of bin category in Course Manager dashboard.
NOTE : {$a->count_teacher} other teachers was enrolled. A mail was sent to warn them you have deleted this course. As they are teachers too, they can restore it or recover datas if needed.';
$string['mail_message_delete_other_teacher'] = 'Hello,<br />
Course {$a->course}, has been moved in trash category by {$a->deleter}, before complete deletion in {$a->delete_period}. Before this, you still can access this course if you wish to retrieve datas.<br />
If you want to restore this course, move it out of bin category in Course Manager dashboard.<br />';
$string['delete_already_moved'] = 'This course is already in bin category.';

// Page - Course restore.
$string['title_restore_confirm'] = 'Restore course';
$string['restore_confirm'] = '<p>This page will take a course out of bin category and restore it in an other category.</p>
<p class="alert alert-info"><i class="fa fa-info-circle"></i> NOTE : don\'t restore this course in  <b>"{$a->trash_category}"</b> : this category is reserved for courses to be deleted in <b>{$a->delete_period}</b>.</p>';
$string['button_restore_confirm'] = 'Confirm course restore';
$string['restore_already_moved'] = 'This course is not in bin category !.';
$string['error_category'] = 'Wrong category selected';
$string['select_restore_category'] = 'Choose category to move course';

// Page - Files information.
$string['coursesize'] = 'Course size';
$string['totalsize'] = 'Total files size : ';
$string['watchedfilessize'] = 'Total size for most watched files : ';
$string['watchedfilessizedetails'] = 'These files come from most used activities : Assign, Resource, Forum, Folder and Label.';
$string['plugin'] = 'Activity type';
$string['size'] = 'Size in Mo';
$string['comment'] = 'Recommandations';
$string['number_of_files'] = 'Number of files';
$string['warn_heavy_assign'] = 'Theses assigns represent a heavy files size :';
$string['warn_heavy_assign_help'] = '<b>Watch out these assigns</b> and consider resetting or deleting them when there won\'t be used anymore.';
$string['empty_files_course'] = 'This course seems to contain no files yet.';
$string['warn_big_files'] = 'These files have an <b>important weight</b> :';
$string['warn_big_files_help'] = 'If a file size is important, consider :
<ul>
<li>zipping it ;</li>
<li>if it contains pictures, try to decrease their resolutions ;</li>
<li>use an external repository if you can.</li>
<li></li>
</ul>';
$string['warn_videos'] = 'Those files are <b>videos</b>, consider move it :';
$string['warn_videos_help'] = 'Videos can be very heavy. If you can, please upload them on a specific service for videos (WebTV, YouTube...).';
$string['warn_orphans'] = 'These assigns contain <b>orphan submissions</b> :';
$string['warn_orphans_help'] = '<p>Files submitted bu unenrolled students are still here.</p>
<p>Please consider : <ul>
<li>deleting assigns with orphan submissions ;</li>
<li>resetting activities or the whole course.</li></ul></p>';
$string['global_chart'] = 'Distribution of file weights by activities';
$string['warn_recyclebin'] = '<p class="alert alert-info"><i class="fa fa-info-circle"></i> <b>Notice that</b> recycle bin is activated on Moodle. Deleted files
 will be included in this chart as long as teacher doesn\'t empty trash.</p>';

// Page for global reset.
$string['reset_info'] = '<p class="alert alert-success"><i class="fa fa-info-circle"></i> Reset function can delete students personal datas in your course.
 <b>It does not delete your files or activities</b></p>
<p>using this course reset will delete :<ul>
<li><b>completion datas</b> ;</li>
<li><b>grades in gradebook</b> ;</li>
<li><b>groups and groupings</b> ;</li>
<li><b>submissions in Assign activities</b> ;</li>
<li><b>forum messages</b> ;</li>
<li><b>quiz attempts</b> ;</li>
<li><b>enrolled cohorts</b> (<span class="text-danger"><b>WARNING</b> : this function can take a long time if there are many students !).</span></li></ul></p>
<p>No other datas will be deleted.</p>
<p>If you wish to reset other activities, please use <a href="/course/view.php?id="><b>reset function in your course</b></a>.<br /></p>
<h5 class="alert alert-primary"><i class="fa fa-question-circle-o"></i> <b>What do you want to do ?</b></h5>
';
$string['reset_result'] = '<p><b>Course has been reseted.</b></p>
<p><b>Reminder</b> : no files or activities have been deleted. If you want to reset all or other activities, please use reset function in course.</p>';

// Page of bulk unenroll cohorts.
$string['title_delete_cohort_confirm'] = 'Unenroll cohorts';
$string['delete_cohort_confirm'] = 'This function will unenroll all cohorts in your course.<br />
Please note that unenrolling cohorts will make disappear datas as forum messages, </b> but not quiz attemps or assigns submissions</b>.
 Please consider using reset function to delete personal datas.
<p class="alert alert-primary"><i class="fa fa-question-circle-o"></i> <b>WARNING :</b> depending on number of cohorts to be unenrolled, this process may take time.</p>
';
$string['button_delete_cohort_confirm'] = 'Unenroll cohorts';
$string['no_cohort'] = '<p class="alert alert-info">No cohort enrolled in this course !</p>';

// Admin dashboard page.
$string['admin_course_managerinfo'] = '<p>This page gives access to tool for Moodle Admin. For Course Manager settings, please go to Plugins > Reports > Course Manager.</p>';
$string['table_tool_name'] = '<p>Tool</p>';
$string['table_tool_description'] = '<p>Description</p>';

// Admin page for orphan submissions.
$string['title_admin_orphan_submissions'] = '<b>Manage orphan submissions</b>';
$string['table_assign_name'] = 'Assign name';
$string['table_files_count'] = 'Number of hidden files';
$string['admin_orphan_submissions_info'] = '<p>Orphan submissions are files submitted by students which are no longer enrolled in course.
 <b>Those files are unvisible for teachers, and can be seen only by re-enrolling students.</b> If assigns are used every year without being reseted, those hidden files
 can represent an important weight in Moodle.<br />
 This tool can delete these hidden files <b>without deleting files submitted by students which are still enrolled in course.<b></p>';
$string['deleteorphansubmissionsconfirm'] = 'Do you want to delete orphan submissions in this assign ? This action is irreversible.';
$string['noassign'] = 'There are no assign on this Moodle instance.';
$string['deleteorphans'] = 'Delete orphan submissions';

// Admin page for files distribution in files table.
$string['title_admin_files_distribution'] = '<b>Files distribution by component</b>';
$string['admin_files_distribution_info'] = '<p>This tool gives a report of the different components in file storage, and total size for each one.</p>';
$string['filesdistributiontablecomponent'] = 'Component';
$string['filesdistributiontotalweight'] = 'Total files size in Mo';
$string['filesdistributiontotalfiles'] = 'Number of files';

// Admin page for courses without teachers.
$string['title_admin_no_teacher_courses'] = '<b>Manage courses without teachers</b>';
$string['admin_no_teacher_courses_info'] = '<p>This tool lists all courses where no user is enrolled as teacher, and possibly move them in bin category if needed.</p>';
$string['adminnoteachercoursesnote'] = '<ul><li>Informations concerning course weight and number of activities are not calculated in real time.</li>
<li>Informations concerning last teacher log are based on edulevel filed in logstore database. Modified permissions can distort this result.</li></ul>';
$string['adminnoteachercoursesweight'] = '<ul class="alert alert-warning">The task for weight calculation is activated. Courses for which  weight has not been calculated yet will not appear in this list. If necessary, launch task manually or wait for next cron run.';
$string['tablecountenrolledstudents'] = 'Students';
$string['tablelastaccess'] = 'Last access in course';
$string['tablehascontents'] = 'Number of contents';
$string['tablecourseweight'] = 'Course weight';
$string['tablecountmodules'] = 'Numlber of activities';
$string['tablelastteacherlog'] = 'Last teacher log';
$string['tablelastteacher'] = 'Last active teacher';
$string['deletecoursewithoutteachersconfirm'] = 'Do you want to move this course in bin category ?';
$string['emptytablenoteacherincourses'] = 'No result : all courses have a teacher';

// Admin page for statistics.
$string['title_admin_stats'] = '<b>Statistics</b>';
$string['admin_stats_info'] = '<p>This page shows statistics most based on reports produced by Course Manager plugins. It also offers statistics on several aspects regarding Course Manager features, such as courses without teachers or courses in trash category.</p>';
$string['stats_title_courses'] = 'Courses Statistics';
$string['stats_count_courses'] = 'Courses count';
$string['stats_count_courses_desc'] = 'Number of courses on Moodle instance.';
$string['stats_count_courses_trash'] = 'Courses in trash';
$string['stats_count_courses_trash_desc'] = 'Count courses in Course Manager trash category.';
$string['stats_weight_courses_trash'] = 'Total Trash weight';
$string['stats_weight_courses_trash_desc'] = 'Total activities files size from courses in Course Manager trash.';
$string['stats_title_contents'] = 'Courses contents and weights';
$string['stats_heavy_courses'] = 'Heavy courses';
$string['stats_heavy_courses_desc'] = 'Courses weighing more than {$a->totalfilesizethreshold} Mo (threshold defined in Course Manager settings).';
$string['stats_empty_courses'] = 'Empty courses';
$string['stats_empty_courses_desc'] = 'Number of courses having only native Forum for activity.';
$string['stats_files_orphan_submissions'] = 'Total of orphan submissions files';
$string['stats_files_orphan_submissions_desc'] = 'Number of files considered as submissions belonging to unenrolled users.';
$string['stats_weight_courses_orphan_submissions'] = 'Weight for orphan submissions';
$string['stats_weight_courses_orphan_submissions_desc'] = 'Total filesize for orphan submissions';
$string['stats_heaviest_course'] = 'Heaviest course';
$string['stats_heaviest_course_desc'] = 'Heaviest course on Moodle instance, according to Course Manager reports.';
$string['stats_title_enrolls_visits'] = 'Courses enrollment and visits';
$string['stats_count_courses_without_teachers'] = 'Courses without teachers';
$string['stats_count_courses_without_teachers_desc'] = 'Number of courses without any users enrolled as teacher.';
$string['stats_count_courses_without_visit_teachers'] = 'Courses without teachers visits ';
$string['stats_count_courses_without_visit_teachers_desc'] = 'Number of courses without any teacher visit since {$a->lastaccessteacher} days.';
$string['stats_count_courses_without_students'] = 'Courses without students';
$string['stats_count_courses_without_students_desc'] = 'Number of courses without any users enrolled as students.';
$string['stats_count_courses_without_visit_students'] = 'Courses without students visits ';
$string['stats_count_courses_without_visit_students_desc'] = 'Number of courses without any student visit since {$a->lastaccessstudents} days.';

// Confirmation alert on dashboard.
$string['confirm_cohort_unenrolled_title'] = 'Cohorts deleted';
$string['confirm_cohort_unenrolled_message'] = 'All cohorts have been unenrolled from course.';
$string['confirm_course_deleted_title'] = 'Course deleted';
$string['confirm_course_deleted_message'] = 'Course has been moved in trash category and will be later deleted by admin ';
$string['confirm_course_restored_title'] = 'Course restored';
$string['confirm_course_restored_message'] = 'Course has been moved out of bin category. Note that a restored course in still hidden for students, consider changing settings if needed.';

// Events.
$string['course_dashboard_viewed'] = 'Teacher dashboard visited';
$string['course_trash_moved'] = 'Course moved in bin category';
$string['course_files_viewed'] = 'Files report page visited';
$string['course_global_reset'] = 'Course reseted with Course Manager function';
$string['course_cohort_unenrolled'] = 'Cohorts unenrolled';
$string['course_restored'] = 'Course restored out of bin category';

// Other.
$string['capability_problem'] = 'ou do not have permission to view this page.';
$string['unknown'] = 'Unknown';

// Dashboard filters.
$string['text_filter'] = 'Enter a few characters of the course name';
$string['all_courses'] = 'All courses';
$string['no_content'] = 'Empty courses';
$string['no_visit_student'] = 'No student visits';
$string['no_visit_teacher'] = 'No teacher visits';
$string['no_student'] = 'No students enrolled';
$string['heavy_course'] = 'Heavy courses';
$string['orphan_submissions_button'] = 'Orphan submissions';
$string['ok'] = 'No reports';

// Tasks.
$string['runreportstask'] = 'Reports calculation for Course Manager';
$string['mailingtask'] = 'Mailing reports for teachers for Course Manager';
$string['runorphansubmissionstask'] = 'Course Manager report for orphaned submissions';
$string['runcoursecontentreporttask'] = 'Course Manager report for heavy and empty courses';
$string['runstudentvisitreporttask'] = 'Course Manager report for students visits and courses without students';
$string['runteachervisitreporttask'] = 'Course Manager report for teachers visits and courses without teachers';
$string['runcleanreportstask'] = 'Course manager reports cleaner';

// Warning displays in courses.
$string['course_alert_heavy'] = 'Total files size in this course is actually <b>{$a->size} Mo</b>. Consider sorting files or reseting this course.<br /><b>{$a->heavy_link}</b>';
$string['course_alert_no_visit_teacher'] = 'No teacher has visited this course since more than {$a->no_teacher_time} days</b>. Please consider deleting this course if it is not used anymore.<br /><b>{$a->delete_link}</b>';
$string['course_alert_no_visit_student'] = 'No student has visited this course since more than {$a->no_teacher_time} days</b>. Please consider deleting this course if it is not used anymore.<br /><b>{$a->delete_link} | {$a->reset_link}</b>';
$string['course_alert_no_student'] = '<b>No student enrolled in this course</b>. Please consider deleting this course if it is not used anymore.<br /><b>{$a->delete_link}</b>';
$string['course_alert_empty'] = 'This course has not contents, eventually except native forum. Please consider deleting this course if it is not used anymore.<br /><b>{$a->delete_link}</b>';
$string['course_alert_orphan_submissions'] = 'This course contains assigns with invisible submissions, uploaded by unenrolled students. Please consider reseting this course if you can.';
$string['more_information'] = 'More information';
$string['text_link_delete'] = 'Delete course';
$string['text_link_reset'] = 'Reset course';
$string['collapse_show_report'] = 'Show report';
$string['warntextcoursetrash'] = 'WARNING : this course is in trash category and will be deleted later. Move it in another category with Course Manager tool if you want to keep it.';
$string['warntextcategorytrash'] = 'WARNING : this category is used by Course Manager as trash. All courses in this category are expected to be deleted at a later date. If you wish to keep your course, please move it in another category.';

// Mailing.
$string['mailingtitle'] = 'Course Manager - Reports for your courses';
$string['mailingintro'] = '<p>Hello %userfirstname%,</p>
<p>Course Manager is a tool report for helping teacher to manage their courses using calculated reports. This mail summarizes these reports, and indicates which courses are concerned by problems.</p>
<p><b>These reports are only intended to attract your attention on certain aspects of managing your courses, and to give keys to good practice in using Moodle</b></p>
<p>For more information, visit your personnal <a href="%coursemanagerlink%">Course Manager dashboard</a>';
$string['mailingoutro'] = '<p>For more information about these reports, please contact Moodle admin or Support service.';
$string['mailingddescreportempty'] = 'Theses courses have no contents, eventually except native Moodle forum.';
$string['mailingddescreportnovisitstudent'] = 'Students have not visited theses courses for a long time.</b> ';
$string['mailingddescreportnostudent'] = 'There are no students enrolled in this courses.';
$string['mailingddescreportnovisitteacher'] = 'Teachers including you have not visited theses courses for a long time';
$string['mailingddescreportheavy'] = 'Total files size in this course seems to be heavy. Click on a course name to see files report.';
$string['mailingddescreportorphansubmissions'] = 'Somme assigns in these courses have hidden submissions, uploaded by unenrolled students. These files can represent a heavy weight.';

// Privacy.
$string['privacy:no_data_reason'] = 'Course Manager plugin does not store any personal datas. It only calculates reports by studying courses contents (settings, enrolled students and teachers, number of activities...).';

// Capabilities.
$string['coursemanager:viewreport'] = 'View reports in course if enabled';
$string['coursemanager:admintools'] = 'Use admin tools in Report section';
