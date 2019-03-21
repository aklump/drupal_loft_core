<?php

namespace Drupal\loft_core;

use AKlump\Data\DataInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\data_api\DataTrait;

/**
 * Abstract class to use for a custom module's "Core" class.
 */
abstract class CoreBase implements CoreInterface {

  use StringTranslationTrait;
  use DataTrait;

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
    $g = data_api();
    $info = &drupal_static(__CLASS__ . '::' . __FUNCTION__, []);
    if (empty($info)) {
      $info = module_invoke('block', 'block_info', $bid);
    }
    $block = [];
    if (!empty($info[$bid])) {
      $list = array(
        (object) (array(
            'title' => '',
            'region' => '',
            'module' => 'block',
            'delta' => $bid,
          ) + $info[$bid]),
      );
      $block = _block_get_renderable_array(_block_render_blocks($list));
      if (count(element_children($block)) > 0) {
        $block = reset($block);
      }
      // Keep the build array so we get the contextual links.
      $block['#markup'] = isset($block['#markup']) ? $this->t($block['#markup'], $tvars) : '';
      $block['#block'] = isset($block['#block']) ? $block['#block'] : new \stdClass();
      $g->fill($block, '#block.subject', $this->getBlockTitle($bid));

      // Turn this off because we probably don't want this.
      $block['#_theme_wrappers'] = isset($block['#theme_wrappers']) ? $block['#theme_wrappers'] : [];
      unset($block['#theme_wrappers']);
    }

    return $block;
  }

  /**
   * {@inheritdoc}
   */
  public function getBlockTranslation($bid, array $tvars = array(), $use_block_theme = TRUE) {
    $build = $this->getBlockRenderable($bid, $tvars);

    if ($use_block_theme) {
      $build['#theme_wrappers'] = $build['#_theme_wrappers'];
    }

    return drupal_render($build);
  }

  /**
   * {@inheritdoc}
   */
  public function getBlockTitle($bid) {
    $g = $this->g;
    $titles = &drupal_static(__CLASS__ . '::' . __FUNCTION__, []);
    if (empty($titles)) {
      $query = db_select('block', 'b');
      $result = $query
        ->fields('b', ['delta', 'title'])
        ->condition('module', 'block')
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

}
