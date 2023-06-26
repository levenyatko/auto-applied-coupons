<?php

    defined( 'ABSPATH' ) || exit;

    class WCAC_Settings
    {
        /**
         * The prefix for subscription settings
         */
        public static $option_prefix = 'wcac';

        /**
         * The WooCommerce settings tab name
         */
        public static $tab_name = 'wcacoupons';

        public static function init()
        {

            add_filter( 'woocommerce_settings_tabs_array', [__CLASS__, 'add_tab'], 50 );

            add_action( 'woocommerce_settings_'.self::$tab_name, [__CLASS__, 'display'] );
            add_action( 'woocommerce_update_options_' . self::$tab_name,  [__CLASS__, 'update_settings'] );

        }

        public static function add_tab( $settings_tabs )
        {
            $settings_tabs[ self::$tab_name ] = __( 'Coupons', 'wcac' );

            return $settings_tabs;
        }

        public static function display()
        {
            woocommerce_admin_fields( self::get_settings() );
            wp_nonce_field( self::$option_prefix . '_settings', '_wcacnonce', false );
        }

        public static function get_settings()
        {
            return [
                [
                    'name'      => _x( 'Auto Coupons Settings', 'options section heading', 'wcdp' ),
                    'type'      => 'title',
                    'desc'      => 'Settings for Woocommerce Auto Coupons plugin.',
                    'id'        => self::$option_prefix . '_settings_title',
                ],
                [
                    'name'      => _x( 'Show available coupons', 'wcac' ),
                    'type'      => 'checkbox',
                    'id'        => self::$option_prefix . '_available_display',
                ],
                [
                    'name'      => _x( 'Add selected coupon to cart with the product', 'wcac' ),
                    'type'      => 'checkbox',
                    'id'        => self::$option_prefix . '_auto_apply_coupon',
                ],
                [
                    'name'      => _x( 'Show price after coupon is applied', 'wcac' ),
                    'type'      => 'checkbox',
                    'id'        => self::$option_prefix . '_make_price_sale',
                ],
                [
                    'title'     => _x( 'Clear coupons cache mode', 'wcac' ),
                    'type'      => 'select',
                    'class'     => 'wc-enhanced-select',
                    'options'   => [
                        'auto'    => __('Auto', 'wcac'),
                        'manual'  => __('Manual', 'wcac'),
                    ],
                    'id'        => self::$option_prefix . '_clear_cache_mode',
                ],
                [
                    'type'      => 'sectionend',
                    'id'        => self::$option_prefix . '_settings_section',
                ],
            ];

        }


        public static function update_settings()
        {
            if ( empty( $_POST['_wcacnonce'] ) || ! wp_verify_nonce( $_POST['_wcacnonce'], self::$option_prefix . '_settings' ) ) {
                return;
            }

            $settings = self::get_settings();

            woocommerce_update_options( $settings );
        }


    }

    WCAC_Settings::init();