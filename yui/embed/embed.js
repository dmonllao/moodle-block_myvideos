YUI.add('moodle-block_myvideos-embed', function(Y) {

    var EMBEDNAME = 'block_myvideos-embed';

    var EMBED = function() {
        EMBED.superclass.constructor.apply(this, arguments);
    };

    Y.extend(EMBED, Y.Base, {

        /**
         * When user clicks the embed button display the div
         */
        initializer : function (params) {

            var element = Y.one('#id_display_embed');

            element.on('click', function(e) {

                var embeddiv = Y.one('#myvideos_embed');
                embeddiv.setStyle('visibility', 'visible');
                embeddiv.setStyle('display', 'inline');

                var maindiv = Y.one('#myvideos');
                maindiv.setStyle('height', '650px');

                e.preventDefault();
            });

        }

    }, {
        NAME: EMBEDNAME,
        ATTRS : {}
    });

    M.block_myvideos = M.block_myvideos || {};
    M.block_myvideos.init_embed_code = function(params) {
        return new EMBED(params);
    }

}, '@VERSION@', {
    requires:['base']
});


