<?php

    class WCAC_Transient_Controller
    {
        /**
         * @return void
         */
        public function hooks()
        {
            $clear_cache_mode = wcac_get_option( 'wcac_clear_cache_mode' );
            $clear_cache_mode = apply_filters('wcac_clear_cache_mode', $clear_cache_mode);

            if ( 'manual' != $clear_cache_mode ) {

                add_action('save_post_product', [ $this, 'clear_product_transient' ], 10, 3);
                add_action('save_post_shop_coupon', [ $this, 'clear_products_transient' ], 10, 3);
                add_action('publish_to_trash', [ $this, 'clear_transient_in_status_change' ], 10, 1);
                add_action('trash_to_publish', [ $this, 'clear_transient_in_status_change' ], 10, 1);

            }

            add_action( 'admin_notices', [ $this, 'show_clear_notice'] );
            add_action('admin_post_wcac_clear_transient_cache_action', [ $this, 'clear_all_cache_action']);
        }

        /**
         * @param $post_id
         * @param $post
         * @param $update
         * @return void
         */
        public function clear_product_transient($post_id, $post, $update)
        {
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return;
            }

            WCAC_Transient::delete_product_transient($post_id);

        }

        /**
         * @param $post_id
         * @param $post
         * @param $update
         * @return void
         */
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

        /**
         * @param $post
         * @return void
         */
        public function clear_transient_in_status_change($post)
        {
            if ( 'shop_coupon' == $post->post_type ) {

                WCAC_Transient::clear_transients();

            }
        }

        /**
         * @return void
         */
        public function show_clear_notice()
        {
            $screen = get_current_screen();

            if ( 'edit-shop_coupon' != $screen->id )
                return;


            ?>
                <div class="notice notice-info" style="display: flex; justify-content: space-between;">
                    <p>
                        <?php esc_html_e('Press the button to clear coupons cache', 'wcac'); ?>
                    </p>
                    <form action="<?php echo esc_attr( admin_url( 'admin-post.php' ) ); ?>" style="margin-top: 3px;">
                        <input type="hidden" name="action" value="wcac_clear_transient_cache_action">
                        <input type="submit" name="submit" class="button button-primary" value="<?php esc_html_e('Clear', 'wcac') ?>">
                    </form>
                </div>
            <?php
        }

        public function clear_all_cache_action()
        {
            WCAC_Transient::clear_transients();

            wp_redirect( admin_url('edit.php?post_type=shop_coupon') );
        }

    }