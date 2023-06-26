<?php
    /**
     * Template to add coupon field in case
     * when coupons list hidden, but you should add coupon to the cart
     */
?>
<?php
    foreach ( $coupons as $coupon_id ) {

        $coupon =  new WC_Coupon( $coupon_id );

        if ( $applied_code == $coupon->get_code() ) {
            ?>
            <input type="hidden"
                   id="wcac-current-coupon-code-hidden"
                   name="wcac-current-coupon-code"
                   value="<?php echo esc_attr($coupon->get_code()); ?>"
            >
            <?php
            break;
        }
    }
?>