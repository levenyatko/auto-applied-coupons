<?php
	/**
	 * Custom action to clear plugin coupons cache.
	 *
	 * @package Auto_Applied_Coupons\Hooks\Actions
	 */

	namespace Auto_Applied_Coupons\Hooks\Actions;

	use Auto_Applied_Coupons\Admin\Notices\Clear_Coupons_Cache_Notice;
	use Auto_Applied_Coupons\Interfaces\Actions_Interface;
	use Auto_Applied_Coupons\Models\Product_Transient;
	use Auto_Applied_Coupons\Plugin_Options;
	use Auto_Applied_Coupons\Utils\Transient_Util;

	defined( 'ABSPATH' ) || exit;

class Clear_Coupons_Cache_Action implements Actions_Interface {

	/**
	 * An instance with plugin options.
	 *
	 * @var Plugin_Options $plugin_options
	 */
	private $plugin_options;

	/**
	 * Class construct.
	 *
	 * @param Plugin_Options $plugin_options Class instance with plugin options.
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
		$actions = array(
			'admin_notices'                          => array( 'display_notice' ),
			'admin_post_wcac_clear_transient_action' => array( 'clear_transient_cache' ),
		);

		$clear_cache_mode = $this->plugin_options->get( 'wcac_clear_cache_mode' );
		$clear_cache_mode = apply_filters( 'wcac_clear_cache_mode', $clear_cache_mode );

		if ( 'manual' !== $clear_cache_mode ) {
			$actions['save_post_shop_coupon'] = array( 'clear_on_save_coupon', 10, 3 );
			$actions['save_post_product']     = array( 'clear_on_save_product', 10, 3 );

			$actions['publish_to_trash'] = array( 'clear_on_status_change' );
			$actions['trash_to_publish'] = array( 'clear_on_status_change' );
		}

		return $actions;
	}

	/**
	 * Display notice with clear cache button.
	 *
	 * @return void
	 */
	public function display_notice() {
		Clear_Coupons_Cache_Notice::display();
	}

	/**
	 * Clear plugin transient data.
	 *
	 * @return void
	 */
	public function clear_transient_cache() {
		Transient_Util::clear_plugin_transients();
		wp_safe_redirect( admin_url( 'edit.php?post_type=shop_coupon' ) );
	}

	/**
	 * Clear cache on save coupon action.
	 *
	 * @param int      $post_id Saved post ID.
	 * @param \WP_Post $post Saved post object.
	 * @param bool     $update Whether this is an existing post being updated.
	 *
	 * @return void
	 */
	public function clear_on_save_coupon( $post_id, $post, $update ) {
		if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		Transient_Util::clear_plugin_transients();
	}

	/**
	 * Clear cache on save product action.
	 *
	 * @param int      $post_id Saved post ID.
	 * @param \WP_Post $post Saved post object.
	 * @param bool     $update Whether this is an existing post being updated.
	 *
	 * @return void
	 */
	public function clear_on_save_product( $post_id, $post, $update ) {
		if ( empty( $post_id ) || empty( $post ) || empty( $_POST ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		$product = wc_get_product( $post_id );

		if ( is_a( $product, 'WC_Product_Variable' ) ) {
			$variations    = $product->get_available_variations();
			$variations_id = wp_list_pluck( $variations, 'variation_id' );

			foreach ( $variations_id as $variation_id ) {
				$product_transient = new Product_Transient( $variation_id );
				$product_transient->delete();
			}
		} else {
			$product_transient = new Product_Transient( $post_id );
			$product_transient->delete();
		}
	}

	/**
	 * Clear cache when post status is changed.
	 *
	 * @param \WP_Post $post Updated post.
	 *
	 * @return void
	 */
	public function clear_on_status_change( $post ) {
		if ( ! empty( $post->post_type ) && 'shop_coupon' === $post->post_type ) {
			Transient_Util::clear_plugin_transients();
		}
	}
}
