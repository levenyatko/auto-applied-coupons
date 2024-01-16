<?php
	namespace Auto_Applied_Coupons\Admin\Interfaces;

	defined( 'ABSPATH' ) || exit;

	interface Post_Meta_Interface{

		public function display( $post_id = 0, $post = null );

		public function save( $post_id = 0, $post = null );
	}