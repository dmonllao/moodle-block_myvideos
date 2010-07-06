<?php // $Id: tabs.php,v 1.2 2010/07/06 08:11:13 davmon Exp $


$tabsarray = array();

// Tmp tab
if ($currenttab == 'video') {
    $tabsarray[] = 'video';
}

$tabsarray[] = 'videos';
if (has_capability('block/myvideos:uploadvideo', $context)) {
    $tabsarray[] = 'uploadvideo';
}
if (has_capability('block/myvideos:linkvideo', $context)) {
    $tabsarray[] = 'linkvideo';
}
$tabsarray[] = 'searchvideos';


$row = array();
foreach ($tabsarray as $tabname) {
    
    $url = $CFG->wwwroot.'/blocks/myvideos/index.php?action='.$tabname.'&amp;courseid='.$courseid;
    if (!empty($returnmod)) {
        $url .= '&amp;returnmod='.$returnmod;
    }
    
    $row[] = new tabobject($tabname, $url, get_string('title'.$tabname, 'block_myvideos'));
}

print_tabs(array($row), $currenttab);

?>