<?php

    class WCAC_Coupon_Restrictions
    {
        public static $include_meta_key = 'wcac_include_attr_ids';
        public static $exclude_meta_key = 'wcac_exclude_attr_ids';

        public function hooks()
        {
            add_action( 'woocommerce_coupon_options_usage_restriction', [ $this, 'usage_restriction_fields' ], 10, 2 );
            add_action( 'save_post', [ $this, 'process_meta' ], 10, 2 );

            add_filter( 'woocommerce_coupon_is_valid_for_product', [ $this, 'is_valid_for_product' ], 11, 4 );
            add_filter( 'woocommerce_coupon_is_valid', [ $this, 'handle_non_product_type_coupons' ], 11, 3 );
            add_filter( 'wc_smart_coupons_export_headers', [ $this, 'export_headers' ] );
            add_filter( 'is_protected_meta', [ $this, 'make_action_meta_protected' ], 10, 3 );

        }

        /**
         * @param $coupon_id
         * @return array|string[]
         */
        public static function get_coupon_included_attributes($coupon_id)
        {
            $include_attr_ids_string = get_post_meta( $coupon_id, self::$include_meta_key, true );
            if ( ! empty( $include_attr_ids_string ) ) {
                return explode( ',', $include_attr_ids_string );
            }
            return [];
        }

        /**
         * @param $coupon_id
         * @return array|string[]
         */
        public static function get_coupon_excluded_attributes($coupon_id)
        {
            $exclude_attr_ids_string = get_post_meta( $coupon_id, self::$exclude_meta_key, true );
            if ( ! empty( $exclude_attr_ids_string ) ) {
                return explode( ',', $exclude_attr_ids_string );
            }
            return [];
        }

        /**
         * @param $coupon_id
         * @param $coupon
         * @return void
         */
        public function usage_restriction_fields( $coupon_id = 0, $coupon = null )
        {
            global $wp_version;

            $include_attribute_ids         = array();
            $exclude_attribute_ids = array();

            if ( ! empty( $coupon_id ) ) {
                $include_attribute_ids = self::get_coupon_included_attributes($coupon_id);
                $exclude_attribute_ids = self::get_coupon_excluded_attributes($coupon_id);
            }

            $attribute_taxonomies       = wc_get_attribute_taxonomies();
            $attribute_options          = array();
            $attribute_taxonomies_label = array();

            if ( ! empty( $attribute_taxonomies ) && is_array( $attribute_taxonomies ) ) {
                $attribute_taxonomies_name = array();
                foreach ( $attribute_taxonomies as $attribute_taxonomy ) {
                    $attribute_name  = isset( $attribute_taxonomy->attribute_name ) ? $attribute_taxonomy->attribute_name : '';
                    $attribute_label = isset( $attribute_taxonomy->attribute_label ) ? $attribute_taxonomy->attribute_label : '';
                    if ( ! empty( $attribute_name ) && ! empty( $attribute_label ) ) {
                        $attribute_taxonomy_name                                = wc_attribute_taxonomy_name( $attribute_name );
                        $attribute_taxonomies_name[]                            = $attribute_taxonomy_name;
                        $attribute_taxonomies_label[ $attribute_taxonomy_name ] = $attribute_label;
                    }
                }
                if ( ! empty( $attribute_taxonomies_name ) ) {
                    if ( version_compare( $wp_version, '4.5.0', '>=' ) ) {
                        $attribute_taxonomies_terms = get_terms([
                            'taxonomy' => $attribute_taxonomies_name,
                        ]);
                    } else {
                        $attribute_taxonomies_terms = get_terms( $attribute_taxonomies_name );
                    }
                    if ( ! empty( $attribute_taxonomies_terms ) && is_array( $attribute_taxonomies_terms ) ) {
                        foreach ( $attribute_taxonomies_terms as $attribute_taxonomy_term ) {
                            $attribute_taxonomy       = $attribute_taxonomy_term->taxonomy;
                            $attribute_taxonomy_label = isset( $attribute_taxonomies_label[ $attribute_taxonomy ] ) ? $attribute_taxonomies_label[ $attribute_taxonomy ] : '';
                            if ( empty( $attribute_taxonomy_label ) ) {
                                continue;
                            }
                            $attribute_term_id   = $attribute_taxonomy_term->term_id;
                            $attribute_term_name = $attribute_taxonomy_term->name;
                            $attribute_title     = __( 'Attribute=', 'wcac' ) . $attribute_taxonomy_label . ':' . __( 'Value=', 'wcac' ) . $attribute_term_name;
                            $attribute_label     = $attribute_taxonomy_label . ': ' . $attribute_term_name;

                            $attribute_options[ $attribute_term_id ] = array(
                                'title' => $attribute_title,
                                'label' => $attribute_label,
                            );
                        }
                    }
                }
            }

            $this->field_display(self::$include_meta_key, __( 'Include attributes', 'wcac' ), $attribute_options, $include_attribute_ids);
            $this->field_display(self::$exclude_meta_key, __( 'Exclude attributes', 'wcac' ), $attribute_options, $exclude_attribute_ids);

        }

        /**
         * @param $name
         * @param $label
         * @param $options
         * @param $values
         * @return void
         */
        private function field_display($name, $label, $options, $values)
        {
            ?>
                <div class="options_group wc-auto-coupons-field" style="background-color: #fdffef;">
                    <p class="form-field">
                        <label for="<?php echo esc_attr($name); ?>"><?php echo esc_html( $label ); ?></label>
                        <select id="<?php echo esc_attr($name); ?>" name="<?php echo esc_attr($name); ?>[]" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'No product attributes', 'wcac' ); ?>">
                            <?php
                                if ( ! empty( $options ) ) {
                                    foreach ( $options as $attribute_id => $attribute_data ) {
                                        $is_selected = selected( in_array( (string) $attribute_id, $values, true ), true, false );
                                        echo '<option title="' . esc_attr( $attribute_data['title'] ) . '" value="' . esc_attr( $attribute_id ) . '"' . esc_attr( $is_selected ) . '>' . esc_html( $attribute_data['label'] ) . '</option>';
                                    }
                                }
                            ?>
                        </select>
                    </p>
                </div>
            <?php
        }

        /**
         * Save new custom fields
         *
         * @param $post_id
         * @param $post
         * @return void
         */
        public function process_meta( $post_id = 0, $post = null )
        {
            if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) {
                return;
            }
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return;
            }
            if ( is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
                return;
            }
            if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( wc_clean( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) { // phpcs:ignore
                return;
            }
            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return;
            }
            if ( 'shop_coupon' !== $post->post_type ) {
                return;
            }

            // update include attr ids
            $product_attribute_ids = [];
            if ( isset( $_POST[ self::$include_meta_key ] ) ) {
                $product_attribute_ids = wc_clean( wp_unslash( $_POST[ self::$include_meta_key ] ) );
                $product_attribute_ids = implode( ',', $product_attribute_ids );
            }
            update_post_meta( $post_id, self::$include_meta_key, $product_attribute_ids );

            // update exclude attr ids
            $product_attribute_ids = [];
            if ( isset( $_POST[ self::$exclude_meta_key ] ) ) {
                $product_attribute_ids = wc_clean( wp_unslash( $_POST[ self::$exclude_meta_key ] ) );
                $product_attribute_ids = implode( ',', $product_attribute_ids );
            }
            update_post_meta( $post_id, self::$exclude_meta_key, $product_attribute_ids );
        }

        public function is_valid_for_product( $valid = false, $product = null, $coupon = null, $values = null )
        {
            // If coupon is already invalid, no need for further checks.
            if ( true !== $valid ) {
                return $valid;
            }

            if ( empty( $product ) || empty( $coupon ) ) {
                return $valid;
            }

            $coupon_id = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_id' ) ) ) ? $coupon->get_id() : 0;

            if ( ! empty( $coupon_id ) ) {

                $include_attribute_ids = self::get_coupon_included_attributes($coupon_id);
                $exclude_attribute_ids = self::get_coupon_excluded_attributes($coupon_id);

                if ( ! empty( $include_attribute_ids ) || ! empty( $exclude_attribute_ids ) ) {

                    $current_product_attribute_ids = wcac_get_product_attributes( $product );

                    // check if product has allowed attributes
                    $have_included_attr = true;

                    if ( ! empty( $include_attribute_ids ) && is_array( $include_attribute_ids ) ) {
                        $common_attribute_ids = array_intersect( $include_attribute_ids, $current_product_attribute_ids );
                        if ( count( $common_attribute_ids ) > 0 ) {
                            $have_included_attr = true;
                        } else {
                            $have_included_attr = false;
                        }
                    }

                    // check if product has excluded attributes
                    $have_excluded_attr = false;

                    if ( ! empty( $exclude_attribute_ids ) && is_array( $exclude_attribute_ids ) ) {
                        $common_exclude_attribute_ids = array_intersect( $exclude_attribute_ids, $current_product_attribute_ids );
                        if ( count( $common_exclude_attribute_ids ) > 0 ) {
                            $have_excluded_attr = true;
                        } else {
                            $have_excluded_attr = false;
                        }
                    }

                    $valid = ( $have_included_attr && ! $have_excluded_attr ) ? true : false;
                }
            }

            return $valid;
        }

        /**
         * Function to validate non product type coupons against product attribute restriction
         * We need to remove coupon if it does not pass attribute validation even for single cart item in case of non product type coupons e.g fixed_cart, smart_coupon since these coupon type require all products in the cart to be valid
         *
         * @param $valid
         * @param $coupon
         * @param $discounts
         * @return mixed|true
         * @throws Exception
         */
        public function handle_non_product_type_coupons( $valid = true, $coupon = null, $discounts = null )
        {
            // If coupon is already invalid, no need for further checks.
            if ( true !== $valid ) {
                return $valid;
            }

            if ( ! is_a( $coupon, 'WC_Coupon' ) ) {
                return $valid;
            }

            $coupon_id     = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_id' ) ) ) ? $coupon->get_id() : 0;
            $discount_type = ( is_object( $coupon ) && is_callable( array( $coupon, 'get_discount_type' ) ) ) ? $coupon->get_discount_type() : '';

            if ( ! empty( $coupon_id ) ) {
                $product_attribute_ids         = get_post_meta( $coupon_id, 'wc_ac_product_attribute_ids', true );
                $exclude_product_attribute_ids = get_post_meta( $coupon_id, 'wc_ac_exclude_product_attribute_ids', true );
                // If product attributes are not set in coupon, stop further processing and return from here.
                if ( empty( $product_attribute_ids ) && empty( $exclude_product_attribute_ids ) ) {
                    return $valid;
                }
            } else {
                return $valid;
            }

            $product_coupon_types = wc_get_product_coupon_types();

            // Proceed if it is non product type coupon.
            if ( ! in_array( $discount_type, $product_coupon_types, true ) ) {
                if ( class_exists( 'WC_Discounts' ) && isset( WC()->cart ) ) {

                    $wc_cart           = WC()->cart;
                    $wc_discounts      = new WC_Discounts( $wc_cart );
                    $items_to_validate = array();

                    if ( is_callable( array( $wc_discounts, 'get_items_to_validate' ) ) ) {
                        $items_to_validate = $wc_discounts->get_items_to_validate();
                    } elseif ( is_callable( array( $wc_discounts, 'get_items' ) ) ) {
                        $items_to_validate = $wc_discounts->get_items();
                    } elseif ( isset( $wc_discounts->items ) && is_array( $wc_discounts->items ) ) {
                        $items_to_validate = $wc_discounts->items;
                    }

                    if ( ! empty( $items_to_validate ) && is_array( $items_to_validate ) ) {
                        $valid_products   = array();
                        $invalid_products = array();
                        foreach ( $items_to_validate as $item ) {
                            $cart_item    = clone $item; // Clone the item so changes to wc_discounts item do not affect the originals.
                            $item_product = isset( $cart_item->product ) ? $cart_item->product : null;
                            $item_object  = isset( $cart_item->object ) ? $cart_item->object : null;
                            if ( ! is_null( $item_product ) && ! is_null( $item_object ) ) {
                                if ( $coupon->is_valid_for_product( $item_product, $item_object ) ) {
                                    $valid_products[] = $item_product;
                                } else {
                                    $invalid_products[] = $item_product;
                                }
                            }
                        }

                        // If cart does not have any valid product then throw Exception.
                        if ( 0 === count( $valid_products ) ) {
                            $error_message = __( 'Sorry, this coupon is not applicable to selected products.', 'wcac' );
                            $error_code    = defined( 'E_WC_COUPON_NOT_APPLICABLE' ) ? E_WC_COUPON_NOT_APPLICABLE : 0;
                            throw new Exception( $error_message, $error_code );
                        } elseif ( count( $invalid_products ) > 0 && ! empty( $exclude_product_attribute_ids ) ) {

                            $exclude_product_attribute_ids = explode( ',', $exclude_product_attribute_ids );
                            $excluded_products             = array();
                            foreach ( $invalid_products as $invalid_product ) {
                                $product_attributes = wc_ac_get_product_attributes( $invalid_product );
                                if ( ! empty( $product_attributes ) && is_array( $product_attributes ) ) {
                                    $common_exclude_attribute_ids = array_intersect( $exclude_product_attribute_ids, $product_attributes );
                                    if ( count( $common_exclude_attribute_ids ) > 0 ) {
                                        $excluded_products[] = $invalid_product->get_name();
                                    }
                                }
                            }

                            if ( count( $excluded_products ) > 0 ) {
                                // If cart contains any excluded product and it is being excluded from our excluded product attributes then throw Exception.
                                /* translators: 1. Singular/plural label for product(s) 2. Excluded product names */
                                $error_message = sprintf( __( 'Sorry, this coupon is not applicable to the %1$s: %2$s.', 'wcac' ), _n( 'product', 'products', count( $excluded_products ), 'wcac' ), implode( ', ', $excluded_products ) );
                                $error_code    = defined( 'E_WC_COUPON_EXCLUDED_PRODUCTS' ) ? E_WC_COUPON_EXCLUDED_PRODUCTS : 0;
                                throw new Exception( $error_message, $error_code );
                            }
                        }
                    }
                }
            }

            return $valid;
        }

        /**
         * Add meta in export headers for compatibility with WooCommerce Smart Coupons
         *
         * @param $headers
         * @return array|mixed
         */
        public function export_headers( $headers = array() )
        {
            $headers[ self::$include_meta_key ]         = __( 'Product Attributes', 'wсac' );
            $headers[ self::$exclude_meta_key ] = __( 'Exclude Attributes', 'wсac' );

            return $headers;

        }

        /**
         * Make new metadata protected
         *
         * @param $protected
         * @param $meta_key
         * @param $meta_type
         * @return bool|mixed
         */
        public function make_action_meta_protected( $protected = false, $meta_key = '', $meta_type = '' )
        {
            $sc_product_attribute_keys = [
                self::$include_meta_key,
                self::$exclude_meta_key
            ];

            if ( in_array( $meta_key, $sc_product_attribute_keys, true ) ) {
                return true;
            }

            return $protected;
        }

    }