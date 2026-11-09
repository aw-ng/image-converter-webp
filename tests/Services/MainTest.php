<?php

namespace ImageConverterWebP\Tests\Services;

use WP_Mock;
use WP_Post;
use Mockery;
use WP_Mock\Tools\TestCase;
use ImageConverterWebP\Services\Main;
use ImageConverterWebP\Core\Converter;

/**
 * @covers \ImageConverterWebP\Core\Converter::__construct
 * @covers \ImageConverterWebP\Services\Main::__construct
 * @covers \ImageConverterWebP\Services\Main::register
 * @covers \ImageConverterWebP\Services\Main::register_webp_img_creation
 * @covers \ImageConverterWebP\Services\Main::register_webp_img_srcset_creation
 * @covers \ImageConverterWebP\Services\Main::register_webp_img_deletion
 * @covers \ImageConverterWebP\Services\Main::register_webp_attachment_fields
 * @covers \ImageConverterWebP\Services\Main::show_webp_images_on_wp_media_modal
 * @covers icfw_get_settings
 * @covers icfw_get_abs_image
 * @covers icfw_get_webp_path
 */
class MainTest extends TestCase {
	public Main $main;

	public function setUp(): void {
		WP_Mock::setUp();

		$this->main = new Main();
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
	}

	public function test_register() {
		WP_Mock::expectActionAdded(
			'add_attachment',
			[ $this->main, 'register_webp_img_creation' ],
			10,
			1
		);

		WP_Mock::expectFilterAdded(
			'wp_generate_attachment_metadata',
			[ $this->main, 'register_webp_img_srcset_creation' ],
			10,
			3
		);

		WP_Mock::expectActionAdded(
			'delete_attachment',
			[ $this->main, 'register_webp_img_deletion' ],
			10,
			1
		);

		WP_Mock::expectFilterAdded(
			'attachment_fields_to_edit',
			[ $this->main, 'register_webp_attachment_fields' ],
			10,
			2
		);

		WP_Mock::expectFilterAdded(
			'wp_prepare_attachment_for_js',
			[ $this->main, 'show_webp_images_on_wp_media_modal' ],
			10,
			3
		);

		$this->main->register();

		$this->assertConditionsMet();
	}

	public function test_register_webp_img_creation_bails_out() {
		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldAllowMockingProtectedMethods();

		WP_Mock::userFunction( 'wp_get_attachment_url' )
			->once()
			->with( 1 )
			->andReturn( 'https://example.com/wp-content/uploads/2024/01/sample.jpeg' );

		$main->source = [
			'id'  => 1,
			'url' => 'https://example.com/wp-content/uploads/2024/01/sample.jpeg',
		];

		WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'icfw', [] )
			->andReturn(
				[
					'upload' => false,
				]
			);

		$main->register_webp_img_creation( 1 );

		$this->assertConditionsMet();
	}

	public function test_register_webp_img_creation_satisfies_conditions() {
		$converter = Mockery::mock( Converter::class )->makePartial();
		$converter->shouldAllowMockingProtectedMethods();

		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldAllowMockingProtectedMethods();
		$main->converter = $converter;

		$main->source = [
			'id'  => 1,
			'url' => 'https://example.com/wp-content/uploads/2024/01/sample.jpeg',
		];

		WP_Mock::userFunction( 'wp_get_attachment_url' )
			->once()
			->with( 1 )
			->andReturn( 'https://example.com/wp-content/uploads/2024/01/sample.jpeg' );

		$main->converter->shouldReceive( 'convert' )
			->once()
			->andReturn( 'https://example.com/wp-content/uploads/2024/01/sample.webp' );

		WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'icfw', [] )
			->andReturn(
				[
					'upload' => true,
				]
			);

		$main->register_webp_img_creation( 1 );

		$this->assertConditionsMet();
	}

	public function test_register_webp_img_srcset_creation() {
		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldAllowMockingProtectedMethods();

		$main->converter = Mockery::mock( Converter::class )->makePartial();
		$main->converter->shouldAllowMockingProtectedMethods();

		$data = [
			'sizes' => [
				[
					'file' => 'sample1.jpeg',
				],
				[
					'file' => 'sample2.jpeg',
				],
				[
					'file' => 'sample3.jpeg',
				],
			],
		];

		WP_Mock::userFunction( 'wp_get_attachment_image_url' )
			->once()
			->with( 1 )
			->andReturn( 'https://example.com/wp-content/uploads/2024/01/sample.jpeg' );

		WP_Mock::userFunction( 'trailingslashit' )
			->times( 3 )
			->with( 'https://example.com/wp-content/uploads/2024/01' )
			->andReturn( 'https://example.com/wp-content/uploads/2024/01/' );

		$main->converter->shouldReceive( 'convert' )
			->times( 3 );

		WP_Mock::userFunction( 'get_option' )
			->times( 3 )
			->with( 'icfw', [] )
			->andReturn(
				[
					'upload' => true,
				]
			);

		$srcset = $main->register_webp_img_srcset_creation( $data, 1, 'create' );

		$this->assertConditionsMet();
	}

	public function test_register_webp_img_srcset_creation_bails_out() {
		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldAllowMockingProtectedMethods();

		$data = [
			'sizes' => [
				[
					'file' => 'sample1.jpeg',
				],
				[
					'file' => 'sample2.jpeg',
				],
				[
					'file' => 'sample3.jpeg',
				],
			],
		];

		WP_Mock::userFunction( 'wp_get_attachment_image_url' )
			->once()
			->with( 1 )
			->andReturn( 'https://example.com/wp-content/uploads/2024/01/sample.jpeg' );

		WP_Mock::userFunction( 'trailingslashit' )
			->times( 3 )
			->with( 'https://example.com/wp-content/uploads/2024/01' )
			->andReturn( 'https://example.com/wp-content/uploads/2024/01/' );

		WP_Mock::userFunction( 'get_option' )
			->times( 3 )
			->with( 'icfw', [] )
			->andReturn(
				[
					'upload' => false,
				]
			);

		$srcset = $main->register_webp_img_srcset_creation( $data, 1, 'create' );

		$this->assertConditionsMet();
	}

	public function test_register_webp_img_deletion_fails_if_not_image() {
		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldAllowMockingProtectedMethods();

		WP_Mock::userFunction( 'wp_attachment_is_image' )
			->once()
			->with( 1 )
			->andReturn( false );

		$image = $main->register_webp_img_deletion( 1 );

		$this->assertConditionsMet();
	}

	public function test_register_webp_img_deletion_bails_if_no_parent_image_abs_path_or_metadata_is_found() {
		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldAllowMockingProtectedMethods();

		WP_Mock::userFunction( 'wp_attachment_is_image' )
			->once()
			->with( 1 )
			->andReturn( true );

		WP_Mock::userFunction( 'get_attached_file' )
			->once()
			->with( 1 )
			->andReturn( '' );

		WP_Mock::userFunction( 'wp_get_attachment_metadata' )
			->once()
			->with( 1 )
			->andReturn( [] );

		$image = $main->register_webp_img_deletion( 1 );

		$this->assertConditionsMet();
	}

	public function test_register_webp_img_deletion_removes_parent_webp_image() {
		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldAllowMockingProtectedMethods();

		WP_Mock::userFunction( 'wp_attachment_is_image' )
			->once()
			->with( 1 )
			->andReturn( true );

		WP_Mock::userFunction( 'get_attached_file' )
			->once()
			->with( 1 )
			->andReturn( __DIR__ . '/sample.jpeg' );

		WP_Mock::expectAction( 'icfw_delete', __DIR__ . '/sample.webp', 1 );

		WP_Mock::userFunction( 'wp_get_attachment_metadata' )
			->once()
			->with( 1 )
			->andReturn( [] );

		// Create Mock Images.
		$this->create_mock_image( __DIR__ . '/sample.webp' );

		$image = $main->register_webp_img_deletion( 1 );

		$this->assertConditionsMet();
	}

	public function test_register_webp_img_deletion_bails_out_if_image_does_not_exist() {
		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldAllowMockingProtectedMethods();

		WP_Mock::userFunction( 'wp_attachment_is_image' )
			->once()
			->with( 1 )
			->andReturn( true );

		WP_Mock::userFunction( 'get_attached_file' )
			->once()
			->with( 1 )
			->andReturn( '/sample.jpeg' );

		WP_Mock::userFunction( 'wp_get_attachment_metadata' )
			->once()
			->with( 1 )
			->andReturn( [] );

		$image = $main->register_webp_img_deletion( 1 );

		$this->assertConditionsMet();
	}

	public function test_register_webp_img_deletion_removes_webp_metadata_image() {
		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldAllowMockingProtectedMethods();

		WP_Mock::userFunction( 'wp_attachment_is_image' )
			->once()
			->with( 1 )
			->andReturn( true );

		WP_Mock::userFunction( 'get_attached_file' )
			->once()
			->with( 1 )
			->andReturn( __DIR__ . '/sample.jpeg' );

		WP_Mock::expectAction( 'icfw_delete', __DIR__ . '/sample.webp', 1 );

		WP_Mock::userFunction(
			'trailingslashit',
			[
				'times'  => 3,
				'return' => function ( $text ) {
					return $text . '/';
				},
			]
		);

		WP_Mock::userFunction( 'wp_get_attachment_metadata' )
			->once()
			->with( 1 )
			->andReturn(
				[
					'sizes' => [
						[
							'file' => 'sample1.jpeg',
						],
						[
							'file' => 'sample2.jpeg',
						],
						[
							'file' => 'sample3.jpeg',
						],
					],
				]
			);

		WP_Mock::expectAction( 'icfw_metadata_delete', __DIR__ . '/sample1.webp', 1 );
		WP_Mock::expectAction( 'icfw_metadata_delete', __DIR__ . '/sample2.webp', 1 );
		WP_Mock::expectAction( 'icfw_metadata_delete', __DIR__ . '/sample3.webp', 1 );

		// Create Mock Images.
		$this->create_mock_image( __DIR__ . '/sample.webp' );
		$this->create_mock_image( __DIR__ . '/sample1.webp' );
		$this->create_mock_image( __DIR__ . '/sample2.webp' );
		$this->create_mock_image( __DIR__ . '/sample3.webp' );

		$image = $main->register_webp_img_deletion( 1 );

		$this->assertConditionsMet();
	}

	public function test_register_webp_img_deletion_bails_out_if_meta_images_do_not_exist() {
		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldAllowMockingProtectedMethods();

		WP_Mock::userFunction( 'wp_attachment_is_image' )
			->once()
			->with( 1 )
			->andReturn( true );

		WP_Mock::userFunction( 'get_attached_file' )
			->once()
			->with( 1 )
			->andReturn( '/sample.jpeg' );

		WP_Mock::userFunction(
			'trailingslashit',
			[
				'times'  => 3,
				'return' => function ( $text ) {
					return $text . '/';
				},
			]
		);

		WP_Mock::userFunction( 'wp_get_attachment_metadata' )
			->once()
			->with( 1 )
			->andReturn(
				[
					'sizes' => [
						[
							'file' => 'sample1.jpeg',
						],
						[
							'file' => 'sample2.jpeg',
						],
						[
							'file' => 'sample3.jpeg',
						],
					],
				]
			);

		$image = $main->register_webp_img_deletion( 1 );

		$this->assertConditionsMet();
	}

	public function test_register_webp_attachment_fields_escapes_array_return_type() {
		$post     = Mockery::mock( WP_Post::class )->makePartial();
		$post->ID = 1;

		WP_Mock::userFunction(
			'__',
			[
				'return' => function ( $text, $domain = 'image-converter-webp' ) {
					return $text;
				},
			]
		);

		WP_Mock::userFunction( 'get_post_meta' )
			->once()
			->with( 1, 'icfw_img', true )
			->andReturn( [] );

		$expected = $this->main->register_webp_attachment_fields( [], $post );

		$this->assertSame(
			[
				'icfw_img' => [
					'input' => 'text',
					'value' => '',
					'label' => 'WebP Image',
					'helps' => 'WebP Image generated by Image Converter for WebP.',
				],
			],
			$expected
		);
		$this->assertConditionsMet();
	}

	public function test_register_webp_attachment_fields() {
		$webp = 'https://example.com/wp-content/uploads/2024/01/sample.webp';

		$post     = Mockery::mock( WP_Post::class )->makePartial();
		$post->ID = 1;

		WP_Mock::userFunction(
			'__',
			[
				'return' => function ( $text, $domain = 'image-converter-webp' ) {
					return $text;
				},
			]
		);

		WP_Mock::userFunction( 'get_post_meta' )
			->once()
			->with( 1, 'icfw_img', true )
			->andReturn( $webp );

		$expected = $this->main->register_webp_attachment_fields( [], $post );

		$this->assertSame(
			[
				'icfw_img' => [
					'input' => 'text',
					'value' => 'https://example.com/wp-content/uploads/2024/01/sample.webp',
					'label' => 'WebP Image',
					'helps' => 'WebP Image generated by Image Converter for WebP.',
				],
			],
			$expected
		);
		$this->assertConditionsMet();
	}

	public function test_show_webp_images_on_wp_media_modal_bails_out_if_not_image() {
		$attachment     = Mockery::mock( WP_Post::class )->makePartial();
		$attachment->ID = 1;

		WP_Mock::userFunction( 'get_post_meta' )
			->with( 1, 'icfw_img', true )
			->andReturn( 'https://example.com/wp-content/uploads/sample.pdf' );

		WP_Mock::userFunction( 'wp_attachment_is_image' )
			->with( 1 )
			->andReturnUsing(
				function ( $arg ) {
					$img_types = [ 'gif', 'png', 'jpg', 'jpeg', 'bmp', 'svg' ];

					if ( in_array( pathinfo( $arg, PATHINFO_EXTENSION ), $img_types, true ) ) {
						return true;
					}

					return false;
				}
			);

		$metadata = [
			'sizes' => [
				'thumbnail' => [
					'url' => 'https://example.com/wp-content/uploads/image-150x150.jpeg',
				],
				'medium'    => [
					'url' => 'https://example.com/wp-content/uploads/image-300x300.jpeg',
				],
				'large'     => [
					'url' => 'https://example.com/wp-content/uploads/image-1024x1024.jpeg',
				],
				'full'      => [
					'url' => 'https://example.com/wp-content/uploads/image.jpeg',
				],
			],
		];

		$this->main->show_webp_images_on_wp_media_modal( $metadata, $attachment, false );

		$this->assertConditionsMet();
	}

	public function test_show_webp_images_on_wp_media_modal_bails_out_if_webp_image_does_not_exist() {
		$attachment     = Mockery::mock( WP_Post::class )->makePartial();
		$attachment->ID = 1;

		WP_Mock::userFunction( 'get_post_meta' )
			->with( 1, 'icfw_img', true )
			->andReturn( 'https://example.com/wp-content/uploads/image.webp' );

		WP_Mock::userFunction( 'wp_attachment_is_image' )
			->twice()
			->with( 1 )
			->andReturn( true );

		WP_Mock::userFunction( 'get_attached_file' )
			->with( 1 )
			->andReturn( '/var/www/html/wp-content/uploads/image.jpeg' );

		$metadata = [
			'sizes' => [
				'thumbnail' => [
					'url' => 'https://example.com/wp-content/uploads/image-150x150.jpeg',
				],
				'medium'    => [
					'url' => 'https://example.com/wp-content/uploads/image-300x300.jpeg',
				],
				'large'     => [
					'url' => 'https://example.com/wp-content/uploads/image-1024x1024.jpeg',
				],
				'full'      => [
					'url' => 'https://example.com/wp-content/uploads/image.jpeg',
				],
			],
		];

		$this->main->show_webp_images_on_wp_media_modal( $metadata, $attachment, false );

		$this->assertConditionsMet();
	}

	public function test_show_webp_images_on_wp_media_modal_passes() {
		$attachment     = Mockery::mock( WP_Post::class )->makePartial();
		$attachment->ID = 1;

		$main = Mockery::mock( Main::class )->makePartial();
		$main->shouldAllowMockingProtectedMethods();

		WP_Mock::userFunction( 'get_post_meta' )
			->with( 1, 'icfw_img', true )
			->andReturn( 'https://example.com/wp-content/uploads/image.webp' );

		WP_Mock::userFunction( 'wp_attachment_is_image' )
			->twice()
			->with( 1 )
			->andReturn( true );

		WP_Mock::userFunction( 'get_attached_file' )
			->with( 1 )
			->andReturn( __DIR__ . '/image.webp' );

		$metadata = [
			'sizes' => [
				'thumbnail' => [
					'url' => 'https://example.com/wp-content/uploads/image-150x150.jpeg',
				],
				'medium'    => [
					'url' => 'https://example.com/wp-content/uploads/image-300x300.jpeg',
				],
				'large'     => [
					'url' => 'https://example.com/wp-content/uploads/image-1024x1024.jpeg',
				],
				'full'      => [
					'url' => 'https://example.com/wp-content/uploads/image.jpeg',
				],
			],
		];

		$metadata = $main->show_webp_images_on_wp_media_modal( $metadata, $attachment, false );

		$this->assertSame(
			$metadata,
			[
				'sizes' => [
					'thumbnail' => [
						'url' => 'https://example.com/wp-content/uploads/image-150x150.jpeg',
					],
					'medium'    => [
						'url' => 'https://example.com/wp-content/uploads/image-300x300.jpeg',
					],
					'large'     => [
						'url' => 'https://example.com/wp-content/uploads/image-1024x1024.jpeg',
					],
					'full'      => [
						'url' => 'https://example.com/wp-content/uploads/image.jpeg',
					],
				],
			]
		);
		$this->assertConditionsMet();
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
