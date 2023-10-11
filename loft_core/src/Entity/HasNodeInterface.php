<?php

namespace Drupal\loft_core\Entity;

use Drupal\node\NodeInterface;

/**
 * Interface HasNodeInterface.
 *
 * For classes that rely on an entity object.
 *
 * @package Drupal\loft_core\Node
 */
interface HasNodeInterface extends HasEntityInterface {

  /**
   * Set the node object.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The entity instance.
   *
   * @return \Drupal\loft_core\Node\HasNodeInterface
   *   Self for chaining.
   */
  public function setNode(NodeInterface $node);

  /**
   * Check if a node is set.
   *
   * @return bool
   *   True if a node is set.
   */
  public function hasNode(): bool;

  /**
   * Get the node instance.
   *
   * @return \Drupal\node\NodeInterface
   *   The node object.
   */
  public function getNode(): NodeInterface;

}
