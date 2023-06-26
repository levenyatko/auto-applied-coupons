<?php

    class WCAC_Transient
    {
        private static $transient_key_format = 'wcac_product_%s_coupons_cache';

        public static function get_transient_key($product_id)
        {
            return sprintf( self::$transient_key_format, $product_id );
        }

        public static function get_product_transient($product_id)
        {
            $cached = get_transient( self::get_transient_key( $product_id ) );

            if ( $cached !== false ) {
                return $cached;
            }

            return [];
        }

        public static function update_product_transient($product_id, $data)
        {
            $transient_key = self::get_transient_key( $product_id );

            // store for 3 hours
            set_transient( $transient_key, $data, 3 * HOUR_IN_SECONDS );
        }

        public static function delete_product_transient($product_id)
        {
            delete_transient( self::get_transient_key( $product_id ) );
        }

        public static function clear_transients()
        {
            $all_keys = self::get_available_transient_keys();

            if ( ! empty($all_keys) ) {
                foreach ($all_keys as $key) {
                    delete_transient( $key );
                }
            }
        }

        private static function get_available_transient_keys()
        {
            global $wpdb;

            $prefix = $wpdb->esc_like('_transient_wcac_product_');
            $sql = "SELECT `option_name` FROM $wpdb->options WHERE `option_name` LIKE '%s'";
            $keys = $wpdb->get_results($wpdb->prepare($sql, $prefix . '%'), ARRAY_A);

            if (is_wp_error($keys)) {
                return [];
            }

            return array_map(function($key) {
                return ltrim($key['option_name'], '_transient_');
            }, $keys);
        }
    }