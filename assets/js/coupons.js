jQuery( function( $ ) {

    var wcacCouponsList = {
        productId: 0,
        variationId: 0,
        isVariableProduct: false,
        $couponsListWrapper: $('.wcac-coupons-list--wrap'),
        $loader: $('#wcac-coupons-list-loader'),
        init: function() {
            if ( 0 < $('form.cart.variations_form').length ) {
                this.isVariableProduct = true;
                this.productId = $('input[name=product_id]').val();
            } else {
                this.productId = $('.single_add_to_cart_button').val();
                this.showCouponsList();
            }
        },
        showList: function () {
            this.$couponsListWrapper.show();
        },
        hideList: function () {
            this.$couponsListWrapper.hide();
        },
        showLoader: function () {
            this.$loader.show();
            $('.wcac-coupons-list-items--wrap').addClass('noscroll').scrollTop(0);;
        },
        hideLoader: function () {
            this.$loader.hide();
            $('.wcac-coupons-list-items--wrap').removeClass('noscroll');
        },
        updateProductCookie: function (value) {
            if ( this.isVariableProduct ) {
                Cookies.set('wcac_product_' + this.variationId + '_coupon', value);
            } else {
                Cookies.set('wcac_product_' + this.productId + '_coupon', value);
            }
        },
        updateProductPrice: function (price) {
            let $priceObj = null;
            let $productWrapper = this.$couponsListWrapper.closest('.product');
            if ( this.isVariableProduct ) {
                $priceObj = $productWrapper.find('.woocommerce-variation-price .price');
                if ( 0 == $priceObj.length ) {
                    $priceObj = $productWrapper.find('.price');
                }
            } else {
                $priceObj = $productWrapper.find('.price');
            }

            if ( 0 < $priceObj.length ) {
                $priceObj.html( price );
            }
        },
        updateProductCoupon: function (coupon) {
            this.updateProductCookie(coupon);
            this.getProductPrice(coupon);
        },
        updateProductVariation: function (variation_id = 0) {
            if ( this.isVariableProduct ) {
                this.variationId = variation_id;
            }
        },
        showCouponsList: function () {

            let requestData = {
                'action'       : 'wcac_get_product_coupons',
                'product_id'   : this.productId,
                'is_variation' : this.isVariableProduct,
                'variation_id' : this.variationId
            };

            $.ajax( {
                url: wc_add_to_cart_params.ajax_url,
                type: 'POST',
                data: requestData,
                beforeSend: function () {
                    wcacCouponsList.showLoader();
                },
                success: function( responce ) {
                    if ( responce.success ) {

                        if ( responce.data.coupons_html ) {
                            $('#wcac-coupons-list-items').html( responce.data.coupons_html );
                            wcacCouponsList.showList();

                            let $appliedCoupon = $('input[name=wcac-current-coupon-code]:checked');
                            if ( $appliedCoupon.length ) {
                                wcacCouponsList.updateProductCoupon( $appliedCoupon.val() );
                            }

                        } else {
                            $('#wcac-coupons-list-items').html( '');
                            wcacCouponsList.hideList();
                        }
                    }
                },
                complete: function() {
                    wcacCouponsList.hideLoader();
                }
            } );
            },
        getProductPrice: function (coupon_code) {

            $.ajax( {
                url: wc_add_to_cart_params.ajax_url,
                type: 'POST',
                data: {
                    'action'     : 'wcac_get_sale_price',
                    'product_id' : this.productId,
                    'coupon'     : coupon_code
                },
                beforeSend: function () {
                    wcacCouponsList.showLoader();
                },
                success: function( responce ) {

                    if ( responce.success ) {
                        wcacCouponsList.updateProductPrice( responce.data.new_price_html );
                    }
                },
                complete: function() {
                    wcacCouponsList.hideLoader();
                }
            } );
        }
    };

    wcacCouponsList.init();

    $(document).on('change', 'input[name=wcac-current-coupon-code]',function (e) {
        wcacCouponsList.updateProductCoupon( $(this).val() );
    });

    $( '.variations_form' ).on( 'show_variation', function(event, variation) {
        wcacCouponsList.updateProductVariation(variation.variation_id);
        wcacCouponsList.showCouponsList();
    });

});