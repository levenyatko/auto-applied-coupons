<?php

    class WCAC_Frontend
    {

        public function hooks()
        {
            // show available coupons in single product page
            add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'show_coupons'] , 11 );
            // for manual display
            add_action( 'wcac_product_available_coupons', [ $this, 'show_coupons'], 11 );

            add_action( 'wp_enqueue_scripts', [$this, 'enqueue'] );

            // apply coupon with product
            add_action( 'woocommerce_add_cart_item_data', [ $this, 'apply' ], 10, 2 );
        }

        public function show_coupons($product_id = 0)
        {
            if ( empty($product_id) ) {
                $product_id = get_the_ID();
            }

            $product_coupons_data = WCAC_Product::get_coupons( $product_id );

            if ( empty( $product_coupons_data ) ) {
                return;
            }

            $coupons = $product_coupons_data['available'];

            $applied_coupon_code  = '';

            if ( isset( $product_coupons_data['apply']['coupon_code'] ) ) {
                $applied_coupon_code = WCAC_Coupon::filter_code( $product_coupons_data['apply']['coupon_code'], $product_id );
            }

            $args = [
                'product_id'        => $product_id,
                'coupons'           => $coupons,
                'displayed_count'   => apply_filters('wcac_coupons_count_to_show', 5),
                'applied_code'      => $applied_coupon_code
            ];

            wc_get_template( 'list.php', $args, 'coupons', WCAC_PLUGIN_DIR . 'templates/' );

        }

        public function enqueue()
        {
            if ( is_singular('product') ) {
                wp_enqueue_style( 'wcac-style', WCAC_PLUGIN_URL . 'assets/css/frontend.css', false, null );
            }
        }

        public function apply($cart_item_data, $product_id)
        {
            if ( isset($_POST['wcac-current-coupon-code']) ) {
                WC()->cart->apply_coupon( $_POST['wcac-current-coupon-code'] );
            }

            return $cart_item_data;
        }
    }