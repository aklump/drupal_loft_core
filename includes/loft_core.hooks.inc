<?php

use Drupal\Core\Asset\AttachedAssetsInterface;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Drupal\loft_core\Utility\RenderInPageBottom;
use Drupal\loft_core_testing\Component\Utility\TestingMarkup;
use Drupal\Component\Utility\NestedArray;

/**
 * Implements hook_js_settings_build().
 */
function loft_core_js_settings_build(array &$settings, AttachedAssetsInterface $assets) {
  $settings['env'] = [
    'env' => DRUPAL_ENV,
    'prod' => DRUPAL_ENV_PROD,
    'staging' => DRUPAL_ENV_STAGING,
    'dev' => DRUPAL_ENV_DEV,
  ];
}

/**
 * Implements hook_preprocess_html().
 */
function loft_core_preprocess_html(&$vars) {
  $user = \Drupal::currentUser();
  $vars['loft_core_tracking'] = '';
  if (DRUPAL_ENV === 'prod' && ($token = \Drupal::config('loft_core.settings')
      ->get('loft_core_trackjs_token'))) {

    // @link http://docs.trackjs.com/tracker/configuration
    $trackjs = [
      'config' => '',
      'metadata' => [],
    ];

    $trackjs['config']['token'] = $token;

    if (function_exists('web_package_get_version')) {
      $trackjs['config']['version'] = web_package_get_version();
    }

    // TrackJS allows you to track errors for multiple applications from the same account.
    // See the UI documentation for how to setup applications.
    $trackjs['config']['application'] = NULL;

    // If you have some identifiable string that represents a user or customer,
    //please include it.
    // In the TrackJS UI we allow you to group and filter by this value.
    $trackjs['config']['userId'] = \Drupal::currentUser()
      ->isAuthenticated() ? $user->uid : NULL;
    $trackjs['metadata']['username'] = \Drupal::currentUser()
      ->isAuthenticated() ? $user->name : NULL;

    // Similar to userId,
    //if you have a session guid or something you can set it here.
    // We allow you to search on this value in the UI.
    $trackjs['config']['sessionId'] = session_id();

    \Drupal::moduleHandler()->alter('loft_core_trackjs', $trackjs);

    $config = json_encode(array_filter($trackjs['config']));

    $meta = [];
    foreach (array_filter($trackjs['metadata']) as $key => $value) {
      $meta[] = 'trackJs.addMetadata("' . $key . '",
            "' . $value . '");';
    }
    $meta = implode(';',
      $meta);
    $vars['loft_core_tracking'] = <<<EOD
<!-- BEGIN TRACKJS -->
<script type="text/javascript">window._trackJs = {$config};</script>
<script type="text/javascript" src="https://cdn.trackjs.com/releases/current/tracker.js"></script>
<script type="text/javascript">{$meta};</script>
<!-- END TRACKJS -->
EOD;
  }
}

/**
 * Implements hook_theme().
 */
function loft_core_theme($existing, $type, $theme, $path) {
  return [
    'form_help' => [
      'render element' => 'element',
    ],
  ];
}

/**
 * Implements hook_menu_links_discovered_alter().
 */
function loft_core_menu_links_discovered_alter(array &$links) {
}

/**
 * Implements hook_menu_local_tasks_alter().
 */
function loft_core_menu_local_tasks_alter(array &$data, $route_name) {
}

/**
 * Implements hook_menu_local_actions_alter().
 */
function loft_core_menu_local_actions_alter(array &$local_actions) {
}

/**
 * Implements hook_contextual_links_view_alter().
 */
function loft_core_contextual_links_view_alter(array &$element, array $items) {
  $id = NULL;
  foreach ($items as $item) {
    if (isset($item['route_parameters']['node'])) {
      $id = $item['route_parameters']['node'];
      $url = Url::fromRoute('entity.node.canonical', ['node' => $id]);
      break;
    }
  }

  if ($id) {
    $element['#links'] = array_merge(
      [
        'loft_core_id' => [
          'title' => $id,
          'url' => $url,
        ],
      ],
      $element['#links'],
    );
  }

}

/**
 * Implements HOOK_preprocess_status_messages().
 *
 * Remove any messages suppressed by loft_core_suppress_messages.
 */
function loft_core_preprocess_status_messages(&$vars) {
  // TODO Fix this fails at /admin/structure/webform/manage/earthrise_discussion_guide/settings
  return;
  if ($vars['message_list'] && ($masks = \Drupal::moduleHandler()
      ->invokeAll('loft_core_suppress_messages'))) {
    foreach (array_keys($masks) as $type) {
      if (isset($vars['message_list'][$type])) {
        foreach ($vars['message_list'][$type] as $key => $message) {
          foreach ($masks[$type] as $mask) {
            if (preg_match($mask, $message)) {
              unset($vars['message_list'][$type][$key]);
            }
          }
        }
      }
      $vars['message_list'] = array_filter($vars['message_list']);
    };
  }
}

/**
 * Implements hook_page_bottom().
 *
 * @deprecated
 */
function loft_core_page_bottom(&$page_bottom) {
  $page_bottom['render_in_page_bottom'] = [
    '#pre_render' => [[RenderInPageBottom::class, 'get']],
  ];
}

/**
 * Implements hook_preprocess_links__dropbutton__operations__paragraphs().
 *
 * Put all paragraphs in alphabetical order.
 */
function loft_core_preprocess_links__dropbutton__operations__paragraphs(&$vars) {
  uasort($vars['links'], function ($a, $b) {
    return strcasecmp((string) $a['text']['#value'], (string) $b['text']['#value']);
  });
}

/**
 * Implements hook_preprocess_menu_local_task().
 */
function loft_core_preprocess_menu_local_task(&$vars) {
  $route_name = $vars['link']['#url']->getRouteName();
  if (!in_array($route_name, [
    'loft_core.node.permalink',
    'loft_core.node.local_link',
  ])) {
    return;
  }

  $is_absolute = $route_name === 'loft_core.node.permalink';

  $vars['#attached']['library'][] = 'loft_core/clipboard';
  $vars['link']['#url']
    ->setOption('path_processing', FALSE)
    ->setOption('absolute', $is_absolute);
  $vars['link']['#options']['attributes']['data-loft-core-clipboard'] = $vars['link']['#url']->toString();
  $vars['link']['#options']['attributes']['data-loft-core-clipboard-confirm'] = t('Copied!', ['@value' => $vars['link']['#url']->toString()]);
  $vars['link']['#options']['attributes']['data-loft-core-clipboard-confirm-duration'] = 2000;

  // It's awkward to see the View tab and both of these tabs all active at the
  // same time, due to the shared route id.  So let's not make these active.
  $vars['link']['#options']['set_active_class'] = FALSE;
}

/**
 * Preprocess callback for form_help.
 *
 * @param array &$vars
 *
 * @return void
 */
function template_preprocess_form_help(array &$vars) {
  $element = $vars['element'];
  $vars['messages'] = $element['#message'];
  if (!is_array($vars['messages'])) {
    $vars['messages'] = [$vars['messages']];
  }
  $vars['children'] = $element['#children'];
}
