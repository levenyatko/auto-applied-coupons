<?php
	/**
	 * Filters for custom coupons block.
	 *
	 * @package Auto_Applied_Coupons\Hooks\Filters
	 */

	namespace Auto_Applied_Coupons\Hooks\Filters;

	use Auto_Applied_Coupons\Interfaces\Filters_Interface;

	defined( 'ABSPATH' ) || exit;

class Coupon_Block_Custom_Filters implements Filters_Interface {

	/**
	 * An instance with plugin options.
	 *
	 * @var Plugin_Options $plugin_options
	 */
	private $plugin_options;

	/**
	 * Class construct.
	 *
	 * @param Plugin_Options $plugin_options An instance of a class with plugin options.
	 */
	public function __construct( $plugin_options ) {
		$this->plugin_options = $plugin_options;
	}

	/**
	 * Return the filters to register.
	 *
	 * @return array
	 */
	public function get_filters() {
		return array(
			'wcac_show_available_coupons' => array( 'is_coupons_block_enabled' ),
			'wcac_apply_coupon_to_price'  => array( 'should_coupon_be_applied_to_price' ),
			'wcac_auto_apply_coupon'      => array( 'should_coupon_be_added_to_cart' ),
		);
	}

	/**
	 * If coupons frontend block should be displayed.
	 *
	 * @param bool $show_coupons_block Value to filter.
	 *
	 * @return bool
	 */
	public function is_coupons_block_enabled( $show_coupons_block = false ) {
		$show_available_coupons = $this->plugin_options->get( 'wcac_available_display' );

		if ( ! empty( $show_available_coupons ) && 'yes' === $show_available_coupons ) {
			return true;
		}

		return false;
	}

	/**
	 * If product price should be displayed after coupon is applied to the product.
	 *
	 * @param bool $apply_coupon Value to filter.
	 *
	 * @return bool
	 */
	public function should_coupon_be_applied_to_price( $apply_coupon ) {
		$make_sale = $this->plugin_options->get( 'wcac_make_price_sale' );

		if ( ! empty( $make_sale ) && 'yes' === $make_sale ) {
			return true;
		}

		return false;
	}

	/**
	 * Should active coupon be added to the cart with product.
	 *
	 * @param bool $add_coupon Value to filter.
	 *
	 * @return bool
	 */
	public function should_coupon_be_added_to_cart( $add_coupon ) {
		$add_coupon = $this->plugin_options->get( 'wcac_auto_apply_coupon' );

		if ( ! empty( $add_coupon ) && 'yes' === $add_coupon ) {
			return true;
		}

		return false;
	}
}
