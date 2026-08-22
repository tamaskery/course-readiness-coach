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
 * Behat steps for the Course Readiness Coach report.
 *
 * @package   report_coursecoach
 * @category  test
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by Behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Behat steps for the Course Readiness Coach report.
 *
 * @package   report_coursecoach
 * @category  test
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_report_coursecoach extends behat_base {
    /**
     * Visit the report for a course identified by short name.
     *
     * Course record IDs are not stable between isolated Behat scenarios, so a
     * standard fixed relative-URL step cannot reliably exercise direct access.
     *
     * @When /^I visit the Course Readiness Coach report for course "(?P<shortname_string>[^"]+)"$/
     * @param string $shortname Course short name.
     */
    public function i_visit_report_for_course(string $shortname): void {
        global $DB;

        $course = $DB->get_record('course', ['shortname' => $shortname], 'id', MUST_EXIST);
        $url = new moodle_url('/report/coursecoach/index.php', ['id' => $course->id]);

        $this->execute('behat_general::i_visit', [$url]);
    }
}
