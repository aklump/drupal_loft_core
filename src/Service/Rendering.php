<?php

namespace Drupal\loft_core\Service;

use Drupal\Core\Render\Renderer;

/**
 * A service to help with render arrays.
 */
final class Rendering {

  /**
   * Service instance.
   *
   * @var \Drupal\loft_core\Service\Drupal\Core\Render\Renderer
   */
  private $renderer;

  /**
   * @param \Drupal\loft_core\Service\Drupal\Core\Render\Renderer $renderer
   *   A service instance.
   */
  public function __construct(Renderer $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * Test a render array for actual, content (text, images, etc).
   *
   * @param array $element
   *   A render element to be checked to see if it outputs anything besides comments or tags.
   *
   * @return bool
   *   TRUE if there is something besides comments and tags.
   */
  public function hasOutput(array $element): bool {

    // Isolate the array so it's not mutated by renderPlain.
    $copy_of_element = array_filter($element);
    $rendered = trim(strip_tags($this->renderer->renderPlain($copy_of_element)));

    return boolval($rendered);
  }

}
