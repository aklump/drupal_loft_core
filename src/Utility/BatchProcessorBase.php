<?php

namespace Drupal\loft_core\Utility;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Utility\UpdateException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Basis for handling batches via a class (i.e., hook_update, hook_post_update).
 *
 * Here is how you would invoke a child class from hook_post_update_NAME().  The
 * implementing child class is not shown and is assumed to be straight-forward.
 *
 * @code
 * function my_module_post_update_study_resource_to_paragraphs(&$sandbox) {
 *   return StudyResourceToParagraphs::create(\Drupal::getContainer())
 *     ->doBatch($sandbox);
 * }
 *
 * class StudyResourceToParagraphs extends BatchProcessorBase { ...
 * @endcode
 */
abstract class BatchProcessorBase implements ContainerInjectionInterface {

  use StringTranslationTrait;

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
   * @return string
   *   A message to be printed upon successful completion of the entire batch.
   */
  public function doBatch(array &$sandbox): string {
    $this->sandbox = &$sandbox;
    try {
      if (!array_key_exists('stack', $sandbox)) {
        $sandbox['stack'] = $this->batchCreateStack();
        $sandbox['total'] = count($sandbox['stack']);
        $sandbox['messages'] = [];
      }
      $i = 0;
      while ($i++ < $this->batchGetMaxItemsPerBatch()
        && ($item = array_shift($sandbox['stack']))) {
        $this->batchProcessStackItem($item);
      }
    }
    catch (\Exception $exception) {
      // @see \hook_update_N()
      throw new UpdateException($exception->getMessage(), NULL, $exception);
    }

    // Check if we're finished with the entire batch.
    if (empty($sandbox['stack'])) {
      $sandbox['#finished'] = 1;
      $messages = $this->sandbox['messages'];
      $messages = array_map('strval', $messages);
      if (count($messages) <= 1) {
        return $messages[0] ?? '';
      }

      // The PHP_EOL are important for CLI usage with drush updb.
      // Don't try to use an <ol> here, it doesn't seem to work. Feb 10, 2022, aklump.
      return sprintf('<ul><li>%s</li></ul>', implode('</li>' . PHP_EOL . '<li>', $messages)) . PHP_EOL;
    }

    $sandbox['#finished'] = 1 - count($sandbox['stack']) / $sandbox['total'];

    return '';
  }

  /**
   * Get max items per batch to process.
   *
   * @return int
   *   The maximum items to process each batch run.
   */
  protected function batchGetMaxItemsPerBatch(): int {
    return 1;
  }

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

  /**
   * Add a status message to be output at the end of a the update.
   *
   * Note: there is no way to write output during the batch, that is controlled
   * entirely by update_invoke_post_update().
   *
   * @param $message
   * @param $repeat
   *
   * @return void
   */
  protected function addStatus($message, $repeat = FALSE) {
    $this->sandbox['messages'] = $this->sandbox['messages'] ?? [];
    if ($repeat || array_search($message, $this->sandbox['messages']) === FALSE) {
      $this->sandbox['messages'][] = $message;
    }
  }
}
