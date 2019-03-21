<?php /**
 * @file
 * Contains \Drupal\loft_core\EventSubscriber\ExitSubscriber.
 */

namespace Drupal\loft_core\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ExitSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::TERMINATE => ['onEvent', 0]];
  }

  public function onEvent() {

    // If the user has not been unstashed by now, we need to force it before drupal_session_commit() happens.
    loft_core_user_stash();

    // Remove some messages by hook.
    if (!empty($_SESSION['messages']) && ($masks = \Drupal::moduleHandler()
        ->invokeAll('loft_core_suppress_messages'))) {
      foreach (array_keys($masks) as $level) {
        if (isset($_SESSION['messages'][$level])) {
          foreach ($_SESSION['messages'][$level] as $key => $message) {
            foreach ($masks[$level] as $mask) {
              if (preg_match($mask, $message)) {
                unset($_SESSION['messages'][$level][$key]);
              }
            }
          }
        }
      };
    }
  }

}
