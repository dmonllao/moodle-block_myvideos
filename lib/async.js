// $Id: async.js,v 1.2 2010/09/09 09:56:14 davmon Exp $

var videoid = 0;
var callbackResult = false;

/**
 * Adds a video to user favorite videos
 * 
 * @param     integer    courseid
 * @param     integer    resultid
 * @return    boolean
 */
function myvideos_add_to_favorites(courseid, resultid, sesskey) {
    
    // Script scope
    videoid = resultid;

    var capa=document.getElementById("id_result_added_"+videoid);
    capa.innerHTML = '<img src="pix/loader.gif" class="icon">';
    
    // Responses manager
    var callbackHandler = 
    {
          success: process_add_to_favorites,
          failure: failure_add_to_favorites,
          timeout: 50000
    };
    
    paramstr = "courseid="+courseid+"&action=addtofavorites&videoid="+videoid+"&sesskey="+sesskey;
    YAHOO.util.Connect.asyncRequest("POST", "rest.php", callbackHandler, paramstr);
    
    return callbackResult;
}


function process_add_to_favorites(transaction) {
    
    var selectedvideo = document.getElementById("id_result_added_" + videoid);

    selectedvideo.style.fontWeight = "bold";
    selectedvideo.style.color = "green";
    selectedvideo.innerHTML = transaction.responseText;
    callbackResult = false;
}
function failure_add_to_favorites() {    callbackResult = false;}


////////////////////////////////////////////////////////////////////////


/**
 * Updates video player with selected video
 * 
 * @param     integer    courseid
 * @param     integer    selected
 * @param     boolean    favorite
 * @return    boolean
 */
function myvideos_preview_video(courseid, selected, favorite) {
    
    // Script scope
    videoid = selected;

    // Responses manager
    var callbackHandler = 
    {
          success: process_preview_video,
          failure: failure_preview_video,
          timeout: 50000
    };
    
    paramstr = "courseid="+courseid+"&action=previewvideo&videoid="+videoid+"&favorite="+favorite;
    YAHOO.util.Connect.asyncRequest("POST", "rest.php", callbackHandler, paramstr);
    
    return callbackResult;
}


function process_preview_video(transaction) {
    
    var selectedvideo = document.getElementById("id_player");

    selectedvideo.innerHTML = transaction.responseText;
    callbackResult = false;
}
function failure_preview_video() {       callbackResult = false;}


////////////////////////////////////////////////////////////////////////////


/**
 * Video comment to DB
 * 
 * @return void
 */
function myvideos_add_comment() {
 
    if (validate_myvideos_video_form() == true) {

        // Unique elements
        videoid = document.getElementsByName("id")[0].value;
        comment = document.getElementById("id_comment").value;
        sesskey = document.getElementsByName("sesskey")[0].value;

        // Responses manager
        var callbackHandler = 
        {
              success: process_add_comment,
              failure: failure_add_comment,
              timeout: 50000
        };
        
        paramstr = "action=addcomment&videoid="+videoid+"&comment="+comment+"&sesskey="+sesskey;
        YAHOO.util.Connect.asyncRequest("POST", "rest.php", callbackHandler, paramstr);
        
    }

    return callbackResult;
}


function process_add_comment(transaction) {
    
    // Notify success
    commentsdiv = document.getElementById("myvideos_id_comment_form");
    commentsdiv.innerHTML = "<br/>" + myvideos_comment_added_text;   // Submit element id = id_submitbutton

    // New child
    child = document.createElement('div');
    child.innerHTML = transaction.responseText;
    
    // Mark style
    child.firstChild.style.borderStyle = "solid";
    child.firstChild.style.borderWidth = "2px";
    child.firstChild.style.borderColor = "green";
    
    // Append child to id_comments node
    parent = document.getElementById("myvideos_id_comments");
    
    // It's the latest comment (list ordered by timeadded desc) 
    if (firstnode = parent.firstChild) {
        parent.insertBefore(child, firstnode);
    } else {
        parent.appendChild(child);
    }
    
    
    callbackResult = false;
}
function failure_add_comment() {         callbackResult = false;}


////////////////////////////////////////////////////////////////////////////


function myvideos_filter_videos() {

    // Responses manager
    var callbackHandler = 
    {
          success: process_filter_videos,
          failure: failure_filter_videos,
          timeout: 50000
    };
    
    // Button disabled until search results load ends
    document.getElementById("id_submitbutton").disabled = true;
    
    var keywords = document.getElementById("id_keywords").value;
        
    paramstr = "&action=filtervideos&keywords=" + keywords;
    YAHOO.util.Connect.asyncRequest("POST", "rest.php", callbackHandler, paramstr);
    
    return callbackResult;
}


function process_filter_videos(transaction) {
    
    
    var columnkey;
    var elementid;
    
    var searchresults = false;
    if (transaction.responseText != '') {
        searchresults = eval("(" + transaction.responseText + ")");
    }

    // Iterates through the column videos and hides or displays the diferent videos
    var columnsids = new Array('uservideoslabel', 'favoritevideoslabel');
    for (var columnid = 0; columnid < columnsids.length; columnid++) {
        
        columnkey = columnsids[columnid];
        
        if (uservideos = document.getElementById(columnkey)) {
            for (var i = 0; i < uservideos.childNodes.length; i++) {
                
                if (!uservideos.childNodes[i].id) {
                    continue;
                }
                
                elementid = uservideos.childNodes[i].id;
                element = document.getElementById(elementid);

                // Display the element
                if (searchresults && searchresults[elementid]) {
                    element.style.visibility = 'visible';
                    element.style.display = 'block';

                // Hide the element
                } else {
                    element.style.visibility = 'hidden';
                    element.style.display = 'none';
                }
            }
        }
    }
    
    document.getElementById("id_submitbutton").disabled = false;
    
}
function failure_filter_videos() { callbackResult = false;}
