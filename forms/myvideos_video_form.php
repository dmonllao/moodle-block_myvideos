<?php // $Id: myvideos_video_form.php,v 1.1 2010/07/04 21:51:30 arborrow Exp $

require_once($CFG->dirroot.'/blocks/myvideos/forms/myvideos_form.php');

class myvideos_video_form extends myvideos_form {

    function definition() {
        global $PAGE;

        $mform = & $this->_form;

        $mform->addElement('textarea', 'comment', get_string('comments', 'block_myvideos'), array("cols" => "58", "rows" => "3"));
        $mform->addRule('comment', null, 'required', null, 'client');
        $mform->setType('comment', PARAM_TEXT);

        $this->_add_hidden_params();

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('submit', 'submitbutton', get_string('add'));

        $PAGE->requires->yui_module('moodle-block_myvideos-video', 'M.block_myvideos.init_add_comment', null, null, true);
    }
}
