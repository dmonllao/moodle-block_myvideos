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
require_once($CFG->dirroot.'/blocks/myvideos/lib/lib.php');
require_once($CFG->dirroot.'/blocks/myvideos/lib/myvideos_searchvideos_class.php');

$action = optional_param('action', 'videos', PARAM_ALPHA);
$videoid = optional_param('videoid', false, PARAM_INT);
$viewlink = null;

if ($videoid) {
    if (!$videodata = get_record('myvideos_video', 'id', $videoid)) {
        print_error('errorwrongvideoid', 'block_myvideos');
    }
    $viewlink = $CFG->wwwroot.'/blocks/myvideos/view.php';
}


// Header
if ($action != 'embed') {
    $title = get_string('title', 'block_myvideos');
    
    $navlinks = array();
    $navlinks[] = array('name' => $title, 'link' => $viewlink, 'type' => 'block');
    if ($videoid) {
        $navlinks[] = array('name' => $videodata->title, 'link' => null, 'type' => 'block');
    }
    
    $navigation = build_navigation($navlinks);
    
    $CFG->stylesheets[] = $CFG->wwwroot.'/blocks/myvideos/myvideos.css';
    print_header($SITE->fullname, $SITE->fullname, $navigation);
    
    if ($videoid) {
        print_heading($videodata->title);
    } else {
        print_heading($title);
    }
    
    print_spacer(20);


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
        
    default:
        break;
}


if ($action != 'embed' && $action != 'getembedcode') {
    print_footer();
}

?>