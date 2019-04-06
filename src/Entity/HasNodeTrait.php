<?php

namespace Drupal\loft_core\Entity;

use Drupal\node\NodeInterface;

/**
 * Trait HasNodeTrait for classes handling a single node object.
 *
 * When using this trait your class MUST:
 * - use \Drupal\loft_core\Entity\HasEntityTrait
 * - implement \Drupal\loft_core\Entity\HasNodeInterface.
 * Your class SHOULD:
 * - make use of ::requireNode() inside of other methods.
 *
 * @package Drupal\loft_core\Entity
 */
trait HasNodeTrait {

  /**
   * {@inheritdoc}
   */
  public function setNode(NodeInterface $node) {
    return $this->setEntity($node);
  }

  /**
   * {@inheritdoc}
   */
  public function hasNode(): bool {
    return $this->hasEntity('node');
  }

  /**
   * {@inheritdoc}
   */
  public function getNode(): NodeInterface {
    return $this->getEntity('node');
  }

  /**
   * Require that a node be set or throw.
   *
   * Use this inside of methods on this class to ensure that they can be run
   * properly, when they require a node instance.  Optionally, you may specify
   * one or more bundles that the node must be.
   *
   * @code
   *  public function someClassMethod() {
   *    list($node, $bundle, $nid) = $this->requireNode();
   *    ...
   * @endcode
   *
   * @param array $required_bundles
   *   To allow all bundles leave these an empty array.  To require one or more
   *   bundles, send those bundle ids as an indexed array.
   *
   * @return array
   *   The node entity, bundle and it's id as indexed array.
   *
   * @throws \Drupal\loft_core\Entity\MissingRequiredEntityException
   *   If the node is not set, or it it's not one of the required bundles.
   */
  protected function requireNode(array $required_bundles = []) {
    $list = $this->requireEntity('node', $required_bundles);
    array_shift($list);

    return $list;
  }

}
