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
 * Course date sanity check.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach\check;

use moodle_url;
use stdClass;

/**
 * Detects reliable, non-pedagogical course date problems.
 */
final class course_dates implements checker {
    /** @var int Score weight. */
    private const WEIGHT = 1;

    /** @var int|null Fixed current time, used for deterministic tests. */
    private ?int $currenttime;

    /**
     * Constructor.
     *
     * @param int|null $currenttime Current timestamp, or null to use the system time.
     */
    public function __construct(?int $currenttime = null) {
        $this->currenttime = $currenttime;
    }

    /**
     * Analyse course dates.
     *
     * @param stdClass $course Course record.
     * @return result Check result.
     */
    public function check(stdClass $course): result {
        $startdate = (int) ($course->startdate ?? 0);
        $enddate = (int) ($course->enddate ?? 0);
        $settingsurl = new moodle_url('/course/edit.php', ['id' => $course->id]);
        $title = get_string('check:dates:title', 'report_coursecoach');

        if ($startdate <= 0 && $enddate <= 0) {
            return new result(
                false,
                result::STATUS_NOT_APPLICABLE,
                result::SEVERITY_RECOMMENDATION,
                $title,
                get_string('check:dates:notapplicable:explanation', 'report_coursecoach'),
                get_string('check:dates:notapplicable:recommendation', 'report_coursecoach'),
                $settingsurl
            );
        }

        if ($startdate > 0 && $enddate > 0 && $enddate <= $startdate) {
            $dates = (object) [
                'startdate' => userdate($startdate),
                'enddate' => userdate($enddate),
            ];
            return new result(
                true,
                result::STATUS_CRITICAL,
                result::SEVERITY_CRITICAL,
                $title,
                get_string('check:dates:invalid:explanation', 'report_coursecoach', $dates),
                get_string('check:dates:invalid:recommendation', 'report_coursecoach'),
                $settingsurl,
                get_string('action:dates', 'report_coursecoach')
            );
        }

        $currenttime = $this->currenttime ?? time();
        if ($enddate > 0 && $enddate < $currenttime) {
            return new result(
                true,
                result::STATUS_WARNING,
                result::SEVERITY_IMPORTANT,
                $title,
                get_string('check:dates:expired:explanation', 'report_coursecoach', userdate($enddate)),
                get_string('check:dates:expired:recommendation', 'report_coursecoach'),
                $settingsurl,
                get_string('action:dates', 'report_coursecoach')
            );
        }

        return new result(
            true,
            result::STATUS_PASSED,
            result::SEVERITY_RECOMMENDATION,
            $title,
            get_string('check:dates:passed:explanation', 'report_coursecoach'),
            get_string('check:dates:passed:recommendation', 'report_coursecoach'),
            $settingsurl
        );
    }

    /**
     * Return the date score weight.
     *
     * @return int
     */
    public function get_weight(): int {
        return self::WEIGHT;
    }
}
