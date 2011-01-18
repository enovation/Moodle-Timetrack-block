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
 * Add time tracking page.
 *
 * This page presents a form allowing tutor time tracking entries
 * It also displays the same entries for confirmation
 *
 * @package    block_timetrack
 * @copyright  2008 Enovation Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Stephen Mc Guinness, Enovation Solutions
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/lib.php');

if (!timetrack_canuse() && !timetrack_canmanage()) {
    error(get_string('ttnocapability','block_timetrack'));
}

$ttquantity = optional_param('ttquantity', null, PARAM_RAW);
$ttallocation = optional_param('ttallocation',   null, PARAM_RAW);
$ttconfirmcancel = optional_param('ttconfirmcancel', null, PARAM_ALPHA);
$ttconfirm = optional_param('ttconfirm', null, PARAM_ALPHA);
$tttimetracking = array();
$tttimetracking = optional_param('tttimetracking', null, PARAM_RAW);
$tttimetrackingday = optional_param('tttimetracking_day', null, PARAM_RAW);
$tttimetrackingmonth = optional_param('tttimetracking_month', null, PARAM_RAW);
$tttimetrackingyear = optional_param('tttimetracking_year', null, PARAM_RAW);

$haveerrors = false;  //indicate errors in the form submitted
$havesubmission = false;  //indicate a form has been submitted

//clean the raw parameters 
if (is_array($ttquantity) || is_array($ttallocation)) {
    foreach (array('ttquantity', 'ttallocation') as $myarray) {
        if (is_array(${$myarray})) {
            foreach (${$myarray} as $ind => $val) {
                ${$myarray}[$ind] = clean_param($val, PARAM_INT);
                ${$myarray}[$ind] = (int)${$myarray}[$ind];
                if (${$myarray}[$ind] < 1) {
                    ${$myarray}[$ind] = 0;
                }
            }
        }
    }
}

if (is_array($tttimetrackingday) || is_array($tttimetrackingmonth) || is_array($tttimetrackingyear)) {
    foreach ($tttimetrackingday as $ind => $val) {
        $tttimetracking[$ind] =
                mktime(0, 0, 1, $tttimetrackingmonth[$ind], $tttimetrackingday[$ind], $tttimetrackingyear[$ind]);
        if ($tttimetracking[$ind] < mktime(0, 0, 1, 1, 1, 1999)) {
            $tttimetracking[$ind] = time();
        }
    }
}

print_header_simple(get_string("tttimeentry","block_timetrack"), "",
                    get_string("ttblocktitle","block_timetrack").'->'.get_string('tttimeentry','block_timetrack'),
                    '', '', true, '', '');

$haveerrors = false;
$formobjects = timetrack_return_formobjects($ttallocation, $ttquantity, $haveerrors,
                                            $havesubmission, $tttimetracking);
$formlines = timetrack_return_formentries($formobjects);
if ($haveerrors) {
    echo notify(get_string('tterrorrequired','block_timetrack'), 'errorbox', 'center', true);
}

if ($havesubmission && $ttconfirm && !$haveerrors) {
    //make the entries
    if (!timetrack_add_multipleinstances($formobjects)) {
        echo notify(get_string('tterrorsaving','block_timetrack'), 'errorbox', 'center', true);
        timetrack_print_entryform($formlines);
    } else {
        echo notify(get_string('ttsuccesssaving','block_timetrack'), 'notifysuccess', 'center', true);
        timetrack_print_entryform(timetrack_return_formentries(
                timetrack_return_formobjects(null, null, $haveerrors, $havesubmission)));
    }
} else if ($havesubmission && !$haveerrors && !$ttconfirmcancel) {
    //request confirmation
    timetrack_print_confirmation($formobjects);
} else {
    //show task allocation entry form
    timetrack_print_entryform($formlines);
}

print_footer();
