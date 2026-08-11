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
 * Course visibility check.
 *
 * @package   local_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursecoach\check;

use moodle_url;
use stdClass;

/**
 * Checks whether the course is visible to learners.
 */
final class course_visibility implements checker {
    /** @var int Score weight. */
    private const WEIGHT = 1;

    /**
     * Analyse course visibility.
     *
     * @param stdClass $course Course record.
     * @return result Check result.
     */
    public function check(stdClass $course): result {
        $settingsurl = new moodle_url('/course/edit.php', ['id' => $course->id]);
        if (!empty($course->visible)) {
            return new result(
                true,
                result::STATUS_PASSED,
                result::SEVERITY_RECOMMENDATION,
                get_string('check:visibility:title', 'local_coursecoach'),
                get_string('check:visibility:passed:explanation', 'local_coursecoach'),
                get_string('check:visibility:passed:recommendation', 'local_coursecoach'),
                $settingsurl
            );
        }

        return new result(
            true,
            result::STATUS_WARNING,
            result::SEVERITY_IMPORTANT,
            get_string('check:visibility:title', 'local_coursecoach'),
            get_string('check:visibility:hidden:explanation', 'local_coursecoach'),
            get_string('check:visibility:hidden:recommendation', 'local_coursecoach'),
            $settingsurl,
            get_string('action:visibility', 'local_coursecoach')
        );
    }

    /**
     * Return the visibility score weight.
     *
     * @return int
     */
    public function get_weight(): int {
        return self::WEIGHT;
    }
}
