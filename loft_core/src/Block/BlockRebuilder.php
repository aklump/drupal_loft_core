<?php

namespace Drupal\loft_core\Block;

use Drupal\Core\Template\Attribute;

/**
 * Base class to facilitate block rendering as other themes.
 *
 * Quick Start:
 * 1. Extend this class as MY_THEME/src/Block/SomeExistingThemeBlock.php
 * 2. Implement ::rebuild in that class.
 * 3. Add that class to the #pre_render of the block to be rebuilt.  See
 * ::preRender for more info on this.
 */
abstract class BlockRebuilder {

  /**
   * BlockRebuilder constructor.
   *
   * @param array $element
   *   The original element.
   */
  public function __construct(array $element) {
    $this->element = $element + ['#rebuild' => []];
  }

  /**
   * Return the attributes as passed in #rebuild.#attributes as an object.
   *
   * @return \Drupal\Core\Template\Attribute
   *   An attribute instance with the original attributes.
   */
  public function getAttributes() {
    $this->mergeAttributes = FALSE;

    return new Attribute($this->element['#rebuild']['#attributes'] ?? []);
  }

  /**
   * Pre-render callback for blocks.
   *
   * Static callback, which is attached as a #pre_render to the block you want
   * to rebuild in an implementation of hook_block_view_alter or
   * hook_block_view_BASE_BLOCK_ID_alter in your custom module.  You may pass a
   * base render array to by including the #rebuild key.  These are passed as
   * $element to ::rebuild in your child class.
   *
   * @param array $element
   *   The render element of the block.
   *
   * @return array
   *   The rebuilt render array.
   *
   * @code
   * function MY_MODULE_block_view_block_content_alter(array &$build,
   *   BlockPluginInterface $block) {
   *   switch ($block->getPluginId()) {
   *     case 'block_content:47fd4e30-1454-4a66-b601-af62bac014de':
   *       $build['#pre_render'][] = [LearnMoreCardBlock::class, 'preRender'];
   *       $build['#rebuild'] = [
   *         '#attributes' => ['class' => ['theme--secondary']],
   *         '#target_href' => $product->toUrl()->toString(),
   *       ];
   *       break;
   *     }
   *   }
   * @endcode
   */
  public static function preRender(array $element) {
    $content = $element['content'];
    $configuration = $element['#configuration'];
    $label = !empty($configuration['label_display']) ? $configuration['label'] : '';
    $obj = new static($element);
    $rebuild = $element['#rebuild'] ?? [];
    $rebuild = $obj->rebuild($rebuild, $label, $content);
    $element['#configuration']['label_display'] = FALSE;
    $element['content'] = $rebuild;

    return $element;
  }

  /**
   * Generate a new render array for the block.
   *
   * This function must return the new, altered, or rebuilt render array for
   * this custom block. You may utilize the following, if needed:
   *   - $this->element
   *   - $this->getAttributes()
   *
   * @param array $element
   *   The value as passed in #rebuild or an empty array.
   * @param string $label
   *   The block label.
   * @param array $content
   *   The block content.
   *
   * @return array
   *   The rebuilt array.
   *
   * @code
   *   public function rebuild(array $element, $label, array $content) {
   *     return [
   *       '#theme' => 'learn_more_card',
   *       '#attributes' => $this->getAttributes()->addClass('is-wide'),
   *       '#title' => $label,
   *       '#body' => $content['body'],
   *     ] + $element;
   *   }
   * @endcode
   */
  abstract public function rebuild(array $element, $label, array $content);

}
