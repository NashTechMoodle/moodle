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
 * Badge assertion library.
 *
 * @package    core
 * @subpackage badges
 * @copyright  2012 onwards Totara Learning Solutions Ltd {@link http://www.totaralms.com/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Yuliya Bozhko <yuliya.bozhko@totaralms.com>
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Open Badges Assertions specification 1.0 {@link https://github.com/mozilla/openbadges/wiki/Assertions}
 *
 * Badge asserion is defined by three parts:
 * - Badge Assertion (information regarding a specific badge that was awarded to a badge earner)
 * - Badge Class (general information about a badge and what it is intended to represent)
 * - Issuer Class (general information of an issuing organisation)
 */
require_once($CFG->libdir . '/badgeslib.php');
require_once($CFG->dirroot . '/badges/renderer.php');

/**
 * Class that represents badge assertion.
 *
 */
class core_badges_assertion {
    /** @var object Issued badge information from database */
    private $_data;

    /** @var moodle_url Issued badge url */
    private $_url;

    /**
     * Constructs with issued badge unique hash.
     *
     * @param string $hash Badge unique hash from badge_issued table.
     */
    public function __construct($hash) {
        global $DB;

        $this->_data = $DB->get_record_sql('
            SELECT
                bi.dateissued,
                bi.dateexpire,
                bi.uniquehash,
                u.email,
                b.*,
                bb.email as backpackemail
            FROM
                {badge} b
                JOIN {badge_issued} bi
                    ON b.id = bi.badgeid
                JOIN {user} u
                    ON u.id = bi.userid
                LEFT JOIN {badge_backpack} bb
                    ON bb.userid = bi.userid
            WHERE ' . $DB->sql_compare_text('bi.uniquehash', 40) . ' = ' . $DB->sql_compare_text(':hash', 40),
            array('hash' => $hash), IGNORE_MISSING);

        if ($this->_data) {
            $this->_url = new moodle_url('/badges/badge.php', array('hash' => $this->_data->uniquehash));
        } else {
            $this->_url = new moodle_url('/badges/badge.php');
        }
    }

    /**
     * Get badge assertion.
     *
     * @return array Badge assertion.
     */
    public function get_badge_assertion() {
        global $CFG;
        $assertion = array();
        if ($this->_data) {
            $hash = $this->_data->uniquehash;
            $email = empty($this->_data->backpackemail) ? $this->_data->email : $this->_data->backpackemail;
            $assertionurl = new moodle_url('/badges/assertion.php', array('b' => $hash));
            $classurl = new moodle_url('/badges/assertion.php', array('b' => $hash, 'action' => 1));

            // Required.
            $assertion['uid'] = $hash;
            $assertion['recipient'] = array();
            $assertion['recipient']['identity'] = 'sha256$' . hash('sha256', $email . $CFG->badges_badgesalt);
            $assertion['recipient']['type'] = 'email'; // Currently the only supported type.
            $assertion['recipient']['hashed'] = true; // We are always hashing recipient.
            $assertion['recipient']['salt'] = $CFG->badges_badgesalt;
            $assertion['badge'] = $classurl->out(false);
            $assertion['verify'] = array();
            $assertion['verify']['type'] = 'hosted'; // 'Signed' is not implemented yet.
            $assertion['verify']['url'] = $assertionurl->out(false);
            $assertion['issuedOn'] = $this->_data->dateissued;
            // Optional.
            $assertion['evidence'] = $this->_url->out(false); // Currently issued badge URL.
            if (!empty($this->_data->dateexpire)) {
                $assertion['expires'] = $this->_data->dateexpire;
            }
            $this->embed_data_badge_version2($assertion, OB2_TYPE_ASSERTION);
        }
        return $assertion;
    }

    /**
     * Get badge class information.
     *
     * @return array Badge Class information.
     */
    public function get_badge_class() {
        $class = array();
        if ($this->_data) {
            if (empty($this->_data->courseid)) {
                $context = context_system::instance();
            } else {
                $context = context_course::instance($this->_data->courseid);
            }
            $issuerurl = new moodle_url('/badges/assertion.php', array('b' => $this->_data->uniquehash, 'action' => 0));

            // Required.
            $class['name'] = $this->_data->name;
            $class['description'] = $this->_data->description;
            $class['image'] = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $this->_data->id, '/', 'f1')->out(false);
            $class['criteria'] = $this->_url->out(false); // Currently issued badge URL.
            $class['issuer'] = $issuerurl->out(false);
            $this->embed_data_badge_version2($class, OB2_TYPE_BADGE);
        }
        return $class;
    }

    /**
     * Get badge issuer information.
     *
     * @return array Issuer information.
     */
    public function get_issuer() {
        $issuer = array();
        if ($this->_data) {
            // Required.
            $issuer['name'] = $this->_data->issuername;
            $issuer['url'] = $this->_data->issuerurl;
            // Optional.
            if (!empty($this->_data->issuercontact)) {
                $issuer['email'] = $this->_data->issuercontact;
            }
        }
        $this->embed_data_badge_version2($issuer, OB2_TYPE_ISSUER);
        return $issuer;
    }

    /**
     * Get related badges
     *
     * @return array Related badges.
     */
    public function get_related_badges() {
        global $DB;
        $relatedbadges = array();
        $relatedbadgeids = $DB->get_fieldset_select('badge_related', 'relatedbadgeid', 'badgeid = :badgeid', array('badgeid' => $this->_data->id));
        if (count($relatedbadgeids) > 0) {
            $conditions = $DB->get_in_or_equal($relatedbadgeids);
            $badges = $DB->get_records_select('badge', 'id ' . $conditions[0], $conditions[1], 'id ASC', 'id, version, language');
            foreach ($badges as $badge) {
                $url = new moodle_url('/badges/badge_json.php', array('id' => $badge->id));
                $relatedbadges[] = array(
                    'id' => $url->out(false),
                    'version' => $badge->version,
                    '@language' => $badge->language);
            }
            if (count($relatedbadges) == 1) {
                return $relatedbadges[0];
            }
            return $relatedbadges;
        }
        return false;
    }

    /**
     * Get endorsement of badge
     *
     * @return false|stdClass
     */
    public function get_endorsement() {
        global $DB;
        $endorsement = array();
        $record = $DB->get_record_select('badge_endorsement', 'badgeid = ?', array($this->_data->id));
        return $record;
    }

    /**
     * Get criteria of badge class.
     *
     * @return array|string
     */
    public function get_criteria_badge_class() {
        $badge = new badge($this->_data->id);
        $narrative = self::markdown_badge_criteria($badge);
        if (!empty($narrative)) {
            $criteria = array();
            $criteria['id'] = $this->_url->out(false);
            $criteria['narrative'] = $narrative;
            return $criteria;
        } else {
            return $this->_url->out(false);
        }
    }

    /**
     * Get competencies alignment
     *
     * @return array
     */
    public function get_competencies_alignment() {
        global $DB;
        $badgeid = $this->_data->id;
        $alignments = array();
        $items = $DB->get_records_select('badge_competencies', 'badgeid = ?', array($badgeid));
        foreach ($items as $item) {
            $alignment = array('targetName' => $item->targetname, 'targetUrl' => $item->targeturl);
            if ($item->targetdescription) {
                $alignment['targetDescription'] = $item->targetdescription;
            }
            if ($item->targetframework) {
                $alignment['targetFramework'] = $item->targetframework;
            }
            if ($item->targetcode) {
                $alignment['targetCode'] = $item->targetcode;
            }
            $alignments[] = $alignment;
        }
        return $alignments;
    }

    /**
     * Return information about badge criteria by markdown text.
     *
     * @param badge $badge Badge objects
     * @param string $short Indicates whether to print full info about this badge
     * @return string $output markdown to output
     */
    public function markdown_badge_criteria(badge $badge, $short = '') {
        $agg = $badge->get_aggregation_methods();
        if (empty($badge->criteria)) {
            return get_string('nocriteria', 'badges');
        }
        $overalldescr = '';
        $overall = $badge->criteria[BADGE_CRITERIA_TYPE_OVERALL];
        if (!$short && !empty($overall->description)) {
            $overalldescr = $this->output->box(
                format_text($overall->description, $overall->descriptionformat, array('context' => $badge->get_context())),
                'criteria-description'
            );
        }
        // Get the condition string.
        if (count($badge->criteria) == 2) {
            $condition = '';
            if (!$short) {
                $condition = get_string('criteria_descr', 'badges');
            }
        } else {
            $condition = get_string('criteria_descr_' . $short . BADGE_CRITERIA_TYPE_OVERALL, 'badges',
                core_text::strtoupper($agg[$badge->get_aggregation_method()]));
        }
        unset($badge->criteria[BADGE_CRITERIA_TYPE_OVERALL]);
        $items = array();
        // If only one criterion left, make sure its description goe to the top.
        if (count($badge->criteria) == 1) {
            $c = reset($badge->criteria);
            if (!$short && !empty($c->description)) {
                $overalldescr = $c->description . ' \n ';
            }
            if (count($c->params) == 1) {
                $items[] = ' * ' . get_string('criteria_descr_single_' . $short . $c->criteriatype, 'badges') .
                    $c->get_details($short);
            } else {
                $items[] = '* ' . get_string('criteria_descr_' . $short . $c->criteriatype, 'badges',
                        core_text::strtoupper($agg[$badge->get_aggregation_method($c->criteriatype)])) .
                    $c->get_details($short);
            }
        } else {
            foreach ($badge->criteria as $type => $c) {
                $criteriadescr = '';
                if (!$short && !empty($c->description)) {
                    $criteriadescr = $c->description;
                }
                if (count($c->params) == 1) {
                    $items[] = ' * ' . get_string('criteria_descr_single_' . $short . $type, 'badges') .
                        $c->get_details($short) . $criteriadescr;
                } else {
                    $items[] = '* ' . get_string('criteria_descr_' . $short . $type, 'badges',
                            core_text::strtoupper($agg[$badge->get_aggregation_method($type)])) .
                        $c->get_details($short) . $criteriadescr;
                }
            }
        }
        return strip_tags($overalldescr . $condition . html_writer::alist($items, array(), 'ul'));
    }

    /**
     * Embed attributes of Open Badges Specification Version 2.0 to json.
     *
     * @param $json
     * @param string $type
     */
    protected function embed_data_badge_version2 (&$json, $type = OB2_TYPE_ASSERTION) {
        // Specification Version 2.0.
        if ($this->_data->obsversion == 2) {
            if (empty($this->_data->courseid)) {
                $context = context_system::instance();
            } else {
                $context = context_course::instance($this->_data->courseid);
            }
            $hash = $this->_data->uniquehash;
            $assertionsurl = new moodle_url('/badges/assertion.php', array('b' => $hash));
            $classurl = new moodle_url('/badges/assertion.php', array('b' => $hash, 'action' => 1));
            $issuerurl = new moodle_url('/badges/assertion.php', array('b' => $this->_data->uniquehash, 'action' => 0));
            // For assertion.
            if ($type == OB2_TYPE_ASSERTION) {
                $json['@context'] = OB2_CONTEXT;
                $json['type'] = OB2_TYPE_ASSERTION;
                $json['badge'] = $this->get_badge_class();
                $json['issuedOn'] = date('c', $this->_data->dateissued);
                if (!empty($this->_data->dateexpire)) {
                    $json['expires'] = date('c', $this->_data->dateexpire);
                }
            }
            // For Badge Class.
            if ($type == OB2_TYPE_BADGE) {
                $json['@context'] = OB2_CONTEXT;
                $json['id'] = $classurl->out(false);
                $json['type'] = OB2_TYPE_BADGE;
                $json['version'] = $this->_data->version;
                $json['criteria'] = $this->get_criteria_badge_class();
                $json['issuer'] = $this->get_issuer();
                $json['@language'] = $this->_data->language;
                if (!empty($relatedbadges = $this->get_related_badges())) {
                    $json['related'] = $relatedbadges;
                }
                if ($endorsement = $this->get_endorsement()) {
                    $endorsementurl = new moodle_url('/badges/endorsement_json.php', array('id' => $this->_data->id));
                    $json['endorsement'] = $endorsementurl->out(false);
                }
                if ($competencies = $this->get_competencies_alignment()) {
                    $json['alignment'] = $competencies;
                }
                if ($this->_data->authorimage || $this->_data->captionimage) {
                    $urlimage = moodle_url::make_pluginfile_url($context->id,
                        'badges', 'badgeimage', $this->_data->id, '/', 'f1')->out(false);
                    $json['image'] = array();
                    $json['image']['id'] = $urlimage;
                    if ($this->_data->authorimage) {
                        $json['image']['author'] = $this->_data->authorimage;
                    }
                    if ($this->_data->captionimage) {
                        $json['image']['caption'] = $this->_data->captionimage;
                    }
                }
            }
            // For issuer.
            if ($type == OB2_TYPE_ISSUER) {
                $json['@context'] = OB2_CONTEXT;
                $json['id'] = $issuerurl->out(false);
                $json['type'] = OB2_TYPE_ISSUER;
            }
        }
    }
}
