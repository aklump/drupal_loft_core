<?php
namespace Drupal\loft_core;

/**
 * Adds the format display extra options form.
 */
class views_handler_field_loft_core_users_status extends views_handler_field_loft_core_users {

  /**
   * {@inheritdoc}
   */
  function has_extra_options() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  function extra_options_form(&$form, &$form_state) {
    $form['loft_core_users_status'] = array(
      '#type' => 'select',
      '#title' => t('Format'),
      '#description' => t("Choose how you would like the status displayed."),
      '#default_value' => $this->options['loft_core_users_status'],
      '#options' => array(
        'code' => t('{CODE}'),
        'description' => t('{DESCRIPTION}'),
        'description_code' => t('{DESCRIPTION} ({CODE})'),
      ),
    );
  }

}
