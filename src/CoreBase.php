<?php

namespace Drupal\loft_core;

use AKlump\Data\DataInterface;
use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Template\Attribute;

/**
 * Abstract class to use for a custom module's "Core" class.
 */
abstract class CoreBase implements CoreInterface {

  /**
   * CoreBase constructor.
   *
   * @param \AKlump\Data\DataInterface $dataApiData
   *   An instance of DataInterface.
   */
  public function __construct(DataInterface $dataApiData) {
    $this->setDataApiData($dataApiData);
  }

  /**
   * {@inheritdoc}
   */
  public function getBlockRenderable($bid, array $tvars = array()) {

    $block = BlockContent::load($bid);
    $viewer = \Drupal::service('entity_type.manager')
      ->getViewBuilder('block_content');
    $build = $viewer->view($block, 'default');

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getBlockTranslation($bid, array $tvars = array(), $use_block_theme = TRUE) {
    $build = $this->getBlockRenderable($bid, $tvars);

    if ($use_block_theme) {
      $build['#theme_wrappers'] = $build['#_theme_wrappers'];
    }

    return \Drupal::service("renderer")->render($build);
  }

  /**
   * {@inheritdoc}
   */
  public function getBlockTitle($bid) {
    $theme = \Drupal::theme()->getActiveTheme()->getName();
    $g = $this->g;
    $titles = &drupal_static(__CLASS__ . '::' . __FUNCTION__, []);
    if (empty($titles)) {
      $query = db_select('block', 'b');
      $result = $query
        ->fields('b', ['delta', 'title'])
        ->condition('module', 'block')
        ->condition('theme', $theme)
        ->addTag('block_load')
        ->addTag('translatable')
        ->execute();
      $titles = $result->fetchAllAssoc('delta');
    }

    return $g->get($titles, [
      $bid,
      'title',
    ]);
  }

  /**
   * Ensure $element[$key] is an instance of Attribute.
   *
   * @param array &$element
   *   An array that will receive $key as an attribute instance.
   * @param string $key
   *   Defaults to '#attributes'.
   */
  public static function ensureAttribute(array &$element, $key = '#attributes') {
    $element[$key] = $element[$key] ?? [];
    if (is_array($element[$key])) {
      $element[$key] = new Attribute($element[$key]);
    }
  }

}
