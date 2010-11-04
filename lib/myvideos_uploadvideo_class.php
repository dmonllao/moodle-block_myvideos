<?php // $Id: myvideos_uploadvideo_class.php,v 1.3 2010/11/04 11:14:41 davmon Exp $

/**
 * Class to view add an uploaded video
 * 
 * @package      blocks/myvideos
 * @copyright    2010 David Monllao <david.monllao@urv.cat>
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt
 */

require_once($CFG->dirroot.'/blocks/myvideos/lib/myvideos_actionable.php');
require_once($CFG->dirroot.'/blocks/myvideos/lib/ffmpeg_class.php');


class myvideos_uploadvideo_class extends myvideos_actionable {
       
    
    function process_data() {
        
        global $CFG, $USER;
        
        require_capability('block/myvideos:uploadvideo', get_context_instance(CONTEXT_COURSE, $this->_courseid));
        
        if ($data = $this->_mform->get_data()) {
            
            if (!confirm_sesskey()) {
                print_error('errorwrongsesskey', 'block_myvideos');
            }
        
            // Dirty hack to avoid notice
            if (empty($data->allowcomments)) {
                $data->allowcomments = '0';
            }
            
            $ffmpeg = new ffmpeg_class();
            
            $ffmpeg->check_file();
            $ffmpeg->encode_video();
            $ffmpeg->create_thumbnail();
            
            
            $now = time();
            $randomstring = rand(10, 99);
            $path = rtrim(get_config('blocks/myvideos', 'moodlepath'), '/').'/'.$USER->id;
            
            $videoname = $USER->id.'_'.$now.'_'.$randomstring.'.flv';
            $ffmpeg->rename_video($videoname);
            
            $thumbname = $USER->id.'_'.$now.'_'.$randomstring.'.jpg';
            $ffmpeg->rename_thumbnail($thumbname);
            
            $videosize = $ffmpeg->get_video_info();
            
            // Video name/path format moodledata/USER->id/videos/USER->id_TIMESTAMP_NN.flv
            // Thumb name/path format moodledata/USER->id/thumbs/USER->id_TIMESTAMP_NN.flv
            $videopath = $path.'/videos/'.$videoname;
            $thumbpath = $path.'/thumbs/'.$thumbname;
            
            $ffmpeg->get_files($videopath, $thumbpath);
            
            $video->userid = $USER->id;
            $video->link = '0';
            $video->publiclevel = $data->publiclevel;
            $video->title = $data->title;
            $video->description = $data->description;
            $video->author = $data->author;
            $video->video = $videoname;
            $video->allowcomments = $data->allowcomments;
            $video->timecreated = time();
            $video->width = $videosize->width;
            $video->height = $videosize->height;
            
            $video->id = insert_record('myvideos_video', $video);
            
            if (!$video->id) {
                print_error('errorcant', 'block_myvideos');
            }
               
            // Video-keywords relation
            $this->_update_video_tags($video->id, $data->tags);
            
            // If user come from myvideos module return to the module
            if (!empty($data->returnmod)) {
                echo '<script>myvideos_return_to_mod('.$video->id.', "'.$video->title.'", "'.get_string("uploadedvideo", "block_myvideos").'");</script>';
            }
            
            $redirecturl = $CFG->wwwroot.'/blocks/myvideos/index.php?action=viewvideo&amp;courseid='.$this->_courseid.'&amp;id='.$video->id;
            redirect($redirecturl, get_string("changessaved"));
        }
    }
    
}

?>