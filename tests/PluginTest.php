<?php

namespace ImageConverterWebP\Tests;

use WP_Mock;
use Mockery;
use WP_Mock\Tools\TestCase;

use ImageConverterWebP\Plugin;
use ImageConverterWebP\Abstracts\Service;

use ImageConverterWebP\Services\Boot;
use ImageConverterWebP\Services\Main;
use ImageConverterWebP\Services\Admin;
use ImageConverterWebP\Services\Logger;
use ImageConverterWebP\Services\MetaData;
use ImageConverterWebP\Services\PageLoad;

/**
 * @covers \ImageConverterWebP\Plugin::get_instance
 * @covers \ImageConverterWebP\Plugin::run
 * @covers \ImageConverterWebP\Abstracts\Service::__construct
 * @covers \ImageConverterWebP\Abstracts\Service::get_instance
 * @covers \ImageConverterWebP\Core\Container::__construct
 * @covers \ImageConverterWebP\Core\Container::register
 * @covers \ImageConverterWebP\Services\Admin::register
 * @covers \ImageConverterWebP\Services\Boot::register
 * @covers \ImageConverterWebP\Services\Logger::register
 * @covers \ImageConverterWebP\Services\Main::register
 * @covers \ImageConverterWebP\Services\MetaData::register
 * @covers \ImageConverterWebP\Services\PageLoad::register
 * @covers \ImageConverterWebP\Services\Admin::__construct
 */
class PluginTest extends TestCase {
	public array $services;
	public Plugin $instance;

	public function setUp(): void {
		WP_Mock::setUp();

		$this->instance = Plugin::get_instance();
	}

	public function tearDown(): void {
		WP_Mock::tearDown();
	}

	public function test_run() {
		$this->services = [
			'Admin'    => Admin::get_instance(),
			'Boot'     => Boot::get_instance(),
			'Logger'   => Logger::get_instance(),
			'Main'     => Main::get_instance(),
			'MetaData' => MetaData::get_instance(),
			'PageLoad' => PageLoad::get_instance(),
		];

		WP_Mock::expectActionAdded(
			'init',
			[
				Service::$services['ImageConverterWebP\Services\Boot'],
				'register_translation',
			]
		);

		WP_Mock::expectActionAdded(
			'admin_init',
			[
				Service::$services['ImageConverterWebP\Services\Admin'],
				'register_options_init',
			]
		);

		WP_Mock::expectActionAdded(
			'admin_menu',
			[
				Service::$services['ImageConverterWebP\Services\Admin'],
				'register_options_menu',
			]
		);

		WP_Mock::expectActionAdded(
			'admin_enqueue_scripts',
			[
				Service::$services['ImageConverterWebP\Services\Admin'],
				'register_options_styles',
			]
		);

		$admin = Service::$services['ImageConverterWebP\Services\Admin'];

		WP_Mock::expectActionAdded(
			'admin_init',
			[
				$admin->pluginate,
				'init',
			]
		);

		WP_Mock::expectActionAdded(
			'icfw_convert',
			[
				Service::$services['ImageConverterWebP\Services\MetaData'],
				'add_webp_meta_to_attachment',
			],
			10,
			2
		);

		WP_Mock::expectActionAdded(
			'icfw_convert',
			[
				Service::$services['ImageConverterWebP\Services\MetaData'],
				'add_webp_for_scaled_images',
			],
			10,
			2
		);

		WP_Mock::expectActionAdded(
			'icfw_convert',
			[
				Service::$services['ImageConverterWebP\Services\Logger'],
				'add_logs_for_webp_conversions',
			],
			10,
			2
		);

		WP_Mock::expectActionAdded(
			'add_attachment',
			[
				Service::$services['ImageConverterWebP\Services\Main'],
				'register_webp_img_creation',
			],
			10,
			1
		);

		WP_Mock::expectFilterAdded(
			'wp_generate_attachment_metadata',
			[
				Service::$services['ImageConverterWebP\Services\Main'],
				'register_webp_img_srcset_creation',
			],
			10,
			3
		);

		WP_Mock::expectActionAdded(
			'delete_attachment',
			[
				Service::$services['ImageConverterWebP\Services\Main'],
				'register_webp_img_deletion',
			],
			10,
			1
		);

		WP_Mock::expectFilterAdded(
			'attachment_fields_to_edit',
			[
				Service::$services['ImageConverterWebP\Services\Main'],
				'register_webp_attachment_fields',
			],
			10,
			2
		);

		WP_Mock::expectFilterAdded(
			'wp_prepare_attachment_for_js',
			[
				Service::$services['ImageConverterWebP\Services\Main'],
				'show_webp_images_on_wp_media_modal',
			],
			10,
			3
		);

		WP_Mock::expectFilterAdded(
			'render_block',
			[
				Service::$services['ImageConverterWebP\Services\PageLoad'],
				'register_render_block',
			],
			20,
			2
		);

		WP_Mock::expectFilterAdded(
			'wp_get_attachment_image',
			[
				Service::$services['ImageConverterWebP\Services\PageLoad'],
				'register_wp_get_attachment_image',
			],
			10,
			5
		);

		WP_Mock::expectFilterAdded(
			'post_thumbnail_html',
			[
				Service::$services['ImageConverterWebP\Services\PageLoad'],
				'register_post_thumbnail_html',
			],
			10,
			5
		);

		$this->instance->run();

		$this->assertConditionsMet();
	}
}
