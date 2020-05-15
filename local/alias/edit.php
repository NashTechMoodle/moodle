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


require(__DIR__.'/../../config.php');
require_once(__DIR__.'/edit_form.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/weblib.php');
require_once($CFG->libdir.'/outputlib.php');

defined('MOODLE_INTERNAL') || die;

$PAGE->set_context(context_system::instance());
require_login();

if(!has_capability('local/alias:viewalias', context_system::instance())){
    print_error('nopermissions', 'error', '', 'create/delete/update alias');
}

$aliasid  = optional_param('id', 0, PARAM_INT);
$stralias = get_string("modulename", "local_alias");
$strlink = get_string("modulename_link", "local_alias");
$strlinkcreate = get_string("modulename_linkcreate", "local_alias");
$alias = $DB->get_record('alias', array('id' => $aliasid));

$PAGE->set_title($stralias);
$PAGE->set_url('/'.$strlinkcreate);
$PAGE->set_heading($stralias);
$PAGE->navbar->add($stralias);
echo $OUTPUT->header();

$mform = new alias_edit_form(null, array('alias' => $alias));
$elData = $mform->get_data();
var_dump($elData->fieldname['friendly']);
if($mform->is_cancelled()){
    redirect("index.php");
}
else if($data = $mform->get_data())
{
    $currentdata = new stdClass();
    $currentdata->friendly = $data->friendly;
    $currentdata->destination = $data->destination;
    if(!empty($data->id)){
        if(!empty($data->deletebutton)){
            $DB->delete_records('alias', array('id' => $data->id));
        }
        else
        {
            if(!empty($data->friendly) && !empty($data->destination)){
                $currentdata->id = $data->id;
                $currentdata->timemodified  = time();
                $DB->update_record('alias', $currentdata);
            }
        }
    }else if(!empty($data->friendly) && !empty($data->destination)){
        $currentdata->timecreated   = time();
        $DB->insert_record("alias", $currentdata);
    }
    redirect(new moodle_url("index.php", null, null));
}
$mform->display();
echo $OUTPUT->footer();
