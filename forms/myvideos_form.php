<?php // $Id: myvideos_form.php,v 1.1 2010/07/04 21:51:30 arborrow Exp $

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * Abstract class (compatible with PHP4) with myvideos forms common methods
 *
 * @abstract
 * @package      blocks/myvideos
 * @copyright    2010 David Monllao <david.monllao@urv.cat>
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt
 */
abstract class myvideos_form extends moodleform {

    function _add_common_params() {

        global $COURSE;

        $context = context_course::instance($COURSE->id);

        // Pointer to the parent class moodle forms var
        $mform = & $this->_form;

        // Title
        $mform->addElement('text', 'title', get_string('videotitle', 'block_myvideos'), array("size"=>"60"));
        $mform->addRule('title', null, 'required', null, 'client');
        $mform->setType('title', PARAM_TEXT);

        // Description
        $mform->addElement('textarea', 'description', get_string('videodescription', 'block_myvideos'), array("cols"=>"58"));
        $mform->addRule('description', null, 'required', null, 'client');
        $mform->setType('description', PARAM_TEXT);

        // Author
        $mform->addElement('text', 'author', get_string('author', 'block_myvideos'), array("size"=>"60"));
        $mform->addHelpButton('author', 'author', 'block_myvideos');
        $mform->setType('author', PARAM_TEXT);

        // Tags
        $mform->addElement('text', 'tags', get_string('videotags', 'block_myvideos'), array("size"=>"60"));
        $mform->addHelpButton('tags', 'keywords', 'block_myvideos');
        $mform->setType('tags', PARAM_TEXT);

        // Public level
        if (has_capability('block/myvideos:publicvideo', $context)) {
            $privacityoptions["2"] = get_string('visiblepublic', "block_myvideos");
        }
        $privacityoptions["1"] = get_string('visiblemoodle', 'block_myvideos');
        $privacityoptions["0"] = get_string('visibleprivate', 'block_myvideos');
        $mform->addElement('select', 'publiclevel', get_string('publiclevel', 'block_myvideos'), $privacityoptions);
        $mform->addHelpButton('publiclevel', 'publiclevel', 'block_myvideos');

        // Allow comments
        $mform->addElement('advcheckbox', 'allowcomments', get_string('allowcomments', 'block_myvideos'), '',
            array('group' => 1), array(0, 1));
        $mform->addHelpButton('allowcomments', 'comments', 'block_myvideos');
        $mform->setType('allowcomments', PARAM_BOOL);

    }


    function _add_hidden_params() {

        $this->_form->addElement('hidden', 'courseid');
        $this->_form->setType('courseid', PARAM_INT);

        $this->_form->addElement('hidden', 'action');
        $this->_form->setType('action', PARAM_ALPHANUMEXT);

        $this->_form->addElement('hidden', 'returnmod');
        $this->_form->setType('returnmod', PARAM_INT);
    }


    function _add_terms() {

        // Terms of use
        $this->_form->addElement('textarea', 'termstext', get_string('terms', 'block_myvideos'), array('rows'=>'5', 'cols'=>'70'));
        $this->_form->setDefault('termstext', get_string('termstext', 'block_myvideos'));

        $this->_form->addElement('checkbox', 'terms', get_string('termsok', 'block_myvideos'));
        $this->_form->addRule('terms', null, 'required', null, 'client');
    }

}
