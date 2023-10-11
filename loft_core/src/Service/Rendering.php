<?php

namespace Drupal\loft_core\Service;

use Drupal\Component\Render\MarkupInterface;
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
   * Test a variable to see if it's going to render anything.
   *
   * This can be a render array, a MarkupInterface, or an array of those.  Basically
   * if you can pass it to Twig in {{ variable }} and want to know if it will show
   * anything, use this method.
   *
   * @param array $variable
   *   The subject to test.
   *
   * @return bool
   *   TRUE if there is something besides comments and tags.
   */
  public function hasOutput($variable): bool {

    // Isolate the array so it's not mutated by renderPlain.
    $copy_of_variable = $variable;

    // Quick reduction of empty content.
    if (is_array($copy_of_variable)) {
      $copy_of_variable = array_filter($copy_of_variable);
    }
    $has_output = FALSE;
    $this->hasOutputHelper($copy_of_variable, $has_output);

    return $has_output;
  }

  private function hasOutputHelperFilterEmptyArrays(array $array) {
    foreach ($array as &$value) {
      if (is_array($value)) {
        $value = $this->hasOutputHelperFilterEmptyArrays($value);
      }
    }

    return array_filter($array, function ($item) {
      return !is_array($item) || count($item) > 0;
    });
  }

  private function hasOutputHelper($value, &$output, $key = NULL) {
    if (is_array($value)) {
      $value = $this->hasOutputHelperFilterEmptyArrays($value);
    }
    else {

      // Ignore properties because they will not render anything.
      if (is_string($key) && '#' === $key[0]) {
        return;
      }

      // These will render by Twig, cast to a string.
      if ($value instanceof MarkupInterface) {
        $value = strval($value);
      }
      $output = !empty($value);
    }

    if (!empty($value) && is_array($value) && !is_null($key) && (is_int($key) || $key === '' || $key[0] !== '#')) {
      $rendered = trim(strip_tags($this->renderer->renderPlain($value)));
      $output = boolval($rendered);
    }

    if ($output) {
      return;
    }

    foreach ($value as $key => $item) {
      $this->hasOutputHelper($item, $output, $key);
      if ($output) {
        return;
      }
    }
  }

}
