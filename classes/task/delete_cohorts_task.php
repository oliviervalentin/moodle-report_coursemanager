<?php
// This file is part of mod_offlinequiz for Moodle - http://moodle.org/
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
 * Calls Course Manager cron task for cohorts unenroll.
 *
 * @package     report_coursemanager
 * @copyright   2022 Olivier VALENTIN
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursemanager\task;

defined('MOODLE_INTERNAL') || die();

class delete_cohorts_task extends \core\task\adhoc_task {

    public function execute() {
        global $DB;

        $data = $this->get_custom_data();
        $courseid = $data->courseid;

        // Retieve all cohorts enrolling methods instances.
        $instances = $DB->get_records('enrol', ['enrol' => 'cohort', 'courseid' => $courseid]);

        foreach ($instances as $instance) {
            enrol_get_plugin('cohort')->delete_instance($instance);
        }

        // Add event for cohort unenrollment.
        $context = \context_course::instance($courseid);
        $eventparams = ['context' => $context, 'courseid' => $courseid];
        $event = \report_coursemanager\event\course_cohort_unenrolled::create($eventparams);
        $event->trigger();
    }
}
