<?php
	namespace Auto_Applied_Coupons\Admin\Postmeta;

	use Auto_Applied_Coupons\Admin\Interfaces\Post_Meta_Interface;
	use Auto_Applied_Coupons\Models\WCAC_Coupon;
	use Auto_Applied_Coupons\Interfaces\Actions_Interface;
	use Auto_Applied_Coupons\Utils\Product_Attributes_Util;

	defined( 'ABSPATH' ) || exit;

	class Coupon_Usage_Restrictions_Meta implements Actions_Interface, Post_Meta_Interface {

		public static $include_meta_key = 'wcac_include_attr_ids';

		public static $exclude_meta_key = 'wcac_exclude_attr_ids';

		/**
		 * @inheritDoc
		 */
		public function get_actions() {
			return array(
				'woocommerce_coupon_options_usage_restriction' => array('display', 10, 2),
				'save_post' => array('save', 10, 2),
			);
		}

		public function display( $coupon_id = 0, $coupon = null ) {

			$selected_include_attrs = array();
			$selected_exclude_attrs = array();

			if ( ! empty( $coupon_id ) ) {
				$wcac_coupon = new WCAC_Coupon($coupon);
				$selected_include_attrs = $wcac_coupon->get_included_attributes();
				$selected_exclude_attrs = $wcac_coupon->get_excluded_attributes();
			}

			$all_attributes = Product_Attributes_Util::get_all();

			$this->select_field_display(
				self::$include_meta_key,
				__( 'Include attributes', 'wcac' ),
				$all_attributes,
				$selected_include_attrs
			);

			$this->select_field_display(
				self::$exclude_meta_key,
				__( 'Exclude attributes', 'wcac' ),
				$all_attributes,
				$selected_exclude_attrs
			);

		}

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

			$product_attribute_ids = [];
			if ( isset( $_POST[ self::$include_meta_key ] ) ) {
				$product_attribute_ids = wc_clean( wp_unslash( $_POST[ self::$include_meta_key ] ) );
			}
			update_post_meta( $post_id, self::$include_meta_key, $product_attribute_ids );

			$product_attribute_ids = [];
			if ( isset( $_POST[ self::$exclude_meta_key ] ) ) {
				$product_attribute_ids = wc_clean( wp_unslash( $_POST[ self::$exclude_meta_key ] ) );
			}
			update_post_meta( $post_id, self::$exclude_meta_key, $product_attribute_ids );
		}

		private function select_field_display($field_name, $field_label, $options, $selected_values) {
			include dirname( __FILE__ ) . '/views/field-select-product-attribute.php';
		}

	}