<?php

    class WCAC_Product
    {

        public static function get_coupons( $coupons_list, $product, $ignore_cached = 0 )
        {
            $product_id = 0;

            if ( is_int($product) ) {
                $product_id = $product;
            } elseif ( is_object( $product ) && is_callable( array( $product, 'get_id' ) ) ) {
                $product_id = $product->get_id();
            }

            if ( ! $product_id ) {
                return false;
            }

            $ignore_cached = apply_filters('wcac_get_coupons_list_from_cache', $ignore_cached, $product_id);

            if ( ! $ignore_cached ) {
                $cached = WCAC_Transient::get_product_transient($product_id);

                if ( ! empty($cached) ) {
                    return $cached;
                }
            }

            $coupons_list = self::make_coupons_list( $product_id );

            WCAC_Transient::update_product_transient($product_id, $coupons_list);

            return $coupons_list;
        }

        public static function update_coupons_list($product_id)
        {
            if ( ! $product_id ) {
                return;
            }

            $coupons_list = self::make_coupons_list( $product_id );
            WCAC_Transient::update_product_transient($product_id, $coupons_list);
        }

        private static function make_coupons_list( $product )
        {
            $updated_list = [];

            if ( is_int($product) ) {
                $product = wc_get_product( $product );
            }

            if ( ! is_object( $product ) || ! is_callable( array( $product, 'get_id' ) ) ) {
                return [];
            }

            $args = array(
                'posts_per_page'   => -1,
                'post_type'        => 'shop_coupon',
                'post_status'      => 'publish',
                'no_found_rows'    => true,
                'fields'           => 'ids',
                'meta_query'       => [
                    'relation' => 'AND',
                    [
                        'key'     => WCAC_Coupon_Restrictions::$show_meta_key,
                        'value'   => 'yes',
                        'compare' => '=',
                    ],
                    [
                        'relation' => 'OR',
                        [
                            'key'     => 'date_expires',
                            'value'   => 'IS NULL',
                            'compare' => '=',
                        ],
                        [
                            'key'     => 'date_expires',
                            'value'   => time(),
                            'compare' => '>=',
                            'type'    => 'NUMERIC',
                        ],
                    ]
                ]
            );

            $coupons_query = new WP_Query( $args );

            $available_product_coupons = [];

            if ($coupons_query->have_posts()) {

                foreach ($coupons_query->posts as $coupon_id) {

                    $coupon =  new WC_Coupon( $coupon_id );

                    if ( is_a($product, 'WC_Product_Variable') ) {

                        $variations = $product->get_visible_children();

                        if ( $variations ) {
                            foreach ($variations as $child_id) {
                                $child_product = wc_get_product($child_id);

                                if ( $coupon->is_valid_for_product( $child_product )
                                    && ! isset( $available_product_coupons[ $coupon->get_id() ] )
                                ) {
                                    $available_product_coupons[ $coupon->get_id() ] = $coupon;
                                }
                            }
                        }

                    } else {
                        if ( $coupon->is_valid_for_product( $product ) ) {
                            $available_product_coupons[ $coupon->get_id() ] = $coupon;
                        }
                    }

                }

            }

            if ( $available_product_coupons ) {

                foreach ( $available_product_coupons as $c ) {
                    $updated_list[] = $c->get_id();
                }

            }

            return $updated_list;
        }

        public static function get_price_after_coupon($product, $coupon)
        {
            $values = array (
                'data'		=> $product,
                'quantity'	=> 1
            );

            $product_price   = $product->get_price();
            $discount_amount = $coupon->get_discount_amount( $product_price, $values, true );
            $discount_amount = min( $product_price, $discount_amount );
            $_price          = max( $product_price - $discount_amount, 0 );

            return wc_format_decimal( $_price, wc_get_price_decimals() );
        }

        public static function get_sale_price( $price, $product )
        {
            if ( is_cart() || is_checkout() || is_admin() || ! wcac_should_make_sale() ) {
                return $price;
            }

            $coupon_data =  wcac_get_product_active_coupon($product);
            if ( isset( $coupon_data['product_price'] ) ) {
                $price = $coupon_data['product_price'];
            }

            return $price;
        }

        public static function get_variation_prices($prices, $product, $for_display)
        {
            foreach ($prices['price'] as $variation_id => $price) {
                $product_coupon = wcac_get_product_active_coupon($variation_id);
                if ( ! empty($product_coupon['coupon_code'])) {
                    $prices['price'][ $variation_id ] = $product_coupon['product_price'];
                    $prices['sale_price'][ $variation_id ] = $product_coupon['product_price'];
                }
            }

            return $prices;
        }

        public static function get_price_html( $price, $product )
        {
            if ( is_cart() || is_checkout() || is_admin() || ! wcac_should_make_sale() ) {
                return $price;
            }

            if ( in_array($product::class, ['WC_Product_Variation', 'WC_Product_Simple']) ) {

                $product_price = $product->get_price();

                wcac_remove_price_hooks();

                $coupon_data =  wcac_get_product_active_coupon($product);

                if ( isset( $coupon_data['product_price'] ) ) {
                    $after_coupon = $coupon_data['product_price'];
                    $price = wc_format_sale_price($product_price, $after_coupon);
                }

                wcac_add_price_hooks();

            }

            return $price;
        }

    }