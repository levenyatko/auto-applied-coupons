<?php

	namespace Auto_Applied_Coupons\Hooks\Filters;

	use Auto_Applied_Coupons\Interfaces\Filters_Interface;
	use Auto_Applied_Coupons\Models\Product_Coupons_List;

	class Product_Coupons_List_Filter implements Filters_Interface{

		/**
		 * @inheritDoc
		 */
		public function get_filters() {
			return array(
				'wcac_available_coupons_for_product' => array('get_product_coupons', 10, 3),
			);
		}

		public function get_product_coupons( $coupons_list, $product, $ignore_cached ) {
			$product_coupons_list = new Product_Coupons_List( $product );
			return $product_coupons_list->get( $ignore_cached );
		}
	}