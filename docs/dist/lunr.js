var lunrIndex = [{"id":"trackjs","title":"Add TrackJS.com to your website","body":"1. Sign up for an account at http:\/\/www.trackjs.com\n2. Obtain your token and add it to `settings.php`\n\n        $conf['loft_core_trackjs_token'] = '5302679c8b624e0395a6a6da5b1199d6';\n\n3. Add the following snippet in `html.tpl.php` in your theme before `$scripts` output.\n\n4. This will only be present when `DRUPAL_ENV` is `prod`.\n\n## Config and Metadata\n\nSee `HOOK_loft_core_trackjs_alter()` for more info."},{"id":"ajax","title":"Ajax","body":"### Testing and Waiting for Ajax Responses\n\nIn automated testing you may need to wait for an ajax request to complete.  Here are some strategy.\n\n### Use `data-data-time`\n\nThe idea is to monitor a data attribute timestamp that gets updated by the ajax response, here is the markup model:\n\n    ...\n\nIn the page markup for the initial render you must call `loft_core_add_data_refresh`:\n\n    $attributes\n      ->addClass('story')\n      ->addClass(loft_core_test_class('story'));\n    gop3_core_include('ajax');\n    loft_core_add_data_refresh($attributes);\n\nIn your commands response you add this command:\n\n    public function getCommands__favorites__post(&$commands, $markup) {\n      $commands[] = loft_core_ajax_command_update_data_refresh('.story');\n    }\n\nThen, in the test method you do something like this:\n\n    $this->loadPageByUrl('\/node\/11206');\n    $el = $this->getDomElements([\n      '.t-story',\n      '.t-favorite-add--11206',\n    ]);\n    $el['.t-favorite-add--11206']->click();\n    $this->waitForDataRefresh('.t-story');"},{"id":"entity_archive","title":"Archiving Entities","body":"Here is an example of how to use `loft_core_update__archive_entities` in a `hook_update_n` implementation to archive entites.\n\n    \/**\n     * Create archive of the product entities.\n     *\n     * @throws \\DrupalUpdateException\n     *\/\n    function MODULE_update_N() {\n      $sql = \"SELECT\n      created,\n      product_id,\n      p.revision_id,\n      sku,\n      title,\n      type,\n      field_format_value AS format,\n      ROUND(commerce_price_amount \/ 100, 2) AS price,\n      field_xml_metadata_xml AS metadata,\n      filename AS image_filename,\n      uri AS image_uri,\n      field_description_references_nid AS related_nid,\n      field_product_description_value AS description,\n      field_product_overview_value AS overview,\n      field_product_contents_value AS contents\n    from commerce_product p\n      LEFT JOIN field_data_field_product_images pi ON (pi.entity_id = product_id)\n      LEFT JOIN field_data_commerce_price cp ON (cp.entity_id = product_id)\n      LEFT JOIN field_data_field_format ff ON (ff.entity_id = product_id)\n      LEFT JOIN field_data_field_product_description pd ON (pd.entity_id = product_id)\n      LEFT JOIN field_data_field_product_contents pc ON (pc.entity_id = product_id)\n      LEFT JOIN field_data_field_product_overview po ON (po.entity_id = product_id)\n      LEFT JOIN field_data_field_xml_metadata xml ON (xml.entity_id = product_id)\n      LEFT JOIN field_data_field_description_references fdr ON (fdr.entity_id = product_id)\n      LEFT JOIN file_managed f ON (f.fid = pi.field_product_images_fid)\n    WHERE 1;\";\n      module_load_include('install', 'loft_core', 'loft_core');\n\n      return loft_core_update__archive_entities(\n        'Create archive of the product entities.',\n        $sql,\n        'commerce_products',\n        [\n          ['image_filename', 'image_uri'],\n        ],\n        function ($key, &$value) {\n          if ($key === 'metadata') {\n            \/\/ Convert XML To JSON.\n            $value = simplexml_load_string($value);\n            $value = $value ? json_encode($value) : NULL;\n          }\n\n          return TRUE;\n        }\n      );\n    }"},{"id":"breadcrumb","title":"Breadcrumb","body":"Working with breadcrumbs can eased with `\\Drupal\\loft_core\\BreadcrumbMutator`.  See code comments for more info."},{"id":"changelog","title":"Changelog","body":"All notable changes to this project will be documented in this file.\n\nThe format is based on [Keep a Changelog](https:\/\/keepachangelog.com\/en\/1.0.0\/), and this project adheres to [Semantic Versioning](https:\/\/semver.org\/spec\/v2.0.0.html).\n\n## [3.0.0] - 2024-02-02\n\n### Added\n\n- Drupal 10 support.\n\n### Removed\n\n- `loft_core_testing` it is available as stand alone module now.\n- `loft_core_user_stash` function\n- `loft_core_users` module.  No longer available.\n- `static-content` stream wrapper\n\n## [2.0.9] - 2023-12-03\n\n### Deprecated\n\n* Drupal\\loft_core\\Utility\\BatchProcessorBase swith to https:\/\/intheloftstudios.com\/packages\/drupal\/drupal_batch_framework\n\n## [2.0] - 2023-10-23\n\n### Changed\n\n- **BREAKING CHANGE** When calling `loft_core_bem` you get back two functions; if you pass to either of those functions string that is incompatible with https:\/\/github.com\/aklump\/bem an exception is now thrown. You should grep your entire codebase for loft_core_bem and review all arguments, and test thoroughly.\n\n## [1.8] - 2023-10-12\n\n### Added\n\n- `\\Drupal\\loft_core\\FeatureSwitches\\OperatorAdapter`\n\n### Deprecated\n\n- `is_live()`; replace with https:\/\/github.com\/aklump\/drupal_feature_switches and use `\\Drupal\\loft_core\\FeatureSwitches\\OperatorAdapter` as necessary.\n\n## [1.7] - 2022-05-18\n\n### Changed\n\n- `Cypress::with('0')` will now print `0` in the element portion instead of dropping. Before the output was `foo`; now the output is `foo__0`.\n\n### Removed\n\n- HtmlDomParser library (sunra\/php-simple-html-dom-parser)\n\n## [1.6] - 2022-03-25\n\n### Added\n\n- \\Drupal\\loft_core\\Plugin\\rest\\AnnotatedCollectionJsonResponse\n- \\Drupal\\loft_core\\Service\\DatesService::getLocalTimeZone()\n\n### Removed\n\n- BREAKING CHANGE!!! Public property \\Drupal\\loft_core\\Service\\DatesService::localTimeZone\n\n## [8.x-1.3.10] - 2021-06-27\n\n### Fixed\n\n- Remote images without extensions will now have extensions added when copying them, by detecting the image type. This fixes an issue with vimeo thumbnails being saved without file extensions.\n\n## [8.x-1.3] - 2020-12-23\n\n### Added\n\n- No Orphans filter to prevent single word orphans. Add it to your text format(s). Learn more at \\Drupal\\loft_core\\Plugin\\Filter\\NoOrphansFilter\n\n### Removed\n\n- The extra email from address \"Registration email address\". There is no upgrade path, this simple breaks implementations.\n\n## [8.x-1.2.7] - 2020-11-16\n\n### Added\n\n- _Share link_ tab.\n\n### Removed\n\n- `$config['loft_core.settings']['permalink_type'] = 'absolute';`\n\n## [8.x-1.2.1] - 2020-07-02\n\n### Deprecated\n\n- \\Drupal\\loft_core\\Utility\\BemTrait - Use \\Drupal\\front_end_components\\BemTrait instead and declare a dependency on that module.\n\n## [8.x-1.2] - 2020-07-01\n\n### Added\n\n- Added \\Drupal\\loft_core\\Utility\\ExpiringCacheTags\n\n## [8.x-1.16] - 2020-04-13\n\n### Removed\n\n1. HOOK_loft_core_redirect_node_BUNDLE_TYPE_view; use [Rabbit Hole Hooks](https:\/\/github.com\/aklump\/drupal_rh_hooks) instead.\n1. `HOOK_loft_core_redirect_node_BUNDLE_TYPE_edit`; no alternative given.\n1. `HOOK_loft_core_redirect_node_BUNDLE_TYPE_delete`; no alternative given.\n1. You should use\n\n## [8.x-1.1.15] - 2019-09-23\n\n### Removed\n\n- DRUPAL_ENV_ROLE and drupalSettings.env.env\n- You must replace all usages of DRUPAL_ENV_ROLE with DRUPAL_ENV; having two concepts was too darn confusing.\n\n## 8.x-1.0-rc1\n\n- The API for the redirect hooks have changed. You must update the following implementations per _loft_core.api.php_.\n\n  * `HOOK_loft_core_redirect_node_BUNDLE_TYPE_view`\n  * `HOOK_loft_core_redirect_node_BUNDLE_TYPE_edit`\n  * `HOOK_loft_core_redirect_node_BUNDLE_TYPE_delete`\n\n## 0.9.12\n\n- BREAKING CHANGE: The structure of `theme_form_help` has changed, it is now wrapped with a , and uses different class names."},{"id":"clipboard","title":"Clipboard API","body":"This module provides a copy to clipboard on click API.  Here is an example implementation.\n\n1. Add the library `loft_core\/clipboard`.\n1. Add the following to a clickable element `data-loft-core-clipboard`, whose value is the value to be copied to the clipboard.\n\n## Optional attributes\n\n1. Add `data-loft-core-clipboard-confirm` with a value that will temporarily replace the inner html of the clicked element.  After a short delay the clicked element's original inner HTML will be returned.\n1. Control the reveal duration by setting `data-loft-core-clipboard-confirm-duration` to a millisecond value.\n\n## Code Example\n\n    Copy link"},{"id":"forms","title":"Forms API","body":"## Hide elements\n\nSee `loft_core_form_hide_elements()`.\n\n## Disable elements\n\nIt is nice to be able to keep an element visible, yet disable it. Making this easy is the goal of `loft_core_form_disable_elements()`.\n\n## Form help\n\nThis module defines a new element called 'form_help'. See `\\Drupal\\loft_core\\Element\\FormHelp` for usage examples.\n\n## Tabindex\n\n```php\nloft_core_form_tabindex()\n```"},{"id":"images","title":"Images","body":"## Cache Buster\n\n`loft_core_image_src_itok_cache_buster`"},{"id":"readme","title":"Loft Core","body":"![Loft Core](..\/..\/images\/loft_core.jpg)\n\nThis module contains features that I wish were in core, and which I often use for all my projects. Documentation can be found at _docs\/index.html_.\n\n## Install with Composer1. Because this is an unpublished package, you must define it's repository in\n   your project's _composer.json_ file. Add the following to _composer.json_ in\n   the `repositories` array:\n\n    ```json\n    {\n        \"type\": \"github\",\n        \"url\": \"https:\/\/github.com\/aklump\/drupal_loft_core\"\n    }\n    ```\n1. Require this package:\n\n    ```\n    composer require aklump_drupal\/loft_core:^3.0\n    ```\n1. Add the installed directory to _.gitignore_\n\n   ```php\n   \/web\/modules\/custom\/loft_core\/\n   ```\n\n1. Enable this module."},{"id":"permalink","title":"Permalink or \"Copy link\" tab","body":"For users with the correct permissions two tabs will be added to node pages, which when clicked copies a canonical link to the clipboard.  The first tab _Copy link_ copies a canonical internal link (no domain), and should be used for page to page linking within the website.  The second tab _Share link_ should be used links to embed in emails, social sharing, etc; outside of the website context, because it contains an absolute URL.\n\n## Hide\/show tabs by role\n\nToggle the permissions as necessary to hide\/show one or both tabs:\n\n    * loft_core access local link tab (Copy link)\n    * loft_core access permalink tab (Share link)\n\n## Change tab label\n\nTo change the tab label implement `hook_local_task_alter`.  See _loft_core.api.php_ for an example implementation."},{"id":"entity_protection","title":"Protecting Critical Entities","body":"With Loft Core, it's easy to protect critical entities that should not be deleted through the admin UI.  Learn more by looking at the docblocks in `\\Drupal\\loft_core\\Service\\EntityProtectionService`.\n\n1. Add this to _settings.php_, where `SE_CORE_` is the uppercase name of your custom module or other identifying prefix you wish to use for your PHP constants.\n\n        $config['loft_core.entity_protection']['prefix'] = 'SE_CORE_';\n\n1. Define some constants like so, in your core module file."},{"id":"redirects","title":"Redirects","body":"This module used to provide an API for entity redirects, but no longer.\n\n* Use exclusively the [Rabbit Hole](https:\/\/www.drupal.org\/project\/rabbit_hole) for redirecting entities.\n* Use [Rabbit Hole Hooks](https:\/\/github.com\/aklump\/drupal_rh_hooks) for hook-based redirection."},{"id":"roadmap","title":"Roadmap and Todo List","body":"- [ ] Port the functions in _loft_core.install_"},{"id":"settings","title":"settings.php","body":"## Files used\n\n    settings.env.php\n    settings.dev.php\n    settings.prod.php\n    settings.local.php\n\n## Changes to `settings.php`\n\nIf using environment variables then the following does not apply.\n\n1. Add the following lines to `settings.php` at the very end:\n\n        define('DRUPAL_ENV_PROD', 'prod');\n        define('DRUPAL_ENV_STAGING', 'staging');\n        define('DRUPAL_ENV_DEV', 'dev');\n        require dirname(__FILE__) . '\/settings.env.php';\n        require dirname(__FILE__) . '\/settings.' . DRUPAL_ENV . '.php';\n        require dirname(__FILE__) . '\/settings.local.php';\n\n1. Create a file `settings.env.php` with this:"},{"id":"smart_url","title":"Smart Urls","body":"* User enters: `http:\/\/www.mysite.com\/some-cool-aliased-title`\n\n* You want: `\/node\/123`\n\n* Use `loft_core_smart_url`\n\nImagine an admin entering a link to the site.  As developer want the unaliased relative path, as the admin, they copy and past the absolute aliased path.  It's a pain to explain to theme that they have to convert it to the way you want it, so you give up.  But that's where `loft_core_smart_url()` comes in.  Hand it the url they entered, and it will give you the url you want.\n\nAdd this to a presave hook after collection an url in a node form.\n\nAdd this to a filter that can then convert the bad urls to good urls.\n\nRock.\n\n## Configuration\n\nThis should work out of the box, but to fine tune it add something like the following to _settings.php_.\n\n    $config['loft_core.settings']['smart_url_regex'] = '\/mysite.(?:org|loft)$\/i';"},{"id":"testing","title":"Testing","body":"Test mode is enabled by default when `DRUPAL_ENV` does not equal `DRUPAL_ENV_PROD`.  This means that test classes will be appened to elements whenever the function `loft_core_test_class()` is used.\n\nWhen not in test mode, test classes will not be added.\n\n## Testing on Prod\n\nIn order to test against a production environment you need to enable test mode using an endpoint.  You must set up the endpoint with an access key in your settings.php like this:\n\n    $config['loft_core.settings']['test_mode_url_token'] = '{some obscure public key that will appear in your url}';\n\nWhen you visit the url endpoint, include the testing key like so:\n\n    \/loft-core\/testing\/enable\/{test_mode_url_token}\n\nThis will enable the test mode for a short duration and cause test classes to appear on production, for your IP only.  The response is JSON and contains the expiry timestamp.\n\n## Anomolies with Adding Test Classes\n\n### Paragraphs Add Widget\n\n![Paragraphs Add Widget](images\/paragraphs-widget.jpg)\n\nThe buttons on a paragraph element widget are really tricky, use `loft_core_paragraphs_element_add_test_classes` to simplify test classes.\n\nIn a form alter hook do something like:\n\n    loft_core_paragraphs_element_add_test_classes($form, [\n      'field_components',\n    ]);\n\nTo target the dropbutton toggle you may need to pick one of these:\n\n    .t-field_components_add .dropbutton__toggle\n    .t-field_components_add .dropbutton-toggle button\n\nTo target any of the add paragraph buttons:\n\n    .t-field_components_add__members_list\n    .t-field_components_add__members_photos\n    .t-field_components_add__...\n\nTo target any paragraph that has been added:\n\n    .t-field_components__item\n    .t-field_components__item1\n    .t-field_components__item...\n\n### Sometimes the auto classes creates duplicates.\n\nThis has shown up for WYSIWYG text areas, to handle this use also the element for your selector like this:\n\n        - .t-field_description\n        + textarea.t-field_description\n\n### Handling the Chosen Module\n\nYou may need to upgrade your selectors to use the `select` portion:\n\n        select.t-field_newsletter"},{"id":"user_persistent_dismiss","title":"User Persistent Dismiss","body":"This API provides a means of clicking and element and remembering that it was clicked so that element can be hidden on next page visit.\n\nAn example of this is a popup modal that should hide for 1 month when it's closed.\n\nImplementation code follows:\n\n## Block Scenario\n\nThis example shows how to use this API to track the appearance of a Drupal block.\n\n### Access Check\n\n```php\nfunction my_module_block_access(\\Drupal\\block\\Entity\\Block $block, $operation, \\Drupal\\Core\\Session\\AccountInterface $account) {\n  list($provider, $uuid) = explode(':', $block->getPluginId() . ':');\n\n  \/\/ First check that we have a block_content entity...\n  if ('view' === $operation && 'block_content' === $provider) {\n    $block_content = array_values(\\Drupal::entityTypeManager()\n        ->getStorage('block_content')\n        ->loadByProperties([\n          'uuid' => $uuid,\n        ]))[0] ?? NULL;\n\n    \/\/ ... then check if it's the bundle we want to track.\n    if ('foobar' === $block_content->bundle()) {\n      $dismiss = new \\Drupal\\loft_core\\Utility\\UserPersistentDismiss($block->getPluginId());\n      if ($dismiss->isDismissed()) {\n        return \\Drupal\\Core\\Access\\AccessResult::forbidden('Cookie exists with previous dismissal.');\n      }\n    }\n  }\n\n  \/\/ No opinion.\n  return \\Drupal\\Core\\Access\\AccessResult::neutral();\n}\n```\n\n### Block Build\n\n```php\nfunction my_module_block_content_view_alter(array &$build, \\Drupal\\Core\\Entity\\EntityInterface $entity, \\Drupal\\Core\\Entity\\Display\\EntityViewDisplayInterface $display) {\n  if ($entity->bundle() == 'foobar') {\n    $dismiss = new \\Drupal\\loft_core\\Utility\\UserPersistentDismiss($entity->getEntityTypeId() . ':' . $entity->uuid());\n    $build['close_button']['#attributes'] += $dismiss->getJavascriptDismiss()->toArray();\n    $dismiss->applyTo($build);\n  }\n}\n```"},{"id":"vimeo","title":"Vimeo","body":"* Use _\\Drupal\\loft_core\\Utility\\VimeoBasedEntityBuilder_ to help with pulling metadata from Vimeo into an entity.\n* https:\/\/developer.vimeo.com\/apps\n\n* This is incompatible with Drupal 9.5.\n\n## Required Modules\n\n* `composer require vimeo\/vimeo-api`\n\n## Suggested Modules\n\n* `composer require drupal\/video_embed_field`\n\n## _.env_\n\n        VIMEO_CLIENT_ID=\"...\"\n        VIMEO_CLIENT_SECRET=\"...\"\n        VIMEO_ACCESS_TOKEN=\"...\"\n\n## `hook_presave`\n\n        public function presave__video() {\n            if (!($vimeo_url = $this->f('', 'field_vimeo'))) {\n              return;\n            }\n            $provider = \\Drupal::service('video_embed_field.provider_manager')\n              ->createInstance('vimeo', ['input' => $vimeo_url]);\n            if (!($vimeo_id = $provider->getIdFromInput($vimeo_url))) {\n              return;\n            }\n            if (!($client_id = getenv('VIMEO_CLIENT_ID'))) {\n              throw new \\RuntimeException(\"Missing VIMEO_CLIENT_ID\");\n            }\n            if (!($secret = getenv('VIMEO_CLIENT_SECRET'))) {\n              throw new \\RuntimeException(\"Missing VIMEO_CLIENT_SECRET\");\n            }\n            if (!($token = getenv('VIMEO_ACCESS_TOKEN'))) {\n              throw new \\RuntimeException(\"Missing VIMEO_ACCESS_TOKEN\");\n            }\n            try {\n              $client = new Vimeo(\n                $client_id,\n                $secret,\n                $token\n              );\n              \\Drupal::service('loft_core.vimeo_based_entity')\n                ->setClient($client)\n                ->setTitleField('title')\n                ->setPosterField('field_video_poster')\n                ->fillWithRemoteData($this->getEntity(), $vimeo_id);\n            }\n            catch (\\Exception $exception) {\n              watchdog_exception('se.vimeo', $exception);\n            }\n        }"},{"id":"entities","title":"Working With Entities Cheat Sheet","body":"Using `EntityTrait` you want to always cast a variable at the top of your script, this will speed up your code.  The benchmarks show that the `get()` and `f()` methods have no performance difference; however instantiating the service expensive.\n\n    $extract = \\Drupal::service('itls.extract')->setEntity($node);\n    $extract->f(...\n    $extract->f(...\n    $extract->f(...\n    $extract->f(...\n\n## Pulling Raw Data\n\n    \/\/ Define the default and the field name.\n    $url = $extract->f('#', 'field_url');\n\n## Pulling out Markup Safe Data\n\n    $summary = $extract->safe('', 'field_summary');\n\n## Pulling out field items array\n\n    $items = $extract->items('field_references');\n\nIf you don't have access to the _extract_ service, then use this:\n\n    $items = $n->get($node, 'field_references.0', []);\n\n## Technical Details\n\n### Markup Safe\n\nWhen given an entity field item the safe value will be the first of:\n\n    $extract->safe('', 'field_thing');\n\n1. `$entity->field_thing['und'][0]['safe_value']`\n2. `check_markup($entity->field_thing['und'][0]['value'], $entity->field_thing['und'][0]['format'])`\n1. `Core::getSafeMarkupHandler()`"}]