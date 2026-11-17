<?php

namespace ImageConverterWebP\Tests\Services;

use WP_Mock;
use Mockery;
use WP_Error;
use Badasswp\WPMockTC\WPMockTestCase;
use ImageConverterWebP\Core\Converter;
use ImageConverterWebP\Services\PageLoad;

/**
 * @covers \ImageConverterWebP\Core\Converter::__construct
 * @covers \ImageConverterWebP\Services\PageLoad::__construct
 * @covers \ImageConverterWebP\Services\PageLoad::register
 * @covers \ImageConverterWebP\Services\PageLoad::register_render_block
 * @covers \ImageConverterWebP\Services\PageLoad::register_wp_get_attachment_image
 * @covers \ImageConverterWebP\Services\PageLoad::register_post_thumbnail_html
 * @covers \ImageConverterWebP\Services\PageLoad::get_webp_image_html
 * @covers \ImageConverterWebP\Services\PageLoad::get_webp
 * @covers \ImageConverterWebP\Services\PageLoad::get_all_srcset_images
 * @covers icfw_get_settings
 * @covers icfw_get_webp_path
 */
class PageLoadTest extends WPMockTestCase {
	public array $source;
	public PageLoad $page_load;

	public function setUp(): void {
		parent::setUp();

		$this->page_load = new PageLoad();
	}

	public function tearDown(): void {
		parent::tearDown();
	}

	public function test_register() {
		WP_Mock::expectFilterAdded(
			'render_block',
			[ $this->page_load, 'register_render_block' ],
			20,
			2
		);

		WP_Mock::expectFilterAdded(
			'wp_get_attachment_image',
			[ $this->page_load, 'register_wp_get_attachment_image' ],
			10,
			5
		);

		WP_Mock::expectFilterAdded(
			'post_thumbnail_html',
			[ $this->page_load, 'register_post_thumbnail_html' ],
			10,
			5
		);

		$this->page_load->register();

		$this->assertConditionsMet();
	}

	public function test_register_render_block_returns_empty_string() {
		$page_load = Mockery::mock( PageLoad::class )->makePartial();
		$page_load->shouldAllowMockingProtectedMethods();

		$image = $page_load->register_render_block( '', [] );

		$this->assertSame( '', $image );
		$this->assertConditionsMet();
	}

	public function test_register_render_block_returns_html_if_no_image() {
		$page_load = Mockery::mock( PageLoad::class )->makePartial();
		$page_load->shouldAllowMockingProtectedMethods();

		$image = $page_load->register_render_block( '<p>John Doe</p>', [] );

		$this->assertSame( '<p>John Doe</p>', $image );
		$this->assertConditionsMet();
	}

	public function test_register_render_block_returns_img_html() {
		$page_load = Mockery::mock( PageLoad::class )->makePartial();
		$page_load->shouldAllowMockingProtectedMethods();

		$page_load->shouldReceive( 'get_webp_image_html' )
			->once()
			->with( '<img src="sample.jpeg"/>' )
			->andReturn( '<img src="sample.webp"/>' );

		$image = $page_load->register_render_block( '<img src="sample.jpeg"/>', [] );

		$this->assertSame( '<img src="sample.webp"/>', $image );
		$this->assertConditionsMet();
	}

	public function test_register_wp_get_attachment_image_fails_and_returns_empty_string() {
		$image = $this->page_load->register_wp_get_attachment_image( '', 1, [], true, [] );

		$this->assertSame( '', $image );
		$this->assertConditionsMet();
	}

	public function test_register_wp_get_attachment_image_returns_img_html() {
		$page_load = Mockery::mock( PageLoad::class )->makePartial();
		$page_load->shouldAllowMockingProtectedMethods();

		$page_load->shouldReceive( 'get_webp_image_html' )
			->once()
			->with( '<img src="sample.jpeg"/>', 1 )
			->andReturn( '<img src="sample.webp"/>' );

		WP_Mock::onFilter( 'icfw_attachment_html' )
			->with(
				'<img src="sample.webp"/>',
				1
			)
			->reply(
				'<img src="sample.webp"/>'
			);

		$image = $page_load->register_wp_get_attachment_image( '<img src="sample.jpeg"/>', 1, [], true, [] );

		$this->assertSame( '<img src="sample.webp"/>', $image );
		$this->assertConditionsMet();
	}

	public function test_register_post_thumbnail_html_fails_and_returns_empty_string() {
		$image = $this->page_load->register_post_thumbnail_html( '', 1, [], true, [] );

		$this->assertSame( '', $image );
		$this->assertConditionsMet();
	}

	public function test_register_post_thumbnail_html_returns_img_html() {
		$page_load = Mockery::mock( PageLoad::class )->makePartial();
		$page_load->shouldAllowMockingProtectedMethods();

		$page_load->shouldReceive( 'get_webp_image_html' )
			->once()
			->with( '<img src="sample.jpeg"/>', 2 )
			->andReturn( '<img src="sample.webp"/>' );

		WP_Mock::onFilter( 'icfw_thumbnail_html' )
			->with(
				'<img src="sample.webp"/>',
				2
			)
			->reply(
				'<img src="sample.webp"/>'
			);

		$image = $page_load->register_post_thumbnail_html( '<img src="sample.jpeg"/>', 1, 2, [], [] );

		$this->assertSame( '<img src="sample.webp"/>', $image );
		$this->assertConditionsMet();
	}

	public function test_get_webp_image_html_returns_default_html_if_empty() {
		$page_load = Mockery::mock( PageLoad::class )->makePartial();
		$page_load->shouldAllowMockingProtectedMethods();

		$image = $page_load->get_webp_image_html( '' );

		$this->assertSame( '', $image );
		$this->assertConditionsMet();
	}

	public function test_get_webp_image_html_returns_default_html_if_no_image_in_html() {
		$page_load = Mockery::mock( PageLoad::class )->makePartial();
		$page_load->shouldAllowMockingProtectedMethods();

		$image = $page_load->get_webp_image_html( '<div></div>' );

		$this->assertSame( '<div></div>', $image );
		$this->assertConditionsMet();
	}

	public function test_get_webp_image_html_returns_default_html_if_page_load_is_not_activated() {
		$page_load = Mockery::mock( PageLoad::class )->makePartial();
		$page_load->shouldAllowMockingProtectedMethods();

		WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'icfw', [] )
			->andReturn(
				[
					'page_load' => false,
				]
			);

		$image = $page_load->get_webp_image_html( '<figure><img src="john.png"/></figure>' );

		$this->assertSame( '<figure><img src="john.png"/></figure>', $image );
		$this->assertConditionsMet();
	}

	public function test_get_webp_image_html_works_correctly_and_returns_new_html_with_webp_images() {
		$page_load = Mockery::mock( PageLoad::class )->makePartial();
		$page_load->shouldAllowMockingProtectedMethods();

		$page_load->shouldReceive( 'get_webp' )
			->andReturnUsing(
				function ( $arg1, $arg2 ) {
					$ext = pathinfo( $arg1, PATHINFO_EXTENSION );
					return str_replace( $ext, 'webp', $arg1 );
				}
			);

		WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'icfw', [] )
			->andReturn(
				[
					'page_load' => true,
				]
			);

		WP_Mock::userFunction( 'wp_filter_content_tags' )
			->with( '<figure class="wp-block-image size-large"><img src="https://example.com/wp-content/uploads/2025/12/image-799x1024.png" alt="" class="wp-image-1140"/></figure>' )
			->andReturn( '<figure class="wp-block-image size-large"><img width="799" height="1024" src="https://example.com/wp-content/uploads/2025/12/image-799x1024.png" alt="" class="wp-image-1140" srcset="https://example.com/wp-content/uploads/2025/12/image-799x1024.png 799w, https://example.com/wp-content/uploads/2025/12/image-234x300.png 234w, https://example.com/wp-content/uploads/2025/12/image-768x985.png 768w, https://example.com/wp-content/uploads/2025/12/image.png 922w" sizes="(max-width: 799px) 100vw, 799px" /></figure>' );

		$image = $page_load->get_webp_image_html( '<figure class="wp-block-image size-large"><img src="https://example.com/wp-content/uploads/2025/12/image-799x1024.png" alt="" class="wp-image-1140"/></figure>' );

		$this->assertSame(
			$image,
			'<figure class="wp-block-image size-large"><img width="799" height="1024" src="https://example.com/wp-content/uploads/2025/12/image-799x1024.webp" alt="" class="wp-image-1140" srcset="https://example.com/wp-content/uploads/2025/12/image-799x1024.webp 799w, https://example.com/wp-content/uploads/2025/12/image-234x300.webp 234w, https://example.com/wp-content/uploads/2025/12/image-768x985.webp 768w, https://example.com/wp-content/uploads/2025/12/image.webp 922w" sizes="(max-width: 799px) 100vw, 799px" /></figure>'
		);
		$this->assertConditionsMet();
	}

	public function test_get_webp_image_html_fails_gracefully_and_returns_default_html_with_original_images() {
		$page_load = Mockery::mock( PageLoad::class )->makePartial();
		$page_load->shouldAllowMockingProtectedMethods();

		$page_load->converter = Mockery::mock( Converter::class )->makePartial();
		$page_load->converter->shouldAllowMockingProtectedMethods();

		$page_load->converter->shouldReceive( 'convert' )
			->andReturn( Mockery::mock( WP_Error::class )->makePartial() );

		WP_Mock::userFunction( 'get_option' )
			->once()
			->with( 'icfw', [] )
			->andReturn(
				[
					'page_load' => true,
				]
			);

		WP_Mock::userFunction( 'wp_filter_content_tags' )
			->with( '<figure class="wp-block-image size-large"><img src="https://example.com/wp-content/uploads/2025/12/image-799x1024.png" alt="" class="wp-image-1140"/></figure>' )
			->andReturn( '<figure class="wp-block-image size-large"><img width="799" height="1024" src="https://example.com/wp-content/uploads/2025/12/image-799x1024.png" alt="" class="wp-image-1140" srcset="https://example.com/wp-content/uploads/2025/12/image-799x1024.png 799w, https://example.com/wp-content/uploads/2025/12/image-234x300.png 234w, https://example.com/wp-content/uploads/2025/12/image-768x985.png 768w, https://example.com/wp-content/uploads/2025/12/image.png 922w" sizes="(max-width: 799px) 100vw, 799px" /></figure>' );

		$image = $page_load->get_webp_image_html( '<figure class="wp-block-image size-large"><img src="https://example.com/wp-content/uploads/2025/12/image-799x1024.png" alt="" class="wp-image-1140"/></figure>', 1 );

		$this->assertSame(
			$image,
			'<figure class="wp-block-image size-large"><img width="799" height="1024" src="https://example.com/wp-content/uploads/2025/12/image-799x1024.png" alt="" class="wp-image-1140" srcset="https://example.com/wp-content/uploads/2025/12/image-799x1024.png 799w, https://example.com/wp-content/uploads/2025/12/image-234x300.png 234w, https://example.com/wp-content/uploads/2025/12/image-768x985.png 768w, https://example.com/wp-content/uploads/2025/12/image.png 922w" sizes="(max-width: 799px) 100vw, 799px" /></figure>'
		);
		$this->assertConditionsMet();
	}

	public function test_get_webp_bails_out_if_it_returns_wp_error_and_returns_default_image() {
		$page_load = Mockery::mock( PageLoad::class )->makePartial();
		$page_load->shouldAllowMockingProtectedMethods();

		$page_load->converter = Mockery::mock( Converter::class )->makePartial();
		$page_load->converter->shouldAllowMockingProtectedMethods();

		$page_load->converter->shouldReceive( 'convert' )
			->andReturn( Mockery::mock( WP_Error::class )->makePartial() );

		$img_html = $page_load->get_webp( 'https://example.com/wp-content/uploads/2024/01/sample.png', 1 );

		$this->assertSame( $img_html, 'https://example.com/wp-content/uploads/2024/01/sample.png' );
		$this->assertConditionsMet();
	}

	public function test_get_webp_returns_new_image_html() {
		$page_load = Mockery::mock( PageLoad::class )->makePartial();
		$page_load->shouldAllowMockingProtectedMethods();

		$page_load->converter = Mockery::mock( Converter::class )->makePartial();
		$page_load->converter->shouldAllowMockingProtectedMethods();

		$page_load->converter->shouldReceive( 'convert' )
			->andReturn( 'https://example.com/wp-content/uploads/2024/01/sample.webp' );

		$img_html = $page_load->get_webp( 'https://example.com/wp-content/uploads/2024/01/sample.jpeg', 1 );

		$this->assertSame( $img_html, 'https://example.com/wp-content/uploads/2024/01/sample.webp' );
		$this->assertConditionsMet();
	}

	public function test_get_all_srcset_images() {
		$page_load = Mockery::mock( PageLoad::class )->makePartial();
		$page_load->shouldAllowMockingProtectedMethods();

		$this->assertSame(
			[
				'https://example.com/wp-content/uploads/2025/12/image.webp',
				'https://example.com/wp-content/uploads/2025/12/image-234x300.png',
				'https://example.com/wp-content/uploads/2025/12/image-799x1024.png',
				'https://example.com/wp-content/uploads/2025/12/image-768x985.png',
			],
			$page_load->get_all_srcset_images( 'https://example.com/wp-content/uploads/2025/12/image.webp 922w, https://example.com/wp-content/uploads/2025/12/image-234x300.png 234w, https://example.com/wp-content/uploads/2025/12/image-799x1024.png 799w, https://example.com/wp-content/uploads/2025/12/image-768x985.png 768w' )
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
}
