<?php
require_once("$CFG->libdir/formslib.php");
class search_form extends moodleform
{
    function definition()
    {
        global $CFG;

        $mform = $this->_form;

        $elements = [];
        $elements[] = $mform->createElement('text', 'query', get_string('query', 'admin'));
        $elements[] = $mform->createElement('submit', 'search', get_string('search'));
        $mform->addGroup($elements);
    }

}
