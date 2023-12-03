# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.1] - 2023-12-03

### Deprecated

* Drupal\loft_core\Utility\BatchProcessorBase swith to https://intheloftstudios.com/packages/drupal/drupal_batch_framework


## [2.0] - 2023-10-23

### Changed

- **BREAKING CHANGE** When calling `loft_core_bem` you get back two functions; if you pass to either of those functions string that is incompatible with https://github.com/aklump/bem an exception is now thrown. You should grep your entire codebase for loft_core_bem and review all arguments, and test thoroughly.

## [1.8] - 2023-10-12

### Added

- `\Drupal\loft_core\FeatureSwitches\OperatorAdapter`

### Deprecated

- `is_live()`; replace with https://github.com/aklump/drupal_feature_switches and use `\Drupal\loft_core\FeatureSwitches\OperatorAdapter` as necessary.

## [1.7] - 2022-05-18

### Changed

- `Cypress::with('0')` will now print `0` in the element portion instead of dropping. Before the output was `foo`; now the output is `foo__0`.

### Removed

- HtmlDomParser library (sunra/php-simple-html-dom-parser)

## [1.6] - 2022-03-25

### Added

- \Drupal\loft_core\Plugin\rest\AnnotatedCollectionJsonResponse
- \Drupal\loft_core\Service\DatesService::getLocalTimeZone()

### Removed

- BREAKING CHANGE!!! Public property \Drupal\loft_core\Service\DatesService::localTimeZone

## [8.x-1.3.10] - 2021-06-27

### Fixed

- Remote images without extensions will now have extensions added when copying them, by detecting the image type. This fixes an issue with vimeo thumbnails being saved without file extensions.

## [8.x-1.3] - 2020-12-23

### Added

- No Orphans filter to prevent single word orphans. Add it to your text format(s). Learn more at \Drupal\loft_core\Plugin\Filter\NoOrphansFilter

### Removed

- The extra email from address "Registration email address". There is no upgrade path, this simple breaks implementations.

## [8.x-1.2.7] - 2020-11-16

### Added

- _Share link_ tab.

### Removed

- `$config['loft_core.settings']['permalink_type'] = 'absolute';`

## [8.x-1.2.1] - 2020-07-02

### Deprecated

- \Drupal\loft_core\Utility\BemTrait - Use \Drupal\front_end_components\BemTrait instead and declare a dependency on that module.

## [8.x-1.2] - 2020-07-01

### Added

- Added \Drupal\loft_core\Utility\ExpiringCacheTags

## [8.x-1.16] - 2020-04-13

### Removed

1. HOOK_loft_core_redirect_node_BUNDLE_TYPE_view; use [Rabbit Hole Hooks](https://github.com/aklump/drupal_rh_hooks) instead.
1. `HOOK_loft_core_redirect_node_BUNDLE_TYPE_edit`; no alternative given.
1. `HOOK_loft_core_redirect_node_BUNDLE_TYPE_delete`; no alternative given.
1. You should use

## [8.x-1.1.15] - 2019-09-23

### Removed

- DRUPAL_ENV_ROLE and drupalSettings.env.env
- You must replace all usages of DRUPAL_ENV_ROLE with DRUPAL_ENV; having two concepts was too darn confusing.

## 8.x-1.0-rc1

- The API for the redirect hooks have changed. You must update the following implementations per _loft_core.api.php_.

  * `HOOK_loft_core_redirect_node_BUNDLE_TYPE_view`
  * `HOOK_loft_core_redirect_node_BUNDLE_TYPE_edit`
  * `HOOK_loft_core_redirect_node_BUNDLE_TYPE_delete`

## 0.9.12

- BREAKING CHANGE: The structure of `theme_form_help` has changed, it is now wrapped with a <blockquote>, and uses different class names.
