<?php
	/**
	 * New tab with plugin options on the WC settings page.
	 *
	 * @package Auto_Applied_Coupons\Admin\Options
	 */

	namespace Auto_Applied_Coupons\Admin\Options;

	use Auto_Applied_Coupons\Interfaces\Actions_Interface;
	use Auto_Applied_Coupons\Interfaces\Filters_Interface;
	use Auto_Applied_Coupons\Admin\Interfaces\Settings_Tab_Interface;

	defined( 'ABSPATH' ) || exit;

class Settings_Tab implements Settings_Tab_Interface, Actions_Interface, Filters_Interface {

	/**
	 * The prefix for subscription settings
	 *
	 * @var string $option_prefix
	 */
	public static $option_prefix = 'wcac';

	/**
	 * The WooCommerce settings tab name
	 *
	 * @var string $tab_name
	 */
	public static $tab_name = 'auto-coupons';

	/**
	 * Return the actions to register.
	 *
	 * @return array
	 */
	public function get_actions() {
		$actions = array();

		$actions[ 'woocommerce_settings_' . self::$tab_name ]       = array( 'display' );
		$actions[ 'woocommerce_update_options_' . self::$tab_name ] = array( 'update' );

		return $actions;
	}

	/**
	 * Return the filters to register.
	 *
	 * @return array
	 */
	public function get_filters() {
		return array(
			'woocommerce_settings_tabs_array' => array( 'add_tab', 50 ),
		);
	}

	/**
	 * Add custom tab to the WC settings.
	 *
	 * @param string $tabs Tabs list.
	 *
	 * @return array
	 */
	public function add_tab( $tabs ) {
		$tabs[ self::$tab_name ] = __( 'Coupons', 'wcac' );

		return $tabs;
	}

	/**
	 * Settings list for custom tab
	 *
	 * @return array
	 */
	public static function get_settings() {
		return array(
			array(
				'name' => _x( 'Auto Coupons Settings', 'options section heading', 'wcac' ),
				'type' => 'title',
				'desc' => __( 'Settings for Auto Applied Coupons plugin.', 'wcac' ),
				'id'   => self::$option_prefix . '_settings_title',
			),
			array(
				'title'           => __( 'Display settings', 'wcac' ),
				'desc'            => __( 'Show available coupons', 'wcac' ),
				'id'              => self::$option_prefix . '_available_display',
				'type'            => 'checkbox',
				'checkboxgroup'   => 'start',
				'show_if_checked' => 'option',
				'desc_tip'        => __( 'Available coupons block will be added to the product detail page.', 'wcac' ),
			),
			array(
				'desc'            => __( 'Apply coupon automatically', 'wcac' ),
				'id'              => self::$option_prefix . '_auto_apply_coupon',
				'default'         => 'no',
				'type'            => 'checkbox',
				'desc_tip'        => __( 'Selected coupon will be added to the cart with the product', 'wcac' ),
				'show_if_checked' => 'yes',
				'checkboxgroup'   => '',
				'autoload'        => false,
			),
			array(
				'desc'            => __( 'Show price after coupon is applied', 'wcac' ),
				'id'              => self::$option_prefix . '_make_price_sale',
				'default'         => 'no',
				'type'            => 'checkbox',
				'desc_tip'        => __( 'All products with available coupons will be displayed on sale with price after coupon is applied', 'wcac' ),
				'show_if_checked' => 'yes',
				'checkboxgroup'   => 'end',
				'autoload'        => false,
			),
			array(
				'title'    => __( 'Available coupons block title', 'wcac' ),
				'id'       => self::$option_prefix . '_front_block_title',
				'default'  => __( 'Available coupons', 'wcac' ),
				'type'     => 'text',
				'desc_tip' => __( 'Title for coupons block displayed on the product page', 'wcac' ),
				'autoload' => false,
			),
			array(
				'title'    => __( 'Base color', 'wcac' ),
				'desc'     => __( 'The main coupon background color.', 'wcac' ),
				'id'       => self::$option_prefix . '_coupon_bg_color',
				'type'     => 'color',
				'css'      => 'width:6em;',
				'default'  => '#ffba00',
				'autoload' => false,
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'Text color', 'wcac' ),
				'desc'     => __( 'The main coupon text color.', 'wcac' ),
				'id'       => self::$option_prefix . '_coupon_text_color',
				'type'     => 'color',
				'css'      => 'width:6em;',
				'default'  => '#515151',
				'autoload' => false,
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'Active color', 'wcac' ),
				'desc'     => __( 'The active coupon background color.', 'wcac' ),
				'id'       => self::$option_prefix . '_active_coupon_bg_color',
				'type'     => 'color',
				'css'      => 'width:6em;',
				'default'  => '#a46497',
				'autoload' => false,
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'Active text color', 'wcac' ),
				'desc'     => __( 'The active coupon text color.', 'wcac' ),
				'id'       => self::$option_prefix . '_active_coupon_text_color',
				'type'     => 'color',
				'css'      => 'width:6em;',
				'default'  => '#ffffff',
				'autoload' => false,
				'desc_tip' => true,
			),
			array(
				'title'   => __( 'Clear coupons cache mode', 'wcac' ),
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'options' => array(
					'auto'   => __( 'Auto', 'wcac' ),
					'manual' => __( 'Manual', 'wcac' ),
				),
				'id'      => self::$option_prefix . '_clear_cache_mode',
			),
			array(
				'type' => 'sectionend',
				'id'   => self::$option_prefix . '_settings_section',
			),
		);
	}

	/**
	 * Custom tab display.
	 *
	 * @return void
	 */
	public function display() {
		woocommerce_admin_fields( self::get_settings() );
		wp_nonce_field( self::$option_prefix . '_settings', '_wcacnonce', false );
	}

	/**
	 * Save custom settings values.
	 *
	 * @return void
	 */
	public function update() {
		if ( ! isset( $_POST['_wcacnonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wcacnonce'] ) ), self::$option_prefix . '_settings' )
		) {
			return;
		}

		woocommerce_update_options( self::get_settings() );
	}
}
