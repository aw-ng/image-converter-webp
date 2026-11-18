<?php
/**
 * Bootstrap Tests.
 *
 * Set up PHPUnit related dependencies
 * for WP Mock tests.
 *
 * @package ImageConverterWebP
 */

// First we need to load the composer autoloader.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Bootstrap WP_Mock.
WP_Mock::activateStrictMode();
WP_Mock::bootstrap();

// Define plugin constants.
define( 'ICFW_VERSION', '1.7.0' );
define( 'ICFW_FILE', dirname( __DIR__ ) . '/image-converter-webp.php' );
