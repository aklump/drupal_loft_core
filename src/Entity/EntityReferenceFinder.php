<?php

namespace Drupal\loft_core\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\loft_core\Loft;

/**
 * A class to find field reference relationships in one or both directions.
 */
final class EntityReferenceFinder {

  /**
   * @var \Drupal\Core\Entity\EntityInterface
   */
  private $entity;

  /**
   * @var string
   */
  private $fieldName;

  /**
   * @param EntityInterface $entity
   * @param string $field_name
   *   The field name to recurse with.
   */
  public function __construct(EntityInterface $entity, string $field_name) {
    $this->entity = $entity;
    $this->fieldName = $field_name;
  }

  /**
   * Find all ancestor relationships sharing the same field.
   *
   * @return int[]
   *   A new instance with all ancestors included.
   */
  public function getEntityIdsReferencingThis(): array {
    return $this->find(TRUE, FALSE);
  }

  /**
   * Find all child relationships sharing the same field.
   *
   * @return int[]
   *   A new instance with all children included.
   */
  public function getEntityIdsThisReferences(): array {
    return $this->find(FALSE, TRUE);
  }

  /**
   * Find all ancestor and child relationships sharing the same field.
   *
   * @return int[]
   *   A new instance with all children and ancestors included.
   */
  public function getEntityIdsOfAllReferences(): array {
    return $this->find(TRUE, TRUE);
  }

  private function find(bool $inbound, bool $outbound) {
    $context = [

      // We ignore the id that was used to begin the search.  The caller can add
      // this in if it wants.
      'ignore' => [$this->entity->id()],
      'ids' => [],
    ];
    if ($outbound) {
      $this->findOutboundReferences([$this->entity->id()], $context);
    }
    if ($inbound) {
      $this->findInboundReferences($this->entity, $context);
    }
    usort($context['ids'], function ($a, $b) {
      return $a - $b;
    });

    return ['node' => $context['ids']];
  }

  /**
   * Find all ancestral relationships.
   *
   * @param \Drupal\Core\Entity\EntityInterface $target_entity
   * @param array $context
   *
   * @return void
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function findInboundReferences(EntityInterface $target_entity, array &$context): void {
    $inbound = Loft::entityReverseReference($target_entity, [$this->fieldName], $target_entity->getEntityTypeId());
    foreach ($inbound as $ancestor) {
      if (!in_array($ancestor->id(), $context['ignore'])) {
        $context['ignore'][] = $ancestor->id();
        $context['ids'][] = intval($ancestor->id());
        $this->findInboundReferences($ancestor, $context);
      }
    }
  }

  private function findOutboundReferences($value, array &$context): void {
    if (is_array($value) and !empty($value)) {
      foreach ($value as $id) {
        $node = \Drupal::entityTypeManager()
          ->getStorage('node')
          ->load($id);
        if (!in_array($id, $context['ignore'])) {
          $context['ignore'][] = $id;
          $context['ids'][] = intval($id);
        }
        $list = $node->{$this->fieldName};
        if ($list) {
          $v = array_map(function ($item) {
            return $item['target_id'];
          }, $list->getValue());
          $this->findOutboundReferences($v, $context);
        }
      }
    }
  }

}
