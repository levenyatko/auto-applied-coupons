<?php
	/**
	 * Filter class for product available coupons.
	 *
	 * @package Auto_Applied_Coupons\Hooks\Filters
	 */

	namespace Auto_Applied_Coupons\Hooks\Filters;

	use Auto_Applied_Coupons\Interfaces\Filters_Interface;
	use Auto_Applied_Coupons\Models\Product_Coupons_List;
	use Auto_Applied_Coupons\Models\Product_Transient;

	defined( 'ABSPATH' ) || exit;

class Product_Coupons_List_Filter implements Filters_Interface {

	/**
	 * Return the filters to register.
	 *
	 * @return array
	 */
	public function get_filters() {
		return array(
			'wcac_available_coupons_for_product' => array( 'get_product_coupons', 10, 3 ),
		);
	}

	/**
	 * Get available coupons for product.
	 *
	 * @param array $coupons_list  Old coupons list to filter.
	 * @param int   $product WooCommerce product ID.
	 * @param bool  $ignore_cached  Should the cached coupons ignored.
	 *
	 * @return array|false
	 */
	public function get_product_coupons( $coupons_list, $product_id, $ignore_cached ) {
		$product_coupons_list = new Product_Coupons_List( $product_id, new Product_Transient( $product_id ) );
		return $product_coupons_list->get( $ignore_cached );
	}
}
