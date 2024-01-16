<?php

	namespace Auto_Applied_Coupons\Hooks\Filters;

	use Auto_Applied_Coupons\Interfaces\Filters_Interface;

	class Database_Query_Filter implements Filters_Interface{

		/**
		 * @inheritDoc
		 */
		public function get_filters() {
			return array(
				'posts_where' => array('allow_is_null_in_where'),
			);
		}

		public function allow_is_null_in_where( $where ) {
			return str_replace( "= 'IS NULL'", ' IS NULL ', $where );
		}
	}