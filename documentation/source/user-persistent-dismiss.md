# User Persistent Dismiss

This API provides a means of clicking and element and remembering that it was clicked so that element can be hidden on next page visit.

An example of this is a popup modal that should hide for 1 month when it's closed.

Implementation code follows:

## Block Scenario

This example shows how to use this API to track the appearance of a Drupal block.

### Access Check

```php
function my_module_block_access(\Drupal\block\Entity\Block $block, $operation, \Drupal\Core\Session\AccountInterface $account) {
  list($provider, $uuid) = explode(':', $block->getPluginId() . ':');

  // First check that we have a block_content entity...
  if ('view' === $operation && 'block_content' === $provider) {
    $block_content = array_values(\Drupal::entityTypeManager()
        ->getStorage('block_content')
        ->loadByProperties([
          'uuid' => $uuid,
        ]))[0] ?? NULL;

    // ... then check if it's the bundle we want to track.
    if ('foobar' === $block_content->bundle()) {
      $dismiss = new \Drupal\loft_core\Utility\UserPersistentDismiss($block->getPluginId());
      if ($dismiss->isDismissed()) {
        return \Drupal\Core\Access\AccessResult::forbidden('Cookie exists with previous dismissal.');
      }
    }
  }

  // No opinion.
  return \Drupal\Core\Access\AccessResult::neutral();
}
```

### Block Build

```php
function my_module_block_content_view_alter(array &$build, \Drupal\Core\Entity\EntityInterface $entity, \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display) {
  if ($entity->bundle() == 'foobar') {
    $dismiss = new \Drupal\loft_core\Utility\UserPersistentDismiss($entity->getEntityTypeId() . ':' . $entity->uuid());
    $build['close_button']['#attributes'] += $dismiss->getJavascriptDismiss()->toArray();
    $dismiss->applyTo($build);
  }
}
```
