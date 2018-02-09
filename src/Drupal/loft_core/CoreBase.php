<?php

namespace Drupal\loft_core;

use AKlump\Data\DataInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\data_api\DataTrait;

abstract class CoreBase implements CoreInterface {

  use StringTranslationTrait;
  use DataTrait;

  public function __construct(DataInterface $dataApiData) {
    $this->setDataApiData($dataApiData);
  }

  /**
   * Return the render array for a block by id.
   *
   * This is used to insert block content into forms, or other renderables.
   *
   * @param int   $bid
   * @param array $tvars
   *
   * @return array
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
   * Return the content of a block run through $this->t() with tvars.
   *
   * This is used to insert block content into forms, or other renderables.
   *
   * @param int   $bid
   * @param array $tvars
   * @param bool  $use_block_theme    Defaults to true.  Set to false and
   *                                  only the content will be rendered, set
   *                                  to true and the entire block will be,
   *                                  with title and block markup.
   *
   * @return mixed|null|string
   */
  public function getBlockTranslation($bid, array $tvars = array(), $use_block_theme = TRUE) {
    $build = $this->getBlockRenderable($bid, $tvars);

    if ($use_block_theme) {
      $build['#theme_wrappers'] = $build['_#theme_wrappers'];
    }

    return drupal_render($build);
  }

  /**
   * Get a block's title as entered in the UI.
   *
   * @param mixed $bid
   *
   * @return mixed
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
