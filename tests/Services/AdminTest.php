<?php

namespace ImageConverterWebP\Tests\Services;

use WP_Mock;
use Mockery;
use WP_Screen;
use Badasswp\WPMockTC\WPMockTestCase;
use ImageConverterWebP\Services\Admin;

/**
 * @covers \ImageConverterWebP\Services\Admin::__construct
 * @covers \ImageConverterWebP\Services\Admin::register
 * @covers \ImageConverterWebP\Services\Admin::register_options_menu
 * @covers \ImageConverterWebP\Services\Admin::register_options_init
 * @covers \ImageConverterWebP\Services\Admin::register_options_styles
 * @covers \ImageConverterWebP\Admin\Options::__callStatic
 * @covers \ImageConverterWebP\Admin\Options::get_form_fields
 * @covers \ImageConverterWebP\Admin\Options::get_form_notice
 * @covers \ImageConverterWebP\Admin\Options::get_form_page
 * @covers \ImageConverterWebP\Admin\Options::get_form_submit
 * @covers \ImageConverterWebP\Admin\Options::init
 */
class AdminTest extends WPMockTestCase {
	public Admin $admin;

	public function setUp(): void {
		parent::setUp();

		$this->admin = new Admin();
	}

	public function tearDown(): void {
		parent::tearDown();
	}

	public function test_register() {
		WP_Mock::expectActionAdded( 'admin_init', [ $this->admin, 'register_options_init' ] );
		WP_Mock::expectActionAdded( 'admin_menu', [ $this->admin, 'register_options_menu' ] );
		WP_Mock::expectActionAdded( 'admin_enqueue_scripts', [ $this->admin, 'register_options_styles' ] );
		WP_Mock::expectActionAdded( 'admin_init', [ $this->admin->pluginate, 'init' ] );

		$this->admin->register();

		$this->assertConditionsMet();
	}

	public function test_register_options_menu() {
		WP_Mock::userFunction( 'add_menu_page' )
			->once()
			->with(
				'Image Converter for WebP',
				'Image Converter for WebP',
				'manage_options',
				'image-converter-webp',
				[ $this->admin, 'register_options_page' ],
				'dashicons-format-image',
				100
			)
			->andReturn( null );

		WP_Mock::userFunction( 'add_submenu_page' )
			->once()
			->with(
				'image-converter-webp',
				'More Plugins',
				'More Plugins',
				'manage_options',
				'image-converter-webp-more-plugins',
				[ $this->admin, 'register_more_plugins' ]
			)
			->andReturn( null );

		$menu = $this->admin->register_options_menu();

		$this->assertNull( $menu );
		$this->assertConditionsMet();
	}

	public function test_register_options_init_bails_out_if_any_nonce_settings_is_missing() {
		$_POST = [
			'icfw_save_settings' => true,
		];

		$settings = $this->admin->register_options_init();

		$this->assertNull( $settings );
		$this->assertConditionsMet();
	}

	public function test_register_options_init_bails_out_if_nonce_verification_fails() {
		$_POST = [
			'icfw_save_settings'  => true,
			'icfw_settings_nonce' => 'a8vbq3cg3sa',
		];

		WP_Mock::userFunction( 'wp_unslash' )
			->times( 1 )
			->with( 'a8vbq3cg3sa' )
			->andReturn( 'a8vbq3cg3sa' );

		WP_Mock::userFunction( 'sanitize_text_field' )
			->times( 1 )
			->with( 'a8vbq3cg3sa' )
			->andReturn( 'a8vbq3cg3sa' );

		WP_Mock::userFunction( 'wp_verify_nonce' )
			->once()
			->with( 'a8vbq3cg3sa', 'icfw_settings_action' )
			->andReturn( false );

		$settings = $this->admin->register_options_init();

		$this->assertNull( $settings );
		$this->assertConditionsMet();
	}

	public function test_register_options_init_passes() {
		$_POST = [
			'icfw_save_settings'  => true,
			'icfw_settings_nonce' => 'a8vbq3cg3sa',
			'quality'             => 75,
			'converter'           => 'gd',
			'upload'              => 1,
			'page_load'           => 1,
			'logs'                => 1,
		];

		WP_Mock::userFunction(
			'wp_unslash',
			[
				'return' => function ( $text ) {
					return $text;
				},
			]
		);

		WP_Mock::userFunction( 'sanitize_text_field' )
			->times( 1 )
			->with( 'a8vbq3cg3sa' )
			->andReturn( 'a8vbq3cg3sa' );

		WP_Mock::userFunction( 'wp_verify_nonce' )
			->times( 1 )
			->with( 'a8vbq3cg3sa', 'icfw_settings_action' )
			->andReturn( true );

		WP_Mock::userFunction( 'update_option' )
			->once()
			->with(
				'icfw',
				[
					'quality'   => 75,
					'converter' => 'gd',
					'upload'    => 1,
					'page_load' => 1,
					'logs'      => 1,
				]
			)
			->andReturn( null );

		WP_Mock::userFunction(
			'sanitize_text_field',
			[
				'times'  => 5,
				'return' => function ( $text ) {
					return $text;
				},
			]
		);

		$settings = $this->admin->register_options_init();

		$this->assertNull( $settings );
		$this->assertConditionsMet();
	}

	public function test_register_options_styles_passes() {
		$screen = Mockery::mock( WP_Screen::class )->makePartial();
		$screen->shouldAllowMockingProtectedMethods();
		$screen->id = 'media_page_image-converter-webp';

		WP_Mock::userFunction( 'get_current_screen' )
			->andReturn( $screen );

		WP_Mock::userFunction( 'plugins_url' )
			->with( 'styles.css', ICFW_FILE )
			->andReturn( 'https://example.com/wp-content/plugins/image-converter-webp/styles.css' );

		WP_Mock::userFunction( 'wp_enqueue_style' )
			->with(
				'icfw-styles',
				'https://example.com/wp-content/plugins/image-converter-webp/styles.css',
				[],
				ICFW_VERSION
			)
			->andReturn( null );

		$this->admin->register_options_styles();

		$this->assertConditionsMet();
	}

	public function test_register_options_styles_bails_out_if_screen_is_not_an_object() {
		WP_Mock::userFunction( 'get_current_screen' )
			->andReturn( '' );

		$this->admin->register_options_styles();

		$this->assertConditionsMet();
	}

	public function test_register_options_styles_bails_out_if_screen_is_not_plugin_page() {
		$screen = Mockery::mock( WP_Screen::class )->makePartial();
		$screen->shouldAllowMockingProtectedMethods();
		$screen->id = 'media_page_another-plugin';

		WP_Mock::userFunction( 'get_current_screen' )
			->andReturn( $screen );

		$this->admin->register_options_styles();

		$this->assertConditionsMet();
	}
}
