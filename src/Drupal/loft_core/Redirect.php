<?php

namespace Drupal\loft_core;

/**
 * Class Redirect
 *
 * @package Drupal\loft_core
 */
class Redirect {

    protected $bundle;
    protected $itemBase;
    protected $cache = [];

    /**
     * Redirect constructor.
     *
     * @param string $bundle       The type of bundle.
     * @param array  $menuItemBase The base array for generating all new menu
     *                             items. This SHOULD come from the menu item
     *                             'node/%node' available during
     *                             hook_menu_alter().
     */
    public function __construct($bundle, array $menuItemBase = [])
    {
        $this->bundle = $bundle;
        $this->itemBase = $menuItemBase;
    }

    /**
     * Return all node bundles that should be considered by us.
     *
     * @return array
     */
    public static function getSupportedBundles()
    {
        $info = entity_get_info('node');

        return array_keys($info['bundles']);
    }

    /**
     * Tell if a node is being redirected by our hooks.
     *
     * You can check a node type by spoofing $node, e.g. (object) ['type' =>
     * 'blog']
     *
     * @param \stdClass $node Must have one of either:
     *                       - nid
     *                       - type
     *
     * @return bool
     */
    public static function isNodeRedirected(\stdClass $node)
    {
        // First see if we have a record in the menu_router table, if we have a nid.
        if (isset($node->nid) && menu_get_item('node/' . $node->nid)) {
            return true;
        }

        // Then look at the modules implemented our hooks, at the node type.
        // This will be used when nodes are creating, before their id is set.
        return module_implements('loft_core_redirect_node_' . $node->type);
    }

    /**
     * Determine if a node's redirect has changed such that the menu needs to
     * be rebuilt.
     *
     * @param \stdClass $node
     *   Must include the key 'original' to compare against.
     *
     * @return bool
     */
    public static function detectNodeChange(\stdClass $node)
    {
        $n = data_api('node');
        $needs_update = false;
        $after = empty($node->original) ? new \stdClass : $node->original;

        // First we'll detect based on our node properties.
        foreach (array('title') as $prop) {
            if ($n->get($node, $prop) !== $n->get($after, $prop)) {
                $needs_update = true;
                break;
            }
        }

        if (!$needs_update) {
            drupal_alter('loft_core_redirect_needs_update', $needs_update, $node->type, $node, $after);
        }

        return $needs_update;
    }

    /**
     * Return an array of node data elements to be used for the redirect
     * callbacks.
     *
     * @return array
     */
    public function getNodeData()
    {
        if (!isset($this->cache['nodes'])) {
            $this->cache['nodes'] = $this->getQuery()
                                         ->execute()
                                         ->fetchAllAssoc('nid');
        }

        return $this->cache['nodes'];
    }

    /**
     * Return an array of all redirect menu items for this bundle.
     *
     * @return array
     */
    public function getBundleRedirects()
    {
        if (!isset($this->cache['redirects'])) {
            $redirect = null;
            $items = [];
            if (($module = $this->getImplementingModuleName())) {
                foreach ($this->getNodeData() as $datum) {
                    $datum = (object) $datum;
                    if ($item = $this->getRedirect($datum)) {
                        $items['node/' . $datum->nid] = $item;
                    }
                }
            }

            $this->cache['redirects'] = $items;
        }

        return $this->cache['redirects'];
    }

    /**
     * Return redirects for a given $datum (node)
     *
     * @param \stdClass $node This really just needs:
     *                        - nid
     *
     * @return array|null
     */
    public function getRedirect(\stdClass $node)
    {
        // Create the new menu item before altering.
        $datum = $this->getNodeData();
        $datum = $datum[$node->nid];
        $item = $this->getItem();
        $item['module'] = $module = $this->getImplementingModuleName();
        $function = $module . '_' . $this->getHook();
        $result = $function($datum, $item);
        if ($item) {
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
                    if ($item['page callback'] === 'drupal_goto') {
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
                    }
                    break;
            }

        }

        return $item;
    }

    protected function getQuery()
    {
        $hook = 'loft_core_redirect_node_' . $this->bundle;
        $query = db_select('node', 'n')
            ->fields('n', array('nid', 'title'))
            ->condition('type', $this->bundle)
            ->addTag($hook);

        return $query;
    }

    protected function getItem()
    {
        $item = $this->itemBase;
        $item['load_functions'] = array();
        $item['title callback'] = '';
        unset($item['title arguments']);
        $item['access callback'] = 'user_access';
        $item['access arguments'] = array('access content');
        $item['page callback'] = 'drupal_goto';
        $item['page arguments'] = array();

        return $item;
    }

    protected function getHook()
    {
        return 'loft_core_redirect_node_' . $this->bundle;
    }

    protected function getImplementingModuleName()
    {
        $modules = module_implements($this->getHook());

        // For performance, only the last module hook will be used.  If you need to, set the weight of your module high so it becomes the last.
        return end($modules);
    }
}
