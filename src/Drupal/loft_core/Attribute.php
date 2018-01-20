<?php

namespace Drupal\loft_core;

use Drupal\Core\Template\Attribute as CoreAttribute;

class Attribute extends CoreAttribute {

  /**
   * Test if a css style is present, e.g. background
   *
   * @param string $cssStyleName
   *
   * @return bool
   */
  public function hasStyle($cssStyleName) {
    return ($style = $this->getStyle()) && isset($style[$cssStyleName]);
  }

  /**
   * Remove a css style if present.
   *
   * @param $cssStyleName
   *
   * @return $this
   */
  public function removeStyle($cssStyleName) {
    if (($style = $this->getStyle())) {
      unset($style[$cssStyleName]);
      $this->setStyle($style);
    }

    return $this;
  }

  /**
   * Add (replace) a css style by name.
   *
   * @param $cssStyleName  e.g. color
   * @param $cssStyleValue e.g. #fff
   *
   * @return $this
   */
  public function addStyle($cssStyleName, $cssStyleValue) {
    $style = $this->getStyle();
    $style[$cssStyleName] = $cssStyleValue;
    $this->setStyle($style);

    return $this;
  }

  /**
   * Return the css styles as a key value associative array.
   *
   * @return array
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
   * Sets (replaces) the style tag using a key/value array
   *
   * @param array $style
   *
   * @see getStyle().
   */
  public function setStyle(array $style) {
    if (empty($style)) {
      $this->removeAttribute('style');
    }
    else if ($style = $this->contractStyle($style)) {
      $this->setAttribute('style', $style);
    }
  }

  protected function contractStyle(array $cssStyleValue) {
    $style = array();
    foreach ($cssStyleValue as $cssStyleName => $cssStyleValue) {
      $style[] = $cssStyleName . ':' . $cssStyleValue;
    }

    return implode(';', $style);
  }

  protected function expandStyle($cssStyleValue) {
    $expanded = array();
    if ($cssStyleValue) {
      $cssStyleValue = array_filter(explode(';', $cssStyleValue));
      $expanded = array();
      foreach ($cssStyleValue as $css) {
        $parts = explode(':', $css);
        $expanded[$parts[0]] = $parts[1];
      }
    }

    return $expanded;
  }
}
