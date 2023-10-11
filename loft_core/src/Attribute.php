<?php

namespace Drupal\loft_core;

use Drupal\Core\Template\Attribute as CoreAttribute;

/**
 * Attributes object handler, adding methods for the "style" attribute.
 *
 * Adds shortcut methods for working with CSS styles, i.e., "style" attribute.
 */
class Attribute extends CoreAttribute {

  /**
   * Test if a CSS style is present.
   *
   * @param string $style_name
   *   The CSS style to check for, e.g., "background".
   *
   * @return bool
   *   True if present.
   */
  public function hasStyle($style_name) {
    return ($style = $this->getStyle()) && isset($style[$style_name]);
  }

  /**
   * Remove a CSS style if present.
   *
   * @param string $style_name
   *   The style attribute name, e.g. "font-size".
   *
   * @return \Drupal\loft_core\Attribute
   *   Self for chaining.
   */
  public function removeStyle($style_name) {
    if (($style = $this->getStyle())) {
      unset($style[$style_name]);
      $this->setStyle($style);
    }

    return $this;
  }

  /**
   * Add (replace) a CSS style by name.
   *
   * @param string $style_name
   *   The name of the style attribute, e.g., "color".
   * @param mixed $style_value
   *   It's value, e.g., "#fff".
   *
   * @return $this
   */
  public function addStyle($style_name, $style_value) {
    $style = $this->getStyle();
    $style[$style_name] = $style_value;
    $this->setStyle($style);

    return $this;
  }

  /**
   * Return the CSS styles as a key value associative array.
   *
   * @return array
   *   Keys are the style names, values are the values.
   */
  public function getStyle() {
    if (!($style = $this->offsetGet('style'))
      || !($style = $this->expandStyle($style))
    ) {
      return array();
    }

    return $style;
  }

  /**
   * Sets (replaces) the style tag using a key/value array.
   *
   * @param array $style
   *   An array of name/values to set into the style attribute.  The array is
   *   concantenated using ';', as you might expect.
   *
   * @see \Drupal\loft_core\Attribute::getStyle
   */
  public function setStyle(array $style) {
    if (empty($style)) {
      $this->removeAttribute('style');
    }
    elseif ($style = $this->contractStyle($style)) {
      $this->setAttribute('style', $style);
    }
  }

  /**
   * Contracts (implodes) a style key/value array into a string.
   *
   * @param array $style_attribute_array
   *   The style name/value array.
   *
   * @return string
   *   A string concantenated by ';'.
   */
  protected function contractStyle(array $style_attribute_array) {
    $style = array();
    foreach ($style_attribute_array as $style_name => $style_value) {
      $style[] = $style_name . ':' . $style_value;
    }

    return implode(';', $style);
  }

  /**
   * Expand a string style attribute value into an array.
   *
   * @param string $style_attribute_string
   *   The value of a style tag, e.g., "color: red; font-size: 14px".
   *
   * @return array
   *   Keys are the style names, values the values.
   */
  protected function expandStyle($style_attribute_string) {
    $expanded = array();
    if ($style_attribute_string) {
      $style_value = array_filter(explode(';', $style_attribute_string));
      $expanded = array();
      foreach ($style_value as $declaration) {
        list($name, $value) = explode(':', $declaration);
        $expanded[$name] = $value;
      }
    }

    return $expanded;
  }

}
