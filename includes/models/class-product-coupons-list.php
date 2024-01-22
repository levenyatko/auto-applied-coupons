<?php
	/**
	 * Procust coupons list data model class.
	 *
	 * @package Auto_Applied_Coupons\Models
	 */

	namespace Auto_Applied_Coupons\Models;

	defined( 'ABSPATH' ) || exit;

class Product_Coupons_List {

	/**
	 * WooCommerce product ID.
	 *
	 * @var int $product_id
	 */
	private $product_id;

	/**
	 * WooCommerce product object.
	 *
	 * @var \WC_Product $wc_product
	 */
	private $wc_product;

	/**
	 * Product transient data object.
	 *
	 * @var Product_Transient $product_transient
	 */
	private $product_transient;

	/**
	 * Class construct.
	 *
	 * @param int|\WC_Product   $product Product.
	 * @param Product_Transient $product_transient Product transient data.
	 */
	public function __construct( $product, $product_transient ) {

		if ( is_object( $product ) ) {
			$this->wc_product = $product;
		} else {
			$this->wc_product = wc_get_product( $product );
		}

		$this->product_id = $this->wc_product->get_id();

		$this->product_transient = $product_transient;
	}

	/**
	 * Get product available coupons list.
	 *
	 * @param int $ignore_cached If cached coupons should be ignored.
	 *
	 * @return array|false
	 */
	public function get( $ignore_cached = 0 ) {

		if ( ! $this->product_id ) {
			return false;
		}

		$ignore_cached = apply_filters( 'wcac_get_coupons_list_from_cache', $ignore_cached, $this->product_id );

		if ( ! $ignore_cached ) {
			$cached = $this->product_transient->get();

			if ( ! empty( $cached ) ) {
				return $cached;
			}
		}

		$coupons_list = $this->prepare();

		$this->product_transient->update( $coupons_list );

		return $coupons_list;
	}

	/**
	 * Update product available coupons list.
	 *
	 * @return void
	 */
	public function update() {
		if ( ! $this->product_id ) {
			return;
		}

		$coupons_list = $this->prepare();
		$this->product_transient->update( $coupons_list );
	}

	/**
	 * Prepare coupons list.
	 *
	 * @return array
	 */
	private function prepare() {
		$updated_list = array();

		if ( ! is_object( $this->wc_product ) || ! is_callable( array( $this->wc_product, 'get_id' ) ) ) {
			return array();
		}

		$args = array(
			'posts_per_page' => -1,
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'no_found_rows'  => true,
			'fields'         => 'ids',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => 'wcac_show_coupon',
					'value'   => '1',
					'compare' => '=',
				),
				array(
					'relation' => 'OR',
					array(
						'key'     => 'date_expires',
						'value'   => 'IS NULL',
						'compare' => '=',
					),
					array(
						'key'     => 'date_expires',
						'value'   => time(),
						'compare' => '>=',
						'type'    => 'NUMERIC',
					),
				),
			),
		);

		$coupons_query = new \WP_Query( $args );

		$available_product_coupons = array();

		if ( $coupons_query->have_posts() ) {
			foreach ( $coupons_query->posts as $coupon_id ) {

				$coupon = new \WC_Coupon( $coupon_id );

				if ( is_a( $this->wc_product, 'WC_Product_Variable' ) ) {

					$variations = $this->wc_product->get_visible_children();

					if ( $variations ) {
						foreach ( $variations as $child_id ) {
							$child_product = wc_get_product( $child_id );

							if ( $coupon->is_valid_for_product( $child_product )
								&& ! isset( $available_product_coupons[ $coupon->get_id() ] )
							) {
								$available_product_coupons[ $coupon->get_id() ] = $coupon;
							}
						}
					}
				} elseif ( $coupon->is_valid_for_product( $this->wc_product ) ) {
					$available_product_coupons[ $coupon->get_id() ] = $coupon;
				}
			}
		}

		if ( $available_product_coupons ) {
			foreach ( $available_product_coupons as $c ) {
				$updated_list[] = $c->get_id();
			}
		}

		return $updated_list;
	}
}
