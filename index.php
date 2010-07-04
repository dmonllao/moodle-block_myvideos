<?php // $Id: index.php,v 1.1 2010/07/04 21:51:31 arborrow Exp $

/**
 * myvideos block main controller
 * 
 * @package      blocks/myvideos
 * @copyright    2010 David Monllao <david.monllao@urv.cat>
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt
 */


require_once('../../config.php');

require_once('./lib/lib.php');
require_js($CFG->wwwroot.'/blocks/myvideos/lib/lib.js');

$courseid = optional_param('courseid', 1, PARAM_INT);
$action = optional_param('action', 'videos', PARAM_ALPHA);
$returnmod = optional_param('returnmod', 0, PARAM_BOOL);    // If it was requested from myvideos module

if (!$course = get_record('course', 'id', $courseid)) {
    print_error('errorcourseid', 'block_myvideos');
}

// Can manage myvideos block?
$context = get_context_instance(CONTEXT_COURSE, $course->id);
require_capability('block/myvideos:manage', $context);


// Header
$straction = get_string('title'.$action, 'block_myvideos');
$navlinks = array();
$navlinks[] = array('name' => get_string('title', 'block_myvideos'), 'link' => null, 'type' => 'block');
$navlinks[] = array('name' => $straction, 'link' => null, 'type' => 'block');
$navigation = build_navigation($navlinks);

// add myvideos stylesheet
$CFG->stylesheets[] = $CFG->wwwroot.'/blocks/myvideos/myvideos.css';

print_header($SITE->fullname, "$SITE->fullname", $navigation);
print_heading(get_string('title'.$action, 'block_myvideos'));


$singlevideoactions = array('viewvideo', 'editvideo', 'deletevideo', 'deletefavoritevideo');
if (in_array($action, $singlevideoactions)) {
    $currenttab = 'video';
} else {
    $currenttab = $action;
}

// Block tabs
include('./tabs.php');


// Class instance
$classname = 'myvideos_'.$currenttab.'_class';
require_once('lib/'.$classname.'.php');
$instance = new $classname();

$instance->process_data();
$instance->display();

print_footer($course);

?>