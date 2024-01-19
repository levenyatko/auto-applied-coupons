<?php
	/**
	 *
	 * @class WCAC_Product
	 * @package Auto_Applied_Coupons\Models
	 */

	namespace Auto_Applied_Coupons\Models;

	defined( 'ABSPATH' ) || exit;

	class WCAC_Product {

		private $product_id;

		private $wc_product;

		public function __construct( $product ) {

			if ( is_object($product) ) {
				$this->wc_product = $product;
			} elseif( is_int( $product ) ) {
				$this->wc_product = wc_get_product( $product );
			} else {
				$this->wc_product = null;
			}

			if (is_callable( array( $this->wc_product, 'get_id' ) ) ) {
				$this->product_id = $this->wc_product->get_id();
			} else {
				$this->product_id = 0;
			}
		}

		public function get_price_after_active_coupon() {
			$active_coupon = $this->get_active_coupon();

			if ( ! empty( $active_coupon ) ) {
				return $this->get_price_after_coupon( $active_coupon );
			}

			return null;
		}

		public function get_price_after_coupon( $coupon ) {
			if ( empty($this->wc_product) ) {
				return '';
			}

			$values = array(
				'data'     => $this->wc_product,
				'quantity' => 1,
			);

			$product_price   = $this->wc_product->get_price();
			$discount_amount = $coupon->get_discount_amount( $product_price, $values, true );
			$discount_amount = min( $product_price, $discount_amount );
			$_price          = max( $product_price - $discount_amount, 0 );

			return wc_format_decimal( $_price, wc_get_price_decimals() );
		}

		public function get_price_html( $price ) {

			if ( in_array( $this->wc_product::class, array( 'WC_Product_Variation', 'WC_Product_Simple' ) ) ) {

				$after_coupon_price = $this->get_price_after_active_coupon();

				if ( ! is_null($after_coupon_price) ) {
					$product_price = $this->wc_product->get_price();
					$price         = wc_format_sale_price( $product_price, $after_coupon_price );
				}

			}

			return $price;
		}

		public function get_active_coupon() {

			$cookie_name = 'wcac_product_' . $this->wc_product->get_id() . '_coupon';

			if ( ! empty( $_COOKIE[ $cookie_name ] ) ) {
				$code = $_COOKIE[ $cookie_name ];

				$coupon_id = wc_get_coupon_id_by_code( $code );
				$coupon    = new \WC_Coupon( $coupon_id );

				if ( is_object( $coupon ) && $coupon->is_valid_for_product( $this->wc_product ) ) {
					return $coupon;
				}
			}

			return null;
		}

		 public function get_attributes() {
			$product_attributes_ids = array();

			 if ( empty($this->wc_product) ) {
				 return $product_attributes_ids;
			 }

			$product_attributes = $this->wc_product->get_attributes();

			if ( ! empty( $product_attributes ) ) {

				if ( true === $this->wc_product->is_type( 'variation' ) ) {
					foreach ( $product_attributes as $variation_taxonomy => $variation_slug ) {
						$variation_attribute = get_term_by( 'slug', $variation_slug, $variation_taxonomy );
						if ( is_object( $variation_attribute ) ) {
							$product_attributes_ids[] = $variation_attribute->term_id;
						}
					}
				} elseif ( ! empty( $this->product_id ) ) {
					foreach ( $product_attributes as $attribute ) {
						if ( isset( $attribute['is_taxonomy'] ) && ! empty( $attribute['is_taxonomy'] ) ) {
							$attribute_taxonomy_name = $attribute['name'];
							$product_term_ids        = wc_get_product_terms( $this->product_id, $attribute_taxonomy_name, array( 'fields' => 'ids' ) );
							if ( ! empty( $product_term_ids ) && is_array( $product_term_ids ) ) {
								foreach ( $product_term_ids as $product_term_id ) {
									$product_attributes_ids[] = $product_term_id;
								}
							}
						}
					}
				}
			}

			return $product_attributes_ids;
		}

	}