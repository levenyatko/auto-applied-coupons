<?php
	/**
	 * Main plugin class.
	 */

	namespace Auto_Applied_Coupons;

	use Auto_Applied_Coupons\Admin\Options\Settings_Tab;
	use Auto_Applied_Coupons\Admin\Postmeta\Coupon_General_Meta;
	use Auto_Applied_Coupons\Admin\Postmeta\Coupon_Usage_Restrictions_Meta;
	use Auto_Applied_Coupons\AJAX\AJAX_Actions_Registrar;
	use Auto_Applied_Coupons\Hooks\Custom_Hooks_Registrar;
	use Auto_Applied_Coupons\Utils\WC_Util;

	defined( 'ABSPATH' ) || exit;

	final class Plugin {

		/**
		 * Current plugin version.
		 *
		 * @var string $version
		 */
		public static $version = '2.0.0';

		/**
		 * An instance of the `Hooks_Manager` class.
		 *
		 * @var Hooks_Manager $hooks_manager
		 */
		public $hooks_manager;

		/**
		 * Ajax actions registrar class.
		 *
		 * @var AJAX_Actions_Registrar $ajax_actions
		 */
		public $ajax_actions;

		/**
		 * Initialize the plugin.
		 *
		 * @return void
		 */
		public function init() {
			$this->hooks_manager = new Hooks_Manager();

			$this->register_ajax_actions();
			$this->register_custom_meta();

			$this->register_custom_hooks();

			if ( is_admin() && WC_Util::is_wc_installed() ) {
				$this->hooks_manager->register( new Settings_Tab() );
			}

			$this->hooks_manager->register( new Public\Coupons_Block() );
		}

		private function register_ajax_actions() {
			$this->ajax_actions = new AJAX_Actions_Registrar();

			$this->ajax_actions->add_action( new AJAX\Actions\Get_Product_Coupons_AJAX_Action() );
			$this->ajax_actions->add_action( new AJAX\Actions\Get_Product_Sale_Price_AJAX_Action() );

			$this->ajax_actions->register();
		}

		private function register_custom_meta() {
			$this->hooks_manager->register( new Coupon_General_Meta() );
			$this->hooks_manager->register( new Coupon_Usage_Restrictions_Meta() );
		}

		private function register_custom_hooks() {
			$custom_hooks = new Custom_Hooks_Registrar( $this->hooks_manager );

			$custom_hooks->add_action( new Hooks\Actions\Clear_Coupons_Cache_Action() );

			$custom_hooks->add_filter( new Hooks\Filters\Database_Query_Filter() );
			$custom_hooks->add_filter( new Hooks\Filters\Product_Coupons_List_Filter() );
			$custom_hooks->add_filter( new Hooks\Filters\Coupon_Is_Valid_For_Product_Filter() );

			$custom_hooks->register();
		}

		private function init_hooks() {
			/*

			if ( wcac_should_make_sale() ) {
				add_filter( 'woocommerce_get_price_html', 'wcac_get_price_html_func', 100, 2 );
				add_filter( 'woocommerce_variation_prices', array( WCAC_Product::class, 'get_variation_prices' ), 15, 3 );
			}

			wcac_add_price_hooks();
*/
		}

		/**
		 * Run on plugin activation.
		 *
		 * @return void
		 */
		public function plugin_registration_hook() {
			if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
				exit( sprintf( esc_html__('The Auto Applied Coupons plugin requires PHP 8.0 or higher. You are currently using %s.', 'wcac'), PHP_VERSION ) );
			}

			if ( ! WC_Util::is_wc_installed() ) {
				exit( esc_html__('Please activate WooCommerce before activating Auto Applied Coupons.', 'wcac') );
			}
		}

		/**
		 * Display an admin notice, if not on the integration screen and if the account isn't yet connected.
		 *
		 * @return void
		 */
		public function maybe_display_admin_notices() {
			if ( ! WC_Util::is_wc_installed() ) {
				Auto_Applied_Coupons\Admin\Notices\Required_Plugins_Notice::display();
			}
		}

		/**
		 * Adds settings link to the plugins page.
		 *
		 * @param array $links Plugin links array.
		 *
		 * @return array mixed
		 */
		public function plugin_action_links( $links ) {
			$settings_url = admin_url( 'admin.php?page=wc-settings&tab=' . Settings_Tab::$tab_name );
			$settings_link = '<a href="' . esc_attr( $settings_url ) . '">' . esc_html__( 'Settings', 'wcac' ) . '</a>';
			array_unshift( $links, $settings_link );

			return $links;
		}

	}