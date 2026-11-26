<?php
/**
 * Converter Class.
 *
 * This class is responsible for converting the
 * JPG/PNG images to WebP format.
 *
 * @package ImageConverterWebP
 */

namespace ImageConverterWebP\Core;

use WP_Error;
use Exception;
use WebPConvert\WebPConvert;

class Converter {
	/**
	 * Convert to WebP image.
	 *
	 * This method is responsible for taking the source image
	 * and converting it to the WebP equivalent. It holds no state
	 * between calls: the result depends only on its arguments,
	 * the plugin settings and the filesystem.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Moved to Converter.
	 * @since 1.7.0 Accept attachment ID and source URL as arguments.
	 *
	 * @param int    $attachment_id Image Attachment ID.
	 * @param string $source_url    Image URL - 'https://example.com/wp-content/uploads/2024/01/sample.png'.
	 *
	 * @return string|\WP_Error
	 */
	public function convert( int $attachment_id, string $source_url ) {
		// Get image source and destination.
		$abs_source  = $this->get_image_source( $source_url );
		$destination = $this->get_image_destination( $source_url, $abs_source );
		$abs_dest    = $destination['abs'];

		// Set response.
		$webp = $destination['rel'];

		// Bail out, if source is empty.
		if ( ! file_exists( $abs_source ) ) {
			return new WP_Error(
				'webp-img-error',
				sprintf(
					/* translators: Absolute path to Source Image. */
					__( 'Error: %s does not exist.', 'image-converter-webp' ),
					$abs_source
				)
			);
		}

		// Bail out, if it is not an image.
		$filetype = wp_check_filetype( $abs_source );
		if ( false === strpos( (string) $filetype['type'], 'image/' ) ) {
			return new WP_Error(
				'webp-img-error',
				sprintf(
					/* translators: Absolute path to Source Image. */
					__( 'Error: %s is not an image.', 'image-converter-webp' ),
					$abs_source
				)
			);
		}

		// Bail out, if image filetype isn't jpg, jpeg or png.
		if ( ! in_array( explode( '/', (string) $filetype['type'] )[1], [ 'jpg', 'jpeg', 'png' ], true ) ) {
			return new WP_Error(
				'webp-img-invalid-type',
				__( 'Error: Invalid image file type.', 'image-converter-webp' )
			);
		}

		// Bail out, if dest. image exists.
		if ( file_exists( $abs_dest ) ) {
			return $webp;
		}

		// Convert to WebP image.
		try {
			WebPConvert::convert( $abs_source, $abs_dest, $this->get_options() );
		} catch ( Exception $e ) {
			$webp = new WP_Error(
				'webp-img-error',
				sprintf(
					/* translators: Exception error msg. */
					__( 'Fatal Error: %s', 'image-converter-webp' ),
					$e->getMessage()
				)
			);
		}

		/**
		 * Fires after Image is converted.
		 *
		 * @since 1.0.1
		 * @since 1.1.0 Moved to Converter.
		 * @since 1.1.1 Rename hook to use `icfw` prefix.
		 *
		 * @param string|\WP_Error $webp          WebP Image URL or WP Error.
		 * @param int              $attachment_id Image ID.
		 *
		 * @return void
		 */
		do_action( 'icfw_convert', $webp, $attachment_id );

		return $webp;
	}

	/**
	 * Get Image source.
	 *
	 * Get the image's absolute path by replacing the
	 * uploads base URL with the uploads base directory.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Moved to Converter.
	 * @since 1.7.0 Renamed from `set_image_source` and made stateless.
	 *
	 * @param string $source_url Image URL.
	 * @return string
	 */
	protected function get_image_source( string $source_url ): string {
		$img_uploads_dir = wp_upload_dir();

		return str_replace( $img_uploads_dir['baseurl'], $img_uploads_dir['basedir'], $source_url );
	}

	/**
	 * Get Image destination.
	 *
	 * Using image sources, get absolute and relative
	 * paths for the WebP image.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Moved to Converter.
	 * @since 1.7.0 Renamed from `set_image_destination` and made stateless.
	 *
	 * @param string $source_url Image URL.
	 * @param string $abs_source Image absolute path.
	 *
	 * @return array{abs: string, rel: string}
	 */
	protected function get_image_destination( string $source_url, string $abs_source ): array {
		$image_extension = '.' . pathinfo( $source_url, PATHINFO_EXTENSION );

		$image_abs_dest = str_replace( $image_extension, '.webp', $abs_source );
		$image_rel_dest = str_replace( $image_extension, '.webp', $source_url );

		/**
		 * Filter Image absolute destination.
		 *
		 * This filter provides a way for users to rename
		 * the absolute source image of the webp to a preferred
		 * file naming convention of their choice.
		 *
		 * @since 1.5.0
		 *
		 * @return string
		 */
		$abs_dest = (string) apply_filters( 'icfw_image_abs_destination', $image_abs_dest, $abs_source, $image_extension );

		/**
		 * Filter Image relative destination.
		 *
		 * This filter provides a way for users to rename
		 * the destination image of the webp to a preferred
		 * file naming convention of their choice.
		 *
		 * @since 1.5.0
		 *
		 * @return string
		 */
		$rel_dest = (string) apply_filters( 'icfw_image_rel_destination', $image_rel_dest, $source_url, $image_extension );

		return [
			'abs' => $abs_dest,
			'rel' => $rel_dest,
		];
	}

	/**
	 * Get Options.
	 *
	 * A list of Conversion options to be used
	 * when converting images to WebP format. E.g. quality...
	 *
	 * @since 1.0.0
	 * @since 1.0.2 Add plugin settings to options.
	 * @since 1.1.0 Moved to Converter.
	 *
	 * @return mixed[]
	 */
	protected function get_options(): array {
		$options = get_option( 'icfw', [] );

		/**
		 * Get Conversion options.
		 *
		 * @since 1.0.0
		 * @since 1.1.0 Moved to Converter.
		 * @since 1.1.1 Rename hook to use `icfw` prefix.
		 *
		 * @param mixed[] $options Conversion options.
		 * @return mixed[]
		 */
		$options = (array) apply_filters( 'icfw_options', $options );

		// Default conversion options.
		$default = [
			'quality'     => 20,
			'max-quality' => 100,
			'converter'   => 'gd',
		];

		// Validate the converter option against the allowed engines.
		if ( isset( $options['converter'] ) ) {
			$options['converter'] =
				in_array(
					$options['converter'],
					[ 'gd', 'cwebp', 'ffmpeg', 'imagick', 'gmagick' ],
					true
				)
				? $options['converter']
				: $default['converter'];
		}

		// Ensure the quality is a valid integer.
		if ( isset( $options['quality'] ) ) {
			$options['quality'] = (int) $options['quality'];

			// Ensure the quality is set to a value greater than zero.
			if ( ! $options['quality'] || ( $options['quality'] < 0 ) ) {
				$options['quality'] = $default['quality'];
			}
		}

		// Ensure the max-quality is a valid integer.
		if ( isset( $options['max-quality'] ) ) {
			$options['max-quality'] = (int) $options['max-quality'];

			// Ensure the max-quality is set to a value greater than zero.
			if ( ! $options['max-quality'] || ( $options['max-quality'] < 0 ) ) {
				$options['max-quality'] = $default['max-quality'];
			}
		}

		// Fallback to sensible default options.
		$options = wp_parse_args( $options, $default );

		return array_filter(
			$options,
			fn( $key ) => in_array( $key, array_keys( $default ), true ),
			ARRAY_FILTER_USE_KEY
		);
	}
}
