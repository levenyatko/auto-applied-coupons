<?php
/**
 * Register plugin custom AJAX actions.
 *
 * @package Auto_Applied_Coupons\AJAX
 */

namespace Auto_Applied_Coupons\AJAX;

use Auto_Applied_Coupons\Interfaces\AJAX_Action_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Class AJAX_Actions_Registrar
 */
class AJAX_Actions_Registrar {

	/**
	 * Custom Ajax actions.
	 *
	 * @var AJAX_Action_Interface[] $ajax_actions
	 */
	private $ajax_actions;

	/**
	 * Class construct.
	 */
	public function __construct() {
		$this->ajax_actions = array();
	}

	/**
	 * Add new action to the list.
	 *
	 * @param AJAX_Action_Interface $action Action to add.
	 *
	 * @return void
	 */
	public function add_action( AJAX_Action_Interface $action ) {
		$this->ajax_actions[] = $action;
	}


	/**
	 * Register custom ajax actions.
	 *
	 * @return void
	 */
	public function register() {
		if ( ! empty( $this->ajax_actions ) ) {
			foreach ( $this->ajax_actions as $action ) {
				add_action( 'wp_ajax_' . $action->action_name, array( $action, 'callback' ) );

				if ( $action->is_public() ) {
					add_action( 'wp_ajax_nopriv_' . $action->action_name, array( $action, 'callback' ) );
				}
			}
		}
	}
}
