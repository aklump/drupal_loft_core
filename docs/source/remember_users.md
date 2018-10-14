# Remember Usernames in Login Form

> If you want a persistent login you should install the [Persistent Login module](https://www.drupal.org/project/persistent_login).  This means that the user will have the option of checking a box that keeps them logged in via a cookie across sessions, that is when they close their browser.  But if they don't check the box they are logged out.

> This is different from core Drupal, which keeps them logged in across sessions always; with no way to opt out.  So the persistent login module adds security for users who are sharing a computer.

> Be sure to follow install instructions as _settings.php_ needs to be modified.

This module has a means to remember the username so that it appears in the login form next time they try to log in.  Which is different from the above, but compilments it well.  In fact this module integrates with the Persistent Login module to leverage it's checkbox.

To enable this feature add the following to _settings.php_:

    $conf['loft_core_users_remember_usernames'] = true;
 
If you enable this feature and you are not going to use Persistent Login, you can leverage a different switch by setting this variable in a hook_form_alter:

    $form['#loft_core_users_remember_key'] = 'some_form_value_that_is_toggled';
    
By default, all usernames will be remembered if you enalbe this feature.

And to control the number of days the username is stored in the cookie you can add this to _settings.php_.

    $conf['loft_core_users_remember_user_for_days'] = 30;
