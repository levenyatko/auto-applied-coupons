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

            add_filter( 'plugin_action_links_woocommerce-auto-coupons/woocommerce-auto-coupons.php', [__CLASS__, 'plugin_settings_link'] );

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
                    'name'      => _x( 'Auto Coupons Settings', 'options section heading', 'wcac' ),
                    'type'      => 'title',
                    'desc'      => __('Settings for Woocommerce Auto Coupons plugin.', 'wcac'),
                    'id'        => self::$option_prefix . '_settings_title',
                ],
                [
                    'title'           => __( 'Display settings', 'wcac' ),
                    'desc'            => __( 'Show available coupons', 'wcac' ),
                    'id'              => self::$option_prefix . '_available_display',
                    'type'            => 'checkbox',
                    'checkboxgroup'   => 'start',
                    'show_if_checked' => 'option',
                    'desc_tip'        => __( 'Available coupons block will be added to the product detail page.', 'wcac' ),
                ],
                [
                    'desc'            => __( 'Apply coupon automatically', 'wcac' ),
                    'id'              => self::$option_prefix . '_auto_apply_coupon',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'desc_tip'        => __( 'Selected coupon will be added to the cart with the product', 'wcac' ),
                    'show_if_checked' => 'yes',
                    'checkboxgroup'   => '',
                    'autoload'        => false,
                ],
                [
                    'desc'            => __( 'Show price after coupon is applied', 'wcac' ),
                    'id'              => self::$option_prefix . '_make_price_sale',
                    'default'         => 'no',
                    'type'            => 'checkbox',
                    'desc_tip'        => __( 'All products with available coupons will be displayed on sale with price after coupon is applied', 'wcac' ),
                    'show_if_checked' => 'yes',
                    'checkboxgroup'   => 'end',
                    'autoload'        => false,
                ],
                [
                    'title'           => __( 'Available coupons block title', 'wcac' ),
                    'id'              => self::$option_prefix . '_front_block_title',
                    'default'         => __( 'Available coupons', 'wcac' ),
                    'type'            => 'text',
                    'desc_tip'        => __( 'Title for coupons block displayed on the product page', 'wcac' ),
                    'autoload'        => false,
                ],
                [
                    'title'    => __( 'Base color', 'wcac' ),
                    'desc'     => __( 'The main coupon background color.', 'wcac' ),
                    'id'       => self::$option_prefix . '_coupon_bg_color',
                    'type'     => 'color',
                    'css'      => 'width:6em;',
                    'default'  => '#ffba00',
                    'autoload' => false,
                    'desc_tip' => true,
                ],
                [
                    'title'    => __( 'Text color', 'wcac' ),
                    'desc'     => __( 'The main coupon text color.', 'wcac' ),
                    'id'       => self::$option_prefix . '_coupon_text_color',
                    'type'     => 'color',
                    'css'      => 'width:6em;',
                    'default'  => '#515151',
                    'autoload' => false,
                    'desc_tip' => true,
                ],
                [
                    'title'    => __( 'Active color', 'wcac' ),
                    'desc'     => __( 'The active coupon background color.', 'wcac' ),
                    'id'       => self::$option_prefix . '_active_coupon_bg_color',
                    'type'     => 'color',
                    'css'      => 'width:6em;',
                    'default'  => '#a46497',
                    'autoload' => false,
                    'desc_tip' => true,
                ],
                [
                    'title'    => __( 'Active text color', 'wcac' ),
                    'desc'     => __( 'The active coupon text color.', 'wcac' ),
                    'id'       => self::$option_prefix . '_active_coupon_text_color',
                    'type'     => 'color',
                    'css'      => 'width:6em;',
                    'default'  => '#ffffff',
                    'autoload' => false,
                    'desc_tip' => true,
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

        public static function plugin_settings_link( $links )
        {
            $url = esc_url( admin_url('admin.php?page=wc-settings&tab=wcacoupons') );

            $settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';

            array_push(
                $links,
                $settings_link
            );
            return $links;
        }
    }

    WCAC_Settings::init();