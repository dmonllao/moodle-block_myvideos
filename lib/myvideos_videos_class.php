<?php // $Id: myvideos_videos_class.php,v 1.3 2010/09/09 09:56:14 davmon Exp $

/**
 * Class to list the user videos
 *
 * @package      blocks/myvideos
 * @copyright    2010 David Monllao <david.monllao@urv.cat>
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt
 */

require_once($CFG->dirroot.'/blocks/myvideos/lib/myvideos_actionable.php');

/**
 * Class to list the user videos
 *
 * It gives access to different myvideos_video_class methods
 */
class myvideos_videos_class extends myvideos_actionable {

    var $_uservideos;
    var $_userfavorites;


    /**
     * myvideos_videos_class implementation of process_data()
     *
     * It loads the user videos to a private var
     */
    function process_data() {

        global $CFG, $USER, $DB;

        $uservideossql = "SELECT DISTINCT mv.id, mv.title, mv.userid, mv.author,
                          mv.video, mv.link, 0 as favorite, mv.views, mv.width, mv.height
                          FROM {$CFG->prefix}myvideos_video mv
                          WHERE mv.userid = '$USER->id' AND mv.timedeleted = '0'
                          ORDER BY mv.timecreated DESC";

        $userfavoritessql = "SELECT DISTINCT mv.id, mv.title, mv.userid, mv.author,
                             mv.video, mv.link, 1 as favorite, mv.views, mv.width, mv.height
                             FROM {$CFG->prefix}myvideos_video mv
                             JOIN {$CFG->prefix}myvideos_video_favorite mvf ON mv.id = mvf.videoid
                             WHERE mvf.userid = '$USER->id'
                             ORDER BY mv.timecreated DESC";

        $this->_uservideos = $DB->get_records_sql($uservideossql);
        $this->_userfavorites = $DB->get_records_sql($userfavoritessql);
    }


    /**
     * myvideos_videos_class implementation of display()
     *
     * It outputs the user videos and the favorite user videos lists
     * as well as the main div to preview the selected video
     */
    function display() {

        global $CFG;

        if (!$this->_uservideos && !$this->_userfavorites) {

            echo '<div class="myvideos_box">';
            echo get_string('younovideos', 'block_myvideos');
            echo '</div>';

        } else {

            // Main div
            echo '<div id="myvideos">';

            // We must have a reference to the first listed video
            $defaultuploadedvideo = $this->_print_column($this->_uservideos, "uservideoslabel", 'float:left;');
            $defaultlinkedvideo = $this->_print_column($this->_userfavorites, "favoritevideoslabel", 'float:right;');

            if ($defaultuploadedvideo) {
                $defaultvideo = $defaultuploadedvideo;
            } else {
                $defaultvideo = $defaultlinkedvideo;
            }

            // Player div
            echo '<div id="id_player" class="myvideos_videoplayer">';
            myvideos_show_video($defaultvideo, false, true);

            // Actions div
            myvideos_show_video_actions($defaultvideo, $this->_courseid);
            echo '</div>';

            // +1 visualizations
            myvideos_add_view($defaultvideo);

            // Main div closed
            echo '</div>';

        }

    }


    /**
     * Prints a videos column
     *
     * @param     array       $videos      Videos to list
     * @param     string      $titlekey    TileList title
     * @param     string      $divstyle    float size
     * @return    integer                  Returns the key of the first video listed to display it by default
     */
    public function _print_column($videos, $titlekey, $divstyle='') {

        global $CFG, $PAGE;


        if ($videos) {

            echo '<div class="myvideos_scroll" id="'.$titlekey.'" style="'.$divstyle.'">';
            echo '<h3>'.get_string($titlekey, "block_myvideos").'</h3>';

            foreach ($videos as $key => $video) {

                // We store the first video (latest added) to return
                if (empty($defaultvideokey)) {
                    $defaultvideokey = $key;
                }

                echo '<div class="myvideos_video" id="'.$titlekey.'_'.$video->id.'">';

                if ($video->link == 0) {
                    $imgsrc = $CFG->wwwroot.'/blocks/myvideos/getfile.php?videoid='.$video->id.'&amp;thumb=1';
                } else {
                    $imgsrc = $CFG->wwwroot.'/blocks/myvideos/pix/linkthumb.gif';
                }

                echo '<div style="float:left;">'.
                     '<a href="#" id="id_video_preview_' . $video->id . '"><img src="'.$imgsrc.'" class="myvideos_thumb"/></a>'.
                     '</div>';
                echo '<div class="myvideos_video_info">'.get_string("videotitle", "block_myvideos").': '.$video->title.'</div>';
                echo '<div class="myvideos_video_info">'.get_string("author", "block_myvideos").': '.$video->author.'</div>';
                echo '<div class="myvideos_video_info">'.get_string("views", "block_myvideos").' '.$video->views.'</div>';

                echo '</div>';

                $params = array('courseid' => $this->_courseid, 'selected' => $video->id, 'favorite' => $video->favorite);
                $PAGE->requires->yui_module('moodle-block_myvideos-videos', 'M.block_myvideos.init_preview_video', array($params), null, true);
            }

            echo '</div>';

            return $videos[$defaultvideokey];
        }

        return false;
    }

}

