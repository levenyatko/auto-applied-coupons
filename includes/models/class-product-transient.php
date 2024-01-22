<?php
	/**
	 * Product transient data model class.
	 *
	 * @package Auto_Applied_Coupons\Models
	 */

	namespace Auto_Applied_Coupons\Models;

	defined( 'ABSPATH' ) || exit;

class Product_Transient {

	/**
	 * Format for transient key.
	 *
	 * @var string $transient_key_format
	 */
	private static $transient_key_format = 'wcac_product_%s_coupons_cache';

	/**
	 * WooCommerce product ID.
	 *
	 * @var int $product_id
	 */
	private $product_id;

	/**
	 * Class construct.
	 *
	 * @param int $product_id Product.
	 */
	public function __construct( $product_id ) {
		$this->product_id = $product_id;
	}

	/**
	 * Get transient data formatted key.
	 *
	 * @return string
	 */
	public function get_transient_key() {
		return sprintf( self::$transient_key_format, $this->product_id );
	}

	/**
	 * Get transient data.
	 *
	 * @return array|mixed
	 */
	public function get() {
		$cached = get_transient( $this->get_transient_key() );

		if ( false !== $cached ) {
			return $cached;
		}

		return array();
	}

	/**
	 * Change product coupons transient data.
	 *
	 * @param mixed $data New product transient value.
	 *
	 * @return void
	 */
	public function update( $data ) {
		set_transient( $this->get_transient_key(), $data, 3 * HOUR_IN_SECONDS );
	}

	/**
	 * Delete product transient data.
	 *
	 * @return void
	 */
	public function delete() {
		delete_transient( $this->get_transient_key() );
	}
}
