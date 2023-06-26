jQuery( function( $ ) {

    function update_product_coupon(product_id, coupon) {
        Cookies.set('wcac_product_' + product_id + '_coupon', coupon);
    }

    $(document).on('change', 'input[name=wcac-current-coupon-code]',function (e) {
        update_product_coupon($('.single_add_to_cart_button').val(), $(this).val());
    });
});