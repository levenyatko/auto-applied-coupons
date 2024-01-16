<?php
    namespace Auto_Applied_Coupons\Public;

	use Auto_Applied_Coupons\Interfaces\Actions_Interface;
	use Auto_Applied_Coupons\Interfaces\Filters_Interface;
	use Auto_Applied_Coupons\Models\WCAC_Coupon;
	use Auto_Applied_Coupons\Models\WCAC_Product;
	use Auto_Applied_Coupons\Utils\Options_Util;
	use Auto_Applied_Coupons\Utils\WC_Util;

	defined( 'ABSPATH' ) || exit;

class Coupons_Block implements Actions_Interface, Filters_Interface {

	/**
	 * @inheritDoc
	 */
	public function get_actions() {
        $actions = array();

		if ( $this->is_coupons_block_enabled() ) {
			$actions = array(
				'woocommerce_after_add_to_cart_button' => array('display', 11),
                'wp_enqueue_scripts'                   => array('enqueue'),
                'wp_print_styles'                      => array('print_styles', 10),
			);

			$auto_apply_coupon = Options_Util::get_option( 'wcac_auto_apply_coupon' );

			if ( ! empty( $auto_apply_coupon ) && 'yes' == $auto_apply_coupon ) {
				// apply coupon with product
				$actions[ 'woocommerce_add_to_cart' ] = array( 'apply_coupon', 10, 6 );
			}
		}

        return $actions;
	}

    public function get_filters() {
        return array(
            'wcac_show_available_coupons' => array('is_coupons_block_enabled'),
        );
    }

	/**
	 * If coupons frontend block should be displayed.
	 *
	 * @return bool
	 */
	public function is_coupons_block_enabled($show_coupons_block = false) {
		$show_available_coupons = Options_Util::get_option( 'wcac_available_display' );

		if ( ! empty( $show_available_coupons ) && 'yes' == $show_available_coupons ) {
			return true;
		}

		return false;
	}

	/**
     * Show coupons list block.
	 * @param int $product_id Product ID.
	 *
	 * @return void
	 */
	public function display( $product_id = 0 ) {
		if ( empty( $product_id ) ) {
			$product_id = get_the_ID();
		}

		$args = array(
			'product_id'  => $product_id,
            'block_title' => Options_Util::get_option( 'wcac_front_block_title' ),
		);

		wc_get_template( 'list.php', $args, 'coupons', WCAC_PLUGIN_DIR . 'templates/' );
	}

	public function enqueue() {
		if ( is_singular( 'product' ) ) {
			wp_enqueue_style( 'wcac-style', WCAC_PLUGIN_URL . 'css/frontend.css', false, null );

			wp_register_script( 'wcac-script', WCAC_PLUGIN_URL . 'js/coupons.js', false, null, true );
			wp_localize_script(
				'wcac-script',
				'wcac_vars',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce('wcac_ajax'),
					'settings' => array(
						'shouldChangePrice' => WC_Util::should_make_sale(),
					),
				)
			);
			wp_enqueue_script( 'wcac-script' );
		}
	}

	public static function print_styles() {
		$base_bg     = Options_Util::get_option( 'wcac_coupon_bg_color' );
		$base_text   = Options_Util::get_option( 'wcac_coupon_text_color' );
		$accent_bg   = Options_Util::get_option( 'wcac_active_coupon_bg_color' );
		$accent_text = Options_Util::get_option( 'wcac_active_coupon_text_color' );
		?>
			<style id="wcac-coupon-colors">
				:root {
					--wcac-main-bg-color: <?php echo esc_attr( $base_bg ); ?>;
					--wcac-main-text-color: <?php echo esc_attr( $base_text ); ?>;
					--wcac-accent-color: <?php echo esc_attr( $accent_bg ); ?>;
					--wcac-accent-text-color: <?php echo esc_attr( $accent_text ); ?>;
				}
			</style>
			<?php
	}

	public static function apply_coupon( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		if ( isset( $_POST['wcac-current-coupon-code'] ) ) {
			WC()->cart->apply_coupon( $_POST['wcac-current-coupon-code'] );
		}
	}

}
