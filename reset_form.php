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
 * Form created form the native reset form.
 * Elements to delete are pre-checked to reset automatically
 * most important elements in a course.
 *
 * @package     report_coursemanager
 * @copyright   2007 Petr Skoda - 2022 Olivier VALENTIN
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/course/lib.php');

class course_reset_form extends moodleform {
    function definition (){
        global $CFG, $COURSE, $DB;

        $mform =& $this->_form;

		// These elements are prechecked.
		
		// Delete course completions.
        $mform->addElement('hidden', 'reset_completion', get_string('deletecompletiondata', 'completion'));
		$mform->setDefault('reset_completion', 1);
		$mform->setType('reset_completion', PARAM_RAW);
		// Delete grades.
        $mform->addElement('hidden', 'reset_gradebook_grades', get_string('removeallcoursegrades', 'grades'));
		$mform->setDefault('reset_gradebook_grades', 1);
		$mform->setType('reset_gradebook_grades', PARAM_RAW);
		// Delete groups.
        $mform->addElement('hidden', 'reset_groups_remove', get_string('deleteallgroups', 'group'));
		$mform->setDefault('reset_groups_remove', 1);
		$mform->setType('reset_groups_remove', PARAM_RAW);
		// Delete groupings.
        $mform->addElement('hidden', 'reset_groupings_remove', get_string('deleteallgroupings', 'group'));
		$mform->setDefault('reset_groupings_remove', 1);
		$mform->setType('reset_groupings_remove', PARAM_RAW);

		// Create array for unsupported activities (useless here).
        $unsupported_mods = array();
		// Now check activities. We only check assigns, forums and quiz.
		$myresets = array("assign", "forum", "quiz");
		
		// Retrieve all activities.
        if ($allmods = $DB->get_records('modules') ) {
			// print_object($allmods);
            foreach ($allmods as $mod) {
				// If activity is in preset list, let's check it.
				if (in_array($mod->name, $myresets)) {
					$modname = $mod->name;
					$modfile = $CFG->dirroot."/mod/$modname/lib.php";
					$mod_reset_course_form_definition = $modname.'_reset_course_form_definition';
					$mod_reset__userdata = $modname.'_reset_userdata';
					
					// print_object($modfile);
					print_object($mod_reset_course_form_definition);
					print_object($id);
					
					if (file_exists($modfile)) {
						if (!$DB->count_records($modname, array('course'=>$this->_customdata['prout']))) {
							continue; // Skip mods with no instances
						}
					include_once($modfile);
					// When reset, function in appropriate lib.php is defined, define elements to reset.
					if (function_exists($mod_reset_course_form_definition)) {
						// print_object($mod_reset_course_form_definition);
						// echo "BBB";
						// For assign : delete submissions.
						if ($modname == "assign") {
							$mform->addElement('hidden', 'reset_assign_submissions', get_string('deleteallsubmissions', 'assign'));
						    $mform->setDefault('reset_assign_submissions', 1);
							$mform->setType('reset_assign_submissions', PARAM_RAW);
						}
						// For forum : delete messages.
						if ($modname == "forum") {
							$mform->addElement('hidden', 'reset_forum_all', get_string('resetforumsall','forum'));
							$mform->setDefault('reset_forum_all', 1);
							$mform->setType('reset_forum_all', PARAM_RAW);
						}
						// For quiz : delete attempts.
						if ($modname == "quiz") {
							$mform->addElement('hidden', 'reset_quiz_attempts', get_string('removeallquizattempts', 'quiz'));
							$mform->setDefault('reset_quiz_attempts', 1);
							$mform->setType('reset_quiz_attempts', PARAM_RAW);
						}
					} else if (!function_exists($mod_reset__userdata)) {
						// If no reset function, add to unsupported activities (useless here).
						$unsupported_mods[] = $mod;
					}
				} else {
						debugging('Missing lib.php in '.$modname.' module');
					}
				}
            }
        }
        // Mentions for unsupported activites (useless here).
        if (!empty($unsupported_mods)) {
            $mform->addElement('header', 'unsupportedheader', get_string('resetnotimplemented'));
            foreach($unsupported_mods as $mod) {
                $mform->addElement('static', 'unsup'.$mod->name, get_string('modulenameplural', $mod->name));
                $mform->setAdvanced('unsup'.$mod->name);
            }
        }

        $mform->addElement('hidden', 'id', $this->_customdata['prout']);
        $mform->setType('id', PARAM_INT);

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('resetcourse'));
        $buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
		
		// print_object($mform);
    }
}
