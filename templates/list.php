
<div class="wcac-coupons-list--wrap">
    <p class="wcac-coupons-list--heading">
        <strong><?php echo esc_html__('Available Coupons:', 'wcac'); ?></strong>
    </p>
    <div class="wcac-coupons-list--items">
        <?php
            foreach ( $coupons as $coupon_id ) {

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
            }
        ?>
        <div class="wcac-loader--wrap" id="wcac-coupons-list-loader">
            <span class="wcac-loader"></span>
        </div>
    </div>
</div>