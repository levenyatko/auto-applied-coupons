<?php

    class WCAC_Frontend
    {

        public static function init_hooks()
        {

            if ( wcac_is_coupons_displayed() ) {

                add_action( 'woocommerce_after_add_to_cart_button', [ self::class, 'show_coupons_list'] , 11 );
                add_action( 'wp_enqueue_scripts', [self::class, 'enqueue'] );

                $auto_apply_coupon = wcac_get_option( 'wcac_auto_apply_coupon' );
                $auto_apply_coupon = apply_filters('wcac_auto_apply_coupon', $auto_apply_coupon);

                if ( ! empty($auto_apply_coupon) && 'yes' == $auto_apply_coupon ) {
                    // apply coupon with product
                    add_action('woocommerce_add_to_cart', [ self::class, 'apply_coupon' ], 10, 6);
                }

                add_action('wp_print_styles', [ self::class, 'print_styles' ], 10);

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

            wc_get_template( 'list.php', $args, 'coupons', WCAC_PLUGIN_DIR . 'templates/' );

        }

        public static function show_available_coupons( $product_id )
        {

            $coupons =  apply_filters('wcac_available_coupons_for_product', [], $product_id, 0);

            if ( empty( $coupons ) ) {
                return;
            }

            $applied_data = wcac_get_product_active_coupon($product_id);
            $applied_code  = '';

            if ( isset( $applied_data['coupon_code'] ) ) {
                $applied_code = $applied_data['coupon_code'];
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
                        'settings'=> [
                            'shouldChangePrice' => wcac_should_make_sale()
                        ],
                    ]
                );
                wp_enqueue_script( 'wcac-script');
            }
        }

        public static function print_styles()
        {
            $base_bg    = wcac_get_option( 'wcac_coupon_bg_color' );
            $base_text  = wcac_get_option( 'wcac_coupon_text_color' );
            $accent_bg  = wcac_get_option( 'wcac_active_coupon_bg_color' );
            $accent_text = wcac_get_option( 'wcac_active_coupon_text_color' );
            ?>
            <style id="wcac-coupon-colors">
                :root {
                    --wcac-main-bg-color: <?php echo sanitize_hex_color($base_bg); ?>;
                    --wcac-main-text-color: <?php echo sanitize_hex_color($base_text); ?>;
                    --wcac-accent-color: <?php echo sanitize_hex_color($accent_bg); ?>;
                    --wcac-accent-text-color: <?php echo sanitize_hex_color($accent_text); ?>;
                }
            </style>
            <?php
        }

        public static function apply_coupon($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
        {
            if ( isset($_POST['wcac-current-coupon-code']) ) {
                WC()->cart->apply_coupon( $_POST['wcac-current-coupon-code'] );
            }

        }
    }