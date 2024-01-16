<?php
	/**
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

		public function is_public();

	}