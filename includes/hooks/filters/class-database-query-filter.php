<?php
	/**
	 * DB query filter class.
	 *
	 * @package Auto_Applied_Coupons\Hooks\Filters
	 */

	namespace Auto_Applied_Coupons\Hooks\Filters;

	use Auto_Applied_Coupons\Interfaces\Filters_Interface;

	defined( 'ABSPATH' ) || exit;

class Database_Query_Filter implements Filters_Interface {

	/**
	 * Return the filters to register.
	 *
	 * @return array
	 */
	public function get_filters() {
		return array(
			'posts_where' => array( 'allow_is_null_in_where' ),
		);
	}

	/**
	 * Filter WHERE string wo allow IS NULL on meta query.
	 *
	 * @param string $where Old where string.
	 *
	 * @return string
	 */
	public function allow_is_null_in_where( $where ) {
		return str_replace( "= 'IS NULL'", ' IS NULL ', $where );
	}
}
