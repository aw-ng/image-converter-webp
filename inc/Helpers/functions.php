<?php
/**
 * Functions.
 *
 * This class holds reusable utility functions that can be
 * accessed across the plugin.
 *
 * @package ImageConverterWebP
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Plugin Options.
 *
 * As the name implies, this function simply grabs the
 * plugin options.
 *
 * @since 1.1.1
 *
 * @param string $option   Plugin option to be retrieved.
 * @param string $fallback Default return value.
 *
 * @return mixed
 */
function icfw_get_settings( $option, $fallback = '' ) {
	return get_option( 'icfw', [] )[ $option ] ?? $fallback;
}

/**
 * Get WebP absolute image.
 *
 * This function checks to see if a WebP absolute image exists and
 * proceeds to return same.
 *
 * @since 1.2.0
 *
 * @param int $image_id Image ID.
 * @return string
 */
function icfw_get_abs_image( $image_id ): string {
	// Bail out, if it is not an image.
	if ( ! wp_attachment_is_image( (int) $image_id ) ) {
		return '';
	}

	// Get default WP absolute image.
	$wp_abs_image = (string) get_attached_file( $image_id );

	// Get WebP absolute image.
	$webp_abs_image = icfw_get_webp_path( $wp_abs_image );

	// Bail out, if it does not exist.
	if ( ! file_exists( $webp_abs_image ) ) {
		return '';
	}

	return $webp_abs_image;
}

/**
 * Get WebP path
 *
 * This function correctly replaces the extension of the image
 * to webp.
 *
 * @since 1.7.0
 *
 * @param string $path Image path.
 * @return string
 */
function icfw_get_webp_path( $path ): string {
	return (string) preg_replace( '/\.[^.\/]+$/', '.webp', $path );
}
