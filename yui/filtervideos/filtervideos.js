YUI.add('moodle-block_myvideos-filtervideos', function(Y) {

    var FILTERVIDEOSNAME = 'block_myvideos-filtervideos';

    var FILTERVIDEOS = function() {
        FILTERVIDEOS.superclass.constructor.apply(this, arguments);
    };

    Y.extend(FILTERVIDEOS, Y.Base, {

        /**
         * Assigns an event to the filter videos submit button
         */
        initializer : function(params) {

            M.block_myvideos.Y = Y;

            var element = Y.one('#id_submitbutton');
            element.on('click', function (e) {
                this.filter_videos(M.block_myvideos.Y);
                e.preventDefault();
            });
        },

        /**
         * Gets the videos filtering by keywords
         */
        filter_videos : function (Y) {

            document.getElementById("id_submitbutton").disabled = true;

            var keywords = document.getElementById("id_keywords").value;

            Y.io(M.cfg.wwwroot + '/blocks/myvideos/rest.php', {
                method: 'POST',
                timeout: 5000,
                data: '&action=filtervideos&keywords=' + keywords,
                on: {
                    complete: function (transactionid, response, arguments) {

                        var columnkey;
                        var elementid;

                        var searchresults = false;
                        if (response.responseText != '') {
                            searchresults = eval("(" + response.responseText + ")");
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
                }
            });

        }

    }, {
        NAME: FILTERVIDEOSNAME,
        ATTRS : {}
    });

    M.block_myvideos = M.block_myvideos || {};
    M.block_myvideos.init_filter_videos = function(params) {
        return new FILTERVIDEOS(params);
    }

}, '@VERSION@', {
    requires:['base', 'io']
});

