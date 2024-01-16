<?php

namespace Auto_Applied_Coupons\Admin\Notices;

use Auto_Applied_Coupons\Admin\Interfaces\Admin_Notice_Interface;

defined( 'ABSPATH' ) || exit;

class Clear_Coupons_Cache_Notice implements Admin_Notice_Interface {

	/**
	 * Display notice.
	 *
	 * @return void
	 */
	public static function display() {
		$screen = get_current_screen();

		if ( 'edit-shop_coupon' != $screen->id ) {
			return;
		}

		?>
        <div class="notice notice-info" style="display: flex; justify-content: space-between;">
            <p>
				<?php esc_html_e( 'Press the button to clear coupons cache', 'wcac' ); ?>
            </p>
            <form action="<?php echo esc_attr( admin_url( 'admin-post.php' ) ); ?>" style="margin-top: 3px;">
                <input type="hidden" name="action" value="wcac_clear_transient_action">
                <input type="submit" name="submit" class="button button-primary" value="<?php esc_html_e( 'Clear', 'wcac' ); ?>">
            </form>
        </div>
		<?php
	}
}
