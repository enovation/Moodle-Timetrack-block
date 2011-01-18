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
 * This file contains block_timetrack class.
 *
 * @package    block_timetrack
 * @copyright  2008 Enovation Solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Stephen Mc Guinness, Enovation Solutions
 */

require_once($CFG->dirroot.'/blocks/timetrack/lib.php');

/**
 * block_timetrack class
 *
 * @copyright 2008 Enovation Solutions
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Stephen Mc Guinness, Enovation Solutions
 */
class block_timetrack extends block_base {
    /**
     * init() function of the block class
     */
    function init() {
        $this->title = get_string('ttblocktitle', 'block_timetrack');
        $this->version = 2010111701;
    }

    /**
     * This function update block title if specialization is set.
     */
    function specialization() {
        $this->title = isset($this->config->title) ? $this->config->title : get_string('ttblocktitle', 'block_timetrack');
    }

    /**
     * This function indicates the instance allows multiple
     *
     * @return boolean
     */
    function instance_allow_multiple() {
        return true;
    }

    /**
     * The function returns the content of the block.
     * 
     * @return mixed
     */
    function get_content() {
        global $CFG;

        if (!timetrack_canuse() && !timetrack_canmanage()) {
            return false;
        }

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->footer =
                isset($this->config->footer) ? $this->config->footer : get_string('ttblockfooter','block_timetrack');
        $this->content->text =
                '<div class="ttlink"><a href="'.$CFG->wwwroot.'/blocks/timetrack/add.php">'.
                (($this->config->content) ? $this->config->content : get_string('blockcontent', 'block_timetrack')).
                '</a></div>';
        return $this->content;
    }

    /**
     * This function indicates that the instance allows for config.
     *
     * @return boolean
     */
    function instance_allow_config() {
        return false;
    }

    /**
     * course & site index, and activities
     *
     * @return array
     */
    function applicable_formats() {
        return array('all'=>true);
    }

    /**
     * Actions to perform once after installation.
     * Set the permissions for tutors and stduents.
     */
    function after_install() {
        $studentroles = get_roles_with_capability('moodle/legacy:student', CAP_ALLOW);
        $teacherroles = get_roles_with_capability('moodle/legacy:teacher', CAP_ALLOW);
        $editingteacherroles = get_roles_with_capability('moodle/legacy:editingteacher', CAP_ALLOW);

        $capview = 'mod/timetrack:participate';
        $capsubmit = 'mod/timetrack:manage';

        $contextobject = get_context_instance(CONTEXT_SYSTEM);

        if (is_array($teacherroles)) {
            foreach ($teacherroles as $teach) {
                assign_capability($capview, CAP_ALLOW, $teach->id, $contextobject->id, true);
                assign_capability($capsubmit, CAP_PREVENT, $teach->id, $contextobject->id, true);
            }
        }

        if (is_array($editingteacherroles)) {
            foreach ($editingteacherroles as $teached) {
                assign_capability($capview, CAP_ALLOW, $teached->id, $contextobject->id, true);
                assign_capability($capsubmit, CAP_PREVENT, $teached->id, $contextobject->id, true);
            }
        }

        if (is_array($studentroles)) {
            foreach ($studentroles as $stud) {
                assign_capability($capview, CAP_PREVENT, $stud->id, $contextobject->id, true);
                assign_capability($capsubmit, CAP_PREVENT, $stud->id, $contextobject->id, true);
            }
        }
        $this->timetrack_options_build();
    }

    /**
     * Insert the default timetrack_options into the database
     * Only take this action is the database table is empty
     */
    function timetrack_options_build() {
        global $CFG;

        $options = array(
            0 => array(
                'name' => 'Assignments',
                'requiresquantity' => 1,
                'rate' => 25.00),
            1 => array(
                'name' => 'Tutorial',
                'requiresquantity' => 0,
                'rate' => 375.00),
            2 => array(
                'name' => 'Exam Preparation Tutorial',
                'requiresquantity' => 0,
                'rate' => 500.00),
            3 => array(
                'name' => 'Special Event',
                'requiresquantity' => 1,
                'rate' => 200.00),
            4 => array(
                'name' => 'Exam Script',
                'requiresquantity' => 1,
                'rate' => 35),
        );

        //only write the default data if the table is empty
        if (!$optionsrs = get_records('timetrack_options')) {
            foreach ($options as $idx => $option) {
                if (is_array($option)) {
                    $optionobject = new stdClass();
                    foreach ($option as $field => $value) {
                        $optionobject->{$field} = $value;
                    }
                    $optionobject = addslashes_object($optionobject);
                    insert_record('timetrack_options', $optionobject);
                }
            }
        }
    }

    /**
     * Check if config exists.
     *
     * @return boolean
     */
    function has_config() {
        return true;
    }
}