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
 * This is a library of functions and constants for the Timetrack Moodle block
 *
 * @package    block_timetrack
 * @copyright  2008 Enovation Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Stephen Mc Guinness, Enovation Solutions
 */

/**
 * Function to return a set of html options from the prefix_timetrack_options table
 *
 * @param int     $selectedvalue The value in the dropdown options which will be selected
 * @param boolean $quantityrequired Pass by reference value indicating if the selected value has associated quantityrequired value set
 * @return mixed html options
 */
function timetrack_options($selectedvalue = null, &$quantityrequired = false) {
    global $CFG;

    $sqlquery = 'SELECT id,name,requiresquantity FROM '.$CFG->prefix.'timetrack_options';
    $timetrackoptions = get_records_sql($sqlquery);

    $options = '';

    if (is_array($timetrackoptions)) {
        foreach ($timetrackoptions as $timetrackoption) {
            if ($timetrackoption->id == $selectedvalue && $timetrackoption->requiresquantity > 0) {
                $quantityrequired = true;
            }
            $options .= '<option value="'.$timetrackoption->id.'"'.
                        (($selectedvalue == $timetrackoption->id) ? ' selected="selected"' : '').'>'.
                        $timetrackoption->name.
                        (($timetrackoption->requiresquantity) ? '*' : '').'</option>';
        }
    }
    return $options;
}

/**
 * Function to return an object for a timetrack_options record from the database
 *
 * @param int $taskid ID number for the required timtrack_options record 
 * @return object timetrackoptions object, id, name, rate
 */
function timetrack_get_taskobject($taskid = 0) {
    global $CFG;

    $record = get_record_sql('SELECT id,name,rate FROM '.$CFG->prefix.'timetrack_options WHERE id=\''.$taskid.'\'');
    return $record;
}

/**
 * Function that displays a HTML table listing the tasks and time entries a user has made.
 * Confirmation that these values are correct is requested.
 *
 * @param array $formobjects An array of objects, one for each task/time entry
 */
function timetrack_print_confirmation($formobjects = array()) {
    global $USER, $CFG;

    $strtimetrack  = get_string('modulename', 'block_timetrack');

    $html = '<div style="text-align: center;">'.
            "<h1>$strtimetrack</h1>".
            '</div>'.
            '<form method="post">'.
            timetrack_print_entryformhidden($formobjects).
            '<div style="width: 800px; margin: 0 auto;">'.
            '<div style="text-align: right;">'.
            '<a href='.$CFG->wwwroot.'/blocks/timetrack/view.php">'.
            get_string('ttviewlink','block_timetrack').'</a>'.
            '</div>'.
            '<table style="width: 100%;">'.
            '<thead style="font-weight: bold;">'.
            '<tr>'.
            '<td>'.get_string('ttheadname','block_timetrack').'</td>'.
            '<td>'.get_string('ttheaddate','block_timetrack').'</td>'.
            '<td>'.get_string('ttheadallocation','block_timetrack').'</td>'.
            '<td>'.get_string('ttheadquantity','block_timetrack').'</td>'.
            '<td colspan="2">'.get_string('ttheadtotal','block_timetrack').'</td>'.
            '</tr>'.
            '</thead>'.
            '<tbody>';

    $totalamount = 0.00;
    $userobject = null;

    if (is_array($formobjects)) {
        foreach ($formobjects as $formobject) {
            if ($formobject->allocation && $formobject->quantity) {
                $taskobject = timetrack_get_taskobject($formobject->allocation);
                if (!is_object($userobject)) {
                    $userobject = get_complete_user_data('id', $formobject->userid);
                } else if ($userobject->id != $formobject->userid) {
                    $userobject = get_complete_user_data('id', $formobject->userid);
                }

                $html .= '<tr>'.
                         '<td>'.fullname($userobject).'</td>'.
                         '<td>'.date("j/m/Y", $formobject->timetracking).'</td>'.
                         '<td>'.$taskobject->name.'</td>'.
                         '<td>'.$formobject->quantity.'</td>'.
                         '<td>&euro;</td>'.
                         '<td style="text-align: right;">'.
                          number_format(($formobject->quantity * $taskobject->rate), 2).'</td>'.
                         '</tr>';
                $totalamount += ($formobject->quantity * $taskobject->rate);
            }
        }
    }

    $html .= '<tr>'.
             '<td colspan="4"> &#160; </td>'.
             '<td colspan="2"> <hr /> </td>'.
             '</tr>'.
             '<tr>'.
             '<td colspan="4"> &#160; </td>'.
             '<td> &euro; </td>'.
             '<td style="text-align: right;">'.number_format($totalamount, 2).'</td>'.
             '</tr>'.
             '<tr>'.
             '<td colspan="6" style="text-align: right;">'.
             '<input type="submit" name="ttconfirmcancel" id="ttconfirmcancel" value="'.
             get_string('ttcancelbutton','block_timetrack').'" />'.
             '<input type="submit" value="'.
             get_string('ttconfirmbutton', 'block_timetrack').
             '" name="ttconfirm" id="ttconfirm" /> </td>'.
             '</tr>'.
             '</tbody>'.
             '</table>'.
             '</div>'.
             '</form>';

    echo $html;
}

/**
 * Function to provide the form for adding times
 *
 * @param string $timelines
 * @return mixed HTML form
 */
function timetrack_print_entryform($timelines = '') {
    global $USER, $CFG;

    $strtimetrack = get_string('modulename', 'block_timetrack');
    $html = '<div style="text-align: center;">'.
            '<h1>'.$strtimetrack.'</h1>'.
            '</div>'.
            '<form method="post">'.
            '<input type="hidden" value="" name="ttuser" id="ttuser" />'.
            '<input type="hidden" value="" name="ttdate" id="ttdate" />'.
            '<div style="width: 800px; margin: 0 auto;">'.
            '<div style="text-align: right;">'.
            '<a href="'.$CFG->wwwroot.'/blocks/timetrack/view.php">'.
            get_string('ttviewlink', 'block_timetrack').'</a>'.
            '</div>'.
            '<table style="width: 100%;">'.
            '<thead style="font-weight: bold;">'.
            '<tr>'.
            '<td>'.get_string('ttheadname','block_timetrack').'</td>'.
            '<td>'.get_string('ttheaddate','block_timetrack').'</td>'.
            '<td>'.get_string('ttheadallocation','block_timetrack').'</td>'.
            '<td>'.get_string('ttheadquantity','block_timetrack').'</td>'.
            '</tr>'.
            '</thead>'.
            '<tbody>'.
            $timelines.
            '<tr>'.
            '<td colspan="2"> &#160; </td>'.
            '<td colspan="3"> '.get_string('ttrequiredhint','block_timetrack').'</td>'.
            '</tr>'.
            '<tr>'.
            '<td colspan="5" style="text-align: right;">'.
            '<input type="submit" value="'.get_string('ttsubmitbutton','block_timetrack').'" /> </td>'.
            '</tr>'.
            '</tbody>'.
            '</table>'.
            '</div>'.
            '</form>';
    echo $html;
}

/**
 * Function which provides a set of HTML hidden input elements for each of the task/time objects
 * Used to allow the user cancel their submission and ammend it before committal 
 *
 * @param array $formobjects An array of objects, an object for each task/time entry
 * @return string Set of HTML hidden INPUT elements with quantity and allocation IDs
 */
function timetrack_print_entryformhidden($formobjects = array()) {
    global $USER, $CFG;

    $strreturn = '';
    if (is_array($formobjects)) {
        foreach ($formobjects as $ind => $formobject) {
            if ($formobject->allocation && $formobject->quantity) {
                $taskobject = timetrack_get_taskobject($formobject->allocation);
                $strreturn .= '
                    <input type="hidden" name="tttimetracking[]" id="tttimetracking'.
                    $ind.'" value="'.$formobject->timetracking.'" />'.
                    '<input type="hidden" name="ttquantity[]" id="ttquantity'.
                    $ind.'" value="'.$formobject->quantity.'" />'.
                    '<input type="hidden" name="ttallocation[]" id="ttallocation'.
                    $ind.'" value="'.$taskobject->id.'" />';
            }
        }
    }

    return $strreturn;
}

/**
 * Function to convert submitted allocation ID numbers and quantities to objects
 * Also indicates if there are any errors in the allocation/quantity pairings
 * Further indicates if any valid submissions have been made
 *
 * @param array   $allocations
 * @param array   $quantities
 * @param boolean $haveerrors pass by reference
 * @param boolean $havesubmission pass by reference
 * @param array   $tttimetracking
 * @return array Array of objects
 */
function timetrack_return_formobjects($allocations = array(),
                                      $quantities = array(),
                                      &$haveerrors = false,
                                      &$havesubmission = false,
                                      $tttimetracking = array()) {
    global $USER;

    $arreturn = array();
    $allocationsselected = 0; //number of allocations the user has selected, should be > 0
    for ($i = 0; $i <= 4; $i++) {
        $arreturn[$i]               = new stdClass();
        $arreturn[$i]->userid       = 0;
        $arreturn[$i]->timetracking = 0;
        $arreturn[$i]->quantity     = 0;
        $arreturn[$i]->allocation   = 0;

        if (!isset($allocations[$i])) {
            $allocations[$i] = 0;
        }

        $highlightquantity = false;
        $highlightapplicable = false;
        timetrack_options($allocations[$i], $highlightapplicable);

        if (!isset($allocations[$i])) {
            $allocations[$i] = 0;
        }
        if (!isset($quantities[$i])) {
            $quantities[$i] = 0;
        }

        //highligh a required quantity if an allocation is set and quantity is not
        if ($allocations[$i] && !$quantities[$i] && $highlightapplicable) {
            $highlightquantity = true;
            $haveerrors = true;
        } else if ($allocations[$i] && !$highlightapplicable) {
            $arreturn[$i]->quantity = 1;
            $havesubmission = true;
        } else if ($allocations[$i] && !$quantities[$i]) {
            //default to a quantity of 1 for other selections
            $arreturn[$i]->quantity = 1;
            $havesubmission = true;
        } else if($allocations[$i] && $quantities[$i] && $highlightapplicable) {
            $havesubmission = true;
        }

        $arreturn[$i]->userid       = $USER->id;
        $arreturn[$i]->timetracking = (($tttimetracking[$i]) ? $tttimetracking[$i] : time());
        $arreturn[$i]->quantity     = $quantities[$i];
        $arreturn[$i]->allocation   = $allocations[$i];
        $arreturn[$i]->rate         = 0.00;
        $arreturn[$i]->total        = 0.00;

        if ($arreturn[$i]->allocation > 0) {
            $timetrackoptionobject = timetrack_get_taskobject($arreturn[$i]->allocation);
            if (is_object($timetrackoptionobject)) {
                $arreturn[$i]->rate = $timetrackoptionobject->rate;
                $arreturn[$i]->total = ($timetrackoptionobject->rate * $arreturn[$i]->quantity);
            }
        }
    } //end for loop
    return $arreturn;
}

/**
 * Function to return HTML table rows containing form elements with the values
 * of the $formobjects parameter (if any)
 *
 * @param array $formobjects An array of objects with values for the html elements
 * @return string Table rows
 */
function timetrack_return_formentries($formobjects = array()) {
    global $USER;

    $strreturn = '';
    $allocationsselected = 0; //number of allocations the user has selected, should be > 0
    for ($i = 0; $i <= 4; $i++) {
        $highlightquantity = false;
        $highlightstyle = '';
        $highlightapplicable = false;
        $htmloptions = timetrack_options($formobjects[$i]->allocation, $highlightapplicable);

        if (!isset($formobjects[$i]->allocation)) {
            $formobjects[$i]->allocation = 0;
        }
        if (!isset($formobjects[$i]->quantity)) {
            $formobjects[$i]->quantity = 0;
        }

        //highlight a required quantity if an allocation is set and quantity is not
        if ($formobjects[$i]->allocation && !$formobjects[$i]->quantity && $highlightapplicable) {
            $highlightquantity = true;
            $highlightstyle = '; font-weight: bold; background-color: #FFAAAA;';
            $haveerrors = true;
        } else if ($formobjects[$i]->allocation && !$highlightapplicable) {
            $formobjects[$i]->quantity = 1;
        } else if ($formobjects[$i]->allocation && !$formobjects[$i]->quantity) {
            //default to a quantity of 1 for other selections
            $formobjects[$i]->quantity = 1;
        }

        if ($USER->id == $formobjects[$i]->userid || !$formobjects[$i]->userid) {
            $username = fullname($USER);
        } else if($formobjects[$i]->userid) {
            $userobject = get_complete_user_data('id', $formobjects[$i]->userid);
            $username = fullname($userobject);
        }

        $strreturn .= '<tr><td> '.$username.' </td><td>';
        $strreturn .= timetrack_print_day_selector('tttimetracking_day['.$i.']',
                                                   date('j', $formobjects[$i]->timetracking), 
                                                   true);
        $strreturn .= timetrack_print_month_selector('tttimetracking_month['.$i.']',
                                                     date('n', $formobjects[$i]->timetracking),
                                                     true);
        $strreturn .= timetrack_print_year_selector('tttimetracking_year['.$i.']',
                                                    date('Y', $formobjects[$i]->timetracking),
                                                    true);
        $strreturn .= '</td><td>'.
                      '<select id="ttallocation'.$i.'" name="ttallocation[]">'.
                      '<option> </option>'.
                      $htmloptions.
                      '</select>'.
                      '</td>'.
                      '<td> '.
                      '<input type="text" style="width: 65px; text-align: right;'.
                      $highlightstyle.
                      '" name="ttquantity[]" id="ttquantity'.$i.
                      '" class="ttquantity" value="'.
                      $formobjects[$i]->quantity.'" /> </td>'.
                      '</tr>';
    } //end for loop
    return $strreturn;
}

/**
 * Print the timetrack report in csv format
 *
 * @param int $timefrom
 * @param int $timeto
 * @param int $userid
 */
function timetrack_print_csv($timefrom = 0, $timeto = 0, $userid = 0) {
    if (!$timefrom) {
        $timefrom = time();
    }
    if (!$timeto) {
        $timeto = time();
    }
    $userobject = get_record('user', 'id', $userid);
    if (!$userobject) {
        $userobject = new stdClass();
    }
    echo timetrack_view_datatable_csv(timetrack_get_dataset($timefrom, $timeto, $userid),
                                      $timefrom,
                                      $timeto,
                                      $userid);
}

/**
 * Function to return a HTML select element populated with the names of
 * tutors who have time tracking data entered
 *
 * @param int $userid User id for the currently selected tutor
 * @param string $fieldid name for the dropdown field
 * @param string $additional Additional parameters for the select element
 * @return string HTML element
 */
function timetrack_print_tutor_dropdown($userid = 0,
                                        $fieldid = 'userid',
                                        $additional = '') {
    $returnstring = '<select id="'.$fieldid.'" name="'.$fieldid.'" '.$additional.'>'.
                    timetrack_print_tutor_dropdown_options($userid).'</select>';
    return $returnstring;
}

/**
 * Function that returns a string containing HTML option elements 
 * with the name and user id for tutors who have made time track entries
 *
 * @param int $userid User Id for the currently selected tutor
 * @return string HTML options
 */
function timetrack_print_tutor_dropdown_options($userid = 0) {
    global $CFG;

    $returnoptions = '<option value="0"></option>';
    $tutorquery = 'SELECT DISTINCT userid FROM '.$CFG->prefix.'timetrack';
    $results = get_records_sql($tutorquery);

    $tutors = array();
    $tutoroptions = "";

    if (is_array($results)) {
        foreach ($results as $stdobject) {
            if (is_object($stdobject)) {
                if ($stdobject->userid) {
                    $tutors[$stdobject->userid] =
                            fullname(get_record('user', 'id', $stdobject->userid));
                }
            }
        }
        asort($tutors);
        foreach ($tutors as $tutorid => $tutorname) {
            $tutoroptions .= '<option value="'.$tutorid.'"'. 
                             (($userid==$tutorid && $tutorid) ? ' selected="selected"' : '').
                             '>'.$tutorname.'</option>';
        }
    }
    return $returnoptions.$tutoroptions;
}

/**
 * Function to output a form allowing a date range selection
 * A table of tutor time tracking entries will be output
 *
 * @param int $timefrom Time value for the start of period date options
 * @param int $timeto Time value for the end of period date options
 * @param int $userid ID of the User to display times for
 */
function timetrack_print_viewform($timefrom = 0, $timeto = 0, $userid = 0) {
    if ($timefrom == 0) {
        $timefrom = time();
    }
    if ($timeto == 0) {
        $timeto = time();
    }

    $userobject = get_record('user', 'id', $userid);

    if (!$userobject) {
        $userobject = new stdClass();
    }
    $html = '<div style="text-align: center;">'.
            '<h1>'.
            get_string('ttreportheading','block_timetrack').
            '</h1>'.
            '</div>'.
            '<div id="ttdatecontainer">'.
            '<form method="post">'.
            '<div style="float: left; text-align: center;">'.
            '<div>'.
            '<strong>'.
            get_string('ttreportstarthead', 'block_timetrack').
            '</strong>'.
            '<div>'.
            timetrack_print_day_selector('dayfrom', date('j', $timefrom), true).
            timetrack_print_month_selector('monthfrom', date('n', $timefrom), true).
            timetrack_print_year_selector('yearfrom', date('Y', $timefrom), true).
            '</div>'.
            '</div>'.
            '<div>'.
            '<strong>'.
            get_string('ttreportendhead', 'block_timetrack').
            '</strong>'.
            '<div>'.
            timetrack_print_day_selector('dayto', date('j', $timeto), true).
            timetrack_print_month_selector('monthto', date('n', $timeto), true).
            timetrack_print_year_selector('yearto', date('Y', $timeto), true).
            '</div>'.
            '</div>'.
            '</div>'.
            '<div style="float: right; text-align: right;">'.
            '<div>'.
            '<h3>';
    if (timetrack_canmanage()) {
        $html .= 'Tutor: ';
        $html .=  timetrack_print_tutor_dropdown($userobject->id,
                                                 'userid',
                                                 'onchange="this.form.submit();"');
    } else {
        $html .= fullname($userobject);
    }
    $html .= '</h3>'.
             '</div>'.
             '<div style="vertical-align: bottom;">'.
             '<input type="submit" value="'.
             get_string('ttviewfindbutton','block_timetrack').'" />'.
             '</div>'.
             '</div>'.
             '<div style="clear: both;"></div>'.
             '</form>';
    $html .= timetrack_view_datatable(timetrack_get_dataset($timefrom, $timeto, $userid),
                                      $timefrom,
                                      $timeto,
                                      $userid);
    $html .= '</div>';

    echo $html;
}

/**
 * Function provides a CSV table detailing time track entries 
 *
 * @param array $timetrackarray Array of objects with time track details
 * @param int   $timefrom
 * @param int   $timeto
 * @param int   $userid
 * @return mixed CSV table
 */
function timetrack_view_datatable_csv($timetrackarray = array(),
                                      $timefrom = 0,
                                      $timeto = 0,
                                      $userid = 0) {
    global $CFG;

    $runningtotal = 0.00;
    $strreturn = '';
    $strallocationarray = array();

    $userobject = get_record('user', 'id', $userid);

    if (is_array($timetrackarray) && sizeof($timetrackarray) > 0) {
        $strreturn .= timetrack_datatable_header_csv();

        if (is_object($userobject)) {
            $userfullname = fullname($userobject);
        } else {
            $userfullname = 'No user';
        }

        foreach ($timetrackarray as $timetrack) {
            $runningtotal += $timetrack->total;
            if (is_object($timetrack)) {
                if (!$strallocationarray[$timetrack->allocation]) {
                    $strallocationarray[$timetrack->allocation] =
                            get_record("timetrack_options", "id", $timetrack->allocation);
                }
                $timetrack->allocation = $strallocationarray[$timetrack->allocation]->name;
                $strreturn .= timetrack_datatable_row_csv($timetrack, $userfullname);
            }
        }
    }
    return $strreturn;
}

/**
 * Function provides a HTML table detailing time track entries 
 *
 * @param array $timetrackarray Array of objects with time track details
 * @param int   $timefrom
 * @param int   $timeto
 * @param int   $userid
 * @return mixed HTML table
 */
function timetrack_view_datatable($timetrackarray = array(),
                                  $timefrom = 0,
                                  $timeto = 0,
                                  $userid=0) {
    global $CFG;

    $runningtotal = 0.00;
    $strreturn = '';
    $strallocationarray = array();

    if (is_array($timetrackarray) && sizeof($timetrackarray) > 0) {
        $strreturn .=  timetrack_datatable_header();

        foreach ($timetrackarray as $timetrack) {
            $runningtotal += $timetrack->total;
            if (is_object($timetrack)) {
                if (!$strallocationarray[$timetrack->allocation]) {
                    $strallocationarray[$timetrack->allocation] =
                            get_record("timetrack_options", "id", $timetrack->allocation);
                }
                $timetrack->allocation = $strallocationarray[$timetrack->allocation]->name;
                $strreturn .= timetrack_datatable_row($timetrack);
            }
        }
        $strreturn .= timetrack_datatable_footer($runningtotal);
        $strreturn .= '<div>'.
                      '<form method="post" action="'.$CFG->wwwroot.'/blocks/timetrack/excel.php">'.
                      '<input type="hidden" name="userid" id="userid" value="'.$userid.'" />'.
                      '<input type="hidden" name="timefrom" id="timefrom" value="'.$timefrom.'" />'.
                      '<input type="hidden" name="timeto" id="timeto" value="'.$timeto.'" />'.
                      '<input type="submit" value="'.get_string('ttexporttoexcel','block_timetrack').'" />'.
                      '</form>'.
                      '</div>';
    } else {
        $strreturn .= notify(get_string('ttviewnoresults','block_timetrack'),
                      'errorbox',
                      'center',
                      true);
    }
    return $strreturn;
}

/**
 * HTML header for the data table
 *
 * @return mixed portion of HTML
 */
function timetrack_datatable_header() {
    $html = '<div>'.
            '<table class="tttutortimes">'.
            '<thead>'.
            '<tr>'.
            '<td class="lal"> '.get_string('ttheaddate', 'block_timetrack').' </td>'.
            '<td class="lal"> '.get_string('ttheadallocation', 'block_timetrack').' </td>'.
            '<td> '.get_string('ttheadquantity', 'block_timetrack').' </td>'.
            '<td class="ral"> '.get_string('ttheadrate', 'block_timetrack').' </td>'.
            '<td class="ral"> '.get_string('ttheadtotal', 'block_timetrack').' </td>'.
            '</tr>'.
            '</thead>'.
            '<tbody>';
    return $html;
}

/**
 * Returns a table row containing details of a tutor time tracking entry
 *
 * @param object $timetrack
 * @return mixed HTML table row
 */
function timetrack_datatable_row($timetrack = null) {
    $html .= '<tr>'.
             '<td class="lal">'.date('j/m/Y', $timetrack->timetracking).'</td>'.
             '<td class="lal">'.$timetrack->allocation.'</td>'.
             '<td>'.$timetrack->quantity.'</td>'.
             '<td class="ral"> &euro; '.$timetrack->rate.'</td>'.
             '<td class="ral"> &euro; '.$timetrack->total.'</td>'.
             '</tr>';
    return $html;
}

/**
 * returns portion of a HTML table, footer for the report data table
 *
 * @param int $runningtotal
 * @return mixed HTML content
 */
function timetrack_datatable_footer($runningtotal = 0.00) {
    $html = '<tr>'.
            '<td colspan="3"> &#160; </td>'.
            '<td> &#160; </td>'.
            '<td> <hr /> </td>'.
            '</tr>'.
            '<tr>'.
            '<td colspan="3"> &#160; </td>'.
            '<td class="ralb"> &euro; </td>'.
            '<td class="ralb">'.number_format($runningtotal, 2).'</td>'.
            '</tr>'.
            '</tbody>'.
            '</table>'.
            '</div>';
    return $html;
}

/**
 * Returns a CSV row containing details of a tutor time tracking entry
 *
 * @param object $timetrack Object with details of tutor time tracking entry
 * @param string $userfullname 
 * @return mixed CSV row, 5 columns, comma delimited
 */
function timetrack_datatable_row_csv($timetrack = false, $userfullname = '') {
    $csvrow = '"'.$userfullname.'",'.date('j/m/Y', $timetrack->timetracking).
              ',"'.$timetrack->allocation.'",'.$timetrack->quantity.','.
              $timetrack->rate.','.$timetrack->total."\n";
    return $csvrow;
}

/**
 * CSV header for the data table
 *
 * @return mixed portion of CSV, comma delimited 
 */
function timetrack_datatable_header_csv() {
    $csvportion = '"'.get_string('ttheadname','block_timetrack').'","'.
                  get_string('ttheaddate','block_timetrack').'","'.
                  get_string('ttheadallocation','block_timetrack').'","'.
                  get_string('ttheadquantity','block_timetrack').'","'.
                  get_string('ttheadrate','block_timetrack').'","'.
                  get_string('ttheadtotal','block_timetrack').'"' . "\n";
    return $csvportion;
}

/**
 * Function to query the database for tutor time track entries in a given
 * period, for a given user
 *
 * @param int $timefrom epoch time value, start of requested time period
 * @param int $timeto epoch time value, end of requested time period
 * @param int $userid User ID for the tutor 
 * @return array Array of objects, one ofr each row of the dataset
 */
function timetrack_get_dataset($timefrom = 0, $timeto = 0, $userid = 0) {
    global $CFG;

    $timefrom = $timefrom - 10;
    $timeto = $timeto + 10;
    //get the timetrack entries
    $sqlquery = 'SELECT id,timetracking,allocation,quantity,rate,total '.
                'FROM '.$CFG->prefix.'timetrack '.
                'WHERE timetracking > \''.$timefrom.'\' '.
                'AND timetracking < \''.$timeto.'\' '.
                'AND userid=\''.$userid.'\' '.
                'ORDER BY timetracking ASC';
    return get_records_sql($sqlquery);
}

/**
 * Function to output the dropdown day selection
 *
 * @param string  $name Fieldname for the dropdown
 * @param int     $selected Value for the selected option (if any)
 * @param boolean $return
 * @return mixed
 */
function timetrack_print_day_selector($name = 'day',
                                      $selected = null,
                                      $return = false) {
    $days = array();
    for ($i = 1; $i <= 31; $i++) {
        $days[$i] = $i;
    }
    return choose_from_menu($days, $name, $selected, '', '', 0, $return);
}

/**
 * Function to output the dropdown month selection
 *
 * @param string  $name Fieldname for the dropdown
 * @param int     $selected Value for the selected option (if any)
 * @param boolean $return
 * @return mixed
 */
function timetrack_print_month_selector($name = 'month',
                                        $selected = null,
                                        $return = false) {
    $months = array();

    for ($i = 1; $i <= 12; $i++) {
        $months[$i] = userdate(gmmktime(12, 0, 0, $i, 15, 2000), '%B');
    }
    return choose_from_menu($months, $name, $selected, '','',0,$return);
}

/**
 * Function to output the dropdown year selection
 *
 * @param string  $name Fieldname for the dropdown
 * @param int     $selected Value for the selected option (if any)
 * @param boolean $return
 * @return mixed
 */
function timetrack_print_year_selector($name = 'year',
                                       $selected = null,
                                       $return = false) {
    $yearss = array();

    for ($i = date("Y"); $i >= 2004; $i--) {
        $years[$i] = $i;
    }
    return choose_from_menu($years, $name, $selected, '', '', 0, $return);
}

/**
 * Commit an array of time track entry objects to the database
 *
 * @param array timetrackobjects Array of objects containing details for each record
 * @return boolean Success/failure
 */
function timetrack_add_multipleinstances($timetrackobjects = null) {
    global $USER;

    if (is_array($timetrackobjects)) {
        foreach ($timetrackobjects as $timetrack) {
            $issetuserid = $timetrack->userid > 0;
            $issettimetracking = $timetrack->timetracking > 0;
            $issetquantity = $timetrack->quantity > 0;
            $issetallocation = $timetrack->allocation > 0;
            if ($issetuserid && $issettimetracking && $issetquantity && $issetallocation) {
                if ($USER->id == $timetrack->userid || timetrack_canmanage()) {
                    timetrack_add_instance($timetrack);
                } else {
                    return false; //user doesnt have permission
                }
            }
        }
        return true;
    } else {
        return false;
    }
    return true;
}

/**
 * Function returns true if the current user has particular participate capability
 *
 * @return boolean has capability
 */
function timetrack_canuse() {
    global $USER;

    /* has permissions through system role */
    if (has_capability('mod/timetrack:participate', get_context_instance(CONTEXT_SYSTEM))) {
        return true;
    } else {
        /* Even if a teacher is not assigned the system role of teacher, but is a teacher in any course, 
         * they should be able to use the tutor time tracking */
        if (isteacherinanycourse($USER->id)) {
            return true;
        } else {
            return false;
        }
    }
}

/**
 * Function returns true if the current user has particular management capability
 *
 * @return boolean has capability
 */
function timetrack_canmanage() {
    if (has_capability('mod/timetrack:manage', get_context_instance(CONTEXT_SYSTEM))) {
        return true;
    }else{
        return false;
    }
}

/**
 * Given an object containing all the necessary data, (defined by the form in mod.html) this function
 * will create a new instance and return the id number of the new instance.
 *
 * @param object $instance An object from the form in mod.html
 * @return int The id of the newly inserted timetrack record
 */
function timetrack_add_instance($timetrack = null) {
    if (is_object($timetrack)) {
        if ($timetrack->allocation && $timetrack->quantity && $timetrack->userid) {
            $timetrack->timemodified = time();
            return insert_record("timetrack", $timetrack);
        }
    }
}

/**
 * Given an object containing all the necessary data, (defined by the form in mod.html) this function
 * will update an existing instance with new data.
 *
 * @param object $instance An object from the form in mod.html
 * @return boolean Success/Fail
 */
function timetrack_update_instance($timetrack) {
    $timetrack->timemodified = time();
    $timetrack->id = $timetrack->instance;

    return update_record("timetrack", $timetrack);
}

/**
 * Given an ID of an instance of this module, 
 * this function will permanently delete the instance 
 * and any data that depends on it. 
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function timetrack_delete_instance($id) {
    if (!$timetrack = get_record("timetrack", "id", "$id")) {
        return false;
    }
    $result = true;

    # Delete any dependent records here #
    if (!delete_records("timetrack", "id", "$timetrack->id")) {
        $result = false;
    }
    return $result;
}

//////////////////////////////////////////////////////////////////////////////////////
/// Any other timetrack functions go here.  Each of them must have a name that 
/// starts with timetrack_
