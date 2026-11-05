<?php

namespace ImageConverterWebP\Tests\Services;

use WP_Mock;
use Mockery;
use WP_Error;
use Badasswp\WPMockTC\WPMockTestCase;
use ImageConverterWebP\Services\Logger;

/**
 * @covers \ImageConverterWebP\Core\Converter::__construct
 * @covers \ImageConverterWebP\Services\Logger::__construct
 * @covers \ImageConverterWebP\Services\Logger::register
 * @covers \ImageConverterWebP\Services\Logger::add_logs_for_webp_conversions
 * @covers icfw_get_settings
 */
class LoggerTest extends WPMockTestCase {
	public Logger $logger;

	public function setUp(): void {
		parent::setUp();

		$this->logger = new Logger();
	}

	public function tearDown(): void {
		parent::tearDown();
	}

	public function test_register() {
		WP_Mock::expectActionAdded( 'icfw_convert', [ $this->logger, 'add_logs_for_webp_conversions' ], 10, 2 );

		$this->logger->register();

		$this->assertConditionsMet();
	}

	public function test_add_logs_for_webp_conversions_does_not_log_error_if_log_option_is_not_enabled() {
		$webp = Mockery::mock( WP_Error::class )->makePartial();

		$options = [
			'logs' => false,
		];

		WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'icfw', [] )
			->andReturn( $options );

		$this->logger->add_logs_for_webp_conversions( $webp, 1 );

		$this->assertConditionsMet();
	}

	public function test_add_logs_for_webp_conversions_logs_error_if_wp_error_is_true() {
		$webp = Mockery::mock( WP_Error::class )->makePartial();

		$webp->shouldReceive( 'get_error_message' )
			->andReturn( 'Fatal Error: sample.pdf is not an image...' );

		$webp->shouldReceive( 'get_error_code' )
			->andReturn( 'webp-img-error' );

		$options = [
			'logs' => true,
		];

		WP_Mock::userFunction( 'get_option' )
			->with( 'icfw', [] )
			->andReturn( $options );

		WP_Mock::userFunction( 'wp_insert_post' )
			->once()
			->with(
				[
					'post_type'    => 'icfw_error',
					'post_title'   => 'WebP error log, ID - 1',
					'post_content' => 'Fatal Error: sample.pdf is not an image...',
					'post_status'  => 'publish',
				]
			)
			->andReturn( 100 );

		$this->logger->add_logs_for_webp_conversions( $webp, 1 );

		$this->assertConditionsMet();
	}
}
