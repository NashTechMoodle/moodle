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

class alias_edit_form extends moodleform {
    function definition(){
        $alias = $this->_customdata['alias'];
        $mform = $this->_form;

        if($alias){
            $mform->addElement('header','general', get_string('editingalias', 'local_alias'));
            $mform = $this->form_generator($mform);
            $mform->addElement('hidden', 'id');
            $mform->setDefault('id',$alias->id);
            $mform->setDefault('friendly',$alias->friendly);
            $mform->setDefault('destination',$alias->destination);
            $mform->setType('id', PARAM_INT);
            $this->add_action_buttons(true, get_string('editingalias_button', 'local_alias'));
            $mform->addElement('submit', 'deletebutton', get_string('deletingalias_button', 'local_alias'));
        }
        if(!$alias)
        {
            $mform->addElement('header','general', get_string('creatingalias', 'local_alias'));
            $mform = $this->form_generator($mform);
            $this->add_action_buttons(true, get_string('creatingalias_button', 'local_alias'));
        } 
        // set the defaults
        $this->set_data($alias);
    }

    function form_generator($form){
        $form->addElement('text','friendly','Friendly url', array('size' => '255'));
        $form->setType('friendly', PARAM_RAW);
        $form->addRule('friendly', null, 'required', null, 'client');
        $form->addElement('text','destination','Destination url', array('size' => '255'));
        $form->setType('destination', PARAM_RAW);
        $form->addRule('destination', null, 'required', null, 'client');
        return $form;
    }  
}
