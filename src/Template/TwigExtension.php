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
      new \Twig_SimpleFunction('has_output', [
        $this,
        'hasOutput',
      ]),
    ];
  }

  /**
   * Provide custom Twig function has_output().
   *
   * This function determine's if a render array produces any visible output,
   * e.g. text or images.  In otherwords, it makes sure that when it renders, it
   * is not merely comments or markup.  This can be important to know when
   * you're trying to determine if a sidebar wrapper should render based on it's
   * content render array, for example.
   *
   * @param array $render_array
   *   The array to test.
   */
  public function hasOutput(array $render_array) {
    $output = \Drupal::service('renderer')->renderPlain($render_array);

    return boolval(trim(strip_tags($output)));
  }

}
