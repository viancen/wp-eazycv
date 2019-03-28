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


        $(document).on('click', '#eazy-apply-submit-btn', function () {

            $('[data-eazycv-required]').on('keyup change', function () {
                if ($(this).val()) {
                    $('#eazycv-error-' + $(this).data('eazycv-required')).addClass('eazycv-hidden');
                }
            });

            var hasError = false;
            $('[data-eazycv-required]').each(function (a, b) {

                if ($(b).attr('name') == 'accept_gdpr_version') {
                    if (!$(b).is(':checked')) {
                        $('#eazycv-error-' + $(b).data('eazycv-required')).removeClass('eazycv-hidden');
                        hasError = true;
                    }
                } else {
                    if (!$(b).val()) {
                        $('#eazycv-error-' + $(b).data('eazycv-required')).removeClass('eazycv-hidden');
                        hasError = true;
                    }
                }
            }).promise().done(function () {

                if (hasError) {
                    return false;
                } else {

                    grecaptcha.ready(function () {
                        grecaptcha.execute($('#eazycv-grekey').val(), {action: 'eazycv_application'}).then(
                            function (token) {
                                $.featherlight($('#eazycv-wait-modal'), {
                                    closeOnEsc: false,
                                    closeIcon: '',
                                });
                                $('#eazy-apply-submit-btn').prop('disabled', true);
                                $('#eazycv-greval').val(token);
                                $('#eazycv-apply-form').submit();
                            });
                    });

                }
            });

        }).on('click', '#accept-gdpr-modal-btn', function () {
            $('#eazycv-field-gdpr').prop('checked', true);
            var current = $.featherlight.current();
            current.close();

        }).on('blur', '#eazycv-field-email', function () {
            $.post(eazycv_ajax_object.ajaxurl, {
                action: 'eazycv_check_email_address',
                email_address: $(this).val(),
                job_id: $('#eazycv-apply-job_id').val(),
                dataType: 'json'
            }, function (response) {

                response = $.parseJSON(response);

                $('#eazycv-ajax-email-error').html('');

                $("#eazycv-apply-form input, #eazycv-apply-form button, #eazycv-apply-form label, #eazycv-apply-form select, #eazycv-apply-form textarea")
                    .prop("disabled", false)
                    .removeClass('disabled').fadeTo("slow", 1);

                if (response.error == true) {

                    $("#eazycv-apply-form input,#eazycv-apply-form button,#eazycv-apply-form label, #eazycv-apply-form select, #eazycv-apply-form textarea")
                        .prop("disabled", true)
                        .addClass('disabled').fadeTo("slow", 0.4);

                    $('#eazycv-field-email')
                        .prop("disabled", false)
                        .removeClass('disabled').fadeTo("slow", 1);

                    var errorRedirectDir = '<div class="eazy-error">' + response.message + '</div>';
                    $('#eazycv-ajax-email-error').html(errorRedirectDir);
                }
                // Handle the response however best suits your needs

            });
        });
    });
})(jQuery);
