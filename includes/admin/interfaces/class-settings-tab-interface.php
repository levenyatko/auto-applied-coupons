<?php
	/**
	 * Interface for custom WC settings tab.
	 *
	 * @package Auto_Applied_Coupons\Admin\Interfaces
	 */

	namespace Auto_Applied_Coupons\Admin\Interfaces;

	defined( 'ABSPATH' ) || exit;

interface Settings_Tab_Interface {
	/**
	 * Add custom tab to the WC settings.
	 *
	 * @param string $tabs Tabs list.
	 *
	 * @return array
	 */
	public function add_tab( $tabs );

	/**
	 * Custom tab display.
	 *
	 * @return void
	 */
	public function display();

	/**
	 * Settings list for custom tab
	 *
	 * @return array
	 */
	public static function get_settings();

	/**
	 * Save custom settings values.
	 *
	 * @return void
	 */
	public function update();
}
