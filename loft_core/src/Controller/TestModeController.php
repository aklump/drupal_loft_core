<?php

namespace Drupal\loft_core\Controller;

use Drupal\Core\Access\AccessResult;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Handles enabling test mode for a time.
 */
class TestModeController {

  const DURATION_MINS = 15;

  /**
   * Page callback to enable the test mode.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   With the key 'expires' and 'status'.
   */
  public function enable() {
    $duration_in_minutes = self::DURATION_MINS;
    $this->setTestMode(TRUE, $duration_in_minutes);

    return new JsonResponse([
      'status' => TRUE,
      'expires' => time() + $duration_in_minutes * 60,
    ]);
  }

  /**
   * Set loft test mode to on or off.
   *
   * @param bool $state
   *   True to enable.
   * @param int $duration_minutes
   *   The duration in minutes.
   */
  public function setTestMode(bool $state, int $duration_minutes): void {
    if ($state) {
      \Drupal::state()->set('loft_core_test_mode_expiry', \Drupal::time()
          ->getRequestTime() + $duration_minutes * 60);
    }
    else {
      \Drupal::state()->delete('loft_core_test_mode_expiry');
    }
  }

  /**
   * Determine if the user has access to test mode.
   *
   * @param string $token
   *   The public access token for test access.
   *
   * @return bool
   *   True if access should be granted.
   */
  public function access($token) {
    $control = \Drupal::config('loft_core.settings')
      ->get('test_mode_url_token');

    return AccessResult::allowedIf($control && $token === $control);
  }

}
