var tipuesearch = {"pages":[{"title":"Loft Core","text":"  This module contains features that I wish were in core, and which I often use for all my projects.  Documentation can be found in:  docs\/public_html   of with Advanced Help module.  Features   May configure the registration from emails separate from site email.   Contact   In the Loft Studios Aaron Klump - Developer PO Box 29294 Bellingham, WA 98228-1294 skype: intheloftstudios d.o: aklump http:\/\/www.InTheLoftStudios.com  ","tags":"","url":"README.html"},{"title":"Database","text":"  loft_core_users  This table augments the users table.  By design, when a user is deleted, their associated record in this table remains; this is because thier information may be used to fight SPAM.  loft_core_users.status  This column shows if a user has been blocked as a robotrap. ","tags":"","url":"database.html"},{"title":"Forms API","text":"  Hide elements  See loft_core_form_hide_elements().  Disable elements  It is nice to be able to keep an element visible, yet disable it.  Making this easy is the goal of loft_core_form_disable_elements().  Form help  This module defines a new element called 'form_help'. It can also take #weight and #attributes (not shown).      &lt;?php     $form['help'] = [         '#type'    =&gt; 'form_help',          \/\/ Notice an array, where each value is a separate paragraph and will be themed as such. It does not have to be an array, and passing a string is considered a single paragraph.         '#message' =&gt; array(             t(\"You are editing the template for all user collections.\"),             t(\"When a user account is created, the values of this node at the time the user account is created will be copied to the users's account as a Sample Collection.  Changes made to the node are not retroactive and only affect the user collections created from that point forward.\"),         ),     ];   Tabindex  loft_core_form_tabindex()  ","tags":"","url":"forms.html"},{"title":"Images","text":"  Cache Buster  loft_core_image_src_itok_cache_buster ","tags":"","url":"images.html"},{"title":"Loft Core: Users","text":"  Authenticated users are tracked by their uid; no surprise here.  About anonymous users  Anonymous users are unique by the domain of their email address + their ip.  That is to say that for these three users, only one record will be created (tracked):  | uid | mail | ip | |----------|----------|----------| 0 | a@spammy.com | 172.1.1.1 0 | b@spammy.com | 172.1.1.1 0 | c@spammy.com | 172.1.1.1  This is because all email address share the same domain and the ip is the same from all three.  But in this next set, three entries would be made because the ip is different for two and non-existent for one, despite having the same base domain.  | uid | mail | ip | |----------|----------|----------| 0 | a@spammy.com | 172.1.1.1 0 | b@spammy.com | 172.1.1.2 0 | c@spammy.com | null  Also take note that if the ip is the same but the domain differs, then three records will be tracked:  | uid | mail | ip | |----------|----------|----------| 0 | trouble@spammy.com | 172.1.1.1 0 | trouble@morespam.com | 172.1.1.1 0 | trouble@totalspam.com | 172.1.1.1  To save database, we don't track anonymous users by email address, but by email domain, as this is the most likely reason to track them: to block their entire domain.  Suggested modules to use:  Honeypot  Honeypot rejections will be tracked as \"honey bears\" and can later be converted to \"spammy\" users.  This only works if the $_POST array contains the key mail.  An example is user_register_form, where this works nicely.  Honeybears can be found in the table loft_core_users with a status of LOFT_CORE_USERS_STATUS_HONEYBEAR.  User Restrictions  Blocks users with an email address containing any domains that have been found as \"spammy\".  The list of domains are found in loft_core_users.  Check the status because honeybear domains do not block new users.  Todos   [] batch process to mark all users that match domains or ips in our loft_users_core. [] automatically add to blocked_ips table.  ","tags":"","url":"loft_core_users.html"},{"title":"Redirects","text":"  In order to use this feature you must add the following to settings.php  $conf['loft_core_node_redirects'] = true;   To redirect by node type  This module provides a clean means of redirecting node in page view by bundle.  So you could redirect all nodes in a certain bundle to a different page, mark them as access denied or even not found.  It takes the approach of injecting the redirect in node_page_view, rather than invoking something during hook_init(), which should have a lesser footprint.  HOOK_loft_core_redirect_node_BUNDLE_TYPE_view HOOK_loft_core_redirect_node_BUNDLE_TYPE_edit HOOK_loft_core_redirect_node_BUNDLE_TYPE_delete HOOK_loft_core_node_BUNDLE_TYPE_page   If you set his module up to redirect say, blog nodes, then when you create a new blog, using the node edit form, instead of the normal behavior which takes you to the node view page, you will be taken to the node edit page instead.  Otherwise you would have an issue with getting redirected to places you didn't intend, and if the redirect was to another site, then you'd really be confused as an admin. ","tags":"","url":"redirects.html"},{"title":"Remember Usernames in Login Form","text":"     If you want a persistent login you should install the Persistent Login module.  This means that the user will have the option of checking a box that keeps them logged in via a cookie across sessions, that is when they close their browser.  But if they don't check the box they are logged out.      This is different from core Drupal, which keeps them logged in across sessions always; with no way to opt out.  So the persistent login module adds security for users who are sharing a computer.      Be sure to follow install instructions as settings.php needs to be modified.   This module has a means to remember the username so that it appears in the login form next time they try to log in.  Which is different from the above, but compilments it well.  In fact this module integrates with the Persistent Login module to leverage it's checkbox.  To enable this feature add the following to settings.php:  $conf['loft_core_remember_usernames'] = true;   If you enable this feature and you are not going to use Persistent Login, you can leverage a different switch by setting this variable in a hook_form_alter:  $form['#loft_core_remember_key'] = 'some_form_value_that_is_toggled';   By default, all usernames will be remembered if you enalbe this feature.  And to control the number of days the username is stored in the cookie you can add this to settings.php.  $conf['loft_core_remember_user_for_days'] = 30;  ","tags":"","url":"remember_users.html"},{"title":"Search Results","text":" ","tags":"","url":"search--results.html"},{"title":"settings.php","text":"  Files used  settings.env.php settings.dev.php settings.prod.php settings.local.php   Changes to settings.php   Add the following lines to settings.php at the very end:  require dirname(__FILE__) . '\/settings.env.php'; require dirname(__FILE__) . '\/settings.' . DRUPAL_ENV . '.php'; require dirname(__FILE__) . '\/settings.local.php';  Create a file settings.env.php with this:  &lt;?php \/**  * @var $settings_presets  * Define the environment: dev or prod  *  * prod:  * - will server minified js  * - will enable the prod settings presets file  *\/ define('DRUPAL_ENV', 'dev');  Create settings.dev.php and settings.prod.php and put in the environment settings specific to environment, e.g. cache settings. Move the database declaration into settings.local.php.  Also include the following lines:  \/\/ \/\/ \/\/ Define the server environment's role, one of: prod, staging, dev \/\/ \/\/ The role may be different than the environment, for example a staging server should have a production environment, but it is serving a staging role.  Production would have the same 'prod' for both DRUPAL_ENV_ROLE and DRUPAL_ENV. \/\/ define('DRUPAL_ENV_ROLE', 'dev');  If you're using loft_deploy module you must add a $conf var right below the DRUPAL_ENV_ROLE definition.  define('DRUPAL_ENV_ROLE', 'dev'); $conf['loft_deploy_site_role'] = DRUPAL_ENV_ROLE;    Available in PHP as  DRUPAL_ENV DRUPAL_ENV_ROLE   Available in JS as  Drupal.settings.DRUPAL_ENV Drupal.settings.DRUPAL_ENV_ROLE  ","tags":"","url":"settings.html"},{"title":"Testing","text":"  Test mode is enabled by default when DRUPAL_ENV does not equal 'prod'.  This means that test classes will be appened to elements whenever the function loft_core_test_class() is used.  When not in test mode, test classes will not be added.  Testing on Prod  In order to test against a production environment you need to enable test mode using an endpoint.  You must set up the endpoint with an access key in your settings.php like this:  $conf['loft_core_testing_key'] = '{some obscure public key that will appear in your url}';   When you visit the url endpoint, include the testing key like so:  \/loft-core\/testing\/enable\/{testing key}   This will enable the test mode for a short duration and cause test classes to appear on production, for your IP only. ","tags":"","url":"testing.html"},{"title":"Add TrackJS.com to your website","text":"   Sign up for an account at http:\/\/www.trackjs.com Obtain your token and add it to settings.php  $conf['loft_core_trackjs_token'] = '5302679c8b624e0395a6a6da5b1199d6';  Add the following snippet in html.tpl.php in your theme before $scripts output.  &lt;?= $loft_core_tracking ?&gt; &lt;?= $scripts; ?&gt;  This will only be present when DRUPAL_ENV_ROLE is prod.   Config and Metadata  See HOOK_loft_core_trackjs_alter() for more info. ","tags":"","url":"trackjs.html"}]};
