<?php

	namespace Auto_Applied_Coupons\Utils;

	defined( 'ABSPATH' ) || exit;

	class General_Util{
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

	}