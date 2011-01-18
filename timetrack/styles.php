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
 * This file contains styles used in the block display.
 *
 * @package    block_timetrack
 * @copyright  2008 Enovation Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Stephen Mc Guinness, Enovation Solutions
 */
?>

.ttquantity {
    width: 80px;
    text-align: right;
}

/* report table */

#ttdatecontainer {
    position: relative;
    width: 550px;
    margin: 0 auto;
    text-align: center;
}

.tttutortimes {
    margin-top: 15px;
    width: 550px;
}
.tttutortimes td {
    padding: 3px;
    padding-left: 5px;
    padding-right: 5px;
}

.ttlink {
    text-align: center;
    margin: 3px;
}

.tttutortimes thead {
    font-weight: bold;
}

.tttutortimes td.lal {
    text-align: left;
}

.tttutortimes td.ral {
    text-align: right;
}

.tttutortimes td.ralb {
    text-align: right;
    font-weight: bold;
}