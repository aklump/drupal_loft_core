<?php

namespace Drupal\loft_core\Utility;

use Drupal\Core\Template\Attribute;

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

  use \Drupal\front_end_components\BemTrait;

  /**
   * @var string
   */
  private $block = '';

  /**
   * @var string
   */
  private $element = '';

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
    return new static($block);
  }

  /**
   * Indicate the suffix to use when tagging $targetElement.
   *
   * @param string $element
   *   The suffix to use combined with the construction prefix.
   */
  public function with(string $element): void {
    $this->element = $element;
    if (empty($this->targetElement)) {
      throw new \RuntimeException("You must call ::tag() first.");
    }
    if (is_array($this->targetElement)) {

      // We assume we have a render array.
      $key = '#attributes';

      // This will work for $variables from preprocess functions.
      if (isset($this->targetElement['attributes'])) {
        $key = 'attributes';
      }
      $this->targetElement[$key]['data-testid'] = $this->bemElement($this->element);
    }
    elseif ($this->targetElement instanceof Attribute) {
      $this->targetElement->setAttribute('data-testid', $this->bemElement($this->element));
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

  /**
   * {@inheritdoc}
   */
  public function bemBlock(): string {
    return $this->block;
  }

}
