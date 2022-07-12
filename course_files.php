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
 * Information page about files related to a course.
 *
 * @package     report_coursemanager
 * @copyright   2022 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/filelib.php');

$courseid = required_param('courseid', PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid));

$context = context_course::instance($course->id);
$contextcheck = $context->path . '/%';

require_capability('moodle/course:update', $context);

// Get site info.
$site = get_site();

// Page settings.
$PAGE->set_context($context);
$PAGE->set_url('/report/coursemanager/course_files.php');
$PAGE->set_pagelayout('mycourses');
$PAGE->set_secondary_navigation(false);

$PAGE->set_pagetype('teachertools');
$PAGE->blocks->add_region('content');
$PAGE->set_title($site->fullname);
$PAGE->set_heading('Gestion des cours - Enseignants');
// Force the add block out of the default area.
$PAGE->theme->addblockposition  = BLOCK_ADDBLOCK_POSITION_CUSTOM;

if (!has_capability('moodle/course:update', $context)) {
    echo $OUTPUT->header();
	echo get_string('capability_problem', 'report_coursemanager');
	echo $OUTPUT->footer();
	exit();
}

// First query to retrieve files related to course.
$sizesql = "SELECT a.component, SUM(a.filesize) as filesize, COUNT(a.contenthash) as countfiles
              FROM (SELECT DISTINCT f.contenthash, f.component, f.filesize
                    FROM {files} f
                    JOIN {context} ctx ON f.contextid = ctx.id
                    WHERE ".$DB->sql_concat('ctx.path', "'/'")." LIKE ?
                       AND f.filename != '.' AND f.source IS NOT NULL) a
             GROUP BY a.component
			 ORDER BY a.component";

$cxsizes = $DB->get_recordset_sql($sizesql, array($contextcheck));

// initialize table to show results.
$coursetable = new html_table();
$coursetable->align = array('right', 'left', 'left');
$coursetable->head = array(
    get_string('plugin', 'report_coursemanager'),
    get_string('size', 'report_coursemanager'),
	get_string('number_of_files', 'report_coursemanager'), 
	get_string('comment', 'report_coursemanager')
);
$coursetable->data = array();
$coursetable->width = '50%';

$total = array();
$chart_sizes = array();
$chart_labels = array();

foreach ($cxsizes as $cxdata) {
	// print_object($cxdata);
    $row = array();
    // If component is not course, retrive file sizes and component for global chart.
	if ($cxdata->component != 'course') {
	    $chart_labels[] = get_string('pluginname', $cxdata->component);
	    $chart_sizes[] = number_format(ceil($cxdata->filesize / 1048576));
    }
	// Retrieve details for every file.
	// According to component, we check special elements.

    // ASSIGN : we check submission files only !
	if ($cxdata->component == 'assignsubmission_file') {
		// Function to retrieve details for submissions.
		$details = (report_coursemanager_get_assign_comment($courseid));
		// Calculate total files size.
	    $size =  number_format(ceil($details[1] / 1048576));
        $row[] = (get_string('pluginname', 'mod_assign'));
		$row[] = $size . "Mo";
		// Number of files.
		$row[] = $details[2];
    } else {
		// If it's not an assign, we check only for labels, forums, folders and resources.
		// For each, we must define component and filearea.
		if ($cxdata->component == 'mod_label') {
			$component = 'label';
			$filearea = 'intro';
		} else if ($cxdata->component == 'mod_forum') {
			$component = 'forum';
			$filearea = 'attachment';
		} else if ($cxdata->component == 'mod_resource') {
			$component = 'resource';
			$filearea = 'content';
		} else if ($cxdata->component == 'mod_folder') {
			$component = 'folder';
			$filearea = 'content';
		} else {
			// Other components are not displayed.
			continue;
		}
		// Now that we have component and filearea, we can use function to retrieve comments.
		$details = (report_coursemanager_get_files_comment($component, $courseid, $filearea));
		$size = number_format(ceil($cxdata->filesize / 1048576));
        $row[] = get_string('pluginname', $cxdata->component);
		$row[] = $size . "Mo";
		$row[] = $cxdata->countfiles;
    }
	
	// Now add line to show comments about files.
    $row[] = $details[0];

    $coursetable->data[] = $row;
	$total[] += $size;
}
$cxsizes->close();

$chart_sizes_mod = new \core\chart_pie();
// $chart_sizes_mod->set_title('Répartition du poids des fichiers par activité');
$chart_serie = new core\chart_series('Poids en Mo', $chart_sizes);
$chart_sizes_mod->set_doughnut(true); // Calling set_doughnut(true) we display the chart as a doughnut.
$chart_sizes_mod->add_series($chart_serie);
$chart_sizes_mod->set_labels($chart_labels);

// All the processing done, now just output stuff.

print $OUTPUT->header();

print html_writer::div('
<div class="btn btn-outline-info"><a href="view.php">
<i class="fa fa-arrow-left"></i>  '.get_string('back').'</a></div><br /><br />
');
print $OUTPUT->heading(get_string('coursesize', 'report_coursemanager'). " - ". format_string($course->fullname));
if (array_sum($total)>0) {
	
	print html_writer::tag('h4', get_string('totalsize', 'report_coursemanager').array_sum($total).' Mo');
	// print html_writer::tag('h4', '&nbsp;');
	if (get_config('tool_recyclebin', 'coursebinenable') == 1) {
		print html_writer::tag('p', get_string('warn_recyclebin', 'report_coursemanager')); 
	} else {
		print html_writer::tag('h4', '&nbsp;');
	}
	echo '<table>';
	echo '<tr><td>';
	print html_writer::table($coursetable);
	echo '</td><td style="min-width: 30%; padding-left: 20px;">';
	print html_writer::tag('h5', get_string('global_chart', 'report_coursemanager')); 
	echo $OUTPUT->render($chart_sizes_mod, false).'</td></tr>';
	echo '</table>';
	// print html_writer::tag('p', '<div style="max-height: 50% !important"> ' . $OUTPUT->render($chart_sizes_mod) . '</div>');

	
	// echo $OUTPUT->render($chart_sizes_mod);
} else {
	print html_writer::tag('p', '<div class=" alert alert-info"><i class="fa fa-glass"></i> ' . get_string('empty_files_course', 'report_coursemanager') . '</div>');
}

print $OUTPUT->footer();

// Add event when showing this page.
$eventparams = array('context' => $context, 'courseid' => $courseid);
$event = \report_coursemanager\event\course_files_viewed::create($eventparams);
$event->trigger();
