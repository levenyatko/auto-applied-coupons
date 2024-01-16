<?php
/**
 * Filters Interface
 *
 * @package Auto_Applied_Coupons\Interfaces
 */

	namespace Auto_Applied_Coupons\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Interface Filters_Interface
 */
interface Filters_Interface {

	/**
	 * Return the filters to register.
	 *
	 * @return array
	 */
	public function get_filters();
}
