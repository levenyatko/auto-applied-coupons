<?php
	/**
	 * Admin Post meta Interface
	 *
	 * @package Auto_Applied_Coupons\Admin\Interfaces
	 */

	namespace Auto_Applied_Coupons\Admin\Interfaces;

	defined( 'ABSPATH' ) || exit;

interface Post_Meta_Interface {

	/**
	 * Display meta fields.
	 *
	 * @param int             $post_id Coupon ID.
	 * @param \WC_Coupon|null $post Coupon Object.
	 *
	 * @return void
	 */
	public function display( $post_id = 0, $post = null );

	/**
	 * Save meta fields.
	 *
	 * @param int           $post_id Saved post ID.
	 * @param \WP_Post|null $post Post object.
	 *
	 * @return void
	 */
	public function save( $post_id = 0, $post = null );
}
