<?php

	namespace Auto_Applied_Coupons\Hooks\Actions;

	use Auto_Applied_Coupons\Admin\Notices\Clear_Coupons_Cache_Notice;
	use Auto_Applied_Coupons\Interfaces\Actions_Interface;
	use Auto_Applied_Coupons\Models\Product_Transient;
	use Auto_Applied_Coupons\Utils\Options_Util;
	use Auto_Applied_Coupons\Utils\WCAC_Transient;

	class Clear_Coupons_Cache_Action implements Actions_Interface {

		public function get_actions() {
			$actions = array(
				'admin_notices' => array('display_notice'),
				'admin_post_wcac_clear_transient_action' => array('clear_transient_cache'),
			);

			$clear_cache_mode = Options_Util::get_option( 'wcac_clear_cache_mode' );
			$clear_cache_mode = apply_filters( 'wcac_clear_cache_mode', $clear_cache_mode );

			if ( 'manual' != $clear_cache_mode ) {
				$actions['save_post_shop_coupon'] = array('clear_on_save_coupon', 10, 3);
				$actions['save_post_product'] = array('clear_on_save_product', 10, 3);

				$actions['publish_to_trash'] = array('clear_on_status_change');
				$actions['trash_to_publish'] = array('clear_on_status_change');
			}

			return $actions;
		}

		public function display_notice() {
			Clear_Coupons_Cache_Notice::display();
		}

		public function clear_transient_cache() {
			WCAC_Transient::clear_plugin_transients();
			wp_redirect( admin_url( 'edit.php?post_type=shop_coupon' ) );
		}


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

			WCAC_Transient::clear_plugin_transients();
		}

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

			$product = wc_get_product($post_id);

			if ( is_a( $product, 'WC_Product_Variable' ) ) {
				$variations = $product->get_available_variations();
				$variations_id = wp_list_pluck( $variations, 'variation_id' );

				foreach ($variations_id as $variation_id) {
					$product_transient = new Product_Transient( $variation_id );
					$product_transient->delete();
				}
			} else {
				$product_transient = new Product_Transient( $post_id );
				$product_transient->delete();
			}
		}

		public function clear_on_status_change( $post ) {
			if ( ! empty($post->post_type) && 'shop_coupon' == $post->post_type ) {
				WCAC_Transient::clear_plugin_transients();
			}
		}

	}