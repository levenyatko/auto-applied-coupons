<?php
	/**
	 * Plugin Name:       Auto Applied Coupons
	 * Description:       Takes the use of coupons to the next level.
	 * Author:            Daria Levchenko
	 * Author URI:        https://github.com/levenyatko
	 * Version:           2.0.0
	 * Text Domain:       wcac
	 * Domain Path:       /languages
	 * Tested up to:      6.4.2
	 * WC tested up to:   8.5.0
	 * Requires PHP:      8.0
	 *
	 * @package Auto_Applied_Coupons
	 */

	defined( 'ABSPATH' ) || exit;

	if ( ! defined( 'WCAC_PLUGIN_DIR' ) ) {
		define( 'WCAC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
	}

	if ( ! defined( 'WCAC_PLUGIN_URL' ) ) {
		define( 'WCAC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	}

	add_action( 'before_woocommerce_init', function() {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	} );

	require_once WCAC_PLUGIN_DIR . '/includes/functions.php';

	require_once __DIR__ . '/includes/class-autoloader.php';

	if ( class_exists( 'Auto_Applied_Coupons\Autoloader' ) ) {
		$wcac_autoloader = new Auto_Applied_Coupons\Autoloader();
		$wcac_autoloader->register();
	}

	$aac_plugin = new Auto_Applied_Coupons\Plugin();

	add_action( 'plugins_loaded', array( $aac_plugin, 'init' ) );

	add_filter(
		'plugin_action_links_' . __FILE__,
		array(
			$aac_plugin,
			'plugin_action_links',
		)
	);

	register_activation_hook( __FILE__, array( $aac_plugin, 'plugin_registration_hook' ) );

	add_action( 'admin_notices', array( $aac_plugin, 'maybe_display_admin_notices' ) );
