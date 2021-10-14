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
 * @return array
 *   The form array.
 * @see loft_core_users_settings_form_submit()
 * @ingroup forms
 *
 * @see loft_core_users_settings_form_validate()
 */
function loft_core_users_settings_form(array $form, array &$form_state) {
  $whitelist = loft_core_users_get_domain_whitelist();
  $apache_ips = _loft_core_users_parse_apache();

  $form['#tree'] = FALSE;
  $form['help'] = [
    '#weight' => -10,
    '#markup' => t('<p>Get more help at <a href="!url">!url</a>.</p>', array('!url' => url('help/loft_core/loft_core_users'))),
  ];
  $form['augmented'] = array(
    '#type' => 'fieldset',
    '#title' => t('Augmented User Data'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['augmented']['loft_core_users_store_authenticated_ips'] = array(
    '#type' => 'checkbox',
    '#title' => t('Store the IP addresses of authenticated users?'),
    '#default_value' => variable_get('loft_core_users_store_authenticated_ips', LOFT_CORE_USERS_STORE_AUTHENTICATED_IPS),
  );

  $form['remember'] = array(
    '#type' => 'fieldset',
    '#title' => t('Remember Usernames'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
  );
  $form['remember']['loft_core_users_remember_usernames'] = array(
    '#type' => 'checkbox',
    '#title' => t('Should the website use cookies to remember usernames?'),
    '#description' => t('This will autofill the username in the login form for returning users.'),
    '#default_value' => variable_get('loft_core_users_remember_usernames', FALSE),
  );
  $form['remember']['loft_core_users_remember_user_for_days'] = array(
    '#type' => 'textfield',
    '#title' => t('Cookie Duration in Days'),
    '#size' => 10,
    '#description' => t('How long before the cookie should expire?'),
    '#default_value' => variable_get('loft_core_users_remember_user_for_days', LOFT_CORE_USERS_REMEMBER_USER_FOR_DAYS),
    '#required' => TRUE,
  );

  $form['spam'] = array(
    '#type' => 'fieldset',
    '#title' => t('Spam & User Blocking'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );

  //
  //
  // Blocked domains.
  //
  $query = db_select('loft_core_users', 'lu')
    ->fields('lu', ['domain', 'status', 'id'])
    ->distinct('domain')
    ->condition('status', loft_core_users_get_blocking_statuses(), 'IN')
    ->condition('domain', '', '<>')
    ->orderBy('domain', 'ASC');
  $result = $query->execute();
  $options = [];
  while ($record = $result->fetchObject()) {
    $options[$record->domain] = $record->domain;
    if (in_array($record->domain, $whitelist)) {
      $options[$record->domain] .= '<sup>WL</sup>';
    }
  }
  if (count($options)) {
    $checked = array_diff_key($options, array_flip($whitelist));
    $form['spam']['domains'] = [
      '#type' => 'fieldset',
      '#title' => t('Domain Blocking (@domains tracked, @checked blocked)', [
        '@checked' => count($checked),
        '@domains' => format_plural(count($options), '1 domain', '@count domains'),
      ]),
      '#description' => t('Any user whose email address domain appears checked in this list may not login, nor register for a new account.  They may browse your website anonymously, but cannot use an account.  You may uncheck a domain and save this form, to remove it from the blocked list of domains. Domains marked by <sup>WL</sup> are whitelisted and cannot be checked.'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];
    $form['spam']['domains']['loft_core_users_domain_whitelist'] = [
      '#type' => 'checkboxes',
      '#title' => t('Blacklist'),
      '#options' => $options,
      '#default_value' => $checked,
      '#attributes' => ['class' => ['loft-core-users-3col']],
    ];
  }
  $form['#attached']['css'][] = array(
    'type' => 'inline',
    'data' => '.loft-core-users-3col { column-count: 3 }',
  );

  $form_state['domains'] = $options;

  //
  //
  // Honeybears.
  //
  $query = db_select('loft_core_users', 'lu')
    ->fields('lu', ['domain', 'status', 'id'])
    ->distinct('domain')
    ->condition('status', LOFT_CORE_USERS_STATUS_HONEYBEAR)
    ->condition('domain', '', '<>');
  if (!empty($form_state['domains'])) {
    $query->condition('domain', $form_state['domains'], 'NOT IN');
  }
  if ($whitelist) {
    $query->condition('domain', $whitelist, 'NOT IN');
  }
  $result = $query->execute();
  $options = [];
  while ($record = $result->fetchObject()) {
    $options[$record->domain] = $record->domain;
  }
  $form_state['domains'] = $options;

  if (count($options)) {
    $form['spam']['honeybears'] = [
      '#weight' => -1,
      '#type' => 'fieldset',
      '#title' => t('Honeybears (@domains)', [
        '@domains' => format_plural(count($options), '1 domain', '@count domains'),
      ]),
      '#description' => t('The domains listed have been flagged as suspicious, but are not blocked.  To block any of these domains, check said domains and save the form.  This will convert them to spammy users.  Alternately use the MySQL shortcut above to batch convert these users.'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];
    $form['spam']['honeybears']['honeybears'] = [
      '#type' => 'checkboxes',
      '#title' => t('Spammy Candidate Domains'),
      '#options' => $options,
      '#default_value' => [],
      '#attributes' => ['class' => ['loft-core-users-3col']],
    ];
  }
  // MySql Shortcuts.
  $form['spam']['mysql'] = [
    '#weight' => 10,
    '#type' => 'fieldset',
    '#title' => t('MySQL shortcuts'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  ];

  $form['spam']['mysql'][] = array(
    '#theme' => 'table',
    '#rows' => [
      [
        "List all honeybears",
        "<pre>SELECT distinct(domain) FROM loft_core_users WHERE status = 3;</pre>",
      ],
      [
        "Convert Honeybears to Spammy",
        "<pre>UPDATE loft_core_users SET status = 34 where status = 3;</pre>",
      ],
      [
        "Bulk unblock IPs",
        "<pre>DELETE b FROM blocked_ips b JOIN loft_core_users u ON (u.ip = b.ip) WHERE u.status IN (" . implode(',', loft_core_users_get_blocking_statuses()) . ");</pre>",
      ],
    ],
  );

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

  $form['spam']['ips'] = [
    '#weight' => 5,
    '#type' => 'fieldset',
    '#title' => t('IP Blocking', [
      '@ips' => format_plural(count($deny_from), '1 ip', '@count ips'),
    ]),
    '#description' => t('IP blocking means to prevent users from accessing any part of your site.  It can performed at the Drupal level, which requires PHP and a Drupal bootstrap, or at the Apache level.  Drupal IP banning can be set to automatic, whereas Apache level requires manual modification of a configuration file.  The latter is less of a load on your server.'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  ];
  $min_title = t('Minimum Spammy Domains to Block IP');

  $form['spam']['ips']['loft_core_users_auto_block_by_ip'] = array(
    '#type' => 'checkbox',
    '#title' => t('Automatically Block by IP'),
    '#description' => t('If enabled, IPs that have been associated with at least the %name number of spammy domains will be automatically added to the <a href="!url">IP ban list</a> and blocked at the Drupal level.  See also the Apache .htaccess ban list.',
      [
        '!url' => url('admin/config/people/ip-blocking'),
        '%name' => $min_title,
      ]),

    '#default_value' => variable_get('loft_core_users_auto_block_by_ip', TRUE),
  );
  $form['spam']['ips']['loft_core_users_apache_min_domains_per_ip_to_list'] = [
    '#weight' => -1,
    '#type' => 'textfield',
    '#title' => $min_title,
    '#description' => t('How many domains must be associated with an IP before it will be automatically blocked and/or added to the Apache ban list.  If you set this number too low, you run this risk of banning legitimate users.'),
    '#default_value' => $min_count,
    '#required' => TRUE,
    '#size' => 8,
  ];
  $form['spam']['ips']['snippet'] = [
    '#access' => !empty($deny_from),
    '#weight' => 20,
    '#type' => 'fieldset',
    '#title' => t('Apache .htaccess Ban List (@ips)', [
      '@ips' => format_plural(count($deny_from), '1 ip', '@count ips'),
    ]),
    '#description' => t('This snippet is generated automatically based on spam levels and provided so that you can copy and paste it into your root-level .htaccess file for blocking by ips.  You may want to do this at regular intervals to decrease server load of spam.  When an IP is blocked at the apache level, the request will never get to Drupal, the database, PHP, etc.  This is a read-only field.  <strong>You should always replace the existing snippet in your .htaccess file with this new one during manual copy.</strong>'),
    '#collapsible' => FALSE,
  ];

  // Add a header: note, in reverse stacking order.
  if (count($deny_from)) {
    array_unshift($deny_from, NULL);
    array_unshift($deny_from, '#');
    array_unshift($deny_from, '# Exported from: ' . current_path());
    array_unshift($deny_from, '# user id, domain, status code');
    array_unshift($deny_from, '#');

    array_push($deny_from, '#');
    array_push($deny_from, '# End exported code');
    array_push($deny_from, '#');

    $form['spam']['ips']['snippet']['deny_from'] = [
      '#type' => 'textarea',
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

  //
  //
  // Currently being denied in .htaccess
  //
  $form['spam']['ips']['apache']['current'] = [
    '#weight' => 18,
    '#type' => 'fieldset',
    '#title' => t('Apache is Currently Blocking (@ips)', [
      '@ips' => format_plural(count($apache_ips), '1 ip', '@count ips'),
    ]),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  ];
  $form['spam']['ips']['apache']['current']['denying'] = [
    '#type' => 'textarea',
    '#description' => t('This is a list of IPs currently being blocked by Apache via the webroot .htaccess file.  The goal is to move candidates here by copying the candidate snippet into your .htaccess file.'),
    '#title' => t('Currently denying'),
    '#default_value' => implode(PHP_EOL, $apache_ips),
    '#rows' => 10,
    '#required' => FALSE,
    '#attributes' => [
      'readonly' => 'readonly',
    ],
    '#resizable' => TRUE,
  ];


  $form['#submit'][] = 'loft_core_users_settings_form_submit';

  return system_settings_form($form);
}

/**
 * Form validation handler.
 */
function loft_core_users_settings_form_validate($form, &$form_state) {
  if (isset($form_state['values']['loft_core_users_domain_whitelist'])) {
    $array = $form_state['values']['loft_core_users_domain_whitelist'];
    $array = array_filter($array, function (&$value) {
      return empty($value);
    });
    form_set_value($form['spam']['domains']['loft_core_users_domain_whitelist'], array_keys($array), $form_state);
  }
}

/**
 * Form submission handler.
 */
function loft_core_users_settings_form_submit($form, &$form_state) {
  if (isset($form_state['values']['honeybears'])
    && ($honeybears = array_values(array_filter($form_state['values']['honeybears'])))) {
    try {
      $num_updated = db_update('loft_core_users')
        ->fields(['status' => LOFT_CORE_USERS_STATUS_HONEYBEAR_MADE_SPAMMY])
        ->condition('domain', $honeybears, 'IN')
        ->condition('status', LOFT_CORE_USERS_STATUS_HONEYBEAR)
        ->execute();
      \Drupal::messenger()->addStatus(
        t("@subject have been blocked.", [
          '@subject' => format_plural($num_updated, '1 honeybear', '@count honeybears'),
        ]), FALSE);
    }
    catch (Exception $e) {
      watchdog_exception('loft_core_admin', $e);
    }
  }
}

/**
 * All IPs that have been denied via the .htaccess file at the root of Drupal.
 *
 * @return array
 *   All unique IPs in ascending order.
 */
function _loft_core_users_parse_apache() {
  static $drupal_static_fast;
  if (!isset($drupal_static_fast)) {
    $drupal_static_fast['ips'] = &drupal_static(__FUNCTION__, NULL);
  }
  $ips = &$drupal_static_fast['ips'];
  if (empty($ips)) {
    $htaccess = file_get_contents(DRUPAL_ROOT . '/.htaccess');
    preg_match_all('/deny from ([\d\.]+)/is', $htaccess, $matches);
    $ips = isset($matches[1]) ? array_unique($matches[1]) : [];
    sort($ips, SORT_NATURAL);
  }

  return $ips;
}
