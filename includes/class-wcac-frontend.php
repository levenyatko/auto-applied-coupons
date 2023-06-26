<?php

    class WCAC_Frontend
    {

        public function hooks()
        {
            // show available coupons if needed
            $show_available_coupons = wcac_get_option( 'wcac_available_display' );
            $show_available_coupons = apply_filters('wcac_show_available_coupons', $show_available_coupons);

            add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'show_coupons'] , 11 );
            if ( ! empty($show_available_coupons) && 'yes' == $show_available_coupons ) {
                add_action( 'wp_enqueue_scripts', [$this, 'enqueue'] );
            }

            $auto_apply_coupon = wcac_get_option( 'wcac_auto_apply_coupon' );
            $auto_apply_coupon = apply_filters('wcac_auto_apply_coupon', $auto_apply_coupon);

            if ( ! empty($auto_apply_coupon) && 'yes' == $auto_apply_coupon ) {
                // apply coupon with product
                add_action('woocommerce_add_cart_item_data', [ $this, 'apply' ], 10, 2);
            }
        }

        public function show_coupons($product_id = 0)
        {
            if ( empty($product_id) ) {
                $product_id = get_the_ID();
            }

            $product_coupons_data =  apply_filters('wcac_available_coupons_for_product', [], $product_id, 0);

            if ( empty( $product_coupons_data ) ) {
                return;
            }

            $coupons = $product_coupons_data['available'];

            $applied_coupon_code  = '';

            if ( isset( $product_coupons_data['apply']['coupon_code'] ) ) {
                $applied_coupon_code = apply_filters('wcac_is_coupon_relevant', $product_coupons_data['apply']['coupon_code'], $product_id);
            }

            $args = [
                'product_id'        => $product_id,
                'coupons'           => $coupons,
                'displayed_count'   => apply_filters('wcac_coupons_count_to_show', 5),
                'applied_code'      => $applied_coupon_code
            ];

            $show_available_coupons = apply_filters('wcac_show_available_coupons', wcac_get_option( 'wcac_available_display' ) );
            $auto_apply_coupon = apply_filters('wcac_auto_apply_coupon', wcac_get_option( 'wcac_auto_apply_coupon' ));

            if ( ! empty($show_available_coupons) && 'yes' == $show_available_coupons ) {
                wc_get_template( 'list.php', $args, 'coupons', WCAC_PLUGIN_DIR . 'templates/' );
            } elseif ( ! empty($auto_apply_coupon) && 'yes' == $auto_apply_coupon ) {
                wc_get_template( 'hidden.php', $args, 'coupons', WCAC_PLUGIN_DIR . 'templates/' );
            }

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