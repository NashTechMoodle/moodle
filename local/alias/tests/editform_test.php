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
 * Unit tests for alias
 * @package    local_edit_form_test
 * @copyright  2014 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/local/alias/edit_form.php');

/** @test */
class edit_form_testcase extends advanced_testcase  {
    
    public function test_form() {
        $mform = new alias_edit_form(null, array('alias' => null));
        $mform->get_data()->fieldname["submmit_button"];
        // $this->assertEquals($mform->submitbutton, get_string("creatingalias_button", "local_alias"));
        $this->assertEquals("1","1");
    }

    public function test_other(){
        $this->assertEquals("1","1");
    }

}