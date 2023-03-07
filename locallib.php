<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin internal classes, functions and constants are defined here.
 *
 * @package     block_resetviews
 * @copyright   2023 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** Creates a select menu of course module in the course
 *
 * @param object $course geting the course id from it
 * @param int $instanceid instance id
 *
 * @return string html writer select menue
 */
function cm_print_filter_form($course, $instanceid) {
    global $DB;

    $modinfo = get_fast_modinfo($course);

    $modules = $DB->get_records_select('modules', "visible = 1", null, 'name ASC');

    $instanceoptions = array();
    foreach ($modules as $module) {
        if (empty($modinfo->instances[$module->name])) {
            continue;
        }
        $instances = array();
        foreach ($modinfo->instances[$module->name] as $cm) {
            // Skip modules such as label which do not actually have links;
            // this means there's nothing to participate in.
            // TODO filter course modules by that only has maxviews conditions.
            if (!$cm->has_view()) {
                continue;
            }
            $instances[$cm->id] = format_string($cm->name);
        }
        if (count($instances) == 0) {
            continue;
        }
        $instanceoptions[] = array(get_string('modulenameplural', $module->name) => $instances);
    }

    $form = '<input type = "hidden" name = "id" value ="'.$course->id.'" />'."\n";
    $form .= "<div style=\"max-width:100%;\">";
    $form .= '<label for="menuinstanceid">'.get_string('activitymodule').'</label>'."\n";
    $form .= html_writer::select($instanceoptions, 'instanceid', $instanceid, false);
    $form .= '</br>';

    return $form;
}
/** Print the select list of active users in the course
 * @param object $course getting the course id from it
 *
 * @return string html write the select menu of users
 */
function users_print_filter_form($course) {
    global $DB, $USER;

    $context = context_course::instance($course->id);

    $users = get_enrolled_users($context, "", 0, "u.*", 'firstname', 0, 0, true);

    foreach ($users as $user) {
        // TODO Show only students in this course.

        $userselect[$user->id] = format_string($user->firstname."\n".$user->lastname);

    }
    $form = '<input type="hidden" name="id" value="'.$course->id.'" />'."\n";
    $form .= '<label for="menuuserid">'.'User'.'</label>'."\n";
    if (!$user || !$userselect) {
        return $form;
    }
    $form .= html_writer::select($userselect, 'user', $user->id, false);
    $form .= '</br>';

    return $form;
}
/** The function responsible for iserting the value added in the database
 * @param int $cmid course module id
 * @param int $userid user id
 * @param int $value the value added to increase or decrease the view limit
 * @param int $time the current time of modification
 *
 * @return stdclass
 */
function insertresetviews($cmid, $userid, $value, $time) {
    global $DB;

    $data = new \stdClass;
    $data->cmid = $cmid;
    $data->userid = $userid;
    $data->timeaccess = $time;

    if (!$DB->record_exists('block_resetviews', array('cmid' => $cmid, 'userid' => $userid))) {
        $data->value = $value;
        $insert = $DB->insert_record('block_resetviews', $data);
        return $insert;
    } else {
        $old = $DB->get_field('block_resetviews', 'value', array('cmid' => $cmid, 'userid' => $userid), IGNORE_MISSING);
        $data->value = (int)$value + (int)$old;
        $DB->delete_records('block_resetviews', array('cmid' => $cmid, 'userid' => $userid));
        $update = $DB->insert_record('block_resetviews', $data);
        return $update;
    }
}

/** This function is used by the plugin availability_maxviews to modify the limitation
 * for certain user, it read the value inserted before in the database an add it the limit.
 *
 * @param int $cmid course module id
 * @param int $userid user id
 *
 * @return int return the value to be added to the limit
 */
function resetviews($cmid, $userid) {
    global $DB;
    $views = $DB->get_field('block_resetviews', 'value', array('cmid' => $cmid, 'userid' => $userid), IGNORE_MISSING);
    return $views;
}
