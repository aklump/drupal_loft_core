# Working With Entities Cheat Sheet

Whether using `data_api()` or `EntityTrait` you want to always cast a variable at the top of your script, this will speed up your code.  The benchmarks show that the `get()` and `f()` methods have no performance difference; however instantiating the service is 4x as expensive as `data_api()`.

    $n = data_api('node');
    $n->get(...
    $n->get(...
    $n->get(...
    $n->get(...

or 

    $extract = \Drupal::service('itls.extract')->setEntity('node', $node);
    $extract->f(...
    $extract->f(...
    $extract->f(...
    $extract->f(...

## Pulling Raw Data
    
    // Define the default and the field name.
    $url = $extract->f('#', 'field_url');
    
If you don't have access to the _extract_ service, then use this more verbose method:
    
    // Define the entity, path to value, and default.
    $url = $n->get($node, 'field_url.0.value', '#');
  
## Pulling out Markup Safe Data

    $summary = $extract->safe('', 'field_summary');
    
## Pulling out field items array

    $items = $extract->items('field_references');

If you don't have access to the _extract_ service, then use this:
    
    $items = $n->get($node, 'field_references.0', []);
