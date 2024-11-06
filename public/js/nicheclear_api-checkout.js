(function ($) {
    'use strict';

    function updatePlaceOrderButton() {
        var selectedPaymentMethod = $('input[name="payment_method"]:checked').val();
        selectedPaymentMethod = selectedPaymentMethod.replace('nc_', '').toUpperCase();
        if (ncapi_checkout_vars.active_payment_methods[selectedPaymentMethod]) {
            $('form.checkout, form#order_review').addClass('ncapi');
            $('#ncapi-payment-method-section').show();
            $('#pay_ncapi').html('Pay with ' + ncapi_checkout_vars.active_payment_methods[selectedPaymentMethod]);
        } else {
            $('form.checkout, form#order_review').removeClass('ncapi');
            $('#ncapi-payment-method-section').hide();
        }
    }

    updatePlaceOrderButton();

    $('form.checkout, form#order_review').on('change', 'input[name="payment_method"]', function () {
        updatePlaceOrderButton();
    });

    $('#pay_ncapi').click(function () {

        const form = $('form.checkout');
        const formData = $(form).serialize();

        $('#ncapi-payment-method-section .messages').empty();
        const pay_button_title = $('#pay_ncapi').text();
        $('#pay_ncapi').prop('disabled', true).text('Processing...');

        $.ajax({
            url: ncapi_checkout_vars.ajax_url,
            type: 'POST',
            data: formData + '&action=ncapi_create_order',
            success: function (response) {
                $('#pay_ncapi').prop('disabled', false).text(pay_button_title); // Re-enable the button

                if (response.messages) {
                    $('#ncapi-payment-method-section .messages').html(response.messages);
                }

                if (response.redirect) {

                    Swal.fire({
                        html: `<iframe src="${response.redirect}" width="100%" height="600px" style="border:none;"></iframe>`,
                        showCloseButton: true,
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didClose: function (event) {
                            $('form.checkout').css({filter: 'blur(2px)', 'pointer-events': 'none'});
                            location.replace(response.returnUrl);
                        }
                    });

                    // $('#ncapi-payment-method-section').append('<iframe src="' + response.redirect + '" width="100%" height="500px"></iframe>');
                }


                /*if (response.success) {
                    console.log('Payment successful!'); // Handle successful payment
                    // window.location.href = response.redirect_url; // Redirect if needed
                } else {
                    console.log('Payment failed: ' + response.data.message); // Handle failure
                }*/
            },
            error: function () {
                $('#pay_ncapi').prop('disabled', false).text(pay_button_title);
                $('#ncapi-payment-method-section .messages').html('An error occurred while processing your payment.');
            }
        });
    })

})(jQuery);
