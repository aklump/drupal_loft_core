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
    // TODO REmove this?
    // If the user has not been unstashed by now, we need to force it before drupal_session_commit() happens.
    loft_core_user_stash();
  }

}
