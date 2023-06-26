<?php
    if ( empty($coupon_object) ) {
        return;
    }

    $coupon_data = WCAC_Coupon::get_meta_data( $coupon_object );

    $description = $coupon_object->get_description();
    $is_applied = false;

    $card_classes = ['wcac-coupon-card--wrapper'];
    if ( $applied_coupon == $coupon_object->get_code() ) {
        $is_applied = true;
    }

    $card_classes = apply_filters('wcac_coupon_card_classes', $card_classes, $coupon_object, $applied_coupon);

    $card_classes = implode(' ', $card_classes);
    $item_id = 'wcac-coupon-card__' . esc_attr($coupon_object->get_id())
?>
<div class="wcac-coupon-card--wrapper">
    <input type="radio"
           id="<?php echo esc_attr($item_id); ?>"
           name="wcac-current-coupon-code"
        <?php checked( $coupon_object->get_code(), $applied_coupon ); ?>
           value="<?php echo esc_attr($coupon_object->get_code()); ?>"
    >
    <label for="<?php echo esc_attr($item_id); ?>" class="wcac-coupon-card--label">
        <div class="wcac-coupon-amount">
            <div class="wcac-coupon-amount--type">
                <?php echo esc_html($coupon_data['coupon_type']); ?>
            </div>
            <div class="wcac-coupon-amount--value">
                <?php echo apply_filters('wcac_coupon_amount_display', $coupon_data['coupon_amount'], $coupon_object); ?>
            </div>
        </div>
        <div class="wcac-coupon-code">
            <?php echo esc_html( $coupon_object->get_code() ); ?>
        </div>
        <?php
            if ( isset($coupon_expiry) ) {
                $expiry_date_string = WCAC_Coupon::get_expiry_date_string( $coupon_expiry );
                ?>
                <div>
                    <strong><?php esc_html_e('Expiry date: ', 'wcac'); ?></strong>
                    <?php echo esc_html($expiry_date_string); ?>
                </div>
                <?php
            }
        ?>
    </label>
</div>
