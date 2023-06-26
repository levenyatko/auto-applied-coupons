<?php

    class WCAC_Ajax_Controller
    {
        public static function init_hooks()
        {
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

            wp_send_json_error(['msg' => 'One or more required fields is empty']);
        }
    }