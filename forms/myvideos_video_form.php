<?php // $Id: myvideos_video_form.php,v 1.1 2010/07/04 21:51:30 arborrow Exp $

require_once($CFG->dirroot.'/blocks/myvideos/forms/myvideos_form.php');

require_js($CFG->wwwroot.'/blocks/myvideos/lib/async.js');
require_js(array('yui_yahoo', 'yui_event', 'yui_connection'));

class myvideos_video_form extends myvideos_form {

    function definition() {
        
        $mform = & $this->_form;
        
        $mform->addElement('textarea', 'comment', get_string('comments', 'block_myvideos'), array("cols" => "58", "rows" => "3"));
        $mform->addRule('comment', null, 'required', null, 'client');
        $mform->setType('comment', PARAM_TEXT);
        
        $this->_add_hidden_params();
        
        $mform->addElement('hidden', 'id');
        
        $mform->addElement('submit', 'submitbutton', get_string('add'), array("onclick"=>"myvideos_add_comment();return false;"));
    }
}
?>