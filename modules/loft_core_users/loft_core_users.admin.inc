<?php
/**
 * @file
 * Provides administrative functions for loft_core_users.
 */

/**
 * Form builder for the loft_core_users_settings_form form.
 *
 * Admin settings for the module.
 *
 * @param array $form
 *   The form structure array.
 * @param array &$form_state
 *   Array representing the state of the form.
 *
 * @see loft_core_users_settings_form_validate()
 * @see loft_core_users_settings_form_submit()
 * @ingroup forms
 *
 * @return array
 *   The form array.
 */
function loft_core_users_settings_form(array $form, array &$form_state) {

  //
  //
  // Blocked domains.
  //
  $query = db_select('loft_core_users', 'lu')
    ->fields('lu', ['domain', 'status', 'id'])
    ->distinct('domain')
    ->condition('status', loft_core_users_get_blocking_statuses(), 'IN')
    ->condition('domain', '', '<>');
  $result = $query->execute();
  $options = [];
  while ($record = $result->fetchObject()) {
    $options[$record->domain] = $record->domain;
  }

  if (count($options)) {
    $checked = array_diff($options, loft_core_users_get_domain_whitelist());
    $form['domains'] = [
      '#type' => 'fieldset',
      '#title' => t('Blacklist (@checked of @domains)', [
        '@checked' => count($checked),
        '@domains' => format_plural(count($options), '1 domain', '@count domains'),
      ]),
      '#description' => t('The domains listed have been identified as suspicious.  All domains checked here will be blocked.  To allow users from one or more of the listed domains, uncheck those domains.  This will allow new users with emails from said domains to sign up for new accounts.'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];
    $form['domains']['loft_core_users_domain_whitelist'] = [
      '#type' => 'checkboxes',
      '#title' => t('Blacklist'),
      '#options' => $options,
      '#default_value' => $checked,
    ];
  }

  $form_state['domains'] = $options;

  //
  //
  // Honeybears.
  //
  $query = db_select('loft_core_users', 'lu')
    ->fields('lu', ['domain', 'status', 'id'])
    ->distinct('domain')
    ->condition('status', LOFT_CORE_USERS_STATUS_HONEYBEAR)
    ->condition('domain', '', '<>')
    ->condition('domain', $form_state['domains'], 'NOT IN')
    ->condition('domain', loft_core_users_get_domain_whitelist(), 'NOT IN');
  $result = $query->execute();
  $options = [];
  while ($record = $result->fetchObject()) {
    $options[$record->domain] = $record->domain;
  }

  if (count($options)) {
    $form['honeybears'] = [
      '#type' => 'fieldset',
      '#title' => t('Honeybears (@domains)', [
        '@domains' => format_plural(count($options), '1 domain', '@count domains'),
      ]),
      '#description' => t('The domains listed have been flagged as suspicious.  These domains are not blocked.  To block any of these domains, check said domains and save the form.'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];
    $form['honeybears']['honeybears'] = [
      '#type' => 'checkboxes',
      '#title' => t('Honeybears'),
      '#options' => $options,
      '#default_value' => [],
    ];
  }

  $form_state['domains'] = $options;

  //
  //
  // MySql Shortcuts.
  //
  $form['mysql'] = [
    '#type' => 'fieldset',
    '#title' => t('MySQL shortcuts'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  ];
  $form['mysql'][] = ['#markup' => '<h2>Get Honeybears:</h2><pre>SELECT distinct(domain) FROM loft_core_users WHERE status = 3;</pre><h2>Make Honeybears SPAMMY</h2><pre>UPDATE loft_core_users SET status = 34 where status = 3;</pre>'];

  //
  //
  // Add apache .htaccess domain blocking.
  //
  $deny_from = [];
  $query = db_select('loft_core_users', 'lu')
    ->fields('lu', ['uid', 'domain', 'status', 'ip'])
    ->condition('ip', NULL, 'is not')
    ->condition('domain', '', '<>')
    ->condition('status', loft_core_users_get_blocking_statuses(), 'IN');
  $result = $query->execute();
  $whitelist = loft_core_users_get_domain_whitelist();
  while ($record = $result->fetchObject()) {
    $comment = (array) $record;
    if (!in_array($comment['domain'], $whitelist)) {
      unset($comment['ip']);
      $deny_from[$record->ip][0] = 'deny from ' . $record->ip;
      $deny_from[$record->ip][] = '# ' . implode(', ', $comment);
    }
  }

  $min_count = variable_get('loft_core_users_apache_min_domains_per_ip_to_list', LOFT_CORE_USERS_APACHE_MIN_DOMAINS_PER_IP_TO_LIST);
  $deny_from = array_filter($deny_from, function ($item) use ($min_count) {
    return count($item) - 1 >= $min_count;
  });

  array_walk($deny_from, function (&$value) {
    $value = implode(PHP_EOL, array_reverse($value)) . PHP_EOL;
  });

  $form['apache'] = [
    '#type' => 'fieldset',
    '#title' => t('.htaccess deny by IP (@ips)', [
      '@ips' => format_plural(count($deny_from), '1 ip', '@count ips'),
    ]),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  ];
  $form['apache']['loft_core_users_apache_min_domains_per_ip_to_list'] = [
    '#type' => 'textfield',
    '#title' => t('Domain Minimum to List'),
    '#description' => t('An IP will only appear in this list if it has at least this many domains that have been blocked.'),
    '#default_value' => $min_count,
    '#required' => TRUE,
    '#size' => 8,
  ];

  // Add a header: note, in reverse stacking order.
  if (count($deny_from)) {
    array_unshift($deny_from, NULL);
    array_unshift($deny_from, '#');
    array_unshift($deny_from, '# Exported from: ' . current_path());
    array_unshift($deny_from, '#');

    array_push($deny_from, '#');
    array_push($deny_from, '# End exported code');
    array_push($deny_from, '#');

    $form['apache']['deny_from'] = [
      '#type' => 'textarea',
      '#description' => t('This snippet is generated automatically based on spam levels and provided so that you can copy and paste it into your root-level .htaccess file for blocking by ips.  You may want to do this at regular intervals say monthly to decrease server load of spam.  When an ip is blocked at the apache level, it will never get to Drupal, the database, PHP, etc.  This is a read-only field.'),
      '#title' => t('Deny from'),
      '#default_value' => implode(PHP_EOL, $deny_from),
      '#rows' => 10,
      '#required' => FALSE,
      '#attributes' => [
        'readonly' => 'readonly',
      ],
      '#resizable' => TRUE,
    ];
  }
  $form['#submit'][] = 'loft_core_users_settings_form_submit';

  return system_settings_form($form);
}

/**
 * Form validation handler.
 */
function loft_core_users_settings_form_validate($form, &$form_state) {
  $array = $form_state['values']['loft_core_users_domain_whitelist'];
  $array = array_filter($array, function (&$value) {
    return empty($value);
  });
  form_set_value($form['domains']['loft_core_users_domain_whitelist'], array_keys($array), $form_state);
}

/**
 * Form submission handler.
 */
function loft_core_users_settings_form_submit($form, &$form_state) {
  if (($honeybears = array_values(array_filter($form_state['values']['honeybears'])))) {
    try {
      $num_updated = db_update('loft_core_users')
        ->fields(['status' => LOFT_CORE_USERS_STATUS_HONEYBEAR_MADE_SPAMMY])
        ->condition('domain', $honeybears, 'IN')
        ->condition('status', LOFT_CORE_USERS_STATUS_HONEYBEAR)
        ->execute();
      drupal_set_message(t("@subject have been blocked.", [
        '@subject' => format_plural($num_updated, '1 honeybear', '@count honeybears'),
      ]), 'status', FALSE);
    }
    catch (Exception $e) {
      watchdog_exception('loft_core_admin', $e);
    }
  }
}
