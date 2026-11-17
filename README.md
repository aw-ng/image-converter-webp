# image-converter-webp

Convert your WordPress JPG/PNG images to WebP formats during runtime.

[![Coverage Status](https://coveralls.io/repos/github/aw-ng/image-converter-webp/badge.svg)](https://coveralls.io/github/aw-ng/image-converter-webp)

![screenshot](https://github.com/aw-ng/image-converter-webp/assets/149586343/9c4a9cb2-63a0-462c-9ba1-a7adf23e51ea)

## Download

Download from [WordPress plugin repository](https://wordpress.org/plugins/image-converter-webp/).

You can also get the latest version from any of our [release tags](https://github.com/aw-ng/image-converter-webp/releases).

## Why WebP Image Converter?

As an internet user, you already know images can be the difference between a great website experience and a terrible one! Think about how often you've landed on a website and hit the back button because the home page was too busy or the banner image was taking so much time to load due to its size.

You may not realize it, but __imagery is a large part of it__. This plugin helps take care of all those concerns, by converting your WordPress images to WebP format during page load so that your site loads extremely fast, without any disruptions or downtime.

### Hooks

### `icfw_image_abs_destination` && `icfw_image_rel_destination`

This custom hook (filter) provides the ability for users to rename the image to a preferred file naming convention of their choice. For e.g you could append the timestamp to the name of the file like so:

```php
add_filter( 'icfw_image_abs_destination', 'custom_abs_destination', 10, 3 );
add_filter( 'icfw_image_rel_destination', 'custom_rel_destination', 10, 3 );

public function custom_abs_destination( $abs_dest, $abs_source, $ext ): void {
  $icfw_timestamp = time();
  return str_replace( '.webp', sprintf( '-%d.webp', $icfw_timestamp ), $abs_dest );
}

public function custom_rel_destination( $rel_dest, $rel_source, $ext ): void {
  $icfw_timestamp = time();
  return str_replace( '.webp', sprintf( '-%d.webp', $icfw_timestamp ), $rel_dest );
}
```

<br/>

#### `icfw_options`

This custom hook (filter) provides the ability to add custom options for your image conversions to WebP. For e.g. to perform a 50% quality, image conversion using the Imagick extension, you could do:

```php
add_filter( 'icfw_options', [ $this, 'custom_options' ] );

public function custom_options( $options ): array {
    $options = wp_parse_args(
        [
            'quality'   => 50,
            'converter' => 'imagick',
        ],
        $options
    );

    return (array) $options;
}
```

**Parameters**

- options _`{array}`_ By default this will be an associative array containing key, value options of each image conversion.
<br/>

#### `icfw_convert`

This custom hook (action) fires immediately after the image is converted to WebP. For e.g. you can capture errors to a custom post type of yours like so:

```php
add_action( 'icfw_convert', [ $this, 'log_webp_errors' ], 10, 2 );

public function log_webp_errors( $webp, $attachment_id ): void {
    if ( is_wp_error( $webp ) ) {
        wp_insert_post(
            [
                'post_type'   => 'webp_error',
                'post_title'  => (string) $webp->get_error_message(),
                'post_status' => 'publish',
            ]
        )
    }
}
```

**Parameters**

- webp _`{string|WP_Error}`_ By default this will be the WebP return value after an image conversion is done. If successful, a string is returned, otherwise a WP_Error instance is.
- attachment_id _`{int}`_ By default this is the Image ID.
<br/>

#### `icfw_attachment_html`

This custom hook (filter) provides the ability to modify the resulting WebP image HTML. For e.g. you can nest your image HTML into a figure element like so:

```php
add_filter( 'icfw_attachment_html', [ $this, 'custom_img_html' ], 10, 2 );

public function custom_img_html( $html, $attachment_id ): string {
    return sprintf(
        '<figure>
          %s
          <figcaption>Image ID: %s</figcaption>
        </figure>',
        (string) $html,
        (string) $attchment_id
    );
}
```

**Parameters**

- webp _`{string}`_ By default this will be the image HTML.
- attachment_id _`{int}`_ By default this is the Image ID.
<br/>

#### `icfw_form_fields`

This custom hook (filter) provides the ability to add custom fields to the Admin options page like so:

```php
add_filter( 'icfw_form_fields', [ $this, 'custom_form_fields' ] );

public function custom_form_fields( $fields ): array {
    $fields = wp_parse_args(
        [
            'custom_group'  => [
                'label'    => 'Custom Heading',
                'controls' => [
                    'custom_option_1' => [
                        'control' => 'text',
                        'label'   => 'My Custom Option 1',
                        'summary' => 'Enable this option to save my custom option 1.',
                    ],
                    'custom_option_2' => [
                        'control' => 'select',
                        'label'   => 'My Custom Option 2',
                        'summary' => 'Enable this option to save my custom option 2.',
                        'options' => [],
                    ],
                    'custom_option_3' => [
                        'control' => 'checkbox',
                        'label'   => 'My Custom Option 3',
                        'summary' => 'Enable this option to save my custom option 3.',
                    ],
                ],
            ],
        ],
        $fields
    );

    return (array) $fields;
}
```

**Parameters**

- fields _`{array}`_ By default this will be an associative array containing key, value options of each field option.
<br/>

#### `icfw_thumbnail_html`

This custom hook (filter) provides the ability to modify the resulting WebP image HTML. For e.g. you can nest your image HTML into a figure element like so:

```php
add_filter( 'icfw_thumbnail_html', [ $this, 'custom_img_html' ], 10, 2 );

public function custom_img_html( $html, $thumbnail_id ): string {
    return sprintf(
        '<figure>
          %s
          <figcaption>Image ID: %s</figcaption>
        </figure>',
        (string) $html,
        (string) $thumbnail_id
    );
}
```

**Parameters**

- webp _`{string}`_ By default this will be the image HTML.
- thumbnail_id _`{int}`_ By default this is the Image ID.
<br/>

#### `icfw_delete`

This custom hook (action) fires immediately after a WebP image is deleteed.

```php
add_action( 'icfw_delete', [ $this, 'delete_bmp_image' ], 10, 2 );

public function delete_bmp_image( $webp, $attachment_id ): void {
    $bmp = str_replace( '.webp', '.bmp', $webp );

    if ( file_exists( $bmp ) ) {
        unlink( $bmp );
    }
}
```

**Parameters**

- webp _`{string}`_ By default this will be the absolute path of the WebP image.
- attachment_id _`{int}`_ By default this is the Image ID.
<br/>

#### `icfw_metadata_delete`

This custom hook (action) fires immediately after a WebP metadata image is deleteed.

```php
add_action( 'icfw_metadata_delete', [ $this, 'delete_bmp_image' ], 10, 2 );

public function delete_bmp_image( $webp, $attachment_id ): void {
    $bmp = str_replace( '.webp', '.bmp', $webp );

    if ( file_exists( $bmp ) ) {
        unlink( $bmp );
    }
}
```

**Parameters**

- webp _`{string}`_ By default this will be the absolute path of the WebP metadata image.
- attachment_id _`{int}`_ By default this is the Image ID.
<br/>

---

## Contribute

Contributions are __welcome__ and will be fully __credited__. To contribute, please fork this repo and raise a PR (Pull Request) against the `master` branch.

### Pre-requisites

You should have the following tools before proceeding to the next steps:

- Composer
- Yarn
- Docker

To enable you start development, please run:

```bash
yarn start
```

This should spin up a local WP env instance for you to work with at:

```bash
http://icfw.localhost:5447
```

You should now have a functioning local WP env to work with. To login to the `wp-admin` backend, please use `admin` for username & `password` for password.

__Awesome!__ - Thanks for being interested in contributing your time and code to this project!
