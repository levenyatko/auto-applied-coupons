<?php
	/**
	 *
	 * @class Options_Util
	 * @package Auto_Applied_Coupons\Utils
	 */

	namespace Auto_Applied_Coupons\Utils;

	defined( 'ABSPATH' ) || exit;

	class Options_Util{
		/**
		 * Return the given value if it's set, otherwise return the default one.
		 *
		 * @param mixed $value Value to get.
		 * @param mixed $default_value Default value if $value wasn't set.
		 *
		 * @return mixed
		 */
		public static function default_value( &$value, $default_value ) {
			if ( isset( $value ) ) {
				return $value;
			}

			if ( isset( $default_value ) ) {
				return $default_value;
			}

			return null;
		}

		/**
		 * Get plugin option.
		 *
		 * @param string $key Option name to get value.
		 *
		 * @return mixed
		 */
		public static function get_option( $key ) {
			return sanitize_text_field( trim( get_option( $key ) ) );
		}
	}