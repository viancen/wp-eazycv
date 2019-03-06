(function ($) {
    'use strict';

    /**
     * All of the code for your public-facing JavaScript source
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

    $(function () {

        // Get the modal
        var modal = document.getElementById('eazycv-gdpr-modal');

        if (modal) {
            // Get the button that opens the modal
            var btn = document.getElementById("eazycv-gdpr-link");

            // Get the <span> element that closes the modal
            var span = document.getElementsByClassName("eazycv-close")[0];

            // When the user clicks on the button, open the modal
            btn.onclick = function () {
                modal.style.display = "block";
            }

            // When the user clicks on <span> (x), close the modal
            span.onclick = function () {
                modal.style.display = "none";
            }

            // When the user clicks anywhere outside of the modal, close it
            window.onclick = function (event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
        }

        $(document).on('click', '#eazy-apply-submit-btn', function () {

            //todo: validation if none on default wordpress settings
            if (!$('#eazycv-field-email').val()) {
                $('#eazy-from-apply-error').html('E-mailadres is verplicht');
                $('#eazy-from-apply-error').css('display', 'block');
            } else {
                $('#eazy-apply-submit-btn').attr('disabled', true);
                grecaptcha.ready(function () {
                    grecaptcha.execute($('#eazycv-grekey').val(), {action: 'eazycv_application'}).then(
                        function (token) {
                            $('#eazy-apply-submit-btn').prop('disabled', true);
                            $('#eazycv-greval').val(token);
                            $('#eazycv-apply-form').submit();
                        });
                });
            }
        }).on('click', '#accept-gdpr-modal-btn', function () {
            $('#eazycv-field-gdpr').prop('checked', true);
            var modal = document.getElementById('eazycv-gdpr-modal');
            modal.style.display = "none";
        });
    });
})(jQuery);
