<?php
	/**
	 * Block with available coupons displayed on the product page.
	 *
	 * @package Auto_Applied_Coupons\Public
	 */

	namespace Auto_Applied_Coupons\Public;

	use Auto_Applied_Coupons\Interfaces\Actions_Interface;
	use Auto_Applied_Coupons\Plugin_Options;
	use Auto_Applied_Coupons\Utils\WC_Util;

	defined( 'ABSPATH' ) || exit;

class Coupons_Block implements Actions_Interface {

	/**
	 * An instance with plugin options.
	 *
	 * @var Plugin_Options $plugin_options
	 */
	private $plugin_options;

	/**
	 * Class constructor.
	 *
	 * @param Plugin_Options $plugin_options Class with plugin's options.
	 */
	public function __construct( Plugin_Options $plugin_options ) {
		$this->plugin_options = $plugin_options;
	}

	/**
	 * Return the actions to register.
	 *
	 * @return array
	 */
	public function get_actions() {
		$actions = array();

		$show_coupons_block = apply_filters( 'wcac_show_available_coupons', true );
		if ( $show_coupons_block ) {
			$actions = array(
				'woocommerce_after_add_to_cart_button' => array( 'display', 11 ),
				'wp_enqueue_scripts'                   => array( 'enqueue' ),
				'wp_print_styles'                      => array( 'print_styles', 10 ),
			);

			$auto_apply_coupon = apply_filters( 'wcac_auto_apply_coupon', false );
			if ( $auto_apply_coupon ) {
				// add coupon to cart with product.
				$actions['woocommerce_add_to_cart'] = array( 'apply_coupon', 10, 6 );
			}
		}

		return $actions;
	}

	/**
	 * Show coupons list block.
	 *
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

	/**
	 * Enqueue scripts and styles for the block.
	 *
	 * @return void
	 */
	public function enqueue() {
		if ( is_singular( 'product' ) ) {
			wp_enqueue_style( 'wcac-style', WCAC_PLUGIN_URL . 'css/frontend.css', false, '2.0' );

			wp_register_script( 'wcac-script', WCAC_PLUGIN_URL . 'js/coupons.js', false, '2.0', true );
			wp_localize_script(
				'wcac-script',
				'wcac_vars',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'wcac_ajax' ),
					'settings' => array(
						'shouldChangePrice' => WC_Util::should_make_sale(),
					),
				)
			);
			wp_enqueue_script( 'wcac-script' );
		}
	}

	/**
	 * Print inline styles with selected colors.
	 *
	 * @return void
	 */
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

	/**
	 * Add coupon to cart with product.
	 *
	 * @param string  $cart_id ID of the item in the cart.
	 * @param integer $product_id ID of the product added to the cart.
	 * @param integer $quantity Quantity of the item added to the cart.
	 * @param integer $variation_id Variation ID of the product added to the cart.
	 * @param array   $variation Array of variation data.
	 * @param array   $cart_item_data Array of other cart item data.
	 *
	 * @return void
	 */
	public function apply_coupon( $cart_id, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		if ( isset( $_POST['wcac-current-coupon-code'] ) ) {
			$coupon_code = sanitize_text_field( wp_unslash( $_POST['wcac-current-coupon-code'] ) );
			WC()->cart->apply_coupon( $coupon_code );
		}
	}
}
