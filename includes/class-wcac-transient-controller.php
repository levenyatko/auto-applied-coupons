<?php

    class WCAC_Transient_Controller
    {
        public function hooks()
        {
            add_action( 'save_post_product', [ $this, 'clear_product_transient' ], 10, 3);
            add_action( 'save_post_shop_coupon', [ $this, 'clear_products_transient' ], 10, 3);
            add_action( 'publish_to_trash', [ $this, 'clear_transient_in_status_change' ], 10, 1);
            add_action( 'trash_to_publish', [ $this, 'clear_transient_in_status_change' ], 10, 1);
        }

        public function clear_product_transient($post_id, $post, $update)
        {
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return;
            }

            WCAC_Transient::delete_product_transient($post_id);

        }

        public function clear_products_transient($post_id, $post, $update)
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

            WCAC_Transient::clear_transients();

        }

        public function clear_transient_in_status_change($post)
        {
            if ( 'shop_coupon' == $post->post_type ) {

                WCAC_Transient::clear_transients();

            }
        }

    }