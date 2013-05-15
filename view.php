<?php // $Id: view.php,v 1.1 2010/07/04 21:51:30 arborrow Exp $

/**
 * myvideos public video
 *
 * This page should be accessible without login into Moodle
 *
 * @package      blocks/myvideos
 * @copyright    2010 David Monllao <david.monllao@urv.cat>
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt
 */


require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/myvideos/locallib.php');
require_once($CFG->dirroot.'/blocks/myvideos/lib/myvideos_searchvideos_class.php');

$action = optional_param('action', 'videos', PARAM_ALPHA);
$videoid = optional_param('videoid', false, PARAM_INT);
$viewlink = null;

if ($videoid) {
    if (!$videodata = $DB->get_record('myvideos_video', array('id' => $videoid))) {
        print_error('errorwrongvideoid', 'block_myvideos');
    }
    $viewlink = $CFG->wwwroot.'/blocks/myvideos/view.php';
}

$url = $CFG->wwwroot . '/blocks/view.php?action=' . $action;
if ($videoid) {
    $url .= '&videoid=' . $videoid;
}

// Header
$title = get_string('title', 'block_myvideos');
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_heading($title);
$PAGE->set_title($title);

$navlinks = array();
$navlinks[] = array('name' => $title, 'link' => $viewlink, 'type' => 'block');
if ($videoid) {
    $navlinks[] = array('name' => $videodata->title, 'link' => null, 'type' => 'block');
}

// If it's an embedded video don't display header
if ($action != 'embed' && $action != 'getembedcode') {

    echo $OUTPUT->header();

    if ($videoid) {
        echo $OUTPUT->heading($videodata->title);
    } else {
        echo $OUTPUT->heading($title);
    }

    echo $OUTPUT->spacer(array('height' => 20));


    // Search div
    echo '<div id="myvideos_public_searchbox">';

    echo '</div>';
}

switch ($action) {

    case 'view':

        // Video div
        echo '<div id="myvideos_public_videobox">';

        echo '<div style="text-align:center;">';
        myvideos_show_video($videodata);
        //myvideos_show_comments($videodata);
        echo '</div>';

        echo '</div>';

        break;

    case 'videos':

        $viewurl = $CFG->wwwroot.'/blocks/myvideos/view.php';
        $search = new myvideos_searchvideos_class($viewurl);

        $search->process_data();
        $search->display(2);

        break;

    case 'embed':
        
        myvideos_show_video($videodata, false, true);
        break;
        
    case 'getembedcode':

        $url = $CFG->wwwroot.'/blocks/myvideos/view.php?action=embed&videoid='.$videoid;
        $code = '<iframe src="'.$url.'" width="440" height="357" frameborder="0">'.chr(13).chr(10);
        $code.= 'No frames support'.chr(13).chr(10);
        $code.= '</iframe>';

        echo '<div style="text-align: center;"><textarea rows="8" cols="50">'.$code.'</textarea></div>';
        break;

    default:
        break;
}

if ($action != 'embed' && $action != 'getembedcode') {
    echo $OUTPUT->footer();
}
