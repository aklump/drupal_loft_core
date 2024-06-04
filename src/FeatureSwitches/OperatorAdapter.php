<?php

namespace Drupal\loft_core\FeatureSwitches;

use Drupal\feature_switches\Operator;
use JsonSerializable;

final class OperatorAdapter implements JsonSerializable {

  /**
   * @var \Drupal\feature_switches\Operator
   */
  private $operator;

  public function __construct(Operator $operator) {
    $this->operator = $operator;
  }

  #[\ReturnTypeWillChange]
  public function jsonSerialize() {
    $data = $this->operator->jsonSerialize();

    return array_combine(array_keys($data), array_map(function ($item) {
      unset($item['id']);
      $item['is_ready'] = $item['ready'];
      unset($item['ready']);
      $item['is_live'] = $item['live'];
      unset($item['live']);

      return $item;
    }, $data));
  }
}
