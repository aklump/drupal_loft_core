<?php

namespace Drupal\loft_core\Utility;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Basis for handling batches via a class (i.e., hook_update, hook_post_update).
 *
 * Here is how you woule invoke a child class from hook_post_update_NAME().  The
 * implementing child class is not shown and is assumed to be straight-forward.
 *
 * @code
 * function my_module_post_update_study_resource_to_paragraphs(&$sandbox) {
 *   StudyResourceToParagraphs::create(\Drupal::getContainer())
 *     ->doBatch($sandbox);
 * }
 *
 * class StudyResourceToParagraphs extends BatchProcessorBase { ...
 * @endcode
 */
abstract class BatchProcessorBase implements ContainerInjectionInterface {

  /**
   * @var array
   */
  protected $sandbox;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static();
  }

  /**
   * Do the batch.
   *
   * @param array $sandbox
   *   As received from the batch API.
   *
   * @return void
   */
  public function doBatch(array &$sandbox) {
    $this->sandbox = &$sandbox;
    if (!array_key_exists('stack', $sandbox)) {
      $sandbox['stack'] = $this->batchCreateStack();
      $sandbox['total'] = count($sandbox['stack']);
    }
    $i = 0;
    while ($i++ < $this->batchGetMaxItemsPerBatch()
      && ($item = array_shift($sandbox['stack']))) {
      $this->batchProcessStackItem($item);
    }
    if (empty($sandbox['stack'])) {
      $sandbox['finished'] = 1;
    }
    else {
      $sandbox['finished'] = $sandbox['total'] / count($sandbox['stack']);
    }
  }

  /**
   * Get max items per batch to process.
   *
   * @return int
   *   The maximum items to process each batch run.
   */
  abstract protected function batchGetMaxItemsPerBatch(): int;

  /**
   * Initialize all items to be batch processed.
   *
   * @return array
   *   One or more items to be sent to \Drupal\loft_core\Utility\BatchProcessor::batchProcessStackItem()
   */
  abstract protected function batchCreateStack(): array;

  /**
   * Process one batch item.
   *
   * @param $item
   *   A single item from the stack to process.
   */
  abstract protected function batchProcessStackItem($item);
}
