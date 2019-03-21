<?php
namespace Drupal\loft_core;

/**
 * Custom rendering for loft_core_users values.
 *
 * @ingroup views_field_handlers
 */
class views_handler_field_loft_core_users extends views_handler_field {

  /**
   * The default display format for the status.
   *
   * @var string
   *
   * @see ::extra_options_form().
   */
  const FORMAT_DEFAULT = 'description';

  /**
   * {@inheritdoc}
   */
  function render($values) {
    $field = $this->field;

    switch ($field) {
      case 'status':
        $value = $this->get_value($values);
        $account = \Drupal::entityManager()->getStorage('user')->load($values->uid);

        // If the account is not blocked and we don't have status then we
        // return clear.
        if (is_null($value) && $account->status == LOFT_CORE_USERS_STATUS_CLEAR) {
          $value = LOFT_CORE_USERS_STATUS_CLEAR;
        }

        $format = isset($this->options['loft_core_users_status']) ? $this->options['loft_core_users_status'] : self::FORMAT_DEFAULT;
        if ($format !== 'code') {
          $description = loft_core_get_status_as_description($value);
          if ($format === 'description') {
            $value = $description;
          }
          else {
            $value = "$description ($value)";
          }
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

}
