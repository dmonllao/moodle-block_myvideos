<?php // $Id: myvideos_uploadvideo_form.php,v 1.1 2010/07/04 21:51:29 arborrow Exp $

require_once($CFG->dirroot.'/blocks/myvideos/forms/myvideos_form.php');

class myvideos_uploadvideo_form extends myvideos_form {

    
    function definition() {

        global $CFG, $COURSE;

        require_js($CFG->wwwroot.'/blocks/myvideos/lib/lib.js');
        
        $mform = & $this->_form;

        $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        
        // title, description, author and tags
        $this->_add_common_params();
        
        // File
        $mform->addElement('file', 'uploadfile', get_string("filetoupload", 'block_myvideos'), array("size"=>"55"));
        $mform->addRule('uploadfile', null, 'required', null, 'client');

        // Quality
        if (has_capability('block/myvideos:selectquality', $context)) {
            
            $qualityoptions["1"] = get_string('qualitylow', 'block_myvideos');
            $qualityoptions["2"] = get_string('qualitymedium', 'block_myvideos');
            $qualityoptions["3"] = get_string('qualityhigh', 'block_myvideos');
            
            $mform->addElement('select', 'quality', get_string('quality', 'block_myvideos'), $qualityoptions);
            $mform->setHelpButton('quality', array('quality', get_string('quality', 'block_myvideos'), 'block_myvideos'));
        } else {
            $mform->addElement('hidden', 'quality', '2');
        }

        // Override flash video
        if (has_capability('block/myvideos:overrideffmpeg', $context)) {
            $mform->addElement('selectyesno', 'overrideffmpeg', get_string('overrideffmpeg', 'block_myvideos'));
        } else {
            $mform->addElement('hidden', 'overrideffmpeg', 0);
        }
        
        // Terms of use
        $this->_add_terms();

        $this->_add_hidden_params();

        $mform->addElement('submit', 'submitbutton', get_string('savechanges'), array("onclick"=>"myvideos_submit_upload();"));
        
        // After the client checking of the required fields id_loader is displayed
        $loaderdiv = myvideos_uploadform_loader();
        $mform->addElement('html', $loaderdiv);
    }

}

?>
