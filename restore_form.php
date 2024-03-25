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
 * Class for displaying course restore form.
 *
 * @copyright  2022 Olivier VALENTIN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_coursemanager_form_restore extends moodleform {
    /**
     * Form definition for course restore.
     *
     * @return void
     */
    public function definition() {
        global $CFG, $USER, $DB;
        $mform = $this->_form;
        $datas = $this->_customdata['post'];

        $displaylist = core_course_category::make_categories_list();
        $mform->addElement('select', 'restore_category', get_string('select_restore_category', 'report_coursemanager'),
        $displaylist);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setDefault('courseid', $datas->courseid);
        $this->add_action_buttons(true, get_string('button_restore_confirm', 'report_coursemanager'));
    }

    /**
     * Form validation.
     *
     * @param array $data  data from the form.
     * @param array $files files uplaoded.
     *
     * @return array of errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (empty($data['restore_category'])) {
            $errors['message'] = get_string('error_category', 'report_coursemanager');
        }
        return $errors;
    }
}
