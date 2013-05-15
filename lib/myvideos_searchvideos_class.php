<?php // $Id: myvideos_searchvideos_class.php,v 1.2 2010/07/06 08:11:13 davmon Exp $

/**
 * Class to search other users videos
 *
 * @package      blocks/myvideos
 * @copyright    2010 David Monllao <david.monllao@urv.cat>
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt
 */

require_once($CFG->dirroot.'/blocks/myvideos/lib/myvideos_actionable.php');

/**
 * Class to search other users video
 *
 * Displays a list or avaiable videos, and allow users to preview the video
 */
class myvideos_searchvideos_class extends myvideos_actionable {

    var $_searchresults = null;

    /**
     * myvideos_searchvideos_class implementation of process_data()
     */
    function process_data() {

        global $CFG, $USER, $DB;

        // Data submitted previously
        if ($data = $this->_mform->get_data()) {

            if ($data->keywords != '') {

                $titlewhere = $this->_where($data->keywords, 'title');

                // If there are no valid keywords "no results"
                if (!$titlewhere) {
                    $this->_searchresults = false;
                    return false;
                }

                if (!empty($data->searchdescription)) {
                    $descriptionwhere = $this->_where($data->keywords, 'description');
                }

                $select = "SELECT mv.id, mv.video, mv.link, mv.userid, mv.publiclevel, mv.width, mv.height,
                           mv.title, mv.description, mv.author, mv.timedeleted ";

                // Not his/her own videos && not previoulsy added
                $sql = "FROM {$CFG->prefix}myvideos_video mv
                        LEFT JOIN {$CFG->prefix}myvideos_video_favorite mvf ON mvf.videoid = mv.id
                        LEFT JOIN {$CFG->prefix}myvideos_video_tag mvt ON mvt.videoid = mv.id
                        LEFT JOIN {$CFG->prefix}myvideos_video_keyword mvk ON mvk.id = mvt.keywordid
                        WHERE mv.timedeleted = 0 AND (mvf.userid != '$USER->id' OR mvf.userid IS NULL)";

                // Title search
                $sql .= " AND (".$titlewhere;

                // Video tags search
                if ($keywords = $this->_get_submitted_keywords($data->keywords)) {
                    foreach ($keywords as $keyword) {
                        $keyword = clean_text($keyword, PARAM_TEXT);
                        $sql .= " OR mvk.keyword = '$keyword'";
                    }
                }

                // Description search
                if (!empty($descriptionwhere)) {
                    $sql .= " OR ".$descriptionwhere;
                }

                $sql .= ")";

                $this->_searchresults = $DB->get_records_sql($select.$sql);
            }

        }

    }


    /**
     * myvideos_searchvideos_class implementation of display()
     *
     * Display the search form and the results if data was previously submitted
     *
     * @param   string    $publiclevel      Indicates the minimal publiclevel required
     */
    function display($publiclevel = 1) {

        global $CFG, $COURSE, $USER, $PAGE, $OUTPUT;

        parent::display();

        // Search results
        if (!empty($this->_searchresults)) {

            echo '<div id="myvideos_results">';

            foreach ($this->_searchresults as $key => $result) {

                // The video must be from another user and can't be private
                // Faster to delegate to PHP than leave it to DB indexs
                if ($result->userid != $USER->id && $result->publiclevel >= $publiclevel) {

                    // Look for empty fields
                    $fields = array('title', 'description', 'author');
                    foreach ($fields as $field) {
                        if ($result->$field == '') {
                            $result->$field = get_string("unknown", "block_myvideos");
                        }
                    }

                    // Main result div
                    echo '<div id="myvideos_result">';

                    myvideos_show_video($result, true);

                    // Video data div
                    echo '<div class="myvideos_result_videodata">';

                    echo '<div>'.get_string("videotitle", "block_myvideos").': '.$result->title.'</div>';
                    echo '<div>'.get_string("videodescription", "block_myvideos").': '.$result->description.'</div>';
                    echo '<div>'.get_string("author", "block_myvideos").': '.$result->author.'</div>';
                    echo '<div>'.get_string("videotags", "block_myvideos").': '.$this->_get_video_tags($result->id).'</div>';

                    if (has_capability('block/myvideos:favoritevideo', context_course::instance($this->_courseid))) {

                        $elementid = 'id_result_added_'.$result->id;
                        echo '<div id="'.$elementid.'"><a href="#">'.get_string("addtofavorites", "block_myvideos").'</a></div>';

                        $jsparams = array('courseid' => $this->_courseid, 'videoid' => $result->id);
                        $PAGE->requires->yui_module('moodle-block_myvideos-searchvideos', 'M.block_myvideos.init_add_to_favorites', array($jsparams), null, true);
                    }

                    if ($publiclevel == 2) {

                        // Link to play video at normal size
                        echo '<div><a href="'.$CFG->wwwroot.'/blocks/myvideos/view.php?action=view&amp;videoid='.$result->id.'">';
                        echo get_string("titleviewvideo", "block_myvideos").'</a></div>';

                        // Link to embed the video
                        $url = new moodle_url($CFG->wwwroot.'/blocks/myvideos/view.php?action=getembedcode&amp;videoid='.$result->id);
                        $popupaction = new popup_action('click', $url);
                        echo $OUTPUT->action_link($url, get_string("getembedcode", "block_myvideos"), $popupaction);
                    }

                    echo '</div>';

                    echo '</div>';

                // Delete from $this->_searchresults to show the "novideos" notify or not
                } else {
                    unset($this->_searchresults[$key]);
                }
            }

            echo '</div>';

            if (empty($this->_searchresults)) {
                $this->_searchresults = false;
            }
        }

        // If there was no videos a little bit of info
        if ($this->_searchresults === false) {
            echo '<div class="myvideos_box">'.get_string("noresults", "block_myvideos").'</div>';
        }
    }


    /**
     * Explodes keywords string and returns a query where piece
     *
     * @param        string     $keywordsstr       Text submitted
     * @param        string     $field             DB field where we must search
     * @return       mixed
     */
    function _where($keywordsstr, $field) {

        $keywords = explode(' ', $keywordsstr);

        // Separate keywords to locate more results
        $keywords = $this->_get_submitted_keywords($keywordsstr);

        $wheres = array();
        foreach ($keywords as $keyword) {
            if ($keyword != '') {
                $wheres[] = " mv.".$field." LIKE '%".$keyword."%' ";
            }
        }

        if (empty($wheres)) {
            return false;
        }

        $wheresql = '('.implode(' OR ', $wheres).')';

        return $wheresql;
    }

}
