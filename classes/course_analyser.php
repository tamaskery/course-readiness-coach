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
 * Course Coach analysis service.
 *
 * @package   local_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursecoach;

use coding_exception;
use local_coursecoach\check\activity_date_alignment;
use local_coursecoach\check\activity_completion_coverage;
use local_coursecoach\check\checker;
use local_coursecoach\check\course_completion;
use local_coursecoach\check\course_dates;
use local_coursecoach\check\course_visibility;
use local_coursecoach\check\feedback_presence;
use local_coursecoach\check\incomplete_content;
use local_coursecoach\check\quiz_pass_completion;
use local_coursecoach\check\quiz_question_randomisation;
use local_coursecoach\check\required_completion_activity_accessibility;
use stdClass;

/**
 * Runs the independent checks and passes their results to the calculator.
 */
final class course_analyser {
    /** @var checker[] Ordered checkers. */
    private array $checkers;

    /**
     * Constructor.
     *
     * @param checker[]|null $checkers Optional checker list for reuse and testing.
     */
    public function __construct(?array $checkers = null) {
        $this->checkers = $checkers ?? [
            new course_visibility(),
            new course_dates(),
            new course_completion(),
            new activity_completion_coverage(),
            new required_completion_activity_accessibility(),
            new quiz_pass_completion(),
            new quiz_question_randomisation(),
            new feedback_presence(),
            new incomplete_content(),
            new activity_date_alignment(),
        ];

        foreach ($this->checkers as $checker) {
            if (!$checker instanceof checker) {
                throw new coding_exception('Course Coach analysers require checker instances.');
            }
        }
    }

    /**
     * Analyse a course without modifying it.
     *
     * @param stdClass $course Course record.
     * @return readiness Calculated course readiness.
     */
    public function analyse(stdClass $course): readiness {
        $results = [];
        foreach ($this->checkers as $checker) {
            $results[] = new weighted_result($checker->check($course), $checker->get_weight());
        }

        return (new readiness_calculator())->calculate($results);
    }
}
