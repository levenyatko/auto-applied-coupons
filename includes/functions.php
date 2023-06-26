<?php

    function wcac_get_instance()
    {
        return WCAC_Plugin::instance();
    }

    function wcac_where_query_allow_is_null ( $where )
    {
        return str_replace("= 'IS NULL'", ' IS NULL ', $where);
    }

    function wcac_is_coupons_displayed()
    {
        $show_available_coupons = wcac_get_option( 'wcac_available_display' );
        $show_available_coupons = apply_filters('wcac_show_available_coupons', $show_available_coupons);

        if ( ! empty($show_available_coupons) && 'yes' == $show_available_coupons ) {
            return true;
        }

        return false;
    }

    function wcac_should_make_sale()
    {
        if ( wcac_is_coupons_displayed() ) {
            $apply_coupon_to_price = wcac_get_option('wcac_make_price_sale');
            $apply_coupon_to_price = apply_filters('wcac_apply_coupon_to_price', $apply_coupon_to_price);

            if (!empty($apply_coupon_to_price) && 'yes' == $apply_coupon_to_price) {
                return true;
            }
        }

        return false;
    }

    function wcac_add_price_hooks()
    {
        add_filter( 'woocommerce_product_is_on_sale', [WCAC_Product::class, 'is_on_sale'], 10, 2 );
        add_filter( 'woocommerce_product_get_price',  [WCAC_Product::class, 'get_sale_price'], 10, 2 );
    }

    function wcac_remove_price_hooks()
    {
        remove_filter( 'woocommerce_product_is_on_sale', [WCAC_Product::class, 'is_on_sale'], 10, 2 );
        remove_filter( 'woocommerce_product_get_price',  [WCAC_Product::class, 'get_sale_price'], 10, 2 );
    }

    /**
     * Function to get attributes of a given product.
     */
    function wcac_get_product_attributes( $product = null )
    {
        $product_attributes_ids = array();

        if ( is_numeric( $product ) ) {
            $product = wc_get_product( $product );
        }

        if ( ! is_a( $product, 'WC_Product' ) ) {
            return $product_attributes_ids;
        }

        $product_attributes = $product->get_attributes();

        if ( ! empty( $product_attributes ) ) {

            if ( true === $product->is_type( 'variation' ) ) {

                foreach ( $product_attributes as $variation_taxonomy => $variation_slug ) {
                    $variation_attribute = get_term_by( 'slug', $variation_slug, $variation_taxonomy );
                    if ( is_object( $variation_attribute ) ) {
                        $product_attributes_ids[] = $variation_attribute->term_id;
                    }
                }

            } else {

                $product_id = ( is_object( $product ) && is_callable( array( $product, 'get_id' ) ) ) ? $product->get_id() : 0;
                if ( ! empty( $product_id ) ) {
                    foreach ( $product_attributes as $attribute ) {
                        if ( isset( $attribute['is_taxonomy'] ) && ! empty( $attribute['is_taxonomy'] ) ) {
                            $attribute_taxonomy_name = $attribute['name'];
                            $product_term_ids        = wc_get_product_terms( $product_id, $attribute_taxonomy_name, array( 'fields' => 'ids' ) );
                            if ( ! empty( $product_term_ids ) && is_array( $product_term_ids ) ) {
                                foreach ( $product_term_ids as $product_term_id ) {
                                    $product_attributes_ids[] = $product_term_id;
                                }
                            }
                        }
                    }
                }

            }

        }

        return $product_attributes_ids;
    }

    function wcac_get_option($key)
    {
        return sanitize_text_field( trim( get_option( $key ) ) );
    }

