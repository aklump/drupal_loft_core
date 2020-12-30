<?php

namespace Drupal\loft_core\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * @Filter(
 *   id = "filter_no_orphans",
 *   title = @Translation("No Orphans Filter"),
 *   description = @Translation("Replaces the final space in a string with &nbps; to prevent single-word orphans."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 * )
 */
class NoOrphansFilter extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {

    // Find the last space, which is followed by a non-space, this we will
    // replace with a nbsp.
    $space = strrpos(trim($text), ' ');
    if ($space !== FALSE) {
      $text = substr_replace($text, '&nbsp;', $space, 1);
    }

    return new FilterProcessResult($text);
  }

}
