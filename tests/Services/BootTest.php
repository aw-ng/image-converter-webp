<?php

namespace ImageConverterWebP\Tests\Services;

use WP_Mock;
use ReflectionClass;
use WP_Mock\Tools\TestCase;
use ImageConverterWebP\Services\Boot;

/**
 * @covers \ImageConverterWebP\Abstracts\Service::__construct
 * @covers \ImageConverterWebP\Services\Boot::register
 * @covers \ImageConverterWebP\Services\Boot::register_translation
 */
class BootTest extends TestCase {
	public Boot $boot;

	public function setUp(): void {
		WP_Mock::setUp();

		$this->boot = new Boot();
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
	}

	public function test_register() {
		WP_Mock::expectActionAdded( 'init', [ $this->boot, 'register_translation' ] );

		$this->boot->register();

		$this->assertConditionsMet();
	}

	public function test_register_translation() {
		$boot = new ReflectionClass( Boot::class );

		WP_Mock::userFunction( 'plugin_basename' )
			->once()
			->with( $boot->getFileName() )
			->andReturn( '/inc/Services/Boot.php' );

		WP_Mock::userFunction( 'load_plugin_textdomain' )
			->once()
			->with(
				'image-converter-webp',
				false,
				'/inc/Services/../../languages'
			);

		$this->boot->register_translation();

		$this->assertConditionsMet();
	}
}
