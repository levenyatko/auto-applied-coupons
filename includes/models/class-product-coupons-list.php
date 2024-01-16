<?php
	namespace Auto_Applied_Coupons\Models;

	defined( 'ABSPATH' ) || exit;

class Product_Coupons_List {

	private $product_id;

	private $wc_product;

	private $product_transient;

	public function __construct( $product ) {

		if ( is_object($product) ) {
			$this->wc_product = $product;
		} else {
			$this->wc_product = wc_get_product( $product );
		}

		$this->product_id = $this->wc_product->get_id();

		$this->product_transient = new Product_Transient( $this->product_id );
	}

	/**
	 * @param array $coupons_list
	 * @param int $ignore_cached
	 *
	 * @return array|false
	 */
	public function get ( $ignore_cached = 0 ) {

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
	 * @param int $product_id
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
	 * @param int|WC_Product $product Product object or id.
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
