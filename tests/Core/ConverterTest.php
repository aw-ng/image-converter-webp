<?php

namespace ImageConverterWebP\Tests\Core;

use WP_Mock;
use Mockery;
use WP_Error;
use WP_Mock\Tools\TestCase;
use ImageConverterWebP\Core\Converter;
use ImageConverterWebP\Services\Main;

/**
 * @covers \ImageConverterWebP\Abstracts\Service::__construct
 * @covers \ImageConverterWebP\Core\Converter::__construct
 * @covers \ImageConverterWebP\Core\Converter::convert
 * @covers \ImageConverterWebP\Core\Converter::get_options
 * @covers \ImageConverterWebP\Core\Converter::set_image_source
 * @covers \ImageConverterWebP\Core\Converter::set_image_destination
 */
class ConverterTest extends TestCase {
	public $converter;

	public function setUp(): void {
		WP_Mock::setUp();

		$this->converter = new Converter( new Main() );
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
	}

	public function test_get_options_returns_default_settings() {
		$converter = Mockery::mock( Converter::class )->makePartial();
		$converter->shouldAllowMockingProtectedMethods();

		WP_Mock::expectFilter(
			'icfw_options',
			[
				'quality'     => 20,
				'max-quality' => 100,
				'converter'   => 'gd',
			]
		);

		WP_Mock::userFunction( 'wp_parse_args' )
			->once()
			->with(
				[ 'quality' => 0 ],
				[
					'quality'     => 20,
					'max-quality' => 100,
					'converter'   => 'gd',
				]
			)
			->andReturn(
				[
					'quality'     => 20,
					'max-quality' => 100,
					'converter'   => 'gd',
				]
			);

		WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'icfw', [] )
			->andReturn( [ 'quality' => 0 ] );

		$options = $converter->get_options();

		$this->assertSame(
			$options,
			[
				'quality'     => 20,
				'max-quality' => 100,
				'converter'   => 'gd',
			]
		);
		$this->assertConditionsMet();
	}

	public function test_get_options_returns_filter_settings() {
		$converter = Mockery::mock( Converter::class )->makePartial();
		$converter->shouldAllowMockingProtectedMethods();

		WP_Mock::onFilter( 'icfw_options' )
			->with(
				[
					'quality'     => 75,
					'max-quality' => 100,
					'converter'   => 'imagick',
				]
			)
			->reply(
				[
					'quality'   => 50,
					'converter' => 'imagick',
				]
			);

		WP_Mock::userFunction( 'wp_parse_args' )
			->once()
			->with(
				[
					'quality'   => 75,
					'converter' => 'imagick',
				],
				[
					'quality'     => 20,
					'max-quality' => 100,
					'converter'   => 'gd',
				]
			)
			->andReturn(
				[
					'quality'     => 75,
					'max-quality' => 100,
					'converter'   => 'imagick',
				]
			);

		WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'icfw', [] )
			->andReturn(
				[
					'quality'   => 75,
					'converter' => 'imagick',
				]
			);

		$options = $converter->get_options();

		$this->assertSame(
			$options,
			[
				'quality'   => 50,
				'converter' => 'imagick',
			]
		);
		$this->assertConditionsMet();
	}

	public function test_get_options_returns_plugin_settings() {
		$converter = Mockery::mock( Converter::class )->makePartial();
		$converter->shouldAllowMockingProtectedMethods();

		WP_Mock::expectFilter(
			'icfw_options',
			[
				'quality'     => 66,
				'max-quality' => 100,
				'converter'   => 'cwebp',
			]
		);

		WP_Mock::userFunction( 'wp_parse_args' )
			->once()
			->with(
				[
					'quality'   => 66,
					'converter' => 'cwebp',
				],
				[
					'quality'     => 20,
					'max-quality' => 100,
					'converter'   => 'gd',
				]
			)
			->andReturn(
				[
					'quality'     => 66,
					'max-quality' => 100,
					'converter'   => 'cwebp',
				]
			);

		WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'icfw', [] )
			->andReturn(
				[
					'quality'   => 66,
					'converter' => 'cwebp',
				]
			);

		$options = $converter->get_options();

		$this->assertSame(
			$options,
			[
				'quality'     => 66,
				'max-quality' => 100,
				'converter'   => 'cwebp',
			]
		);
		$this->assertConditionsMet();
	}

	public function test_get_options_returns_back_options_with_quality_set_when_no_quality_exists_in_the_options() {
		$converter = Mockery::mock( Converter::class )->makePartial();
		$converter->shouldAllowMockingProtectedMethods();

		WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'icfw', [] )
			->andReturn(
				[
					'max-quality' => 100,
				]
			);

		WP_Mock::userFunction( 'wp_parse_args' )
			->andReturnUsing(
				function ( $args, $defaults ) {
					return array_merge( $defaults, $args );
				}
			);

		$expected_array = [
			'quality'     => 20,
			'max-quality' => 100,
			'converter'   => 'gd',
		];

		WP_Mock::expectFilter(
			'icfw_options',
			$expected_array
		);

		$options = $converter->get_options();

		$this->assertSame(
			$options,
			$expected_array
		);
		$this->assertConditionsMet();
	}

	public function test_set_image_source() {
		$service = Mockery::mock( Main::class )->makePartial();
		$service->shouldAllowMockingProtectedMethods();

		$converter = Mockery::mock( Converter::class )->makePartial();
		$converter->shouldAllowMockingProtectedMethods();
		$converter->service = $service;

		$converter->service->source = [
			'id'  => 1,
			'url' => 'https://example.com/wp-content/uploads/2024/01/sample.jpeg',
		];

		WP_Mock::userFunction( 'wp_upload_dir' )
			->once()
			->andReturn(
				[
					'baseurl' => 'https://example.com/wp-content/uploads/2024/01/',
					'basedir' => '/var/www/html/wp-content/uploads/2024/01/',
				]
			);

		$converter->set_image_source();

		$this->assertSame(
			'/var/www/html/wp-content/uploads/2024/01/sample.jpeg',
			$converter->abs_source
		);
		$this->assertConditionsMet();
	}

	public function test_set_image_destination() {
		$service = Mockery::mock( Main::class )->makePartial();
		$service->shouldAllowMockingProtectedMethods();

		$converter = Mockery::mock( Converter::class )->makePartial();
		$converter->shouldAllowMockingProtectedMethods();
		$converter->service = $service;

		WP_Mock::expectFilter(
			'icfw_image_abs_destination',
			'/var/www/html/wp-content/uploads/2024/01/sample.webp',
			'/var/www/html/wp-content/uploads/2024/01/sample.jpeg',
			'.jpeg'
		);

		WP_Mock::expectFilter(
			'icfw_image_rel_destination',
			'https://example.com/wp-content/uploads/2024/01/sample.webp',
			'https://example.com/wp-content/uploads/2024/01/sample.jpeg',
			'.jpeg'
		);

		$converter->service->source = [
			'id'  => 1,
			'url' => 'https://example.com/wp-content/uploads/2024/01/sample.jpeg',
		];

		// Image Source (Absolute Path).
		$converter->abs_source = '/var/www/html/wp-content/uploads/2024/01/sample.jpeg';

		$converter->set_image_destination();

		$this->assertSame(
			'/var/www/html/wp-content/uploads/2024/01/sample.webp',
			$converter->abs_dest
		);
		$this->assertSame(
			'https://example.com/wp-content/uploads/2024/01/sample.webp',
			$converter->rel_dest
		);
		$this->assertConditionsMet();
	}

	public function test_convert_fails_if_source_is_empty_and_returns_WP_error() {
		$converter = Mockery::mock( Converter::class )->makePartial();
		$converter->shouldAllowMockingProtectedMethods();

		$converter->abs_source = '';

		$converter->shouldReceive( 'set_image_source' )
			->once()->with();

		$converter->shouldReceive( 'set_image_destination' )
			->once()->with();

		WP_Mock::userFunction( '__' )
			->once()
			->with( 'Error: %s does not exist.', 'image-converter-webp' )
			->andReturn( 'Error: does not exist.' );

		$mock = Mockery::mock( WP_Error::class );

		$webp = $converter->convert();

		$this->assertInstanceOf( 'WP_Error', $webp );
		$this->assertConditionsMet();
	}

	public function test_convert_fails_if_source_is_not_an_image_and_returns_WP_error() {
		$converter = Mockery::mock( Converter::class )->makePartial();
		$converter->shouldAllowMockingProtectedMethods();

		$converter->abs_source = __DIR__ . '/sample.txt';
		$converter->abs_dest   = __DIR__ . '/sample.webp';
		$converter->rel_dest   = str_replace( __DIR__, 'https://example.com/wp-content/uploads/2024/01', $converter->abs_dest );

		// Create Mock Files.
		$this->create_mock_file( $converter->abs_source );

		$converter->shouldReceive( 'set_image_source' )
			->once()->with();

		$converter->shouldReceive( 'set_image_destination' )
			->once()->with();

		WP_Mock::userFunction( 'wp_check_filetype' )
			->once()
			->with( __DIR__ . '/sample.txt' )
			->andReturn(
				[
					'type' => 'text/plain',
				]
			);

		WP_Mock::userFunction( '__' )
			->once()
			->with( 'Error: %s is not an image.', 'image-converter-webp' )
			->andReturn( 'Error: is not an image.' );

		$mock = Mockery::mock( WP_Error::class );

		$webp = $converter->convert();

		$this->assertInstanceOf( 'WP_Error', $webp );
		$this->assertConditionsMet();

		// Destroy Mock Files.
		$this->destroy_mock_image( $converter->abs_source );
	}

	public function test_convert_returns_same_if_destination_image_exists() {
		$converter = Mockery::mock( Converter::class )->makePartial();
		$converter->shouldAllowMockingProtectedMethods();

		$converter->abs_source = __DIR__ . '/sample.jpeg';
		$converter->abs_dest   = __DIR__ . '/sample.webp';
		$converter->rel_dest   = str_replace( __DIR__, 'https://example.com/wp-content/uploads/2024/01', $converter->abs_dest );

		// Create Mock Images.
		$this->create_mock_image( $converter->abs_source );
		$this->create_mock_image( $converter->abs_dest );

		$converter->shouldReceive( 'set_image_source' )
			->once()->with();

		$converter->shouldReceive( 'set_image_destination' )
			->once()->with();

		WP_Mock::userFunction( 'wp_check_filetype' )
			->once()
			->with( __DIR__ . '/sample.jpeg' )
			->andReturn(
				[
					'type' => 'image/jpeg',
				]
			);

		$webp = $converter->convert();

		$this->assertSame( 'https://example.com/wp-content/uploads/2024/01/sample.webp', $webp );
		$this->assertConditionsMet();

		// Destroy Mock Images.
		$this->destroy_mock_image( $converter->abs_source );
		$this->destroy_mock_image( $converter->abs_dest );
	}

	public function test_convert_returns_webp() {
		$service = Mockery::mock( Main::class )->makePartial();
		$service->shouldAllowMockingProtectedMethods();

		$converter = Mockery::mock( Converter::class )->makePartial();
		$converter->shouldAllowMockingProtectedMethods();
		$converter->service = $service;

		$converter->service->source = [
			'id'  => 1,
			'url' => 'https://example.com/wp-content/uploads/2024/01/sample.jpeg',
		];

		$converter->abs_source = __DIR__ . '/sample.jpeg';
		$converter->abs_dest   = __DIR__ . '/sample.webp';
		$converter->rel_dest   = str_replace( __DIR__, 'https://example.com/wp-content/uploads/2024/01', $converter->abs_dest );

		// Create Mock Images.
		$this->create_mock_image( $converter->abs_source );

		$converter->shouldReceive( 'set_image_source' )
			->once()->with();

		$converter->shouldReceive( 'set_image_destination' )
			->once()->with();

		WP_Mock::userFunction( 'wp_check_filetype' )
			->once()
			->with( __DIR__ . '/sample.jpeg' )
			->andReturn(
				[
					'type' => 'image/jpeg',
				]
			);

		$converter->shouldReceive( 'get_options' )
			->once()
			->with()
			->andReturn(
				[
					'quality'     => 20,
					'max-quality' => 100,
					'converter'   => 'gd',
				]
			);

		WP_Mock::expectAction(
			'icfw_convert',
			'https://example.com/wp-content/uploads/2024/01/sample.webp',
			1
		);

		$webp = $converter->convert();

		$this->assertTrue( file_exists( $converter->abs_dest ) );
		$this->assertSame( 'https://example.com/wp-content/uploads/2024/01/sample.webp', $webp );
		$this->assertConditionsMet();

		// Destroy Mock Images.
		$this->destroy_mock_image( $converter->abs_source );
		$this->destroy_mock_image( $converter->abs_dest );
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

	public function create_mock_file( $mock_file ) {
		file_put_contents( $mock_file, 'Hello World!', FILE_APPEND );
	}

	public function destroy_mock_file( $mock_file ) {
		if ( file_exists( $mock_file ) ) {
			unlink( $mock_file );
		}
	}
}
