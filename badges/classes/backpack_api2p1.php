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
 * Communicate with backpacks.
 *
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Yuliya Bozhko <yuliya.bozhko@totaralms.com>
 */

namespace core_badges;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');

use cache;
use coding_exception;
use context_system;
use moodle_url;
use core_badges\backpack_api2p1_mapping;
use core_badges\oauth2\client;
use curl;
use stdClass;

/**
 * To process badges with backpack and control api request
 *
 * @copyright  2020 Tung Thai
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Tung Thai <Tung.ThaiDuc@nashtechglobal.com>
 */
class backpack_api2p1 {
    /** @var backpack To do. */
    private $backpack;

    /** @var array To do. */
    private $mappings = [];

    /** @var false|null|stdClass|\core_badges\backpack_api2p1 To do. */
    private $tokendata;

    /** @var null To do. */
    private $clientid = null;

    /** @var null To do. */
    protected $backpackapiversion;

    /** @var null To do. */
    protected $backpackapiurl = '';

    /**
     * Constructor.
     *
     * @param $backpack
     * @throws coding_exception
     */
    public function __construct($backpack) {

        if (!empty($backpack)) {
            $this->backpack = $backpack;
            $this->backpackapiversion = $backpack->apiversion;
            $this->backpackapiurl = $backpack->backpackapiurl;
            $this->get_clientid = $this->get_clientid($backpack->oauth2_issuerid);

            if (!($this->tokendata = $this->get_stored_token($backpack->id))
                && $this->backpackapiversion != OPEN_BADGES_V2) {
                throw new coding_exception('Backpack incorrect');
            }
        }

        $this->define_mappings();
    }


    /**
     * Define the mappings supported by this usage and api version.
     */
    private function define_mappings() {
        if ($this->backpackapiversion == OPEN_BADGES_V2) {

            $mapping = [];
            $mapping[] = [
                'post.assertions',                               // Action.
                '[URL]/assertions',   // URL
                '[PARAM]',                                  // Post params.
                false,                                      // Multiple.
                'post',                                     // Method.
                true,                                       // JSON Encoded.
                true                                        // Auth required.
            ];

            $mapping[] = [
                'get.assertions',                               // Action.
                '[URL]/assertions',   // URL
                '[PARAM]',                                  // Post params.
                false,                                      // Multiple.
                'get',                                     // Method.
                true,                                       // JSON Encoded.
                true                                        // Auth required.
            ];

            foreach ($mapping as $map) {
                $map[] = false; // Site api function.
                $map[] = OPEN_BADGES_V2; // V2 function.
                $this->mappings[] = new backpack_api2p1_mapping(...$map);
            }

        }
    }

    /**
     * Make an api request.
     *
     * @param string $action The api function.
     * @param string $clientid clientid app.
     * @param string $postdata The body of the api request.
     * @return mixed
     */
    public function curl_request($action, $clientid = null, $postdata = null) {
        global $CFG, $SESSION;

        $curl = new curl();
        $authrequired = false;
        $tokenkey = $this->tokendata->token;

        foreach ($this->mappings as $mapping) {
            if ($mapping->is_match($action)) {
                return $mapping->request(
                    $this->backpackapiurl,
                    $tokenkey,
                    $postdata
                );
            }
        }

        throw new coding_exception('Unknown request');
    }

    /**
     * Get stored token.
     *
     * @param $backpackid
     * @return oauth2\badge_backpack_oauth2|false|stdClass|null
     */
    protected function get_stored_token($backpackid) {
        global $USER;

        $token = \core_badges\oauth2\badge_backpack_oauth2::get_record(
            ['externalbackpackid' => $backpackid, 'userid' => $USER->id]);
        if ($token !== false) {
            $token = $token->to_record();
            return $token;
        }
        return null;
    }

    /**
     * Get client id.
     *
     * @param $issuerid
     * @throws coding_exception
     */
    private function get_clientid($issuerid) {
        $issuer = \core\oauth2\api::get_issuer($issuerid);
        if (!empty($issuer)) {
            $this->clientid = $issuer->get('clientid');
        }
    }

    /**
     * To push the selected badges to the backpack site.
     *
     * @param array $assertions
     * @return array
     * @throws \moodle_exception
     * @throws coding_exception
     */
    public function put_assertions($assertions = []) {
        $issuer = new \core\oauth2\issuer($this->backpack->oauth2_issuerid);
        $client = new client($issuer, new moodle_url('/badges/mybadges.php'), '', $this->backpack);
        if (!$client->is_logged_in()) {
            $redirecturl = new moodle_url('/badges/mybadges.php', ['error' => 'backpackexporterror']);
            redirect($redirecturl);
        }

        $data = [];
        $results = [];
        foreach ($assertions as $hash) {
            if (!$hash) {
                continue;
            }
            $assertion = new \core_badges_assertion($hash, OPEN_BADGES_V2);
            $data['assertion'] = $assertion->get_badge_assertion();
            $response = $this->curl_request('post.assertions', $this->clientid, $data);
            if ($response->status->statusCode == 200) {
                $msg = get_string('backpackexportsuccess', 'badges', $data['assertion']['badge']['name']);
            } else {
                $msg = get_string('backpackexporterror', 'badges', $data['assertion']['badge']['name']);
            }
            $results[] = $this->get_status_response($response->status->statusCode, $msg);
        }
        return $results;
    }

    /**
     * Import the badges from the backpack site.
     *
     * @param int $limit
     * @param int $offset
     * @param string $since
     * @return mixed
     * @throws \moodle_exception
     * @throws coding_exception
     */
    public function get_assertions($limit = 0, $offset = 0, $since = '') {
        $issuer = new \core\oauth2\issuer($this->backpack->oauth2_issuerid);
        $client = new client($issuer, new moodle_url('/badges/mybadges.php'), '', $this->backpack);
        if (!$client->is_logged_in()) {
            $redirecturl = new moodle_url('/badges/mybadges.php', ['error' => 'backpackimporterror']);
            redirect($redirecturl);
        }
        $data = [];
        if ($limit) {
            $data['limit'] = $limit;
        }
        if ($offset) {
            $data['offset'] = $offset;
        }
        if ($since) {
            $data['since'] = $since;
        }
        return $this->curl_request('get.assertions', $this->clientid, $data);
    }

    /**
     * Compile reponse messages.
     *
     * @param $statuscode
     * @param $message
     * @return array
     */
    public function get_status_response($statuscode, $message) {
        $msg = [];
        switch ($statuscode) {
            case 200:
                $msg['status'] = 'success';
                $msg['message'] = $message;
                break;
            default:
                $msg['status'] = 'error';
                $msg['message'] = $message;
                break;
        }
        return $msg;
    }

    /**
     * Get badge backpack of user.
     *
     * @param $userid
     * @param $externalbackpackid
     * @return mixed
     * @throws \dml_exception
     */
    protected function get_badge_backpack($userid, $externalbackpackid) {
        global $DB;
        $record = $DB->get_record('badge_backpack', ['userid' => $userid, 'externalbackpackid' => $externalbackpackid]);
        return $record;
    }

    /**
     * Storage the external badges from backpack.
     *
     * @param $externalbackpackid
     * @param $assertions
     * @return bool
     * @throws \dml_exception
     */
    public function set_backpack_assertions($externalbackpackid, $assertions) {
        global $DB, $USER;
        if ($this->backpackapiversion == OPEN_BADGES_V2) {
            $badgebackpack = $this->get_badge_backpack($USER->id, $externalbackpackid);
            if ($badgebackpack) {
                // Delete any previously selected collections.
                $sqlparams = array('backpack' => $badgebackpack->id);
                $select = 'backpackid = :backpack ';
                $DB->delete_records_select('badge_external', $select, $sqlparams);
                $badgescache = cache::make('core', 'externalbadges');

                // Insert selected collections if they are not in database yet.
                foreach ($assertions as $assertion) {
                    $obj = new stdClass();
                    $obj->backpackid = $badgebackpack->id;
                    $obj->entityid = $assertion->id;
                    $obj->collectionid = -1;
                    $obj->assertion = json_encode($assertion);
                    $DB->insert_record('badge_external', $obj);

                }
                $badgescache->delete($USER->id);
                return true;
            }
        }
        return false;
    }
}