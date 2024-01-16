<?php
/**
 * Actions Interface
 *
 * @package Auto_Applied_Coupons\Interfaces
 */

	namespace Auto_Applied_Coupons\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Interface Actions_Interface
 */
interface Actions_Interface {

	/**
	 * Return the actions to register.
	 *
	 * @return array
	 */
	public function get_actions();
}
