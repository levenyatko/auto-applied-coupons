<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WCAC_Plugin' ) ) {

    class WCAC_Plugin {

        private static $instance;

        private static $frontend;
        private static $restrictions;

        public function __clone() {}

        public function __wakeup() {}

        private function __construct()
        {
        }

        public static function instance()
        {
            if (
                self::$instance === null ||
                ! self::$instance instanceof self
            ) {

                self::$instance = new self();

                self::$instance->load_dependencies();
                self::$instance->run();

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
            require_once WCAC_PLUGIN_DIR . 'includes/class-wcac-frontend.php';

        }

        /**
         * @return void
         */
        private function run()
        {
            self::$restrictions = new WCAC_Coupon_Restrictions();
            self::$frontend = new WCAC_Frontend();

            self::$restrictions->hooks();
            self::$frontend->hooks();
        }

    }
}
