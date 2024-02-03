<?php

namespace Drupal\loft_core\Template;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
/**
 * Provides some Twig functions.
 */
class TwigExtension extends AbstractExtension {

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
      new TwigFunction('splitBy', [
        '\AKlump\LoftLib\Code\Strings',
        'splitBy',
      ]),
      new TwigFunction('is_live', 'is_live'),
      new TwigFunction('loft_core_test_class', 'loft_core_test_class'),
      new TwigFunction('testing_id', 'loft_core_test_class'),
      new TwigFunction('has_output', [
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
    return \Drupal::service('loft_core.rendering')
      ->hasOutput($render_array);
  }

}
