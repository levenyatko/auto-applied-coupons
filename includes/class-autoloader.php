<?php
	/**
	 * Autoloader class.
	 *
	 * @package Auto_Applied_Coupons
	 */

	namespace Auto_Applied_Coupons;

	defined( 'ABSPATH' ) || exit;

class Autoloader {

	/**
	 * Project-specific namespace prefix.
	 *
	 * @var string PREFIX
	 */
	const PREFIX = 'Auto_Applied_Coupons\\';

	/**
	 * Base directory for the namespace prefix.
	 *
	 * @var string BASE_DIR
	 */
	const BASE_DIR = __DIR__;

	/**
	 * Register loader.
	 *
	 * @link https://www.php.net/manual/en/function.spl-autoload-register.php
	 * @return void
	 */
	public function register() {
		spl_autoload_register( array( $this, 'load_class' ) );
	}

	/**
	 * Check whether the given class name uses the namespace prefix.
	 *
	 * @param string $class_name The class name to check.
	 * @return bool
	 */
	private function starts_with_namespace_prefix( $class_name ) {
		$len = strlen( self::PREFIX );
		return ( 0 === strncmp( self::PREFIX, $class_name, $len ) );
	}

	/**
	 * Return the mapped file for the namespace prefix and the given class name.
	 *
	 * @param string $class_name The fully-qualified class name.
	 * @return string
	 */
	private function get_mapped_file( $class_name ) {
		$relative_class = substr( $class_name, strlen( self::PREFIX ) );
		$relative_class = strtolower( $relative_class );
		$relative_class = str_replace( '_', '-', $relative_class );

		$class_path = explode( '\\', $relative_class );
		$class_file = array_pop( $class_path );

		if ( empty( $class_path ) ) {
			$path = sprintf( '%s/class-%s.php', self::BASE_DIR, $class_file );
		} else {
			$class_path = implode( '/', $class_path );
			$path       = sprintf( '%s/%s/class-%s.php', self::BASE_DIR, $class_path, $class_file );
		}
		return $path;
	}

	/**
	 * Require the file at the given path, if it exists.
	 *
	 * @param string $file Required file name.
	 */
	private function require_file( $file ) {
		if ( file_exists( $file ) ) {
			require $file;
		}
	}

	/**
	 * Load the class file for the given class name.
	 *
	 * @param string $class_name The fully-qualified class name.
	 */
	public function load_class( $class_name ) {
		if ( ! $this->starts_with_namespace_prefix( $class_name ) ) {
			return;
		}

		$mapped_file = $this->get_mapped_file( $class_name );

		$this->require_file( $mapped_file );
	}
}
