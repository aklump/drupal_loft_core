<?php

namespace Drupal\loft_core\EventSubscriber;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\loft_core\Redirect;
use Drupal\rabbit_hole\BehaviorInvoker;
use Drupal\rabbit_hole\Plugin\RabbitHoleBehaviorPluginInterface;
use Drupal\rabbit_hole\Plugin\RabbitHoleBehaviorPluginManager;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EventSubscriber.
 *
 * @package Drupal\rabbit_hole
 */
class RabbitHoleSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\rabbit_hole\BehaviorInvoker definition.
   *
   * @var Drupal\rabbit_hole\BehaviorInvoker
   */
  protected $rabbitHoleBehaviorInvoker;

  /**
   * Constructor.
   */
  public function __construct(
    BehaviorInvoker $rabbit_hole_behavior_invoker,
    RabbitHoleBehaviorPluginManager $plugin_manager_rabbit_hole_behavior_plugin
  ) {
    $this->rhBehaviorPluginManager = $plugin_manager_rabbit_hole_behavior_plugin;
    $this->rabbitHoleBehaviorInvoker = $rabbit_hole_behavior_invoker;
  }

  /**
   * {@inheritdoc}
   */
  static public function getSubscribedEvents() {
    $events['kernel.request'] = ['onRequest', 29];

    return $events;
  }

  /**
   * A method to be called whenever a kernel.request event is dispatched.
   *
   * It invokes a rabbit hole behavior on an entity in the request if
   * applicable.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The event triggered by the request.
   */
  public function onRequest(Event $event) {
    if (\Drupal::config('loft_core.settings')
      ->get('use_redirect_api')) {
      return $this->processEvent($event);
    }
  }

  /**
   * Process events generically invoking rabbit hole behaviors if necessary.
   *
   * @param \Symfony\Component\EventDispatcher\Event $event
   *   The event to process.
   */
  private function processEvent(Event $event) {
    // Don't process events with HTTP exceptions - those have either been thrown
    // by us or have nothing to do with rabbit hole.
    if ($event->getRequest()->get('exception') != NULL) {
      return;
    }

    // Get the route from the request.
    if ($route = $event->getRequest()->get('_route')) {
      // Only continue if the request route is the an entity canonical (node view)
      if (preg_match('/^entity\.(.+)\.canonical$/', $route)) {
        // We check for all of our known entity keys that work with rabbit hole
        // and invoke rabbit hole behavior on the first one we find (which
        // should also be the only one).
        $entity_keys = $this->rabbitHoleBehaviorInvoker->getPossibleEntityTypeKeys();
        foreach ($entity_keys as $ekey) {
          $entity = $event->getRequest()->get($ekey);
          if (isset($entity) && $entity instanceof ContentEntityInterface && $ekey === 'node') {

            //
            //
            // First check for a BUNDLE_TYPE_page...
            //
            $hook = 'loft_core_node_' . $entity->getType() . '_page';

            //@fixme not yet ported.
            if (($modules = \Drupal::moduleHandler()
              ->getImplementations($hook))) {
              // We only take the last one.
              $callback = end($modules) . '_' . $hook;

              return $callback($entity);
            }

            //
            //
            // ... then look for a redirect.
            //
            else {
              if (!($values = Redirect::getNodeRedirect($entity, 'view'))) {
                return;
              }

              $entity->set('rh_redirect', $values['rh_redirect']);
              $entity->set('rh_redirect_response', $values['rh_redirect_response']);

              $plugin = $this->rhBehaviorPluginManager
                ->createInstance($values['rh_action'], $values);

              $current_response = $event->getResponse();
              $resp_use = $plugin->usesResponse();
              $response_required = $resp_use == RabbitHoleBehaviorPluginInterface::USES_RESPONSE_ALWAYS;
              $response_allowed = $resp_use == $response_required
                || $resp_use == RabbitHoleBehaviorPluginInterface::USES_RESPONSE_SOMETIMES;

              // Most plugins never make use of the response and only run when it's not
              // provided (i.e. on a request event).
              if ((!$response_allowed && $current_response == NULL)
                // Some plugins may or may not make use of the response so they'll run in
                // both cases and work out the logic of when to return NULL internally.
                || $response_allowed
                // Though none exist at the time of this writing, some plugins could
                // require a response so that case is handled.
                || $response_required && $current_response != NULL) {

                $new_response = $plugin->performAction($entity, $current_response);
                if (isset($new_response)) {
                  $event->setResponse($new_response);
                }
              }
              // All other cases return NULL, meaning the response is unchanged.
              else {
                return NULL;
              }

            }
          }
        }
      }
    }
  }

}
