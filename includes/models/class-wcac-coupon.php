<?php
	/**
	 *
	 * @class WCAC_Coupon
	 * @package Auto_Applied_Coupons\Models
	 */

	namespace Auto_Applied_Coupons\Models;

	defined( 'ABSPATH' ) || exit;

	class WCAC_Coupon {

		private $coupon_id;

		private $wc_coupon;

		public function __construct( $coupon ) {
			$this->wc_coupon = $coupon;
			$this->coupon_id = ( ! empty( $coupon ) && is_callable( array( $coupon, 'get_id' ) ) ) ? $coupon->get_id() : 0;
		}

		/**
		 * Formatted coupon data for display
		 */
		public function get_meta_data() {
			global $store_credit_label;

			$all_discount_types = wc_get_coupon_types();

			$coupon_amount = 0;
			$discount_type = '';

			if ( is_object( $this->wc_coupon ) ) {
				if ( is_callable( array( $this->wc_coupon, 'get_amount' ) ) ) {
					$coupon_amount = $this->wc_coupon->get_amount();
				}

				if ( is_callable( array( $this->wc_coupon, 'get_discount_type' ) ) ) {
					$discount_type = $this->wc_coupon->get_discount_type();
				}
			}

			$coupon_data = array();
			switch ( $discount_type ) {
				case 'smart_coupon':
					$coupon_data['coupon_type']   = ! empty( $store_credit_label['singular'] ) ? ucwords( $store_credit_label['singular'] ) : __( 'Store Credit', 'wcac' );
					$coupon_data['coupon_amount'] = wc_price( $coupon_amount );
					break;

				case 'fixed_cart':
					$coupon_data['coupon_type']   = __( 'Cart Discount', 'wcac' );
					$coupon_data['coupon_amount'] = wc_price( $coupon_amount );
					break;

				case 'fixed_product':
					$coupon_data['coupon_type']   = __( 'Product Discount', 'wcac' );
					$coupon_data['coupon_amount'] = wc_price( $coupon_amount );
					break;

				case 'percent_product':
					$coupon_data['coupon_type']   = __( 'Product Discount', 'wcac' );
					$coupon_data['coupon_amount'] = $coupon_amount . '%';
					break;

				case 'percent':
					$coupon_data['coupon_type']   = __( 'Discount', 'wcac' );
					$coupon_data['coupon_amount'] = $coupon_amount . '%';
					$max_discount                 = get_post_meta( $this->coupon_id, 'wcac_max_discount', true );
					if ( ! empty( $max_discount ) && is_numeric( $max_discount ) ) {
						/* translators: %s: Maximum coupon discount amount */
						$coupon_data['coupon_type'] .= ' ' . sprintf( __( ' upto %s', 'wcac' ), wc_price( $max_discount ) );
					}
					break;

				default:
					$coupon_data['coupon_type']   = apply_filters( 'wcac_coupon_type', '', $this->wc_coupon, $all_discount_types );
					$coupon_data['coupon_amount'] = $coupon_amount;
					break;

			}
			return $coupon_data;
		}

		public function get_included_attributes()
		{
			$value = get_post_meta( $this->coupon_id, 'wcac_include_attr_ids', true );
			if ( ! empty( $value ) ) {
				return (array)$value;
			}
			return [];
		}

		/**
		 * @param $coupon_id
		 * @return array|string[]
		 */
		public function get_excluded_attributes()
		{
			$value = get_post_meta( $this->coupon_id, 'wcac_exclude_attr_ids', true );
			if ( ! empty( $value ) ) {
				return (array)$value;
			}
			return [];
		}

	}