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
 */
function loft_core_users_settings_form($form, &$form_state)
{
    $form['loft_core_users_honeypot_reject_domains_whitelist'] = array(
        '#type' => 'textarea',
        '#title' => t('Honeypot Reject Whitelist'),
        '#description' => t('CSV list of domains that will not be blocked.'),
        '#default_value' => variable_get('loft_core_users_honeypot_reject_domains_whitelist', LOFT_CORE_USERS_HONEYPOT_REJECT_DOMAINS_WHITELIST),
        '#rows' => 6,
        '#required' => false,
        '#resizable' => true,
        '#suffix' => '<pre>select distinct(domain) from loft_core_users where status = 3;</pre><pre>UPDATE loft_core_users SET status = 34 where status = 3 and domain not in ("yahoo.com");</pre>',
    );

    return system_settings_form($form);
}
