# Working with Entities

These examples use the `EntityTrait`

    $extract = \Drupal::service('itls.extract')->setEntity($entity_type, $entity);
    $default_value = 'lorem';

## Fields

When working with fields you probabably want to do one of:

* Get the raw value of field item (value, uri, target_id, etc).
* Display to the end user said value.
* Get the array of a field item.
* Get an array of all items for a field.

The goal of `EntityTrait` is to make these goals very, very simple for the developer.  So the signature of the methods reflect this.  Default values are required as a means of fallback.

### Display to the end user said value
    
These are all equivalent and give you the escaped version of `$entity->field_pull_quote[{language}][0]['value']` ready for output to the browser.

    $extract->safe('lorem', 'field_pull_quote'),
    $extract->safe('lorem', 'field_pull_quote', 'value'),
    $extract->safe('lorem', 'field_pull_quote', 'value', 0),
    $extract->safe('lorem', 'field_pull_quote', 0, 'value'),
    
If you need the value of the second item in the array, you have fewer choices, but here's how.

    $extract->safe('lorem', 'field_pull_quote', 'value', 1),
    $extract->safe('lorem', 'field_pull_quote', 1, 'value'),  

And if you need a different column, you'll do:

    $extract->safe('lorem', 'field_pull_quote', 'target_id'),
    $extract->safe('lorem', 'field_pull_quote', 'target_id', 0),
    $extract->safe('lorem', 'field_pull_quote', 0, 'target_id'),
    
Or.. for the second `target_id`:

    $extract->safe('lorem', 'field_pull_quote', 'target_id', 1),
    $extract->safe('lorem', 'field_pull_quote', 1, 'target_id'),  


#### Get the array of a field item.

    $extract->safe('lorem', 'field_pull_quote', 0),
    
    Array
    (
        [target_id] => 12
    )

### Get an array of all items for a field.

Use the `items` method.

    $extract->items('field_pull_quote'),
    
    Array
    (
        [0] => Array
            (
                [target_id] => 10
            )
    
        [1] => Array
            (
                [target_id] => 12
            )
    
    )

