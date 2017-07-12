# Loft Core: Users

## Suggested modules to use:

### [Honeypot](https://www.drupal.org/project/honeypot)

Honeypot rejections will be tracked as "honey bears" and can later be converted to "spammy" users.  This only works if the `$_POST` array contains the key `mail`.  An example is `user_register_form`, where this works nicely.  Honeybears can be found in the table `loft_core_users` with a status of `LOFT_CORE_USERS_STATUS_HONEYBEAR`.

### [User Restrictions](https://www.drupal.org/project/user_restrictions)

Blocks users with an email address containing any domains that have been found as "spammy".  The list of domains are found in `loft_core_users`.  Check the status because honeybear domains do not block new users.
