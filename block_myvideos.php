<?php  // $Id: block_myvideos.php,v 1.1 2010/07/04 21:51:31 arborrow Exp $


class block_myvideos extends block_list {

    function init() {
        $this->title = get_string('title', 'block_myvideos');
        $this->version = 2010020300;
    }

    function get_content() {
        
        global $CFG, $USER, $COURSE;
        
        $this->content = new object();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';
        
        if ($USER->id == 0) {
            return $this->content;
        } 
        
        $context = get_context_instance(CONTEXT_COURSE, $this->instance->pageid);
        
        if (has_capability('block/myvideos:manage', $context)) {
            $this->content->items[] = '<a href="'.$CFG->wwwroot.'/blocks/myvideos/index.php?courseid='.$COURSE->id.'">'.get_string('title', 'block_myvideos').'</a>';
            $this->content->icons[] = '<img src="'.$CFG->wwwroot.'/blocks/myvideos/pix/icon.gif" class="icon">';
        }
         
        $this->content->footer = '';

        return $this->content;
    }
    
    
    function applicable_formats() {
        return array('all' => true);
    }
}

?>
