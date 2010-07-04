<?php // $Id: myvideos_linkvideo_form.php,v 1.1 2010/07/04 21:51:30 arborrow Exp $

require_once($CFG->dirroot.'/blocks/myvideos/forms/myvideos_form.php');


class myvideos_linkvideo_form extends myvideos_form {


    function definition() {

        $mform = & $this->_form;
        
        // title, description, author and tags
        $this->_add_common_params();

        // Embedded code
        $mform->addElement('textarea', 'embedded', get_string('videoembeddedcode', 'block_myvideos'), array("cols"=>"58"));
        $mform->addRule('embedded', null, 'required', null, 'client');
        $mform->setHelpButton('embedded', array('embedded', get_string('videoembeddedcode', 'block_myvideos'), 'block_myvideos'));
        
        // Code cleaned in myvideos_linkvideo_class
        $mform->setType('embedded', PARAM_RAW);

        // Terms of use
        $this->_add_terms();
        
        $this->_add_hidden_params();

        $mform->addElement('submit', 'submitbutton', get_string('savechanges'));
    }

}

?>
