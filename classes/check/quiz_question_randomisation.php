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
 * Quiz question randomisation check.
 *
 * @package   local_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursecoach\check;

use completion_info;
use mod_quiz\quiz_settings;
use moodle_url;
use stdClass;

/**
 * Checks random question slots in quizzes required for course completion.
 */
final class quiz_question_randomisation implements checker {
    /** @var int Score weight. */
    private const WEIGHT = 1;

    /**
     * Assess question randomisation for completion-relevant quizzes.
     *
     * @param stdClass $course Course record.
     * @return result
     */
    public function check(stdClass $course): result {
        global $CFG;

        require_once($CFG->libdir . '/completionlib.php');

        $title = get_string('check:randomisation:title', 'local_coursecoach');
        $completioninfo = new completion_info($course);
        if (!$completioninfo->is_enabled()) {
            return $this->not_applicable($title);
        }

        $cms = get_fast_modinfo($course)->get_cms();
        $quizzes = [];
        foreach ($completioninfo->get_criteria(COMPLETION_CRITERIA_TYPE_ACTIVITY) as $criterion) {
            if (isset($cms[$criterion->moduleinstance]) && $cms[$criterion->moduleinstance]->modname === 'quiz') {
                $quizzes[] = $cms[$criterion->moduleinstance];
            }
        }
        if (!$quizzes) {
            return $this->not_applicable($title);
        }

        $fixed = [];
        foreach ($quizzes as $cm) {
            $structure = quiz_settings::create($cm->instance)->get_structure();
            $random = false;
            foreach ($structure->get_slots() as $slot) {
                if (!empty($slot->random) || $structure->get_question_type_for_slot($slot->slot) === 'random') {
                    $random = true;
                    break;
                }
            }
            if (!$random) {
                $fixed[] = $cm;
            }
        }

        if ($fixed) {
            return new result(
                true,
                result::STATUS_WARNING,
                result::SEVERITY_RECOMMENDATION,
                $title,
                get_string('check:randomisation:warning:explanation', 'local_coursecoach', $this->names($fixed)),
                get_string('check:randomisation:warning:recommendation', 'local_coursecoach'),
                new moodle_url('/mod/quiz/edit.php', ['cmid' => $fixed[0]->id]),
                get_string('action:quizquestions', 'local_coursecoach')
            );
        }

        return new result(
            true,
            result::STATUS_PASSED,
            result::SEVERITY_RECOMMENDATION,
            $title,
            get_string('check:randomisation:passed:explanation', 'local_coursecoach'),
            get_string('check:randomisation:passed:recommendation', 'local_coursecoach')
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
     * @param array $cms Course-module information objects.
     * @return string Formatted activity names.
     */
    private function names(array $cms): string {
        $names = [];
        foreach ($cms as $cm) {
            $names[] = name_formatter::activity($cm);
        }
        return implode(', ', $names);
    }

    /**
     * Create a not-applicable result.
     *
     * @param string $title Result title.
     * @return result Not-applicable check result.
     */
    private function not_applicable(string $title): result {
        return new result(
            false,
            result::STATUS_NOT_APPLICABLE,
            result::SEVERITY_RECOMMENDATION,
            $title,
            get_string('check:randomisation:notapplicable:explanation', 'local_coursecoach'),
            get_string('check:randomisation:notapplicable:recommendation', 'local_coursecoach')
        );
    }
}
