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
 * Weighted Course Coach result.
 *
 * @package   report_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_coursecoach;

use coding_exception;
use report_coursecoach\check\result;

/**
 * Associates one checker result with its readiness score weight.
 */
final class weighted_result {
    /** @var result Check result. */
    private result $result;

    /** @var int Positive score weight. */
    private int $weight;

    /**
     * Constructor.
     *
     * @param result $result Check result.
     * @param int $weight Positive score weight.
     */
    public function __construct(result $result, int $weight) {
        if ($weight <= 0) {
            throw new coding_exception('Course Coach check weights must be positive.');
        }
        $this->result = $result;
        $this->weight = $weight;
    }

    /**
     * Return the check result.
     *
     * @return result Check result.
     */
    public function get_result(): result {
        return $this->result;
    }

    /**
     * Return the score weight.
     *
     * @return int Score weight.
     */
    public function get_weight(): int {
        return $this->weight;
    }
}
