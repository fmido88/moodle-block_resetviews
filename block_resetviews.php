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
 * Block resetviews is defined here.
 *
 * @package     block_resetviews
 * @copyright   2023 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_resetviews extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_resetviews');
    }
    public function instance_allow_multiple() {
        return false;
    }
    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {
        global $CFG, $COURSE, $DB, $USER;

        require_once(__DIR__.'/locallib.php');

        $this->content = new stdClass();
        if (!has_capability('block/resetviews:view', $this->context)) {
            return $this->content;
        }
        $this->content->icons = array();
        $this->content->footer = '';
        $this->content->items = array();

        if (PARAM_URL !== '') {

            $id = required_param('id', PARAM_INT); // Course id.
            $instanceid = optional_param('instanceid', 0, PARAM_INT); // Instance we're looking at.

            $course = $DB->get_record('course', array('id' => $id));
            $modinfo = get_fast_modinfo($course);

            $this->content->text = '<form class="resetviewsform form-inline" action="
                '.$CFG->wwwroot.'\blocks\resetviews\action.php" method="post">';

            $cmselect = participation_print_filter_form($course, $instanceid);
            $this->content->text .= '</br>'.$cmselect;

            $usersselect = users_print_filter_form($course);
            $this->content->text .= $usersselect;

            $input = '<input name="value" type="integer"/>';
            $this->content->text .= $input;

            $submit = '<input type="submit" name = "ok" value="ok"/>';
            $this->content->text .= $submit;

            $text = '</br>Select users to reset its views';

            $this->content->text .= $text;
            $this->content->text .= '</form>';

            return $this->content;
        } else {
            return $this->content;
        }
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediately after init().
     */
    public function specialization() {

        // Load user defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_resetviews');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Enables global configuration of the block in settings.php.
     *
     * @return bool True if the global configuration is enabled.
     */
    public function has_config() {
        return false;
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats() {
        return array(
            'course-view' => true,
        );
    }
}
