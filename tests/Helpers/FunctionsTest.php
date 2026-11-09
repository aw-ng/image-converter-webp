<?php

namespace ImageConverterWebP\Tests\Helpers;

use WP_Mock;
use WP_Mock\Tools\TestCase;

require_once __DIR__ . '/../../inc/Helpers/functions.php';

/**
 * @covers icfw_get_settings
 * @covers icfw_get_abs_image
 * @covers icfw_get_webp_path
 */
class FunctionsTest extends TestCase {
	public function test_icfw_get_settings() {
		WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'icfw', [] )
			->andReturn(
				[
					'upload' => true,
				]
			);

		$is_image_converted_on_upload = icfw_get_settings( 'upload', [] );

		$this->assertTrue( $is_image_converted_on_upload );
	}

	public function test_icfw_get_webp_path() {
		$path = icfw_get_webp_path( '/uploads/a.jpg-folder/a.jpg' );

		$this->assertSame( $path, '/uploads/a.jpg-folder/a.webp' );
	}

	public function test_icfw_get_abs_image_returns_empty_string_if_not_image() {
		WP_Mock::userFunction( 'wp_attachment_is_image' )
			->once()
			->with( 1 )
			->andReturn( false );

		$webp = icfw_get_abs_image( 1 );

		$this->assertSame( $webp, '' );
	}

	public function test_icfw_get_abs_image_returns_empty_string_if_file_does_not_exist() {
		WP_Mock::userFunction( 'wp_attachment_is_image' )
			->with( 1 )
			->andReturn( true );

		WP_Mock::userFunction( 'get_attached_file' )
			->with( 1 )
			->andReturn( '/var/www/html/wp-content/uploads/sample.pdf' );

		$webp = icfw_get_abs_image( 1 );

		$this->assertSame( $webp, '' );
	}

	public function test_icfw_get_abs_image_passes() {
		WP_Mock::userFunction( 'wp_attachment_is_image' )
			->with( 1 )
			->andReturn( true );

		WP_Mock::userFunction( 'get_attached_file' )
			->with( 1 )
			->andReturn( __DIR__ . '/sample.jpg' );

		$this->create_mock_image( __DIR__ . '/sample.webp' );

		$webp = icfw_get_abs_image( 1 );

		$this->assertSame( $webp, __DIR__ . '/sample.webp' );

		$this->destroy_mock_image( __DIR__ . '/sample.webp' );
	}

	public function create_mock_image( $image_file_name ) {
		// Create a blank image.
		$width  = 400;
		$height = 200;
		$image  = imagecreatetruecolor( $width, $height );

		// Set background color.
		$bg_color = imagecolorallocate( $image, 255, 255, 255 );
		imagefill( $image, 0, 0, $bg_color );
		imagejpeg( $image, $image_file_name );
	}

	public function destroy_mock_image( $image_file_name ) {
		if ( file_exists( $image_file_name ) ) {
			unlink( $image_file_name );
		}
	}
}
