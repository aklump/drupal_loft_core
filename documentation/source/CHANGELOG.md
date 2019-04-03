## 8.x-1.0-rc1

- The API for the redirect hooks have changed.  You must update the following implementations per _loft_core.api.php_.

  * `HOOK_loft_core_redirect_node_BUNDLE_TYPE_view`
  * `HOOK_loft_core_redirect_node_BUNDLE_TYPE_edit`
  * `HOOK_loft_core_redirect_node_BUNDLE_TYPE_delete`

## 0.9.12

- BREAKING CHANGE: The structure of `theme_form_help` has changed, it is now wrapped with a <blockquote>, and uses different class names.
