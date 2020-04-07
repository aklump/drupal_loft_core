<?php

namespace Drupal\loft_core\Ajax;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\ReplaceCommand;

/**
 * Provides a better helper for submitting an AJAX form.
 */
trait AjaxFormHelperTrait {

  /**
   * Submit form dialog #ajax callback.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response that display validation error messages or represents a
   *   successful submission.
   */
  public function ajaxSubmit(array &$form, FormStateInterface $form_state): AjaxResponse {
    if ($form_state->hasAnyErrors()) {
      return $this->failedAjaxSubmit($form, $form_state);
    }

    return $this->successfulAjaxSubmit($form, $form_state);
  }

  /**
   * Calculate the response for a form that has failed validation.
   *
   * For more control on failures, you should extend this method, optionally
   * calling `parent::failedAjaxSubmit` as desired.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response to handle the failed form submission.
   */
  protected function failedAjaxSubmit(array $form, FormStateInterface $form_state): AjaxResponse {
    $form['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -1000,
    ];
    $form['#sorted'] = FALSE;
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('[data-drupal-selector="' . $form['#attributes']['data-drupal-selector'] . '"]', $form));

    return $response;
  }

  /**
   * Allows the form to respond to a successful AJAX submission.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response that represents a successful submission.
   */
  abstract protected function successfulAjaxSubmit(array $form, FormStateInterface $form_state): AjaxResponse;

}
