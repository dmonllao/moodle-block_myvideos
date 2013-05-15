<?php // $Id: index.php,v 1.1 2010/07/04 21:51:31 arborrow Exp $

/**
 * myvideos block main controller
 *
 * @package      blocks/myvideos
 * @copyright    2010 David Monllao <david.monllao@urv.cat>
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt
 */


require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/blocks/myvideos/locallib.php');

$courseid = optional_param('courseid', 1, PARAM_INT);
$action = optional_param('action', 'videos', PARAM_ALPHA);
$returnmod = optional_param('returnmod', 0, PARAM_BOOL);    // If it was requested from myvideos module

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('errorcourseid', 'block_myvideos');
}

// Can manage myvideos block?
$context = context_course::instance($course->id);
require_login($course->id);
require_capability('block/myvideos:manage', $context);

$title = get_string('title'.$action, 'block_myvideos');
$PAGE->set_url($CFG->wwwroot . '/blocks/index.php?courseid=' . $course->id . '&action=' . $action);
$PAGE->set_context($context);
$PAGE->set_heading($title);
$PAGE->set_title($title);
echo $OUTPUT->header();

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

echo $OUTPUT->footer();
