<?php
    $block_title = wcac_get_option( 'wcac_front_block_title' );
?>
<br>
<div class="wcac-coupons-list--wrap" style="display: none">
    <?php if ( ! empty($block_title) ) { ?>
        <p class="wcac-coupons-list--heading">
            <strong><?php echo esc_html($block_title); ?></strong>
        </p>
    <?php } ?>
    <div class="wcac-coupons-list-items--wrap">
        <div class="wcac-coupons-list--items" id="wcac-coupons-list-items"></div>
        <div class="wcac-loader--wrap" id="wcac-coupons-list-loader">
            <span class="wcac-loader"></span>
        </div>
    </div>
</div>