let adScout = document;
adScout.addEventListener('change', (e) => {

    if (e.target.id === 'wc-block-components-totals-coupon__input-0') {
        jQuery.post(
            adscout_ajax_object.ajax_url,
            {
                action: 'adscout_apply_coupon',
                coupon: e.target.value,
            }
        ).done(
            function (response) {
            }
        );

    }

})

