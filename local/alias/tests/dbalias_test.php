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

class db_alias_testcase extends advanced_testcase {
   
    public function test_create(){
        global $DB;
        $this->resetAfterTest(true);
        $alias = (object)[
            'friendly' => 'http://localhost/country.php',
            'destination' => 'http://localhost/course.php?id=4',
            'timecreated' => time(),
            'timemodified' => time()
        ];
        $alias->id = $DB->insert_record('alias', $alias);
        $this->assertIsNumeric($alias->id);
    }

    public function test_deletebyid(){
        global $DB;
        $this->resetAfterTest(true);
        $alias = (object)[
            'id' => 100,
            'friendly' => 'http://localhost/country.php',
            'destination' => 'http://localhost/course.php?id=4',
            'timecreated' => time(),
            'timemodified' => time()
        ];
        $alias->id = $DB->insert_record('alias', $alias);
        $DB->delete_records('alias', ['id' => $alias->id]);
        $this->assertEmpty($DB->get_records('alias', ['id' => $alias->id]));
    }

    public function test_update(){
        global $DB;
        $this->resetAfterTest(true);
        $alias = (object)[
            'friendly' => 'http://localhost/country.php',
            'destination' => 'http://localhost/course.php?id=4',
            'timecreated' => time(),
            'timemodified' => time()
        ];
        $alias->id = $DB->insert_record('alias', $alias);
        $aliasupdate = (object)[
            'id' => $alias->id,
            'friendly' => 'http://localhost/city.php',
            'destination' => 'http://localhost/course.php?id=5',
            'timemodified' => time()
        ];
        $DB->update_record('alias', $aliasupdate);
        $current = $DB->get_record_sql("SELECT COUNT(*) FROM {alias} WHERE id = $alias->id")->count;
        $this->assertEquals($current, 1);
    }
}