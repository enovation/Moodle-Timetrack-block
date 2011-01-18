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
 * This page outputs a report as a CSV file, containing Tutor Time Tracking data
 *
 * @package    block_timetrack
 * @copyright  2008 Enovation Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Stephen Mc Guinness, Enovation Solutions
 */

require_once('../../config.php');
require_once('lib.php');

$userid = optional_param('userid', 0, PARAM_INT);
$timefrom = optional_param('timefrom', 0, PARAM_INT);
$timeto = optional_param('timeto', 0, PARAM_INT);

if (!$timefrom || !$timeto) {
    $timefrom = mktime(0, 0, 1, date('m'), date('j'), date('Y'));
    $timeto = mktime(23, 59, 59, date('m'), date('j'), date('Y'));
}

if ($timefrom > $timeto) {
    $tmptime = $timefrom;
    $timefrom = $timeto;
    $timeto = $tmptime;
}

if (!$userid) {
    $userid = $USER->id;
}

if( $userid != $USER->id || !timetrack_canuse() ) {
    if (!timetrack_canmanage()) {
        error(get_string('ttnocapability','block_timetrack'));
    }
}

header('Content-type: text/csv');
header('Content-disposition: attachment; filename=tutortimetracking-' . date('YmdHi'). '.csv');
echo timetrack_print_csv($timefrom, $timeto, $userid);