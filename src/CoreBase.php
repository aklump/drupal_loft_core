<?php

namespace Drupal\loft_core;

use AKlump\Data\DataInterface;
use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Template\Attribute;

/**
 * Abstract class to use for a custom module's "Core" class.
 */
abstract class CoreBase implements CoreInterface {

  use \Drupal\data_api\DataTrait;

  /**
   * CoreBase constructor.
   *
   * @param \AKlump\Data\DataInterface $dataApiData
   *   An instance of DataInterface.
   */
  public function __construct(DataInterface $dataApiData) {
    // TODO Get rid of this dependency.
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
   * @param null $attribute_classname
   *   You may pass an alternative classname to use, e.g.,
   *   \Drupal\loft_core\Attribute.
   *
   * @return \Drupal\Core\Template\Attribute
   *   The attribute instance at $key so you can call methods in one line.
   *
   * @code
   *   Core::ensureAttribute($build, '#face_attributes')
   *     ->addClass('js-autofocus');
   * @endcode
   */
  public static function ensureAttribute(array &$element, $key = '#attributes', $attribute_classname = NULL) {
    $element[$key] = $element[$key] ?? [];
    if (is_array($element[$key])) {
      $attribute_classname = $attribute_classname ?? Attribute::class;
      $element[$key] = new $attribute_classname($element[$key]);
    }

    return $element[$key];
  }

  /**
   * Get a datetime object formated by drupal date time format name.
   *
   * @param $date_time
   *   A \DateTime or \Drupal\Core\Datetime\DrupalDateTime instance.
   * @param string $format_name
   *   The date time Drupal format name.
   * @param string $fallback_pattern
   *   The fallback pattern to use to format if the $format_name is not found.
   *
   * @return string
   *   The formated string representing $date_time.
   */
  public function getFormattedByDateFormatName($date_time, string $format_name, string $fallback_pattern): string {
    if (!is_object($date_time) || !method_exists($date_time, 'format')) {
      throw new \InvalidArgumentException(sprintf('\$date_time must be a date object with a format method.'));
    }

    if ($format_name) {

      // TODO DI inject to contstructor.
      $loaded = \Drupal::service('entity_type.manager')
        ->getStorage('date_format')
        ->load($format_name);
      if ($loaded) {
        $pattern = $loaded->getPattern();
      }
    }
    $pattern = !empty($loaded) ? $loaded->getPattern() : $fallback_pattern;

    return strval($date_time->format($pattern));
  }

}
