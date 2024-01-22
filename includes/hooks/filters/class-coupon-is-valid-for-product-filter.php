<?php
	/**
	 * Filter if coupon is valid for product.
	 *
	 * @package Auto_Applied_Coupons\Hooks\Filters
	 */

	namespace Auto_Applied_Coupons\Hooks\Filters;

	use Auto_Applied_Coupons\Interfaces\Filters_Interface;
	use Auto_Applied_Coupons\Models\WCAC_Coupon;
	use Auto_Applied_Coupons\Models\WCAC_Product;
	use WC_Discounts;

	defined( 'ABSPATH' ) || exit;

class Coupon_Is_Valid_For_Product_Filter implements Filters_Interface {

	/**
	 * Return the filters to register.
	 *
	 * @return array
	 */
	public function get_filters() {
		return array(
			'woocommerce_coupon_is_valid_for_product' => array( 'is_coupon_valid', 20, 4 ),
			'woocommerce_coupon_is_valid'             => array( 'is_valid_non_product_type_coupon', 20, 3 ),
		);
	}

	/**
	 * Check is coupon with custom rules is valid for product.
	 *
	 * @param bool             $valid   Old valid value.
	 * @param \WC_Product|null $product Product to check validity for.
	 * @param \WC_Coupon|null  $coupon  Coupon which could be valid for product.
	 * @param array            $values  Additional data.
	 *
	 * @return bool|mixed
	 */
	public function is_coupon_valid( $valid = false, $product = null, $coupon = null, $values = null ) {

		if ( empty( $product ) || empty( $coupon ) ) {
			return $valid;
		}

		$coupon_id = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_id' ) ) ) ? $coupon->get_id() : 0;

		if ( ! empty( $coupon_id ) ) {

			$wcac_coupon = new WCAC_Coupon( $coupon );

			if ( $wcac_coupon->is_expired() ) {
				return false;
			}

			$include_attribute_ids = $wcac_coupon->get_included_attributes( $coupon_id );
			$exclude_attribute_ids = $wcac_coupon->get_excluded_attributes( $coupon_id );

			if ( ! empty( $include_attribute_ids ) || ! empty( $exclude_attribute_ids ) ) {

				$wcac_product                  = new WCAC_Product( $product );
				$current_product_attribute_ids = $wcac_product->get_attributes();

				// check if product has allowed attributes.
				$have_included_attr = false;

				if ( ! empty( $include_attribute_ids ) && is_array( $include_attribute_ids ) ) {
					$common_attribute_ids = array_intersect( $include_attribute_ids, $current_product_attribute_ids );
					if ( count( $common_attribute_ids ) > 0 ) {
						$have_included_attr = true;
					}
				}

				// check if product has excluded attributes.
				$have_excluded_attr = false;

				if ( ! empty( $exclude_attribute_ids ) && is_array( $exclude_attribute_ids ) ) {
					$common_exclude_attribute_ids = array_intersect( $exclude_attribute_ids, $current_product_attribute_ids );
					if ( count( $common_exclude_attribute_ids ) > 0 ) {
						$have_excluded_attr = true;
					}
				}

				$valid = ( $have_included_attr && ! $have_excluded_attr ) ? true : false;
			}
		}

		return $valid;
	}

	/**
	 * Check is coupon with custom rules is valid for cart.
	 *
	 * @param bool          $valid     Old valid value.
	 * @param \WC_Coupon    $coupon    Coupon for validation.
	 * @param \WC_Discounts $discounts Available discoupons.
	 *
	 * @return mixed|true
	 * @throws \Exception If coupon is not applicable to the product.
	 */
	public function is_valid_non_product_type_coupon( $valid = true, $coupon = null, $discounts = null ) {
		// If coupon is already invalid, no need for further checks.
		if ( true !== $valid ) {
			return $valid;
		}

		if ( ! is_a( $coupon, 'WC_Coupon' ) ) {
			return $valid;
		}

		$coupon_id = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_id' ) ) ) ? $coupon->get_id() : 0;

		if ( empty( $coupon_id ) ) {
			return $valid;
		} else {
			$wcac_coupon = new WCAC_Coupon( $coupon );

			$product_attribute_ids         = $wcac_coupon->get_included_attributes();
			$exclude_product_attribute_ids = $wcac_coupon->get_excluded_attributes();

			// If product attributes are not set in coupon, stop further processing and return from here.
			if ( empty( $product_attribute_ids ) && empty( $exclude_product_attribute_ids ) ) {
				return $valid;
			}
		}

		$discount_type = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_discount_type' ) ) ) ? $coupon->get_discount_type() : '';

		if ( ! in_array( $discount_type, wc_get_product_coupon_types(), true ) ) {
			// Proceed if it is non product type coupon.

			if ( class_exists( 'WC_Discounts' ) && isset( WC()->cart ) ) {

				$wc_cart           = WC()->cart;
				$wc_discounts      = new WC_Discounts( $wc_cart );
				$items_to_validate = $wc_discounts->get_items_to_validate();

				if ( ! empty( $items_to_validate ) && is_array( $items_to_validate ) ) {
					$valid_products = array();
					foreach ( $items_to_validate as $item ) {
						$cart_item    = clone $item; // Clone the item so changes to wc_discounts item do not affect the originals.
						$item_product = isset( $cart_item->product ) ? $cart_item->product : null;
						if ( ! is_null( $item_product ) ) {
							if ( $coupon->is_valid_for_product( $item_product ) ) {
								$valid_products[] = $item_product;
							}
						}
					}

					// If cart does not have any valid product then throw Exception.
					if ( 0 === count( $valid_products ) ) {
						$error_message = __( 'Sorry, this coupon is not applicable to selected products.', 'wcac' );
						$error_code    = defined( 'E_WC_COUPON_NOT_APPLICABLE' ) ? E_WC_COUPON_NOT_APPLICABLE : 0;
						throw new \Exception( esc_html( $error_message ), (int) $error_code );
					}
				}
			}
		}

		return $valid;
	}
}
