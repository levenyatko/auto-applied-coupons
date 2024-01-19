<?php
/**
 * Hooks manager class
 *
 * @package Auto_Applied_Coupons
 */

	namespace Auto_Applied_Coupons;

	use Auto_Applied_Coupons\Interfaces\Actions_Interface;
	use Auto_Applied_Coupons\Interfaces\Filters_Interface;
	use Auto_Applied_Coupons\Utils\General_Util;

	defined( 'ABSPATH' ) || exit;

class Hooks_Manager {

	/**
	 * Register an object.
	 *
	 * @param object $obj Class object which actions should be registered.
	 *
	 * @return void
	 */
	public function register( $obj ) {
		if ( $obj instanceof Actions_Interface ) {
			$this->register_actions( $obj );
		}

		if ( $obj instanceof Filters_Interface ) {
			$this->register_filters( $obj );
		}
	}

	/**
	 * Register the actions of the given object.
	 *
	 * @param object $obj Class object which actions should be registered.
	 *
	 * @return void
	 */
	private function register_actions( $obj ) {
		$actions = $obj->get_actions();

		foreach ( $actions as $action_name => $action_details ) {
			$method        = $action_details[0];
			$priority      = General_Util::default_value( $action_details[1], 10 );
			$accepted_args = General_Util::default_value( $action_details[2], 1 );

			add_action(
				$action_name,
				array( $obj, $method ),
				$priority,
				$accepted_args
			);
		}
	}

	/**
	 * Register the filters of the given object.
	 *
	 * @param object $obj Class object which filters should be registered.
	 *
	 * @return void
	 */
	private function register_filters( $obj ) {
		$filters = $obj->get_filters();

		foreach ( $filters as $filter_name => $filter_details ) {
			$method        = $filter_details[0];
			$priority      = General_Util::default_value( $filter_details[1], 10 );
			$accepted_args = General_Util::default_value( $filter_details[2], 1 );

			add_filter(
				$filter_name,
				array( $obj, $method ),
				$priority,
				$accepted_args
			);
		}
	}
}
