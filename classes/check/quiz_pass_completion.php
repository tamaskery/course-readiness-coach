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
 * Quiz pass and completion configuration check.
 *
 * @package   local_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursecoach\check;

use completion_info;
use grade_item;
use moodle_url;
use stdClass;

/**
 * Checks quizzes used as course-completion criteria for a passing requirement.
 */
final class quiz_pass_completion implements checker {
    /** @var int Score weight. */
    private const WEIGHT = 2;

    /**
     * Analyse relevant quiz pass and completion configuration.
     *
     * @param stdClass $course Course record.
     * @return result Check result.
     */
    public function check(stdClass $course): result {
        global $CFG;

        require_once($CFG->libdir . '/completionlib.php');
        require_once($CFG->libdir . '/gradelib.php');

        $title = get_string('check:quiz:title', 'local_coursecoach');
        $completioninfo = new completion_info($course);
        if (!$completioninfo->is_enabled()) {
            return $this->not_applicable($title, 'check:quiz:disabled');
        }

        $cms = get_fast_modinfo($course)->get_cms();
        $quizzes = [];
        foreach ($completioninfo->get_criteria(COMPLETION_CRITERIA_TYPE_ACTIVITY) as $criterion) {
            if (!isset($cms[$criterion->moduleinstance])) {
                continue;
            }

            $cm = $cms[$criterion->moduleinstance];
            if ($cm->modname === 'quiz') {
                $quizzes[] = $cm;
            }
        }

        if (!$quizzes) {
            return $this->not_applicable($title, 'check:quiz:none');
        }

        $withoutpassgrade = [];
        $withoutpasscompletion = [];
        foreach ($quizzes as $cm) {
            $gradeitem = grade_item::fetch([
                'courseid' => $course->id,
                'itemtype' => 'mod',
                'itemmodule' => 'quiz',
                'iteminstance' => $cm->instance,
                'itemnumber' => 0,
            ]);
            if (!$gradeitem || (float) $gradeitem->gradepass <= 0) {
                $withoutpassgrade[] = $cm;
                continue;
            }

            if (
                $completioninfo->is_enabled($cm) !== COMPLETION_TRACKING_AUTOMATIC ||
                empty($cm->completionpassgrade)
            ) {
                $withoutpasscompletion[] = $cm;
            }
        }

        if ($withoutpassgrade || $withoutpasscompletion) {
            $issues = [];
            if ($withoutpassgrade) {
                $issues[] = get_string(
                    'check:quiz:nopassgrade:issue',
                    'local_coursecoach',
                    $this->activity_names($withoutpassgrade)
                );
            }
            if ($withoutpasscompletion) {
                $issues[] = get_string(
                    'check:quiz:nopasscompletion:issue',
                    'local_coursecoach',
                    $this->activity_names($withoutpasscompletion)
                );
            }
            $firstaffected = $withoutpassgrade[0] ?? $withoutpasscompletion[0];

            return new result(
                true,
                result::STATUS_WARNING,
                result::SEVERITY_IMPORTANT,
                $title,
                implode(' ', $issues),
                get_string('check:quiz:warning:recommendation', 'local_coursecoach'),
                new moodle_url('/course/modedit.php', ['update' => $firstaffected->id, 'return' => 0]),
                get_string('action:quizcompletion', 'local_coursecoach')
            );
        }

        return new result(
            true,
            result::STATUS_PASSED,
            result::SEVERITY_RECOMMENDATION,
            $title,
            get_string('check:quiz:passed:explanation', 'local_coursecoach'),
            get_string('check:quiz:passed:recommendation', 'local_coursecoach')
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
            $names[] = format_string($cm->name, true, ['context' => $cm->context]);
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
