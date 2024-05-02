<!--
id: entities
tags: ''
-->

# Working With Entities Cheat Sheet

Using `EntityTrait` you want to always cast a variable at the top of your script, this will speed up your code.  The benchmarks show that the `get()` and `f()` methods have no performance difference; however instantiating the service expensive.

    $extract = \Drupal::service('itls.extract')->setEntity($node);
    $extract->f(...
    $extract->f(...
    $extract->f(...
    $extract->f(...

## Pulling Raw Data
    
    // Define the default and the field name.
    $url = $extract->f('#', 'field_url');
  
## Pulling out Markup Safe Data

    $summary = $extract->safe('', 'field_summary');
    
## Pulling out field items array

    $items = $extract->items('field_references');

If you don't have access to the _extract_ service, then use this:
    
    $items = $n->get($node, 'field_references.0', []);

## Technical Details

### Markup Safe

When given an entity field item the safe value will be the first of:

    $extract->safe('', 'field_thing');

1. `$entity->field_thing['und'][0]['safe_value']`
2. `check_markup($entity->field_thing['und'][0]['value'], $entity->field_thing['und'][0]['format'])`
1. `Core::getSafeMarkupHandler()`
