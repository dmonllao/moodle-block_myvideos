// $Id: lib.js,v 1.1 2010/07/04 21:51:22 arborrow Exp $

/**
 * Displays the loader div after submitting a new file to convert
 * 
 * It stays shown during the upload form file uploading
 * 
 * @return void
 */
function myvideos_submit_upload() {
	
    if (validate_myvideos_uploadvideo_form() == true) {
        document.getElementById('id_loader').style.display = 'block'; 
    }
}


/**
 * Hides the loader div of myvideos block index
 * 
 * @return void
 */
function myvideos_hide_index_loader() {

    var loader = document.getElementById("loader");
    
    if (loader != null) {
        loader.style.visibility = "hidden";
        loader.style.overflow = "hidden";
	}
}


function myvideos_display_embed_code() {
    
    var embeddiv = document.getElementById("myvideos_embed");
    embeddiv.style.visibility = "visible";
    embeddiv.style.display = "inline";
    
    var maindiv = document.getElementById("myvideos");
    maindiv.style.height = "650px";
    
    return false;
}

/**
 * Adds an user video to myvideos module from myvideos block 
 * 
 * After submitting a file/object in myvideos block the block instance window
 * is closed and returns to the opener window, that function adds the new 
 * submitted video to the avaiable videos list
 * 
 * @param     integer    videoid
 * @param     string     videotitle
 * @param     string     uservideostr
 */
function myvideos_return_to_mod(videoid, videotitle, uservideostr) {

    // If there is no opener we should act like "no $returnmod" mode
    if (!window.opener) {
        return false
    }

    var videoidstr = videoid + "_user";
    var videotitlestr = videotitle + " (" + uservideostr + ")";
    var videoselect = window.opener.document.getElementById("id_video_videoid");

    //newoption = new Option(videotitlestr, videoidstr);
    var newoption = document.createElement('option');
    newoption.innerHTML = videotitlestr;
    newoption.text = videotitlestr;
    newoption.value = videoidstr;
    newoption.selected = true;

    // If this is the first user/favorite video we must take out the "novideos" disabled option
    if (videoselect.options[0]) {
        videoselect.disabled = false;
        videoselect.removeChild(videoselect.options[0]);
    }

    // TODO: solve IE problem
    videoselect.appendChild(newoption);
    
    // We close that block window
    window.close();
}

