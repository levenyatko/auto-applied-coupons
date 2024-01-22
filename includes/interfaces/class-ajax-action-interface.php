<?php
	/**
	 * Interface for ajax action class.
	 *
	 * @package Auto_Applied_Coupons\Interfaces
	 */

	namespace Auto_Applied_Coupons\Interfaces;

	defined( 'ABSPATH' ) || exit;

interface AJAX_Action_Interface {

	/**
	 * Action callback function.
	 *
	 * @return mixed
	 */
	public function callback();

	/**
	 * Is action public.
	 *
	 * @return bool
	 */
	public function is_public();
}
