<?php // $Id: myvideos_editvideo_form.php,v 1.1 2010/07/04 21:51:30 arborrow Exp $

require_once($CFG->dirroot.'/blocks/myvideos/forms/myvideos_form.php');

class myvideos_editvideo_form extends myvideos_form {


    function definition() {

        $mform = & $this->_form;
        
        // title, description, author and tags
        $this->_add_common_params();

        $this->_add_hidden_params();
        
        $mform->addElement('hidden', 'id');

        $mform->addElement('submit', 'submitbutton', get_string('savechanges'));
    }


}

?>
