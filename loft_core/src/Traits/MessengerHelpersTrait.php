<?php


namespace Drupal\loft_core\Traits;


/**
 * Trait MessengerHelpersTrait.
 *
 * @package Drupal\loft_core\Traits
 */
trait MessengerHelpersTrait {

  /**
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Delete a single message by it's text, if it exists.
   *
   * @param string|\Drupal\Component\Render\MarkupInterface $message_text
   *   The message text to search for and remove.
   * @param string $message_type
   *   The type of messages to search through.
   */
  public function deleteMessageByMessage($message_text, string $message_type): void {
    $messages = $this->messenger->messagesByType($message_type);
    $this->messenger->deleteByType($message_type);
    foreach ($messages as $message) {
      if (strval($message) !== strval($message_text)) {
        $this->messenger->addMessage($message, $message_type);
      }
    }
  }
}
