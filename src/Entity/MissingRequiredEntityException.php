<?php

namespace Drupal\loft_core\Entity;

/**
 * An exception to throw when an entity is required for an operation.
 */
class MissingRequiredEntityException extends \RuntimeException {

  /**
   * MissingRequiredEntityException constructor.
   *
   * @param string $entity_type_id
   *   The entity type id.
   * @param array $bundles
   *   Optional, an array of valid bundles.
   */
  public function __construct(string $entity_type_id, array $bundles = []) {
    $message = "An entity of type $entity_type_id must be set first.";
    if ($bundles) {
      $message .= " It must be one of bundles: " . implode(', ', $bundles);
    }
    parent::__construct($message);
  }

}
