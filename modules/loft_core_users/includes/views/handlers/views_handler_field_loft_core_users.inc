<?php

/**
 * @file
 * Definition of views_handler_field_loft_core_users_status.
 */

/**
 * Custom rendering for loft_core_users values.
 *
 * @ingroup views_field_handlers
 */
class views_handler_field_loft_core_users extends views_handler_field {

  /**
   * {@inheritdoc}
   */
  function render($values) {
    $field = $this->field;

    switch ($field) {
      case 'status':
        $value = $this->get_value($values);
        $account = user_load($values->uid);

        // If the account is not blocked and we don't have status then we
        // return clear.
        if (is_null($value) && $account->status == LOFT_CORE_USERS_STATUS_CLEAR) {
          $value = LOFT_CORE_USERS_STATUS_CLEAR;
        }

        if (!$this->options['loft_core_users_status_as_code']) {
          $value = loft_core_get_status_as_description($value);
        }
        break;

      case 'id':
      case 'domain':
      case 'ip':
        $value = empty($value) ? '-' : '';
        break;
    }

    return $this->sanitize_value($value);
  }

  /**
   * {@inheritdoc}
   */
  function has_extra_options() {
    return TRUE;
  }

  /**
   * Provide defaults for the handler.
   */
  function extra_options(&$option) {
    if ($option === 'loft_core_users_status_as_code') {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  function extra_options_form(&$form, &$form_state) {
    $form['loft_core_users_status_as_code'] = array(
      '#type' => 'checkbox',
      '#title' => t('Display code, not description'),
      '#description' => t('Check this to display status as the raw code, instead of description text.'),
      '#default_value' => $this->options['loft_core_users_status_as_code'],
    );
  }

}
