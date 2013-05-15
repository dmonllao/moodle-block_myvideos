<?php // $Id: myvideos_video_class.php,v 1.2 2010/07/06 08:11:14 davmon Exp $

/**
 * Class to view an user video (uploaded, linked or "favorited")
 *
 * @package      blocks/myvideos
 * @copyright    2010 David Monllao <david.monllao@urv.cat>
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt
 */

require_once($CFG->dirroot.'/blocks/myvideos/lib/myvideos_actionable.php');

require_once($CFG->dirroot.'/blocks/myvideos/locallib.php');
require_once($CFG->dirroot.'/blocks/myvideos/forms/myvideos_editvideo_form.php');


/**
 * Class to view an user video (uploaded, linked or "favorited")
 *
 * It manages has different actions, all related to one myvideos_video instance,
 * edit the video info, delete an user video, delete a favorite video or view a video
 */
class myvideos_video_class extends myvideos_actionable {

    /**
     * myvideos_video_class implementation of process_data()
     */
    function process_data() {

        global $CFG, $USER, $DB;

        if (!$this->_id) {
            print_error('errornoid', 'block_myvideos');
        }

        $redirecturl = $CFG->wwwroot.'/blocks/myvideos/index.php?courseid='.$this->_courseid;

        switch ($this->_action) {

            case 'editvideo':

                $editurl = $CFG->wwwroot.'/blocks/myvideos/index.php';
                $this->_mform = new myvideos_editvideo_form($editurl);

                if ($video = $this->_mform->get_data()) {

                    // To ensure that the user is the video "chef"
                    if (!$DB->get_field('myvideos_video', 'id', array('id' => $video->id, 'userid' => $USER->id))) {
                        print_error('errorwrongvideoid', 'block_myvideos');
                    }

                    if (!$DB->update_record('myvideos_video', $video)) {
                        print_error('cantbemodified', 'block_myvideos');
                    }

                    // Updating video tags
                    $this->_update_video_tags($video->id, $video->tags);

                    redirect($redirecturl, get_string('changessaved'), 2);
                }

                break;

            case 'deletevideo':

                if (optional_param('confirm', false, PARAM_INT)) {

                    // Only timedeleted must be updated
                    $video = $DB->get_record('myvideos_video', array('id' => $this->_id, 'userid' => $USER->id));
                    $video->timedeleted = time();

                    if (!$DB->update_record('myvideos_video', $video)) {
                        print_error('errorcantdelete', 'block_myvideos');
                    }

                    redirect($redirecturl, get_string('changessaved'), 2);
                }
                break;

            case 'deletefavoritevideo':

                if (optional_param('confirm', false, PARAM_INT)) {

                    if (!$DB->delete_records('myvideos_video_favorite', array(
                        'videoid' => $this->_id, 'userid' => $USER->id))) {
                        print_error('errorcantdelete', 'block_myvideos');
                    }

                    redirect($redirecturl, get_string('changessaved'), 2);
                }
                break;

            default:
                break;
        }

    }

    /**
     * myvideos_video_class implementation of display()
     */
    function display() {

        global $CFG, $USER, $DB, $OUTPUT;

        $videodata = $DB->get_record('myvideos_video', array('id' => $this->_id));
        echo '<h2 class="myvideos_title">'.$videodata->title.'</h2>';


        switch ($this->_action) {

            case 'viewvideo':

                // We must check that the video is public or a $USER video
                if ($videodata->userid != $USER->id && $videodata->publiclevel < 1) {
                    print_error('errorwrongvideoid', 'block_myvideos');
                }

                // Print video description
                echo '<div class="myvideos_description">';
                echo format_text($videodata->description, FORMAT_PLAIN);
                echo '</div>';

                myvideos_show_video($videodata);

                // +1 visualizations
                myvideos_add_view($videodata);

                // Video comments
                if ($videodata->allowcomments) {

                    // Auxiliar text to i18n video added notification
                    echo '<script type="text/javascript">var myvideos_comment_added_text = "'.get_string("commentadded", "block_myvideos").'";</script>';

                    // Inside a div to hide it after submitting a comment
                    echo '<div id="myvideos_id_comment_form">';
                    parent::display();
                    echo '</div>';

                    myvideos_show_comments($videodata);
                }

                break;

            case 'editvideo':

                // Ensure that the user is the video "chef"
                if ($videodata->userid != $USER->id) {
                    print_error('errorwrongvideoid', 'block_myvideos');
                }

                // Can't use parent display method cause we must load the form values
                $videodata->id = $this->_id;
                $videodata->action = $this->_action;
                $videodata->courseid = $this->_courseid;
                $videodata->tags = $this->_get_video_tags($videodata->id);

                $this->_mform->set_data($videodata);
                $this->_mform->display();
                break;

            default:

                $linkyes = $CFG->wwwroot.'/blocks/myvideos/index.php?courseid='.$this->_courseid.'&amp;action='.$this->_action.'&amp;id='.$this->_id.'&amp;confirm=1';
                $linkno = $CFG->wwwroot.'/blocks/myvideos/index.php?courseid='.$this->_courseid;
                echo $OUTPUT->confirm(get_string('areyousure', 'block_myvideos'), $linkyes, $linkno);
                break;
        }
    }

}
