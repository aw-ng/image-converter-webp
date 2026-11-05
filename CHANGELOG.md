# Changelog

## 1.7.0
* Fix: Set the default quality to 20 when no value is explicitly set in the plugin options.
* Fix: Ensure correct saved option settings are passed to `WebPConvert` class.

## 1.6.0
* Feat: Add `More Plugins` options page.

## 1.5.2
* Tested up to WP 7.0.

## 1.5.1
* Chore: Update CI/CD pipeline.

## 1.5.0
* Feat: Add translation languages for `Japanese`,`Indonesian`,`Danish`, `Turkish`, `Polish`, `Dutch`, `Brazil` and `Portuguese`.
* Feat: Add new custom filter `icfw_image_abs_destination` and `icfw_image_rel_destination`.
* Docs: Update README notes.
* Tested up to WP 6.9.

## 1.4.1
* Specify `wordpress-plugin` as Composer package type.
* Tested up to WP 6.9.

## 1.4.0
* Fix: Resolve issue for Image with no `srcset` attribute.
* Fix: Missing thumbnail images in Media Library.
* Chore: Improve menu visibility on WP admin dashboard.
* Tests: Refactor & Improve PHP unit tests.
* Docs: Update README notes.
* Update to WP 6.9.

## 1.3.2
* Translate Options label correctly.
* Tested up to WP 6.8.

## 1.3.1
* Chore: Add WP local dev env.
* Chore: Update README docs.
* Tested up to WP 6.7.2.

## 1.3.0
* Implement conversion for WP scaled images.
* Added Unit tests.
* Update README notes.
* Tested up tp WP 6.7.2.

## 1.2.1
* Implement WebP image display on WP Media Library.
* Prevent style bleeding from Options page.
* Update Unit Tests & Code Coverage.
* Update README notes.
* Tested up to WP 6.7.1.

## 1.2.0
* Resolve issue with undefined array keys in Main service.
* Serve WebP images in WP media library.
* Refactor Form & Option classes.
* Make strings translatable across plugin.
* Fix failing tests, add new tests.
* Update README notes.
* Tested up to WP 6.7.1.

## 1.1.2
* Refactor Admin page, make extensible with new classes.
* Add new custom filter `icfw_form_fields`.
* Add new Log error option in Admin page.
* Update translation files.
* Update Unit Tests.
* Update README notes.

## 1.1.1
* Ensure WP_Error is passed and returned to Hook.
* Rename hooks across codebase to use `icfw` prefix.
* Implement Kernel interface.
* Fix bugs & failing tests.
* Update README notes.

## 1.1.0
* Major code base refactor.
* Add more **Settings** options to **Settings** page.
* Update language translations.
* Fix bugs & linting issues.
* Update README notes.

## 1.0.5
* Add language translation.
* Add error logging capabilities to **Settings** page.
* Add more Unit tests, Code coverage.
* Fix bugs & linting issues.
* Update README notes.

## 1.0.4
* Add more Unit tests, Code coverage.
* Fix bugs & linting issues.
* Fix nonce related problems with settings page.
* Update plugin folder name, file & text domain.
* Update build, deploy-ignore listing.
* README and change logs.

## 1.0.3
* Update Plugin display name to __Image Converter for WebP__.
* Update README notes and change logs.
* Update version numbers.
* Add more Unit tests & Code coverage.

## 1.0.2
* Add `icfw_delete` and `icfw_metadata_delete` hooks.
* Add Settings page for plugin options.
* Add WebP field on WP attachment modal.
* Add new class methods.
* Fix Bugs and Linting issues within class methods.
* Add more Unit tests & Code coverage.
* Update README notes.

## 1.0.1
* Refactor hook `icfw_convert` to placement within convert public method.
* Fix bugs within `Plugin` class methods.
* Add more Unit tests & Code coverage.
* Update README notes.

## 1.0.0 (Initial Release)
* WebP image conversion for any type of image.
* Custom Hooks - `icfw_options`, `icfw_convert`, `icfw_attachment_html`, `icfw_thumbnail_html`.
* Unit Tests coverage.
* Tested up to WP 6.5.3.
