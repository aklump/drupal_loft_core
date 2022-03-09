<?php

namespace Drupal\loft_core\Service;

interface VisualMediaInterface {

  const PORTRAIT = 0;

  const SQUARE = 1;

  const LANDSCAPE = 2;

  public function getAspectRatio(string $uri): float;

  public function getWidth(string $uri): float;

  public function getHeight(string $uri): float;

  /**
   * Get the orientation of the media.
   *
   * @return int
   *
   * @see \Drupal\loft_core\Service\VisualMediaInterface::PORTRAIT;
   * @see \Drupal\loft_core\Service\VisualMediaInterface::SQUARE;
   * @see \Drupal\loft_core\Service\VisualMediaInterface::LANDSCAPE;
   */
  public function getOrientation(string $uri): int;
}
