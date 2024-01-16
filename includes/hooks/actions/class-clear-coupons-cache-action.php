<?php
	/**
	 *
	 * @class Clear_Coupons_Cache_Action
	 * @package Auto_Applied_Coupons\Hooks\actions
	 */

	namespace Auto_Applied_Coupons\Hooks\Actions;

	use Auto_Applied_Coupons\Admin\Notices\Clear_Coupons_Cache_Notice;
	use Auto_Applied_Coupons\Interfaces\Actions_Interface;
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
				$actions['save_post_product'] = array('clear_on_save_post', 10, 3);
				$actions['save_post_shop_coupon'] = array('clear_on_save_post', 10, 3);

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


		public function clear_on_save_post( $post_id, $post, $update ) {
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

		public function clear_on_status_change( $post ) {
			if ( ! empty($post->post_type) && 'shop_coupon' == $post->post_type ) {
				WCAC_Transient::clear_plugin_transients();
			}
		}

	}