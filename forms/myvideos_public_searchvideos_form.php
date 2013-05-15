<?php // $Id: myvideos_public_searchvideos_form.php,v 1.1 2010/07/04 21:51:30 arborrow Exp $

require_once($CFG->dirroot.'/blocks/myvideos/forms/myvideos_form.php');

class myvideos_public_searchvideos_form extends myvideos_form {


    function definition() {

        $mform = & $this->_form;

        $mform->addElement('text', 'keywords', get_string('keywords', 'block_myvideos'), array("size"=>"40"));
        $mform->addRule('keywords', null, 'required', null, 'client');
        $mform->setType('keywords', PARAM_NOTAGS);

        $mform->addElement('checkbox', 'searchdescription', get_string('searchdescription', 'block_myvideos'));

        $mform->addElement('submit', 'submitbutton', get_string("search"));
    }
}
