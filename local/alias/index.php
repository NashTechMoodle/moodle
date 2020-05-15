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
 * The local_autovisible class.
 *
 * @package    local_autovisible
 * @copyright  2014 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/authlib.php');
require_once($CFG->libdir.'/aliaslib.php');
require_once(__DIR__.'/search_form.php');

$PAGE->set_context(context_system::instance());
require_login();

if(!has_capability('local/alias:viewalias', context_system::instance())){
    print_error('nopermissions', 'error', '', 'view aliases');
}

/** Config table of alias */
$stralias = get_string("modulename", "local_alias");
$strlink = get_string("modulename_link", "local_alias");
$id = get_string('table_id', 'local_alias');
$friendly = get_string('table_friendly', 'local_alias');
$destination = get_string('table_destination', 'local_alias');

/** Option in params of url */
$sortby = optional_param('sort','id', PARAM_ALPHA);
$sorthow = optional_param('dir', 'DESC', PARAM_ALPHA);
$page = optional_param('page', 0, PARAM_INT);
$search = optional_param('search', '', PARAM_RAW);

/** Set url in first time showing in the page */
$urlparams = array(
    'sort'  => is_null($sortby)   ? 'id'  : $sortby,
    'dir'   => is_null($sorthow)  ? 'ASC' : $sorthow,
    'page'  => is_null($page)     ? 0     : $page,
    'search'=> is_null($search)   ? null  : $search,
);

/** Set url params */
$PAGE->set_url(new moodle_url('/'.$strlink, $urlparams));

/** Page head */
$PAGE->set_title($stralias);
$PAGE->set_heading($stralias);
$PAGE->navbar->add($stralias);
echo $OUTPUT->header();

/** Create button alias */
echo $OUTPUT->box($OUTPUT->single_button(new moodle_url("edit.php"),get_string('creatingalias_button', 'local_alias')));

$seachform = new search_form();
$seachform->display();
$where = '';
if($data = $seachform->get_data() || $search != ''){
    if(!empty($data->search) && $data->search != ''){
        $keyword = strtolower($data->search);
    }else{
        $keyword = strtolower($search);
    }
    $orwhere = [];
    $orwhere[] =  ("$friendly LIKE '%$keyword%'");
    $orwhere[] =  ("$destination LIKE '%$keyword%'");
    $where = 'WHERE ' . implode(' OR ', $orwhere);
    /** Set url params */
    $urlparams['search'] = $keyword;
    $PAGE->set_url(new moodle_url('/'.$strlink, $urlparams));
}

/** Records can get base option of params of url */
$records = $DB->get_records_sql("SELECT * FROM {alias} $where ORDER BY $sortby $sorthow", null, ALIAS_PERPAGE * $page, ALIAS_PERPAGE);

/** Count all row in alias table for */
$totalcount = $DB->get_record_sql("SELECT COUNT(*) FROM {alias} $where;")->count;

/** Table of alias */
if(!empty($totalcount)){
    $output              = $PAGE->get_renderer('local_alias');
    $aliases             = new \local_alias\output\alias_collection($records);
    $aliases->titlecolumn = array($id, $friendly,$destination);
    $aliases->sort       = $urlparams['sort'];
    $aliases->dir        = $urlparams['dir'];
    $aliases->page       = $urlparams['page'];
    $aliases->perpage    = ALIAS_PERPAGE;
    $aliases->totalcount = $totalcount;
    echo $output->render($aliases);
}else{
    echo $OUTPUT->notification(get_string('noalias', 'local_alias'));
}

echo $OUTPUT->footer();