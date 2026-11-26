<?php

namespace ImageConverterWebP\Tests\Core;

use WP_Mock;
use Mockery;
use WP_Error;
use WP_Mock\Tools\TestCase;
use ImageConverterWebP\Core\Converter;

/**
 * @covers \ImageConverterWebP\Core\Converter::convert
 * @covers \ImageConverterWebP\Core\Converter::get_options
 * @covers \ImageConverterWebP\Core\Converter::get_image_source
 * @covers \ImageConverterWebP\Core\Converter::get_image_destination
 */
class ConverterTest extends TestCase {
	const BASE_URL = 'https://example.com/wp-content/uploads/2024/01';

	public $converter;

	public function setUp(): void {
		WP_Mock::setUp();

		$this->converter = new Converter();
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
	}

	public function test_get_options_returns_default_settings() {
		$converter = Mockery::mock( Converter::class )->makePartial();
		$converter->shouldAllowMockingProtectedMethods();

		WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'icfw', [] )
			->andReturn(
				[
					'quality' => 0,
				]
			);

		WP_Mock::expectFilter(
			'icfw_options',
			[
				'quality' => 0,
			]
		);

		WP_Mock::userFunction( 'wp_parse_args' )
			->andReturnUsing(
				function ( $args, $defaults ) {
					return array_merge( $defaults, $args );
				}
			);

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

		WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'icfw', [] )
			->andReturn(
				[
					'quality'   => 75,
					'converter' => 'imagick',
				]
			);

		WP_Mock::onFilter( 'icfw_options' )
			->with(
				[
					'quality'   => 75,
					'converter' => 'imagick',
				]
			)
			->reply(
				[
					'quality'   => 50,
					'converter' => 'cwebp',
				]
			);

		WP_Mock::userFunction( 'wp_parse_args' )
			->andReturnUsing(
				function ( $args, $defaults ) {
					return array_merge( $defaults, $args );
				}
			);

		$options = $converter->get_options();

		$this->assertSame(
			$options,
			[
				'quality'     => 50,
				'max-quality' => 100,
				'converter'   => 'cwebp',
			]
		);
		$this->assertConditionsMet();
	}

	public function test_get_options_returns_plugin_settings() {
		$converter = Mockery::mock( Converter::class )->makePartial();
		$converter->shouldAllowMockingProtectedMethods();

		WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'icfw', [] )
			->andReturn(
				[
					'quality'   => 66,
					'converter' => 'cwebp',
				]
			);

		WP_Mock::expectFilter(
			'icfw_options',
			[
				'quality'   => 66,
				'converter' => 'cwebp',
			]
		);

		WP_Mock::userFunction( 'wp_parse_args' )
			->andReturnUsing(
				function ( $args, $defaults ) {
					return array_merge( $defaults, $args );
				}
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

		WP_Mock::expectFilter(
			'icfw_options',
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

	public function test_get_options_returns_options_that_are_not_empty_strings() {
		$converter = Mockery::mock( Converter::class )->makePartial();
		$converter->shouldAllowMockingProtectedMethods();

		WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'icfw', [] )
			->andReturn(
				[
					'quality'     => '',
					'max-quality' => '',
					'converter'   => '',
				]
			);

		WP_Mock::expectFilter(
			'icfw_options',
			[
				'quality'     => '',
				'max-quality' => '',
				'converter'   => '',
			]
		);

		WP_Mock::userFunction( 'wp_parse_args' )
			->andReturnUsing(
				function ( $args, $defaults ) {
					return array_merge( $defaults, $args );
				}
			);

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

	public function test_get_options_returns_back_only_options_needed_by_webp_converter_class() {
		$converter = Mockery::mock( Converter::class )->makePartial();
		$converter->shouldAllowMockingProtectedMethods();

		$options = [
			'quality'   => '',
			'converter' => 'unknown',
			'log'       => '1',
			'page_load' => '1',
			'age'       => 35,
		];

		WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'icfw', [] )
			->andReturn( $options );

		WP_Mock::expectFilter( 'icfw_options', $options );

		WP_Mock::userFunction( 'wp_parse_args' )
			->andReturnUsing(
				function ( $args, $defaults ) {
					return array_merge( $defaults, $args );
				}
			);

		$options = $converter->get_options();

		$this->assertSame(
			$options,
			[
				'quality'     => 20,
				'max-quality' => 100,
				'converter'   => 'gd',
			]
		);
		$this->assertCount( 3, $options );

		$this->assertConditionsMet();
	}

	public function test_get_image_source() {
		$converter = Mockery::mock( Converter::class )->makePartial();
		$converter->shouldAllowMockingProtectedMethods();

		WP_Mock::userFunction( 'wp_upload_dir' )
			->once()
			->andReturn(
				[
					'baseurl' => 'https://example.com/wp-content/uploads/2024/01/',
					'basedir' => '/var/www/html/wp-content/uploads/2024/01/',
				]
			);

		$this->assertSame(
			'/var/www/html/wp-content/uploads/2024/01/sample.jpeg',
			$converter->get_image_source( 'https://example.com/wp-content/uploads/2024/01/sample.jpeg' )
		);
		$this->assertConditionsMet();
	}

	public function test_get_image_destination() {
		$converter = Mockery::mock( Converter::class )->makePartial();
		$converter->shouldAllowMockingProtectedMethods();

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

		$destination = $converter->get_image_destination(
			'https://example.com/wp-content/uploads/2024/01/sample.jpeg',
			'/var/www/html/wp-content/uploads/2024/01/sample.jpeg'
		);

		$this->assertSame(
			[
				'abs' => '/var/www/html/wp-content/uploads/2024/01/sample.webp',
				'rel' => 'https://example.com/wp-content/uploads/2024/01/sample.webp',
			],
			$destination
		);
		$this->assertConditionsMet();
	}

	public function test_convert_fails_if_source_does_not_exist_and_returns_WP_error() {
		$this->mock_paths( 'missing.jpeg' );

		WP_Mock::userFunction( '__' )
			->once()
			->with( 'Error: %s does not exist.', 'image-converter-webp' )
			->andReturn( 'Error: %s does not exist.' );

		// Define WP_Error for the test run.
		Mockery::mock( WP_Error::class );

		$webp = $this->converter->convert( 1, self::BASE_URL . '/missing.jpeg' );

		$this->assertInstanceOf( 'WP_Error', $webp );
		$this->assertConditionsMet();
	}

	public function test_convert_fails_if_source_is_not_an_image_and_returns_WP_error() {
		$this->mock_paths( 'sample.txt' );
		$this->create_mock_file( __DIR__ . '/sample.txt' );

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
			->andReturn( 'Error: %s is not an image.' );

		// Define WP_Error for the test run.
		Mockery::mock( WP_Error::class );

		$webp = $this->converter->convert( 1, self::BASE_URL . '/sample.txt' );

		$this->assertInstanceOf( 'WP_Error', $webp );
		$this->assertConditionsMet();

		$this->destroy_mock_file( __DIR__ . '/sample.txt' );
	}

	public function test_convert_fails_if_filetype_is_not_jpg_or_png_and_returns_WP_error() {
		$this->mock_paths( 'sample.gif' );
		$this->create_mock_file( __DIR__ . '/sample.gif' );

		WP_Mock::userFunction( 'wp_check_filetype' )
			->once()
			->with( __DIR__ . '/sample.gif' )
			->andReturn(
				[
					'type' => 'image/gif',
				]
			);

		WP_Mock::userFunction( '__' )
			->once()
			->with( 'Error: Invalid image file type.', 'image-converter-webp' )
			->andReturn( 'Error: Invalid image file type.' );

		// Define WP_Error for the test run.
		Mockery::mock( WP_Error::class );

		$webp = $this->converter->convert( 1, self::BASE_URL . '/sample.gif' );

		$this->assertInstanceOf( 'WP_Error', $webp );
		$this->assertConditionsMet();

		$this->destroy_mock_file( __DIR__ . '/sample.gif' );
	}

	public function test_convert_returns_same_if_destination_image_exists() {
		$this->mock_paths( 'sample.jpeg' );
		$this->create_mock_image( __DIR__ . '/sample.jpeg' );
		$this->create_mock_image( __DIR__ . '/sample.webp' );

		WP_Mock::userFunction( 'wp_check_filetype' )
			->once()
			->with( __DIR__ . '/sample.jpeg' )
			->andReturn(
				[
					'type' => 'image/jpeg',
				]
			);

		$webp = $this->converter->convert( 1, self::BASE_URL . '/sample.jpeg' );

		$this->assertSame( self::BASE_URL . '/sample.webp', $webp );
		$this->assertConditionsMet();

		$this->destroy_mock_image( __DIR__ . '/sample.jpeg' );
		$this->destroy_mock_image( __DIR__ . '/sample.webp' );
	}

	public function test_convert_returns_webp() {
		$converter = Mockery::mock( Converter::class )->makePartial();
		$converter->shouldAllowMockingProtectedMethods();

		$this->mock_paths( 'sample.jpeg' );
		$this->create_mock_image( __DIR__ . '/sample.jpeg' );

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

		WP_Mock::expectAction( 'icfw_convert', self::BASE_URL . '/sample.webp', 1 );

		$webp = $converter->convert( 1, self::BASE_URL . '/sample.jpeg' );

		$this->assertTrue( file_exists( __DIR__ . '/sample.webp' ) );
		$this->assertSame( self::BASE_URL . '/sample.webp', $webp );
		$this->assertConditionsMet();

		$this->destroy_mock_image( __DIR__ . '/sample.jpeg' );
		$this->destroy_mock_image( __DIR__ . '/sample.webp' );
	}

	public function test_convert_uses_only_its_arguments_across_successive_calls() {
		$this->create_mock_image( __DIR__ . '/first.jpeg' );
		$this->create_mock_image( __DIR__ . '/first.webp' );
		$this->create_mock_image( __DIR__ . '/second.jpeg' );
		$this->create_mock_image( __DIR__ . '/second.webp' );

		WP_Mock::userFunction( 'wp_upload_dir' )
			->twice()
			->andReturn(
				[
					'baseurl' => self::BASE_URL,
					'basedir' => __DIR__,
				]
			);

		foreach ( [ 'first', 'second' ] as $name ) {
			WP_Mock::expectFilter(
				'icfw_image_abs_destination',
				__DIR__ . "/{$name}.webp",
				__DIR__ . "/{$name}.jpeg",
				'.jpeg'
			);

			WP_Mock::expectFilter(
				'icfw_image_rel_destination',
				self::BASE_URL . "/{$name}.webp",
				self::BASE_URL . "/{$name}.jpeg",
				'.jpeg'
			);

			WP_Mock::userFunction( 'wp_check_filetype' )
				->once()
				->with( __DIR__ . "/{$name}.jpeg" )
				->andReturn(
					[
						'type' => 'image/jpeg',
					]
				);
		}

		// The same instance must not carry anything over from the first call.
		$this->assertSame( self::BASE_URL . '/first.webp', $this->converter->convert( 1, self::BASE_URL . '/first.jpeg' ) );
		$this->assertSame( self::BASE_URL . '/second.webp', $this->converter->convert( 2, self::BASE_URL . '/second.jpeg' ) );
		$this->assertConditionsMet();

		foreach ( [ 'first.jpeg', 'first.webp', 'second.jpeg', 'second.webp' ] as $file ) {
			$this->destroy_mock_image( __DIR__ . '/' . $file );
		}
	}

	/**
	 * Map the uploads URL onto this test directory and expect
	 * the destination filters for the given file.
	 *
	 * @param string $file File name, e.g. 'sample.jpeg'.
	 * @return void
	 */
	private function mock_paths( string $file ): void {
		$extension = '.' . pathinfo( $file, PATHINFO_EXTENSION );
		$webp      = str_replace( $extension, '.webp', $file );

		WP_Mock::userFunction( 'wp_upload_dir' )
			->once()
			->andReturn(
				[
					'baseurl' => self::BASE_URL,
					'basedir' => __DIR__,
				]
			);

		WP_Mock::expectFilter(
			'icfw_image_abs_destination',
			__DIR__ . '/' . $webp,
			__DIR__ . '/' . $file,
			$extension
		);

		WP_Mock::expectFilter(
			'icfw_image_rel_destination',
			self::BASE_URL . '/' . $webp,
			self::BASE_URL . '/' . $file,
			$extension
		);
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
