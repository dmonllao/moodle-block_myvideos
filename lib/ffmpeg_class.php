<?php // $Id: ffmpeg_class.php,v 1.1 2010/07/04 21:51:23 arborrow Exp $


/**
 * Class to manage the video encoding
 * 
 * @uses      ssh2 (http://php.net/manual/en/book.ssh2.php)
 * @uses      ffmpeg (http://ffmpeg.org/)
 * @uses      mencoder (http://www.mplayerhq.hu)
 * 
 * @package      blocks/myvideos
 * @copyright    2010 David Monllao <david.monllao@urv.cat>
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt
 */
class ffmpeg_class {

    var $_config;
    var $_cnx;
    
    var $_filetypes;
    var $_quality;
    var $_defaultpermissions;
    
    // Absolute rute
    var $_tmpfile;
    var $_convertedfile;
    var $_thumb;
    
    
    /**
     * Adds block configuration to private var
     */
    function __construct() {
        
        global $CFG, $COURSE;
        
        // Get and Check block config
        $this->_config = get_config('blocks/myvideos');
        $vars = array('server', 'username', 'password', 'path', 'moodlepath');
        foreach ($vars as $var) {
            
            if (!isset($this->_config->$var)) {
                redirect($CFG->wwwroot.'/course/view.php?id='.$COURSE->id, get_string('noblockconfig', 'block_myvideos'), 5);
            }
        } 
        
        // Clean slash
        $this->_config->path = rtrim($this->_config->path, '/');
        $this->_config->moodlepath = rtrim($this->_config->moodlepath, '/');
        
        
        // Connection and authentication to server
        if (!function_exists('ssh2_connect')) {
            print_error('errornossh', 'block_myvideos');
        }
        
        $this->_cnx = ssh2_connect($this->_config->server, 22);
        if (!$this->_cnx) {
            print_error('errorffmpegconnecting', 'block_myvideos');
        }
        if (!ssh2_auth_password($this->_cnx, $this->_config->username, $this->_config->password)) {
            print_error('errorffmpeglogin', 'block_myvideos');
        }
        
        // Accepted filetypes (all in lower case)
        $this->_filetypes = array('avi', 'asf', 
                                 'mpeg', 'realmedia', 
                                 'Flash Video', 'QuickTime');
        
        // Quality options
        $this->_quality = array('1' => '150k', '2' => '500k', '3' => '1000k');
        
        $this->_defaultpermissions = 0770;
    }
    
    
    /**
     * Check if the video will be encoded
     */
    function check_file() {
        
        global $CFG, $USER, $COURSE;
        
        if ($_FILES['uploadfile']['error'] != 0) {
            print_error('errorffmpegfile', 'block_myvideos');
        }

        $this->_tmpfile = $this->_config->path.'/'.$USER->id.'_'.rand(10, 99);
        
        // Send video to encode
        if (!ssh2_scp_send($this->_cnx, $_FILES['uploadfile']['tmp_name'], $this->_tmpfile, $this->_defaultpermissions)) {
            print_error('errorffmpegsending', 'block_myvideos');
        }

        // Check filetype (file command)
        $filetypecommand = 'file '.$this->_tmpfile;
        $videofiletype = $this->execute_command($filetypecommand);
        $videofiletype = str_replace($this->_tmpfile, '', $videofiletype);
        
        foreach ($this->_filetypes as $filetype) {
            
            if (strstr(strtolower($videofiletype), strtolower($filetype)) != false) {
                $accepted = 1;
            }
        }
        
        // If wrong filetype delete file
        if (empty($accepted)) {
            
            $this->delete_files();
            redirect($CFG->wwwroot.'/blocks/myvideos/index.php?courseid='.$COURSE->id.'&action=uploadvideo', get_string('errorffmpegfiletype', 'block_myvideos'), 5);
        }
        
    }
    
    
    /**
     * Encodes the file with ffmpeg, if fails, it tries to pass through mencoder first
     *
     */
    function encode_video() {
        
        global $CFG, $USER, $COURSE;
        
        if (!$_FILES['uploadfile']['tmp_name']) {
            return false;
        }
        
        // Quality
        $qualitysubmitted = optional_param('quality', '2', PARAM_INT);
        $bitratestring = ' -b '.$this->_quality[$qualitysubmitted];
        $this->_convertedfile = $this->_tmpfile.'.flv';
    
        // If override ffmpeg flash video conversion was enabled we skip the conversion
        if (optional_param('overrideffmpeg', false, PARAM_INT) && 
            has_capability('block/myvideos:overrideffmpeg', get_context_instance(CONTEXT_COURSE, $COURSE->id))) {
            
                $filetypecommand = 'file '.$this->_tmpfile;
                $videofiletype = $this->execute_command($filetypecommand);
                
                // Only for flash video files
                if (strstr(strtolower($videofiletype), 'flash video') != false) {
                    
                    $movecommand = 'mv '.$this->_tmpfile.' '.$this->_convertedfile;
                    $this->execute_command($movecommand);
                    return true;
                }
            
        }
        
        // Encode video using ffmpeg
        $command = 'ffmpeg -i '.$this->_tmpfile.' -ar 44100 '.$bitratestring.' '.$this->_convertedfile;
        $feedback = $this->execute_command($command);
        
        // If it can't be encoded we try to encode with mencoder
        if (!strstr($feedback, 'Press [q] to stop encoding')) {
            
            // Delete crashed file
            $rmcommand = 'rm '.$this->_convertedfile;
            $this->execute_command($rmcommand);
            
            $mencodercommand = 'mencoder '.$this->_tmpfile.' -ovc lavc -oac mp3lame -o '.$this->_convertedfile.'.flv';
            $feedback = $this->execute_command($mencodercommand);
            
            if (!strstr($feedback, 'Writing index...')) {
                
                // If we can't convert the file, redirection to index
                $this->delete_files();
                redirect($CFG->wwwroot.'/blocks/myvideos/index.php?courseid='.$COURSE->id.'&action=uploadvideo', get_string('fileconversionerror', 'block_myvideos'), 5);
            }
            
            // To ffmpeg again
            $command = 'ffmpeg -i '.$this->_convertedfile.'.flv -ar 44100 '.$bitratestring.' '.$this->_convertedfile;
            $feedback = $this->execute_command($command);
            if (!strstr($feedback, 'Press [q] to stop encoding')) {
                
                // If we can't convert the file redirection to index
                $this->delete_files();
                redirect($CFG->wwwroot.'/blocks/myvideos/index.php?courseid='.$COURSE->id.'&action=uploadvideo', get_string('fileconversionerror', 'block_myvideos'), 5);
            }
        }
        
    }
    
    
    /**
     * Create a thumbnail from .flv file
     *
     */
    function create_thumbnail() {
        
        global $CFG, $COURSE;
        
        $thumbcommand = "ffmpeg -i ".$this->_convertedfile." -ss 00:00:01 -vframes 1 ".$this->_tmpfile.".%d.jpg";
        $feedback = $this->execute_command($thumbcommand);
        
        if (!strstr($feedback, 'Press [q] to stop encoding')) {
            
            $this->delete_files();
            redirect($CFG->wwwroot.'/blocks/myvideos/index.php?courseid='.$COURSE->id.'&action=uploadvideo', get_string('thumberror', 'block_myvideos'), 5);
        }
        
        $this->_thumb = $this->_tmpfile.'.1.jpg';
    }
    
    
    function rename_video($name) {
        
        $renamevideopath = $this->_config->path.'/'.$name;
        $renamevideocommand = 'mv '.$this->_convertedfile.' '.$renamevideopath;
        $this->execute_command($renamevideocommand);
        $this->_convertedfile = $renamevideopath;
        
        return $this->_convertedfile;
    }
    
    
    function rename_thumbnail($name) {
        
        $renamethumbpath = $this->_config->path.'/'.$name;
        $renamethumbcommand = 'mv '.$this->_thumb.' '.$renamethumbpath;
        $this->execute_command($renamethumbcommand);
        $this->_thumb = $renamethumbpath;
        
        return $this->_thumb;
    }
    
    
    function get_video_info() {
        
        $infocommand = 'ffmpeg  -i '.$this->_convertedfile;
        $feedback = $this->execute_command($infocommand);
        
        // Info
        // TODO: replace for preg_match
        if (strstr($feedback, $this->_convertedfile) != false) {
            $info = explode($this->_convertedfile, $feedback);
            
            // $streams[1] => Video, $streams[2] => Audio
            // TODO: Check streams strings
            $streams = explode('Stream #', $info[1]);
            
            // Video data
            $videodata = explode(',', $streams[1]);
            
            // Video size
            $size = explode('x', $videodata[2]);
            $videoinfo->width = $size[0];
            $videoinfo->height = $size[1];
        }
        
        return $videoinfo;
    }
    
    
    function get_files($videopath, $thumbpath) {

        global $USER;
        
        $videoexp = explode('/', $this->_convertedfile);
        $thumbexp = explode('/', $this->_thumb);
        $videofilename = $videoexp[count($videoexp)-1];
        $thumbfilename = $thumbexp[count($thumbexp)-1];
        
        $mainpath = rtrim($this->_config->moodlepath, '/');
                
        if (!file_exists($mainpath.'/'.$USER->id)) {
            
            
            if (!file_exists($mainpath)) {
                            
                // Creating main path
                if (!mkdir($mainpath, $this->_defaultpermissions, true)) {
                    print_error('errorcheckpermissions', 'block_myvideos');
                }
            }
            
            // Check moodlepath permissions
            if (!is_writable($mainpath)) {
                
                $this->delete_files();
                print_error('errorcheckpermissions', 'block_myvideos');
            }
            
            mkdir(rtrim($this->_config->moodlepath, '/').'/'.$USER->id.'/videos/', $this->_defaultpermissions, true);
            mkdir(rtrim($this->_config->moodlepath, '/').'/'.$USER->id.'/thumbs/', $this->_defaultpermissions, true);
        }
        if (!ssh2_scp_recv($this->_cnx, $this->_convertedfile, $videopath) ||
            !ssh2_scp_recv($this->_cnx, $this->_thumb, $thumbpath)) {
                
            $this->delete_files();
            print_error('errorffmpegrecieving', 'block_myvideos');
        }
        
        chmod($videopath, $this->_defaultpermissions);
        chmod($thumbpath, $this->_defaultpermissions);
        
        $this->delete_files();
    }
    
    
    function execute_command($command) {
        
        $stream = ssh2_exec($this->_cnx, $command, false);
        stream_set_blocking($stream, true);
        
        $data = '';
        while ( $buf = fread($stream, 4096)) {
            $data .= $buf;
        }
        
        fclose($stream);
        
        return $data;
    }
        
    
    function delete_files() {
        $rmcommand = 'rm '.$this->_tmpfile.' '.$this->_convertedfile.' '.$this->_thumb.' '.$this->_convertedfile.'.flv';
        $this->execute_command($rmcommand);
    }
    
}

?>
