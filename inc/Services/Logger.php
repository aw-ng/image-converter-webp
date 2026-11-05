<?php
/**
 * Logger Class.
 *
 * This class handles the logging of failed
 * image conversions.
 *
 * @package ImageConverterWebP
 */

namespace ImageConverterWebP\Services;

use ImageConverterWebP\Abstracts\Service;
use ImageConverterWebP\Interfaces\Kernel;

class Logger extends Service implements Kernel {
	/**
	 * Bind to WP.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'icfw_convert', [ $this, 'add_logs_for_webp_conversions' ], 10, 2 );
	}

	/**
	 * Add WebP meta to Attachment.
	 *
	 * This is responsible for creating meta data or logging errors
	 * depending on the conversion result ($webp).
	 *
	 * @since 1.0.2
	 * @since 1.1.0 Moved to Logger class.
	 * @since 1.2.0 Moved meta data logic to MetaData class.
	 *
	 * @param string|\WP_Error $webp          WebP's relative path.
	 * @param int              $attachment_id Image ID.
	 *
	 * @return void
	 */
	public function add_logs_for_webp_conversions( $webp, $attachment_id ): void {
		if ( ! icfw_get_settings( 'logs' ) ) {
			return;
		}

		if ( is_wp_error( $webp ) ) {
			$error_code = $webp->get_error_code();

			// Bail out early for non-allowed images.
			if ( 'webp-img-invalid-type' === $error_code ) {
				return;
			}

			// Only log errors that are not 'webp-img-invalid-type' which is JPEG/PNG
			wp_insert_post(
				[
					'post_type'    => 'icfw_error',
					'post_title'   => 'WebP error log, ID - ' . $attachment_id,
					'post_content' => (string) $webp->get_error_message(),
					'post_status'  => 'publish',
				]
			);
		}
	}
}
