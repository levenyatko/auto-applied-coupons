<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WCAC_Plugin' ) ) {

    class WCAC_Plugin {

        private static $instance;

        private static $frontend;
        private static $restrictions;
        private static $transient;

        public function __clone() {}

        public function __wakeup() {}

        private function __construct()
        {
            $this->load_dependencies();
            $this->init_hooks();
            $this->run();
        }

        public static function instance()
        {
            if (
                self::$instance === null ||
                ! self::$instance instanceof self
            ) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Include necessary classes
         *
         * @return void
         */
        private function load_dependencies()
        {
            require_once WCAC_PLUGIN_DIR . 'includes/class-wcac-coupon-restrictions.php';
            require_once WCAC_PLUGIN_DIR . 'includes/class-wcac-product.php';
            require_once WCAC_PLUGIN_DIR . 'includes/class-wcac-coupon.php';
            require_once WCAC_PLUGIN_DIR . 'includes/class-wcac-transient.php';
            require_once WCAC_PLUGIN_DIR . 'includes/class-wcac-transient-controller.php';
            require_once WCAC_PLUGIN_DIR . 'includes/class-wcac-frontend.php';
            require_once WCAC_PLUGIN_DIR . 'includes/class-wcac-settings.php';
            require_once WCAC_PLUGIN_DIR . 'includes/class-wcac-ajax-controller.php';
        }

        private function init_hooks()
        {
            add_filter('wcac_available_coupons_for_product', [WCAC_Product::class, 'get_coupons'], 10, 3);

            add_filter('wcac_is_coupon_relevant', [WCAC_Coupon::class, 'is_relevant'], 10, 2);
        }

        /**
         * @return void
         */
        private function run()
        {
            self::$restrictions = new WCAC_Coupon_Restrictions();
            self::$transient = new WCAC_Transient_Controller();
            self::$frontend = new WCAC_Frontend();

            self::$restrictions->hooks();
            self::$transient->hooks();
            self::$frontend->hooks();

            WCAC_Ajax_Controller::init_hooks();
        }

    }
}
