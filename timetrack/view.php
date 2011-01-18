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
 * This page provides a date range selection form, which generates a report
 *
 * @package    block_timetrack
 * @copyright  2008 Enovation Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Stephen Mc Guinness, Enovation Solutions
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/lib.php');

$userid = optional_param('userid', 0, PARAM_INT);

$dayfrom = optional_param('dayfrom', 0, PARAM_INT);
$monthfrom = optional_param('monthfrom', 0, PARAM_INT);
$yearfrom = optional_param('yearfrom', 0, PARAM_INT);

$dayto = optional_param('dayto', 0, PARAM_INT);
$monthto = optional_param('monthto', 0, PARAM_INT);
$yearto = optional_param('yearto', 0, PARAM_INT);

if (!$dayfrom || !$dayto) {
    $timefrom = mktime(0, 0, 1, date('m'), date('j'), date('Y'));
    $timeto = mktime(23, 59, 59, date('m'), date('j'), date('Y'));
} else {
    $timefrom = mktime(0, 0, 1, $monthfrom, $dayfrom, $yearfrom);
    $timeto = mktime(23, 59, 59, $monthto, $dayto, $yearto);
}

if ($timefrom > $timeto) {
    $tmptime = $timefrom;
    $timefrom = $timeto;
    $timeto = $tmptime;
}

if (!$userid) {
    $userid = $USER->id;
}

if ($userid != $USER->id || !timetrack_canuse()) {
    if (!timetrack_canmanage()) {
        error(get_string('ttnocapability','block_timetrack'));
    }
}

$ttquantity = optional_param('ttquantity', null, PARAM_RAW);
$ttallocation = optional_param( 'ttallocation', null, PARAM_RAW);

if (is_array($ttquantity) && is_array($ttallocation)) {
    foreach (array('ttquantity', 'ttallocation') as $myAr) {
        foreach (${$myAr} as $ind=>$val) {
            ${$myAr}[$ind] = clean_param($val, PARAM_INT);
        }
    }
}

print_header_simple(get_string("tttimeview",'block_timetrack'), "",
        '<a href="'.$CFG->wwwroot.'/blocks/timetrack/add.php">'.
        get_string("ttblocktitle",'block_timetrack').'</a>->'.
        get_string('tttimeview','block_timetrack'), '', '', true, '', '');

echo timetrack_print_viewform($timefrom, $timeto, $userid);

print_footer();
