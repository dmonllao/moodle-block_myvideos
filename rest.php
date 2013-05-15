<?php // $Id: rest.php,v 1.2 2010/09/09 09:56:14 davmon Exp $

require_once('../../config.php');
require_once($CFG->dirroot .'/blocks/myvideos/locallib.php');

$courseid = optional_param('courseid', 1, PARAM_INT);
$videoid = optional_param('videoid', false, PARAM_INT);
$action = optional_param('action', false, PARAM_ALPHA);
$keywords = optional_param('keywords', false, PARAM_TEXT);

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

        if (!has_capability('block/myvideos:favoritevideo', context_course::instance($courseid))) {
            die();
        }

        $videodata = $DB->get_record('myvideos_video', array('id' => $videoid));
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
        if (!$DB->insert_record('myvideos_video_favorite', $favorite)) {
            echo get_string('cantbeadded', 'block_myvideos');
            die();
        }

        echo strtolower(get_string('videoadded', 'block_myvideos').'!');

        break;


    case 'previewvideo':

        $videodata = $DB->get_record('myvideos_video', array('id' => $videoid));
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

        $videodata = $DB->get_record("myvideos_video", array("id" => $videoid));

        if (empty($videodata->allowcomments)) {
            die();
        }

        $commentdata->videoid = $videoid;
        $commentdata->userid = $USER->id;
        $commentdata->text = stripslashes(optional_param('comment', '', PARAM_TEXT));
        $commentdata->timeadded = time();
        if (!$commentdata->id = $DB->insert_record('myvideos_video_comment', $commentdata)) {
            die();
        }

        myvideos_show_comment($commentdata);

        break;

    case 'filtervideos':

        // Using myvideos_searchvideo class to reuse parsing code
        require_once($CFG->dirroot.'/blocks/myvideos/lib/myvideos_searchvideos_class.php');
        $searchvideo = new myvideos_searchvideos_class();

        // It returns an associative array with the results DOM ids
        $uservideossql = "SELECT mv.id, 'uservideoslabel' as videotype
                          FROM {$CFG->prefix}myvideos_video mv ";

        $favoritevideossql = "SELECT mv.id, 'favoritevideoslabel' as videotype
                              FROM {$CFG->prefix}myvideos_video mv
                              JOIN {$CFG->prefix}myvideos_video_favorite mvf ON mvf.videoid = mv.id ";


        // Common query
        $commonsql = "LEFT JOIN {$CFG->prefix}myvideos_video_tag mvt ON mvt.videoid = mv.id
                      LEFT JOIN {$CFG->prefix}myvideos_video_keyword mvk ON mvk.id = mvt.keywordid
                      WHERE (";

        // Video title search
        $commonsql .= $searchvideo->_where($keywords, 'title');

        // Video tags search
        if ($explodedkeywords = $searchvideo->_get_submitted_keywords($keywords)) {
            foreach ($explodedkeywords as $keyword) {

                //$keyword = clean_text($keyword, PARAM_TEXT);        // Already cleaned
                $commonsql .= " OR mvk.keyword = '$keyword'";
            }
        }
        $commonsql .= ")";

        $uservideossql = $uservideossql.$commonsql." AND mv.userid = '$USER->id' AND mv.timedeleted = '0'";
        $favoritevideossql = $favoritevideossql.$commonsql." AND mvf.userid = '$USER->id' AND mv.timedeleted = '0'";

        $results['user'] = $DB->get_records_sql($uservideossql);
        $results['favorite'] = $DB->get_records_sql($favoritevideossql);

        // Array to JSONize
        $jsonarray = array();

        $arraykeys = array('user', 'favorite');
        foreach ($arraykeys as $arraykey) {
            if (!empty($results[$arraykey])) {
                foreach ($results[$arraykey] as $result) {
                    $key = $result->videotype.'_'.$result->id;
                    $jsonarray[$key] = $key;
                }
            }
        }

        if (!empty($jsonarray)) {
            echo json_encode($jsonarray);
        }

        break;

    default:

        //echo 'done';
        break;

}
