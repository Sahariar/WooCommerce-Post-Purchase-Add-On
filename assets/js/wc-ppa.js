(function ($) {
    'use strict';

    let isProcessing = false;

    $(document).on('click', '.wc-ppa-accept', function (e) {
        e.preventDefault();

        if (isProcessing) {
            return;
        }

        const $btn = $(this);
        const upsellProductId = $btn.data('product-id');

        if (!upsellProductId) {
            console.error('Missing upsell product ID');
            return;
        }

        isProcessing = true;
        $btn.prop('disabled', true).text('Adding…');

        $.ajax({
            url: wcPpaData.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'wc_ppa_accept_offer',
                nonce: wcPpaData.nonce,
                order_id: wcPpaData.orderId,
                upsell_product_id: upsellProductId
            },
        })
        .done(function (response) {
            if (!response || !response.success) {
                showMessage(response?.data?.message || 'Something went wrong', 'error');
                resetButton($btn);
                return;
            }

            showMessage('Added to your order ✔', 'success');

            // If backend returns next upsells, you handle them here
            if (response.data?.next?.length) {
                // TODO: render next upsell
                console.log('Next upsells:', response.data.next);
            } else {
                $('.wc-ppa-offer').fadeOut();
            }
        })
        .fail(function () {
            showMessage('Server error. Please try again.', 'error');
            resetButton($btn);
        });
    });

    function showMessage(msg, type) {
        const $msg = $('#wc-ppa-message');
        $msg
            .removeClass('success error')
            .addClass(type)
            .text(msg)
            .show();
    }

    function resetButton($btn) {
        isProcessing = false;
        $btn.prop('disabled', false).text('Add to Order');
    }

})(jQuery);
