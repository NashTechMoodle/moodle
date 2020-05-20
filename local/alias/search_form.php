<?php
require_once("$CFG->libdir/formslib.php");
class local_alias_search_form extends moodleform
{
    function definition()
    {
        global $CFG;

        $mform = $this->_form;

        $elements = [];
        $elements[] = $mform->createElement('text', 'query', get_string('query', 'admin'));
        $elements[] = $mform->createElement('submit', 'search', get_string('search'));
        $mform->addGroup($elements);
        $mform->setType('query', PARAM_RAW);
    }
}
