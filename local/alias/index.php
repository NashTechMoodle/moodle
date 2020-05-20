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
 * This page lets users to manage site wide competencies.
 *
 * @package    local_alias
 * @copyright  2015 Damyon Wiese
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// config
require_once(__DIR__ . '../../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . '/aliaslib.php');
require_once($CFG->libdir . '/formslib.php');
require_once('edit_form.php');
require_once('search_form.php');

require_login();

// set permisson
$context = context_system::instance();
$PAGE->set_context($context);

// Get all required strings
$strid = get_string('id', 'local_alias');
$strfriendly = get_string('friendly', 'local_alias');
$strdestination = get_string('destination', 'local_alias');
$strtitle = get_string('titlealias', 'local_alias');
$strtitleheading = get_string('titleheading', 'local_alias');
$strpluginname = get_string('pluginname', 'local_alias');


/** Option in params of url */
$sortby = optional_param('sort', 'id', PARAM_ALPHA);
$sorthow = optional_param('dir', 'ASC', PARAM_ALPHA);
$page = optional_param('page', 0, PARAM_INT);


/** Set url in first time showing in the page */
$urlparams = array(
    'sort'  => is_null($sortby)   ? 'id'  : $sortby,
    'dir'   => is_null($sorthow)  ? 'ASC' : $sorthow,
    'page'  => is_null($page)     ? 0     : $page
);

$PAGE->set_url(new moodle_url('/local/alias/index.php', $urlparams));

$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitleheading);
$PAGE->navbar->add($strpluginname);
echo $OUTPUT->header();


// get dabatable
$records = $DB->get_records_sql("SELECT * FROM {alias} ORDER BY $sortby $sorthow", null, ALIAS_PERPAGE * $page, ALIAS_PERPAGE);
$totalcount = $DB->get_record_sql('SELECT COUNT(*) FROM {alias}')->count;


// search form
$searchform = new local_alias_search_form();
$searchform->display();

if (!empty($data = $searchform->get_data())) { //WHERE `friendly` like $search->query OR `destination` like $search->query
    $search = $data->query;
    $querysearch = "SELECT * FROM {alias} WHERE friendly like '%$search%' OR destination like '%$search%'";
    // var_dump($querysearch);
    $records = $DB->get_records_sql($querysearch, null, ALIAS_PERPAGE * $page, ALIAS_PERPAGE);
    $totalcount = count($DB->get_records_sql($querysearch));
}

// using renderer
$output = $PAGE->get_renderer('local_alias');
$alias             = new local_alias\output\alias_collection($records);
$alias->titlecolumn = array($strid, $strfriendly, $strdestination);
$alias->sort       = is_null($sortby) ? 'id' : $sortby;
$alias->dir        = is_null($sorthow) ? 'ASC' : $sorthow;
$alias->page       = is_null($page) ? 0 : $page;
$alias->perpage    = ALIAS_PERPAGE;
$alias->totalcount = $totalcount;
echo $output->render($alias);

$hassiteconfig = has_capability('local/alias:edit', $context);
if ($hassiteconfig) {
    echo $OUTPUT->box($OUTPUT->single_button(new moodle_url("edit.php"), get_string('addnewalias', 'local_alias')));
}

echo $OUTPUT->footer();
