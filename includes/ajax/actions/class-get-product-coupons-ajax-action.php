<?php

namespace Auto_Applied_Coupons\AJAX\Actions;

use Auto_Applied_Coupons\Interfaces\AJAX_Action_Interface;
use Auto_Applied_Coupons\Utils\General_Util;

defined( 'ABSPATH' ) || exit;

class Get_Product_Coupons_AJAX_Action implements AJAX_Action_Interface {

	/**
	 * Action name.
	 *
	 * @var string $action_name
	 */
	public $action_name = 'wcac_get_product_coupons';

	/**
	 * Action callback function.
	 *
	 * @return mixed
	 */
	public function callback() {

		$response = array( 'success' => false );

		if ( empty( $_GET['wcac_nonce'] ) || empty( $_GET['product_id'] ) ) {

			$response['message'] = __( 'One or more required fields are empty.', 'wcac' );

		} else {

			// check nonce.
			$nonce_value = sanitize_text_field( wp_unslash( $_GET['wcac_nonce'] ) );

			if ( wp_verify_nonce( $nonce_value, 'wcac_ajax' ) ) {

				if ( ! empty( $_GET['is_variation'] ) && ! empty( $_GET['variation_id'] ) ) {
					$product_id = (int) $_GET['variation_id'];
				} else {
					$product_id = (int) $_GET['product_id'];
				}

				$product = wc_get_product( $product_id );

				if ( $product && is_callable( array( $product, 'get_id' ) ) ) {
					ob_start();

					General_Util::show_available_coupons( $product->get_id() );

					$list_html = ob_get_contents();
					ob_end_clean();

					$response['coupons_html'] = $list_html;
					$response['success']      = true;
				}
			}
		}

		wp_send_json( $response );
	}

	/**
	 * Is action is public.
	 *
	 * @return true
	 */
	public function is_public() {
		return true;
	}
}
