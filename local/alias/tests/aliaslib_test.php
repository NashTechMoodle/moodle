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
require_once($CFG->dirroot . '/local/alias/classes/alias.php');

/**
 * Testing alias lib for work with database alias table.
 */
class alias_lib_testcase extends advanced_testcase {
    /**
     * Fake records property.
     */
    protected function setUp() {
        parent::setUp();
        $this->resetAfterTest();
        global $DB;
        $data = new stdClass();
        $data->friendly = 'http://localhost/hash1.php';
        $data->destination ='http://localhost/course.php?id=1';
        $DB->insert_record('alias', $data);

        $data = new stdClass();
        $data->friendly = 'http://localhost/hash2.php';
        $data->destination ='http://localhost/course.php?id=2';
        $DB->insert_record('alias', $data);
        
        $data = new stdClass();
        $data->friendly = 'http://localhost/hash3.php';
        $data->destination ='http://localhost/course.php?id=3';
        $DB->insert_record('alias', $data);

        $data = new stdClass();
        $data->friendly = 'http://localhost/hash4.php';
        $data->destination ='http://localhost/course.php?id=4';
        $DB->insert_record('alias', $data);
    }

    /**
     * Testing get all alias.
     */
    public function test_get_aliases() {
        global $DB;
        $this->resetAfterTest();
        $alias = new \local_alias\alias();
        $records = $alias->get_aliases('', 0, 'id', 'ASC');
        $this->assertEquals($records->total, 4);
    }

    /**
     * Testing get all alias with key search.
     */
    public function test_get_aliases_with_search() {
        global $DB;
        $this->resetAfterTest();

        $data = new stdClass();
        $data->friendly = 'http://localhost/country1.php';
        $data->destination ='http://localhost/course.php?id=1';
        $DB->insert_record('alias', $data);

        $data = new stdClass();
        $data->friendly = 'http://localhost/country2.php';
        $data->destination ='http://localhost/course.php?id=2';
        $DB->insert_record('alias', $data);
        
        $data = new stdClass();
        $data->friendly = 'http://localhost/country3.php';
        $data->destination ='http://localhost/course.php?id=3';
        $DB->insert_record('alias', $data);
        $alias = new \local_alias\alias();
        $records = $alias->get_aliases('country', 0, 'id', 'ASC');

        $this->assertEquals($records->total, 3);
    }

    /**
     * Testing get alias with id.
     */
    public function test_get_aliases_by_id() {
        global $DB;
        $this->resetAfterTest();

        $data = new stdClass();
        $data->friendly = 'http://localhost/country3.php';
        $data->destination ='http://localhost/course.php?id=3';
        $id = $DB->insert_record('alias', $data);

        $alias = new \local_alias\alias();
        $record = new stdClass();
        $record = $alias->get_alias_by_id($id);
        $this->assertIsNumeric($record->id);
    }

     /**
     * Testing get alias by id with error.
     */
    public function test_get_aliases_by_id_with_error() {
        global $DB;
        $this->resetAfterTest();
        $alias = new \local_alias\alias();
        $record = new stdClass();
        $record = $alias->get_alias_by_id(1);
        $this->assertFalse($record);
    }

    /**
     * Testing save alias.
     */
    public function test_save() {
        global $DB;
        $this->resetAfterTest();
        
        $data = new stdClass();
        $data->friendly = 'http://localhost/home.php';
        $data->destination ='http://localhost/course.php?id=3';

        $alias = new \local_alias\alias();
        $result = new stdClass();
        $result = $alias->save($data);
        $this->assertIsNumeric($result);
    }

    
    /**
     * Testing save alias with error.
     */
    public function test_save_error() {
        global $DB;
        $this->resetAfterTest();
        
        $data = new stdClass();
        $data->friendly = 'http://localhost/home.php';
        $data->destination ='http://localhost/course.php?id=3';
        $id = $DB->insert_record('alias', $data);
        $alias = new \local_alias\alias();
        $result = new stdClass();
        $result = $alias->save($data);
        $this->assertFalse($result);
    }

    /**
     * Testing update alias.
     */
    public function test_update() {
        global $DB;
        $this->resetAfterTest();
        
        $data = new stdClass();
        $data->friendly = 'http://localhost/home.php';
        $data->destination = 'http://localhost/course.php?id=3';
        $id = $DB->insert_record('alias', $data);

        $updatedata = new stdClass();
        $updatedata->id = $id;
        $updatedata->friendly = 'http://localhost/country.php';
        $updatedata->destination = 'http://localhost/course.php?id=4';

        $alias = new \local_alias\alias();
        $result = new stdClass();
        $result = $alias->update($updatedata);
        $this->assertTrue($result);
    }

    /**
     * Testing update alias with error by id not found.
     */
    public function test_update_with_error_by_id_not_found() {
        global $DB;
        $this->resetAfterTest();

        $updatedata = new stdClass();
        $updatedata->id = 10000;
        $updatedata->friendly = 'http://localhost/country.php';
        $updatedata->destination = 'http://localhost/course.php?id=4';

        $alias = new \local_alias\alias();
        $result = new stdClass();
        $result = $alias->update($updatedata);
        $this->assertFalse($result);
    }

    /**
     * Testing update alias with error by friendly is existed.
     */
    public function test_update_with_error_by_friendly_is_existed() {
        global $DB;
        $this->resetAfterTest();

        $data = new stdClass();
        $data->friendly = 'http://localhost/country.php';
        $data->destination = 'http://localhost/course.php?id=3';
        $id = $DB->insert_record('alias', $data);
        
        $data = new stdClass();
        $data->friendly = 'http://localhost/home.php';
        $data->destination = 'http://localhost/course.php?id=3';
        $id = $DB->insert_record('alias', $data);

        $updatedata = new stdClass();
        $updatedata->id = $id;
        $updatedata->friendly = 'http://localhost/country.php';
        $updatedata->destination = 'http://localhost/course.php?id=4';

        $alias = new \local_alias\alias();
        $result = new stdClass();
        $result = $alias->update($updatedata);
        $this->assertFalse($result);
    }

    /**
     * Testing delete alias.
     */
    public function test_delete() {
        global $DB;
        $this->resetAfterTest();

        $data = new stdClass();
        $data->friendly = 'http://localhost/country.php';
        $data->destination = 'http://localhost/course.php?id=3';
        $id = $DB->insert_record('alias', $data);

        $alias = new \local_alias\alias();
        $result = new stdClass();
        $result = $alias->delete($id);
        $this->assertTrue($result);
    }

    /**
     * Testing delete alias with error by id not found.
     */
    public function test_delete_with_error_by_id_not_found() {
        global $DB;
        $this->resetAfterTest();
        
        $alias = new \local_alias\alias();
        $result = new stdClass();
        $result = $alias->delete(100000);
        $this->assertFalse($result);
    }

    /**
     * Testing check alias is exists
     */
    public function test_check_friendly_is_exists() {
        global $DB;
        $this->resetAfterTest();
        
        $alias = new \local_alias\alias();
        $result = new stdClass();
        $result = $alias->check_isexists_friendly('http://localhost/hash1.php');
        $this->assertIsNumeric($result);
    }
}