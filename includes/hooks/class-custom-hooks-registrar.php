<?php
	/**
	 * Registrar for hook and action callbacks.
	 *
	 * @package Auto_Applied_Coupons\Hooks
	 */

namespace Auto_Applied_Coupons\Hooks;

use Auto_Applied_Coupons\Interfaces\Actions_Interface;
use Auto_Applied_Coupons\Interfaces\Filters_Interface;

defined( 'ABSPATH' ) || exit;

class Custom_Hooks_Registrar {

	/**
	 * Plugin hooks manager class.
	 *
	 * @var Hooks_Manager $hooks_manager
	 */
	private $hooks_manager;

	/**
	 * Array of custom callbacks for actions.
	 *
	 * @var Actions_Interface[] $actions
	 */
	private $actions;

	/**
	 * Array of custom callbacks for filters.
	 *
	 * @var Filters_Interface[] $filters
	 */
	private $filters;

	/**
	 * Class construct.
	 *
	 * @param Hooks_Manager $hooks_manager Hooks manager object.
	 */
	public function __construct( $hooks_manager ) {
		$this->hooks_manager = $hooks_manager;
	}

	/**
	 * Add new action to the list.
	 *
	 * @param Actions_Interface $action Action to add.
	 *
	 * @return void
	 */
	public function add_action( Actions_Interface $action ) {
		$this->actions[] = $action;
	}

	/**
	 * Add new action to the list.
	 *
	 * @param Filters_Interface $filter Filter to add.
	 *
	 * @return void
	 */
	public function add_filter( Filters_Interface $filter ) {
		$this->filters[] = $filter;
	}

	/**
	 * Register custom ajax actions.
	 *
	 * @return void
	 */
	public function register() {

		if ( ! empty( $this->actions ) ) {
			foreach ( $this->actions as $action ) {
				$this->hooks_manager->register( $action );
			}
		}

		if ( ! empty( $this->filters ) ) {
			foreach ( $this->filters as $filter ) {
				$this->hooks_manager->register( $filter );
			}
		}
	}
}
