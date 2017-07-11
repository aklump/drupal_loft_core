<?php
/**
 * @file Functions concerned with controlling users and SPAM.
 *
 */
/**
 * This account was trapped as a robot.
 */
define('LOFT_CORE_USER_STATUS_ROBOT', 2);

/**
 * This account was trapped by the honepot module.
 */
define('LOFT_CORE_USER_STATUS_HONEYBEAR', 3);

/**
 * For one reason or another this account appears spammy.
 */
define('LOFT_CORE_USER_STATUS_SPAMMY', 4);

/**
 * @var LOFT_CORE_HONEYPOT_REJECT_DOMAINS_WHITELIST
 *
 * CSV list of domains to whitelist that cannot be considered SPAM when
 * honeypot rejects.
 */
define('LOFT_CORE_HONEYPOT_REJECT_DOMAINS_WHITELIST', 'gmail.com');

/**
 * Block a user with optional augmented status.
 *
 * @param \stdClass $account
 * @param int       $status
 *
 * @see LOFT_CORE_USER_STATUS_*
 */
function loft_core_block_user($account, $status = 0)
{
    if ($status > 1) {
        loft_core_set_user_status($account, $status);
    }
    user_block_user_action($account);
}


/**
 * Set the Loft Core status of a user.
 *
 * You MUST update users.status separate; this module does not handle that.
 *
 * @param $account
 * @param $status
 *
 * @see loft_core_block_user().
 */
function loft_core_set_user_status($account, $status)
{
    $meta = [
        'uid' => $account->uid,
        'status' => $status,
        // Store the initial domain of a user to use in blocking future accounts, potentially.
        'domain' => _loft_core_get_email_domain($account->init),
        'ip' => ip_address(),
    ];
    $meta = array_filter($meta);

    if ($meta['uid']) {
        $query = db_select('loft_core_users', 'lu')
            ->fields('lu', ['uid'])
            ->condition('uid', $meta['uid']);
        $primary_key = $query->execute()->fetchField() ? 'uid' : null;
        drupal_write_record('loft_core_users', $meta, $primary_key);
    }
    else {
        $exists = db_select('loft_core_users', 'lu')
            ->condition('uid', 0)
            ->condition('domain', $meta['domain'])
            ->countQuery()->execute()->fetchField();
        $primary_key = $exists ? ['uid', 'domain'] : null;
        drupal_write_record('loft_core_users', $meta, $primary_key);

        //        if ($exists) {
        //            $num_updated = db_update('loft_core_users')
        //                ->fields($meta)
        //                ->condition('uid', 0)
        //                ->condition('domain', $meta['domain'])
        //                ->execute();
        //        }
        //        else {
        //            db_insert('loft_core_users')
        //                ->fields($meta);
        //        }
    }
}

/**
 * This can be used as an menu page callback to trap robots.
 *
 * The basic idea is that if a visitor hits an url that they shouldn't then by
 * doing so they are exposing their nefarious intentions.  Their account will
 * immediately be blocked and they are logged out.  Watchdog messages will show
 * this has happened.
 *
 * To implement this feature, you need to modify a menu page callback.  And set
 * this function as the page callback.  In this next example we are going to
 * modify a node/add/user-collection to trap users.
 *
 * @code
 *   if (isset($items['node/add/user-collection'])) {
 *     array_unshift($items['node/add/user-collection']['page arguments'],
 *     $items['node/add/user-collection']['page callback']);
 *     $items['node/add/user-collection']['page callback'] =
 *     'loft_core_robotrap_page_callback';
 *   }
 * @endcode
 *
 * You MUST implement hook_loft_core_robotrap_access().
 * You MAY implement hook_loft_core_robotrap_goto_alter().
 */
function loft_core_robotrap_page_callback()
{
    global $user;
    $args = func_get_args();
    $callback = array_shift($args);
    $context = [
        'parent' => __FUNCTION__,
        'account' => $user,
        'page callback' => $callback,
        'page arguments' => $args,
    ];
    $access = module_invoke_all('loft_core_robotrap_access', $context);
    $access = end($access);
    if (!$access) {
        watchdog('loft_core', 'Robotrap: Blocked user %name.', ['%name' => $user->name]);
        loft_core_block_user($user, LOFT_CORE_USER_STATUS_ROBOT);
        module_load_include('inc', 'user', 'user.pages');
        user_logout_current_user();
        drupal_set_message(t('You have visited a forbidden url; your account has been locked for review.  Please contact us using the form below to unlock your account.', ['!url' => url('contact', ['query' => ['cid' => 6]])]), 'warning', false);
        $path = module_exists('contact') ? 'contact' : '<front>';
        unset($_GET['destination']);
        $options = [];
        $context['parent'] = __FUNCTION__;
        drupal_alter('loft_core_robotrap_goto', $path, $options, $context);
        drupal_goto($path, $options);

        return false;
    }
    else {
        return call_user_func_array($callback, $args);
    }
}

/**
 * Return the domain of an email address.
 *
 * @param string $email
 *
 * @return string
 */
function _loft_core_get_email_domain($email)
{
    return preg_replace('/^.+@/', '', $email);
}

/**
 * Implements hook_hook_user_restrictions_alter().
 */
function loft_core_user_restrictions_alter(&$denied, &$error, &$context)
{
    //
    //
    // Restrict any email address whose domain matches that of an already robo-trapped user.
    //
    if ($context['type'] === 'mail') {

        // Check if the domain has been trapped.
        $domain = _loft_core_get_email_domain($context['form_state']['values']['mail']);
        $query = db_select('loft_core_users', 'lu')
            ->fields('lu', ['uid', 'domain'])
            ->condition('status', [LOFT_CORE_USER_STATUS_ROBOT], 'IN')
            ->condition('domain', $domain);

        // If trapped, leverage user_restrictions module to block by email address.
        if ($is_suspicious = $query->execute()->fetchAllAssoc('uid')) {
            watchdog('loft_core', 'Suspicious Domain: Blocked new user @ %domain.', ['%domain' => $domain]);
            $context['mask'] = '*' . $domain;
            $denied = true;
            $error['field'] = 'mail';
            $contact = 'contact us';

            // With the contact module enabled we give an easy way for mistakes to get corrected.
            if (module_exists('contact')) {
                unset($_GET['destination']);
                $options = [];
                $path = 'contact';
                $contact = '<a href="!url">contact us</a>';
                $context['parent'] = __FUNCTION__;
                drupal_alter('loft_core_robotrap_goto', $path, $options, $context);
            }
            $error['message'] = t('Your email address is associated with a suspicious domain (%domain); please ' . $contact . ' if this is in error.', [
                '%domain' => $domain,
                '!url' => url($path, $options),
            ]);
        }
    }
}

/**
 * Implements hook_honeypot_reject().
 *
 * Make a note of this email address/user as a honeybear.
 */
function loft_core_honeypot_reject($form_id, $uid, $type)
{
    $g = data_api();
    if (($mail = $g->get($_POST, 'mail'))) {
        $domain = _loft_core_get_email_domain($mail);
        $default = explode(',', LOFT_CORE_HONEYPOT_REJECT_DOMAINS_WHITELIST);
        $may_block = !in_array($domain, variable_get('loft_core_honeypot_reject_domains_whitelist', $default));
        if ($may_block) {
            $account = (object) [
                'uid' => $uid,
                'init' => $mail,
            ];
            loft_core_set_user_status($account, LOFT_CORE_USER_STATUS_HONEYBEAR);
        }
    }
}

