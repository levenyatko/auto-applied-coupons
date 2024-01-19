<?php
    namespace Auto_Applied_Coupons\Public;

	use Auto_Applied_Coupons\Interfaces\Actions_Interface;
	use Auto_Applied_Coupons\Plugin_Options;
	use Auto_Applied_Coupons\Utils\WC_Util;

	defined( 'ABSPATH' ) || exit;

class Coupons_Block implements Actions_Interface {

	private $plugin_options;

	public function __construct(Plugin_Options $plugin_options) {
		$this->plugin_options = $plugin_options;
	}

	/**
	 * @inheritDoc
	 */
	public function get_actions() {
        $actions = array();

		$show_coupons_block = apply_filters('wcac_show_available_coupons', true);
		if ( $show_coupons_block ) {
			$actions = array(
				'woocommerce_after_add_to_cart_button' => array('display', 11),
                'wp_enqueue_scripts'                   => array('enqueue'),
                'wp_print_styles'                      => array('print_styles', 10),
			);

			$auto_apply_coupon = apply_filters('wcac_auto_apply_coupon', false);
			if ( $auto_apply_coupon ) {
				// add coupon to cart with product
				$actions[ 'woocommerce_add_to_cart' ] = array( 'apply_coupon', 10, 6 );
			}
		}

        return $actions;
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
            'block_title' => $this->plugin_options->get( 'wcac_front_block_title' ),
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

	public function print_styles() {
		$base_bg     = $this->plugin_options->get( 'wcac_coupon_bg_color' );
		$base_text   = $this->plugin_options->get( 'wcac_coupon_text_color' );
		$accent_bg   = $this->plugin_options->get( 'wcac_active_coupon_bg_color' );
		$accent_text = $this->plugin_options->get( 'wcac_active_coupon_text_color' );
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

	public function apply_coupon( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		if ( isset( $_POST['wcac-current-coupon-code'] ) ) {
			WC()->cart->apply_coupon( $_POST['wcac-current-coupon-code'] );
		}
	}

}
