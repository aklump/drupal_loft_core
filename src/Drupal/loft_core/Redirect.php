<?php

namespace Drupal\loft_core;

/**
 * Class Redirect
 *
 * @package Drupal\loft_core
 */
class Redirect {

    /**
     * Tell if a node is being redirected by our hooks.
     *
     * You can check a node type by spoofing $node, e.g. (object) ['type' =>
     * 'blog']
     *
     * @param \stdClass $node
     *
     * @return bool
     */
    public static function isNodeRedirected(\stdClass $node, $op = 'view')
    {
        if (isset($node->nid) && static::getNodeRedirect($node, $op)) {
            return true;
        }

        // Then look at the modules implemented our hooks, at the node type.
        // This will be used when nodes are creating, before their id is set.
        return boolval(static::getImplementingModuleName($node->type));
    }

    /**
     * Return a redirect based on a menu object
     *
     * @param null $op  One of: create, view, edit, delete
     * @param null $path *
     *
     * @return array|null
     */
    public static function getNodeMenuObjectRedirect($op = 'view', $path = null)
    {
        $path = empty($path) ? current_path() : $path;
        if (strpos($path, 'node/') === 0
            // use preg match to make sure with in the viewing path
            && preg_match('/^node\/\d+$/', $path)

            // finally get the node.  Of course this approach will not work if the standard node view pages have changed, in which case such a custom module needs to do something else like this.
            && ($node = menu_get_object('node', 1, $path))
        ) {
            return Redirect::getNodeRedirect($node, $op);
        }

        return null;
    }

    /**
     * Return redirects for a given $datum (node)
     *
     * @param \stdClass $node
     *
     * @return array|null
     *
     * // TODO Benchmark how this would work with db caching enabled.
     */
    public static function getNodeRedirect(\stdClass $node, $op = 'view')
    {
        $redirects = &drupal_static(__CLASS__ . '::' . __FUNCTION__, []);
        $static_key = $node->nid;
        if (!array_key_exists($static_key, $redirects)) {
            $bundle = $node->type;
            $item = null;
            if (!$module = static::getImplementingModuleName($bundle)) {
                return $item;
            }
            $function = $module . '_' . static::getHook($bundle, $op);
            $item = ['page callback' => 'drupal_goto'];
            $result = $function($node);
            switch ($result) {
                case MENU_ACCESS_DENIED:
                    $item['page callback'] = 'drupal_access_denied';
                    $item['page arguments'] = [];
                    break;
                case MENU_NOT_FOUND:
                    $item['page callback'] = 'drupal_not_found';
                    $item['page arguments'] = [];
                    break;
                default:
                    $result = is_array($result) ? array_values($result) : array($result);
                    if (empty($result[0])) {
                        $item = null;
                    }
                    else {
                        $item['page arguments'] = $result + array(
                                '<front>',
                                array(),
                                // We want to make all redirects permanent by default.
                                301,
                            );
                    }
                    break;
            }
            $redirects[$static_key] = $item;
        }

        return $redirects[$static_key];
    }

    /**
     * Get the hook name based on an op.
     *
     * @param string $bundle
     * @param string $op One of: view, update, delete. Defaults to view.
     *
     * @return string
     */
    protected static function getHook($bundle, $op = 'view')
    {
        return 'loft_core_redirect_node_' . $bundle . "__$op";
    }

    protected static function getImplementingModuleName($bundle)
    {
        $modules = module_implements(static::getHook($bundle));

        // For performance, only the last module hook will be used.  If you need to, set the weight of your module high so it becomes the last.
        return end($modules);
    }
}
