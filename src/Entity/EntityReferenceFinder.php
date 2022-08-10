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
   * @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface
   */
  private $list;

  /**
   * @param \Drupal\Core\Field\EntityReferenceFieldItemListInterface $list
   */
  public function __construct(EntityReferenceFieldItemListInterface $list) {
    $this->list = $list;
  }

  /**
   * Find all ancestor relationships sharing the same field.
   *
   * @return \Drupal\Core\Field\EntityReferenceFieldItemListInterface
   *   A new instance with all ancestors included.
   */
  public function withAllAncestors(): EntityReferenceFieldItemListInterface {
    return $this->find(TRUE, FALSE);
  }

  /**
   * Find all child relationships sharing the same field.
   *
   * @return \Drupal\Core\Field\EntityReferenceFieldItemListInterface
   *   A new instance with all children included.
   */
  public function withAllChildren(): EntityReferenceFieldItemListInterface {
    return $this->find(FALSE, TRUE);
  }

  /**
   * Find all ancestor and child relationships sharing the same field.
   *
   * @return \Drupal\Core\Field\EntityReferenceFieldItemListInterface
   *   A new instance with all children and ancestors included.
   */
  public function withAllRelatives(): EntityReferenceFieldItemListInterface {
    return $this->find(TRUE, TRUE);
  }

  private function find(bool $ancestors, bool $children) {
    $id = $this->list->getEntity()->id();
    $context = [
      'field_name' => $this->list->getFieldDefinition()->getName(),
      'ids' => [$id],
      'list' => [['target_id' => $id]],
    ];
    if ($children) {
      $this->findChildren($this->list->getValue(), $context);
    }
    if ($ancestors) {
      $this->findAncestors($this->list->getEntity(), $context);
    }

    uasort($context['list'], function ($a, $b) {
      return $a['target_id'] - $b['target_id'];
    });

    $list = clone $this->list;
    $list->filter('is_null');
    foreach ($context['list'] as $item) {
      $list->appendItem($item['target_id']);
    }

    return $list;
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
  private function findAncestors(EntityInterface $target_entity, array &$context): void {
    $ancestors = Loft::entityReverseReference($target_entity, [$context['field_name']], $target_entity->getEntityTypeId());
    foreach ($ancestors as $ancestor) {
      if (!in_array($ancestor->id(), $context['ids'])) {
        $context['ids'][] = $ancestor->id();
        $context['list'][] = ['target_id' => $ancestor->id()];
        $this->findAncestors($ancestor, $context);
      }
    }
  }

  /**
   * Search out all dependent equipment IDs and present as one array.
   *
   * @param $value
   *   An array as coming from field_equipment->getValue(), where each item is
   *   an array with the key "target_id".
   * @param array $context
   */
  private function findChildren($value, array &$context): void {
    if (is_array($value) and !empty($value)) {
      foreach (array_keys($value) as $k) {
        $node = \Drupal::entityTypeManager()
          ->getStorage('node')
          ->load($value[$k]['target_id']);
        if (!in_array($value[$k]['target_id'], $context['ids'])) {
          $context['ids'][] = $value[$k]['target_id'];
          $context['list'][] = ['target_id' => $value[$k]['target_id']];
        }
        $v = $node->{$context['field_name']}->getValue();
        $this->findChildren($v, $context);
      }
    }
  }

}
