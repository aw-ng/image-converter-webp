<?php

namespace ImageConverterWebP\Tests\Services;

use WP_Mock;
use Mockery;
use Badasswp\WPMockTC\WPMockTestCase;
use ImageConverterWebP\Core\Converter;
use ImageConverterWebP\Services\MetaData;

/**
 * @covers \ImageConverterWebP\Services\MetaData::__construct
 * @covers \ImageConverterWebP\Services\MetaData::register
 * @covers \ImageConverterWebP\Services\MetaData::add_webp_meta_to_attachment
 * @covers \ImageConverterWebP\Services\MetaData::add_webp_for_scaled_images
 * @covers icfw_get_settings
 */
class MetaDataTest extends WPMockTestCase {
	public MetaData $metadata;

	public function setUp(): void {
		parent::setUp();

		$this->metadata = new MetaData();
	}

	public function tearDown(): void {
		parent::tearDown();
	}

	public function test_register() {
		WP_Mock::expectActionAdded(
			'icfw_convert',
			[ $this->metadata, 'add_webp_meta_to_attachment' ],
			10,
			2
		);

		WP_Mock::expectActionAdded(
			'icfw_convert',
			[ $this->metadata, 'add_webp_for_scaled_images' ],
			10,
			2
		);

		$this->metadata->register();

		$this->assertConditionsMet();
	}

	public function test_add_webp_meta_to_attachment_bails_out_if_is_wp_error() {
		$wp_error = Mockery::mock( \WP_Error::class )->makePartial();
		$wp_error->shouldAllowMockingProtectedMethods();

		$options = [
			'logs' => true,
		];

		$this->metadata->add_webp_meta_to_attachment( $wp_error, 1 );

		$this->assertConditionsMet();
	}

	public function test_add_webp_meta_to_attachment_updates_post_meta() {
		$webp = 'https://example.com/wp-content/uploads/2024/01/sample.webp';

		$options = [
			'logs' => true,
		];

		WP_Mock::userFunction( 'get_post_meta' )
			->once()
			->with( 1, 'icfw_img', true )
			->andReturn( '' );

		WP_Mock::userFunction( 'update_post_meta' )
			->once()
			->with( 1, 'icfw_img', 'https://example.com/wp-content/uploads/2024/01/sample.webp' )
			->andReturn( null );

		$this->metadata->add_webp_meta_to_attachment( $webp, 1 );

		$this->assertConditionsMet();
	}

	public function test_add_webp_for_scaled_images_bails_out_if_is_wp_error() {
		$wp_error = Mockery::mock( \WP_Error::class )->makePartial();
		$wp_error->shouldAllowMockingProtectedMethods();

		$this->metadata->add_webp_for_scaled_images( $wp_error, 1 );

		$this->assertConditionsMet();
	}

	public function test_add_webp_for_scaled_images_bails_out_if_image_is_not_scaled() {
		$webp = 'https://example.com/wp-content/uploads/2024/01/sample.webp';

		WP_Mock::userFunction( 'wp_get_attachment_url' )
			->once()
			->with( 1 )
			->andReturn( 'https://example.com/wp-content/uploads/2024/01/sample.jpg' );

		$this->metadata->add_webp_for_scaled_images( $webp, 1 );

		$this->assertConditionsMet();
	}

	public function test_add_webp_for_scaled_images_passes() {
		$webp = 'https://example.com/wp-content/uploads/2024/01/sample.webp';

		WP_Mock::userFunction( 'wp_get_attachment_url' )
			->once()
			->with( 1 )
			->andReturn( 'https://example.com/wp-content/uploads/2024/01/sample-scaled.jpg' );

		$this->metadata->converter = Mockery::mock( Converter::class )->makePartial();
		$this->metadata->converter->shouldAllowMockingProtectedMethods();

		$this->metadata->converter->shouldReceive( 'convert' )
			->once()
			->with( 1, 'https://example.com/wp-content/uploads/2024/01/sample-scaled.jpg' );

		$this->metadata->add_webp_for_scaled_images( $webp, 1 );

		$this->assertConditionsMet();
	}
}
