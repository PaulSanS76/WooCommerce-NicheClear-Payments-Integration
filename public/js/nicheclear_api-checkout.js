(function ($) {
    'use strict';

    function updatePlaceOrderButton() {
        let selectedPaymentMethod = $('input[name="payment_method"]:checked').val() || '';
        selectedPaymentMethod = selectedPaymentMethod.replace('nc_', '').toUpperCase();
        if (ncapi_checkout_init_vars.active_payment_methods[selectedPaymentMethod]) {
            $('form.checkout, form#order_review').addClass('ncapi');
            $('#ncapi-payment-method-section').show();
            $('#pay_ncapi').html('Pay with ' + ncapi_checkout_init_vars.active_payment_methods[selectedPaymentMethod]);
        } else {
            $('form.checkout, form#order_review').removeClass('ncapi');
            $('#ncapi-payment-method-section').hide();
        }
    }

    updatePlaceOrderButton();

    $('form.checkout, form#order_review').on('change', 'input[name="payment_method"]', function () {
        updatePlaceOrderButton();
    });

    $(document.body).on('updated_checkout', function() {
        updatePlaceOrderButton();
    });

    $('#pay_ncapi').click(function () {

        let selectedPaymentMethod = $('input[name="payment_method"]:checked').val() || '';

        const form = $('form.checkout');
        const formData = $(form).serialize();

        $('#ncapi-payment-method-section .messages').empty();
        const pay_button_title = $('#pay_ncapi').text();
        $('#pay_ncapi').prop('disabled', true).text('Processing...');

        $.ajax({
            url: ncapi_checkout_init_vars.ajax_url,
            type: 'POST',
            data: `${formData}&action=${ncapi_checkout_init_vars.ajax_action}&order_id=${ncapi_checkout_init_vars.order_id || 0}&payment_processor_code=${selectedPaymentMethod.replace('nc_', '').toUpperCase()}`,
            success: function (response) {
                $('#pay_ncapi').prop('disabled', false).text(pay_button_title); // Re-enable the button

                if (response.messages) {
                    $('#ncapi-payment-method-section .messages').html(response.messages);
                }

                window.ncapi_checkout_dyn_data = response.ncapi_checkout_dyn_data || response.data.ncapi_checkout_dyn_data;

                if (ncapi_checkout_dyn_data.nc_frame_url) {

                    const open_time = Date.now();
                    Swal.fire({
                        html: `<iframe src="${ncapi_checkout_dyn_data.nc_frame_url}" width="100%" height="600px" style="border:none;"></iframe>`,
                        showCloseButton: true,
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didClose: function (event) {
                            const close_time = Date.now();
                            const time_diff = (close_time - open_time) / 1000;

                            if (ncapi_checkout_dyn_data.after_pay_url && time_diff > 10) {
                                $('form.checkout').css({filter: 'blur(2px)', 'pointer-events': 'none'});
                                location.replace(ncapi_checkout_dyn_data.after_pay_url);
                            }
                        }
                    });
                }
            },
            error: function () {
                $('#pay_ncapi').prop('disabled', false).text(pay_button_title);
                $('#ncapi-payment-method-section .messages').html('An error occurred while processing your payment.');
            }
        });
    })

})(jQuery);

function ncapi_add_notice() {
    order_id = ncapi_checkout_init_vars.order_id;
    if (!order_id) {
        console.log('No order ID provided.');
        return;
    }
    jQuery.ajax({
        url: ncapi_checkout_init_vars.ajax_url,
        type: 'POST',
        data: {
            action: 'ncapi_add_notice',
            order_id: order_id
        },
        success: function (response) {
            if (response.success) {
                console.log('Notice added successfully:', response.data);
            } else {
                console.log('Failed to add notice:', response.message);
            }
        },
        error: function () {
            console.log('An error occurred while trying to add the notice.');
        }
    });
}