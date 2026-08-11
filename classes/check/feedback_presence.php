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
 * Feedback presence check.
 *
 * @package   local_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursecoach\check;

use moodle_url;
use stdClass;

/**
 * Detects visible Moodle Feedback activities without inspecting responses.
 */
final class feedback_presence implements checker {
    /** @var int Score weight. */
    private const WEIGHT = 1;

    /**
     * Assess availability of a Moodle Feedback activity.
     *
     * @param stdClass $course Course record.
     * @return result
     */
    public function check(stdClass $course): result {
        $title = get_string('check:feedback:title', 'local_coursecoach');
        $feedbacks = get_fast_modinfo($course)->get_instances_of('feedback');
        foreach ($feedbacks as $cm) {
            if ($cm->visible) {
                return new result(
                    true,
                    result::STATUS_PASSED,
                    result::SEVERITY_RECOMMENDATION,
                    $title,
                    get_string('check:feedback:passed:explanation', 'local_coursecoach'),
                    get_string('check:feedback:passed:recommendation', 'local_coursecoach')
                );
            }
        }

        $hidden = reset($feedbacks);
        return new result(
            true,
            result::STATUS_WARNING,
            result::SEVERITY_RECOMMENDATION,
            $title,
            get_string('check:feedback:warning:explanation', 'local_coursecoach'),
            get_string('check:feedback:warning:recommendation', 'local_coursecoach'),
            $hidden ? new moodle_url('/course/modedit.php', ['update' => $hidden->id, 'return' => 0]) : null,
            $hidden ? get_string('action:feedback', 'local_coursecoach') : null
        );
    }

    /**
     * Return the score weight.
     *
     * @return int Score weight.
     */
    public function get_weight(): int {
        return self::WEIGHT;
    }
}
