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
 * Course completion configuration check.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach\check;

use completion_info;
use moodle_url;
use stdClass;

/**
 * Uses Moodle's completion API to assess course completion configuration.
 */
final class course_completion implements checker {
    /** @var int Score weight. */
    private const WEIGHT = 3;

    /**
     * Analyse completion configuration.
     *
     * @param stdClass $course Course record.
     * @return result Check result.
     */
    public function check(stdClass $course): result {
        global $CFG;

        require_once($CFG->libdir . '/completionlib.php');

        $title = get_string('check:completion:title', 'report_coursecoach');
        if (!completion_info::is_enabled_for_site()) {
            return new result(
                true,
                result::STATUS_CRITICAL,
                result::SEVERITY_CRITICAL,
                $title,
                get_string('check:completion:sitedisabled:explanation', 'report_coursecoach'),
                get_string('check:completion:sitedisabled:recommendation', 'report_coursecoach')
            );
        }

        $completioninfo = new completion_info($course);
        if (!$completioninfo->is_enabled()) {
            return new result(
                true,
                result::STATUS_CRITICAL,
                result::SEVERITY_CRITICAL,
                $title,
                get_string('check:completion:disabled:explanation', 'report_coursecoach'),
                get_string('check:completion:disabled:recommendation', 'report_coursecoach'),
                new moodle_url('/course/edit.php', ['id' => $course->id]),
                get_string('action:completion', 'report_coursecoach')
            );
        }

        $settingsurl = new moodle_url('/course/completion.php', ['id' => $course->id]);
        if (!$completioninfo->has_criteria()) {
            return new result(
                true,
                result::STATUS_WARNING,
                result::SEVERITY_IMPORTANT,
                $title,
                get_string('check:completion:nocriteria:explanation', 'report_coursecoach'),
                get_string('check:completion:nocriteria:recommendation', 'report_coursecoach'),
                $settingsurl,
                get_string('action:completion', 'report_coursecoach')
            );
        }

        return new result(
            true,
            result::STATUS_PASSED,
            result::SEVERITY_RECOMMENDATION,
            $title,
            get_string('check:completion:configured:explanation', 'report_coursecoach'),
            get_string('check:completion:configured:recommendation', 'report_coursecoach'),
            $settingsurl
        );
    }

    /**
     * Return the completion score weight.
     *
     * @return int
     */
    public function get_weight(): int {
        return self::WEIGHT;
    }
}
