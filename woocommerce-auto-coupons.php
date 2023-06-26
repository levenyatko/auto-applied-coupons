<?php
    /**
     * Plugin Name:       Woocommerce Auto Coupons
     * Description:       Takes the use of coupons to the next level.
     * Author:            Daria Levchenko
     * Author URI:        https://github.com/levenyatko
     * Version:           0.5.0
     * Text Domain:       wcac
     * Domain Path:       /languages
     * Tested up to:      6.2.2
     * Requires PHP:      7.4
     */

    defined( 'ABSPATH' ) || exit;

    if ( ! defined('WCAC_PLUGIN_DIR') ) {
        define( 'WCAC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
    }

    if ( ! defined('WCAC_PLUGIN_URL') ) {
        define( 'WCAC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
    }

    include_once WCAC_PLUGIN_DIR . '/includes/class-wcac-plugin.php';
    include_once WCAC_PLUGIN_DIR . '/includes/functions.php';

    wcac_get_instance();
