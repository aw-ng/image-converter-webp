<?php
/**
 * PageLoad Class.
 *
 * This class handles the conversion of images during
 * page loads.
 *
 * @package ImageConverterWebP
 */

namespace ImageConverterWebP\Services;

use DOMDocument;
use ImageConverterWebP\Interfaces\Kernel;
use ImageConverterWebP\Abstracts\Service;

class PageLoad extends Service implements Kernel {
	/**
	 * Bind to WP.
	 *
	 * @since 1.1.0
	 *
	 * @return void
	 */
	public function register(): void {
		add_filter( 'render_block', [ $this, 'register_render_block' ], 20, 2 );
		add_filter( 'wp_get_attachment_image', [ $this, 'register_wp_get_attachment_image' ], 10, 5 );
		add_filter( 'post_thumbnail_html', [ $this, 'register_post_thumbnail_html' ], 10, 5 );
	}

	/**
	 * Render Image Block with WebP Images.
	 *
	 * Loop through each block and swap regular images for
	 * WebP versions.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Moved to PageLoad class.
	 *
	 * @param string  $html  Image HTML.
	 * @param mixed[] $block Block array.
	 *
	 * @return string
	 */
	public function register_render_block( $html, $block ): string {
		// Bail out, if empty or NOT image.
		if ( empty( $html ) || ! preg_match( '/<img.*>/', $html, $image ) ) {
			return $html;
		}

		return $this->get_webp_image_html( $html );
	}

	/**
	 * Generate WebP on wp_get_attachment_image.
	 *
	 * Filter WP image on the fly for image display used in
	 * posts, pages and so on.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Moved to PageLoad class.
	 *
	 * @param string       $html          HTML img element or empty string on failure.
	 * @param int          $attachment_id Image attachment ID.
	 * @param string|int[] $size          Requested image size.
	 * @param bool         $icon          Whether the image should be treated as an icon.
	 * @param string[]     $attr          Array of attribute values for the image markup, keyed by attribute name.
	 *
	 * @return string
	 */
	public function register_wp_get_attachment_image( $html, $attachment_id, $size, $icon, $attr ): string {
		if ( empty( $html ) ) {
			return $html;
		}

		$html = $this->get_webp_image_html( $html, $attachment_id );

		/**
		 * Filter WebP Image HTML.
		 *
		 * @since 1.0.0
		 * @since 1.1.0 Moved to PageLoad class.
		 * @since 1.1.1 Rename hook to use `icfw` prefix.
		 *
		 * @param string $html          WebP Image HTML.
		 * @param int    $attachment_id Image ID.
		 *
		 * @return string
		 */
		return (string) apply_filters( 'icfw_attachment_html', $html, $attachment_id );
	}

	/**
	 * Generate WebP on post_thumbnail_html.
	 *
	 * Filter WP post thumbnail by grabbing the DOM and
	 * replacing with generated WebP images.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Moved to PageLoad class.
	 *
	 * @param string         $html         The post thumbnail HTML.
	 * @param int            $post_id      The post ID.
	 * @param int            $thumbnail_id The post thumbnail ID, or 0 if there isn't one.
	 * @param string|int[]   $size         Requested image size.
	 * @param string|mixed[] $attr         Query string or array of attributes.
	 *
	 * @return string
	 */
	public function register_post_thumbnail_html( $html, $post_id, $thumbnail_id, $size, $attr ): string {
		if ( empty( $html ) ) {
			return $html;
		}

		$html = $this->get_webp_image_html( $html, $thumbnail_id );

		/**
		 * Filter WebP Image Thumbnail HTML.
		 *
		 * @since 1.0.0
		 * @since 1.1.1 Rename hook to use `icfw` prefix.
		 *
		 * @param string $html         WebP Image HTML.
		 * @param int    $thumbnail_id The post thumbnail ID, or 0 if there isn't one.
		 *
		 * @return string
		 */
		return (string) apply_filters( 'icfw_thumbnail_html', $html, $thumbnail_id );
	}

	/**
	 * Get WebP image HTML.
	 *
	 * This generic method uses the original image HTML to generate
	 * a WebP-Image HTML. This is useful for images that pre-date the installation
	 * of the plugin on a WP Instance.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Moved to PageLoad class.
	 *
	 * @param string $html Image HTML.
	 * @param int    $id   Image Attachment ID.
	 *
	 * @return string
	 */
	protected function get_webp_image_html( $html, $id = 0 ): string {
		// Bail out, if empty or NOT image.
		if ( empty( $html ) || ! preg_match( '/<img.*>/', $html, $image ) ) {
			return $html;
		}

		// Ensure this is allowed.
		if ( ! icfw_get_settings( 'page_load' ) ) {
			return $html;
		}

		$html = wp_filter_content_tags( $html );

		$dom = new DOMDocument();
		$dom->loadHTML( $html, LIBXML_NOERROR );

		// Generate WebP images.
		foreach ( $dom->getElementsByTagName( 'img' ) as $image ) {
			// For the src image.
			$src    = $image->getAttribute( 'src' );
			$srcset = $image->getAttribute( 'srcset' );

			// Generate the main WebP image for non-webp images.
			if ( $src && ( pathinfo( $src, PATHINFO_EXTENSION ) !== 'webp' ) ) {
				$html = icfw_get_webp_path( $html );
			}

			// Generate WebP images for srcset variations.
			if ( $srcset ) {
				$srcset_images = $this->get_all_srcset_images( $srcset );
				$srcset_webps  = array_map( fn( $srcset ) => $this->get_webp( $srcset, $id ), $srcset_images );

				$html = str_replace( $srcset_images, $srcset_webps, $html );
			}
		}

		return $html;
	}

	/**
	 * Reusable method for obtaining new Image HTML string.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Moved to PageLoad class.
	 *
	 * @param string $img_url  Relative path - 'https://example.com/wp-content/uploads/2024/01/sample.png'.
	 * @param int    $img_id   Image Attachment ID.
	 *
	 * @return string
	 */
	protected function get_webp( $img_url, $img_id ): string {
		// Get image ID.
		if ( ! $img_id ) {
			$img_id = attachment_url_to_postid( $img_url );
		}

		// Set Source.
		$this->source = [
			'id'  => $img_id,
			'url' => $img_url,
		];

		// Convert image to WebP.
		$webp = $this->converter->convert();

		// Fail gracefully, return default image.
		if ( is_wp_error( $webp ) ) {
			return $img_url;
		}

		return $webp;
	}

	/**
	 * Get all srcset images.
	 *
	 * @since 1.4.0
	 *
	 * @param string $srcset Source set string containing image URLs with size variations.
	 * @return string[]
	 */
	protected function get_all_srcset_images( $srcset ): array {
		preg_match_all( '/https?:\/\/[^\s,]+\.(?:png|jpe?g|webp|gif|svg|avif)/i', $srcset, $matches );

		return $matches[0];
	}
}
