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
 * Incomplete course content check.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach\check;

use moodle_url;
use stdClass;

/**
 * Detects visible non-general sections containing no course modules.
 */
final class incomplete_content implements checker {
    /** @var int Score weight. */
    private const WEIGHT = 1;

    /**
     * Assess deterministic empty-section configuration.
     *
     * @param stdClass $course Course record.
     * @return result
     */
    public function check(stdClass $course): result {
        $modinfo = get_fast_modinfo($course);
        $empty = [];
        foreach ($modinfo->get_section_info_all() as $section) {
            if ($section->section === 0 || !$section->visible || !empty($modinfo->sections[$section->section])) {
                continue;
            }
            $empty[] = $section;
        }

        $title = get_string('check:incomplete:title', 'report_coursecoach');
        if ($empty) {
            return new result(
                true,
                result::STATUS_WARNING,
                result::SEVERITY_RECOMMENDATION,
                $title,
                get_string('check:incomplete:warning:explanation', 'report_coursecoach', $this->names($course, $empty)),
                get_string('check:incomplete:warning:recommendation', 'report_coursecoach'),
                new moodle_url('/course/editsection.php', ['id' => $empty[0]->id]),
                get_string('action:section', 'report_coursecoach')
            );
        }

        return new result(
            true,
            result::STATUS_PASSED,
            result::SEVERITY_RECOMMENDATION,
            $title,
            get_string('check:incomplete:passed:explanation', 'report_coursecoach'),
            get_string('check:incomplete:passed:recommendation', 'report_coursecoach')
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
     * Return formatted section names.
     *
     * @param stdClass $course Course record.
     * @param array $sections Empty sections.
     * @return string Formatted section names.
     */
    private function names(stdClass $course, array $sections): string {
        $names = [];
        foreach ($sections as $section) {
            $names[] = name_formatter::section($course, $section);
        }
        return implode(', ', $names);
    }
}
