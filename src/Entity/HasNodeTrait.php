<?php

namespace Drupal\loft_core\Entity;

use AKlump\LoftLib\Code\Cache;
use Drupal\node\NodeInterface;

/**
 * Trait HasNodeTrait for classes handling a single node object.
 *
 * @package Drupal\loft_core\Entity
 */
trait HasNodeTrait {

  /**
   * Return the node.
   *
   * @return \Drupal\node\NodeInterface
   *   A node instance.
   */
  public function getNode(): NodeInterface {
    return $this->getEntity();
  }

  /**
   * Set the node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node instance.
   *
   * @return \Drupal\loft_core\Entity\HasNodeTrait
   *   Self for chaining.
   */
  public function setNode(NodeInterface $node) {
    return $this->setEntity('node', $node);
  }

  /**
   * Return a string used for caching this instance in the database or in the
   * SESSION, etc.
   *
   * You must implement a method on the class called `getNodeCacheConfig` that
   * returns at least an empty array, if not meta data to further refine this
   * cache string, beyond the node/{bundle}/{nid}.
   *
   * @return string
   *  - If the entity is not set, you will get back the classname.
   *   "Drupal\gop3_core\Entity\RelatedContent:"
   *  - If the entity is set you will get back something like this, which
   *   includes the nid and the configuration md5:
   *  "Drupal\gop3_core\Entity\RelatedContent:3880:5d5a851aa176fff5a142c82555e69eae"
   */
  protected function getNodeCacheId() {
    try {
      $prefix = __CLASS__ . ':';
      list($type, $node) = $this->validateEntity();

      $config = array_merge([
        $type,
        $node->type,
        $this->getNode()->nid,

        // Any additional configuration options to make this cache id specific enough, should be returned from this method.
      ], $this->getNodeCacheConfig());

      return $prefix . $this->getNode()->nid . ':' . Cache::id($config);
    }
    catch (\Exception $exception) {
      return $prefix;
    }
  }

}
