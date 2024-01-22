<?php
	/**
	 * Plugin options class.
	 *
	 * @package Auto_Applied_Coupons
	 */

	namespace Auto_Applied_Coupons;

	use Auto_Applied_Coupons\Utils\General_Util;

	defined( 'ABSPATH' ) || exit;

class Plugin_Options {

	/**
	 * Stored options.
	 *
	 * @var array $options
	 */
	private $options;

	/**
	 * Class construct.
	 */
	public function __construct() {

		$options = $this->get_default_options();
		foreach ( $options as $key => $default_value ) {
			$option_value = sanitize_text_field( trim( get_option( $key ) ) );
			$option_value = General_Util::default_value( $option_value, $default_value );

			$this->options[ $key ] = $option_value;
		}
	}

	/**
	 * Get default options.
	 *
	 * @return array
	 */
	private function get_default_options() {
		return array(
			'wcac_available_display'        => '',
			'wcac_auto_apply_coupon'        => 'no',
			'wcac_make_price_sale'          => 'no',
			'wcac_front_block_title'        => __( 'Available coupons', 'wcac' ),
			'wcac_coupon_bg_color'          => '#ffba00',
			'wcac_coupon_text_color'        => '#515151',
			'wcac_active_coupon_bg_color'   => '#a46497',
			'wcac_active_coupon_text_color' => '#ffffff',
			'wcac_clear_cache_mode'         => '',

		);
	}

	/**
	 * Return the option value based on the given option name.
	 *
	 * @param string $name Option name.
	 *
	 * @return mixed
	 */
	public function get( $name ) {
		if ( ! isset( $this->options[ $name ] ) ) {
			return null;
		}

		return $this->options[ $name ];
	}
}
