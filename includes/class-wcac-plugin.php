<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WCAC_Plugin' ) ) {

    class WCAC_Plugin {

        private static $instance;

        public function __clone() {}

        public function __wakeup() {}

        private function __construct()
        {
            $this->load_dependencies();
            $this->init_hooks();
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
            add_action('wcac_update_coupons_for_product', [WCAC_Product::class, 'update_coupons_list'], 10, 1);

            if ( wcac_should_make_sale() ) {
                add_filter('woocommerce_get_price_html',  [WCAC_Product::class, 'get_price_html'], 100, 2 );
                add_filter('woocommerce_variation_prices', [WCAC_Product::class, 'get_variation_prices'], 15, 3);
            }

            add_filter( 'posts_where' , 'wcac_where_query_allow_is_null' );

            wcac_add_price_hooks();

            WCAC_Frontend::init_hooks();
            WCAC_Ajax_Controller::init_hooks();

            $restrictions = new WCAC_Coupon_Restrictions();
            $restrictions->init_hooks();

            $transient = new WCAC_Transient_Controller();
            $transient->init_hooks();

        }

    }
}
