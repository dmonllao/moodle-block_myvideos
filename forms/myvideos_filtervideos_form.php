<?php // $Id: myvideos_filtervideos_form.php,v 1.1 2010/09/09 09:56:14 davmon Exp $

require_once($CFG->dirroot.'/blocks/myvideos/forms/myvideos_form.php');

class myvideos_filtervideos_form extends moodleform {

    function myvideos_filtervideos_form() {

        $styles = array('style' => 'width: 40%; margin: 0px auto;');
        parent::moodleform(false, null, 'post', '', $styles);
    }

    function definition() {
        global $PAGE;

        $mform = & $this->_form;

        $mform->addElement('text', 'keywords', get_string('keywords', 'block_myvideos'), array("size"=>"40"));
        $mform->setType('keywords', PARAM_NOTAGS);

        $mform->addElement('submit', 'submitbutton', get_string("filter", "block_myvideos"));

        $PAGE->requires->yui_module('moodle-block_myvideos-filtervideos', 'M.block_myvideos.init_filter_videos', null, null, true);
    }
}
