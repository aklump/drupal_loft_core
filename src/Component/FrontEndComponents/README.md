# Front-End Components

## What is a Front-End Component?

A front-end component is contained in a single directory, the name of which is the component id.

## Implementation

Given a theme with the following structure:

    .
    └── my_theme
        ├── components
        │   └── admin_card
        │       ├── admin-card.html.twig
        │       ├── admin-card.php
        │       ├── admin-card.schema.yml
        │       └── admin-card.scss
        └── scss
            └── base.scss

You would implement like so:

    /**
     * Implements hook_theme().
     */
    function my_theme_theme($existing, $type, $theme, $path) {
      $fec = new FrontEndComponents($path, 'components');
      $themes = $fec->getHookThemeDefinitions();
    
      return $themes;
    }

## Handling the SCSS

This example is assuming you are using an SCSS preprocessor of some sort.  In such case you need to import your component's SCSS file, in our example we'll be using _base.scss_ as the parent SCSS file, to which we'd add the following.

    @import "../components/admin_card/admin-card.scss"

## PHP Preprocessing

If you include a PHP file, it is included when the component is rendered by Drupal, therefor you should place any functions needed therein.  For example this is where you define your component's hook_preprocess_THEME function, if needed.

## What's the Schema For?

The schema file is either JSON or YAML and defines a [JSON-Schema](https://json-schema.org/latest/json-schema-validation.html) representing the variables that your template expects or allows.  You may choose the format as your prefer.  Here's an example in YAML:

    properties:
      image_nid:
        type: number
        description: The node whose thumb will be used for the bg image.
      image_src:
        description: Instead of image_nid, you may specify the path directly.
      image_style:
        default: watch_cta
      href:
      title:
        default: '@Translation("Watch to learn more")'
      link_attributes:
        type: array

Notice several points:

* The default type is string, and may be omitted, e.g., `href`.
* Default values may be translated, e.g., `title.default`.
* Descriptions helpful for non-obvious variable names and may be used by automatic document generators.

## Translation Text

Follows the API as defined [here](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Annotation%21Translation.php/group/plugin_translatable/)
