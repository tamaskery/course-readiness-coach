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
 * Moodle callbacks for the Course Readiness Coach plugin.
 *
 * @package   local_coursecoach
 * @copyright 2026 Course Coach contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Add Course Readiness Coach to the course navigation for authorised users.
 *
 * @param navigation_node $navigation Course navigation node.
 * @param stdClass $course Course record.
 * @param context_course $context Course context.
 * @return void
 */
function local_coursecoach_extend_navigation_course(
    navigation_node $navigation,
    stdClass $course,
    context_course $context
): void {
    if ($course->id == SITEID || !has_capability('local/coursecoach:view', $context)) {
        return;
    }

    $url = new moodle_url('/local/coursecoach/report.php', ['id' => $course->id]);
    $node = $navigation->add(
        get_string('navigationlink', 'local_coursecoach'),
        $url,
        navigation_node::TYPE_SETTING,
        null,
        'local_coursecoach'
    );
    $node->set_force_into_more_menu(true);
}
