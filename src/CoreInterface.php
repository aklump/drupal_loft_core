<?php

namespace Drupal\loft_core;

/**
 * Defines an interface for a site's custom "Core" module.
 *
 * @package Drupal\loft_core
 */
interface CoreInterface {

  /**
   * Return the safe markup handler.
   *
   * Return a fallback "safe" markup handler, callable or a text format.
   * Callables and functions will be given the $value as the argument.  A good
   * default value is 'filter_xss'.
   *
   * @return string|callable
   *   The fallback safe markup handler.
   *
   * @see filter_formats()
   */
  public function getSafeMarkupHandler();

  /**
   * Return the render array for a custom block by id.
   *
   * This is used to insert block content into forms, or other renderables.
   *
   * @param int $bid
   *   The custom block id.
   * @param array $tvars
   *   The markup will be run through t(); and these are it's vars.
   *
   * @return array
   *   The render array for the block.
   */
  public function getBlockRenderable($bid, array $tvars = array());

  /**
   * Return the content of a block run through $this->t() with tvars.
   *
   * This is used to insert block content into forms, or other renderables.
   *
   * @param int $bid
   *   The custom block id.
   * @param array $tvars
   *   The markup will be run through t(); and these are it's vars.
   * @param bool $use_block_theme
   *   Defaults to TRUE. Set to false and only the content will be rendered,
   *   set to true and the entire block will be, with title and block markup.
   *
   * @return string
   *   The translated block markup.
   */
  public function getBlockTranslation($bid, array $tvars = array(), $use_block_theme = TRUE);

  /**
   * Return a block's title as string.
   *
   * @param int $bid
   *   The custom block id.
   *
   * @return string
   *   The title of the block.
   */
  public function getBlockTitle($bid);

}
