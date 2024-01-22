<?php
	/**
	 * Class to filter variation product prices.
	 *
	 * @package Auto_Applied_Coupons\Hooks\Filters
	 */

	namespace Auto_Applied_Coupons\Hooks\Filters;

	use Auto_Applied_Coupons\Interfaces\Filters_Interface;
	use Auto_Applied_Coupons\Models\WCAC_Product;
	use Auto_Applied_Coupons\Utils\WC_Util;

	defined( 'ABSPATH' ) || exit;

class Variation_Prices_Filter implements Filters_Interface {

	/**
	 * Return the filters to register.
	 *
	 * @return array
	 */
	public function get_filters() {
		return array(
			'woocommerce_variation_prices' => array( 'get_variation_prices', 30, 3 ),
		);
	}

	/**
	 * Get variation prices after coupon is applied.
	 *
	 * @param array       $price_hash    Prices array.
	 * @param \WC_Product $product Product to get prices for.
	 * @param bool        $for_display    If true, prices will be adapted for display.
	 *
	 * @return array
	 */
	public function get_variation_prices( $price_hash, $product, $for_display ) {
		if ( is_admin() || ! WC_Util::should_make_sale() ) {
			return $price_hash;
		}

		foreach ( $price_hash['price'] as $variation_id => $price ) {

			$product            = new WCAC_Product( $variation_id );
			$price_after_coupon = $product->get_price_after_active_coupon();

			if ( null !== $price_after_coupon ) {
				$price_hash['price'][ $variation_id ]      = $price_after_coupon;
				$price_hash['sale_price'][ $variation_id ] = $price_after_coupon;
			}
		}

		return $price_hash;
	}
}
