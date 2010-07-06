<?php // $Id: myvideos_linkvideo_class.php,v 1.2 2010/07/06 08:11:13 davmon Exp $

/**
 * Class to view add a linked video
 * 
 * @package      blocks/myvideos
 * @copyright    2010 David Monllao <david.monllao@urv.cat>
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt
 */

require_once($CFG->dirroot.'/blocks/myvideos/lib/myvideos_actionable.php');


/**
 * Class to view add a linked video
 */
class myvideos_linkvideo_class extends myvideos_actionable {
       
    
    function process_data() {
        
        global $CFG, $USER;
        
        require_capability('block/myvideos:linkvideo', get_context_instance(CONTEXT_COURSE, $this->_courseid));
        
        if ($data = $this->_mform->get_data()) {
            
            if (!confirm_sesskey()) {
                print_error('errorwrongsesskey', 'block_myvideos');
            }
            
            // Dirty hack to avoid notice caused by checkbox empty value
            if (empty($data->allowcomments)) {
                $data->allowcomments = '0';
            }
            
            $video->userid = $USER->id;
            $video->title = $data->title;
            $video->description = $data->description;
            $video->author = $data->author;
            $video->width = $this->_get_embedded_size($data->embedded, 'width');
            $video->height = $this->_get_embedded_size($data->embedded, 'height');
            $video->video = $this->_clean_embedded($data->embedded, $video->width, $video->height);
            $video->allowcomments = $data->allowcomments;
            $video->link = '1';
            $video->publiclevel = $data->publiclevel;
            $video->timecreated = time();
            
            if (!$video->width || !$video->height) {
                print_error('errornosize', 'block_myvideos');
            }
            
            $video->id = insert_record('myvideos_video', $video);
            
            if (!$video->id) {
                print_error('errorcant', 'block_myvideos');
            }
            
            // Video-keywords relation
            $this->_update_video_tags($video->id, $data->tags);
            
            // If user come from myvideos module return to the module
            if (!empty($data->_returnmod)) {
                echo '<script>myvideos_return_to_mod('.$video->id.', "'.$video->title.'", "'.get_string("uploadedvideo", "block_myvideos").'");</script>';
            }
            
            $redirecturl = $CFG->wwwroot.'/blocks/myvideos/index.php?action=viewvideo&amp;courseid='.$this->_courseid.'&amp;id='.$video->id;
            redirect($redirecturl, get_string("changessaved"));
        }
    }
    
    
    /**
     * Cleans the code submitted to include only a object
     *
     * .In the first step it cleans the info outside the <object> or <embed> tag
     * .In the second step it formats the video size to a all browser friendly format 
     * .In the third step we must clean the code submitted (cleanAttributes breaks styles we must reimplement something similar) 
     * 
     * Tested videos from: youtube.com, vimeo.com, veoh.com, ustream.tv, 
     *                     video.google, video.yahoo, dailymotion.com, 
     *                     5min.com, myspace.com, vbox7.com
     * 
     * @param     string      $html     Tag submitted by the user, only cleaned by clean_param with PARAM_CLEAN
     * @param     integer     $width    Video width
     * @param     integer     $height   Video height
     * @return    string                Only the object tag
     */
    function _clean_embedded($html, $width, $height) {
        
        global $ALLOWED_TAGS;
        
        // If there are no detected sizes we can't save
        if (!$width || !$height) {
            return false;
        }
        
        // Vimeo and other video repositories adds extra info with the <object>
        preg_match('/<(. ?|)object(.*?)object(.*?)>/is', $html, $object);
        
        if (empty($object)) {
            
            // We have to give an oportunity to google video
            preg_match('/<(. ?|)embed(.*?)embed(.*?)>/is', $html, $object);
            
            if (empty($object)) {
                print_error('errorcantsave', 'block_myvideos');
            }
        }
        
        $html = $object[0];
        
        // There are many problems with Chrome to apply object sizes based on width="1" and height="1"
        
        // Deleting old values width="" height="" and style=width:px;height:px
        $html = preg_replace('/width=\\\["\'](.*?)\\\["\']/is', '', $html);
        $html = preg_replace('/height=\\\["\'](.*?)\\\["\']/is', '', $html);
        $html = preg_replace('/style=(.*?) /is', '', $html);
        
        // Adding new styles string
        $stylestring = 'style=\"width:'.$width.'px;height:'.$height.'px;\"';
        $html = str_ireplace('<object', '<object '.$stylestring, $html);
        $html = str_ireplace('<embed', '<embed '.$stylestring, $html);
        
        // Adapted clean_text (moodle/lib/weblib.php
        $html = preg_replace('/(&#[0-9]+)(;?)/is', "\\1;", $html);
        $html = preg_replace('/(&#x[0-9a-fA-F]+)(;?)/is', "\\1;", $html);
        
        // The tags <embed> and <object> must be allowed to link external videos
        // We could have used the moodle setting, but then that tags will be allowed in the whole site
        if (strstr($ALLOWED_TAGS, '<embed>') == false || strstr($ALLOWED_TAGS, '<object>') == false) {
            $allowed = $ALLOWED_TAGS.',<embed>,<object>';
        } 
        
        $html = strip_tags($html, $allowed);
        
        $html = eregi_replace("([^a-z])language([[:space:]]*)=", "\\1Xlanguage=", $html);
        $html = eregi_replace("([^a-z])on([a-z]+)([[:space:]]*)=", "\\1Xon\\2=", $html);
        
        return $html;
    }
    
    
    /**
     * Gets the submitted video size
     * 
     * It searches width="ASDASD" styles and width:ASDASDpx 
     * 
     * @param     string     $html      Code submitted
     * @param     string     $value     The searched dimension
     * @return    integer               The result or false
     */
    function _get_embedded_size($html, $value) {
        
        // $value='' OR $value="" OR $vALue=''.....
        preg_match('/'.$value.'=\\\["\'](.*?)\\\["\']/is', $html, $size);
        
        // Google video uses style to define video size
        if (empty($size)) {
            preg_match('/'.$value.':(.*?)px/is', $html, $size);
        }
        
        if (empty($size)) {
            return false;
        }
        
        return $size[1];
    }
    
}

?>