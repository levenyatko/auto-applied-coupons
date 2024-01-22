<?php
	/**
	 * Coupon meta for general tab.
	 *
	 * @package Auto_Applied_Coupons\Admin\Postmeta
	 */

	namespace Auto_Applied_Coupons\Admin\Postmeta;

	use Auto_Applied_Coupons\Admin\Interfaces\Post_Meta_Interface;
	use Auto_Applied_Coupons\Interfaces\Actions_Interface;

	defined( 'ABSPATH' ) || exit;

class Coupon_General_Meta implements Actions_Interface, Post_Meta_Interface {

	/**
	 * Return the actions to register.
	 *
	 * @return array
	 */
	public function get_actions() {
		return array(
			'woocommerce_coupon_options' => array( 'display', 10, 2 ),
			'save_post'                  => array( 'save', 10, 2 ),
		);
	}

	/**
	 * Display meta fields.
	 *
	 * @param int             $post_id Coupon ID.
	 * @param \WC_Coupon|null $post Coupon Object.
	 *
	 * @return void
	 */
	public function display( $post_id = 0, $post = null ) {

		$show_coupon = get_post_meta( $post_id, 'wcac_show_coupon', true );

		woocommerce_wp_checkbox(
			array(
				'id'          => 'wcac_show_coupon',
				'label'       => __( 'Show on the product page', 'wcac' ),
				'description' => __( 'Check this box if the coupon should be displayed in allowed coupons block.', 'wcac' ),
				'value'       => wc_bool_to_string( $show_coupon ),
			)
		);
	}

	/**
	 * Save meta fields.
	 *
	 * @param int           $post_id Saved post ID.
	 * @param \WP_Post|null $post Post object.
	 *
	 * @return void
	 */
	public function save( $post_id = 0, $post = null ) {
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

		$show_coupon = false;
		if ( isset( $_POST['wcac_show_coupon'] ) ) {
			$show_coupon = wc_string_to_bool( sanitize_text_field( wp_unslash( $_POST['wcac_show_coupon'] ) ) );
		}
		update_post_meta( $post_id, 'wcac_show_coupon', $show_coupon );
	}
}
