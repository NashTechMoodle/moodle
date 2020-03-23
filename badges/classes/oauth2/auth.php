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
 * This file to proccess Oauth2 connects for backpack.
 *
 * @package    core
 * @subpackage badges
 * @copyright  2020 Tung Thai
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Tung Thai <Tung.ThaiDuc@nashtechglobal.com>
 */

namespace core_badges\oauth2;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');

use stdClass;

/**
 * Proccess Oauth2 connects to backpack site.
 *
 * @copyright  2020 Tung Thai
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Tung Thai <Tung.ThaiDuc@nashtechglobal.com>
 */
class auth extends \auth_oauth2\auth {

    /**
     * To complete data handle after login.
     *
     * @param client $client
     * @param $redirecturl
     */
    public function complete_data(\core_badges\oauth2\client $client, $redirecturl) {
        global $DB, $USER;

        $badgebackpack = new stdClass();
        $badgebackpack->userid = $USER->id;
        $badgebackpack->email = $USER->email;
        $badgebackpack->externalbackpackid = $client->backpack->id;
        $badgebackpack->backpackuid = 0;
        $badgebackpack->autosync = 0;
        $badgebackpack->password = '';
        $record = $DB->get_record('badge_backpack', ['userid' => $USER->id, 'externalbackpackid' => $client->backpack->id]);
        if (!$record) {
            $DB->insert_record('badge_backpack', $badgebackpack);
        } else {
            $badgebackpack->id = $record->id;
            $DB->update_record('badge_backpack', $badgebackpack);
        }

        redirect($redirecturl);
    }

    /**
     * Check user has been logged the backpack site.
     *
     * @param $backpackid
     * @param $userid
     * @return bool
     */
    public static function is_logged_oauth2($backpackid, $userid) {
        global $USER;
        if (empty($userid)) {
            $userid = $USER->id;
        }
        $persistedtoken = badge_backpack_oauth2::get_record(['externalbackpackid' => $backpackid, 'userid' => $userid]);
        if ($persistedtoken) {
            return true;
        }
        return false;
    }

    /**
     * Disconnect with backpack site.
     *
     * @param $backpack
     * @param string $redirect
     */
    public static function disconnect($backpack, $redirect = '') {
        global $USER, $DB;
        $record = $DB->get_record('badge_backpack', ['userid' => $USER->id, 'externalbackpackid' => $backpack->id]);
        if ($record) {
            $sqlparams = array('backpack' => $record->id);
            $select = 'backpackid = :backpack ';
            $DB->delete_records_select('badge_external', $select, $sqlparams);
            $DB->delete_records('badge_backpack', ['id' => $record->id]);
            $DB->delete_records('badge_backpack_oauth2', ['externalbackpackid' => $backpack->id, 'userid' => $USER->id]);
        }

        if ($redirect) {
            redirect($redirect);
        }
    }
}


