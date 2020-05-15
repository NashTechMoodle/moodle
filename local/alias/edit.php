<?php

require_once(__DIR__ . '../../../config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once("$CFG->libdir/formslib.php");
require_once('edit_form.php');
require_once('search_form.php');

require_login();

$context = context_system::instance();

$hassiteconfig = has_capability('local/alias:edit', $context);
var_dump($hassiteconfig);

if (!$hassiteconfig ) {
    redirect(new moodle_url('/'));
}

// Get all required strings
$strid = get_string('id', 'local_alias');
$strfriendly = get_string('friendly', 'local_alias');
$strdestination = get_string('destination', 'local_alias');

$strtitle = get_string('titlealias', 'local_alias');
$strtitleheading = get_string('titleheading', 'local_alias');
$strcreatenewalias = get_string('createnewalias', 'local_alias');

$PAGE->set_title($strtitle);
$PAGE->set_heading($strtitleheading);
$PAGE->navbar->add($strcreatenewalias);
echo $OUTPUT->header();

$mform = new info_form();

if($mform->is_cancelled()){
    redirect("index.php");
}else if($data = $mform->get_data()){
    $newData = new stdClass();
    if(!empty($data->friendly) && !empty($data->destination)){
        $newData->friendly = $data->friendly;
        $newData->destination = $data->destination;
        $newData->timecreated   = time();
        $newData->timemodified  = time();
        $DB->insert_record("alias", $newData);
        redirect(new moodle_url("index.php", null, null));
    }
}
$mform->display();

echo $OUTPUT->footer();