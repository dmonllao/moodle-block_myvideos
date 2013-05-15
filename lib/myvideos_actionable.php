<?php // $Id: myvideos_actionable.php,v 1.1 2010/07/04 21:51:24 arborrow Exp $


/**
 * Abstract class with common methods for the different myvideos actions
<<<<<<< HEAD
 *
 * Child classes must require files
 *
=======
 *
 * Child classes must require files
 *
>>>>>>> 141f0d9511d8346ea314daad70d692881f5ca040
 * @abstract
 * @package      blocks/myvideos
 * @copyright    2010 David Monllao <david.monllao@urv.cat>
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt
 */
class myvideos_actionable {

    var $_mform;

    var $_courseid;
    var $_action;
    var $_returnmod;

    var $_id;


    /**
     * Initializes $this->_mform value with child class form
     *
     * Child classes without form must override the constructor
     *
     * It looks for an action form in forms/ folder to "autoload" it. It also adds
     * to the moodle form the myvideos block common params
     *
     * @param     string      $url        To be able to use the actions outside index.php
     */
    function __construct($url = false) {

        global $CFG, $COURSE;

        if (!$url) {
            $url = $CFG->wwwroot.'/blocks/myvideos/index.php?courseid='.$COURSE->id;
        }

        // Getting child class form name
        $formname = str_replace('_class', '_form', get_class($this));

        $formpath = $CFG->dirroot.'/blocks/myvideos/forms/'.$formname.'.php';
        if (file_exists($formpath)) {

            require_once($formpath);
            $this->_mform = new $formname($url);

        // If it doesn't have form it doesn't need it
        } else {
            $this->_mform = false;
        }

        // Videoid
        $this->_id = optional_param('id', false, PARAM_INT);

        // Common index params
        $this->_courseid = optional_param('courseid', 1, PARAM_INT);
        $this->_action = optional_param('action', 'videos', PARAM_ALPHA);
        $this->_returnmod = optional_param('returnmod', 0, PARAM_BOOL);

    }


    /**
     * Display the child class form
     *
     * Classes without form must override the method
     */
    function display() {

        if (!empty($this->_id)) {
            $data["id"] = $this->_id;
        }

        $data["courseid"] = $this->_courseid;
        $data["action"] = $this->_action;
        $data["returnmod"] = $this->_returnmod;

        $this->_mform->set_data($data);
        $this->_mform->display();
    }


    /**
     * Returns a string with the video tags
     *
     * @param   integer   $videoid
     * @return  string
     */
    function _get_video_tags($videoid) {

        global $CFG, $DB;

        $sql = "SELECT k.id, k.keyword
                FROM {myvideos_video_tag} t
                JOIN {myvideos_video_keyword} k ON k.id = t.keywordid
                WHERE t.videoid = '$videoid' ORDER BY t.id";

        if (!$keywords = $DB->get_records_sql($sql)) {
            return '';
        }

        $strings = array();
        foreach ($keywords as $keyword) {
            $strings[] = $keyword->keyword;
        }

        return (implode(', ', $strings));
    }


    /**
     * Updates the video tags records
     *
     * @pre     The video record exists in myvideos_video
     *
     * @param   integer   $videoid
     * @param   string    $tagsstring      Raw tags string recieved
     * @return  boolean                    Success?
     */
    function _update_video_tags($videoid, $tagsstring) {

        global $CFG, $DB;

        if (!$tagsstring) {
            return false;
        }

        $keywords = $this->_get_submitted_keywords($tagsstring);

        // Getting actual video tags
        $videotagssql = "SELECT t.id, k.keyword
                         FROM {myvideos_video_tag} t
                         JOIN {myvideos_video_keyword} k ON k.id = t.keywordid
                         WHERE t.videoid = '$videoid'";
        $videotags = $DB->get_records_sql($videotagssql);


        // Iterate through the selected tags
        foreach ($keywords as $keyword) {

            // Getting keyword id
            if (!$keywordid = $DB->get_field('myvideos_video_keyword', 'id', array('keyword' => $keyword))) {

                // Inserting tag
                $keywordobj->keyword = addslashes($keyword);
                if (!$keywordid = $DB->insert_record('myvideos_video_keyword', $keywordobj)) {
                    print_error('errorinserting', 'block_myvideos');
                }
            }

            // Adding new video tags
            if (!$tagid = $DB->get_field('myvideos_video_tag', 'id', array('videoid' => $videoid, 'keywordid' => $keywordid))) {

                $tagdata->videoid = $videoid;
                $tagdata->keywordid = $keywordid;

                if (!$tagid = $DB->insert_record('myvideos_video_tag', $tagdata)) {
                    print_error('errorinserting', 'block_myvideos');
                }
            }

            unset($videotags[$tagid]);
        }

        // Deleting old keywords relations
        if (!empty($videotags)) {
            foreach ($videotags as $videotag) {
                $DB->delete_records('myvideos_video_tag', array('id' => $videotag->id));
            }
        }

        return true;
    }


    /**
     * Cleans the user input and returns an array of keywords
     *
     * @param    string     $string      String submitted
     * @return   array                   Array of keywords
     */
    function _get_submitted_keywords($string) {

        // Initializing keywords string replaces
        $searchfrom = array(';', '_', ' ');
        $searchto = array(',', ',', ',');

        // Patch possible input errors
        $string = str_replace($searchfrom, $searchto, $string);

        // Skip multiple coma
        $string = preg_replace('/(,+)/', ',', $string);

        return explode(',', $string);
    }
}
