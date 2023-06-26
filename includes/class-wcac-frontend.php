<?php

    class WCAC_Frontend
    {

        public static function init_hooks()
        {
            // show available coupons if needed
            $show_available_coupons = wcac_get_option( 'wcac_available_display' );
            $show_available_coupons = apply_filters('wcac_show_available_coupons', $show_available_coupons);

            add_action( 'woocommerce_after_add_to_cart_button', [ self::class, 'show_coupons_list'] , 11 );
            if ( ! empty($show_available_coupons) && 'yes' == $show_available_coupons ) {
                add_action( 'wp_enqueue_scripts', [self::class, 'enqueue'] );
            }

            $auto_apply_coupon = wcac_get_option( 'wcac_auto_apply_coupon' );
            $auto_apply_coupon = apply_filters('wcac_auto_apply_coupon', $auto_apply_coupon);

            if ( ! empty($auto_apply_coupon) && 'yes' == $auto_apply_coupon ) {
                // apply coupon with product
                add_action('woocommerce_add_cart_item_data', [ self::class, 'apply' ], 10, 2);
            }
        }

        public static function show_coupons_list($product_id = 0)
        {
            if ( empty($product_id) ) {
                $product_id = get_the_ID();
            }

            $args = [
                'product_id'        => $product_id,
            ];

            $show_available_coupons = apply_filters('wcac_show_available_coupons', wcac_get_option( 'wcac_available_display' ) );
            $auto_apply_coupon = apply_filters('wcac_auto_apply_coupon', wcac_get_option( 'wcac_auto_apply_coupon' ));

            if ( ! empty($show_available_coupons) && 'yes' == $show_available_coupons ) {
                wc_get_template( 'list.php', $args, 'coupons', WCAC_PLUGIN_DIR . 'templates/' );
            } elseif ( ! empty($auto_apply_coupon) && 'yes' == $auto_apply_coupon ) {
                wc_get_template( 'hidden.php', $args, 'coupons', WCAC_PLUGIN_DIR . 'templates/' );
            }

        }

        public static function show_available_coupons( $product_id )
        {

            $product_coupons_data =  apply_filters('wcac_available_coupons_for_product', [], $product_id, 0);

            if ( empty( $product_coupons_data ) ) {
                return;
            }

            $coupons = $product_coupons_data['available'];

            $applied_code  = '';

            if ( isset( $product_coupons_data['apply']['coupon_code'] ) ) {
                $applied_code = apply_filters('wcac_is_coupon_relevant', $product_coupons_data['apply']['coupon_code'], $product_id);
            }

            $displayed_count = apply_filters('wcac_coupons_count_to_show', 5);

            $i = 0;
            foreach ( $coupons as $coupon_id ) {

                if ( $i >= $displayed_count ) {
                    break;
                }

                $coupon =  new WC_Coupon( $coupon_id );

                $coupon_amount    = $coupon->get_amount();
                $is_free_shipping = ( $coupon->get_free_shipping() ) ? 'yes' : 'no';
                $discount_type    = $coupon->get_discount_type();
                $expiry_date      = WCAC_Coupon::get_expired_date($coupon);

                $expiry_timestamp = '';
                if ( !empty($expiry_date) ) {
                    $expiry_timestamp = $expiry_date->getTimestamp();
                }

                $is_zero_amount_coupon = false;

                if ( ( empty( $coupon_amount ) ) && ( ( ! empty( $discount_type ) && ! in_array( $discount_type, array( 'free_gift', 'smart_coupon' ), true ) ) || ( 'yes' !== $is_free_shipping ) ) ) {
                    if ( 'yes' !== $is_free_shipping ) {
                        $is_zero_amount_coupon = true;
                    }
                }

                if ( $is_zero_amount_coupon ) {
                    continue;
                }

                if ( empty( $discount_type ) || ( ! empty( $expiry_timestamp ) && time() > $expiry_timestamp ) ) {
                    continue;
                }

                $args = array(
                    'product_id'         => $product_id,
                    'coupon_object'      => $coupon,
                    'coupon_expiry'      => $expiry_date,
                    'applied_coupon'     => $applied_code,
                );

                wc_get_template( 'card.php', $args, 'coupons', WCAC_PLUGIN_DIR . 'templates/' );

                $i++;
            }

        }

        public static function enqueue()
        {
            if ( is_singular('product') ) {
                wp_enqueue_style( 'wcac-style', WCAC_PLUGIN_URL . 'assets/css/frontend.css', false, null );

                wp_register_script( 'wcac-script', WCAC_PLUGIN_URL . 'assets/js/coupons.js', false, null, true );
                wp_localize_script( 'wcac-script', 'wcac_vars',
                    [
                        'apibase' => get_rest_url(null, 'wcac-action'),
                        'nonce'   => wp_create_nonce( 'wp_rest' )
                    ]
                );
                wp_enqueue_script( 'wcac-script');
            }
        }

        public static function apply($cart_item_data, $product_id)
        {
            if ( isset($_POST['wcac-current-coupon-code']) ) {
                WC()->cart->apply_coupon( $_POST['wcac-current-coupon-code'] );
            }

            return $cart_item_data;
        }
    }