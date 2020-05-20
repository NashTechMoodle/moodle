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
 * Issued badge renderable.
 *
 * @package    local
 * @subpackage aliases
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Yuliya Bozhko <yuliya.bozhko@totaralms.com>
 */

namespace local_alias;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/aliaslib.php');

use context_system;
use context_course;
use context_user;
use moodle_exception;
use moodle_url;
use core_text;
use core_php_time_limit;
use html_writer;
use stdClass;

/**
 * Class that represents alias.
 */
class alias {
    /** @var int alias id */
    public $id;

    /** @var string alias friendly */
    public $friendly;

    /** @var string alias destination */
    public $destination;

    /** @var integer Timestamp this alias was modified */
    public $timemodified;

    /** @var integer Timestamp this alias was created */
    public $timecreated;

    /** @var int The user who modified this alias */
    public $usermodified;

    /**
     * Constructor of alias.
     */
    public function __construct() {
        global $USER;
        $this->usermodified = $USER->id;
    }

    /**
     * Return records and count of alias.
     * @param string $key search key
     * @param int $page current page of url
     * @param string $sortby what is field sort in alias
     * @param string $sorthow how to sort
     */
    public function get_aliases($key, $page, $sortby, $sorthow) {
        global $DB;
        $where = '';
        if (!empty($key) && $key != '') {
            $orwhere = [];
            $orwhere[] = ("friendly LIKE '%$key%'");
            $orwhere[] = ("destination LIKE '%$key%'");
            $where = 'WHERE ' . implode(' OR ', $orwhere);
        }
        $result = new stdClass();
        $result->records = $DB->get_records_sql(
            "SELECT * FROM {alias} $where ORDER BY $sortby $sorthow",
            null,
            ALIAS_PERPAGE * $page,
            ALIAS_PERPAGE
        );
        $result->total = $DB->get_record_sql("SELECT COUNT(*) FROM {alias} $where;")->count;
        return $result;
    }

    /**
     * Get alias by id.
     *
     * @param int $aliasid
     * @return alias record or false if alias not found
     */
    public function get_alias_by_id($aliasid) {
        if ($aliasid != null && !empty($aliasid)) {
            global $DB;
            $result = $DB->get_record('alias', ['id' => $aliasid]);
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Create new alias.
     *
     * @param object $aliascreate
     * @return alias record
     */
    public function save($aliascreate) {
        if ($this->check_isexists_friendly($aliascreate->friendly) > 0) {
            return false;
        }
        global $DB;
        $aliascreate->datecreated = time();
        $result = $DB->insert_record("alias", $aliascreate);
        return $result;
    }

    /**
     * Update current alias.
     *
     * @param object $updatealias
     * @return alias record or false
     */
    public function update($updatealias) {
        if (!$record = $this->get_alias_by_id($updatealias->id)) {
            return false;
        }
        if ($record->friendly != $updatealias->friendly) {
            if ($this->check_isexists_friendly($updatealias->friendly) > 0) {
                return false;
            }
        }
        global $DB;
        $updatealias->timemodified = time();
        $result = $DB->update_record("alias", $updatealias);
        return $result;
    }

    /**
     * Delete current alias.
     *
     * @param int $id
     * @return alias record or false
     */
    public function delete($id) {
        if (!$this->get_alias_by_id($id)) {
            return false;
        }
        global $DB;
        $result = $DB->delete_records("alias", array('id' => $id));
        return $result;
    }

    /**
     * Checking friendly record is exist.
     *
     * @param string $friendly
     * @return number
     */
    public function check_isexists_friendly($friendly) {
        global $DB;
        $totalcount = $DB->get_record_sql("SELECT COUNT(*) FROM {alias} WHERE friendly = '$friendly';")->count;
        return $totalcount;
    }
}