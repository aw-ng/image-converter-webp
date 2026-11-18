<?php
/**
 * Admin Class.
 *
 * This class holds the logic for registering
 * the plugin's admin page.
 *
 * @package ImageConverterWebP
 */

namespace ImageConverterWebP\Services;

use ImageConverterWebP\Admin\Form;
use ImageConverterWebP\Admin\Options;
use ImageConverterWebP\Abstracts\Service;
use ImageConverterWebP\Interfaces\Kernel;
use ImageConverterWebP\ICFW_FILE;
use ImageConverterWebP\ICFW_VERSION;

use Pluginate\Admin as Pluginate;

class Admin extends Service implements Kernel {
	/**
	 * Pluginate instance.
	 *
	 * @since 1.6.0
	 *
	 * @var Pluginate
	 */
	public Pluginate $pluginate;

	/**
	 * Admin constructor.
	 *
	 * @since 1.6.0
	 */
	public function __construct() {
		$this->pluginate = new Pluginate( 'image-converter-webp' );
	}

	/**
	 * Bind to WP.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_init', [ $this, 'register_options_init' ] );
		add_action( 'admin_menu', [ $this, 'register_options_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'register_options_styles' ] );
		add_action( 'admin_init', [ $this->pluginate, 'init' ] );
	}

	/**
	 * Register Options Menu.
	 *
	 * This controls the menu display for the plugin.
	 *
	 * @since 1.0.2
	 * @since 1.1.0 Moved to Admin class.
	 * @since 1.2.0 Remove unnecessary translations.
	 *
	 * @return void
	 */
	public function register_options_menu(): void {
		add_menu_page(
			Options::get_page_title(),
			Options::get_page_title(),
			'manage_options',
			Options::get_page_slug(),
			[ $this, 'register_options_page' ],
			'dashicons-format-image',
			100
		);

		add_submenu_page(
			Options::get_page_slug(),
			__( 'More Plugins', 'image-converter-webp' ),
			__( 'More Plugins', 'image-converter-webp' ),
			'manage_options',
			sprintf( '%s-more-plugins', Options::get_page_slug() ),
			[ $this, 'register_more_plugins' ]
		);
	}

	/**
	 * Register Options Page.
	 *
	 * This controls the display of the menu page.
	 *
	 * @since 1.0.2
	 * @since 1.1.0 Moved to Admin class.
	 *
	 * @return void
	 */
	public function register_options_page(): void {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		vprintf(
			'<section class="wrap">
				<h1>%s</h1>
				<p>%s</p>
				%s
			</section>',
			array_map(
				'__',
				( new Form( Options::$form ) )->get_options()
			)
		);
	}

	/**
	 * Register More Plugins.
	 *
	 * This controls the display of the
	 * "More Plugins" submenu page.
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	public function register_more_plugins(): void {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		vprintf(
			'<section class="wrap">
				<h1>%s</h1>
				<p>%s</p>
				%s
			</section>',
			array_map(
				'__',
				[
					'More Plugins',
					'Check out some other amazing plugin of ours...',
					$this->pluginate->get_more_plugins(),
				]
			)
		);
	}

	/**
	 * Register Settings.
	 *
	 * This method handles all save actions for the fields
	 * on the Plugin's settings page.
	 *
	 * @since 1.0.2
	 * @since 1.1.0 Moved to Admin class.
	 *
	 * @return void
	 */
	public function register_options_init(): void {
		// Form data.
		$form_fields = [];
		$form_values = [];

		// Button & WP Nonces.
		$form_button_name     = Options::get_submit_button_name();
		$form_settings_nonce  = Options::get_submit_nonce_name();
		$form_settings_action = Options::get_submit_nonce_action();

		// Bail out early, if save button or nonce is not set.
		if ( ! isset( $_POST[ $form_button_name ] ) || ! isset( $_POST[ $form_settings_nonce ] ) ) {
			return;
		}

		// Bail out early, if nonce is not verified.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $form_settings_nonce ] ) ), $form_settings_action ) ) {
			return;
		}

		// Get Form Fields.
		foreach ( Options::get_fields() as $field ) {
			$form_fields = array_merge(
				array_keys( $field['controls'] ?? [] ),
				$form_fields
			);
		}

		// Get Form Values.
		foreach ( $form_fields as $field ) {
			$form_values[] = sanitize_text_field( wp_unslash( $_POST[ $field ] ?? '' ) );
		}

		// Update Plugin options.
		update_option( Options::get_page_option(), array_combine( $form_fields, $form_values ) );
	}

	/**
	 * Register Styles.
	 *
	 * @since 1.1.0
	 * @since 1.2.0 Restrict styles to plugin page.
	 *
	 * @return void
	 */
	public function register_options_styles(): void {
		$screen = get_current_screen();

		// Bail out, if not plugin Admin page.
		if ( ! is_object( $screen ) || ! str_contains( $screen->id, Options::get_page_slug() ) ) {
			return;
		}

		wp_enqueue_style(
			'icfw-styles',
			plugins_url( 'styles.css', ICFW_FILE ),
			[],
			ICFW_VERSION
		);
	}
}
