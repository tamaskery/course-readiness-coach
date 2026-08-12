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
 * Activity date alignment check.
 *
 * @package   local_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursecoach\check;

use moodle_url;
use stdClass;

/**
 * Detects high-confidence Quiz and Assignment timeline mismatches.
 */
final class activity_date_alignment implements checker {
    /** @var int Score weight. */
    private const WEIGHT = 1;

    /**
     * Assess deterministic activity dates against configured course boundaries.
     *
     * Assignment dates are skipped in relative-date courses because their effective
     * values depend on learner enrolment dates.
     *
     * @param stdClass $course Course record.
     * @return result Check result.
     */
    public function check(stdClass $course): result {
        global $DB;

        $title = get_string('check:activitydates:title', 'local_coursecoach');
        $startdate = (int) ($course->startdate ?? 0);
        $enddate = (int) ($course->enddate ?? 0);
        if (
            ($startdate <= 0 && $enddate <= 0) ||
            ($startdate > 0 && $enddate > 0 && $enddate <= $startdate)
        ) {
            return $this->not_applicable($title);
        }

        $activities = [
            'assign' => [],
            'quiz' => [],
        ];
        foreach (get_fast_modinfo($course)->get_cms() as $cm) {
            if (!$cm->visible || !isset($activities[$cm->modname])) {
                continue;
            }
            if ($cm->modname === 'assign' && !empty($course->relativedatesmode)) {
                continue;
            }
            $activities[$cm->modname][$cm->instance] = $cm;
        }

        $assessed = false;
        $affected = [];
        if ($activities['quiz']) {
            $quizzes = $DB->get_records_list(
                'quiz',
                'id',
                array_keys($activities['quiz']),
                '',
                'id,timeopen,timeclose'
            );
            foreach ($quizzes as $quiz) {
                $mismatch = false;
                if ($enddate > 0 && (int) $quiz->timeopen > 0) {
                    $assessed = true;
                    $mismatch = (int) $quiz->timeopen >= $enddate;
                }
                if ($startdate > 0 && (int) $quiz->timeclose > 0) {
                    $assessed = true;
                    $mismatch = $mismatch || (int) $quiz->timeclose <= $startdate;
                }
                if ($mismatch) {
                    $cm = $activities['quiz'][$quiz->id];
                    $affected[$cm->id] = $cm;
                }
            }
        }

        if ($activities['assign']) {
            $assignments = $DB->get_records_list(
                'assign',
                'id',
                array_keys($activities['assign']),
                '',
                'id,allowsubmissionsfromdate,cutoffdate'
            );
            foreach ($assignments as $assignment) {
                $mismatch = false;
                if ($enddate > 0 && (int) $assignment->allowsubmissionsfromdate > 0) {
                    $assessed = true;
                    $mismatch = (int) $assignment->allowsubmissionsfromdate >= $enddate;
                }
                if ($startdate > 0 && (int) $assignment->cutoffdate > 0) {
                    $assessed = true;
                    $mismatch = $mismatch || (int) $assignment->cutoffdate <= $startdate;
                }
                if ($mismatch) {
                    $cm = $activities['assign'][$assignment->id];
                    $affected[$cm->id] = $cm;
                }
            }
        }

        if ($affected) {
            $first = reset($affected);
            return new result(
                true,
                result::STATUS_WARNING,
                result::SEVERITY_RECOMMENDATION,
                $title,
                get_string('check:activitydates:warning:explanation', 'local_coursecoach', $this->names($affected)),
                get_string('check:activitydates:warning:recommendation', 'local_coursecoach'),
                new moodle_url('/course/modedit.php', ['update' => $first->id, 'return' => 0]),
                get_string('action:activitydates', 'local_coursecoach')
            );
        }

        if (!$assessed) {
            return $this->not_applicable($title);
        }

        return new result(
            true,
            result::STATUS_PASSED,
            result::SEVERITY_RECOMMENDATION,
            $title,
            get_string('check:activitydates:passed:explanation', 'local_coursecoach'),
            get_string('check:activitydates:passed:recommendation', 'local_coursecoach')
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

    /**
     * Return formatted activity names.
     *
     * @param array $activities Course-module information objects.
     * @return string Activity names.
     */
    private function names(array $activities): string {
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
     * @return result Not-applicable result.
     */
    private function not_applicable(string $title): result {
        return new result(
            false,
            result::STATUS_NOT_APPLICABLE,
            result::SEVERITY_RECOMMENDATION,
            $title,
            get_string('check:activitydates:notapplicable:explanation', 'local_coursecoach'),
            get_string('check:activitydates:notapplicable:recommendation', 'local_coursecoach')
        );
    }
}
