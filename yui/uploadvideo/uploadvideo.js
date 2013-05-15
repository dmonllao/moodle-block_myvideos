YUI.add('moodle-block_myvideos-uploadvideo', function(Y) {

    var UPLOADVIDEONAME = 'block_myvideos-uploadvideo';

    var UPLOADVIDEO = function() {
        UPLOADVIDEO.superclass.constructor.apply(this, arguments);
    };

    Y.extend(UPLOADVIDEO, Y.Base, {

        /**
         * Adds a onclick listener to the form submit button
         *
         * @return void
         */
        initializer : function (params) {

            // Submit button
            var submitElement = Y.one('#id_submitbutton');

            // Displays the loader div after submitting a new file to convert
            // Shown during the upload form file uploading
            submitElement.on("click", function(e) {

                if (validate_myvideos_uploadvideo_form() == true) {
                    var loader = Y.one('#id_loader');
                    loader.setStyle('display', 'block');
                }
            });

        }

    }, {
        NAME: UPLOADVIDEONAME,
    });

    M.block_myvideos = M.block_myvideos || {};
    M.block_myvideos.init_uploadvideoform = function(params) {
        return new UPLOADVIDEO(params);
    }

}, '@VERSION@', {
    requires:['base']
});
