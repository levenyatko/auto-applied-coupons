<?php

	namespace Auto_Applied_Coupons\Hooks\Filters;

	use Auto_Applied_Coupons\Interfaces\Filters_Interface;
	use Auto_Applied_Coupons\Models\WCAC_Product;
	use Auto_Applied_Coupons\Utils\WC_Util;

	class Get_Price_Html_Filter implements Filters_Interface{

		/**
		 * @inheritDoc
		 */
		public function get_filters() {
			return array(
				'woocommerce_get_price_html' => array('get_product_price_html', 100, 2),
			);
		}

		public function get_product_price_html( $price, $product ) {
			if ( is_cart() || is_checkout() || is_admin() || ! WC_Util::should_make_sale() ) {
				return $price;
			}

			$wcac_product = new WCAC_Product($product);
			return $wcac_product->get_price_html( $price );
		}
	}