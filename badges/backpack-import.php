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
 * Get badges from the backpack site.
 *
 * @package    core
 * @subpackage badges
 * @copyright  2020 Tung Thai
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Tung Thai <Tung.ThaiDuc@nashtechglobal.com>
 */
require_once(__DIR__ . '/../config.php');
require_once($CFG->libdir . '/badgeslib.php');

if (badges_open_badges_backpack_api($CFG->badges_site_backpack) != OPEN_BADGES_V2) {
    throw new coding_exception('backpacks only support Open Badges V2.1');
}

$url = new moodle_url('/badges/backpack-export.php');

require_login();
if (empty($CFG->badges_allowexternalbackpack) || empty($CFG->enablebadges)) {
    redirect($CFG->wwwroot);
}
$backpack = badges_get_site_backpack($CFG->badges_site_backpack);
$userbadges = badges_get_user_badges($USER->id);
$context = context_user::instance($USER->id);

// Export badges selected to backpack connect.
$backpack = badges_get_site_backpack($CFG->badges_site_backpack);
$api = new core_badges\backpack_api2p1($backpack);
$response = $api->get_assertions();
if (!empty($response->status && $response->status->statusCode)) {
    if ($response->status->statusCode == 200 && !empty($response->assertions)) {
        $api->set_backpack_assertions($backpack->id, $response->assertions);
    }
}
$msg = [];
if ($response->status->statusCode == 200) {
    $msg = $api->get_status_response($response->status->statusCode, 'backpackimportsuccess');
} else {
    $msg = $api->get_status_response($response->status->statusCode, $response->status->error);
}
redirect(new moodle_url('/badges/mybadges.php', [$msg['status'] => $msg['message']]));
