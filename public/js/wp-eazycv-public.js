(function ($) {
    'use strict';

    $(function () {
        function performEazyCvSearch() {
            var $params = '?eazycv-search=1';
            $('.eazycv-job-search-filters').each(function () {
                if (this.value) {
                    $params += '&' + $(this).attr('name') + '=' + this.value;
                }
            });
            if ($params != '?eazycv-search=1') {
                window.location.href = window.location.protocol + "//" + window.location.host + "/" + window.location.pathname + $params;
            }
        }

        function sendEazyData() {
            const XHR = new XMLHttpRequest();

            // Bind the FormData object and the form element
            const FD = new FormData(document.getElementById('eazycv-apply-form'));

            // Define what happens on successful data submission
            XHR.addEventListener("load", function (event) {
                let jsonResponse = JSON.parse(event.target.responseText);

                if (jsonResponse.status == 'success') {
                    $('#eazy-successful-application').removeClass('eazycv-hidden');
                    document.getElementById('eazycv-apply-form').remove();
                } else if (jsonResponse.status == 'captcha') {
                    $('#eazy-error-application-captcha').removeClass('eazycv-hidden');
                } else {
                    $('#eazy-error-application').removeClass('eazycv-hidden');
                }
                $.featherlight.close();

            });

            // Define what happens in case of error
            XHR.addEventListener("error", function (event) {
                $('#eazy-error-application').removeClass('eazycv-hidden');
            });

            // Set up our request
            XHR.open("POST", "/eazycv-process-subscription");

            // The data sent is what the user provided in the form
            XHR.send(FD);
        }

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


                                sendEazyData();

                            });
                    });

                }
            });

        }).on('click', '.eazycv-job-search-filters-reset', function () {
            window.location.href = window.location.protocol + "//" + window.location.host + "/" + window.location.pathname;
        }).on('keyup', '.eazycv-job-search-filters', function (event) {
            var keycode = (event.keyCode ? event.keyCode : event.which);
            if(keycode == '13'){
                performEazyCvSearch();
            }
        }).on('click', '.eazycv-job-search-filters-submit', function () {
            performEazyCvSearch();
        }).on('change', '.eazycv-job-search-filters', function () {

        }).on('click', '#accept-terms-modal-btn', function () {
            $('#eazycv-field-terms').prop('checked', true);
            var current = $.featherlight.current();
            current.close();
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

        if ($('#share-eazy').length) {

            jsSocials.setDefaults({
                showLabel: false,
                css: "eazycv-share-btns"
            });

            $("#share-eazy").jsSocials({
                shares: ["email", "twitter", "facebook", "googleplus", "linkedin", "whatsapp"],
                showLabel: false,
                showCount: false,
                shareIn: "popup",
            });
        }
    });
})(jQuery);
