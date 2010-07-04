<?php // $Id: rest.php,v 1.1 2010/07/04 21:51:32 arborrow Exp $

require_once('../../config.php');
require_once('lib/lib.php');

$courseid = optional_param('courseid', 1, PARAM_INT);
$videoid = optional_param('videoid', false, PARAM_INT);
$action = optional_param('action', false, PARAM_ALPHA);

require_login($courseid);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die(); 
}

switch ($action) {

    case 'addtofavorites':
        
        // CSRF
        if (!confirm_sesskey()) {
            die();
        }
        
        if (!has_capability('block/myvideos:favoritevideo', get_context_instance(CONTEXT_COURSE, $courseid))) {
            die();
        }
        
        $videodata = get_record('myvideos_video', 'id', $videoid);
        if (!$videodata) {
            echo get_string('cantbeadded', 'block_myvideos');
            die();
        }
        
        // We must ensure that the video isn't private
        if ($videodata->publiclevel == 0) {
            echo get_string('cantbeadded', 'block_myvideos');
            die();
        }
        
        $favorite->userid = $USER->id;
        $favorite->videoid = $videoid;
        $favorite->timeadded = time();
        if (!insert_record('myvideos_video_favorite', $favorite)) {
            echo get_string('cantbeadded', 'block_myvideos');
            die();
        }
        
        echo strtolower(get_string('videoadded', 'block_myvideos').'!');
        
        break;
        
        
    case 'previewvideo':
                
        $videodata = get_record('myvideos_video', 'id', $videoid);
        if (!$videodata) {
            echo get_string('cantbeadded', 'block_myvideos');
            die();
        }
        
        // Checking video public level
        if ($videodata->publiclevel == 0 && $videodata->userid != $USER->id) {
            echo 'ACCESS DENIED';
            die();
        }
        
        // Better to send a favorite video param than check if $USER is the video uploader
        $videodata->favorite = optional_param('favorite', 0, PARAM_INT);
        
        myvideos_show_video($videodata, false, true);
        myvideos_show_video_actions($videodata, $courseid);
        
        // +1 visualizations
        myvideos_add_view($videodata);
        
        break;
        
        
    case 'addcomment':

        // CSRF
        if (!confirm_sesskey()) {
            die();
        }
        
        $videodata = get_record("myvideos_video", "id", $videoid);
        
        if (empty($videodata->allowcomments)) {
            die();
        }
        
        $commentdata->videoid = $videoid;
        $commentdata->userid = $USER->id;
        $commentdata->text = optional_param('comment', '', PARAM_TEXT);
        $commentdata->timeadded = time();
        if (!$commentdata->id = insert_record('myvideos_video_comment', $commentdata)) {
            die();
        }
        
        myvideos_show_comment(stripslashes_recursive($commentdata));
        
        break;
        
    default:
        
        //echo 'done';
        break;
        
}

?>
