<?php

    class WCAC_Coupon
    {
        /**
         * @param $coupon
         * @return DateTime|WC_DateTime|null
         */
        public static function get_expired_date( $coupon )
        {
            $expiry_coupon_date = null;

            $expiry_date = $coupon->get_date_expires();

            if ($expiry_date) {

                if ($expiry_date instanceof WC_DateTime) {
                    $expiry_coupon_date = $expiry_date;
                } else {
                    if (is_int($expiry_date)) {
                        $expiry_coupon_date = new DateTime('Y-m-d', $expiry_date);
                    } else {
                        $expiry_coupon_date = new DateTime(date('Y-m-d', $expiry_date));
                    }
                }
            }

            return $expiry_coupon_date;
        }

        /**
         * @param $expiry_date
         * @return string
         */
        public static function get_expiry_date_string($expiry_date)
        {
            if ( empty($expiry_date) ) {
                $expiry_date = __( 'Never expires', 'wcac' );
            } else {
                $expiry_date = $expiry_date->format('d F, Y');
            }

            return $expiry_date;
        }

        public static function filter_code( $coupon, $product_id )
        {
            $applied_coupon_code = '';

            $applied_coupon =  new WC_Coupon( $coupon );

            $coupon_expires = self::get_expired_date($applied_coupon);

            if ( $coupon_expires ) {
                $coupon_expires = $coupon_expires->getTimestamp();
                if ( $coupon_expires <= time() ) {

                    // update coupons list if current coupon expires
                    $updated_list = WCAC_Product::get_available_coupons( $product_id );

                    if ( isset( $updated_list['apply']['coupon_code'] ) ) {
                        return $updated_list['apply']['coupon_code'];
                    }

                    return '';
                }
            }

            $product = wc_get_product( $product_id );

            if ( $applied_coupon->is_valid_for_product( $product ) ) {
                $applied_coupon_code  = $coupon;
            }

            return $applied_coupon_code;
        }

        /**
         * Formatted coupon data for display
         */
        public static function get_meta_data( $coupon )
        {
            global $store_credit_label;

            $all_discount_types = wc_get_coupon_types();

            $coupon_id     = ( ! empty( $coupon ) && is_callable( array( $coupon, 'get_id' ) ) ) ? $coupon->get_id() : 0;
            $coupon_amount = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_amount' ) ) ) ? $coupon->get_amount() : 0;
            $discount_type = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_discount_type' ) ) ) ? $coupon->get_discount_type() : '';

            $coupon_data = array();
            switch ( $discount_type ) {
                case 'smart_coupon':
                    $coupon_data['coupon_type']   = ! empty( $store_credit_label['singular'] ) ? ucwords( $store_credit_label['singular'] ) : __( 'Store Credit', 'wcac' );
                    $coupon_data['coupon_amount'] = wc_price( $coupon_amount );
                    break;

                case 'fixed_cart':
                    $coupon_data['coupon_type']   = __( 'Cart Discount', 'wcac' );
                    $coupon_data['coupon_amount'] = wc_price( $coupon_amount );
                    break;

                case 'fixed_product':
                    $coupon_data['coupon_type']   = __( 'Product Discount', 'wcac' );
                    $coupon_data['coupon_amount'] = wc_price( $coupon_amount );
                    break;

                case 'percent_product':
                    $coupon_data['coupon_type']   = __( 'Product Discount', 'wcac' );
                    $coupon_data['coupon_amount'] = $coupon_amount . '%';
                    break;

                case 'percent':
                    $coupon_data['coupon_type']   = __( 'Discount', 'wcac' );
                    $coupon_data['coupon_amount'] = $coupon_amount . '%';
                    $max_discount                 = get_post_meta( $coupon_id, 'wcac_max_discount', true );
                    if ( ! empty( $max_discount ) && is_numeric( $max_discount ) ) {
                        /* translators: %s: Maximum coupon discount amount */
                        $coupon_data['coupon_type'] .= ' ' . sprintf( __( ' upto %s', 'wcac' ), wc_price( $max_discount ) );
                    }
                    break;

                default:
                    $coupon_data['coupon_type']   = apply_filters( 'wcac_coupon_type', '', $coupon, $all_discount_types );
                    $coupon_data['coupon_amount'] = $coupon_amount;
                    break;

            }
            return $coupon_data;
        }

    }