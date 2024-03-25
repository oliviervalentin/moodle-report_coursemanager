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
 * Course Manager form, derivated from the native reset form created by Petr Skoda. Elements to reset are pre-checked in form.
 *
 * @package    report_coursemanager
 * @copyright  2022 Olivier VALENTIN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/course/lib.php');

/**
 * Class for displaying reset form.
 *
 * @copyright  2022 Olivier VALENTIN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_coursemanager_reset_form extends moodleform {
    /**
     * Form definition for course reset.
     *
     * @return void
     */
    public function definition() {
        global $CFG, $COURSE, $DB;

        $mform =& $this->_form;

        // These elements are prechecked.

        // Delete course completions.
        $mform->addElement('hidden', 'reset_completion', get_string('deletecompletiondata', 'completion'));
        $mform->setDefault('reset_completion', 1);
        $mform->setType('reset_completion', PARAM_INT);
        // Delete grades.
        $mform->addElement('hidden', 'reset_gradebook_grades', get_string('removeallcoursegrades', 'grades'));
        $mform->setDefault('reset_gradebook_grades', 1);
        $mform->setType('reset_gradebook_grades', PARAM_INT);
        // Delete groups.
        $mform->addElement('hidden', 'reset_groups_remove', get_string('deleteallgroups', 'group'));
        $mform->setDefault('reset_groups_remove', 1);
        $mform->setType('reset_groups_remove', PARAM_INT);
        // Delete groupings.
        $mform->addElement('hidden', 'reset_groupings_remove', get_string('deleteallgroupings', 'group'));
        $mform->setDefault('reset_groupings_remove', 1);
        $mform->setType('reset_groupings_remove', PARAM_INT);

        // Create array for unsupported activities (useless here).
        $unsupportedmods = [];
        // Now check activities. We only check assigns, forums and quiz.
        $myresets = ["assign", "forum", "quiz"];

        // Retrieve all activities.
        if ($allmods = $DB->get_records('modules')) {
            foreach ($allmods as $mod) {
                // If activity is in preset list, let's check it.
                if (in_array($mod->name, $myresets)) {
                    $modname = $mod->name;
                    $modfile = $CFG->dirroot."/mod/$modname/lib.php";
                    $modresetcourseformdefinition = $modname.'_reset_course_form_definition';
                    $modresetserdata = $modname.'_reset_userdata';

                    if (file_exists($modfile)) {
                        if (!$DB->count_records($modname, ['course' => $this->_customdata['courseid']])) {
                            continue; // Skip mods with no instances.
                        }
                        include_once($modfile);
                        // When reset, function in appropriate lib.php is defined, define elements to reset.
                        if (function_exists($modresetcourseformdefinition)) {
                            // For assign : delete submissions.
                            if ($modname == "assign") {
                                $mform->addElement('hidden', 'reset_assign_submissions',
                                get_string('deleteallsubmissions', 'assign'));
                                $mform->setDefault('reset_assign_submissions', 1);
                                $mform->setType('reset_assign_submissions', PARAM_INT);
                            }
                            // For forum : delete messages.
                            if ($modname == "forum") {
                                $mform->addElement('hidden', 'reset_forum_all', get_string('resetforumsall', 'forum'));
                                $mform->setDefault('reset_forum_all', 1);
                                $mform->setType('reset_forum_all', PARAM_INT);
                            }
                            // For quiz : delete attempts.
                            if ($modname == "quiz") {
                                $mform->addElement('hidden', 'reset_quiz_attempts', get_string('removeallquizattempts', 'quiz'));
                                $mform->setDefault('reset_quiz_attempts', 1);
                                $mform->setType('reset_quiz_attempts', PARAM_INT);
                            }
                        } else if (!function_exists($modresetserdata)) {
                            // If no reset function, add to unsupported activities (useless here).
                            $unsupportedmods[] = $mod;
                        }
                    } else {
                        debugging('Missing lib.php in '.$modname.' module');
                    }
                }
            }
        }
        // Mentions for unsupported activites (useless here).
        if (!empty($unsupportedmods)) {
            $mform->addElement('header', 'unsupportedheader', get_string('resetnotimplemented'));
            foreach ($unsupportedmods as $mod) {
                $mform->addElement('static', 'unsup'.$mod->name, get_string('modulenameplural', $mod->name));
                $mform->setAdvanced('unsup'.$mod->name);
            }
        }

        $mform->addElement('hidden', 'id', $this->_customdata['courseid']);
        $mform->setType('id', PARAM_INT);

        $buttonarray = [];
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('resetcourse'));
        $buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
    }
}
