<?php  // $Id: block_myvideos.php,v 1.1 2010/07/04 21:51:31 arborrow Exp $


class block_myvideos extends block_list {

    function init() {
        $this->title = get_string('pluginname', 'block_myvideos');
    }

    function get_content() {

        global $CFG, $USER, $COURSE, $OUTPUT;

        $this->content = new object();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if ($USER->id == 0) {
            return $this->content;
        }

        $context = context_course::instance($COURSE->id);

        if (has_capability('block/myvideos:manage', $context)) {

            $url = $CFG->wwwroot . '/blocks/myvideos/index.php?courseid='.$COURSE->id;
            $pixurl = $OUTPUT->pix_url('icon', 'block_myvideos');
            $this->content->items[] = html_writer::link($url,
                get_string('title', 'block_myvideos'));
            $this->content->icons[] = html_writer::empty_tag('img', array('src' => $pixurl,
               'class' => 'icon'));
        }

        $this->content->footer = '';

        return $this->content;
    }

    function applicable_formats() {
        return array('all' => true);
    }

    /**
     * Returns true if this block has global config.
     *
     * @return bool
     */
    public function has_config() {
        return true;
    }
}

