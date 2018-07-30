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
 * Serve BadgeClass JSON for related badge
 *
 * @package    core
 * @subpackage badges
 * @copyright  2018 The Open University {@link http://www.open.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     The Open University
 */
define('AJAX_SCRIPT', true);
define('NO_MOODLE_COOKIES', true); // No need for a session here.
require_once(__DIR__ . '/../config.php');
require_once($CFG->libdir . '/badgeslib.php');
$id = required_param('id', PARAM_INT);
$badge = new badge($id);
if (empty($badge->courseid)) {
    $context = context_system::instance();
} else {
    $context = context_course::instance($badge->courseid);
}
$urlimage = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1')->out(false);
$json = array();
$json['id'] = $badge->id;
$json['type'] = OB2_TYPE_BADGE;
$json['name'] = $badge->name;
$json['description'] = $badge->description;
$json['image'] = $urlimage;
if ($badge->obsversion == 2) {
    $json['version'] = $badge->version;
    $relatedbadges = $badge->get_related_badges();
    if (!empty($relatedbadges)) {
        foreach ($relatedbadges as $related) {
            $relatedurl = new moodle_url('/badges/badge_json.php', array('id' => $related->id));
            $relateds[] = array('id' => $relatedurl->out(false), 'version' => $related->version, 'language' => $related->language);
        }
        if (count($relateds) == 1) {
            $json['related'] = $relateds[0];
        } else {
            $json['related'] = $relateds;
        }
    }
    if ($badge->authorimage || $badge->captionimage) {
        $class['image'] = array();
        $class['image']['id'] = $urlimage;
        if ($badge->authorimage) {
            $class['image']['author'] = $badge->authorimage;
        }
        if ($badge->captionimage) {
            $class['image']['caption'] = $badge->captionimage;
        }
    }
}
echo $OUTPUT->header();
echo json_encode($json);