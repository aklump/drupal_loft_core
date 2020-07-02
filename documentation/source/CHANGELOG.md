# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
- lorem

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

- The API for the redirect hooks have changed.  You must update the following implementations per _loft_core.api.php_.

  * `HOOK_loft_core_redirect_node_BUNDLE_TYPE_view`
  * `HOOK_loft_core_redirect_node_BUNDLE_TYPE_edit`
  * `HOOK_loft_core_redirect_node_BUNDLE_TYPE_delete`

## 0.9.12

- BREAKING CHANGE: The structure of `theme_form_help` has changed, it is now wrapped with a <blockquote>, and uses different class names.
