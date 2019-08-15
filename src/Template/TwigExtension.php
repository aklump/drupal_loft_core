<?php

namespace Drupal\loft_core\Template;

/**
 * Provides some Twig functions.
 */
class TwigExtension extends \Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'loft_core';
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('splitBy', [
        '\AKlump\LoftLib\Code\Strings',
        'splitBy',
      ]),
      new \Twig_SimpleFunction('loft_core_test_class', 'loft_core_test_class'),
      new \Twig_SimpleFunction('testing_id', 'loft_core_test_class'),
    ];
  }

}
