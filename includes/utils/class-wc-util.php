<?php
	/**
	 * Helper wo tord wih WooCommerce
	 *
	 * @package Auto_Applied_Coupons\Utils
	 */

	namespace Auto_Applied_Coupons\Utils;

	defined( 'ABSPATH' ) || exit;

class WC_Util {

	/**
	 * Minimun WooCommerce plugin version.
	 *
	 * @var string $minimum_wc_version
	 */
	public static $minimum_wc_version = '6.4.0';

	/**
	 * Check is WooCommerce installed.
	 *
	 * @return bool
	 */
	public static function is_wc_installed() {
		$active_plugins = (array) get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}
		$woocommerce_active = in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );

		if ( ! $woocommerce_active || version_compare( get_option( 'woocommerce_db_version' ), self::$minimum_wc_version, '<' ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Should the coupon be applied to the cart with the product.
	 *
	 * @return bool
	 */
	public static function should_apply_coupon() {

		if ( is_cart() || is_checkout() || is_admin() || ! self::should_make_sale() ) {
			return false;
		}

		return true;
	}

	/**
	 * Should sale price after a coupon be displayed.
	 *
	 * @return bool
	 */
	public static function should_make_sale() {
		$show_coupons_block = apply_filters( 'wcac_show_available_coupons', true );
		if ( $show_coupons_block ) {
			return apply_filters( 'wcac_apply_coupon_to_price', false );
		}
		return false;
	}
}
