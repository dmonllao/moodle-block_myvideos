YUI.add('moodle-block_myvideos-video', function(Y) {

    var VIDEONAME = 'block_myvideos-video';

    var VIDEO = function() {
        VIDEO.superclass.constructor.apply(this, arguments);
    };

    Y.extend(VIDEO, Y.Base, {

        /**
         * Assigns the async call to the submit button element
         * @return boolean False on onclick to prevent the form of being sent
         */
        initializer : function (params) {

            var element = Y.one('#id_submitbutton');
            if (element) {
                element.on("click", function(e) {

                    if (validate_myvideos_video_form()) {
                        this.add_comment();
                    }
                    e.preventDefault();
                });
            }
        },

        /**
         * Video comment to DB and returning feedback as HTML
         *
         * @return void
         */
        add_comment : function () {

            // Unique elements
            // TODO: Y.one
            videoid = document.getElementsByName("id")[0].value;
            comment = document.getElementById("id_comment").value;

            // Petition to the server
            Y.io(M.cfg.wwwroot+'/blocks/myvideos/rest.php', {
                method: 'POST',
                timeout: 5000,
                data:"action=addcomment&videoid="+videoid+"&comment="+comment+"&sesskey="+M.cfg.sesskey,
                on: {
                    complete: function (transactionid, response, arguments) {

                        // Cool styles

                        // Notify success
                        var commentsdiv = Y.one("#myvideos_id_comment_form");
                        // Submit element id = id_submitbutton
                        commentsdiv.setContent("<br/>" + myvideos_comment_added_text);

                        // New child
                        var child = Y.Node.create('<div>' + response.responseText + '</div>');

                        // Green style for the table
                        var tableNodes = child.all('.forumpost');
                        for (var i = 0; i < tableNodes.length; i++) {
                            tableNodes[i].setStyle('borderStyle', 'solid');
                            tableNodes[i].setStyle('borderWidth', '2px');
                            tableNodes[i].setStyle('borderColor', 'green');
                        }

                        // Append child to id_comments node
                        var parent = Y.one("#myvideos_id_comments");

                        // It's the latest comment (list ordered by timeadded desc)
                        if (firstnode = parent.firstChild) {
                            parent.insertBefore(child, firstnode);
                        } else {
                            parent.appendChild(child);
                        }

                    }
                },
                arguments: {
                    videoid : videoid
                }
            });

        }

    }, {
        NAME: VIDEONAME,
    });

    M.block_myvideos = M.block_myvideos || {};
    M.block_myvideos.init_add_comment = function(params) {
        return new VIDEO(params);
    }

}, '@VERSION@', {
    requires:['base', 'io']
});


