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
use ImageConverterWebP\Abstracts\Service;

class Converter {
	/**
	 * Image source (absolute path).
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Moved to Converter.
	 *
	 * @var string
	 */
	public string $abs_source = '';

	/**
	 * Image destination (absolute path).
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Moved to Converter.
	 *
	 * @var string
	 */
	public string $abs_dest = '';

	/**
	 * Image destination (relative path).
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Moved to Converter.
	 *
	 * @var string
	 */
	public string $rel_dest = '';

	/**
	 * Service.
	 *
	 * @since 1.1.0
	 *
	 * @var Service
	 */
	public Service $service;

	/**
	 * Get Service.
	 *
	 * Pass in Service class as constructor params
	 * to enable us retrieve ID|URL.
	 *
	 * @since 1.1.0
	 *
	 * @param Service $service
	 */
	public function __construct( Service $service ) {
		$this->service = $service;
	}

	/**
	 * Convert to WebP image.
	 *
	 * This method is responsible for taking the source image
	 * and converting it to the WebP equivalent.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Moved to Converter.
	 *
	 * @return string|\WP_Error
	 */
	public function convert() {
		// Set image source and destination.
		$this->set_image_source();
		$this->set_image_destination();

		// Set response.
		$webp = $this->rel_dest;

		// Bail out, if source is empty.
		if ( ! file_exists( $this->abs_source ) ) {
			return new WP_Error(
				'webp-img-error',
				sprintf(
					/* translators: Absolute path to Source Image. */
					__( 'Error: %s does not exist.', 'image-converter-webp' ),
					$this->abs_source
				)
			);
		}

		// Bail out, if it is not an image.
		$filetype = wp_check_filetype( $this->abs_source );
		if ( false === strpos( (string) $filetype['type'], 'image/' ) ) {
			return new WP_Error(
				'webp-img-error',
				sprintf(
					/* translators: Absolute path to Source Image. */
					__( 'Error: %s is not an image.', 'image-converter-webp' ),
					$this->abs_source
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
		if ( file_exists( $this->abs_dest ) ) {
			return $webp;
		}

		// Convert to WebP image.
		try {
			WebPConvert::convert( $this->abs_source, $this->abs_dest, $this->get_options() );
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
		do_action( 'icfw_convert', $webp, $attachment_id = $this->service->source['id'] );

		return $webp;
	}

	/**
	 * Set Image source.
	 *
	 * Get the image's relative path and replace with
	 * absolute path.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Moved to Converter.
	 *
	 * @return void
	 */
	protected function set_image_source(): void {
		$img_uploads_dir  = wp_upload_dir();
		$this->abs_source = str_replace( $img_uploads_dir['baseurl'], $img_uploads_dir['basedir'], $this->service->source['url'] );
	}

	/**
	 * Set Image destination.
	 *
	 * Using image sources, set absolute and relative
	 * paths for images.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Moved to Converter.
	 *
	 * @return void
	 */
	protected function set_image_destination(): void {
		$image_extension = '.' . pathinfo( $this->service->source['url'], PATHINFO_EXTENSION );

		// Get relative source url.
		$rel_source_url = $this->service->source['url'] ?? '';

		$image_abs_dest = str_replace( $image_extension, '.webp', $this->abs_source );
		$image_rel_dest = str_replace( $image_extension, '.webp', $rel_source_url );

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
		$this->abs_dest = apply_filters( 'icfw_image_abs_destination', $image_abs_dest, $this->abs_source, $image_extension );

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
		$this->rel_dest = apply_filters( 'icfw_image_rel_destination', $image_rel_dest, $rel_source_url, $image_extension );
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
