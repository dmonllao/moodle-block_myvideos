<?php // $Id: myvideos_filtervideos_form.php,v 1.1 2010/09/09 09:56:14 davmon Exp $

require_once($CFG->dirroot.'/blocks/myvideos/forms/myvideos_form.php');

class myvideos_filtervideos_form extends moodleform {
    
    function myvideos_filtervideos_form() {
        
        $styles = array('style' => 'width: 40%;');
        parent::moodleform(false, null, 'post', '', $styles);
    }
    
    
    function definition() {
        
        $mform = & $this->_form;

        $mform->addElement('text', 'keywords', get_string('keywords', 'block_myvideos'), array("size"=>"40"));
        
        $attributes = array("onclick" => "myvideos_filter_videos();return false;");
        $mform->addElement('submit', 'submitbutton', get_string("filter", "block_myvideos"), $attributes);
    }
}