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

/**
 * Handle the reset views event.
 *
 * @param object $event The event object.
 */
function participation_print_filter_form($course, $instanceid) {
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

    $form = "</br>".'<input type = "hidden" name = "id" value ="'.$course->id.'" />'."\n";
    $form .= "<div>";
    $form .= '<label for="menuinstanceid">'.get_string('activitymodule').'</label>'."\n";
    $form .= html_writer::select($instanceoptions, 'instanceid', $instanceid);
    $form .= '</br>';

    return $form;
}
function users_print_filter_form($course) {
    global $DB, $USER;

    $context = context_course::instance($course->id);

    $users = get_enrolled_users($context, "", 0, "u.*", null, 0, 0, false);

    foreach ($users as $user) {
        $instances[$user->id] = format_string($user->firstname."\n".$user->lastname);
    }
    $form = '<input type="hidden" name="id" value="'.$course->id.'" />'."\n";
    $form .= "<div>";
    $form .= '<label for="menuuserid">'.'User'.'</label>'."\n";
    $form .= html_writer::select($instances, 'user', $user->id);
    $form .= '</br>';

    return $form;
}

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

function resetviews($cmid, $userid) {
    global $DB;
    $views = $DB->get_field('block_resetviews', 'value', array('cmid' => $cmid, 'userid' => $userid), IGNORE_MISSING);
    return $views;
}