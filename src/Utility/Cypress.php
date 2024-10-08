<?php

namespace Drupal\loft_core\Utility;

use AKlump\Bem\Fluent\Bem;
use AKlump\Bem\Styles\OfficialPassThrough;
use Drupal\Core\Template\Attribute;
use RuntimeException;

/**
 * Supports testing with Cypress.
 *
 * @code
 *  $form = [...];
 *
 *  // Like this if you have multiple tagging to do:
 *  $cy = new Cypress('user_register');
 *  $cy->tag($form)->with('form');
 *
 *  // Or like this for a single tag.
 *  Cypress::create('user_register')->tag($form)->with('form');
 * @endcode
 *
 * @link https://www.cypress.io/
 */
final class Cypress {

  /**
   * @var string
   */
  private $block;

  /**
   * @var mixed
   */
  private $targetElement;

  /**
   * Cypress constructor.
   *
   * @param string $block
   *   The prefix for all tags.
   */
  public function __construct(string $block) {
    $this->block = $block;
  }

  /**
   * Static instanction for one-off usage.
   *
   * @param string $block
   *
   * @return static
   */
  public static function create(string $block) {
    return new Cypress($block);
  }

  /**
   * Indicate the suffix to use when tagging $targetElement.
   *
   * @param string $element
   *   The suffix to use combined with the construction prefix.
   */
  public function with(string $element): void {
    if (is_null($this->targetElement)) {
      throw new RuntimeException("You must call ::tag() first.");
    }

    $bem = new Bem($this->block, NULL, new OfficialPassThrough());
    if (empty($element) && !is_numeric($element)) {
      $test_id = $bem->block()->toString();
    }
    else {
      $test_id = $bem->element($element)->toString();
    }
    if (is_array($this->targetElement)) {

      // We assume we have a render array.
      $key = '#attributes';

      // This will work for $variables from preprocess functions.
      if (isset($this->targetElement['attributes'])) {
        $key = 'attributes';
      }
      $this->targetElement[$key]['data-testid'] = $test_id;
    }
    elseif ($this->targetElement instanceof Attribute) {
      $this->targetElement->setAttribute('data-testid', $test_id);
    }
    unset($this->targetElement);
  }

  /**
   * Indicate the item to be tagged for Cypress testing.
   *
   * @param array|Attribute $targetElement
   *   Renderable arrays such as for a button or a textfield.  Or an instance of
   *   an Attribute.
   *
   * @return $this
   *   Self for chaining.
   */
  public function tag(&$targetElement): Cypress {
    $this->targetElement =& $targetElement;

    return $this;
  }

}
