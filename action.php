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
 * Block resetviews action after submit the value.
 *
 * @package     block_resetviews
 * @copyright   2023 Mohammad Farouk <phun.for.physics@gmail.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require(__DIR__.'/locallib.php');
global $CFG;

require_login();

$cmid = $_POST['instanceid'];
$userid = $_POST['user'];
$value = $_POST['value'];

if ($cmid == null) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    insertresetviews($cmid, $userid, $value, time());
    header('Location: ' . $_SERVER['HTTP_REFERER']);
}
