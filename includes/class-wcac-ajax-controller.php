<?php

    class WCAC_Ajax_Controller
    {
        public static function init_hooks()
        {
            add_action( 'wp_ajax_wcac_get_product_coupons', [ self::class, 'get_product_coupons' ] );
            add_action( 'wp_ajax_nopriv_wcac_get_product_coupons', [ self::class, 'get_product_coupons' ] );

            add_action( 'wp_ajax_wcac_get_sale_price', [ self::class, 'get_product_sale_price' ] );
            add_action( 'wp_ajax_nopriv_wcac_get_sale_price', [ self::class, 'get_product_sale_price' ] );
        }

        public static function get_product_sale_price()
        {
            if ( ! empty($_POST['product_id']) && ! empty($_POST['coupon']) ) {
                $product = wc_get_product( (int)$_POST['product_id'] );

                if ( ! $product || ! is_callable( array( $product, 'get_id' ) ) ) {
                    wp_send_json_error(['msg' => 'Please, check the product']);
                }

                $coupon_id = wc_get_coupon_id_by_code($_POST['coupon']);
                if ( $coupon_id ) {
                    $coupon = new WC_Coupon($coupon_id);
                    $price = WCAC_Product::get_price_after_coupon($product, $coupon);

                    wp_send_json_success([
                        'new_price'      => $price,
                        'new_price_html' => wc_price($price)
                    ]);
                }

            }

            wp_send_json_error(['msg' => 'One or more required fields are empty']);
        }

        public static function get_product_coupons()
        {
            if ( ! empty($_POST['product_id']) ) {

                if ( ! empty($_POST['is_variation']) && ! empty($_POST['variation_id']) ) {
                    $product_id = (int)$_POST['variation_id'];
                } else {
                    $product_id = (int)$_POST['product_id'];
                }

                $product = wc_get_product( $product_id );

                if ( ! $product || ! is_callable( array( $product, 'get_id' ) ) ) {
                    wp_send_json_error(['msg' => 'Please, check the product']);
                }

                ob_start();

                WCAC_Frontend::show_available_coupons( $product->get_id() );

                $list_html = ob_get_contents();
                ob_end_clean();

                wp_send_json_success([
                    'coupons_html' => $list_html
                ]);

            }

            wp_send_json_error(['msg' => 'One or more required fields are empty']);

        }
    }