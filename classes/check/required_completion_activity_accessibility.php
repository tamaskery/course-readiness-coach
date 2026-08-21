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
 * Required completion activity accessibility check.
 *
 * @package   local_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursecoach\check;

use completion_info;
use moodle_url;
use stdClass;

/**
 * Detects course-completion activities that are deterministically hidden.
 */
final class required_completion_activity_accessibility implements checker {
    /** @var int Score weight. */
    private const WEIGHT = 3;

    /**
     * Analyse deterministic availability of required completion activities.
     *
     * Learner-specific availability conditions are intentionally not assessed.
     *
     * @param stdClass $course Course record.
     * @return result Check result.
     */
    public function check(stdClass $course): result {
        global $CFG;

        require_once($CFG->libdir . '/completionlib.php');

        $title = get_string('check:requiredactivity:title', 'local_coursecoach');
        $completioninfo = new completion_info($course);
        if (!$completioninfo->is_enabled()) {
            return $this->not_applicable($title, 'check:requiredactivity:disabled');
        }

        $criteria = $completioninfo->get_criteria(COMPLETION_CRITERIA_TYPE_ACTIVITY);
        if (!$criteria) {
            return $this->not_applicable($title, 'check:requiredactivity:none');
        }

        $cms = get_fast_modinfo($course)->get_cms();
        $required = [];
        $hidden = [];
        foreach ($criteria as $criterion) {
            if (!isset($cms[$criterion->moduleinstance])) {
                continue;
            }

            $cm = $cms[$criterion->moduleinstance];
            $required[] = $cm;
            if (!$cm->visible) {
                $hidden[] = $cm;
            }
        }

        if (!$required) {
            return $this->not_applicable($title, 'check:requiredactivity:none');
        }

        if ($hidden) {
            return new result(
                true,
                result::STATUS_CRITICAL,
                result::SEVERITY_CRITICAL,
                $title,
                get_string('check:requiredactivity:hidden:explanation', 'local_coursecoach', $this->activity_names($hidden)),
                get_string('check:requiredactivity:hidden:recommendation', 'local_coursecoach'),
                new moodle_url('/course/modedit.php', ['update' => $hidden[0]->id, 'return' => 0]),
                get_string('action:requiredactivity', 'local_coursecoach')
            );
        }

        return new result(
            true,
            result::STATUS_PASSED,
            result::SEVERITY_RECOMMENDATION,
            $title,
            get_string('check:requiredactivity:passed:explanation', 'local_coursecoach'),
            get_string('check:requiredactivity:passed:recommendation', 'local_coursecoach')
        );
    }

    /**
     * Return the score weight.
     *
     * @return int
     */
    public function get_weight(): int {
        return self::WEIGHT;
    }

    /**
     * Return a concise list of formatted activity names.
     *
     * @param array $activities Course-module information objects.
     * @return string Activity names.
     */
    private function activity_names(array $activities): string {
        $names = [];
        foreach ($activities as $cm) {
            $names[] = name_formatter::activity($cm);
        }

        return implode(', ', $names);
    }

    /**
     * Create a not-applicable result.
     *
     * @param string $title Result title.
     * @param string $identifier Explanation and recommendation string prefix.
     * @return result
     */
    private function not_applicable(string $title, string $identifier): result {
        return new result(
            false,
            result::STATUS_NOT_APPLICABLE,
            result::SEVERITY_RECOMMENDATION,
            $title,
            get_string($identifier . ':explanation', 'local_coursecoach'),
            get_string($identifier . ':recommendation', 'local_coursecoach')
        );
    }
}
