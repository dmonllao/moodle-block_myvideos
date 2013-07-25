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

        global $CFG, $USER, $DB;

        require_capability('block/myvideos:uploadvideo', context_course::instance($this->_courseid));

        if ($data = $this->_mform->get_data()) {

            if (!confirm_sesskey()) {
                print_error('errorwrongsesskey', 'block_myvideos');
            }

            // Saving the file to a tmp filearea
            $tmpfilepath = $this->_mform->save_temp_file('uploadfile');
            $tmpfileitemid = file_get_submitted_draft_itemid('uploadfile');

            $ffmpeg = new ffmpeg_class($tmpfilepath, $tmpfileitemid);

            $ffmpeg->check_file();
            $ffmpeg->encode_video();
            $ffmpeg->create_thumbnail();

            $now = time();
            $randomstring = rand(10, 99);

            // Video filename
            $videofilename = $USER->id.'_'.$now.'_'.$randomstring.'.flv';
            $ffmpeg->rename_video($videofilename);

            // Thumb filename
            $thumbfilename = $USER->id.'_'.$now.'_'.$randomstring.'.jpg';
            $ffmpeg->rename_thumbnail($thumbfilename);

            $videosize = $ffmpeg->get_video_info();

            // Long videos encoding could pass over the database timeout
            $this->db_reconnect();

            $video = new stdClass();
            $video->userid = $USER->id;
            $video->link = '0';
            $video->publiclevel = $data->publiclevel;
            $video->title = $data->title;
            $video->description = $data->description;
            $video->author = $data->author;
            $video->video = $videofilename;
            $video->allowcomments = $data->allowcomments;
            $video->timecreated = time();
            $video->width = $videosize->width;
            $video->height = $videosize->height;

            $video->id = $DB->insert_record('myvideos_video', $video);

            if (!$video->id) {
                print_error('errorinserting', 'block_myvideos');
            }

            // Video-keywords relation
            $this->_update_video_tags($video->id, $data->tags);

            // Storing files into moodle filesystem
            $ffmpeg->get_files($video->id, $videofilename, $thumbfilename);

            // Long videos encoding could pass over the database timeout
            $this->db_reconnect();

            // If user come from myvideos module return to the module
            if (!empty($data->returnmod)) {
                echo '<script>myvideos_return_to_mod('.$video->id.', "'.$video->title.'", "'.get_string("uploadedvideo", "block_myvideos").'");</script>';
            }

            $redirecturl = $CFG->wwwroot.'/blocks/myvideos/index.php?action=viewvideo&amp;courseid='.$this->_courseid.'&amp;id='.$video->id;
            redirect($redirecturl, get_string("changessaved"));
        }
    }

    /**
     * For installations with low wait DB connection timeout
     */
    function db_reconnect() {
        global $CFG, $DB;

        $DB->dispose();
        $connected = $DB->connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass, $CFG->dbname, $CFG->prefix, $CFG->dboptions);

        if (!$connected) {
            print_error('errorinserting', 'block_myvideos');
        }

    }

}
