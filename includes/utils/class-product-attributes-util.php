<?php
	/**
	 * Helper to work with product attributes.
	 *
	 * @package Auto_Applied_Coupons\Utils
	 */

	namespace Auto_Applied_Coupons\Utils;

	defined( 'ABSPATH' ) || exit;

class Product_Attributes_Util {

	/**
	 * Get all product attributes.
	 *
	 * @return array
	 */
	public static function get_all() {

		$attribute_taxonomies       = wc_get_attribute_taxonomies();
		$attribute_options          = array();
		$attribute_taxonomies_label = array();

		if ( ! empty( $attribute_taxonomies ) && is_array( $attribute_taxonomies ) ) {

			$attribute_taxonomies_name = array();

			foreach ( $attribute_taxonomies as $attribute_taxonomy ) {
				$attribute_name  = $attribute_taxonomy->attribute_name ?? '';
				$attribute_label = $attribute_taxonomy->attribute_label ?? '';

				if ( ! empty( $attribute_name ) && ! empty( $attribute_label ) ) {
					$attribute_taxonomy_name     = wc_attribute_taxonomy_name( $attribute_name );
					$attribute_taxonomies_name[] = $attribute_taxonomy_name;

					$attribute_taxonomies_label[ $attribute_taxonomy_name ] = $attribute_label;

				}
			}

			if ( ! empty( $attribute_taxonomies_name ) ) {

				$attribute_taxonomies_terms = get_terms(
					array(
						'taxonomy' => $attribute_taxonomies_name,
					)
				);

				if ( ! empty( $attribute_taxonomies_terms ) && is_array( $attribute_taxonomies_terms ) ) {
					foreach ( $attribute_taxonomies_terms as $attribute_taxonomy_term ) {
						$attribute_taxonomy       = $attribute_taxonomy_term->taxonomy;
						$attribute_taxonomy_label = $attribute_taxonomies_label[ $attribute_taxonomy ] ?? '';
						if ( empty( $attribute_taxonomy_label ) ) {
							continue;
						}
						$attribute_term_id   = $attribute_taxonomy_term->term_id;
						$attribute_term_name = $attribute_taxonomy_term->name;
						$attribute_title     = __( 'Attribute: ', 'wcac' ) . $attribute_taxonomy_label . ':' . __( 'Value=', 'wcac' ) . $attribute_term_name;
						$attribute_label     = $attribute_taxonomy_label . ': ' . $attribute_term_name;

						$attribute_options[ $attribute_term_id ] = array(
							'title' => $attribute_title,
							'label' => $attribute_label,
						);
					}
				}
			}
		}

		return $attribute_options;
	}
}
