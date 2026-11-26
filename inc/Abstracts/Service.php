<?php
/**
 * Service Abstraction.
 *
 * This abstraction defines the base logic from which all
 * Service classes are derived.
 *
 * @package ImageConverterWebP
 */

namespace ImageConverterWebP\Abstracts;

use ImageConverterWebP\Core\Converter;
use ImageConverterWebP\Interfaces\Kernel;

abstract class Service implements Kernel {
	/**
	 * Service classes.
	 *
	 * @since 1.1.0
	 *
	 * @var mixed[]
	 */
	public static array $services;

	/**
	 * Converter Instance.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Moved to Service abstraction.
	 *
	 * @var Converter
	 */
	public Converter $converter;

	/**
	 * Set up Converter.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		$this->converter = new Converter();
	}

	/**
	 * Register Singleton.
	 *
	 * This defines the generic method used by
	 * Service classes.
	 *
	 * @since 1.1.0
	 *
	 * @return static
	 */
	public static function get_instance() {
		$class = get_called_class();

		if ( ! isset( static::$services[ $class ] ) ) {
			static::$services[ $class ] = new static();
		}

		return static::$services[ $class ];
	}

	/**
	 * Register to WP.
	 *
	 * Bind concrete logic to WP here.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	abstract public function register(): void;
}
