<?php

	use Auto_Applied_Coupons\Models\WCAC_Product;

	function wcac_add_price_hooks() {
		add_filter( 'woocommerce_product_get_sale_price', array( WCAC_Product::class, 'get_sale_price' ), 100, 2 );
	}

	function wcac_remove_price_hooks() {
		remove_filter( 'woocommerce_product_get_sale_price', array( WCAC_Product::class, 'get_sale_price' ), 100, 2 );
	}

	function wcac_get_price_html_func( $price, $product ) {
		if ( is_cart() || is_checkout() || is_admin() || ! \Auto_Applied_Coupons\Utils\WC_Util::should_make_sale()() ) {
			return $price;
		}

		$price = WCAC_Product::get_price_html( $price, $product );

		return $price;
	}

	function wcac_show_available_coupons( $product_id, $ignore_cached = false ) {

		$coupons = apply_filters( 'wcac_available_coupons_for_product', array(), $product_id, $ignore_cached );

		if ( empty( $coupons ) ) {
			return;
		}

		$product = new WCAC_Product($product_id);

		$applied_coupon = $product->get_active_coupon();
		$applied_code   = '';

		if ( ! empty( $applied_coupon ) ) {
			$applied_code = $applied_coupon->get_code();
		}

		$displayed_count = apply_filters( 'wcac_coupons_count_to_show', 5 );

		$i = 0;
		foreach ( $coupons as $coupon_id ) {

			if ( $i >= $displayed_count ) {
				break;
			}

			$coupon = new \WC_Coupon( $coupon_id );

			$coupon_amount    = $coupon->get_amount();
			$is_free_shipping = ( $coupon->get_free_shipping() ) ? 'yes' : 'no';
			$discount_type    = $coupon->get_discount_type();

			$coupon_date_expires = $coupon->get_date_expires();

			$expiry_date      = null;
			$expiry_timestamp = '';

			if ( $coupon_date_expires ) {
				if ( $coupon_date_expires instanceof \WC_DateTime ) {
					$expiry_date = $coupon_date_expires;
				} elseif ( is_int( $coupon_date_expires ) ) {
					$expiry_date = new \DateTime( 'Y-m-d', $coupon_date_expires );
				} else {
					$expiry_date = new \DateTime( date( 'Y-m-d', $coupon_date_expires ) );
				}

				if ( ! empty( $expiry_date ) ) {
					$expiry_timestamp = $expiry_date->getTimestamp();
				}
			}

			$is_zero_amount_coupon = false;

			if ( ( empty( $coupon_amount ) ) && ( ( ! empty( $discount_type ) && ! in_array( $discount_type, array( 'free_gift', 'smart_coupon' ), true ) ) || ( 'yes' !== $is_free_shipping ) ) ) {
				if ( 'yes' !== $is_free_shipping ) {
					$is_zero_amount_coupon = true;
				}
			}

			if ( $is_zero_amount_coupon ) {
				continue;
			}

			if ( empty( $discount_type ) || ( ! empty( $expiry_timestamp ) && time() > $expiry_timestamp ) ) {
				continue;
			}

			$wcac_coupon = new \Auto_Applied_Coupons\Models\WCAC_Coupon( $coupon );

			$args = array(
				'product_id'     => $product_id,
				'coupon_object'  => $coupon,
				'coupon_expiry'  => $expiry_date,
				'applied_coupon' => $applied_code,
				'coupon_data'    => $wcac_coupon->get_meta_data(),
			);

			wc_get_template( 'card.php', $args, 'coupons', WCAC_PLUGIN_DIR . 'templates/' );

			++$i;
		}
	}