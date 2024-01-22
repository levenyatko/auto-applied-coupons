<?php
	/**
	 * Helper to work with plugin transient data.
	 *
	 * @package Auto_Applied_Coupons\Utils
	 */

	namespace Auto_Applied_Coupons\Utils;

	defined( 'ABSPATH' ) || exit;

class Transient_Util {

	/**
	 * Function to clear all plugin transient data.
	 *
	 * @return void
	 */
	public static function clear_plugin_transients() {
		$all_keys = self::get_available_transient_keys();

		if ( ! empty( $all_keys ) ) {
			foreach ( $all_keys as $key ) {
				delete_transient( $key );
			}
		}
	}

	/**
	 * Get all plugin transient data keys in DB.
	 *
	 * @return array|string[]
	 */
	private static function get_available_transient_keys() {
		global $wpdb;

		$prefix = $wpdb->esc_like( '_transient_wcac_product_' );
		$keys   = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT `option_name` FROM %i WHERE `option_name` LIKE %s',
				$wpdb->options,
				$prefix . '%'
			),
			ARRAY_A
		);

		if ( is_wp_error( $keys ) ) {
			return array();
		}

		return array_map(
			function ( $key ) {
				return ltrim( $key['option_name'], '_transient_' );
			},
			$keys
		);
	}
}
