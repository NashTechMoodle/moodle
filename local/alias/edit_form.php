<?php
require_once("$CFG->libdir/formslib.php");


class info_form extends moodleform
{
    function definition()
    {
        global $CFG;

        $mform = $this->_form;


        $mform->addElement('text', 'friendly', get_string('friendly', 'local_alias'));
        $mform->addRule('friendly', get_string('required'), 'required', null);
        $mform->setType('friendly', PARAM_NOTAGS);

        $mform->addElement('text', 'destination', get_string('destination', 'local_alias'));
        $mform->addRule('destination', get_string('required'), 'required', null);
        $mform->setType('destination', PARAM_NOTAGS);

        $strsubmit = get_string('createalias', 'local_alias');
        $this->add_action_buttons(true, $strsubmit);
    }

}
