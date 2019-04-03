<?php

namespace Drupal\loft_core;

use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\rabbit_hole\Plugin\RabbitHoleBehaviorPlugin\PageRedirect;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles entity redirection for loft_core.
 *
 * @package Drupal\loft_core
 */
class Redirect {

  /**
   * Tell if a node is being redirected by our hooks.
   *
   * You can check a node type by spoofing $node @code (object) ['type' =>
   * 'blog'] @endcode.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to look at.
   * @param string $op
   *   One of: view.
   *
   * @return bool
   *   True if a given node is redirected.
   *
   * @d8
   */
  public static function isNodeRedirected(NodeInterface $node, $op = 'view') {
    if ($node->id() && static::getNodeRedirect($node, $op)) {
      return TRUE;
    }

    // Then look at the modules implemented our hooks, at the node type.
    // This will be used when nodes are creating, before their id is set.
    return (bool) static::getImplementingModuleName($node->getType(), $op);
  }

  /**
   * Return redirects for a given $datum (node).
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to check redirect for.
   * @param string $op
   *   One of: view.
   *
   * @return array
   *   An empty array means no action, otherwise an array with keys:
   *   - rh_action
   *   - rh_redirect
   *   - rh_redirect_response
   *
   * @see \Drupal\rabbit_hole\BehaviorInvoker::getRabbitHoleValuesForEntity
   *
   * @d8
   */
  public static function getNodeRedirect(NodeInterface $node, $op = 'view'): array {
    // TODO Benchmark how this would work with db caching enabled.
    $redirects = &drupal_static(__CLASS__ . '::' . __FUNCTION__, array());
    $static_key = $node->id();
    if (!array_key_exists($static_key, $redirects)) {
      $bundle = $node->getType();
      $item = [];
      if (!$module = static::getImplementingModuleName($bundle, $op)) {
        return $item;
      }
      $function = $module . '_' . static::getHook($bundle, $op);
      $item = array(
        'rh_action' => 'page_redirect',

        // string, the path to redirect to.
        'rh_redirect' => '',

        // The response code, e.g. 301.
        'rh_redirect_response' => NULL,
      );
      try {
        $result = $function($node);
      }
      catch (AccessDeniedHttpException $exception) {
        $item['rh_action'] = 'access_denied';
      }
      catch (NotFoundHttpException $exception) {
        $item['rh_action'] = 'page_not_found';
      }
      if (empty($result)) {
        $item = [];
      }
      else {
        if (!is_array($result)) {
          $result = [$result, PageRedirect::REDIRECT_MOVED_PERMANENTLY];
        }
        list($item['rh_redirect'], $item['rh_redirect_response']) = array_values($result);
        if ($item['rh_redirect'] instanceof Url) {
          $item['rh_redirect'] = $item['rh_redirect']->toString();
        }
      }
      $redirects[$static_key] = $item;
    }

    return $redirects[$static_key];
  }

  /**
   * Get the hook name based on an op.
   *
   * @param string $bundle
   *   The name of the bundle.
   * @param string $op
   *   One of: view, update, delete. Defaults to view.
   *
   * @return string
   *   The name of the hook based on bundle and op.
   */
  protected static function getHook($bundle, $op = 'view') {
    return 'loft_core_redirect_node_' . $bundle . "_$op";
  }

  /**
   * Determine which module we listen to for the redirect alter.
   *
   * For performance, only the last module hook will be used.  If you need to,
   * set the weight of your module high so it becomes the last.
   *
   * @param string $bundle
   *   The node bundle.
   * @param string $op
   *   The op, one of: view.
   *
   * @return string
   *   The name of the module with the highest weight implementing a redirect
   *   hook for this bundle and op.
   */
  protected static function getImplementingModuleName($bundle, $op) {
    $modules = \Drupal::moduleHandler()
      ->getImplementations(static::getHook($bundle, $op));

    return end($modules);
  }

}
