# Loft Core: Users

Authenticated users are tracked by their uid; no surprise here.

## About anonymous users

Anonymous users are unique by the domain of their email address + their ip.  That is to say that for these three users, only one record will be created (tracked):

| uid | mail | ip |
|----------|----------|----------|
0 | a@spammy.com | 172.1.1.1
0 | b@spammy.com | 172.1.1.1
0 | c@spammy.com | 172.1.1.1

This is because all email address share the same domain and the ip is the same from all three.

But in this next set, three entries would be made because the ip is different for two and non-existent for one, despite having the same base domain.

| uid | mail | ip |
|----------|----------|----------|
0 | a@spammy.com | 172.1.1.1
0 | b@spammy.com | 172.1.1.2
0 | c@spammy.com | null

Also take note that if the ip is the same but the domain differs, then three records will be tracked:

| uid | mail | ip |
|----------|----------|----------|
0 | trouble@spammy.com | 172.1.1.1
0 | trouble@morespam.com | 172.1.1.1
0 | trouble@totalspam.com | 172.1.1.1

**To save database, we don't track anonymous users by email address, but by email domain, as this is the most likely reason to track them: _to block their entire domain._**
## Suggested modules to use:

### [Honeypot](https://www.drupal.org/project/honeypot)

Honeypot rejections will be tracked as "honey bears" and can later be converted to "spammy" users.  This only works if the `$_POST` array contains the key `mail`.  An example is `user_register_form`, where this works nicely.  Honeybears can be found in the table `loft_core_users` with a status of `LOFT_CORE_USERS_STATUS_HONEYBEAR`.

### [User Restrictions](https://www.drupal.org/project/user_restrictions)

Blocks users with an email address containing any domains that have been found as "spammy".  The list of domains are found in `loft_core_users`.  Check the status because honeybear domains do not block new users.
