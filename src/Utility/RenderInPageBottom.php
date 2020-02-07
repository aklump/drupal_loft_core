<?php

namespace Drupal\loft_core\Utility;

use Drupal\Component\Render\MarkupInterface;

/**
 * A post_render utility to move markup to the bottom of the page.
 *
 * To cause a renderable array to be rendered in the $page_bottom section add
 * this to it's render array:
 *
 * @code
 *   '#post_render' => [[RenderInPageBottom::class, 'add']],
 * @endcode
 *
 * To prevent duplicates you may use the key `#page_bottom_unique` and only the
 *   last element having that key will be rendered in the final output.
 *
 * @code
 *   '#page_bottom_unique' => 'login_modal',
 *   '#post_render' => [[RenderInPageBottom::class, 'add']],
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
   * This should be registered as the last post_render hook on a renderable
   * element.  See class code docblock for usage.
   *
   * @param \Drupal\Component\Render\MarkupInterface $output
   *   The output of rendering $element.
   * @param array $element
   *   The render array.
   */
  public static function add(MarkupInterface $output, array $element) {
    if (static::$disabled) {
      return $output;
    }
    $output = is_array($output) ?: ['#markup' => $output];

    // If we have a uuid then only the last element to register  will be used.
    if (($uuid = $element['#page_bottom_unique'] ?? NULL)) {
      static::$build[$uuid] = $output;
    }
    else {
      static::$build[] = $output;
    }
  }

  /**
   * Get the final rendering.
   *
   * @return \Drupal\Component\Render\MarkupInterface|string
   *   The final rendered markup of all renderables that were added in ::add;
   *   to be appended to the $page_bottom variable.
   *
   * @see loft_core_page_bottom().
   */
  public static function render() {
    return \Drupal::service('renderer')->render(static::$build);
  }

}
