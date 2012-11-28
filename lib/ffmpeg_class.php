<?php // $Id: ffmpeg_class.php,v 1.4 2010/11/04 11:14:41 davmon Exp $


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
    var $_sftp;

    var $_filetypes;
    var $_quality;
    var $_defaultpermissions;

    // Absolute rute
    var $_tmpfile;
    var $_convertedfile;
    var $_thumb;

    // URL params
    var $_overrideffmpeg;
    var $_qualitysubmitted;


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


        // Search for ssh2
        if ($this->_config->server != 'localhost') {

            // Connection and authentication to server
            if (!function_exists('ssh2_connect')) {
                print_error('errornossh', 'block_myvideos');
            }

            $this->_cnx = @ssh2_connect($this->_config->server, 22);
            if (!$this->_cnx) {
                print_error('errorffmpegconnecting', 'block_myvideos');
            }
            if (!@ssh2_auth_password($this->_cnx, $this->_config->username, $this->_config->password)) {
                print_error('errorffmpeglogin', 'block_myvideos');
            }
        }

        // Accepted filetypes (all in lower case)
        $this->_filetypes = array('avi', 'asf',
                                 'mpeg', 'realmedia',
                                 'flash video', 'QuickTime');

        // Quality options
        $this->_quality = array('1' => '150k', '2' => '500k', '3' => '1000k');
        $this->_defaultpermissions = 0770;

        $this->_qualitysubmitted = optional_param('quality', '2', PARAM_INT);
        $this->_overrideffmpeg = false;
        $this->_sftp = null;
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
        if ($this->_config->server != 'localhost') {
            if (!@ssh2_scp_send($this->_cnx, $_FILES['uploadfile']['tmp_name'], $this->_tmpfile, $this->_defaultpermissions)) {
                print_error('errorffmpegsending', 'block_myvideos');
            }
        } else {
            if (!copy($_FILES['uploadfile']['tmp_name'], $this->_tmpfile)) {
                print_error('errorcheckpermissions', 'block_myvideos');
            }
            if (!chmod($this->_tmpfile, $this->_defaultpermissions)) {
                print_error('errorcheckpermissions', 'block_myvideos');
            }
        }

        // Check filetype (file command)
        $filetypecommand = 'file '.$this->_tmpfile;
        $videofiletype = $this->execute_command($filetypecommand);
        $videofiletype = str_replace($this->_tmpfile, '', $videofiletype);

        foreach ($this->_filetypes as $filetype) {

            if (strstr(strtolower($videofiletype), strtolower($filetype)) != false) {
                $accepted = 1;

                // We skip the flash video videos encoding
                if (strstr(strtolower($videofiletype), 'flash video') != false) {
                    $this->_overrideffmpeg = 1;
                }
            }
        }

        // If wrong filetype delete file
        if (empty($accepted)) {

            $this->delete_files();
            redirect($CFG->wwwroot.'/blocks/myvideos/index.php?courseid='.$COURSE->id.'&amp;action=uploadvideo', get_string('errorffmpegfiletype', 'block_myvideos'), 5);
        }

    }


    /**
     * Encodes the file with ffmpeg, if fails, it tries to pass through mencoder first
     */
    function encode_video() {

        global $CFG, $USER, $COURSE;

        if (!$_FILES['uploadfile']['tmp_name']) {
            return false;
        }

        // Quality
        $bitratestring = ' -b '.$this->_quality[$this->_qualitysubmitted];
        $this->_convertedfile = $this->_tmpfile.'.flv';

        // If override ffmpeg flash video conversion was enabled we skip the conversion
        if ($this->_overrideffmpeg) {

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
                redirect($CFG->wwwroot.'/blocks/myvideos/index.php?courseid='.$COURSE->id.'&amp;action=uploadvideo', get_string('fileconversionerror', 'block_myvideos'), 5);
            }

            // To ffmpeg again
            $command = 'ffmpeg -i '.$this->_convertedfile.'.flv -ar 44100 '.$bitratestring.' '.$this->_convertedfile;
            $feedback = $this->execute_command($command);
            if (!strstr($feedback, 'Press [q] to stop encoding')) {

                // If we can't convert the file redirection to index
                $this->delete_files();
                redirect($CFG->wwwroot.'/blocks/myvideos/index.php?courseid='.$COURSE->id.'&amp;action=uploadvideo', get_string('fileconversionerror', 'block_myvideos'), 5);
            }
        }

    }


    /**
     * Create a thumbnail from .flv file
     *
     *
     */
    function create_thumbnail() {

        global $CFG, $COURSE;

        $thumbcommand = "ffmpeg -i ".$this->_convertedfile." -ss 00:00:01 -vframes 1 ".$this->_tmpfile.".%d.jpg";
        $feedback = $this->execute_command($thumbcommand);

        if (!strstr($feedback, 'Press [q] to stop encoding')) {

            $this->delete_files();
            redirect($CFG->wwwroot.'/blocks/myvideos/index.php?courseid='.$COURSE->id.'&amp;action=uploadvideo', get_string('thumberror', 'block_myvideos'), 5);
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

            $streams = explode('Stream #', $info[1]);

            // Looking for a video stream
            for ($i = 1; $i < 5; $i++) {
                if (!empty($streams[$i]) && empty($videostream) && strstr($streams[$i], 'Video:') != false) {
                    $videostream = $streams[$i];
                }
            }

            // Video data
            $videodata = explode(',', $videostream);

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
                if (!@mkdir($mainpath, $this->_defaultpermissions, true)) {
                    print_error('errorcheckpermissions', 'block_myvideos');
                }
            }

            // Check moodlepath permissions
            if (!is_writable($mainpath)) {

                $this->delete_files();
                print_error('errorcheckpermissions', 'block_myvideos');
            }

            if (!@mkdir(rtrim($this->_config->moodlepath, '/').'/'.$USER->id.'/videos/', $this->_defaultpermissions, true) ||
                !@mkdir(rtrim($this->_config->moodlepath, '/').'/'.$USER->id.'/thumbs/', $this->_defaultpermissions, true)) {

                    print_error('errorcheckpermissions', 'block_myvideos');
            }
        }

        if ($this->_config->server != 'localhost') {
            if (!$this->get_remote_file($this->_convertedfile, $videopath) ||
                !$this->get_remote_file($this->_thumb, $thumbpath)) {

                $this->delete_files();
                print_error('errorffmpegrecieving', 'block_myvideos');
            }
        } else {

            if (!copy($this->_convertedfile, $videopath) ||
                !copy($this->_thumb, $thumbpath)) {

                $this->delete_files();
                print_error('errorffmpegfile', 'block_myvideos');
            }
        }

        chmod($videopath, $this->_defaultpermissions);
        chmod($thumbpath, $this->_defaultpermissions);

        $this->delete_files();
    }


    function execute_command($command) {

        $data = '';

        if ($this->_config->server != 'localhost') {
            $stream = @ssh2_exec($this->_cnx, $command, false);
            stream_set_blocking($stream, true);

            while ( $buf = fread($stream, 4096)) {
                $data .= $buf;
            }
            fclose($stream);

        // http://lists.mplayerhq.hu/pipermail/ffmpeg-user/2006-October/004773.html
        } else {

            $descriptorspec = array(0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
                                    1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
                                    2 => array("pipe", "w") // stderr is a file to write to
                                   );

            $pipes= array();
            $process = proc_open($command, $descriptorspec, $pipes);
            if (!is_resource($process)) {
                return false;
            }

            fclose($pipes[0]);

            stream_set_blocking($pipes[1],false);
            stream_set_blocking($pipes[2],false);

            $todo = array($pipes[1],$pipes[2]);

            while( true ) {
                $read = array();
                if( !feof($pipes[1]) ) $read[]= $pipes[1];
                if( !feof($pipes[2]) ) $read[]= $pipes[2];

                if (!$read) break;

                $ready = stream_select($read, $write=NULL, $ex= NULL, 2);

                if ($ready === false) {
                    break; #should never happen - something died
                }

                foreach ($read as $r) {
                    $s = fread($r, 1024);
                    $data .= $s;
                }
            }

            fclose($pipes[1]);
            fclose($pipes[2]);

            proc_close($process);
        }

        return $data;
    }


    function delete_files() {
        $rmcommand = 'rm '.$this->_tmpfile.' '.$this->_convertedfile.' '.$this->_thumb.' '.$this->_convertedfile.'.flv';
        $this->execute_command($rmcommand);
    }

    /**
     * Gets the $remote_file from the remote host and stores it as the $local_file in the local
     * host.
     * This is a tricky thing to avoid bugs in libssh2 library on different operating systems.
     * @param string $remote_file absolute path of the file in the remote host.
     * @param string $local_file absolute path of the file in the local host.
     * @return boolean true if the file exists and the file has a certain size.
     * false otherwise.
     */
    function get_remote_file($remote_file, $local_file) {
        //first try: via ssh2_scp_recv
        $scp_success = @ssh2_scp_recv($this->_cnx, $remote_file, $local_file);
        $filesize = filesize($local_file);
        $file_crated = file_exists($local_file) && $filesize !== false && $filesize > 0;
        // there are bugs in libssh2... trying other way via sftp
        if (!$scp_success || !$file_created) {

            if (is_null($this->_sftp)) {
                $this->_sftp = @ssh2_sftp($this->_cnx);
            }
            if (!$this->_sftp) {
                $this->delete_files();
                print_error('errorffmpegrecieving', 'block_myvideos');
            }
            $this->read_remote_file($remote_file, $local_file);
        }
        $filesize = filesize($local_file);
        return file_exists($local_file) && $filesize !== false && $filesize > 0;
    }

    /**
     * Reads from the the remote host the specified $remote_file and stores it as $local_file.
     * @param string $remote_file absolute path of the file in the remote host.
     * @param string $local_file absolute path of the file to be stored in the local host.
     */
    function read_remote_file($remote_file, $local_file) {
        $sftp = $this->_sftp;

        //in order to avoid load in memory, we will read from and write to each part of the file.
        $rstream = @fopen("ssh2.sftp://$sftp$remote_file", 'r');
        $lstream = @fopen($local_file, 'w');

        if (! $rstream || !$lstream) {
            $this->delete_files();
            print_error('errorffmpegrecieving', 'block_myvideos');
            return;
        }

        $len = $this->get_remote_file_size($remote_file);
        $read = 0;

        while ($read < $len && ($buf = @fread($rstream, $len - $read))) {
            $buflen = strlen($buf);
            $read += $buflen;
            @fwrite($lstream, $buf, $buflen);
        }

        @fclose($rstream);
        @fclose($lstream);
    }

    /**
     * Calculates the file size of a remote file via sftp.
     * @param string $file absolute file path on the remote server
     * @return int size in bytes of the remote file
     */
    function get_remote_file_size($file){
        $sftp = $this->_sftp;
        return filesize("ssh2.sftp://$sftp$file");
    }

}

?>