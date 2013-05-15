<?php // $Id: myvideos_searchvideos_form.php,v 1.1 2010/07/04 21:51:30 arborrow Exp $

require_once($CFG->dirroot.'/blocks/myvideos/forms/myvideos_public_searchvideos_form.php');

class myvideos_searchvideos_form extends myvideos_public_searchvideos_form {

    function definition() {
        parent::definition();
        $this->_add_hidden_params();
    }
}
