<?php

namespace Drupal\loft_core\Ajax;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a better helper for submitting an AJAX form.
 */
trait AjaxFormHelperTrait {

  /**
   * Flags an element as having an error sometime after validation.
   *
   * Use this to trigger ::failedAjaxSubmit when something bad happens during
   * the submission, e.g. failure to save an object, etc.  The error message
   * will get converted to a user message.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state instance.
   * @param string $message
   *   (optional) The error message to present to the user.
   *
   * @return $this
   */
  public function setPostValidationError(FormStateInterface $form_state, $message = '') {
    $errors = $form_state->get('ajaxFormHelperTraitPostValidationErrors');
    $errors[] = $message;
    $form_state->set('ajaxFormHelperTraitPostValidationErrors', $errors);

    return $this;
  }

  /**
   * Get any post-validation errors.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The same form state instance used for ::setPostValidationError.
   *
   * @return array
   *   Any errors occurring during submission.
   */
  public function getPostValidationErrors(FormStateInterface $form_state): array {
    return $form_state->get('ajaxFormHelperTraitPostValidationErrors') ?? [];
  }

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
    if ($form_state->hasAnyErrors() || ($messages = $this->getPostValidationErrors($form_state))) {
      foreach ($messages as $message) {
        \Drupal::messenger()->addError($message);
      }

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

    // If you want to suppress the display of status_messages then you need to
    // set #access = false before you get to this point.  You probably only need
    // to worry about this element if you want to change the weight from being
    // at the top of the form.
    if (empty($form['status_messages'])) {
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -1000,
      ];
    }
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
