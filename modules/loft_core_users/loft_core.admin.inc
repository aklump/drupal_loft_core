<?php
/**
 * Form builder for the loft_core_users_settings_form form.
 *
 * Admin settings for the module.
 *
 * @param array $form
 * @param array &$form_state
 *
 * @see     loft_core_users_settings_form_validate()
 * @see     loft_core_users_settings_form_submit()
 * @ingroup forms
 * @return array
 */
function loft_core_users_settings_form($form, &$form_state)
{
    
    //
    //
    // Blocked domains.
    //
    $query = db_select('loft_core_users', 'lu')
        ->fields('lu', array('domain'))
        ->distinct('domain')
        ->condition('status', 1, '>');
    $options = $query->execute()->fetchCol();
    $options = drupal_map_assoc($options);

    if (count($options)) {
        $form['domains'] = array(
            '#type' => 'fieldset',
            '#title' => t('Domain blocking'),
            '#description' => t('The domains listed have been identified as suspicious.  All domains checked here will be blocked.  To allow users from one or more of the listed domains, uncheck those domains.  This will allow new users with emails from said domains.'),
            '#collapsible' => true,
            '#collapsed' => true,
        );
        $form['domains']['loft_core_users_domain_whitelist'] = array(
            '#type' => 'checkboxes',
            '#title' => t('Blacklist'),
            '#options' => $options,
            '#default_value' => array_diff($options, loft_core_users_get_domain_whitelist()),
        );
    }

    $form_state['domains'] = $options;

    //
    //
    // MySql Shortcuts.
    //
    $form['mysql'] = array(
        '#type' => 'fieldset',
        '#title' => t('MySQL shortcuts'),
        '#collapsible' => true,
        '#collapsed' => true,
    );
    $form['mysql'][] = array('#markup' => '<h2>Get Honeybears:</h2><pre>SELECT distinct(domain) FROM loft_core_users WHERE status = 3;</pre><h2>Make Honeybears SPAMMY</h2><pre>UPDATE loft_core_users SET status = 34 where status = 3;</pre>');

    //
    //
    // Add apache .htaccess domain blocking.
    //
    $deny_from = array();
    $query = db_select('loft_core_users', 'lu')
        ->fields('lu', array('uid', 'domain', 'status', 'ip'))
        ->condition('ip', null, 'is not')
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
    array_walk($deny_from, function (&$value) {
        $value = implode(PHP_EOL, array_reverse($value)) . PHP_EOL;
    });

    // Add a header: note, in reverse stacking order
    if (count($deny_from)) {

        $form['apache'] = array(
            '#type' => 'fieldset',
            '#title' => t('.htaccess deny by IP'),
            '#description' => t('This snippet is generated automatically based on spam levels and provided so that you can copy and paste it into your root-level .htaccess file for blocking by ips.  This is a read-only field.'),
            '#collapsible' => true,
            '#collapsed' => true,
        );

        array_unshift($deny_from, null);
        array_unshift($deny_from, '#');
        array_unshift($deny_from, '# Exported from: ' . current_path());
        array_unshift($deny_from, '#');

        array_push($deny_from, '#');
        array_push($deny_from, '# End exported code');
        array_push($deny_from, '#');

        $form['apache']['deny_from'] = array(
            '#type' => 'textarea',
            '#title' => t('Deny from'),
            '#default_value' => implode(PHP_EOL, $deny_from),
            '#rows' => 10,
            '#required' => false,
            '#attributes' => array(
                'readonly' => 'readonly',
            ),
            '#resizable' => true,
        );
    }

    return system_settings_form($form);
}

/**
 * Form validation handler.
 */
function loft_core_users_settings_form_validate($form, &$form_state)
{
    $array = $form_state['values']['loft_core_users_domain_whitelist'];
    $array = array_filter($array, function (&$value) {
        return empty($value);
    });
    form_set_value($form['domains']['loft_core_users_domain_whitelist'], array_keys($array), $form_state);
}
