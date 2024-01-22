<?php

namespace Auto_Applied_Coupons\AJAX\Actions;

use Auto_Applied_Coupons\Models\WCAC_Product;
use Auto_Applied_Coupons\Interfaces\AJAX_Action_Interface;

defined( 'ABSPATH' ) || exit;

class Get_Product_Sale_Price_AJAX_Action implements AJAX_Action_Interface {

	/**
	 * Action name.
	 *
	 * @var string $action_name
	 */
	public $action_name = 'wcac_get_sale_price';

	/**
	 * Action callback function.
	 *
	 * @return mixed
	 */
	public function callback() {

		$response = array( 'success' => false );

		if ( empty( $_GET['wcac_nonce'] ) || empty( $_GET['product_id'] ) || empty( $_GET['coupon'] ) ) {

			$response['message'] = esc_html__( 'One or more required fields are empty.', 'wcac' );

		} else {
			// check nonce.
			$nonce_value = sanitize_text_field( wp_unslash( $_GET['wcac_nonce'] ) );

			if ( wp_verify_nonce( $nonce_value, 'wcac_ajax' ) ) {

				if ( ! empty( $_GET['is_variation'] ) && ! empty( $_GET['variation_id'] ) ) {
					$product = wc_get_product( (int) $_GET['variation_id'] );
				} else {
					$product = wc_get_product( (int) $_GET['product_id'] );
				}

				if ( $product && is_callable( array( $product, 'get_id' ) ) ) {
					$coupon_code = sanitize_text_field( wp_unslash( $_GET['coupon'] ) );
					$coupon_id   = wc_get_coupon_id_by_code( $coupon_code );

					if ( $coupon_id ) {
						$wcac_product = new WCAC_Product( $product );

						$response['new_price_html'] = $wcac_product->get_price_html( $product->get_price() );
						$response['success']        = true;
					}
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
