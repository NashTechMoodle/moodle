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

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');

class search_form extends moodleform{

    public function definition(){
        $mform = $this->_form;
        $elements = [];
        // $elements[] = $mform->addHelpButton('link', 'createnealias',null,new moodle_url('edit.php'), get_string('creatingalias_button', 'local_alias'), "btn btn-secondary");
        $elements[] = $mform->createElement('text', 'search', get_string('search_textbox', 'local_alias'));
        $elements[] = $mform->createElement('submit', 'button', get_string('search_button', 'local_alias'));
        $mform->addGroup($elements);
        $mform->setType('search', PARAM_RAW);
        $mform->setDefault('search', optional_param('search', '', PARAM_RAW));
    }
}