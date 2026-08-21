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
 * Formatting helpers for names embedded in checker result text.
 *
 * @package   local_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursecoach\check;

use cm_info;
use section_info;
use stdClass;

/**
 * Produces semantic name text for final escaping by the output layer.
 */
final class name_formatter {
    /**
     * Format an activity name without applying intermediate HTML escaping.
     *
     * @param cm_info $cm Course-module information.
     * @return string Formatted semantic activity name.
     */
    public static function activity(cm_info $cm): string {
        return format_string($cm->name, true, [
            'context' => $cm->context,
            'escape' => false,
        ]);
    }

    /**
     * Return a course-format section display name as semantic text.
     *
     * Course formats return display-ready names, so decode their entities before
     * the result is passed to the normally escaped Mustache variable.
     *
     * @param stdClass $course Course record.
     * @param section_info $section Course section information.
     * @return string Formatted semantic section name.
     */
    public static function section(stdClass $course, section_info $section): string {
        return html_entity_decode(get_section_name($course, $section), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
