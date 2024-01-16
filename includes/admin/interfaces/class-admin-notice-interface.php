<?php
/**
 * Admin Notice Interface
 *
 * @package Auto_Applied_Coupons\Admin\Interfaces
 */

	namespace Auto_Applied_Coupons\Admin\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Interface Admin_Notice_Interface
 */
interface Admin_Notice_Interface {
	/**
	 * Display notice.
	 *
	 * @return void
	 */
	public static function display();
}
