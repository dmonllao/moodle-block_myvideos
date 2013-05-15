YUI.add('moodle-block_myvideos-videos', function(Y) {

    var VIDEOSNAME = 'block_myvideos-videos';

    var VIDEOS = function() {
        VIDEOS.superclass.constructor.apply(this, arguments);
    };

    Y.extend(VIDEOS, Y.Base, {

        /**
         * Assigns the async call to the video element to load the selected video into the main div
         * @return boolean False on onclick to prevent the form of being sent
         */
        initializer : function (Y, courseid, selected, favorite) {

            var element = Y.one('#id_video_preview_' + this.get('selected'));

            element.on("click", function() {
                this.preview_video(Y, this.get('courseid'), this.get('selected'), this.get('favorite'));
                return false;
            });
        },

        /**
         * Updates video player with selected video
         *
         * @param     integer    courseid
         * @param     integer    selected
         * @param     boolean    favorite
         * @return    boolean
         */
        preview_video : function (Y, courseid, videoid, favorite) {

            // Petition to the server
            Y.io(M.cfg.wwwroot+'/blocks/myvideos/rest.php', {
                method: 'POST',
                timeout: 5000,
                data: "courseid="+courseid+"&action=previewvideo&videoid="+videoid+"&favorite="+favorite,
                on: {
                    complete: function (transactionid, response, arguments) {
                        Y.one("#id_player").setContent(response.responseText);
                    }
                }
            });
        }

    }, {
        NAME: VIDEOSNAME,
        ATTRS : {
            courseid : {
                value : false
            },
            selected : {
                value : false
            },
            favorite : {
                value : false
            }
        }
    });

    M.block_myvideos = M.block_myvideos || {};
    M.block_myvideos.init_preview_video = function(params) {
        return new VIDEOS(params);
    }

}, '@VERSION@', {
    requires:['base', 'io']
});
