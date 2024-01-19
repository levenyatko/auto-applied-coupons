<?php

	namespace Auto_Applied_Coupons\Hooks\Filters;

	use Auto_Applied_Coupons\Interfaces\Filters_Interface;

	class Coupon_Block_Custom_Filters implements Filters_Interface{

		private $plugin_options;

		public function __construct($plugin_options) {
			$this->plugin_options = $plugin_options;
		}

		/**
		 * @inheritDoc
		 */
		public function get_filters() {
			return array(
				'wcac_show_available_coupons' => array('is_coupons_block_enabled'),
				'wcac_apply_coupon_to_price'  => array('should_coupon_be_applied_to_price'),
				'wcac_auto_apply_coupon'      => array('should_coupon_be_added_to_cart'),
			);
		}

		/**
		 * If coupons frontend block should be displayed.
		 *
		 * @return bool
		 */
		public function is_coupons_block_enabled($show_coupons_block = false) {
			$show_available_coupons = $this->plugin_options->get( 'wcac_available_display' );

			if ( ! empty( $show_available_coupons ) && 'yes' == $show_available_coupons ) {
				return true;
			}

			return false;
		}

		public function should_coupon_be_applied_to_price($apply_coupon) {
			$make_sale = $this->plugin_options->get( 'wcac_make_price_sale' );

			if ( ! empty( $make_sale ) && 'yes' == $make_sale ) {
				return true;
			}

			return false;
		}

		public function should_coupon_be_added_to_cart($add_coupon) {
			$add_coupon = $this->plugin_options->get( 'wcac_auto_apply_coupon' );

			if ( ! empty( $add_coupon ) && 'yes' == $add_coupon ) {
				return true;
			}

			return false;
		}

	}