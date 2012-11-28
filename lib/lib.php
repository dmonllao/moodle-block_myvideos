<?php // $Id: lib.php,v 1.4 2010/11/10 08:31:54 davmon Exp $


/**
 * Displays the Moodle flv player
 * 
 * @param      object       $videodata
 * @param      boolean      $preview         It's a video preview? (search results)
 * @param      boolean      $staticsize      Display the video with a limited size?
 * @param      mixed        $cmid            If the video it's viewed from a module instance we must check permissions
 * @return     void
 */
function myvideos_show_video($videodata, $preview=false, $limitedsize=false, $cmid=false) {
    
    global $CFG;
    
    // We must avoid $videodata pointer
    $video = clone $videodata;
    
    if (!$video) {
        return false;
    }
    
    // Static sizes
    $maxwidth = '1024';
    $maxheight = '768';
    $limitedmaxwidth = '410';
    $limitedmaxheight = '340';
    $previewstaticwidth = '230';
    $previewstaticheight = '180';
    
    // Ensure that the video size is higher than the max max max static size
    if ($video->width > $maxwidth) {
        $video->width = $maxwidth;
    }
    if ($video->height > $maxheight) {
        $video->height = $maxheight;
    }
    
    // In case we must limite the video size
    if ($limitedsize) {

        // Uploaded video
        if ($video->link == 0) {
            
            if (intval($video->width) > intval($limitedmaxwidth) || 
                intval($video->height) > intval($limitedmaxheight)) {
                            
                    // Uploaded videos
                    $video->width = $limitedmaxwidth;
                    $video->height = $limitedmaxheight;
            }
            
            
        // Linked video
        } else {
            
            if (intval($video->width) > intval($limitedmaxwidth) || 
                intval($video->height) > intval($limitedmaxheight)) {
                        
                    // Google video uses styles to set the video size
                    $video->video = preg_replace('/width:'.$video->width.'px/', 'width:'.$limitedmaxwidth.'px', $video->video);
                    $video->video = preg_replace('/height:'.$video->height.'px/', 'height:'.$limitedmaxheight.'px', $video->video);
            }
            
        }
        
    }
    
    
    // If it's a preview video we should change styles and resize <object> if it's a link
    if ($preview) {
        
        $divclass = 'myvideos_result_video';
        
        if ($video->link == '0') {    
            $video->width = $previewstaticwidth;
            $video->height = $previewstaticheight;
            
        } else {
            $video->video = str_replace('width:'.$video->width.'px', 'width:'.$previewstaticwidth.'px', $video->video);
            $video->video = str_replace('height:'.$video->height.'px', 'height:'.$previewstaticheight.'px', $video->video);
            
        }
        
    } else {
        $divclass = 'myvideos_videoplayer';
    }
    
    echo '<div class="'.$divclass.' resourcecontent resourceflv">';
    
    // File linked from youtube, vimeo...
    if ($video->link == '1') {
        
        // Formatting output
        $options->noclean = true;
        echo format_text($video->video, FORMAT_HTML, $options);

    // Uploaded file
    } else {
        
        $swfpath = $CFG->wwwroot.'/filter/mediaplugin/flvplayer.swf';
        
        $fileurl = $CFG->wwwroot.'/blocks/myvideos/getfile.php?videoid='.$video->id;
        
        // Add the course_module to check the private video access
        if ($cmid) {
            $fileurl .= '&cmid='.$cmid;
        }
        
        // ext param added to avoid the flv player detection of the video extension
        $fileurl .= '&ext=.flv';
        $fileurl = urlencode($fileurl);
        
        echo '<object id="id_flvplayer" style="width:'.$video->width.'px;height:'.$video->height.'px;">';
        echo '<param name="flashvars" value="file='.$fileurl.'" />';
        echo '<param name="src" value="'.$swfpath.'" />';
        echo '<param name="movie" value="'.$swfpath.'" />';
        echo '<param name="quality" value="high" />';
        echo '<param name="allowfullscreen" value="true" />';
        
        $flashvars = 'file='.$fileurl.'&amp;quality=high';
        
        if (!$preview) {
            //echo '<param name="autoplay" value="true" />';
            //echo '<param name="autostart" value="true" />';
            //$flashvars .= '&amp;autoplay=true&amp;autostart=true';
        }
        
        echo '<embed src="'.$swfpath.'" 
               style="width:'.$video->width.'px;height:'.$video->height.'px;"
               flashvars="'.$flashvars.'" 
               allowfullscreen="true" 
             />';
        
        echo '</object>';
    }
    
    echo '</div>';    
}


/**
 * Displays the link to the video actions
 * 
 * @param   object     $videodata     Object with the video data
 * @param   integer    $courseid      Course identifier
 */
function myvideos_show_video_actions($videodata, $courseid) {
    
    global $CFG, $USER;
    
    $actions = array();
    
    // Actions
    $actions[] = '<a href="'.$CFG->wwwroot.'/blocks/myvideos/index.php?courseid='.$courseid.'&amp;action=viewvideo&amp;id='.$videodata->id.'">'.get_string("view").'</a>';
    
    if ($videodata->favorite) {
        $actions[] = '<a href="'.$CFG->wwwroot.'/blocks/myvideos/index.php?courseid='.$courseid.'&amp;action=deletefavoritevideo&amp;id='.$videodata->id.'">'.get_string("delete").'</a>';

    } else {
        $actions[] = '<a href="'.$CFG->wwwroot.'/blocks/myvideos/index.php?courseid='.$courseid.'&amp;action=editvideo&amp;id='.$videodata->id.'">'.get_string("edit").'</a>';
        $actions[] = '<a href="'.$CFG->wwwroot.'/blocks/myvideos/index.php?courseid='.$courseid.'&amp;action=deletevideo&amp;id='.$videodata->id.'">'.get_string("delete").'</a>';
    }
    
    // Only the uploader can download the encoded video
    if ($USER->id == $videodata->userid || $videodata->public == 2) {
        $actions[] = '<a href="'.$CFG->wwwroot.'/blocks/myvideos/getfile.php?videoid='.$videodata->id.'">'.get_string("download", "block_myvideos").'</a>';
        
        $actions[] = '<a href="#" onclick="return myvideos_display_embed_code();">'.get_string("getembedcode", "block_myvideos").'</a>';
    }
    
    
    echo '<div>';
    foreach ($actions as $action) {
        echo '<span class="myvideos_action">'.$action.'</span>';
    }
    echo '</div>';
    
    // Embed
    $url = $CFG->wwwroot.'/blocks/myvideos/view.php?action=embed&videoid='.$videodata->id;
    $code = '<iframe src="'.$url.'" width="440" height="357" frameborder="0">'.chr(13).chr(10);
    $code.= 'No frames support'.chr(13).chr(10);
    $code.= '</iframe>';
    echo '<div id="myvideos_embed" ><br/><textarea rows="8" cols="50">'.$code.'</textarea></div>';
    
    // Filters
    require_once($CFG->dirroot.'/blocks/myvideos/forms/myvideos_filtervideos_form.php');
    $searchform = new myvideos_filtervideos_form();
    
    echo '<div>';
    $searchform->display();
    echo '</div>';
}


/**
 * Adds +1 to the video visualization counter
 * 
 * Not "DISTINCT" user
 * 
 * @param     object      $videodata
 * @return    void
 */
function myvideos_add_view($videodata) {

    // Just to avoid possible reference problems...
    $tmpobj->id = $videodata->id;
    $tmpobj->views = intval($videodata->views) + 1;
    
    update_record('myvideos_video', $tmpobj);
}


/**
 * Displays the list of comments of a video
 * 
 * @param     object      $videodata
 * @return    void
 */
function myvideos_show_comments($videodata) {
    
    
    $comments = get_records('myvideos_video_comment', 'videoid', $videodata->id, "timeadded DESC");
    
    echo '<div id="myvideos_id_comments">';
        
    if ($comments) {        
        foreach ($comments as $comment) {
            
            echo '<div id="id_comment_'.$comment->id.'" class="myvideos_comment">';
            myvideos_show_comment($comment);
            echo '</div>';
        }
    }
    
    echo '</div>';
}


/**
 * Displays a video comment
 * 
 * @param     object      
 * @return    void
 */
function myvideos_show_comment($comment) {
    
    global $CFG;
    
    echo '<table align="center" cellspacing="0" class="forumpost">';

    // Picture
    $userpicture = get_record_sql("SELECT id, firstname, lastname, imagealt, picture 
                                   FROM {$CFG->prefix}user 
                                   WHERE id = '$comment->userid'");

    echo '<tr class="header"><td class="picture left">';
    print_user_picture($userpicture, 1);
    echo '</td>';
    

    echo '<td class="topic starter">';

    //echo '<div class="subject">'.format_string($usernew->title).'</div>';
    echo '<div class="subject"></div>';
    
    echo '<div class="author">';
    $fullname = fullname($userpicture);
    $by = new object();
    $by->name = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$comment->userid.'&amp;course=1">'.$fullname.'</a>';
    $by->date = userdate($comment->timeadded);
    print_string('bynameondate', 'forum', $by);
    
    echo '</div></td></tr>';

    echo '<tr><td class="left side">';
    echo '&nbsp;';
    echo '</td>';
    
    echo '<td class="content">'."\n";
    echo format_text($comment->text, FORMAT_PLAIN);
    
    echo '</td></tr></table>'."\n\n";
    
}


/**
 * Returns the div to display after submit a video file
 * 
 * @return string
 */
function myvideos_uploadform_loader() {
    
    return '<div id="id_loader" style="text-align:center;display:none;"><br/>
           <img src="pix/loader.gif" style="text-align:center;"/><br/>
           '.get_string("loading", "block_myvideos").'
           </div>';
}

?>
