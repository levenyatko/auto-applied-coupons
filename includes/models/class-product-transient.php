<?php

	namespace Auto_Applied_Coupons\Models;

	defined( 'ABSPATH' ) || exit;

class Product_Transient {

	private static $transient_key_format = 'wcac_product_%s_coupons_cache';

	private $product_id;

	public function __construct($product_id) {
		$this->product_id = $product_id;
	}

	public function get_transient_key() {
		return sprintf( self::$transient_key_format, $this->product_id );
	}

	public function get() {
		$cached = get_transient( $this->get_transient_key() );

		if ( $cached !== false ) {
			return $cached;
		}

		return array();
	}

	public function update( $data ) {
		// store for 3 hours
		set_transient( $this->get_transient_key(), $data, 3 * HOUR_IN_SECONDS );
	}

	public function delete() {
		delete_transient( $this->get_transient_key() );
	}

}
