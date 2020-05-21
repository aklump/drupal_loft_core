<?php

namespace Drupal\loft_core\Utility;

/**
 * A utility to move markup to the bottom of the page and reduce duplication.
 *
 * To cause a renderable array to be rendered in the $page_bottom section add
 * this to it's render array:
 *
 * @code
 *   '#pre_render' => [[RenderInPageBottom::class, 'add']],
 * @endcode
 *
 * To prevent duplicates you may use the key `#page_bottom_unique` and only the
 *   last element having that key will be rendered in the final output.  This
 *   is helpful if you only want to render a single block of markup once, but
 *   may build it multiple times, calling this class, e.g., a modal or tooltip
 *   help window.
 *
 * @code
 *   '#page_bottom_unique' => 'login_modal',
 *   '#pre_render' => [[RenderInPageBottom::class, 'add']],
 * @endcode
 */
class RenderInPageBottom {

  /**
   * Holds the delayed render arrays.
   *
   * @var array
   */
  protected static $build = [];

  /**
   * Flag to know if delayed rendered is disabled.
   *
   * @var bool
   */
  protected static $disabled = FALSE;

  /**
   * Disable the rendering of content in the page bottom.
   *
   * In the case of AJAX callbacks you may need to render your data
   * immediately, and not push it to the page bottom.  Call this method to
   * disable the migration of content rendering to the page bottom.  This has
   * the affect of not using this class at all.
   */
  public static function disable() {
    static::$disabled = TRUE;
  }

  /**
   * Add to the page_bottom renderable.
   *
   * This should be registered as the last pre_render hook on a renderable
   * element.  See class code docblock for usage.
   *
   * @param \Drupal\Component\Render\MarkupInterface $output
   *   The output of rendering $element.
   * @param array $element
   *   The render array.
   */
  public static function add(array $element) {

    if (static::$disabled) {
      return $element;
    }

    // Remove the pre_render callback to this method so we don't have recursion.
    if (isset($element['#pre_render'])) {
      $element['#pre_render'] = array_filter($element['#pre_render'], function ($item) {
        return $item[0] !== static::class && $item[1] !== __METHOD__;
      });
      if (empty($element['#pre_render'])) {
        unset($element['#pre_render']);
      }
    }

    // If we have a uuid then only the last element to register  will be used.
    if (($uuid = $element['#page_bottom_unique'] ?? NULL)) {
      static::$build[$uuid] = $element;
      unset($element['#page_bottom_unique']);
    }
    else {
      static::$build[] = $element;
    }

    // We return an empty array because we've effectively "moved" the render
    // array to be later retrieved by ::get() via loft_core_page_bottom().  We
    // HAVE TO SET THE CACHE CONTEXTS to `url` since this is a page bottom event
    // and it has to bubble up later.  This is a very, very important step or we
    // will loose data in production mode.
    return [
      '#cache' => ['contexts' => ['url']],
    ];
  }

  /**
   * Get the amalgam of render arrays to be rendered in the bottom.
   *
   * All elements that have been sent to
   * \Drupal\loft_core\Utility\RenderInPageBottom::add will be returned by this
   * method.
   *
   * @return array
   *   The final render array.
   *
   * @see loft_core_page_bottom()
   */
  public static function get() {
    return static::$build;
  }

}
