(function ($) {
    'use strict';


    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */

    $(document).on('click', '#check-api-connection', function () {
        $.ajax({
            type: "GET",
            url: 'https://api.eazycv.cloud',
            //  data: {q: idiom},
            async: true,
            // dataType: 'jsonp',   //you may use jsonp for cross origin request
            crossDomain: true,
            success: function (data, status, xhr) {
                console.log(xhr.getResponseHeader);
            }
        });

    }).on('change', '#eazycv-job-field-selector', function () {
        var objectData = JSON.stringify($(this).val());

        $('#wp_eazycv_display_job_fields').val(objectData);
    });


})(jQuery);
