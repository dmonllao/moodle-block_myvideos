YUI.add('moodle-block_myvideos-searchvideos', function(Y) {

    var SEARCHVIDEOSNAME = 'block_myvideos-searchvideos';

    var SEARCHVIDEOS = function() {
        SEARCHVIDEOS.superclass.constructor.apply(this, arguments);
    };

    Y.extend(SEARCHVIDEOS, Y.Base, {

        /**
         * Assigns the onclick event to the button
         */
        initializer : function (params) {

            var element = Y.one('#id_result_added_' + this.get('videoid'));

            element.on('click', function (e) {
                this.add_to_favorites();
                e.preventDefault();
            });
        },

        /**
         * Adds a video to user favorite videos
         *
         * @param     integer    courseid
         * @param     integer    videoid
         * @return    boolean
         */
        add_to_favorites : function (Y, courseid, videoid) {

            var loaderpath = M.cfg.wwwroot + "/blocks/myvideos/pix/loader.gif";
            Y.one("#id_result_added_" + this.get('videoid')).setContent('<img src="' + loaderpath + '" class="icon">');

            // Petition to the server
            Y.io(M.cfg.wwwroot+'/blocks/myvideos/rest.php', {
                method: 'POST',
                timeout: 5000,
                data: "courseid="+this.get('courseid')+"&action=addtofavorites&videoid="+this.get('videoid')+"&sesskey="+M.cfg.sesskey,
                on: {
                    complete: function (transactionid, response, arguments) {

                        // Notify success
                        videoid = arguments[0];
                        var selectedvideo = Y.one("#id_result_added_" + videoid);

                        selectedvideo.setStyle('fontWeight', 'bold');
                        selectedvideo.setStyle('color', 'green');
                        selectedvideo.setContent(response.responseText);
                    }
                },
                arguments: [this.get('videoid')]
            });

        }

    }, {
        NAME: SEARCHVIDEOSNAME,
        ATTRS : {
            courseid : {
                value : 0
            },
            videoid : {
                value : 0
            }
        }
    });

    M.block_myvideos = M.block_myvideos || {};
    M.block_myvideos.init_add_to_favorites = function(params) {
        return new SEARCHVIDEOS(params);
    }

}, '@VERSION@', {
    requires:['base', 'io']
});



