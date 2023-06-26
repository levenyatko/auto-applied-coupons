jQuery( function( $ ) {

    var wcac_coupons_list = {
        product_id: 0,
        is_variable_product: false,
        $coupons_list: $('.wcac-coupons-list--wrap'),
        $loader: $('#wcac-coupons-list-loader'),
        init: function() {
            if ( 0 < $('form.cart.variations_form').length ) {
                this.is_variable_product = true;
            }

            this.product_id = $('.single_add_to_cart_button').val();
        },
        update_product_cookie: function (value) {
            Cookies.set('wcac_product_' + this.product_id + '_coupon', value);
        },
        update_product_coupon: function (coupon) {
            if ( this.is_variable_product ) {

            } else {
                this.update_product_cookie(coupon);
                this.get_new_product_price(coupon);
            }
        },
        updateProductPrice: function (price) {
           this.$coupons_list.closest('.product').find('.price ins').html(price);
        },
        showLoader: function () {
            this.$loader.show();
            $('.wcac-coupons-list--items').addClass('noscroll');
        },
        hideLoader: function () {
            this.$loader.hide();
            $('.wcac-coupons-list--items').removeClass('noscroll');
        },
        get_new_product_price: function (coupon_code) {

            $.ajax( {
                url: wc_add_to_cart_params.ajax_url,
                type: 'POST',
                data: {
                    'action'     : 'wcac_get_sale_price',
                    'product_id' : this.product_id,
                    'coupon'     : coupon_code
                },
                beforeSend: function () {
                    wcac_coupons_list.showLoader();
                },
                success: function( responce ) {

                    if ( responce.success ) {
                        wcac_coupons_list.updateProductPrice( responce.data.new_price_html );
                    }
                },
                complete: function() {
                    wcac_coupons_list.hideLoader();
                }
            } );
        }
    };

    wcac_coupons_list.init();


    $(document).on('change', 'input[name=wcac-current-coupon-code]',function (e) {
        wcac_coupons_list.update_product_coupon( $(this).val() );
    });

});