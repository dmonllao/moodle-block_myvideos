<?php // $Id: myvideos_videos_class.php,v 1.1 2010/07/04 21:51:23 arborrow Exp $

/**
 * Class to list the user videos
 * 
 * @package      blocks/myvideos
 * @copyright    2010 David Monllao <david.monllao@urv.cat>
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt
 */

require_once($CFG->dirroot.'/blocks/myvideos/lib/myvideos_actionable.php');

require_js(array('yui_yahoo', 'yui_event', 'yui_connection'));
require_js($CFG->wwwroot.'/blocks/myvideos/lib/async.js');


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
        
        global $CFG, $USER;
        
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
        
        $this->_uservideos = get_records_sql($uservideossql);
        $this->_userfavorites = get_records_sql($userfavoritessql);
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
            
            // We must hae a reference to the first listed video
            $defaultuploadedvideo = $this->_print_column($this->_uservideos, get_string("uservideoslabel", "block_myvideos"), 'float:left;');
            $defaultlinkedvideo = $this->_print_column($this->_userfavorites, get_string("favoritevideoslabel", "block_myvideos"), 'float:right;');
            
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
     * @param     string      $title       TileList title
     * @param     string      $divstyle    float size
     * @return    integer                  Returns the key of the first video listed to display it by default
     */
    public function _print_column($videos, $title, $divstyle='') {

        global $CFG;
        
        
        if ($videos) {
            
            echo '<div id="myvideos_scroll" style="'.$divstyle.'">';
            echo '<h3>'.$title.'</h3>';
            
            foreach ($videos as $key => $video) {

                
                // We store the first video (latest added) to return
                if (empty($defaultvideokey)) {
                    $defaultvideokey = $key;
                }
                
                echo '<div class="myvideos_video">';
                
                if ($video->link == 0) {
                    $imgsrc = $CFG->wwwroot.'/blocks/myvideos/getfile.php?videoid='.$video->id.'&thumb=1';
                } else {
                    $imgsrc = $CFG->wwwroot.'/blocks/myvideos/pix/linkthumb.gif';
                }
                
                echo '<div style="float:left;">'.
                     '<a href="#" onclick="myvideos_preview_video(\''.$this->_courseid.'\', \''.$video->id.'\', \''.$video->favorite.'\');return false;"><img src="'.$imgsrc.'" class="myvideos_thumb"/></a>'.
                     '</div>';
                echo '<div class="myvideos_video_info">'.get_string("videotitle", "block_myvideos").': '.$video->title.'</div>';
                echo '<div class="myvideos_video_info">'.get_string("videoauthor", "block_myvideos").': '.$video->author.'</div>';
                echo '<div class="myvideos_video_info">'.get_string("views", "block_myvideos").' '.$video->views.'</div>';
                
                echo '</div>';
            }
            
            echo '</div>';
            
            return $videos[$defaultvideokey];
        }
        
        return false;
    }

}

?>