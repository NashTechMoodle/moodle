<?php

require_once(__DIR__ . '../../../config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once("$CFG->libdir/formslib.php");
require_once('edit_form.php');
require_once('search_form.php');

require_login();


// Get all required strings
$strid = get_string('id', 'local_alias');
$strfriendly = get_string('friendly', 'local_alias');
$strdestination = get_string('destination', 'local_alias');
$strtitle = get_string('titlealias', 'local_alias');
$strtitleheading = get_string('titleheading', 'local_alias');
$strpluginname = get_string('pluginname', 'local_alias');

$aliases = $DB->get_records("alias");

$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitleheading);
$PAGE->navbar->add($strpluginname);
echo $OUTPUT->header();


$txt = array('settings', 'name', 'version');
$table = new html_table();
$table->attributes['class'] = 'generaltable table-striped boxaligncenter';
$table->head = array($strid, $strfriendly, $strdestination);
$table->align = array ('left', 'left', 'left');


$sform = new search_form();
$sform->display();

foreach($aliases as $alias){
    $table->data[] = array($alias->id,$alias->friendly, $alias->destination);
}

echo html_writer::table($table);



$context = context_system::instance();
$PAGE->set_context($context);

$hassiteconfig = has_capability('local/alias:edit', $context);

if ($hassiteconfig ) {
    echo $OUTPUT->box($OUTPUT->single_button(new moodle_url("edit.php"), get_string('addnewalias', 'local_alias')));
//    redirect(new moodle_url('/'));
}





echo $OUTPUT->footer();