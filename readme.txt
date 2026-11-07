=== Image Converter for WebP ===
Contributors: badasswp, activist507
Tags: webp, image, convert, jpeg, png.
Requires at least: 4.0
Tested up to: 7.0
Stable tag: 1.6.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Convert your WordPress JPG and PNG images to efficient WebP format, improving performance, reducing file size, and enhancing website speed.

== Installation ==

1. Go to <strong>Plugins</strong> > <strong>Add New</strong> on your WordPress admin dashboard.
2. Search for <strong>Image Converter for WebP</strong> plugin from the official WordPress plugin repository.
3. Click <strong>Install Now</strong> and then <strong>Activate</strong>.
4. Locate the <strong>Image Converter for WebP</strong> menu option on the WP admin dashboard.
5. Click on it.
6. You can now set your plugin options here. Ensure you enable the <strong>Convert Images on Upload</strong> option.
7. Now upload any image into your WP website, it would convert it to a WebP format that will be served on your pages when users visit your website.
8. You're all set!

== Description ==

As an internet user, you already know images can be the difference between a great website experience and a terrible one! Think about how often you've landed on a website and hit the back button because the home page was too busy or the banner image was taking so much time to load due to its size.

You may not realize it, but imagery is a large part of it. This plugin helps take care of all those concerns, by converting your WordPress images to WebP format during page load so that your site loads extremely fast, without any disruptions or downtime.

= ✔️ Features =

Our plugin comes with everything you need to convert your WP website images to WebP.

✔️ <strong>Convert your WP images to WebP</strong> feature.
✔️ <strong>Convert images on both upload and page load</strong>.
✔️ <strong>Conversion Quality & WebP Engine</strong> options.
✔️ <strong>Error Loggging Capabilities</strong>.
✔️ <strong>Custom Hooks</strong> to help you customize plugin behaviour.
✔️ Compatible with <strong>Divi & Elementor</strong> page builders.
✔️ Available in <strong>mutiple langauges</strong> such as Arabic, Chinese, Hebrew, Hindi, Russian, German, Italian, Croatian, Spanish & French languages.
✔️ <strong>Backward compatible</strong>, works with most WP versions.

= ✨ Getting Started =

1. Locate the <strong>Media</strong> menu option on the left side of the WP admin dashboard.
2. You should see the <strong>Image Converter for WebP</strong> menu listed as one of the options, click on it.
3. You can now set your plugin options here. Ensure you enable the <strong>Convert Images on Upload</strong> option.
4. Now upload any image into your WP website, it would convert it to a WebP format that will be served on your pages when users visit your website.
5. You're all set!

You can get a taste of how this works, by using the [demo](https://tastewp.com/create/NMS/8.0/6.7.0/image-converter-webp/twentytwentythree?ni=true&origin=wp) link.

= ⚡ WooCommerce, Posts, Pages & Images =

By default, <strong>Image Converter for WebP</strong>, will serve WebP images for your posts, pages and woocommerce pages. For future releases, you should be able to toggle this feature ON/OFF based on your needs.

NB: The <strong>Convert Images on Page Load</strong> option helps you convert and serve WebP images for images that were already uploaded on your WP website before the plugin was installed. It does this when the page or post that contains that image is loaded.

= 🔌🎨 Plug and Play or Customize =

The <strong>Image Converter for WebP</strong> plugin is built to work right out of the box. Simply install, activate, configure options and start using straight away.

Want to add your personal touch? All of our documentation can be found [here](https://github.com/badasswp/image-converter-webp). You can override the plugin's behaviour with custom logic of your own using [hooks](https://github.com/badasswp/image-converter-webp?tab=readme-ov-file#hooks).

== Screenshots ==

1. Generated WebP Image - Convert your images both on upload and page load easily.
2. Options Page - Configure your plugin options here.
3. Attachment Modal - See Converted WebP image path here.

== Changelog ==

= 1.7.0 =
* Fix: Set the default quality to 20 when no value is explicitly set in the plugin options.
* Fix: Ensure correct saved option settings are passed to `WebPConvert` class.
* Fix: Only allow image conversions for `image/jpeg` and `image/png` types.
* Fix: Only replace the image extension with `webp`.

= 1.6.0 =
* Feat: Add `More Plugins` options page.

= 1.5.2 =
* Tested up to WP 7.0.

= 1.5.1 =
* Chore: Update CI/CD pipeline.

= 1.5.0 =
* Feat: Add translation languages for `Japanese`,`Indonesian`,`Danish`, `Turkish`, `Polish`, `Dutch`, `Brazil` and `Portuguese`.
* Feat: Add new custom filter `icfw_image_abs_destination` and `icfw_image_rel_destination`.
* Docs: Update README notes.
* Tested up to WP 6.9.

= 1.4.1 =
* Specify `wordpress-plugin` as Composer package type.
* Tested up to WP 6.9.

= 1.4.0 =
* Fix: Resolve issue for Image with no `srcset` attribute.
* Fix: Missing thumbnail images in Media Library.
* Chore: Improve menu visibility on WP admin dashboard.
* Tests: Refactor & Improve PHP unit tests.
* Docs: Update README notes.
* Update to WP 6.9.

= 1.3.2 =
* Translate Options label correctly.
* Tested up to WP 6.8.

= 1.3.1 =
* Chore: Add WP local dev env.
* Chore: Update README docs.
* Tested up to WP 6.7.2.

= 1.3.0 =
* Implement conversion for WP scaled images.
* Added Unit tests.
* Update README notes.
* Tested up tp WP 6.7.2.

= 1.3.0 =
* Implement WebP image display on WP Media Library.
* Prevent style bleeding from Options page.
* Update Unit Tests & Code Coverage.
* Update README notes.
* Tested up to WP 6.7.1.

= 1.2.0 =
* Resolve issue with undefined array keys in Main service.
* Serve WebP images in WP media library.
* Refactor Form & Option classes.
* Make strings translatable across plugin.
* Fix failing tests, add new tests.
* Update README notes.
* Tested up to WP 6.7.1.

= 1.1.2 =
* Refactor Admin page, make extensible with new classes.
* Add new custom filter `icfw_form_fields`.
* Add new Log error option in Admin page.
* Update translation files.
* Update Unit Tests.
* Update README notes.

= 1.1.1 =
* Ensure WP_Error is passed and returned to Hook.
* Rename hooks across codebase to use `icfw` prefix.
* Implement Kernel interface.
* Fix bugs & failing tests.
* Update README notes.

= 1.1.0 =
* Major code base refactor.
* Add more **Settings** options to **Settings** page.
* Update language translations.
* Fix bugs & linting issues.
* Update README notes.

= 1.0.5 =
* Add language translation.
* Add error logging capabilities to **Settings** page.
* Add more Unit tests, Code coverage.
* Fix bugs & linting issues.
* Update README notes.

= 1.0.4 =
* Add more Unit tests, Code coverage.
* Fix bugs & linting issues.
* Fix nonce related problems with settings page.
* Update plugin folder name, file & text domain.
* Update build, deploy-ignore listing.
* README and change logs.

= 1.0.3 =
* Update Plugin display name to Image Converter for WebP.
* Update README and change logs.
* Update version numbers.
* Add more Unit tests & Code coverage.

= 1.0.2 =
* Add `icfw_delete` and `icfw_metadata_delete` hooks.
* Add Settings page for plugin options.
* Add WebP field on WP attachment modal.
* Add new class methods.
* Fix Bugs and Linting issues within class methods.
* Add more Unit tests & Code coverage.
* Update README notes.

= 1.0.1 =
* Refactor hook icfw_convert to placement within convert public method.
* Add more Unit tests & Code coverage.
* Update README notes.

= 1.0.0 =
* Initial release
* WebP image conversion for any type of image.
* Custom Hooks - icfw_options, icfw_convert, icfw_attachment_html, icfw_thumbnail_html.
* Unit Tests coverage.
* Tested up to WP 6.5.3.

== Contribute ==

If you'd like to contribute to the development of this plugin, you can find it on [GitHub](https://github.com/badasswp/image-converter-webp).
