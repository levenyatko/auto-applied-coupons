<?php

namespace Auto_Applied_Coupons\Hooks;

use Auto_Applied_Coupons\Interfaces\Actions_Interface;
use Auto_Applied_Coupons\Interfaces\Filters_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Class Custom_Hooks_Registrar
 */
class Custom_Hooks_Registrar {

	/**
	 * @var Hooks_Manager $hooks_manager
	 */
	private $hooks_manager;

	/**
	 * @var Actions_Interface[] $actions
	 */
	private $actions;

	/**
	 * @var Filters_Interface[] $filters
	 */
	private $filters;

	/**
	 * Class construct.
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
