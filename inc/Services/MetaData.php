<?php
/**
 * MetaData Class.
 *
 * This class handles the creation of meta data
 * for WebP converted images.
 *
 * @package ImageConverterWebP
 */

namespace ImageConverterWebP\Services;

use ImageConverterWebP\Abstracts\Service;
use ImageConverterWebP\Interfaces\Kernel;

class MetaData extends Service implements Kernel {
	/**
	 * Bind to WP.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'icfw_convert', [ $this, 'add_webp_meta_to_attachment' ], 10, 2 );
		add_action( 'icfw_convert', [ $this, 'add_webp_for_scaled_images' ], 10, 2 );
	}

	/**
	 * Add WebP meta to Attachment.
	 *
	 * This is responsible for creating meta data or logging errors
	 * depending on the conversion result ($webp).
	 *
	 * @since 1.2.0
	 *
	 * @param string|\WP_Error $webp          WebP's relative path.
	 * @param int              $attachment_id Image ID.
	 *
	 * @return void
	 */
	public function add_webp_meta_to_attachment( $webp, $attachment_id ): void {
		// Bail out early, if \WP_Error.
		if ( is_wp_error( $webp ) ) {
			return;
		}

		// Save only if WebP image doesn't exist.
		if ( empty( get_post_meta( $attachment_id, 'icfw_img', true ) ) ) {
			update_post_meta( $attachment_id, 'icfw_img', $webp );
		}
	}

	/**
	 * Add WebP meta for WP Scaled Images.
	 *
	 * This is responsible for creating WebP images for WP scaled
	 * images (if any).
	 *
	 * @since 1.3.0
	 *
	 * @param string|\WP_Error $webp          WebP's relative path.
	 * @param int              $attachment_id Image ID.
	 *
	 * @return void
	 */
	public function add_webp_for_scaled_images( $webp, $attachment_id ): void {
		// Bail out early, if \WP_Error.
		if ( is_wp_error( $webp ) ) {
			return;
		}

		$image_url = (string) wp_get_attachment_url( $attachment_id );

		if ( false === strpos( $image_url, '-scaled' ) ) {
			return;
		}

		$this->converter->convert( (int) $attachment_id, $image_url );
	}
}
