<?php

namespace Auto_Applied_Coupons\Admin\Notices;

use Auto_Applied_Coupons\Admin\Interfaces\Admin_Notice_Interface;
use Auto_Applied_Coupons\Utils\WC_Util;

defined( 'ABSPATH' ) || exit;

class Required_Plugins_Notice implements Admin_Notice_Interface {

	/**
	 * Display notice.
	 *
	 * @return void
	 */
	public static function display() {
		if ( current_user_can( 'activate_plugins' ) ) {
			// translators: Installation required plugin notice.
			$admin_notice_content = __( 'The Auto Applied Coupons plugin requires WooCommerce %1$s or newer. Please install or update WooCommerce to version %1$s or newer.', 'wcac' );
			$admin_notice_content = sprintf( $admin_notice_content, WC_Util::$minimum_wc_version );
			?>
			<div class="notice notice-error">
				<p><?php echo esc_html( $admin_notice_content ); ?></p>
			</div>
			<?php
		}
	}
}
